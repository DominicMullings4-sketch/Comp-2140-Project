<?php
// Manage Users (owner only). Builder: P6.
// This page only decides WHICH UserUI method to call. UserUI does the work.
require __DIR__ . '/src/bootstrap.php';

$action = $_GET['action'] ?? 'list';

if ($action == 'new') {
    $userUI->submitNewUser($_POST);
} elseif ($action == 'role') {
    $userUI->submitRoleChange($_POST['user'], UserRole::from($_POST['role']));
} elseif ($action == 'deactivate') {
    $userUI->submitDeactivate($_POST['user']);
} elseif ($action == 'reactivate') {
    $userUI->submitReactivate($_POST['user']);
} else {
    $userUI->showUsers();
}
