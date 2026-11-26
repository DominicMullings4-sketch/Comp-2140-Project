<?php
// Feature 4: Record Orders (staff's home page).
// This page only decides WHICH OrderUI method to call. OrderUI does the work.
require __DIR__ . '/bootstrap.php';

$ui = new OrderUI();
$action = $_GET['action'] ?? 'new';

if ($action === 'invoice') {
    $ui->showInvoice((int) ($_GET['id'] ?? 0));          // orders.php?action=invoice&id=12
} elseif ($action === 'customer') {
    $ui->showCustomerOrders((int) ($_GET['id'] ?? 0));   // orders.php?action=customer&id=3
} elseif (is_post()) {
    $ui->submitOrder($_POST);
} else {
    $ui->showOrderForm();
}
