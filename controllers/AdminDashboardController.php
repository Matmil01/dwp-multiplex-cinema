<?php

require_once __DIR__ . '/../models/Showing.php';

class AdminDashboardController
{
    public function index(): void
    {
        $showingsToday = count(Showing::today());

        require __DIR__ . '/../views/admin/index.php';
    }
}
