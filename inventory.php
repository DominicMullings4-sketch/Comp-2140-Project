<?php
// Feature 2: Viewing and editing inventory.
// This page only decides WHICH InventoryUI method to call. InventoryUI does the work.
require __DIR__ . '/bootstrap.php';

$ui = new InventoryUI();

if (is_post()) {
    $ui->submitItem($_POST);
} else {
    $ui->showItems();
}
