<?php

require __DIR__ . '/../../components/require-admin.php';
require __DIR__ . '/../../controllers/AdminScheduleController.php';

$controller = new AdminScheduleController();
$date = $_GET['date'] ?? date('Y-m-d');
$action = $_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['action'] ?? '') : '';

if ($action === 'save') {
    $controller->save($date, $_POST);
} elseif ($action === 'delete') {
    $controller->delete($date, (int) $_POST['id']);
} elseif ($action === 'move') {
    $controller->move($_POST);
} elseif ($action === 'duplicate') {
    $controller->duplicate($_POST);
} else {
    $controller->index($date, $_GET);
}
