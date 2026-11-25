<?php
// Feature 1: Record Stock Received. Builder: P2.
// This page only decides WHICH InventoryUI method to call. InventoryUI does the work.
require __DIR__ . '/src/bootstrap.php';

$action = $_GET['action'] ?? 'items';
$formWasSent = ($_SERVER['REQUEST_METHOD'] == 'POST');
$id = $_GET['id'] ?? '';

if ($action == 'receipt') {
    if ($formWasSent) {
        $inventoryUI->submitStockReceipt($_POST);
    } else {
        $inventoryUI->showStockReceiptForm();
    }
} elseif ($action == 'viewreceipt') {
    $inventoryUI->showStockReceipt($id);                 // inventory.php?action=viewreceipt&id=SR-...
} elseif ($action == 'item' && $formWasSent) {
    $inventoryUI->submitNewItem($_POST);
} elseif ($action == 'edit') {
    if ($formWasSent) {
        $inventoryUI->submitItemEdit($id, $_POST);       // inventory.php?action=edit&id=I-...
    } else {
        $inventoryUI->showItems([], null, $id);
    }
} elseif ($action == 'supplier') {
    $inventoryUI->submitNewSupplier($_POST);
} else {
    $inventoryUI->showItems();
}
