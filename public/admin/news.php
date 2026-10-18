<?php

require __DIR__ . '/../../components/require-admin.php';
require __DIR__ . '/../../controllers/AdminNewsController.php';

$controller = new AdminNewsController();
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['action'] ?? '') : '';

if ($action === 'save') {
    $controller->save($_POST, $_FILES);
} elseif ($action === 'delete') {
    $controller->delete((int) $_POST['id']);
} else {
    $controller->index($_GET);
}
