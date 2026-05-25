-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 17, 2026 at 12:01 PM
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
-- Database: `rminder_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `community_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `user_id`, `community_id`, `created_at`) VALUES
(1, '', '', 7, 2, '2026-03-28 03:51:48'),
(2, '', '', 7, 2, '2026-03-28 03:51:49'),
(3, '', '', 7, 2, '2026-03-28 03:51:49'),
(4, '', '', 7, 2, '2026-03-28 03:51:49'),
(5, '', '', 7, 2, '2026-03-28 03:51:49'),
(6, '', '', 7, 2, '2026-03-28 03:51:49'),
(7, '', '', 7, 2, '2026-03-28 03:51:49'),
(8, '', '', 7, 2, '2026-03-28 03:51:49'),
(9, '', '', 7, 2, '2026-03-28 03:51:49'),
(10, '', '', 7, 2, '2026-03-28 03:51:49'),
(11, '', '', 7, 2, '2026-03-28 03:51:49'),
(12, '', '', 7, 2, '2026-03-28 03:51:49'),
(13, '', '', 7, 2, '2026-03-28 03:51:49'),
(14, '', '', 7, 2, '2026-03-28 03:51:49'),
(15, '', '', 7, 2, '2026-03-28 03:51:49'),
(16, '', '', 7, 2, '2026-03-28 03:51:49'),
(17, '', '', 7, 2, '2026-03-28 03:51:49'),
(18, '', '', 7, 2, '2026-03-28 03:51:49'),
(19, '', '', 7, 2, '2026-03-28 03:51:50'),
(20, '', '', 7, 2, '2026-03-28 03:51:50'),
(21, '', '', 7, 2, '2026-03-28 03:51:54'),
(22, '', '', 7, 2, '2026-03-28 03:51:54'),
(23, '', '', 7, 2, '2026-03-28 03:51:54'),
(24, '', '', 7, 2, '2026-03-28 03:51:54'),
(25, '', '', 7, 2, '2026-03-28 03:51:54'),
(26, '', '', 7, 2, '2026-03-28 03:51:54'),
(27, '', '', 7, 2, '2026-03-28 03:51:54'),
(28, '', '', 7, 2, '2026-03-28 03:51:55'),
(29, '', '', 7, 2, '2026-03-28 03:51:55'),
(30, '', '', 7, 2, '2026-03-28 03:51:55'),
(31, '', '', 7, 2, '2026-03-28 03:51:55'),
(32, '', '', 7, 2, '2026-03-28 03:51:55'),
(33, '', '', 7, 2, '2026-03-28 03:51:55'),
(34, '', '', 7, 2, '2026-03-28 03:51:55'),
(35, '', '', 7, 2, '2026-03-28 03:51:55'),
(36, '', '', 7, 2, '2026-03-28 03:51:55'),
(37, '', '', 7, 2, '2026-03-28 03:51:55'),
(38, '', '', 7, 2, '2026-03-28 03:51:55'),
(39, '', '', 7, 2, '2026-03-28 03:51:56'),
(40, '', '', 7, 2, '2026-03-28 03:51:56'),
(41, '', '', 7, 2, '2026-03-28 03:52:01'),
(42, '', '', 7, 2, '2026-03-28 03:52:01'),
(43, '', '', 7, 2, '2026-03-28 03:52:01'),
(44, '', '', 7, 2, '2026-03-28 03:52:01'),
(45, '', '', 7, 2, '2026-03-28 03:52:01'),
(46, '', '', 7, 2, '2026-03-28 03:52:01'),
(47, '', '', 7, 2, '2026-03-28 03:52:01'),
(48, '', '', 7, 2, '2026-03-28 03:52:01'),
(49, '', '', 7, 2, '2026-03-28 03:52:01'),
(50, '', '', 7, 2, '2026-03-28 03:52:01'),
(51, '', '', 7, 2, '2026-03-28 03:52:01'),
(52, '', '', 7, 2, '2026-03-28 03:52:01'),
(53, '', '', 7, 2, '2026-03-28 03:52:02'),
(54, '', '', 7, 2, '2026-03-28 03:52:02'),
(55, '', '', 7, 2, '2026-03-28 03:52:02'),
(56, '', '', 7, 2, '2026-03-28 03:52:02'),
(57, '', '', 7, 2, '2026-03-28 03:52:02'),
(58, '', '', 7, 2, '2026-03-28 03:52:02'),
(59, '', '', 7, 2, '2026-03-28 03:52:02'),
(60, '', '', 7, 2, '2026-03-28 03:52:02'),
(61, '', '', 7, 2, '2026-03-28 03:52:32'),
(62, '', '', 7, 2, '2026-03-28 03:52:32'),
(63, '', '', 7, 2, '2026-03-28 03:52:32'),
(64, '', '', 7, 2, '2026-03-28 03:52:32'),
(65, '', '', 7, 2, '2026-03-28 03:52:32'),
(66, '', '', 7, 2, '2026-03-28 03:52:33'),
(67, '', '', 7, 2, '2026-03-28 03:52:33'),
(68, '', '', 7, 2, '2026-03-28 03:52:33'),
(69, '', '', 7, 2, '2026-03-28 03:52:33'),
(70, '', '', 7, 2, '2026-03-28 03:52:33'),
(71, '', '', 7, 2, '2026-03-28 03:52:33'),
(72, '', '', 7, 2, '2026-03-28 03:52:34'),
(73, '', '', 7, 2, '2026-03-28 03:52:34'),
(74, '', '', 7, 2, '2026-03-28 03:52:34'),
(75, '', '', 7, 2, '2026-03-28 03:52:34'),
(76, '', '', 7, 2, '2026-03-28 03:52:34'),
(77, '', '', 7, 2, '2026-03-28 03:52:34'),
(78, '', '', 7, 2, '2026-03-28 03:52:34'),
(79, '', '', 7, 2, '2026-03-28 03:52:34'),
(80, '', '', 7, 2, '2026-03-28 03:52:34'),
(81, '', '', 7, 2, '2026-03-28 03:52:49'),
(82, '', '', 7, 2, '2026-03-28 03:52:49'),
(83, '', '', 7, 2, '2026-03-28 03:52:49'),
(84, '', '', 7, 2, '2026-03-28 03:52:49'),
(85, '', '', 7, 2, '2026-03-28 03:52:49'),
(86, '', '', 7, 2, '2026-03-28 03:52:49'),
(87, '', '', 7, 2, '2026-03-28 03:52:49'),
(88, '', '', 7, 2, '2026-03-28 03:52:49'),
(89, '', '', 7, 2, '2026-03-28 03:52:49'),
(90, '', '', 7, 2, '2026-03-28 03:52:49'),
(91, '', '', 7, 2, '2026-03-28 03:52:49'),
(92, '', '', 7, 2, '2026-03-28 03:52:49'),
(93, '', '', 7, 2, '2026-03-28 03:52:49'),
(94, '', '', 7, 2, '2026-03-28 03:52:49'),
(95, '', '', 7, 2, '2026-03-28 03:52:49'),
(96, '', '', 7, 2, '2026-03-28 03:52:49'),
(97, '', '', 7, 2, '2026-03-28 03:52:49'),
(98, '', '', 7, 2, '2026-03-28 03:52:49'),
(99, '', '', 7, 2, '2026-03-28 03:52:50'),
(100, '', '', 7, 2, '2026-03-28 03:52:50'),
(101, '', '', 7, 2, '2026-03-28 03:53:50'),
(102, '', '', 7, 2, '2026-03-28 03:53:50'),
(103, '', '', 7, 2, '2026-03-28 03:53:50'),
(104, '', '', 7, 2, '2026-03-28 03:53:50'),
(105, '', '', 7, 2, '2026-03-28 03:53:50'),
(106, '', '', 7, 2, '2026-03-28 03:53:50'),
(107, '', '', 7, 2, '2026-03-28 03:53:50'),
(108, '', '', 7, 2, '2026-03-28 03:53:50'),
(109, '', '', 7, 2, '2026-03-28 03:53:50'),
(110, '', '', 7, 2, '2026-03-28 03:53:50'),
(111, '', '', 7, 2, '2026-03-28 03:53:50'),
(112, '', '', 7, 2, '2026-03-28 03:53:50'),
(113, '', '', 7, 2, '2026-03-28 03:53:50'),
(114, '', '', 7, 2, '2026-03-28 03:53:50'),
(115, '', '', 7, 2, '2026-03-28 03:53:51'),
(116, '', '', 7, 2, '2026-03-28 03:53:51'),
(117, '', '', 7, 2, '2026-03-28 03:53:51'),
(118, '', '', 7, 2, '2026-03-28 03:53:51'),
(119, '', '', 7, 2, '2026-03-28 03:53:51'),
(120, '', '', 7, 2, '2026-03-28 03:53:51'),
(121, '', '', 7, 2, '2026-03-28 04:55:18'),
(122, '', '', 7, 2, '2026-03-28 04:55:18'),
(123, '', '', 7, 2, '2026-03-28 04:55:18'),
(124, '', '', 7, 2, '2026-03-28 04:55:18'),
(125, '', '', 7, 2, '2026-03-28 04:55:18'),
(126, '', '', 7, 2, '2026-03-28 04:55:18'),
(127, '', '', 7, 2, '2026-03-28 04:55:18'),
(128, '', '', 7, 2, '2026-03-28 04:55:18'),
(129, '', '', 7, 2, '2026-03-28 04:55:18'),
(130, '', '', 7, 2, '2026-03-28 04:55:18'),
(131, '', '', 7, 2, '2026-03-28 04:55:18'),
(132, '', '', 7, 2, '2026-03-28 04:55:18'),
(133, '', '', 7, 2, '2026-03-28 04:55:18'),
(134, '', '', 7, 2, '2026-03-28 04:55:18'),
(135, '', '', 7, 2, '2026-03-28 04:55:18'),
(136, '', '', 7, 2, '2026-03-28 04:55:18'),
(137, '', '', 7, 2, '2026-03-28 04:55:18'),
(138, '', '', 7, 2, '2026-03-28 04:55:18'),
(139, '', '', 7, 2, '2026-03-28 04:55:18'),
(140, '', '', 7, 2, '2026-03-28 04:55:18'),
(141, 'test', 'test', 1, 0, '2026-04-16 23:09:56'),
(142, 'tgest 2', ' test 2', 1, 0, '2026-04-16 23:10:04'),
(143, 'Object Oriented Programming', 'Encapsulation!', 1, 0, '2026-04-16 23:26:50');

