-- Run once on an existing Track-It database. Fresh installations use schema.sql.
USE trackit;

ALTER TABLE orders
    ADD confirmed_at DATETIME NULL,
    ADD dispatch_date DATE GENERATED ALWAYS AS
        (DATE(confirmed_at) + INTERVAL (TIME(confirmed_at) > '10:00:00') DAY) STORED,
    ADD bearer_id VARCHAR(20) NULL,
    ADD handed_over_at DATETIME NULL,
    ADD FOREIGN KEY (bearer_id) REFERENCES users(user_id),
    ADD INDEX idx_dispatch_date (dispatch_date, order_status);

-- Legacy rows have no confirmation timestamp. Restore their actual local
-- confirmation times from business records to include them on a dated list.
-- Jamaica observes UTC-05:00 year-round, independent of the DB server zone.
CREATE TRIGGER orders_dispatch_insert BEFORE INSERT ON orders FOR EACH ROW
SET NEW.confirmed_at = IF(NEW.order_status = 'OrderConfirmed',
    COALESCE(NEW.confirmed_at, UTC_TIMESTAMP() - INTERVAL 5 HOUR), NULL);

CREATE TRIGGER orders_dispatch_update BEFORE UPDATE ON orders FOR EACH ROW
SET NEW.confirmed_at = CASE
    WHEN OLD.confirmed_at IS NOT NULL THEN OLD.confirmed_at
    WHEN NEW.order_status = 'OrderConfirmed' THEN COALESCE(NEW.confirmed_at,
        IF(OLD.order_status <> 'OrderConfirmed', UTC_TIMESTAMP() - INTERVAL 5 HOUR, NULL))
    ELSE NULL END;
