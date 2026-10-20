<?php
// Include first on every admin page: anyone who isn't a logged-in admin is sent to the login page.
session_start();

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /login');
    exit;
}
