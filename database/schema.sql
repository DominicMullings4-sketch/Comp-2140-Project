-- Track-It database tables, one per entity class on the EntityClasses diagram.
-- Works on XAMPP's MySQL/MariaDB and on the Azure MySQL 8.4 server.
-- Import in phpMyAdmin (Import tab), or run: mysql -u root < database/schema.sql
--
-- Track-It never deletes records (SF-3), so foreign keys block deletes by default.

CREATE DATABASE IF NOT EXISTS trackit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE trackit;

-- User (SC-1, SC-2, SC-3)
CREATE TABLE users (
    user_id           VARCHAR(20)  NOT NULL PRIMARY KEY,
    name              VARCHAR(100) NOT NULL,
    username          VARCHAR(50)  NOT NULL UNIQUE,
    email             VARCHAR(255) NOT NULL,
    password_hash     VARCHAR(255) NOT NULL,            -- never the plain password (SC-3)
    two_factor_secret VARCHAR(255) NULL,
    role              VARCHAR(20)  NOT NULL,            -- UserRole: BusinessOwner | OfficeStaff | DeliveryWorker
    status            VARCHAR(20)  NOT NULL DEFAULT 'Active'   -- UserStatus: Active | Inactive
) ENGINE=InnoDB;

-- Customer (Feature 2)
CREATE TABLE customers (
    customer_id         VARCHAR(20)  NOT NULL PRIMARY KEY,
    name                VARCHAR(100) NOT NULL,
    phone               VARCHAR(10)  NOT NULL,          -- exactly 10 digits (SR-2.4)
    address             VARCHAR(255) NOT NULL,
    social_media_handle VARCHAR(100) NULL,
    INDEX idx_customers_name (name),
    INDEX idx_customers_phone (phone)
) ENGINE=InnoDB;

-- Supplier (SR-1.8, SR-1.9)
CREATE TABLE suppliers (
    supplier_id   VARCHAR(20)  NOT NULL PRIMARY KEY,
    name          VARCHAR(100) NOT NULL UNIQUE,
    city          VARCHAR(100) NOT NULL,
    country       VARCHAR(100) NOT NULL,
    contact_name  VARCHAR(100) NULL,
    contact_phone VARCHAR(30)  NULL
) ENGINE=InnoDB;

-- Item (SR-1.4, SR-1.5, SR-3.6)
CREATE TABLE items (
    item_id           VARCHAR(20)   NOT NULL PRIMARY KEY,
    name              VARCHAR(100)  NOT NULL UNIQUE,
    selling_price     DECIMAL(10,2) NOT NULL,
    stock_quantity    INT           NOT NULL DEFAULT 0,
    reserved_quantity INT           NOT NULL DEFAULT 0,
    CONSTRAINT chk_items_price    CHECK (selling_price >= 0),
    CONSTRAINT chk_items_stock    CHECK (stock_quantity >= 0),
    CONSTRAINT chk_items_reserved CHECK (reserved_quantity >= 0)
) ENGINE=InnoDB;

-- StockReceipt (SR-1.1, SR-1.6, SR-1.7)
CREATE TABLE stock_receipts (
    receipt_id    VARCHAR(20) NOT NULL PRIMARY KEY,
    supplier_id   VARCHAR(20) NOT NULL,
    date_received DATE        NOT NULL,
    entered_at    DATETIME    NOT NULL,
    entered_by    VARCHAR(20) NOT NULL,
    FOREIGN KEY (supplier_id) REFERENCES suppliers (supplier_id),
    FOREIGN KEY (entered_by)  REFERENCES users (user_id)
) ENGINE=InnoDB;

-- StockReceiptLine (composition: belongs to one stock receipt)
CREATE TABLE stock_receipt_lines (
    line_id           INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
    receipt_id        VARCHAR(20)   NOT NULL,
    item_id           VARCHAR(20)   NOT NULL,
    quantity_received INT           NOT NULL,
    unit_cost_price   DECIMAL(10,2) NOT NULL,
    CONSTRAINT chk_srl_quantity CHECK (quantity_received > 0),   -- SR-1.3
    CONSTRAINT chk_srl_cost     CHECK (unit_cost_price >= 0),    -- SR-1.10
    FOREIGN KEY (receipt_id) REFERENCES stock_receipts (receipt_id),
    FOREIGN KEY (item_id)    REFERENCES items (item_id)
) ENGINE=InnoDB;

