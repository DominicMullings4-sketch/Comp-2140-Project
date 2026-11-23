<?php
// Feature 1: Record Stock Received. Builder: P2.
// This page only decides WHICH InventoryUI method to call. InventoryUI does the work.
require __DIR__ . '/src/bootstrap.php';

$action = $_GET['action'] ?? 'items';
$formWasSent = ($_SERVER['REQUEST_METHOD'] == 'POST');

if ($action == 'receipt') {
    if ($formWasSent) {
        $inventoryUI->submitStockReceipt($_POST);
    } else {
        $inventoryUI->showStockReceiptForm();
    }
} elseif ($action == 'item') {
    $inventoryUI->submitNewItem($_POST);
} elseif ($action == 'supplier') {
    $inventoryUI->submitNewSupplier($_POST);
} else {
    $inventoryUI->showItems();
}
