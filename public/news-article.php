<?php

session_start();
require __DIR__ . '/../controllers/NewsController.php';

(new NewsController())->show((int) ($_GET['id'] ?? 0));
