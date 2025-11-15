-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 15, 2025 at 05:30 AM
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
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `announcement_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `body` text NOT NULL,
  `audience` varchar(20) NOT NULL DEFAULT 'all',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `author_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_messages`
--

INSERT INTO `chat_messages` (`message_id`, `sender_id`, `receiver_id`, `message`, `is_read`, `timestamp`) VALUES
(1, 3, 2, 'hi', 1, '2025-11-14 04:58:14'),
(2, 2, 3, 'hi', 1, '2025-11-14 19:18:50'),
(3, 3, 2, 'hello', 1, '2025-11-14 19:18:57'),
(4, 3, 2, 'hakhak', 1, '2025-11-14 19:46:18'),
(5, 2, 3, 'ha?', 1, '2025-11-14 19:46:34'),
(6, 3, 2, 'halimaw', 1, '2025-11-14 19:46:42'),
(7, 3, 2, 'hotdog', 1, '2025-11-14 19:46:50'),
(8, 2, 3, 'halaman', 1, '2025-11-14 19:47:01'),
(9, 2, 3, 'ha', 1, '2025-11-14 19:47:35'),
(10, 3, 2, 'haq', 1, '2025-11-14 19:50:57'),
(11, 2, 3, 'aw', 1, '2025-11-14 20:09:29'),
(12, 3, 2, 'ha', 1, '2025-11-14 20:13:32'),
(13, 3, 2, 'halu', 1, '2025-11-14 20:25:31'),
(14, 2, 3, 'hai', 1, '2025-11-14 20:25:35'),
(15, 2, 1, 'Kuya Na Tangal', 1, '2025-11-14 21:16:04'),
(16, 1, 2, 'Gagawin ko?', 1, '2025-11-14 21:24:58'),
(17, 2, 1, 'ha?', 1, '2025-11-14 21:25:58'),
(18, 1, 2, 'halimaw', 1, '2025-11-14 21:26:08'),
(19, 1, 4, 'Eyy bago ka lang dito boss?', 1, '2025-11-14 21:57:17'),
(20, 4, 1, 'hehek', 0, '2025-11-14 22:03:56'),
(21, 2, 3, 'hiii', 1, '2025-11-15 02:17:37'),
(22, 2, 3, 'ha', 1, '2025-11-15 02:17:49'),
(23, 2, 3, 'hahaha', 1, '2025-11-15 02:22:57'),
(24, 2, 3, 'hahahaha', 1, '2025-11-15 02:23:01'),
(25, 3, 2, 'happy?', 1, '2025-11-15 02:23:10'),
(26, 3, 2, 'happy', 1, '2025-11-15 02:23:21'),
(27, 3, 2, 'hahaha', 1, '2025-11-15 02:23:27'),
(28, 2, 3, 'West Fashion confirmed your order #1. We will notify you once it ships.', 1, '2025-11-15 02:37:03'),
(29, 3, 2, 'okay', 1, '2025-11-15 02:42:28'),
(30, 2, 1, 'haha', 0, '2025-11-15 02:42:45');

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
(2, '1654684', 'fixed', 10.00, '2025-11-29 03:57:00', 5, 1, 2, '2025-11-14 03:53:08', 1),
(3, 'CHRISTMAS123', 'percent', 10000000.00, '2025-11-15 00:00:00', 1, 0, NULL, '2025-11-14 21:18:06', NULL);

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

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 3, 'Order #2 placed successfully. Current status: Pending.', 1, '2025-11-14 19:08:31'),
(2, 2, 'New order #2 from Danhil Baluyot contains your products. Check the Seller Portal for details.', 1, '2025-11-14 19:08:31'),
(3, 3, 'Order #3 placed successfully. Current status: Pending.', 1, '2025-11-15 02:27:15'),
(4, 4, 'New order #3 from Danhil Baluyot contains your products. Check the Seller Portal for details.', 0, '2025-11-15 02:27:15'),
(5, 3, 'Order #1 status updated to Shipped.', 1, '2025-11-15 02:37:00'),
(6, 3, 'Order #1 status updated to Confirmed.', 1, '2025-11-15 02:37:03'),
(7, 3, 'Order #1 status updated to Shipped.', 1, '2025-11-15 02:37:04'),
(8, 3, 'Order #1 status updated to Delivered.', 1, '2025-11-15 02:42:31');

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
  `cancel_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `coupon_id` int(11) DEFAULT NULL,
  `coupon_code` varchar(50) DEFAULT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `buyer_id`, `total_amount`, `payment_method`, `status`, `cancel_reason`, `created_at`, `coupon_id`, `coupon_code`, `discount_amount`) VALUES
