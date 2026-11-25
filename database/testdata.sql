-- =============================================================
-- Test data for development. Import AFTER schema.sql.
-- DEVELOPMENT ONLY: never load this onto a database with real customer data.
--
-- Test accounts (all use the password  TrackIt-Demo-2026! ):
--   staff1   Staff
--   bearer1  Bearer
--   bearer2  Bearer
-- =============================================================

USE track_it;

INSERT INTO users (username, password_hash, role) VALUES
('staff1',  '$2y$10$JHR7VQQg4A75ja/piExtHOozLPUuiInVxnI/tM/wR76wvYpeEgv3u', 'Staff'),
('bearer1', '$2y$10$JHR7VQQg4A75ja/piExtHOozLPUuiInVxnI/tM/wR76wvYpeEgv3u', 'Bearer'),
('bearer2', '$2y$10$JHR7VQQg4A75ja/piExtHOozLPUuiInVxnI/tM/wR76wvYpeEgv3u', 'Bearer');

INSERT INTO products (name, quantity, selling_price) VALUES
('Phone case - Black',      25,  1500.00),
('USB-C charger 20W',       12,  3200.00),
('Wireless earbuds',         8,  6500.00),
('Ring light 10 inch',       5,  4800.00),
('Bluetooth speaker mini',  10,  5200.00);

INSERT INTO customers (name, delivery_address, phone, email) VALUES
('Kerry-Ann Brown', '12 Hope Road, Kingston 6',          '876-555-0101', 'kerryann.b@example.com'),
('Andre Campbell',  '4 Main Street, Spanish Town',       '876-555-0102', 'andre.c@example.com'),
('Shanique Reid',   '27 Barnett Street, Montego Bay',    '876-555-0103', 'shanique.r@example.com');
