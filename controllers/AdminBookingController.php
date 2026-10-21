<?php

class AdminBookingController
{
    public function index(): void
    {
        $bookings = Booking::all();

        require __DIR__ . '/../views/admin/bookings.php';
    }

    // $showingId is only set by the showing-seats page, so the admin lands back there instead of on the bookings list.
    public function cancel(int $id, int $showingId = 0): void
    {
        Booking::cancel($id, null);

        header('Location: ' . ($showingId > 0 ? '/admin/showing-seats?showing=' . $showingId : '/admin/bookings'));
        exit;
    }

    public function showingSeats(int $showingId): void
    {
        $showing = Showing::findForBooking($showingId);

        if (!$showing) {
            http_response_code(404);
            echo 'Showing not found.';
            return;
        }

        $seatOwners = [];
        $bookings = [];

        foreach (Booking::seatsForShowing($showingId) as $seat) {
            $seatId = $seat['seat_row'] . $seat['seat_number'];
            $seatOwners[$seatId] = $seat;

            $bookings[$seat['booking_id']] ??= [
                'id' => $seat['booking_id'],
                'customer_name' => $seat['customer_name'],
                'customer_email' => $seat['customer_email'],
                'seats' => [],
            ];
            $bookings[$seat['booking_id']]['seats'][] = $seatId;
        }

        require __DIR__ . '/../views/admin/showing-seats.php';
    }
}
