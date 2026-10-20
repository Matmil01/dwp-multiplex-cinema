<?php

// All reads/writes for bookings and their reserved seats.
class Booking
{
    public const MAX_SEATS = 5;

    // A showing can be booked until it starts; takes any row that has show_datetime.
    public static function isOpen(array $showing): bool
    {
        return strtotime($showing['show_datetime']) >= time();
    }

    // Saves a booking and its seats together, or nothing at all. $seats are ids like "B4".
    public static function create(int $userId, array $showing, array $seats): int
    {
        if (!self::isOpen($showing)) {
            throw new RuntimeException('This showing has already started.');
        }

        $seats = array_values(array_unique($seats));

        if (count($seats) === 0) {
            throw new RuntimeException('Pick at least one seat.');
        }
        if (count($seats) > self::MAX_SEATS) {
            throw new RuntimeException('You can book up to ' . self::MAX_SEATS . ' seats at a time.');
        }

        $positions = [];
        foreach ($seats as $seat) {
            $position = self::parseSeat($seat, $showing);
            if ($position === null) {
                throw new RuntimeException('That seat does not exist on this screen.');
            }
            $positions[] = $position;
        }

        if (self::leavesLoneSeat($positions, self::takenSeats((int) $showing['id']))) {
            throw new RuntimeException('Please don\'t leave a single empty seat between your seats and another booking.');
        }

        $db = Db::connection();
        $db->beginTransaction();

        try {
            $db->prepare(
                'INSERT INTO bookings (user_id, showing_id, price_per_seat) VALUES (?, ?, ?)'
            )->execute([$userId, $showing['id'], $showing['ticket_price']]);
            $bookingId = (int) $db->lastInsertId();

            $insertSeat = $db->prepare(
                'INSERT INTO booking_seats (booking_id, showing_id, seat_row, seat_number) VALUES (?, ?, ?, ?)'
            );
            foreach ($positions as $position) {
                $insertSeat->execute([$bookingId, $showing['id'], $position[0], $position[1]]);
            }

            $db->commit();

            return $bookingId;
        } catch (PDOException $e) {
            $db->rollBack();

            // 1062 is MariaDB's duplicate-key error: the UNIQUE key on booking_seats caught a seat someone else just took.
            if (($e->errorInfo[1] ?? 0) === 1062) {
                throw new RuntimeException('One of those seats was just taken. Please pick again.');
            }

            throw $e;
        }
    }

    // Splits "B4" into ['B', 4], or null if the seat is outside the screen's grid.
    private static function parseSeat(string $seat, array $showing): ?array
    {
        if (!preg_match('/^([A-Z])([0-9]{1,2})$/', $seat, $parts)) {
            return null;
        }

        $rowIndex = ord($parts[1]) - ord('A');
        $number = (int) $parts[2];

        if ($rowIndex >= (int) $showing['seat_rows'] || $number < 1 || $number > (int) $showing['seats_per_row']) {
            return null;
        }

        return [$parts[1], $number];
    }

    // True if the new seats would leave exactly one empty seat between two occupied seats in a row.
    // Only gaps next to a new seat can be new, so only those are checked.
    private static function leavesLoneSeat(array $positions, array $takenSeats): bool
    {
        $occupied = array_flip($takenSeats);
        foreach ($positions as [$row, $number]) {
            $occupied[$row . $number] = true;
        }

        foreach ($positions as [$row, $number]) {
            foreach ([-1, 1] as $direction) {
                $gap = $row . ($number + $direction);
                $beyond = $row . ($number + 2 * $direction);

                if (!isset($occupied[$gap]) && isset($occupied[$beyond])) {
                    return true;
                }
            }
        }

        return false;
    }

    // Mirrors the conditions cancel() enforces for a customer, so pages only offer a button that will work.
    public static function canCancel(array $booking): bool
    {
        return $booking['status'] !== 'cancelled' && self::isOpen($booking);
    }