-- Order (Feature 3)
CREATE TABLE orders (
    order_id     VARCHAR(20) NOT NULL PRIMARY KEY,
    customer_id  VARCHAR(20) NOT NULL,
    order_source VARCHAR(20) NULL,                     -- OrderSource, optional (SR-3.1)
    entered_at   DATETIME    NOT NULL,
    order_status VARCHAR(30) NOT NULL,                 -- OrderStatus
    FOREIGN KEY (customer_id) REFERENCES customers (customer_id)
) ENGINE=InnoDB;

-- OrderLine (composition: belongs to one order). unit_price is frozen when ordered (SR-4.5).
CREATE TABLE order_lines (
    line_id          INT           NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id         VARCHAR(20)   NOT NULL,
    item_id          VARCHAR(20)   NOT NULL,
    quantity_ordered INT           NOT NULL,
    unit_price       DECIMAL(10,2) NOT NULL,
    CONSTRAINT chk_ol_quantity CHECK (quantity_ordered > 0),     -- SR-3.3
    FOREIGN KEY (order_id) REFERENCES orders (order_id),
    FOREIGN KEY (item_id)  REFERENCES items (item_id)
) ENGINE=InnoDB;

-- Invoice (Feature 4): at most one per order (SR-4.1)
CREATE TABLE invoices (
    invoice_number VARCHAR(30) NOT NULL PRIMARY KEY,
    order_id       VARCHAR(20) NOT NULL UNIQUE,
    status         VARCHAR(10) NOT NULL DEFAULT 'Active',   -- InvoiceStatus: Active | Void
    FOREIGN KEY (order_id) REFERENCES orders (order_id)
) ENGINE=InnoDB;

-- Delivery (Features 5 and 6). Every assignment is kept, including failed ones (SR-5.7).
CREATE TABLE deliveries (
    delivery_id         VARCHAR(20) NOT NULL PRIMARY KEY,
    order_id            VARCHAR(20) NOT NULL,
    worker_id           VARCHAR(20) NOT NULL,
    delivery_date       DATE        NOT NULL,
    status              VARCHAR(20) NOT NULL,          -- DeliveryStatus
    outcome_recorded_at DATETIME    NULL,              -- SR-6.6
    outcome_recorded_by VARCHAR(20) NULL,              -- SR-6.6
    assigned_at         DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,   -- order of assignments; database only
    FOREIGN KEY (order_id)            REFERENCES orders (order_id),
    FOREIGN KEY (worker_id)           REFERENCES users (user_id),
    FOREIGN KEY (outcome_recorded_by) REFERENCES users (user_id)
) ENGINE=InnoDB;

-- Settings such as the low-stock threshold (SR-7.5)
CREATE TABLE settings (
    setting_key   VARCHAR(50)  NOT NULL PRIMARY KEY,
    setting_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

INSERT INTO settings (setting_key, setting_value) VALUES ('low_stock_threshold', '5');

-- Store Products (SRS Feature 5, SR-5.1 to SR-5.5). Never deleted (SF-3): flagged instead.
CREATE TABLE stock_items (
    stock_item_id      VARCHAR(20)   NOT NULL PRIMARY KEY,
    item_name          VARCHAR(100)  NOT NULL,
    description        VARCHAR(255)  NULL,
    quantity           INT           NOT NULL,
    rate               DECIMAL(10,2) NOT NULL,
    total_value        DECIMAL(12,2) AS (quantity * rate) STORED,
    taken_for_delivery TINYINT(1)    NOT NULL DEFAULT 0,
    logged_at          DATETIME      NOT NULL,
    logged_by          VARCHAR(20)   NOT NULL,
    is_flagged         TINYINT(1)    NOT NULL DEFAULT 0,
    flagged_at         DATETIME      NULL,
    flagged_by         VARCHAR(20)   NULL,
    CONSTRAINT chk_si_quantity CHECK (quantity > 0),
    CONSTRAINT chk_si_rate     CHECK (rate >= 0),
    FOREIGN KEY (logged_by)  REFERENCES users (user_id),
    FOREIGN KEY (flagged_by) REFERENCES users (user_id)
) ENGINE=InnoDB;