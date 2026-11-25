<?php
// Copy this file to config/config.php and fill in your own values.
// config.php is ignored by Git, so passwords and keys never reach GitHub.

// Database (same settings as the PDO connection in INFO2180 Lab 5)
define('DB_HOST', 'localhost');      // For Azure: trackit-db.mysql.database.azure.com
define('DB_NAME', 'trackit');
define('DB_USER', 'root');           // XAMPP's default user. For Azure: trackitadmin
define('DB_PASSWORD', '');           // XAMPP's default is empty. For Azure: the admin password
define('DB_SSL_CA', '');             // Azure only: full path to the certificate file (see README)

// Team key for DataAccess::encrypt(). Everyone must use the SAME value (see README).
define('ENCRYPTION_KEY', '');

// Local time, used by SR-5.5's 10:00 AM rule.
define('TIMEZONE', 'America/Jamaica');

// Show PHP errors in the browser while building. Set to false for the demo.
define('SHOW_ERRORS', true);

// Signs you in automatically as this test user, so you can skip the login page while building.
// Use 'owner', 'staff', 'driver1' or 'driver2' to test each role.
// Set it to '' to use the real login page (and before the demo).
define('DEV_AUTO_LOGIN', 'owner');

// Two-factor sign-in with an authenticator app (SC-2).
// false = password only while building, true = password + 6-digit code (for the demo).
// If your config.php doesn't have this line, bootstrap.php treats it as false.
define('USE_TWO_FACTOR', false);
