<?php

class AdminBookingController
{
    public function index(): void
    {
        $bookings = Booking::all();

        require __DIR__ . '/../views/admin/bookings.php';
    }

    public function cancel(int $id): void
    {
        Booking::cancel($id, null);

        header('Location: /admin/bookings');
        exit;
    }

    public function invoice(int $id): void
    {
        $booking = Booking::find($id);

        if (!$booking) {
            http_response_code(404);
            echo 'Booking not found.';
            return;
        }

        $company = CompanyInfo::find();

        require __DIR__ . '/../views/admin/invoice.php';
    }
}
