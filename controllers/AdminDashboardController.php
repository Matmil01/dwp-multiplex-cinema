<?php

class AdminDashboardController
{
    public function index(): void
    {
        $showingsToday = count(Showing::today());
        $bookingsToday = Booking::countForToday();

        require __DIR__ . '/../views/admin/index.php';
    }
}
