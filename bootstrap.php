<?php
// Every page loads this file first:   require __DIR__ . '/bootstrap.php';
// It does four things, in this order:
//   1. loads your settings (config/config.php)
//   2. loads the helper functions
//   3. loads every class file, layer by layer (one file per class on the class diagram)
//   4. starts the session, which remembers who is signed in

// ---------------------------------------------------------------- 1. Settings
if (!file_exists(__DIR__ . '/config/config.php')) {
    echo 'Missing config/config.php. Copy config/config.example.php to config/config.php (see README.md).';
    exit;
}
require_once __DIR__ . '/config/config.php';

date_default_timezone_set(TIMEZONE);
if (SHOW_ERRORS) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

// ---------------------------------------------------------------- 2. Helpers
require_once __DIR__ . '/helpers.php';

// ---------------------------------------------------------------- 3. Class files
// Entity classes (the things the system stores)
require_once __DIR__ . '/logic/entities/User.php';
require_once __DIR__ . '/logic/entities/Product.php';
require_once __DIR__ . '/logic/entities/Customer.php';
require_once __DIR__ . '/logic/entities/StockReceipt.php';
require_once __DIR__ . '/logic/entities/StockReceiptLine.php';
require_once __DIR__ . '/logic/entities/Order.php';
require_once __DIR__ . '/logic/entities/OrderLine.php';

// Data Management layer
require_once __DIR__ . '/data/DataAccess.php';

// Application Logic layer
require_once __DIR__ . '/logic/UserService.php';
require_once __DIR__ . '/logic/StockReceiptService.php';
require_once __DIR__ . '/logic/InventoryService.php';
require_once __DIR__ . '/logic/CustomerService.php';
require_once __DIR__ . '/logic/OrderService.php';
require_once __DIR__ . '/logic/DeliveryService.php';
require_once __DIR__ . '/logic/DeliveryFeedbackService.php';

// Authentication & Authorization layer
require_once __DIR__ . '/auth/totp.php';
require_once __DIR__ . '/auth/Auth.php';
require_once __DIR__ . '/auth/AccessControl.php';

// User Interface layer (one UI class per feature; the pages in the main folder call their methods)
require_once __DIR__ . '/ui/LoginUI.php';
require_once __DIR__ . '/ui/StockReceiptUI.php';
require_once __DIR__ . '/ui/InventoryUI.php';
require_once __DIR__ . '/ui/CustomerUI.php';
require_once __DIR__ . '/ui/OrderUI.php';
require_once __DIR__ . '/ui/DeliveryUI.php';
require_once __DIR__ . '/ui/DeliveryFeedbackUI.php';

// ---------------------------------------------------------------- 4. Session
session_start();
