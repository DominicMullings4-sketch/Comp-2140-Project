-- Test data for development, so each feature can be built without waiting for the others.
-- Import AFTER schema.sql. Every test account's password is in README.md.
-- These are development-only accounts: don't load this file on a server with real data.

USE trackit;

INSERT INTO users (user_id, name, username, email, password_hash, role, status) VALUES
('U-OWNER',  'Business Owner (test)'  , 'owner',   'owner@example.com',   '$2y$10$iAT2e6AB5HaHdkRqbk4IXuY9oRSyKQstBuzmN5WyoMMFK69T2suJi', 'BusinessOwner',  'Active'),
('U-STAFF',  'Office Staff (test)',     'staff',   'staff@example.com',   '$2y$10$iAT2e6AB5HaHdkRqbk4IXuY9oRSyKQstBuzmN5WyoMMFK69T2suJi', 'OfficeStaff',    'Active'),
('U-DRIVER1','Delivery Worker 1 (test)','driver1', 'driver1@example.com', '$2y$10$iAT2e6AB5HaHdkRqbk4IXuY9oRSyKQstBuzmN5WyoMMFK69T2suJi', 'DeliveryWorker', 'Active'),
('U-DRIVER2','Delivery Worker 2 (test)','driver2', 'driver2@example.com', '$2y$10$iAT2e6AB5HaHdkRqbk4IXuY9oRSyKQstBuzmN5WyoMMFK69T2suJi', 'DeliveryWorker', 'Active');

INSERT INTO suppliers (supplier_id, name, city, country, contact_name, contact_phone) VALUES
('S-0001', 'Island Wholesale Ltd', 'Kingston', 'Jamaica', 'Marcia Brown', '8765550110'),
('S-0002', 'Coastline Imports',    'Miami',    'USA',     NULL,           NULL);

INSERT INTO items (item_id, name, selling_price, stock_quantity, reserved_quantity) VALUES
('I-0001', 'Wireless Earbuds',     4500.00, 20, 0),
('I-0002', 'Phone Case',            1200.00, 35, 0),
('I-0003', 'LED Ring Light',        6800.00,  4, 0),
('I-0004', 'Satin Hair Bonnet',      900.00, 50, 0),
('I-0005', 'Insulated Water Bottle', 2300.00,  3, 0);

INSERT INTO customers (customer_id, name, phone, address, social_media_handle) VALUES
('C-0001', 'Tanya Campbell', '8765550101', '12 Hope Road, Kingston 6',         '@tanyac'),
('C-0002', 'Andre Williams', '8765550102', '45 Main Street, Ocho Rios',        NULL),
('C-0003', 'Kerry-Ann Reid', '8765550103', '7 Barnett Street, Montego Bay',    '@kerryannr');
