-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 03:35 PM
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
-- Database: `web_assignment`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(20) NOT NULL,
  `color` varchar(255) NOT NULL,
  `added_at` datetime DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `product_id` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `cat_id` varchar(4) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`cat_id`, `name`) VALUES
('CAT1', 'New Arrivals'),
('CAT2', 'Casual Shirts'),
('CAT3', 'Pants'),
('CAT4', 'Shorts'),
('CAT5', 'Skirts'),
('CAT6', 'Sweater & Hoodies'),
('CAT7', 'Tees');

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE `member` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `dob` date NOT NULL,
  `photo_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  `address` varchar(255) DEFAULT NULL,
  `hp` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member`
--

INSERT INTO `member` (`user_id`, `username`, `password_hash`, `email`, `dob`, `photo_path`, `created_at`, `is_admin`, `address`, `hp`) VALUES
(3, 'user', '$2y$10$gNYDxlJGa0jsVeyERiJTp.Q8UX/LhQ2bgZcQO9id/K5Qg/.h1S5tK', 'inso0620@gmail.com', '2025-04-23', '../images/uploads/6808e85857ec17.16303787_Logo.png', '2025-04-23 21:16:36', 0, 'pv9', 1234567891),
(4, 'admin', '$2y$10$Y1JzwdghSrObeAFP56Owcezua5PqhhHRdCrJCXd9iPK7.9ZZsnrKa', 'admin060410@gmail.com', '0000-00-00', '../images/uploads/6808e93d50996_abao.png', '2025-04-23 21:21:01', 1, NULL, 0),
(5, 'ping', '$2y$10$lhjRNZuQHPxwDuOlQfKrc.Bn/EgiLZQ4Ls7uxNvPO5mzJHpBt.7FO', 'inso0q620@gmail.com', '2025-04-23', '../images/default_profile.png', '2025-04-23 21:25:52', 0, 'Block B -12-04 , PV 9 Residence, Jalan Kampung Wira Jaya，taman melati W.P. Kuala Lumpur, W.P. Kuala Lumpur, 53100', 1234567822),
(6, 'sleeping', '$2y$10$XCKXtF6hJUttEDus5Uh35ObAKQLHaefwawxy4rn.I3/K6acajrH1W', 'apaaing06a0410@gmail.com', '2025-04-23', '../images/default_profile.png', '2025-04-23 21:28:34', 0, 'Block A -32-08 , PV 9 Residence, Jalan Kampung Wira Jaya，taman melati W.P. Kuala Lumpur, W.P. Kuala Lumpur, 53100', 1111111111);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` varchar(20) DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  `payment_method` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `total_amount`, `order_status`, `created_at`, `payment_method`) VALUES
(1, 3, 155.00, 'Paid', '2025-04-23 21:18:38', 'TNG'),
(2, 3, 124.00, 'Shipped', '2025-04-23 21:22:40', 'Credit Card'),
(3, 5, 36.00, 'Paid', '2025-04-23 21:26:26', 'TNG'),
(4, 6, 275.00, 'Paid', '2025-04-23 21:29:30', 'Bank Transfer'),
(5, 6, 36.00, 'Cancelled', '2025-04-23 21:30:17', 'Not Specified'),
(6, 6, 114.00, 'Paid', '2025-04-23 21:30:49', 'TNG'),
(7, 5, 36.00, 'Pending', '2025-04-23 21:34:31', 'Not Specified');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `detail_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `size` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`detail_id`, `quantity`, `size`, `price`, `order_id`, `product_id`) VALUES
(33, 1, 'S', 50.00, 1, 'P0921'),
(34, 1, 'XL', 60.00, 1, 'P0043'),
(35, 1, 'M', 45.00, 1, 'P0697'),
(36, 1, 'M', 55.00, 2, 'P0472'),
(37, 1, 'S', 55.00, 2, 'P0472'),
(39, 1, 'XL', 30.00, 3, 'P0762'),
(40, 1, 'S', 200.00, 4, 'P0604'),
(41, 1, 'XL', 75.00, 4, 'P0566'),
(43, 1, 'XL', 30.00, 5, 'P0179'),
(44, 1, 'L', 100.00, 6, 'P0592'),
(45, 1, 'XL', 30.00, 7, 'P0963');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `color` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `img_front` varchar(255) NOT NULL,
  `img_back` varchar(255) NOT NULL,
  `cat_id` varchar(4) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `name`, `color`, `price`, `img_front`, `img_back`, `cat_id`, `created_at`) VALUES
