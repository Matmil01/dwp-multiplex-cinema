<?php

session_start();
require __DIR__ . '/../controllers/MovieController.php';

(new MovieController())->index((string) ($_GET['status'] ?? ''));
