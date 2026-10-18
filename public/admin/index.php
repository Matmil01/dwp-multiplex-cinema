<?php

require __DIR__ . '/../../components/require-admin.php';
require __DIR__ . '/../../controllers/AdminDashboardController.php';

(new AdminDashboardController())->index();
