<?php
// Features 5 and 6: Assign Deliveries and Confirm Delivery. Builder: P5.
// This page only decides WHICH DeliveryUI method to call. DeliveryUI does the work.
require __DIR__ . '/src/bootstrap.php';

$action = $_GET['action'] ?? 'awaiting';

if ($action == 'assign') {
    $deliveryUI->submitAssignment($_POST['order'], $_POST['worker'], $_POST['date']);
} elseif ($action == 'today') {
    $deliveryUI->showTodaysDeliveries();
} elseif ($action == 'completed') {
    $deliveryUI->submitCompleted($_POST['delivery']);
} elseif ($action == 'failed') {
    $deliveryUI->submitFailed($_POST['delivery']);
} else {
    $deliveryUI->showAwaitingAssignment();
}
