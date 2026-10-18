<?php

require __DIR__ . '/../../components/require-admin.php';
require __DIR__ . '/../../controllers/AdminSettingsController.php';

$controller = new AdminSettingsController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->save($_POST);
} else {
    $controller->showForm();
}
