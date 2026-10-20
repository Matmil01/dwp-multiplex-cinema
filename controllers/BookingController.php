<?php

class BookingController
{
    public function showSeats(int $showingId, ?int $userId): void
    {
        $showing = $this->bookableShowing($showingId, $userId);

        if ($showing) {
            $this->renderSeatPicker($showing, null, []);
        }
    }

    public function book(int $showingId, ?int $userId, array $seats): void
    {
        $showing = $this->bookableShowing($showingId, $userId);

        if (!$showing) {
            return;
        }

        try {
            Booking::create($userId, $showing, $seats);
        } catch (RuntimeException $e) {
            $this->renderSeatPicker($showing, $e->getMessage(), $seats);
            return;
        }

        header('Location: /profile');
        exit;
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
