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

// Until the login page is built, you are signed in automatically as this test user.
// Use 'owner', 'staff', 'driver1' or 'driver2' to test each role. Set to '' for the demo.
define('DEV_AUTO_LOGIN', 'owner');
