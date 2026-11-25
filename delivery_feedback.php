<?php
// Feature 6: Provide Delivery Feedback, staff side (review, filter, and record outcomes).
// This page only decides WHICH DeliveryFeedbackUI method to call. DeliveryFeedbackUI does the work.
require __DIR__ . '/bootstrap.php';

$ui = new DeliveryFeedbackUI();

if (is_post()) {
    $ui->submitStaffFeedback($_POST);   // staff recorded an outcome
} else {
    $ui->showFeedbackReview();
}
