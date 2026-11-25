<?php
// Signs the user out and returns to the sign-in page.
require __DIR__ . '/../bootstrap.php';

$auth = new Auth();
$auth->logout();
redirect('login.php');
