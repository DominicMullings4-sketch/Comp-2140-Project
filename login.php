<?php
// Sign-in page (SC-2). Builder: P1.
// This page only decides WHICH LoginUI method to call. LoginUI does the work.
require __DIR__ . '/src/bootstrap.php';

// What does the visitor want? It's the ?action= part of the address (login.php?action=code)
$action = $_GET['action'] ?? 'login';

// Did they submit a form (POST), or just open the page (GET)?
$formWasSent = ($_SERVER['REQUEST_METHOD'] == 'POST');

if ($action == 'code') {
    if ($formWasSent) {
        $loginUI->submitCode($_POST['code']);
    } else {
        $loginUI->showCodeForm();
    }
} elseif ($action == 'logout') {
    $loginUI->logout();
} else {
    if ($formWasSent) {
        $loginUI->submitCredentials($_POST['username'], $_POST['password']);
    } else {
        $loginUI->showLoginForm();
    }
}
