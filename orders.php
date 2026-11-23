<?php
// Features 3 and 4: Manage Orders, Invoices and Confirm Orders. Builder: P4.
// This page only decides WHICH OrderUI method to call. OrderUI does the work.
require __DIR__ . '/src/bootstrap.php';

$action = $_GET['action'] ?? 'list';
$formWasSent = ($_SERVER['REQUEST_METHOD'] == 'POST');
$orderId = $_POST['order'] ?? $_GET['order'] ?? '';

if ($action == 'new') {
    if ($formWasSent) {
        $orderUI->submitOrder($_POST);
    } else {
        $orderUI->showNewOrderForm($_GET['customer'] ?? '');
    }
} elseif ($action == 'cancel') {
    $orderUI->submitCancel($orderId);
} elseif ($action == 'invoice') {
    $orderUI->submitGenerateInvoice($orderId);
} elseif ($action == 'confirm') {
    $orderUI->submitConfirm($orderId);
} elseif ($action == 'pdf') {
    $orderUI->downloadInvoice($orderId);
} else {
    $orderUI->showOrders();
}
