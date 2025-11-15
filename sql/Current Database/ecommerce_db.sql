-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2025 at 07:13 PM
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
-- Database: `ecommerce_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`, `description`, `parent_id`) VALUES
(1, 'Home & Living', 'Home & Living', NULL),
(2, 'Fashion & Apparel', 'Fashion & Apparel', NULL),
(3, 'Electronics & Gadgets', 'Electronics & Gadgets', NULL),
(4, 'Appliances', 'Appliances', NULL),
(5, 'Kitchen & Dining', 'Kitchen & Dining', NULL),
(6, 'Beauty & Personal Care', 'Beauty & Personal Care', NULL),
(7, 'Baby, Kids & Toys', 'Baby, Kids & Toys', NULL),
(8, 'Tools, DIY & Hardware', 'Tools, DIY & Hardware', NULL),
(9, 'Sports & Outdoors', 'Sports & Outdoors', NULL),
(10, 'Pet Supplies', 'Pet Supplies', NULL),
(11, 'Food & Beverages', 'Food & Beverages', NULL),
(12, 'Automotive', 'Automotive', NULL),
(13, 'Computers & Accessories', 'Computers & Accessories', NULL),
(14, 'Gaming', 'Gaming', NULL),
(15, 'Books & Stationery', 'Books & Stationery', NULL),
(16, 'Furniture', 'Furniture', 1),
(17, 'Bedding & Pillows', 'Bedding & Pillows', 1),
(18, 'Home Decor', 'Home Decor', 1),
(19, 'Kitchenware', 'Kitchenware', 1),
(20, 'Dining Essentials', 'Dining Essentials', 1),
(21, 'Storage & Organization', 'Storage & Organization', 1),
(22, 'Lighting', 'Lighting', 1),
(23, 'Men\'s Clothing', 'Men\'s Clothing', 2),
(24, 'Women\'s Clothing', 'Women\'s Clothing', 2),
(25, 'Kids & Baby Clothing', 'Kids & Baby Clothing', 2),
(26, 'Shoes', 'Shoes', 2),
(27, 'Bags', 'Bags', 2),
(28, 'Accessories', 'Accessories', 2),
(29, 'Mobile Phones', 'Mobile Phones', 3),
(30, 'Tablets', 'Tablets', 3),
(31, 'Laptops', 'Laptops', 3),
(32, 'Cameras', 'Cameras', 3),
(33, 'Audio Devices', 'Audio Devices', 3),
(34, 'Smartwatches', 'Smartwatches', 3),
(35, 'Accessories & Cables', 'Accessories & Cables', 3),
(36, 'Small Kitchen Appliances', 'Small Kitchen Appliances', 4),
(37, 'Refrigerators', 'Refrigerators', 4),
(38, 'Air Conditioners', 'Air Conditioners', 4),
(39, 'Washing Machines', 'Washing Machines', 4),
(40, 'Vacuum Cleaners', 'Vacuum Cleaners', 4),
(41, 'Cookware', 'Cookware', 5),
(42, 'Bakeware', 'Bakeware', 5),
(43, 'Utensils', 'Utensils', 5),
(44, 'Kitchen Storage', 'Kitchen Storage', 5),
(45, 'Drinkware', 'Drinkware', 5),
(46, 'Skin Care', 'Skin Care', 6),
(47, 'Hair Care', 'Hair Care', 6),
(48, 'Makeup', 'Makeup', 6),
(49, 'Fragrances', 'Fragrances', 6),
(50, 'Health & Wellness', 'Health & Wellness', 6),
(51, 'Bath & Body', 'Bath & Body', 6),
(52, 'Toys', 'Toys', 7),
(53, 'Baby Essentials', 'Baby Essentials', 7),
(54, 'Educational Toys', 'Educational Toys', 7),
(55, 'Strollers & Car Seats', 'Strollers & Car Seats', 7),
(56, 'Power Tools', 'Power Tools', 8),
(57, 'Hand Tools', 'Hand Tools', 8),
(58, 'Electrical', 'Electrical', 8),
(59, 'Plumbing', 'Plumbing', 8),
(60, 'Safety Gear', 'Safety Gear', 8),
(61, 'Fitness Equipment', 'Fitness Equipment', 9),
(62, 'Outdoor Gear', 'Outdoor Gear', 9),
(63, 'Bicycles', 'Bicycles', 9),
(64, 'Sports Apparel', 'Sports Apparel', 9),
(65, 'Camping Gear', 'Camping Gear', 9),
(66, 'Dog Supplies', 'Dog Supplies', 10),
(67, 'Cat Supplies', 'Cat Supplies', 10),
(68, 'Food & Treats', 'Food & Treats', 10),
(69, 'Pet Grooming', 'Pet Grooming', 10),
(70, 'Aquariums', 'Aquariums', 10),
(71, 'Snacks', 'Snacks', 11),
(72, 'Packaged Goods', 'Packaged Goods', 11),
(73, 'Drinks', 'Drinks', 11),
(74, 'Coffee & Tea', 'Coffee & Tea', 11),
(75, 'Organic Products', 'Organic Products', 11),
(76, 'Car Accessories', 'Car Accessories', 12),
(77, 'Motorcycle Accessories', 'Motorcycle Accessories', 12),
(78, 'Tools & Maintenance', 'Tools & Maintenance', 12),
(79, 'Lubricants', 'Lubricants', 12),
(80, 'Components', 'Components', 13),
(81, 'Keyboards & Mice', 'Keyboards & Mice', 13),
(82, 'Monitors', 'Monitors', 13),
(83, 'Storage Devices', 'Storage Devices', 13),
(84, 'Networking', 'Networking', 13),
(85, 'Consoles', 'Consoles', 14),
(86, 'Games', 'Games', 14),
(87, 'Gaming Accessories', 'Gaming Accessories', 14),
(88, 'VR Gear', 'VR Gear', 14),
(89, 'Educational Books', 'Educational Books', 15),
(90, 'Novels', 'Novels', 15),
(91, 'School Supplies', 'School Supplies', 15),
(92, 'Office Tools', 'Office Tools', 15);

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `message_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`message_id`, `sender_id`, `receiver_id`, `message`, `timestamp`) VALUES
(1, 3, 2, 'hi', '2025-11-14 04:58:14');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `coupon_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `type` enum('fixed','percent') NOT NULL DEFAULT 'fixed',
  `value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expires_at` datetime DEFAULT NULL,
  `max_uses` int(11) DEFAULT 0,
  `used_count` int(11) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `store_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`coupon_id`, `code`, `type`, `value`, `expires_at`, `max_uses`, `used_count`, `created_by`, `created_at`, `store_id`) VALUES
(1, 'HEKHEK55155', 'fixed', 2.00, '2025-11-29 12:17:00', 5, 0, 2, '2025-11-14 01:18:08', NULL),
(2, '1654684', 'fixed', 10.00, '2025-11-29 03:57:00', 5, 0, 2, '2025-11-14 03:53:08', 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Confirmed','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `coupon_id` int(11) DEFAULT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `buyer_id`, `total_amount`, `payment_method`, `status`, `created_at`, `coupon_id`, `coupon_code`, `discount_amount`) VALUES
(1, 3, 100.00, 'Card', 'Confirmed', '2025-11-14 02:19:51', NULL, NULL, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 1, 1, 100.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `seller_id`, `category_id`, `name`, `description`, `price`, `stock`, `image`, `status`, `created_at`) VALUES
(1, 2, 26, 'Nike Full Force Low Premium Men\'s Shoes', 'Nike Full Force Low Premium Men\'s Shoes. Nike PH', 100.00, 4, 'https://static.nike.com/a/images/t_web_pdp_936_v2/f_auto/0a82dcaf-0911-4aaa-9eba-970c9fb02a23/NIKE+FULL+FORCE+LO+PRM.png', 'active', '2025-11-13 23:52:59');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `buyer_id` int(11) DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `product_id`, `buyer_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 3, 5, 'Maganda', '2025-11-14 02:38:35');

