-- Import once into a disposable demo database AFTER schema.sql and seed.sql.
-- All inserts roll back together on failure when run with the mysql CLI.
START TRANSACTION;
INSERT INTO customers (customer_id, name, phone, address) VALUES
('DEMO7-C1','Alicia Reid','8765550104','8 Hope Road, Kingston'),
('DEMO7-C2','Kemar Brown','8765550105','14 Main Street, Ocho Rios');
INSERT INTO orders (order_id, customer_id, entered_at, order_status) VALUES
('DEMO7-A','DEMO7-C1','2026-05-11 18:00:00','PendingConfirmation'),
('DEMO7-B','DEMO7-C2','2026-05-11 18:15:00','PendingConfirmation'),
('DEMO7-C','DEMO7-C1','2026-05-11 19:00:00','PendingConfirmation');
INSERT INTO order_lines (order_id,item_id,quantity_ordered,unit_price) VALUES
('DEMO7-A','I-0001',2,100.50),('DEMO7-A','I-0002',1,5.25),
('DEMO7-B','I-0002',3,5.25),('DEMO7-C','I-0001',1,100.50);
SET time_zone = '-05:00';
SET timestamp = UNIX_TIMESTAMP('2026-05-12 09:30:00');
UPDATE orders SET order_status = 'OrderConfirmed' WHERE order_id = 'DEMO7-A';
SET timestamp = UNIX_TIMESTAMP('2026-05-12 10:00:00');
UPDATE orders SET order_status = 'OrderConfirmed' WHERE order_id = 'DEMO7-B';
SET timestamp = UNIX_TIMESTAMP('2026-05-12 10:01:00');
UPDATE orders SET order_status = 'OrderConfirmed' WHERE order_id = 'DEMO7-C';
SET timestamp = DEFAULT;
COMMIT;
