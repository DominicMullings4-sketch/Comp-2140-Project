-- =============================================================
-- Track-It database schema (simplified scope, 6 features)
-- phpMyAdmin -> Import -> choose this file -> Import.
-- Running it again DELETES all Track-It data and starts fresh.
-- =============================================================

DROP DATABASE IF EXISTS track_it;
CREATE DATABASE track_it CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE track_it;

-- -------------------------------------------------------------
-- users  (pre-loaded accounts; there is no add-user feature)
-- -------------------------------------------------------------
CREATE TABLE users (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    username           VARCHAR(50)  NOT NULL UNIQUE,
    password_hash      VARCHAR(255) NOT NULL,           -- from PHP password_hash(), never the plain password
    two_factor_secret  VARCHAR(64)  NULL,               -- set the first time the user signs in with 2FA on
    role               ENUM('Staff', 'Bearer') NOT NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- products  (Feature 2: view / edit / add inventory items)
-- -------------------------------------------------------------
CREATE TABLE products (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    name           VARCHAR(100) NOT NULL UNIQUE,         -- unique so the dropdowns have no duplicates
    quantity       INT NOT NULL DEFAULT 0 CHECK (quantity >= 0),
    selling_price  DECIMAL(10,2) NOT NULL CHECK (selling_price >= 0)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- stock_receipts  (Feature 1: receipt header)
-- id = the unique identifier required by 1.5
-- -------------------------------------------------------------
CREATE TABLE stock_receipts (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name  VARCHAR(100) NOT NULL,
    date_received  DATE NOT NULL,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- stock_receipt_lines  (the products received on each receipt)
-- -------------------------------------------------------------
CREATE TABLE stock_receipt_lines (
    id                 INT AUTO_INCREMENT PRIMARY KEY,
    receipt_id         INT NOT NULL,
    product_id         INT NOT NULL,                     -- chosen from the product dropdown
    quantity_received  INT NOT NULL CHECK (quantity_received >= 1),   -- 1.3
    FOREIGN KEY (receipt_id) REFERENCES stock_receipts(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- customers  (Feature 3)
-- UNIQUE on phone and email backs up 3.2 at the database level
-- -------------------------------------------------------------
CREATE TABLE customers (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    name              VARCHAR(100) NOT NULL,
    delivery_address  VARCHAR(255) NOT NULL,
    phone             VARCHAR(20)  NOT NULL UNIQUE,
    email             VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- orders  (Feature 4, plus the delivery columns for Features 5 and 6)
-- One delivery per order, so the delivery data lives here.
-- -------------------------------------------------------------
CREATE TABLE orders (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    customer_id       INT NOT NULL,                      -- 4.6 order linked to customer
    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,   -- used for the 10:00 AM rule
    delivery_date     DATE NOT NULL,                     -- 5.1, 5.3, 5.4
    status            ENUM('Pending', 'Out for delivery', 'Delivered', 'Failed')
                      NOT NULL DEFAULT 'Pending',        -- 5.2
    bearer_id         INT NULL,                          -- 5.5 (empty until a bearer is assigned)
    delivery_comment  TEXT NULL,                         -- 6.3
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (bearer_id)   REFERENCES users(id)
) ENGINE=InnoDB;

-- -------------------------------------------------------------
-- order_lines  (the products inside each order)
-- unit_price is copied from products when the order is saved,
-- so old invoices don't change when a price is edited (2.2).
-- -------------------------------------------------------------
CREATE TABLE order_lines (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    order_id    INT NOT NULL,
    product_id  INT NOT NULL,
    quantity    INT NOT NULL CHECK (quantity >= 1),        -- 4.2 (the stock check is done in PHP)
    unit_price  DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id)   REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
) ENGINE=InnoDB;

-- The invoice (4.4) and grand total (4.3) are not stored:
-- they are built from orders + order_lines + customers when needed.
