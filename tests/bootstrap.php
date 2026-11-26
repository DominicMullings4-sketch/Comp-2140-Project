<?php
// Test settings. Uses a separate database so tests never touch the team's data.
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'trackit_test');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_SSL_CA', '');
define('ENCRYPTION_KEY', base64_encode(str_repeat('k', 32)));
date_default_timezone_set('America/Jamaica');

require __DIR__ . '/../src/helpers.php';
require __DIR__ . '/../src/entities/UserRole.php';
require __DIR__ . '/../src/entities/StockItem.php';
require __DIR__ . '/../src/data/DataAccess.php';
require __DIR__ . '/../src/logic/InventoryMgmt.php';