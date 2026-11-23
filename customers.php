<?php
// Feature 2: Manage Customers. Builder: P3.
// This page only decides WHICH CustomerUI method to call. CustomerUI does the work.
require __DIR__ . '/src/bootstrap.php';

$action = $_GET['action'] ?? 'search';
$customerId = $_GET['id'] ?? '';

if ($action == 'view') {
    $customerUI->showCustomer($customerId);
} elseif ($action == 'new') {
    $customerUI->submitNewCustomer($_POST);
} elseif ($action == 'edit') {
    $customerUI->submitCustomerEdit($customerId, $_POST);
} elseif ($action == 'anonymize') {
    $customerUI->submitAnonymize($customerId);
} else {
    $customerUI->showSearch($_GET['q'] ?? '');
}
