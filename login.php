<?php
// Sign-in page. This page only decides WHICH LoginUI method to call. LoginUI does the work.
require __DIR__ . '/bootstrap.php';

$ui = new LoginUI();
$action = $_GET['action'] ?? 'login';   // the ?action= part of the address

if ($action === 'code') {
    if (is_post()) {
        $ui->submitCode($_POST['code'] ?? '');
    } else {
        $ui->showCodeForm();
    }
} elseif ($action === 'logout') {
    $ui->logout();
} else {
    if (is_post()) {
        $ui->submitCredentials($_POST['username'] ?? '', $_POST['password'] ?? '');
    } else {
        $ui->showLoginForm();
    }
}
