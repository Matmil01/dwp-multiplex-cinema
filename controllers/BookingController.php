<?php

class BookingController
{
    public function showSeats(int $showingId, ?int $userId): void
    {
        // Admins get the read-only seat map (any showing, past or upcoming) instead of the booking picker.
        if (($_SESSION['role'] ?? '') === 'admin') {
            header('Location: /admin/showing-seats?showing=' . $showingId);
            exit;
        }

        $showing = $this->bookableShowing($showingId, $userId);

        if ($showing) {
            $this->renderSeatPicker($showing, null, []);
        }
    }

    public function book(int $showingId, ?int $userId, array $seats): void
    {
        if (($_SESSION['role'] ?? '') === 'admin') {
            http_response_code(403);
            echo 'Admins can\'t book seats.';
            return;
        }

        $showing = $this->bookableShowing($showingId, $userId);

        if (!$showing) {
            return;
        }

        try {
            $bookingId = Booking::create($userId, $showing, $seats);
        } catch (RuntimeException $e) {
            $this->renderSeatPicker($showing, $e->getMessage(), $seats);
            return;
        }

        $this->sendConfirmation($bookingId);

        header('Location: /profile');
        exit;
    }

    // The booking is already saved, so a failed email is ignored rather than shown as a booking error.
    private function sendConfirmation(int $bookingId): void
    {
        $booking = Booking::find($bookingId);
        $seats = implode(', ', array_map(fn($seat) => $seat['seat_row'] . $seat['seat_number'], $booking['seats']));
        $total = number_format((float) $booking['price_per_seat'] * count($booking['seats']), 0);

        $body = "Hi {$booking['customer_name']},\n\n"
            . "Your booking is confirmed.\n\n"
            . "Movie: {$booking['movie_title']}\n"
            . 'When: ' . date('D j M Y, H:i', strtotime($booking['show_datetime'])) . "\n"
            . "Seats: $seats\n"
            . "Total: $total kr.\n";

        Mailer::send($booking['customer_email'], 'Your booking at Untitled Cinema', $body);
    }

    // Sends logged-out visitors to /login and returns null (after a 404) when the showing is missing or already started.
    private function bookableShowing(int $showingId, ?int $userId): ?array
    {
        if ($userId === null) {
            header('Location: /login');
            exit;
        }

        $showing = Showing::findForBooking($showingId);

        if (!$showing || !Booking::isOpen($showing)) {
            http_response_code(404);
            echo 'Showing not found.';
            return null;
        }

        return $showing;
    }

    private function renderSeatPicker(array $showing, ?string $error, array $selectedSeats): void
    {
        $takenSeats = Booking::takenSeats($showing['id']);

        require __DIR__ . '/../views/book.php';
    }
}
