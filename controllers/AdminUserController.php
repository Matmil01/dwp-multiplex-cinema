<?php

class AdminUserController
{
    public function index(): void
    {
        $users = User::all();

        require __DIR__ . '/../views/admin/users.php';
    }
}