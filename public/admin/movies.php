<?php

require __DIR__ . '/../../components/require-admin.php';
require __DIR__ . '/../../controllers/AdminMovieController.php';

$controller = new AdminMovieController();
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['action'] ?? '') : '';

if ($action === 'save') {
    $controller->save($_POST, $_GET);
} elseif ($action === 'delete') {
    $controller->delete((int) $_POST['id']);
} else {
    $controller->index($_GET);
}
