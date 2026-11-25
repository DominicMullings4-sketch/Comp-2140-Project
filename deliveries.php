<?php
// Feature 5: Confirm Delivery (staff assign bearers).
// This page only decides WHICH DeliveryUI method to call. DeliveryUI does the work.
require __DIR__ . '/bootstrap.php';

$ui = new DeliveryUI();

if (is_post()) {
    $ui->submitAssignment($_POST);
} else {
    $ui->showDeliveries();
}
