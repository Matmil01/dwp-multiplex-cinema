<?php

class AdminBookingController
{
    public function index(array $query): void
    {
        $bookings = Booking::all();
        [$snackbarType, $snackbarMessage] = $this->cancelSnackbar($query);

        require __DIR__ . '/../templates/admin/bookings.php';
    }

    public function cancel(int $id, int $showingId = 0): void
    {
        $cancelled = Booking::cancel($id, null);
        $flag = $cancelled ? 'cancelled=1' : 'cancel_failed=1';

        header('Location: ' . ($showingId > 0 ? "/admin/showing-seats?showing=$showingId&$flag" : "/admin/bookings?$flag"));
        exit;
    }

    public function showingSeats(int $showingId, array $query): void
    {
        $showing = Showing::findForBooking($showingId);

        if (!$showing) {
            http_response_code(404);
            $title = 'Showing not found';
            $message = 'No showing has that id.';
            require __DIR__ . '/../templates/error.php';
            return;
        }

        [$snackbarType, $snackbarMessage] = $this->cancelSnackbar($query);

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

        require __DIR__ . '/../templates/admin/showing-seats.php';
    }

    // cancel() redirects back with a flag in the address; this turns it into the snackbar's [type, message].
    private function cancelSnackbar(array $query): array
    {
        if (isset($query['cancelled'])) {
            return ['success', 'Booking cancelled.'];
        }
        if (isset($query['cancel_failed'])) {
            return ['error', 'That booking could not be cancelled. It may have already started.'];
        }

        // No flag: no snackbar.
        return [null, null];
    }
}
