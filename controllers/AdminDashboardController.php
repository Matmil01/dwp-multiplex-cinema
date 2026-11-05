<?php

class AdminDashboardController
{
    public function index(): void
    {
        $showingsToday = count(Showing::today());
        $bookingsToday = Booking::countForToday();
        $totalUsers = count(User::all());

        require __DIR__ . '/../templates/admin/index.php';
    }
}
