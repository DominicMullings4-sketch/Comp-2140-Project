<?php
require __DIR__ . '/src/bootstrap.php';

$action = $_GET['action'] ?? 'awaiting';
if ($action === 'today') {
    $deliveryUI->showTodaysDeliveries();
} elseif ($action === 'completed' || $action === 'failed') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Allow: POST', true, 405);
        exit;
    }
    if ($action === 'completed') {
        $deliveryUI->submitCompleted($_POST['delivery'] ?? '');
    } else {
        $deliveryUI->submitFailed($_POST['delivery'] ?? '');
    }
} else {
    $deliveryUI->showAwaitingAssignment();
}
