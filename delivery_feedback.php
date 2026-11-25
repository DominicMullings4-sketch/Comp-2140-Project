<?php
// Feature 6: Provide Delivery Feedback, staff side (review and filter).
// This page only decides WHICH DeliveryFeedbackUI method to call. DeliveryFeedbackUI does the work.
require __DIR__ . '/bootstrap.php';

$ui = new DeliveryFeedbackUI();
$ui->showFeedbackReview();
