-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2026 at 03:02 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `track_it`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `delivery_address` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `name`, `delivery_address`, `phone`, `email`) VALUES
(1, 'Terrell Kurt', '12 Hope Road, Kingston 6', '876-555-0101', 'terrell.k@example.com'),
(2, 'Tevin Hislop', '4 Main Street, Spanish Town', '876-555-0102', 'tevin.h@example.com'),
(3, 'Jahvillia Black', '27 Barnett Street, Montego Bay', '876-555-0103', 'jahvilla.b@example.com'),
(4, 'Dominic Mullings', '12 Hope Road, Kingston 6', '876-555-1004', 'dominic.m@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `delivery_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `worker_id` int(11) DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `status` enum('Pending','Out for delivery','Delivered','Failed') NOT NULL DEFAULT 'Pending',
  `comment` text DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `outcome_recorded_at` datetime DEFAULT NULL,
  `outcome_recorded_by` int(11) DEFAULT NULL,
  `handed_over_at` datetime DEFAULT NULL,
  `handed_over_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`delivery_id`, `order_id`, `worker_id`, `delivery_date`, `status`, `comment`, `assigned_at`, `outcome_recorded_at`, `outcome_recorded_by`) VALUES
(1, 1, 2, '2026-11-25', 'Failed', NULL, '2026-11-25 20:56:20', '2026-11-25 22:23:22', 1),
(2, 2, 2, '2026-11-25', 'Delivered', NULL, '2026-11-25 16:05:27', '2026-11-25 22:23:19', 1),
(3, 3, 3, '2026-11-25', 'Delivered', NULL, '2026-11-25 16:05:30', '2026-11-25 22:24:26', 1),
(4, 4, NULL, '2026-11-26', 'Pending', NULL, NULL, NULL, NULL),
(5, 5, NULL, '2026-11-26', 'Pending', NULL, NULL, NULL, NULL),
(6, 6, NULL, '2026-11-26', 'Pending', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `item_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `stock_quantity` int(11) NOT NULL DEFAULT 0 CHECK (`stock_quantity` >= 0),
  `reserved_quantity` int(11) NOT NULL DEFAULT 0 CHECK (`reserved_quantity` >= 0),
  `selling_price` decimal(10,2) NOT NULL CHECK (`selling_price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`item_id`, `name`, `stock_quantity`, `reserved_quantity`, `selling_price`) VALUES
(1, 'Phone case - Black', 18, 0, 1500.00),
(2, 'USB-C charger 20W', 12, 0, 3200.00),
(3, 'Wireless earbuds', 8, 0, 6500.00),
(4, 'Ring light 10 inch', 4, 0, 4800.00),
(5, 'Bluetooth speaker mini', 17, 0, 5200.00),
(6, 'Smart Bulb 12 W', 2, 0, 3500.00),
(7, '14\" Laptop', 8, 0, 75000.00),
(9, '14kWh Power Bank', 14, 0, 12500.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `created_at`) VALUES
(1, 4, '2026-11-25 02:55:38'),
(2, 4, '2026-11-25 08:08:08'),
(3, 2, '2026-11-25 08:09:28'),
(4, 3, '2026-11-25 22:25:35'),
(5, 1, '2026-11-25 22:32:49'),
(6, 2, '2026-11-25 22:33:07');

-- --------------------------------------------------------

--
-- Table structure for table `order_lines`
--

CREATE TABLE `order_lines` (
  `line_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_ordered` int(11) NOT NULL CHECK (`quantity_ordered` >= 1),
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_lines`
--

INSERT INTO `order_lines` (`line_id`, `order_id`, `item_id`, `quantity_ordered`, `unit_price`) VALUES
(1, 1, 6, 2, 3500.00),
(2, 1, 5, 1, 5200.00),
(3, 2, 4, 1, 4800.00),
(4, 2, 1, 2, 1500.00),
(5, 2, 5, 1, 5200.00),
(6, 3, 1, 3, 1500.00),
(7, 4, 1, 1, 1500.00),
(8, 5, 9, 1, 12500.00),
(9, 6, 1, 1, 1500.00);

-- --------------------------------------------------------

--
-- Table structure for table `stock_receipts`
--

CREATE TABLE `stock_receipts` (
  `receipt_id` int(11) NOT NULL,
  `supplier_name` varchar(100) NOT NULL,
  `date_received` date NOT NULL,
  `entered_at` datetime NOT NULL DEFAULT current_timestamp(),
  `entered_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_receipts`
--

INSERT INTO `stock_receipts` (`receipt_id`, `supplier_name`, `date_received`, `entered_at`, `entered_by`) VALUES
(1, 'SHAMANA Sourcing', '2026-11-25', '2026-11-25 02:07:46', 1);

-- --------------------------------------------------------

--
-- Table structure for table `stock_receipt_lines`
--

CREATE TABLE `stock_receipt_lines` (
  `line_id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_received` int(11) NOT NULL CHECK (`quantity_received` >= 1),
  `unit_cost_price` decimal(10,2) NOT NULL CHECK (`unit_cost_price` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_receipt_lines`
--

INSERT INTO `stock_receipt_lines` (`line_id`, `receipt_id`, `item_id`, `quantity_received`, `unit_cost_price`) VALUES
(3, 1, 7, 5, 65000.00),
(4, 1, 9, 10, 9500.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `two_factor_secret` varchar(100) DEFAULT NULL,
  `role` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `username`, `password_hash`, `two_factor_secret`, `role`) VALUES
(1, 'Jair Simms', 'staff1', '$2y$10$JHR7VQQg4A75ja/piExtHOozLPUuiInVxnI/tM/wR76wvYpeEgv3u', NULL, 'OfficeStaff'),
(2, 'Dante Thames', 'bearer1', '$2y$10$JHR7VQQg4A75ja/piExtHOozLPUuiInVxnI/tM/wR76wvYpeEgv3u', NULL, 'DeliveryWorker'),
(3, 'Tevin Hislop', 'bearer2', '$2y$10$JHR7VQQg4A75ja/piExtHOozLPUuiInVxnI/tM/wR76wvYpeEgv3u', NULL, 'DeliveryWorker');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `phone` (`phone`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`delivery_id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `worker_id` (`worker_id`),
  ADD KEY `outcome_recorded_by` (`outcome_recorded_by`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`item_id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_lines`
--
ALTER TABLE `order_lines`
  ADD PRIMARY KEY (`line_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `stock_receipts`
--
ALTER TABLE `stock_receipts`
  ADD PRIMARY KEY (`receipt_id`),
  ADD KEY `entered_by` (`entered_by`);

--
-- Indexes for table `stock_receipt_lines`
--
ALTER TABLE `stock_receipt_lines`
  ADD PRIMARY KEY (`line_id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `order_lines`
--
ALTER TABLE `order_lines`
  MODIFY `line_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `stock_receipts`
--
ALTER TABLE `stock_receipts`
  MODIFY `receipt_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_receipt_lines`
--
ALTER TABLE `stock_receipt_lines`
  MODIFY `line_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `deliveries_ibfk_2` FOREIGN KEY (`worker_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `deliveries_ibfk_3` FOREIGN KEY (`outcome_recorded_by`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `deliveries_ibfk_4` FOREIGN KEY (`handed_over_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`);

--
-- Constraints for table `order_lines`
--
ALTER TABLE `order_lines`
  ADD CONSTRAINT `order_lines_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_lines_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);

--
-- Constraints for table `stock_receipts`
--
ALTER TABLE `stock_receipts`
  ADD CONSTRAINT `stock_receipts_ibfk_1` FOREIGN KEY (`entered_by`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `stock_receipt_lines`
--
ALTER TABLE `stock_receipt_lines`
  ADD CONSTRAINT `stock_receipt_lines_ibfk_1` FOREIGN KEY (`receipt_id`) REFERENCES `stock_receipts` (`receipt_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_receipt_lines_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`item_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
