<?php

session_start();
require __DIR__ . '/../../controllers/AccountController.php';

(new AccountController())->logout();
