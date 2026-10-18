<?php

session_start();
require __DIR__ . '/../controllers/NewsController.php';

(new NewsController())->index();
