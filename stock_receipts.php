<?php
// Feature 1: Add Stock Receipts.
// This page only decides WHICH StockReceiptUI method to call. StockReceiptUI does the work.
require __DIR__ . '/bootstrap.php';

$ui = new StockReceiptUI();

if (is_post()) {
    $ui->submitReceipt($_POST);
} else {
    $ui->showReceiptForm();
}
