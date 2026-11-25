<?php
// Copy this file to config/config.php and change the values if yours differ.
// config.php is ignored by Git, so your own settings never get pushed.

// Database. These values already match a default XAMPP install.
define('DB_HOST', 'localhost');
define('DB_NAME', 'track_it');
define('DB_USER', 'root');
define('DB_PASSWORD', '');

// Local time zone. Feature 5 uses it for the 10:00 AM delivery-date rule.
define('TIMEZONE', 'America/Jamaica');

// Show PHP errors in the browser while building. Set to false for the demo.
define('SHOW_ERRORS', true);

// Two-factor sign-in with an authenticator app.
// false while building (password only), true for the demo.
define('USE_TWO_FACTOR', false);