(1, 3, 100.00, 'Card', 'Delivered', NULL, '2025-11-14 02:19:51', NULL, NULL, 0.00),
(2, 3, 100.00, 'COD', 'Cancelled', NULL, '2025-11-14 19:08:31', NULL, NULL, 0.00),
(3, 3, 1367.00, 'COD', 'Pending', NULL, '2025-11-15 02:27:15', NULL, '1654684', 10.00);

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
(1, 1, 1, 1, 100.00),
(2, 2, 1, 1, 100.00),
(3, 3, 32, 2, 589.00),
(4, 3, 36, 1, 199.00);

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
(1, 2, 26, 'Nike Full Force Low Premium Men\'s Shoes', 'Nike Full Force Low Premium Men\'s Shoes. Nike PH', 100.00, 3, 'https://static.nike.com/a/images/t_web_pdp_936_v2/f_auto/0a82dcaf-0911-4aaa-9eba-970c9fb02a23/NIKE+FULL+FORCE+LO+PRM.png', 'active', '2025-11-13 23:52:59'),
(3, 2, 28, 'Opal Glow Drop Earrings', 'Ultra-light drop earrings with faux opal stones and nickel-free hooks for everyday wear.', 549.00, 45, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTVR-l9aPHB5KIOXYIDJRzj82TEb_q6c0ZJMg&s', 'active', '2025-11-14 20:41:09'),
(4, 2, 28, 'Velvet Luxe Hair Clip Set', 'Set of three velvet hair clips lined with anti-slip grips to keep styles in place all day.', 299.00, 60, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRPTEtyO2zTREm9NteccnPlpB7YcrWkf07JWQ&s', 'active', '2025-11-14 20:41:09'),
(5, 2, 28, 'Quartz Stackable Bracelet Pack', 'Set of four stretch bracelets with rose-quartz beads, matte gold spacers, and elastic cords.', 459.00, 40, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSUcNWkvilifp-X2bfPFE8DECtiUO5smtCS6A&s', 'active', '2025-11-14 20:41:09'),
(6, 2, 28, 'Classic Leather Skinny Belt', 'Full-grain leather belt with polished hardware designed to cinch dresses or layer over blazers.', 899.00, 30, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTm_80c5lJbAZusfdZcgp5jZ-xTi-SZVf_mPw&s', 'active', '2025-11-14 20:41:09'),
(7, 2, 27, 'Midnight City Crossbody', 'Pebbled vegan leather crossbody with adjustable strap, zip pocket, and matte black fittings.', 1890.00, 28, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTMZULFKgFzXKQNS_DM21Bnc3qg8M7aFCAGQg&s', 'active', '2025-11-14 20:41:09'),
(8, 2, 27, 'Everyday Structured Tote', 'Roomy saffiano tote with padded laptop sleeve, key leash, and reinforced base feet.', 2190.00, 24, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTV6-rWf6b3dA0-4QzL8M-KGg8_v25J-BhOFQ&s', 'active', '2025-11-14 20:41:09'),
(9, 2, 27, 'Metro Sling Pack', 'Slim anti-theft sling with hidden zipper panel, breathable back mesh, and USB pass-through.', 1250.00, 36, 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(10, 2, 27, 'Heritage Mini Backpack', 'Compact daypack with twin front pockets, padded straps, and water-resistant twill shell.', 1580.00, 32, 'https://images.unsplash.com/photo-1475180098004-ca77a66827be?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(11, 2, 27, 'Weekend Canvas Duffle', 'Oversized canvas duffle with leather trims, trolley sleeve, and removable shoulder strap.', 2450.00, 20, 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(12, 2, 25, 'CloudSoft Baby Romper Set', 'Two-piece bamboo romper set featuring fold-over mittens and nickel-free snaps.', 690.00, 45, 'https://images.unsplash.com/photo-1504151932400-72d4384f04b3?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(13, 2, 25, 'Playground Stretch Denim', 'Soft stretch denim with knee reinforcements and adjustable waist for growing kids.', 780.00, 38, 'https://images.unsplash.com/photo-1470813740244-df37b8c1edcb?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(14, 2, 25, 'Sunbeam Graphic Tee Pack', 'Three-piece cotton tee set with sunny graphics and tag-free collars for itch-free days.', 650.00, 50, 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(15, 2, 25, 'Dreamy Knitted Cardigan', 'Cozy knit cardigan with wooden buttons and cloud embroidery for cooler play dates.', 890.00, 34, 'https://images.unsplash.com/photo-1503341455253-b2e723bb3dbb?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(16, 2, 25, 'Galaxy Lights Sneakers', 'Lightweight sneakers with galaxy print mesh uppers and glow-in-the-dark eyelets.', 1190.00, 42, 'https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(17, 2, 23, 'Heritage Oxford Shirt', 'Tailored oxford shirt with button-down collar, locker loop, and easy-iron cotton.', 1290.00, 40, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRQuX911_m6JN9lAPHkAu_lkoQWLmbAXEUw6w&s', 'active', '2025-11-14 20:41:09'),
(18, 2, 23, 'Coastal Linen Camp Shirt', 'Breathable linen-blend camp shirt with coconut buttons and relaxed sleeves for summer.', 1490.00, 34, 'https://images.unsplash.com/photo-1490111718993-d98654ce6cf7?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(19, 2, 23, 'CoreFlex Chino Pants', 'Tapered chino pants with 4-way stretch, smart creases, and hidden zip pocket.', 1350.00, 38, 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(20, 2, 23, 'Everyday Crew-Neck Tee Pack', 'Bundle of three enzyme-washed tees with reinforced collars and curved hems.', 990.00, 55, 'https://images.unsplash.com/photo-1503602642458-232111445657?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(21, 2, 23, 'AeroTech Training Hoodie', 'Moisture-wicking hoodie with reflective piping, thumbholes, and media pocket.', 1650.00, 28, 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(22, 2, 26, 'Pulse Runner Knit Sneakers', 'Knit upper runners with responsive foam midsole, wide toe box, and reflective heel tab.', 2250.00, 30, 'https://images.unsplash.com/photo-1514986888952-8cd320577b68?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(23, 2, 26, 'Heritage Leather Brogues', 'Hand-burnished leather brogues with Goodyear welt construction and cushioned insoles.', 3150.00, 22, 'https://images.unsplash.com/photo-1460353581641-37baddab0fa2?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(24, 2, 26, 'Coastline Espadrille Flats', 'Handwoven espadrilles with jute soles, cushioned footbeds, and color-pop canvas.', 1750.00, 26, 'https://images.unsplash.com/photo-1505685296765-3a2736de412f?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(25, 2, 26, 'Elevate Court High-Tops', 'Padded high-top sneakers with contrast panels, rubber cup sole, and waxed laces.', 2890.00, 24, 'https://images.unsplash.com/photo-1529333166437-7750a6dd5a70?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(26, 2, 26, 'Nimbus Cloud Slides', 'Ultra-cushioned EVA slides with anti-slip grooves and contoured footbed for recovery days.', 950.00, 45, 'https://images.unsplash.com/photo-1504198458649-3128b932f49b?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(27, 2, 24, 'Muse Pleated Midi Dress', 'Fluid pleated midi dress with removable sash, flutter sleeves, and hidden side zip.', 1890.00, 32, 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(28, 2, 24, 'Serene Wrap Blouse', 'Soft crepe wrap blouse with shirred cuffs, interior snap, and waist tie for custom fit.', 1290.00, 40, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSy0ZghAwgOIqQdiOiqntsnmACq5gyfpqeXTQ&s', 'active', '2025-11-14 20:41:09'),
(29, 2, 24, 'Aline High-Rise Culottes', 'Structured culottes with pleated front, stretch waistband, and side seam pockets.', 1490.00, 36, 'https://images.unsplash.com/photo-1504593811423-6dd665756598?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(30, 2, 24, 'FeatherSoft Lounge Set', 'Matching brushed-knit lounge top and jogger with rib cuffs and deep hand pockets.', 1650.00, 28, 'https://images.unsplash.com/photo-1503341455253-b2e723bb3dbb?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(31, 2, 24, 'Luxe Essential Tank Trio', 'Pack of three modal tanks with curved hem, bra-friendly straps, and raw edge finish.', 990.00, 50, 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?auto=format&fit=crop&w=900&q=80', 'active', '2025-11-14 20:41:09'),
(32, 4, 85, 'PlayStation 5 Slim Disc Edition', 'Latest PS5 slim chassis with 1TB SSD and DualSense controller.', 589.00, 18, 'https://th.bing.com/th/id/OIP.Zczg1gW4wdrmXCan5I7vugHaE8?w=276&h=184&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(33, 4, 85, 'Xbox Series X 1TB Performance Bundle', '4K native console with 1TB NVMe storage and Velocity Architecture.', 549.00, 18, 'https://m.media-amazon.com/images/I/71NBQ2a52CL._SL1500_.jpg', 'active', '2025-11-14 22:08:20'),
(34, 4, 85, 'Xbox Series S Carbon Black 1TB', 'Compact next-gen console with 1TB storage and high frame-rate output.', 349.00, 22, 'https://th.bing.com/th/id/OIP.p6R_UfweUms8Dy-PnuhPQgHaGW?w=196&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(35, 4, 85, 'Nintendo Switch OLED White Joy-Con', 'Vibrant 7-inch OLED Switch with improved audio and docked LAN.', 349.00, 25, 'https://m.media-amazon.com/images/I/61-PblYntsL._SL1500_.jpg', 'active', '2025-11-14 22:08:20'),
(36, 4, 85, 'Nintendo Switch Lite Turquoise', 'Lightweight handheld-only Switch ideal for travel gaming.', 199.00, 29, 'https://th.bing.com/th/id/OIP.YI5WdtfnsQ0tjdyq_LV6dgHaEx?w=295&h=190&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(37, 4, 85, 'ASUS ROG Ally X Ryzen Z1 Extreme', 'Windows 11 handheld with upgraded cooling and 24GB LPDDR5X RAM.', 799.00, 12, 'https://th.bing.com/th/id/OIP.Ca4Eex9RST-lDxAV6lCXsAHaEK?w=275&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(38, 4, 85, 'Steam Deck OLED 1TB Limited', 'Valve handheld with HDR OLED panel and rapid charging.', 649.00, 14, 'https://th.bing.com/th/id/OIP.0_3L0xBcaCicNcrd58yy5AHaEK?w=284&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(39, 4, 85, 'AYANEO Air 1S Pro 32GB', 'Ultra-portable handheld PC with Ryzen 7 7840U and AMOLED display.', 979.00, 6, 'https://th.bing.com/th/id/OIP.pK4UHbl-KHe3V4tggP2NagHaEK?w=290&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(40, 4, 85, 'Logitech G Cloud Gaming Handheld', 'Cloud-focused Android handheld with 12-hour battery life.', 299.00, 16, 'https://th.bing.com/th/id/OIP.mxcJe8E386cIZsuJYSM14AHaEK?w=287&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(41, 4, 85, 'Analogue Pocket Black Edition', 'FPGA handheld that plays original Game Boy, GBA and GG carts.', 249.00, 10, 'https://tse2.mm.bing.net/th/id/OIP.agyXOibWhEIub6ifqUcmwAHaEK?cb=ucfimg2ucfimg=1&rs=1&pid=ImgDetMain&o=7&rm=3', 'active', '2025-11-14 22:08:20'),
(42, 4, 86, 'Elden Ring Shadow of the Erdtree Edition', 'Base game plus expansion, ready for PS5/PC cross-save.', 79.99, 40, 'https://th.bing.com/th/id/OIP.88wqKgUS0rjBtqt3Q8vzsQHaEH?w=315&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(43, 4, 86, 'Final Fantasy VII Rebirth Deluxe', 'Two-disc adventure continuing the Remake saga on PS5.', 89.99, 28, 'https://media.karousell.com/media/photos/products/2023/9/15/final_fantasy_vii_rebirth_delu_1694745224_d784a29b', 'active', '2025-11-14 22:08:20'),
(44, 4, 86, 'The Legend of Zelda: Tears of the Kingdom', 'Expansive open-world sequel with sky islands and Ultrahand.', 69.99, 35, 'https://th.bing.com/th/id/OIP.kahiat9HgVMozJ2CtuPeOgHaHa?w=161&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(45, 4, 86, 'Marvel\'s Spider-Man 2 Launch Edition', 'Swing across an expanded New York with Peter and Miles.', 69.99, 32, 'https://th.bing.com/th/id/OIP.XFrVC7R0FLXyb40-icezjgHaDC?w=276&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(46, 4, 86, 'Starfield Constellation Edition Code', 'Bethesda space RPG with chronomark watch digital extras.', 299.99, 5, 'https://th.bing.com/th/id/OIP.XFdvUEe9j6P9E4a90urlpwHaEK?w=317&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(47, 4, 86, 'Baldur\'s Gate 3 Deluxe Physical', 'Collector pack with art cards, stickers and cloth map.', 79.99, 24, 'https://th.bing.com/th/id/OIP.jSQyjcwZsHIgXsFn-b3zxgHaE8?w=253&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(48, 4, 86, 'Call of Duty: Modern Warfare III Cross-Gen', 'Includes both PS4 and PS5 versions plus open beta access.', 69.99, 38, 'https://th.bing.com/th/id/OIP.O6nH4dNThWocoBnPSO6GCAHaEK?w=319&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(49, 4, 86, 'Hogwarts Legacy Deluxe Switch', 'Includes Dark Arts pack and exclusive mounts on Switch.', 69.99, 26, 'https://th.bing.com/th/id/OIP.FLpSRjCq3TSLyqnu0Giz1wHaL_?w=115&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(50, 4, 86, 'Persona 5 Tactica Phantom Thieves Edition', 'Strategy spin-off with artbook, soundtrack and DLC.', 99.99, 15, 'https://fextralife.com/wp-content/uploads/2023/06/Persona-5-Tactica-A-New-Tactical-RPG.jpg', 'active', '2025-11-14 22:08:20'),
(51, 4, 86, 'Helldivers 2 Super Citizen Upgrade', 'Comes with DP-53 armor, SMG-32 and Stratagem hero cape.', 39.99, 45, 'https://i.ytimg.com/vi/ADqlq-Uq25A/maxresdefault.jpg', 'active', '2025-11-14 22:08:20'),
(52, 4, 87, 'DualSense Edge Wireless Controller', 'Pro-grade PS5 controller with replaceable stick modules.', 199.99, 20, 'https://tse3.mm.bing.net/th/id/OIP.AkTC86mSqxfUnCG4F2qVggHaDy?cb=ucfimg2ucfimg=1&rs=1&pid=ImgDetMain&o=7&rm=3', 'active', '2025-11-14 22:08:20'),
(53, 4, 87, 'Xbox Elite Wireless Controller Series 2 Core', 'Adjustable-tension thumbsticks and wrap-around rubber grip.', 129.99, 22, 'https://th.bing.com/th/id/OIP.9wjnlThtqyQ-OmY3HXTSnAHaFj?w=224&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(54, 4, 87, 'Razer Huntsman V2 TKL Optical Keyboard', 'Linear optical switches with dampening foam and PBT keycaps.', 159.99, 18, 'https://th.bing.com/th/id/OIP._MYyP-r94OlN46drSkZzZwHaD4?w=342&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(55, 4, 87, 'Logitech G Pro X Superlight 2 Mouse', '54-gram wireless mouse with HERO 32K sensor and USB-C.', 159.99, 25, 'https://th.bing.com/th/id/OIP._nG8uSCWP_wYPmRMe7798QHaHa?w=170&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(56, 4, 87, 'SteelSeries Arctis Nova Pro Wireless Headset', 'ANC gaming headset with dual hot-swappable batteries.', 349.99, 16, 'https://th.bing.com/th/id/OIP.nUvN5HajtWefqLy63c3QZgHaJl?w=106&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(57, 4, 87, 'Elgato Stream Deck MK.2 White', '15 customizable LCD keys for macros, scenes and automation.', 179.99, 14, 'https://th.bing.com/th/id/OIP.qks8SqkxUx2G10D0aTQMwgHaHa?w=184&h=185&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(58, 4, 87, 'Razer Wolverine V2 Pro Controller', 'Low-latency wireless controller with Mecha-Tactile buttons.', 249.99, 12, 'https://th.bing.com/th/id/OIP._giqNQm2Z9bhhqRL2MfYcQHaFa?w=273&h=199&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(59, 4, 87, 'Secretlab Titan Evo 2024 XL Gaming Chair', 'Magnetic head pillow and 4-way L-ADAPT lumbar system.', 629.00, 8, 'https://th.bing.com/th/id/OIP.SKNbUgdnXd5VETaXW4H9QAHaHE?w=205&h=196&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(60, 4, 87, 'NZXT Capsule Mini USB Microphone', 'Cardioid USB mic tuned for crisp team chat and streaming.', 69.99, 30, 'https://th.bing.com/th/id/OIP.kx2YUNYiLn_Q5K_Jjxi_xgHaHS?w=200&h=196&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(61, 4, 87, 'HyperX ChargePlay Duo for DualSense', 'Adds weighted base and LED indicators for two PS5 controllers.', 39.99, 40, 'https://tse1.mm.bing.net/th/id/OIP.mR8CXTK7syFO8mFU_BjI4wHaHa?cb=ucfimg2ucfimg=1&rs=1&pid=ImgDetMain&o=7&rm=3', 'active', '2025-11-14 22:08:20'),
(62, 4, 88, 'Meta Quest 3 512GB Advanced All-In-One', 'Mixed reality headset with Snapdragon XR2 Gen 2 and Touch Plus controllers.', 649.99, 20, 'https://th.bing.com/th/id/OIP.4JXgm2XLW_AUIZrCe4o5qAHaHa?w=202&h=202&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(63, 4, 88, 'PlayStation VR2 Horizon Bundle', 'Includes Horizon Call of the Mountain voucher and Sense controllers.', 599.99, 15, 'https://th.bing.com/th/id/OIP.JhcE88eFm4nVHMN1bWsfigHaHa?w=179&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(64, 4, 88, 'HTC Vive XR Elite Headset Set', 'Convertible MR headset with removable battery cradle and diopter dials.', 1099.99, 6, 'https://th.bing.com/th/id/OIP.bmfqw_0oO0qS0HvPu1doawHaD3?w=295&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(65, 4, 88, 'Valve Index Full VR Kit', 'Includes headset, controllers and 2.0 base stations for SteamVR tracking.', 999.00, 5, 'https://th.bing.com/th/id/OIP.Z7qBwqlzkMEhjytBuz7czwHaEK?w=276&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(66, 4, 88, 'PICO 4 Pro 512GB Standalone', 'Balanced pancake optics with eye and face tracking.', 799.00, 7, 'https://th.bing.com/th/id/OIP.fXVKC4NdDDmIkfyJ5wixswHaHa?w=171&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(67, 4, 88, 'HP Reverb G2 Omnicept Edition', 'Enterprise-grade VR with eye, heart-rate and facial expression sensors.', 1249.00, 4, 'https://th.bing.com/th/id/OIP._XwFjZgDORZVNS_NGgHXpgHaHa?w=186&h=186&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(68, 4, 88, 'Varjo Aero Professional Headset', 'Mini-LED dual displays with 2880x2720 per eye resolution.', 1990.00, 3, 'https://th.bing.com/th/id/OIP.s78HXmWx0b1YoI3IJ3cDlQHaFn?w=243&h=185&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(69, 4, 88, 'Meta Quest Pro Enterprise Kit', 'Color passthrough MR headset with eye tracking and pro controllers.', 999.99, 6, 'https://th.bing.com/th/id/OIP.ZiPCGOQ6odzaEvDKKyHn6wHaDC?w=336&h=143&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(70, 4, 88, 'HTC Vive Tracker 3.0 Three-Pack', 'Track feet, props or full-body rigs with extended battery life.', 349.00, 18, 'https://th.bing.com/th/id/OIP.xc6rzaNOi0-LODnNPHR4jAAAAA?w=312&h=176&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20'),
(71, 4, 88, 'bHaptics TactSuit X40 Haptic Vest', '40 point haptic feedback suit compatible with leading VR titles.', 499.00, 9, 'https://th.bing.com/th/id/OIP.uoYKTRk_vIjpIWwWF1e34QHaE9?w=263&h=180&c=7&r=0&o=7&cb=ucfimg2&dpr=1.3&pid=1.7&rm=3&ucfimg=1', 'active', '2025-11-14 22:08:20');

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
(1, 2, 'West Fashion', 'assets/images/shops/s2_logo_1763082933_e12906fd.png', 'assets/images/shops/s2_banner_1763087870_871a6874.png', 'The Shop where you can find happiness', '📦 Shipping Policy (Short Version)\r\n\r\nOrders are processed within 1–3 business days.\r\n\r\nPH delivery: 3–7 days, International: 7–21 days.\r\n\r\nShipping fees are calculated at checkout.\r\n\r\nYou will receive a tracking number once your order ships.\r\n\r\nCustomers are responsible for customs/import taxes for international orders.', 'Returns allowed within 7 days (PH) or 14 days (International) after receiving the item.\r\n\r\nItems must be unused, unwashed, and with original tags.\r\n\r\nNon-returnable: custom-made items, intimates, jewelry, and sale items.\r\n\r\nExchanges allowed if stock is available.\r\n\r\nIf wrong or damaged item received, we cover return shipping.\r\n\r\nFor size changes or preference, customer pays return shipping.\r\n\r\nTo request a return, contact us with your Order Number and issue.', '2025-11-14 00:51:50'),
(2, 4, 'DENMAR GAMING', 'assets/images/shops/s4_logo_1763158364_b6f8bf8d.png', 'assets/images/shops/s4_banner_1763158376_15115636.png', '', '', '', '2025-11-14 21:30:40');

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
(1, 2, 'West Fashion', NULL, NULL, 'The Shop where you can find happiness', '📦 Shipping Policy (Short Version)\r\n\r\nOrders are processed within 1–3 business days.\r\n\r\nPH delivery: 3–7 days, International: 7–21 days.\r\n\r\nShipping fees are calculated at checkout.\r\n\r\nYou will receive a tracking number once your order ships.\r\n\r\nCustomers are responsible for customs/import taxes for international orders.', 'Returns allowed within 7 days (PH) or 14 days (International) after receiving the item.\r\n\r\nItems must be unused, unwashed, and with original tags.\r\n\r\nNon-returnable: custom-made items, intimates, jewelry, and sale items.\r\n\r\nExchanges allowed if stock is available.\r\n\r\nIf wrong or damaged item received, we cover return shipping.\r\n\r\nFor size changes or preference, customer pays return shipping.\r\n\r\nTo request a return, contact us with your Order Number and issue.', '2025-11-14 02:45:27'),
(2, 4, 'DENMAR GAMING', 'assets/images/shops/s4_logo_1763158364_b6f8bf8d.png', 'assets/images/shops/s4_banner_1763158376_15115636.png', '', '', '', '2025-11-14 21:59:07');

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
(3, 'buyer', 'Danhil Baluyot', 'dbaluyot@gmail.com', '$2y$10$bCnl73NYZ9hx9xm.gehZ5ew.GVH9Qa58BDys7HaQGgrlxLvSxDhQ.', NULL, NULL, 'approved', '2025-11-13 23:22:05'),
(4, 'seller', 'Deee Curtivo', 'dcurtivo@gmail.com', '$2y$10$0rRpaV/6Mo3QwZbD4JvDj.vfisAu9rOCxJKui01MEMXo0e7KI/nU6', NULL, NULL, 'approved', '2025-11-14 21:30:40');

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
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `audience` (`audience`);

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
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `coupon_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seller_profiles`
--
ALTER TABLE `seller_profiles`
  MODIFY `profile_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `store_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
