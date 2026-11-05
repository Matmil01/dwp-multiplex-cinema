<?php

// All reads/writes for the showings table (a movie playing on a screen at a specific time).
class Showing
{
    // The four screens, always in the same order (smallest film format to largest). Without
    // FIELD(...) the database would sort them alphabetically: 16mm, 35mm, 70mm, imax happens
    // to be alphabetical too, but FIELD makes the intended order explicit.
    public static function screens(): array
    {
        return Db::connection()->query(
            "SELECT id, type FROM screens ORDER BY FIELD(type, '16mm', '35mm', '70mm', 'imax')"
        )->fetchAll();
    }

    // Every screen (even ones with nothing scheduled) plus its upcoming showings, for the public
    // Screens page's full schedule — LEFT JOIN keeps empty screens in the result.
    // Note the time filter (show_datetime >= NOW()) sits inside the ON part of the LEFT JOIN,
    // not in a WHERE. In a WHERE it would throw away screens with no future showings; in the
    // ON it just leaves those screens with empty showing columns.
    public static function upcomingByScreen(): array
    {
        return Db::connection()->query(
            "SELECT sc.id AS screen_id, sc.type AS screen_type,
                    s.id AS showing_id, s.show_datetime, m.id AS movie_id, m.title AS movie_title
             FROM screens sc
             LEFT JOIN showings s ON s.screen_id = sc.id AND s.show_datetime >= NOW()
             LEFT JOIN movies m ON m.id = s.movie_id
               ORDER BY FIELD(sc.type, '16mm', '35mm', '70mm', 'imax'), s.show_datetime"
        )->fetchAll();
    }

    // Today's showings (one row per showing, not grouped) for the front page's "Today's Program" —
    // grouping by movie for display happens in PHP, same as schedule.php groups by screen.
    // today_showings is not a real table but a VIEW (database/views.sql): a saved SELECT with
    // all the joins already written, that can be queried as if it were a table. The exam
    // brief asks for views, and keeping the long join in the database also keeps this short.
    public static function today(): array
    {
        return Db::connection()->query(
            'SELECT * FROM today_showings ORDER BY show_datetime'
        )->fetchAll();
    }

