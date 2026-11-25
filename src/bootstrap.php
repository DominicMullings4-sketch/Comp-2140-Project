<?php
// Every page loads this file first. It does four things, in this order:
//   1. loads your settings (config/config.php)
//   2. loads every class file (one file per class on the class diagram)
//   3. starts the session, which remembers who is signed in
//   4. creates one object of each class, connected like the arrows on the diagrams

// ---------------------------------------------------------------- 1. Settings
if (!file_exists(__DIR__ . '/../config/config.php')) {
    echo 'Missing config/config.php. Copy config/config.example.php to config/config.php (see README.md).';
    exit;
}
require __DIR__ . '/../config/config.php';

// Settings added after some people made their config.php. If yours doesn't have one,
// it gets the default here, so the page still runs.
if (!defined('USE_TWO_FACTOR')) {
    define('USE_TWO_FACTOR', false);   // false = password only, true = password + authenticator code
}

date_default_timezone_set(TIMEZONE);
if (SHOW_ERRORS) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}

require __DIR__ . '/helpers.php';

// ---------------------------------------------------------------- 2. Class files
// Enumerations (the fixed lists of values on the EntityClasses diagram)
require __DIR__ . '/entities/DeliveryStatus.php';
require __DIR__ . '/entities/InvoiceStatus.php';
require __DIR__ . '/entities/OrderSource.php';
require __DIR__ . '/entities/OrderStatus.php';
require __DIR__ . '/entities/ReportType.php';
require __DIR__ . '/entities/UserRole.php';
require __DIR__ . '/entities/UserStatus.php';

// Entity classes
require __DIR__ . '/entities/Customer.php';
require __DIR__ . '/entities/Delivery.php';
require __DIR__ . '/entities/Invoice.php';
require __DIR__ . '/entities/Item.php';
require __DIR__ . '/entities/Order.php';
require __DIR__ . '/entities/OrderLine.php';
require __DIR__ . '/entities/Report.php';
require __DIR__ . '/entities/ReportRow.php';
require __DIR__ . '/entities/StockReceipt.php';
require __DIR__ . '/entities/StockReceiptLine.php';
require __DIR__ . '/entities/Supplier.php';
require __DIR__ . '/entities/User.php';

// Data Access layer
require __DIR__ . '/data/DataAccess.php';

// Application Logic layer
require __DIR__ . '/logic/CustomerMgmt.php';
require __DIR__ . '/logic/DeliveryMgmt.php';
require __DIR__ . '/logic/InventoryMgmt.php';
require __DIR__ . '/logic/OrderMgmt.php';
require __DIR__ . '/logic/ReportingMgmt.php';
require __DIR__ . '/logic/UserMgmt.php';

// Authentication & Authorization layer
require __DIR__ . '/auth/totp.php';   // helper functions for the 6-digit codes (not a class, so not on the diagram)
require __DIR__ . '/auth/LoginValidation.php';
require __DIR__ . '/auth/TwoFactorAuth.php';
require __DIR__ . '/auth/UserPermission.php';

// User Interface layer
require __DIR__ . '/ui/CustomerUI.php';
require __DIR__ . '/ui/DeliveryUI.php';
require __DIR__ . '/ui/InventoryUI.php';
require __DIR__ . '/ui/LoginUI.php';
require __DIR__ . '/ui/OrderUI.php';
require __DIR__ . '/ui/ReportingUI.php';
require __DIR__ . '/ui/UserUI.php';

// ---------------------------------------------------------------- 3. Session
// Started after the class files, so PHP knows what a User is when it reads the session.
session_start();

// ---------------------------------------------------------------- 4. Objects
// Each "new" below passes in the classes that class uses: the same as the
// dependency arrows on the WorkerClasses and UIAuthClasses diagrams.

// Data Access layer
$dataAccess = new DataAccess();

// Application Logic layer
$userMgmt      = new UserMgmt($dataAccess);
$inventoryMgmt = new InventoryMgmt($dataAccess);
$customerMgmt  = new CustomerMgmt($dataAccess);
$orderMgmt     = new OrderMgmt($dataAccess, $inventoryMgmt);
$deliveryMgmt  = new DeliveryMgmt($dataAccess);
$reportingMgmt = new ReportingMgmt($dataAccess);

// Authentication & Authorization layer
$userPermission  = new UserPermission();
$loginValidation = new LoginValidation($userMgmt);
$twoFactorAuth   = new TwoFactorAuth($userMgmt);

// User Interface layer
$loginUI     = new LoginUI($userPermission, $loginValidation, $twoFactorAuth);
$userUI      = new UserUI($userPermission, $loginValidation, $userMgmt);
$inventoryUI = new InventoryUI($userPermission, $inventoryMgmt);
$customerUI  = new CustomerUI($userPermission, $customerMgmt);
$orderUI     = new OrderUI($userPermission, $orderMgmt, $customerMgmt, $inventoryMgmt);
$deliveryUI  = new DeliveryUI($userPermission, $deliveryMgmt);
$reportingUI = new ReportingUI($userPermission, $reportingMgmt);

// ---------------------------------------------------------------- Development shortcut
// Until the login page is built, sign in automatically as the DEV_AUTO_LOGIN user.
if (DEV_AUTO_LOGIN != '' && $userPermission->getCurrentUser() == null) {
    $rows = $dataAccess->select('SELECT * FROM users WHERE username = :username', ['username' => DEV_AUTO_LOGIN]);
    if (count($rows) > 0) {
        $userPermission->startSession(User::fromRow($rows[0]));
    }
}
