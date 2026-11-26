<?php
// Feature 4: Confirm Delivery (staff: delivery list, assign a bearer, record the handover).
// This page only decides WHICH DeliveryUI method to call. DeliveryUI does the work.
require __DIR__ . '/bootstrap.php';

$ui = new DeliveryUI();

if (is_post()) {
    $ui->submitAction($_POST);
} else {
    $ui->showDeliveryList();
}