    // One row per screen type's earliest showing after today, for the front page's "Later This Week" teaser.
    // This is the most complex query in the project. Piece by piece:
    //   GROUP_CONCAT(...)    glues a movie's genre names into one text "Drama, Sci-Fi" so the
    //                        result stays one row per showing instead of one per genre
    //   CURDATE() + INTERVAL 1 DAY   = tomorrow at 00:00, i.e. "from tomorrow onwards"
    //   AND s.id = ( SELECT ... )    a query inside the query (a "subquery"). For the current
    //                        showing's screen type, it finds the id of the very first showing
    //                        from tomorrow on. Only the showing with that id is kept — so we
    //                        end up with exactly one showing per screen type.
    //   GROUP BY             required whenever GROUP_CONCAT (or MAX, COUNT...) is used: lists
    //                        every normal column so the database knows what "one row" means
    public static function laterThisWeekPreview(int $limit): array
    {
        $stmt = Db::connection()->prepare(
            "SELECT m.id AS movie_id, m.title, m.release_year, m.duration_minutes, m.poster_url,
                    sc.type AS screen_type,
                    s.show_datetime AS next_show_datetime,
                    GROUP_CONCAT(DISTINCT g.name ORDER BY g.name SEPARATOR ', ') AS genres
             FROM showings s
             JOIN movies m ON m.id = s.movie_id
             JOIN screens sc ON sc.id = s.screen_id
             LEFT JOIN movie_genres mg ON mg.movie_id = m.id
             LEFT JOIN genres g ON g.id = mg.genre_id
             WHERE s.show_datetime >= CURDATE() + INTERVAL 1 DAY
               AND s.id = (
                   SELECT s2.id
                   FROM showings s2
                   JOIN screens sc2 ON sc2.id = s2.screen_id
                   WHERE sc2.type = sc.type
                     AND s2.show_datetime >= CURDATE() + INTERVAL 1 DAY
                   ORDER BY s2.show_datetime, s2.id
                   LIMIT 1
                 )
             GROUP BY m.id, m.title, m.release_year, m.duration_minutes, m.poster_url, sc.type, s.show_datetime
             ORDER BY FIELD(sc.type, '16mm', '35mm', '70mm', 'imax')
             LIMIT ?"
        );
        // LIMIT needs a real number, but execute([...]) sends everything as text, which the
        // database rejects here. bindValue with PARAM_INT fills the ? as a number instead.
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    // One day's showings, joined with the movie title/duration for the admin schedule grid.
    // DATE(...) cuts the time off "2026-10-31 20:00:00" so it can be compared to "2026-10-31".
    public static function forDate(string $date): array
    {
        $stmt = Db::connection()->prepare(
            'SELECT s.id, s.screen_id, s.movie_id, s.show_datetime, m.title AS movie_title, m.duration_minutes
             FROM showings s
             JOIN movies m ON m.id = s.movie_id
             WHERE DATE(s.show_datetime) = ?
             ORDER BY s.show_datetime'
        );
        $stmt->execute([$date]);

        return $stmt->fetchAll();
    }

    // A single showing plus its movie's title, shaped for pre-filling the admin editor form.
    public static function find(int $id): ?array
    {
        $stmt = Db::connection()->prepare(
            'SELECT s.*, m.title AS movie_title FROM showings s JOIN movies m ON m.id = s.movie_id WHERE s.id = ?'
        );
        $stmt->execute([$id]);

        // fetch() gives false when nothing matched; `?: null` turns that into null.
        return $stmt->fetch() ?: null;
    }

    // One showing with its movie title plus the screen's seating layout and ticket price, for the booking page.
    // seat_rows / seats_per_row / aisle_after describe the room so the seat picker can draw it.
    public static function findForBooking(int $id): ?array
    {
        $stmt = Db::connection()->prepare(
            'SELECT s.id, s.show_datetime, m.title AS movie_title,
                    sc.type AS screen_type, sc.seat_rows, sc.seats_per_row, sc.aisle_after, sc.ticket_price
             FROM showings s
             JOIN movies m ON m.id = s.movie_id
             JOIN screens sc ON sc.id = s.screen_id
             WHERE s.id = ?'
        );
        $stmt->execute([$id]);

        return $stmt->fetch() ?: null;
    }

    // Upcoming showings for one movie, joined with the screen type, for the public movie page.
    public static function upcomingForMovie(int $movieId): array
    {
        $stmt = Db::connection()->prepare(
            'SELECT s.id, s.show_datetime, sc.type AS screen_type
             FROM showings s
             JOIN screens sc ON sc.id = s.screen_id
             WHERE s.movie_id = ? AND s.show_datetime >= NOW()
             ORDER BY s.show_datetime'
        );
        $stmt->execute([$movieId]);

        return $stmt->fetchAll();
    }

    // Overlap prevention lives in a DB trigger (see schema.sql) — this just surfaces that
    // trigger's error as a normal exception for the admin form to display.
    // Why in the database and not here: a trigger runs on EVERY insert/update of showings,
    // no matter which PHP code (or a person at the SQL prompt) does it, so the rule can't be
    // bypassed by forgetting a check. Called by the admin schedule page when saving a showing.
    public static function save(array $input): int
    {
        $db = Db::connection();

        // Read the saved runtime so the form cannot supply a made-up movie length.
        $runtimeQuery = $db->prepare('SELECT duration_minutes FROM movies WHERE id = ?');
        $runtimeQuery->execute([$input['movie_id']]);
        // Missing, zero, or negative runtimes cannot be used to check whether showings overlap.
        // throw = stop here and hand this error up to the controller, which shows the message.
        if ((int) $runtimeQuery->fetchColumn() <= 0) {
            throw new RuntimeException('Set a runtime greater than zero for this movie in Movies before scheduling it.');
        }

        // Changing a showing that people have booked would move or break their seats.
        // An id in the input means "editing an existing showing" (new ones have no id yet).
        // SELECT 1 ... LIMIT 1 is the cheapest way to ask "does at least one such row exist?".
        if (!empty($input['id'])) {
            $bookedSeat = $db->prepare('SELECT 1 FROM booking_seats WHERE showing_id = ? LIMIT 1');
            $bookedSeat->execute([$input['id']]);
            if ($bookedSeat->fetchColumn()) {
                throw new RuntimeException('This showing has bookings, so it can\'t be changed. Cancel its bookings first.');
            }
        }

        $params = [$input['movie_id'], $input['screen_id'], $input['show_datetime']];

        try {
            // No id: create a new showing.
            if (empty($input['id'])) {
                $db->prepare(
                    'INSERT INTO showings (movie_id, screen_id, show_datetime) VALUES (?, ?, ?)'
                )->execute($params);

                return (int) $db->lastInsertId();
            }

            // Has an id: change the existing one. `...$params` unpacks the three values so the
            // list becomes [movie_id, screen_id, show_datetime, id] — matching the four ?s.
            $db->prepare(
                'UPDATE showings SET movie_id = ?, screen_id = ?, show_datetime = ? WHERE id = ?'
            )->execute([...$params, $input['id']]);

            return (int) $input['id'];
        } catch (PDOException $e) {
            // 45000 is the error number our trigger uses when it refuses an overlapping showing.
            if ($e->getCode() === '45000') {
                // Turn database's overlap error into a message the admin form can show.
                // errorInfo[2] is the sentence the trigger itself wrote (see triggers.sql).
                throw new RuntimeException($e->errorInfo[2] ?? $e->getMessage());
            }

            throw $e;
        }
    }

    public static function delete(int $id): void
    {
        // Bookings are deliberately not deleted along with a showing (no ON DELETE CASCADE on
        // that link in schema.sql), so the database refuses if someone has booked seats for it.
        try {
            Db::connection()->prepare('DELETE FROM showings WHERE id = ?')->execute([$id]);
        } catch (PDOException $e) {
            // 1451 is MariaDB's "row is still referenced" error: bookings point at this showing.
            if (($e->errorInfo[1] ?? 0) === 1451) {
                throw new RuntimeException('This showing has bookings and can\'t be deleted.');
            }

            throw $e;
        }
    }
}