('P0011', 'Belted Cargo Maxi Skirt', 'Blue', 120.00, '../images/clothes//Belted Cargo Maxi Skirt Blue_front.png', '../images/clothes//Belted Cargo Maxi Skirt Blue_back.png', 'CAT5', '2025-04-23 20:46:10'),
('P0020', 'Lettering Embroidered Shorts ', 'Black', 20.00, '../images/clothes//Lettering Embroidered Shorts  Black_front.png', '../images/clothes//Lettering Embroidered Shorts  Black_back.png', 'CAT4', '2025-04-23 21:00:02'),
('P0043', 'Answer Me ', 'Black', 60.00, '../images/clothes//Answer Me  Black_front.png', '../images/clothes//Answer Me  Black_back.png', 'CAT7', '2025-04-23 20:42:37'),
('P0080', 'Ribbon Pants ', 'Green', 45.00, '../images/clothes//Ribbon Pants  Green_front.png', '../images/clothes//Ribbon Pants  Green_back.png', 'CAT3', '2025-04-23 20:55:12'),
('P0103', 'Neo Chinese Style I', 'Black', 100.00, '../images/clothes//Neo Chinese Style I Black_front.png', '../images/clothes//Neo Chinese Style I Black_back.png', 'CAT2', '2025-04-23 21:07:35'),
('P0130', 'Cargo S.P', 'Green', 30.00, '../images/clothes//Cargo S.P Green_front.png', '../images/clothes//Cargo S.P Green_back.png', 'CAT4', '2025-04-23 20:51:20'),
('P0133', 'Sweater Brown ', 'Brown', 90.00, '../images/clothes//Sweater Brown  brown_front.png', '../images/clothes//Sweater Brown  brown_back.png', 'CAT6', '2025-04-23 20:35:46'),
('P0150', 'Washed Sweatshirt', 'Pink', 100.00, '../images/clothes//Washed Sweatshirt Pink_front.png', '../images/clothes//Washed Sweatshirt Pink_back.png', 'CAT6', '2025-04-23 20:38:12'),
('P0154', 'Ez Life', 'Blue', 40.00, '../images/clothes//Ez Life Blue_front.png', '../images/clothes//Ez Life Blue_back.png', 'CAT7', '2025-04-23 20:44:18'),
('P0179', 'Oversized Sweat Pants ', 'White', 30.00, '../images/clothes//Oversized Sweat Pants  White_front.png', '../images/clothes//Oversized Sweat Pants  White_back.png', 'CAT3', '2025-04-23 20:53:21'),
('P0273', 'Denim Skirt ', 'Blue', 80.00, '../images/clothes//Denim Skirt  blue_front.png', '../images/clothes//Denim Skirt  blue_back.png', 'CAT1', '2025-04-23 20:27:24'),
('P0421', 'Dwarstring Skirt', 'Black', 120.00, '../images/clothes//Dwarstring Skirt Black_front.png', '../images/clothes//Dwarstring Skirt Black_back.png', 'CAT5', '2025-04-23 20:45:14'),
('P0463', 'Check Scarf Skirt beige', 'Red', 80.00, '../images/clothes//Check Scarf Skirt beige Red_front.png', '../images/clothes//Check Scarf Skirt beige Red_back.png', 'CAT5', '2025-04-23 20:47:34'),
('P0472', 'Sport Trunks ', 'Pink', 55.00, '../images/clothes//Sport Trunks  Pink_front.png', '../images/clothes//Sport Trunks  Pink_back.png', 'CAT4', '2025-04-23 20:52:02'),
('P0539', 'Loose Retro Basketball Shorts', 'White', 30.00, '../images/clothes//Loose Retro Basketball Shorts White_front.png', '../images/clothes//Loose Retro Basketball Shorts White_back.png', 'CAT4', '2025-04-23 20:49:40'),
('P0566', 'Leather Mini Skirt', 'Brown', 75.00, '../images/clothes//Leather Mini Skirt Brown_front.png', '../images/clothes//Leather Mini Skirt Brown_back.png', 'CAT5', '2025-04-23 20:46:50'),
('P0592', 'Neo Chinese Style I', 'White', 100.00, '../images/clothes//Neo Chinese Style I White_front.png', '../images/clothes//Neo Chinese Style I White_back.png', 'CAT2', '2025-04-23 21:07:17'),
('P0604', 'Hoodie', 'Blue', 200.00, '../images/clothes//Hoodie Blue_front.png', '../images/clothes//Hoodie Blue_back.png', 'CAT6', '2025-04-23 20:39:42'),
('P0605', 'Classic Jeans L.P ', 'Blue', 60.00, '../images/clothes//Classic Jeans L.P  blue_front.png', '../images/clothes//Classic Jeans L.P  blue_back.png', 'CAT1', '2025-04-23 20:29:46'),
('P0628', 'Classic Jeans S.P ', 'Blue', 50.00, '../images/clothes//Classic Jeans S.P  blue_front.png', '../images/clothes//Classic Jeans S.P  blue_back.png', 'CAT1', '2025-04-23 20:28:58'),
('P0642', 'Polo Sweat Shirt', 'Grey', 150.00, '../images/clothes//Polo Sweat Shirt Grey_front.png', '../images/clothes//Polo Sweat Shirt Grey_back.png', 'CAT6', '2025-04-23 20:33:31'),
('P0697', 'GGreen olivegreen', 'Green', 45.00, '../images/clothes//GGreen olivegreen Green_front.png', '../images/clothes//GGreen olivegreen Green_back.png', 'CAT2', '2025-04-23 21:10:55'),
('P0729', 'Ez Life', 'Black', 40.00, '../images/clothes//Ez Life Black_front.png', '../images/clothes//Ez Life Black_back.png', 'CAT7', '2025-04-23 20:43:48'),
('P0748', 'Sport Trunks ', 'Black', 55.00, '../images/clothes//Sport Trunks  Black_front.png', '../images/clothes//Sport Trunks  Black_back.png', 'CAT4', '2025-04-23 20:52:24'),
('P0762', 'Casual S.P', 'Brown', 30.00, '../images/clothes//Casual S.P Brown_front.png', '../images/clothes//Casual S.P Brown_back.png', 'CAT4', '2025-04-23 21:00:39'),
('P0769', 'Classic Jean L.S.', 'Blue', 150.00, '../images/clothes//Classic Jean L.S. blue_front.png', '../images/clothes//Classic Jean L.S. blue_back.png', 'CAT1', '2025-04-23 20:31:03'),
('P0793', 'Sweater Red ', 'Red', 90.00, '../images/clothes//Sweater Red  Red_front.png', '../images/clothes//Sweater Red  Red_back.png', 'CAT6', '2025-04-23 20:36:23'),
('P0827', 'Cargo L.P', 'Black', 45.00, '../images/clothes//Cargo L.P Black_front.png', '../images/clothes//Cargo L.P Black_back.png', 'CAT3', '2025-04-23 20:57:23'),
('P0832', 'Neo Chinese Style II', 'Black', 100.00, '../images/clothes//Neo Chinese Style II Black_front.png', '../images/clothes//Neo Chinese Style II Black_back.png', 'CAT2', '2025-04-23 21:06:51'),
('P0847', 'Lettering Embroidered Shorts ', 'Grey', 20.00, '../images/clothes//Lettering Embroidered Shorts  Grey_front.png', '../images/clothes//Lettering Embroidered Shorts  Grey_back.png', 'CAT4', '2025-04-23 20:59:34'),
('P0884', 'Cargo S.P', 'Black', 35.00, '../images/clothes//Cargo S.P Black_front.png', '../images/clothes//Cargo S.P Black_back.png', 'CAT4', '2025-04-23 20:50:41'),
('P0898', 'Hoodie ', 'Black', 200.00, '../images/clothes//Hoodie  Black_front.png', '../images/clothes//Hoodie  Black_back.png', 'CAT6', '2025-04-23 20:38:56'),
('P0914', 'Classic Jean S.S ', 'Blue', 45.00, '../images/clothes//Classic Jean S.S  blue_front.png', '../images/clothes//Classic Jean S.S  blue_back.png', 'CAT1', '2025-04-23 20:30:28'),
('P0921', 'Logo Retro Tee ivory', 'White', 50.00, '../images/clothes//Logo Retro Tee ivory White_front.png', '../images/clothes//Logo Retro Tee ivory White_back.png', 'CAT7', '2025-04-23 20:41:47'),
('P0927', 'Neo Chinese Style II', 'White', 100.00, '../images/clothes//Neo Chinese Style II White_front.png', '../images/clothes//Neo Chinese Style II White_back.png', 'CAT2', '2025-04-23 21:06:22'),
('P0953', 'Sweat Shirt', 'Black', 150.00, '../images/clothes//Sweat Shirt black_front.png', '../images/clothes//Sweat Shirt black_back.png', 'CAT6', '2025-04-23 20:32:15'),
('P0958', 'Casual S.P', 'Black', 30.00, '../images/clothes//Casual S.P Black_front.png', '../images/clothes//Casual S.P Black_back.png', 'CAT4', '2025-04-23 21:02:07'),
('P0963', 'Cargo L.P', 'White', 30.00, '../images/clothes//Cargo L.P White_front.png', '../images/clothes//Cargo L.P White_back.png', 'CAT3', '2025-04-23 20:54:24'),
('P0981', 'Answer Me', 'Olive', 60.00, '../images/clothes//Answer Me Olive_front.png', '../images/clothes//Answer Me Olive_back.png', 'CAT7', '2025-04-23 20:43:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `cart_ibfk_1` (`user_id`),
  ADD KEY `cart_ibfk_2` (`product_id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`cat_id`);

--
-- Indexes for table `member`
--
ALTER TABLE `member`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `orders_ibfk_1` (`user_id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `order_details_ibfk_1` (`order_id`),
  ADD KEY `order_details_ibfk_2` (`product_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `product_ibfk_1` (`cat_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `member`
--
ALTER TABLE `member`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `detail_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `member` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `member` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_details`
--
ALTER TABLE `order_details`
  ADD CONSTRAINT `order_details_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`cat_id`) REFERENCES `category` (`cat_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
