<?php
// The front door: http://localhost/Comp-2140-Project/
// Not signed in -> the sign-in page. Signed in -> the home page for your role.
require __DIR__ . '/bootstrap.php';

if (!AccessControl::isLoggedIn()) {
    redirect('ui/login.php');
}
redirect('ui/' . AccessControl::homePageFor(AccessControl::currentRole()));
