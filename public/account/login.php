<?php

session_start();
require __DIR__ . '/../../controllers/AccountController.php';

$controller = new AccountController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->login(trim($_POST['email'] ?? ''), $_POST['password'] ?? '');
} else {
    $controller->showLoginForm();
}
