<?php
// Feature 3: Storing Customer Information.
// This page only decides WHICH CustomerUI method to call. CustomerUI does the work.
require __DIR__ . '/bootstrap.php';

$ui = new CustomerUI();

if (is_post()) {
    $ui->submitCustomer($_POST);   // the form was submitted
} else {
    $ui->showCustomers();          // just opening the page
}