-- --------------------------------------------------------

--
-- Table structure for table `communities`
--

CREATE TABLE `communities` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `communities`
--

INSERT INTO `communities` (`id`, `name`, `created_at`) VALUES
(1, 'Lagao (1st & 3rd)', '2026-03-26 07:40:18'),
(2, 'Bula', '2026-03-26 07:40:18'),
(3, 'Uhaw', '2026-03-26 07:40:18');

-- --------------------------------------------------------

--
-- Table structure for table `distributions`
--

CREATE TABLE `distributions` (
  `id` int(11) NOT NULL,
  `donation_id` int(11) DEFAULT NULL,
  `beneficiary` varchar(255) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `distributed_at` date DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `community_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `id` int(11) NOT NULL,
  `donor_name` varchar(255) DEFAULT NULL,
  `donation_type` enum('cash','food','supplies') NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `community_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`id`, `donor_name`, `donation_type`, `amount`, `quantity`, `remarks`, `community_id`, `user_id`, `created_at`) VALUES
(1, 'Mr beast', 'cash', 99999999.99, 0, 'First one to touch the square wins a Lambo :fire_emoji:', 0, 1, '2026-04-17 00:06:42');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `venue` varchar(255) NOT NULL,
  `status` enum('upcoming','completed','','') NOT NULL DEFAULT 'upcoming',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `event_type` varchar(20) NOT NULL DEFAULT 'personal',
  `visibility` varchar(20) NOT NULL DEFAULT 'personal',
  `community_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `venue`, `status`, `created_at`, `user_id`, `event_type`, `visibility`, `community_id`) VALUES
(36, 'Iftar', 'Auto-generated event', '2026-03-28', '18:00:00', 'Mosque', 'upcoming', '2026-03-28 14:25:07', 0, 'system', 'community', 2),
(37, 'Taraweeh', 'Auto-generated event', '2026-03-28', '19:30:00', 'Mosque', 'upcoming', '2026-03-28 14:25:07', 0, 'system', 'community', 2),
(38, 'Suhoor Reminder', 'Auto-generated event', '2026-03-28', '04:30:00', 'Mosque', 'upcoming', '2026-03-28 14:25:07', 0, 'system', 'community', 2),
(39, 'Iftar', 'Auto-generated event', '2026-03-29', '18:00:00', 'Mosque', 'upcoming', '2026-03-29 11:53:37', 0, 'system', 'community', NULL),
(40, 'Taraweeh', 'Auto-generated event', '2026-03-29', '19:30:00', 'Mosque', 'upcoming', '2026-03-29 11:53:37', 0, 'system', 'community', NULL),
(41, 'Suhoor Reminder', 'Auto-generated event', '2026-03-29', '04:30:00', 'Mosque', 'upcoming', '2026-03-29 11:53:37', 0, 'system', 'community', NULL),
(42, '123', '123', '2222-12-03', '00:32:00', '123', 'upcoming', '2026-03-29 12:12:02', 7, 'personal', 'personal', 2),
(43, 'Iftar', 'Auto-generated event', '2026-04-16', '18:00:00', 'Mosque', 'upcoming', '2026-04-16 15:12:27', 0, 'system', 'community', NULL),
(44, 'Taraweeh', 'Auto-generated event', '2026-04-16', '19:30:00', 'Mosque', 'upcoming', '2026-04-16 15:12:27', 0, 'system', 'community', NULL),
(45, 'Suhoor Reminder', 'Auto-generated event', '2026-04-16', '04:30:00', 'Mosque', 'upcoming', '2026-04-16 15:12:27', 0, 'system', 'community', NULL),
(46, 'Pray', 'Pray', '2026-04-16', '17:00:00', 'House', 'upcoming', '2026-04-16 15:49:13', 8, 'personal', 'personal', 1),
(47, 'Iftar', 'Auto-generated event', '2026-04-17', '18:00:00', 'Mosque', 'upcoming', '2026-04-16 16:02:17', 0, 'system', 'community', NULL),
(48, 'Taraweeh', 'Auto-generated event', '2026-04-17', '19:30:00', 'Mosque', 'upcoming', '2026-04-16 16:02:17', 0, 'system', 'community', NULL),
(49, 'Suhoor Reminder', 'Auto-generated event', '2026-04-17', '04:30:00', 'Mosque', 'upcoming', '2026-04-16 16:02:17', 0, 'system', 'community', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','imam','leader','viewer') NOT NULL DEFAULT 'viewer',
  `community_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `community_id`) VALUES