-- --------------------------------------------------------

--
-- Table structure for table `seller_profiles`
--

CREATE TABLE `seller_profiles` (
  `profile_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `shop_name` varchar(150) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `shipping_policy` text DEFAULT NULL,
  `return_policy` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `seller_profiles`
--

INSERT INTO `seller_profiles` (`profile_id`, `seller_id`, `shop_name`, `logo`, `banner`, `description`, `shipping_policy`, `return_policy`, `created_at`) VALUES
(1, 2, 'WEST Shop', 'assets/images/shops/s2_logo_1763082933_e12906fd.png', 'assets/images/shops/s2_banner_1763087870_871a6874.png', 'The Shop where you can find happiness', NULL, NULL, '2025-11-14 00:51:50');

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `store_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `store_name` varchar(120) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `shipping_policy` text DEFAULT NULL,
  `return_policy` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`store_id`, `seller_id`, `store_name`, `logo`, `banner`, `description`, `shipping_policy`, `return_policy`, `created_at`) VALUES
(1, 2, 'West Ragma Store', NULL, NULL, NULL, NULL, NULL, '2025-11-14 02:45:27');

-- --------------------------------------------------------

--
-- Table structure for table `store_follows`
--

CREATE TABLE `store_follows` (
  `follow_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `store_follows`
--

INSERT INTO `store_follows` (`follow_id`, `store_id`, `buyer_id`, `seller_id`, `created_at`) VALUES
(1, 1, 3, 2, '2025-11-14 02:38:14');

-- --------------------------------------------------------

--
-- Table structure for table `store_reviews`
--

CREATE TABLE `store_reviews` (
  `review_id` int(11) NOT NULL,
  `store_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `buyer_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ;

--
-- Dumping data for table `store_reviews`
--

INSERT INTO `store_reviews` (`review_id`, `store_id`, `seller_id`, `buyer_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(5, 1, 2, 3, 5, 'maangas po yung store nyo', '2025-11-14 06:19:18', '2025-11-14 06:19:48');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role` enum('buyer','seller','admin') NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role`, `name`, `email`, `password`, `phone`, `address`, `status`, `created_at`) VALUES
(1, 'admin', 'Westlie Casuncad', 'westragma@gmail.com', '$2y$10$Y41.jnEjNFOFSU7Qo4yWl.x.rzvC820kwH5b2b0poROYhfPxGlKqm', NULL, NULL, 'approved', '2025-11-13 23:22:05'),
(2, 'seller', 'West Ragma', 'west@gmail.com', '$2y$10$KdSJi6LOFc5oM9HgwdglPeo5QlX/OS6G/yVq.hwS4QEW3FIkepag.', NULL, NULL, 'approved', '2025-11-13 23:22:05'),
(3, 'buyer', 'Danhil Baluyot', 'dbaluyot@gmail.com', '$2y$10$bCnl73NYZ9hx9xm.gehZ5ew.GVH9Qa58BDys7HaQGgrlxLvSxDhQ.', NULL, NULL, 'approved', '2025-11-13 23:22:05');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `voucher_id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `discount_percent` int(11) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `expires_at` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `store_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD KEY `fk_categories_parent` (`parent_id`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`coupon_id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_coupons_store` (`store_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `fk_orders_coupon` (`coupon_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  ADD PRIMARY KEY (`profile_id`),
  ADD UNIQUE KEY `uniq_seller` (`seller_id`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`store_id`),
  ADD KEY `idx_store_seller` (`seller_id`);

--
-- Indexes for table `store_follows`
--
ALTER TABLE `store_follows`
  ADD PRIMARY KEY (`follow_id`),
  ADD UNIQUE KEY `uniq_follow` (`buyer_id`,`seller_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `idx_sf_store` (`store_id`);

--
-- Indexes for table `store_reviews`
--
ALTER TABLE `store_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `uniq_store_review` (`seller_id`,`buyer_id`),
  ADD KEY `seller_id` (`seller_id`),
  ADD KEY `buyer_id` (`buyer_id`),
  ADD KEY `idx_sr_store` (`store_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`voucher_id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_vouchers_store` (`store_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `store_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `store_follows`
--
ALTER TABLE `store_follows`
  MODIFY `follow_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `store_reviews`
--
ALTER TABLE `store_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `voucher_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `fk_chat_receiver` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_chat_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `fk_coupons_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`store_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_coupons_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_orders_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`coupon_id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_products_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_reviews_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  ADD CONSTRAINT `fk_profile_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `fk_stores_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `store_follows`
--
ALTER TABLE `store_follows`
  ADD CONSTRAINT `fk_follow_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_follow_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_store_follows_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`store_id`) ON DELETE CASCADE;

--
-- Constraints for table `store_reviews`
--
ALTER TABLE `store_reviews`
  ADD CONSTRAINT `fk_store_reviews_buyer` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_store_reviews_seller` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_store_reviews_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`store_id`) ON DELETE CASCADE;

--
-- Constraints for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD CONSTRAINT `fk_vouchers_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`store_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_vouchers_user` FOREIGN KEY (`created_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
