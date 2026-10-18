<?php

session_start();
require __DIR__ . '/../controllers/MovieController.php';

(new MovieController())->show((int) ($_GET['id'] ?? 0));
