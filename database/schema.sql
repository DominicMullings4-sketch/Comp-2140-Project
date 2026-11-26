-- =============================================================
-- Track-It database schema (simplified scope, 6 features)
-- phpMyAdmin -> Import -> choose this file -> Import.
-- Running it again DELETES all Track-It data and starts fresh.
--
-- Table and column names follow the class diagram:
-- items (not products), and every table's key is named <table>_id
-- (user_id, item_id, customer_id, order_id, receipt_id, line_id, delivery_id).
-- =============================================================

DROP DATABASE IF EXISTS track_it;
CREATE DATABASE track_it CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE track_it;

-- -------------------------------------------------------------
-- users  (pre-loaded accounts; there is no add-user feature)
-- role is 'OfficeStaff' or 'DeliveryWorker' (User::ROLE_STAFF / User::ROLE_BEARER in PHP)
-- -------------------------------------------------------------
CREATE TABLE users (
    user_id            INT AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(100) NOT NULL,
    username           VARCHAR(50)  NOT NULL UNIQUE,
    password_hash      VARCHAR(255) NOT NULL,           -- from PHP password_hash(), never the plain password
    two_factor_secret  VARCHAR(100) NULL,               -- set the first time the user signs in with 2FA on
    role               VARCHAR(20)  NOT NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- items  (Feature 2: view / edit / add inventory items)
-- -------------------------------------------------------------
CREATE TABLE items (
    item_id            INT AUTO_INCREMENT PRIMARY KEY,
    name               VARCHAR(100) NOT NULL UNIQUE,         -- unique so the dropdowns have no duplicates
    stock_quantity     INT NOT NULL DEFAULT 0 CHECK (stock_quantity >= 0),
    reserved_quantity  INT NOT NULL DEFAULT 0 CHECK (reserved_quantity >= 0),
    selling_price      DECIMAL(10,2) NOT NULL CHECK (selling_price >= 0)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- stock_receipts  (Feature 1: receipt header)
-- receipt_id = the unique identifier required by 1.5
-- -------------------------------------------------------------
CREATE TABLE stock_receipts (
    receipt_id     INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name  VARCHAR(100) NOT NULL,
    date_received  DATE NOT NULL,
    entered_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    entered_by     INT NOT NULL,                         -- the signed-in user who recorded it
    FOREIGN KEY (entered_by) REFERENCES users(user_id)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- stock_receipt_lines  (the items received on each receipt)
-- -------------------------------------------------------------
CREATE TABLE stock_receipt_lines (
    line_id            INT AUTO_INCREMENT PRIMARY KEY,
    receipt_id         INT NOT NULL,
    item_id            INT NOT NULL,                     -- chosen from the item dropdown
    quantity_received  INT NOT NULL CHECK (quantity_received >= 1),        -- 1.3
    unit_cost_price    DECIMAL(10,2) NOT NULL CHECK (unit_cost_price >= 0), -- what the supplier charged per unit
    FOREIGN KEY (receipt_id) REFERENCES stock_receipts(receipt_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id)    REFERENCES items(item_id)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- customers  (Feature 3)
-- UNIQUE on phone and email backs up 3.2 at the database level
-- -------------------------------------------------------------
CREATE TABLE customers (
    customer_id       INT AUTO_INCREMENT PRIMARY KEY,
    name              VARCHAR(100) NOT NULL,
    delivery_address  VARCHAR(255) NOT NULL,
    phone             VARCHAR(20)  NOT NULL UNIQUE,
    email             VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- orders  (Feature 4)
-- The delivery details are in the deliveries table below.
-- -------------------------------------------------------------
CREATE TABLE orders (
    order_id     INT AUTO_INCREMENT PRIMARY KEY,
    customer_id  INT NOT NULL,                                     -- 4.6 order linked to customer
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,      -- used for the 10:00 AM rule
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- order_lines  (the items inside each order)
-- unit_price is copied from items when the order is saved,
-- so old invoices don't change when a price is edited (2.2).
-- -------------------------------------------------------------
CREATE TABLE order_lines (
    line_id           INT AUTO_INCREMENT PRIMARY KEY,
    order_id          INT NOT NULL,
    item_id           INT NOT NULL,
    quantity_ordered  INT NOT NULL CHECK (quantity_ordered >= 1),    -- 4.2 (the stock check is done in PHP)
    unit_price        DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (item_id)  REFERENCES items(item_id)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- deliveries  (Features 4 and 6)
-- One delivery per order (UNIQUE order_id). Created when the order is saved.
-- -------------------------------------------------------------
CREATE TABLE deliveries (
    delivery_id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id             INT NOT NULL UNIQUE,
    worker_id            INT NULL,                       -- 5.5 (empty until a delivery worker is assigned)
    delivery_date        DATE NOT NULL,                  -- 5.1, 5.3, 5.4
    status               ENUM('Pending', 'Out for delivery', 'Delivered', 'Failed')
                         NOT NULL DEFAULT 'Pending',     -- 5.2
    comment              TEXT NULL,                      -- 6.3
    assigned_at          DATETIME NULL,                  -- when the worker was assigned (5.5)
    outcome_recorded_at  DATETIME NULL,                  -- when Delivered / Failed was saved (6.2)
    outcome_recorded_by  INT NULL,                       -- who saved it
    handed_over_at       DATETIME NULL,                  -- Feature 4 (AC-4.3): when the order was handed to the bearer
    handed_over_by       INT NULL,                       -- Feature 4 (AC-4.3): the staff member who recorded the handover
    FOREIGN KEY (order_id)            REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (worker_id)           REFERENCES users(user_id),
    FOREIGN KEY (outcome_recorded_by) REFERENCES users(user_id),
    FOREIGN KEY (handed_over_by)      REFERENCES users(user_id)
) ENGINE=InnoDB;

-- The invoice (4.4) and grand total (4.3) are not stored:
-- they are built from orders + order_lines + customers when needed.
