<?php

session_start();
require __DIR__ . '/../controllers/HomeController.php';

(new HomeController())->index();
