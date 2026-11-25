<?php
// Feature 1: Add Stock Receipts.
// This page only decides WHICH StockReceiptUI method to call. StockReceiptUI does the work.
require __DIR__ . '/bootstrap.php';

$ui = new StockReceiptUI();
$action = $_GET['action'] ?? 'new';   // the ?action= part of the address

if ($action === 'view') {
    $ui->showReceipt((int) ($_GET['id'] ?? 0));   // stock_receipts.php?action=view&id=3
} elseif (is_post()) {
    $ui->submitReceipt($_POST);
} else {
    $ui->showReceiptForm();
}
