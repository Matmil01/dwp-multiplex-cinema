<?php

session_start();
require __DIR__ . '/../controllers/ScreenController.php';

(new ScreenController())->index();
