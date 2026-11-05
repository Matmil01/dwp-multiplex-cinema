<?php

class AdminUserController
{
    public function index(): void
    {
        $users = User::all();

        require __DIR__ . '/../templates/admin/users.php';
    }
}