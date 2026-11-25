<?php
// Feature 6: Provide Delivery Feedback, bearer side (bearers' home page).
// This page only decides WHICH DeliveryFeedbackUI method to call. DeliveryFeedbackUI does the work.
require __DIR__ . '/bootstrap.php';

$ui = new DeliveryFeedbackUI();

if (is_post()) {
    $ui->submitFeedback($_POST);
} else {
    $ui->showMyDeliveries();
}