    // Cancels a booking and frees its seats. With $userId, only that customer's own booking, and only before the showing starts.
    public static function cancel(int $bookingId, ?int $userId): bool
    {
        $sql = "UPDATE bookings b JOIN showings s ON s.id = b.showing_id SET b.status = 'cancelled'
                WHERE b.id = ? AND b.status <> 'cancelled'";
        $params = [$bookingId];

        if ($userId !== null) {
            $sql .= ' AND b.user_id = ? AND s.show_datetime >= NOW()';
            $params[] = $userId;
        }

        $db = Db::connection();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $cancelled = $stmt->rowCount() > 0;

            if ($cancelled) {
                $db->prepare('DELETE FROM booking_seats WHERE booking_id = ?')->execute([$bookingId]);
            }

            $db->commit();

            return $cancelled;
        } catch (PDOException $e) {
            $db->rollBack();

            throw $e;
        }
    }

    // Active bookings for showings that are today, for the admin dashboard.
    public static function countForToday(): int
    {
        return (int) Db::connection()->query(
            "SELECT COUNT(*) FROM bookings b JOIN showings s ON s.id = b.showing_id
             WHERE DATE(s.show_datetime) = CURDATE() AND b.status <> 'cancelled'"
        )->fetchColumn();
    }

    // One booking with its customer, showing details and an ordered list of its seats, for the invoice.
    public static function find(int $id): ?array
    {
        $db = Db::connection();

        $stmt = $db->prepare(
            'SELECT b.id, b.status, b.price_per_seat, b.created_at, u.name AS customer_name, u.email AS customer_email,
                    s.show_datetime, m.title AS movie_title, sc.type AS screen_type
             FROM bookings b
             JOIN users u ON u.id = b.user_id
             JOIN showings s ON s.id = b.showing_id
             JOIN movies m ON m.id = s.movie_id
             JOIN screens sc ON sc.id = s.screen_id
             WHERE b.id = ?'
        );
        $stmt->execute([$id]);
        $booking = $stmt->fetch();

        if (!$booking) {
            return null;
        }

        $seats = $db->prepare(
            'SELECT seat_row, seat_number FROM booking_seats WHERE booking_id = ? ORDER BY seat_row, seat_number'
        );
        $seats->execute([$id]);
        $booking['seats'] = $seats->fetchAll();

        return $booking;
    }

    // Every booking with customer, movie, screen and seat count, newest showing first, for the admin overview.
    public static function all(): array
    {
        return Db::connection()->query(
            'SELECT b.id, b.status, b.price_per_seat, u.name AS customer_name, u.email AS customer_email,
                    s.show_datetime, m.title AS movie_title, sc.type AS screen_type,
                    COUNT(bs.id) AS seat_count
             FROM bookings b
             JOIN users u ON u.id = b.user_id
             JOIN showings s ON s.id = b.showing_id
             JOIN movies m ON m.id = s.movie_id
             JOIN screens sc ON sc.id = s.screen_id
             LEFT JOIN booking_seats bs ON bs.booking_id = b.id
             GROUP BY b.id, b.status, b.price_per_seat, u.name, u.email, s.show_datetime, m.title, sc.type
             ORDER BY s.show_datetime DESC'
        )->fetchAll();
    }

    // A user's bookings, newest showing first, with the movie, screen and how many seats each holds.
    public static function forUser(int $userId): array
    {
        $stmt = Db::connection()->prepare(
            'SELECT b.id, b.status, s.show_datetime, m.title AS movie_title, sc.type AS screen_type,
                    COUNT(bs.id) AS seat_count
             FROM bookings b
             JOIN showings s ON s.id = b.showing_id
             JOIN movies m ON m.id = s.movie_id
             JOIN screens sc ON sc.id = s.screen_id
             LEFT JOIN booking_seats bs ON bs.booking_id = b.id
             WHERE b.user_id = ?
             GROUP BY b.id, b.status, s.show_datetime, m.title, sc.type
             ORDER BY s.show_datetime DESC'
        );
        $stmt->execute([$userId]);

        return $stmt->fetchAll();
    }

    // Seat ids like "B4" already reserved for a showing; cancelled bookings delete their seats, so every row counts.
    public static function takenSeats(int $showingId): array
    {
        $stmt = Db::connection()->prepare(
            'SELECT CONCAT(seat_row, seat_number) FROM booking_seats WHERE showing_id = ?'
        );
        $stmt->execute([$showingId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
