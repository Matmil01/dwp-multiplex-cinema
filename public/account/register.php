<?php

session_start();
require __DIR__ . '/../../controllers/AccountController.php';

$controller = new AccountController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->register(
        trim($_POST['name'] ?? ''),
        trim($_POST['email'] ?? ''),
        $_POST['password'] ?? '',
        $_POST['password_confirm'] ?? ''
    );
} else {
    $controller->showRegisterForm();
}