(1, 'Admin user', 'admin@email.com', '21232f297a57a5a743894a0e4a801fc3', 'admin', 0),
(2, '123', '123@123.com', '202cb962ac59075b964b07152d234b70', 'viewer', 0),
(3, 'abol', 'abol@email.com', '13201f1ebe21c03d41e79df3d82d5b26', 'viewer', 0),
(4, 'aa', 'aa@aa.com', '4124bc0a9335c27f086f24ba207a4912', 'viewer', 0),
(5, 'Ibn al Tefor', 'goodman@good.com', '202cb962ac59075b964b07152d234b70', 'leader', 0),
(6, 'Bee', 'bee@bee.com', '9dfd70fdf15a3cb1ea00d7799ac6651b', 'viewer', 2),
(7, 'Ibn al Balls', 'alBallsani@email.com', '417e4705aee1415f8583243b8c403af3', 'imam', 2),
(8, 'Kenneth Daniel C. Bandiala', 'kdban@gmail.com', '13e665f795204a186dd4e7c9286875b4', 'viewer', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `communities`
--
ALTER TABLE `communities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `distributions`
--
ALTER TABLE `distributions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`) COMMENT 'autoincrement';

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `communities`
--
ALTER TABLE `communities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `distributions`
--
ALTER TABLE `distributions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
