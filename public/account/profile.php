<?php

session_start();
require __DIR__ . '/../../controllers/AccountController.php';

(new AccountController())->profile(isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null);
