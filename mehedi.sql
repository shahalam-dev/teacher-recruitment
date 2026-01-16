-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: host.docker.internal:3306
-- Generation Time: Jan 16, 2026 at 12:30 AM
-- Server version: 12.1.2-MariaDB
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mehedi`
--

-- --------------------------------------------------------

--
-- Table structure for table `campaigns`
--

CREATE TABLE `campaigns` (
  `id` int(11) NOT NULL,
  `campaign_name` varchar(100) DEFAULT NULL,
  `channel_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `status` enum('pending','running','completed','paused') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `campaigns`
--

INSERT INTO `campaigns` (`id`, `campaign_name`, `channel_id`, `list_id`, `status`, `created_at`, `user_id`, `group_id`) VALUES
(2, 'test 15/jan/26', 1, 2, 'completed', '2026-01-14 22:29:11', 1, 2),
(19, 'ismail', 1, 7, 'completed', '2026-01-15 15:19:15', 1, 5),
(21, 'test sharif', 1, 8, 'running', '2026-01-15 22:17:51', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `channels`
--

CREATE TABLE `channels` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `api_endpoint` varchar(255) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `channels`
--

INSERT INTO `channels` (`id`, `name`, `api_endpoint`, `api_key`, `is_active`, `created_at`, `user_id`) VALUES
(1, 'channel teletalk (01580378787)', 'https://api.360messenger.com/v2/sendMessage', 'zd22fxF5YO8S78aDdv8UClWwXGXpOUxxvwN', 1, '2026-01-14 16:20:33', 1),
(3, 'channel grameen (01703794217)', 'https://api.360messenger.com/v2/sendMessage', '2FsWmM9kPhgH1MPVWUp70QjTeI5pie94y1M', 1, '2026-01-15 13:25:21', 1);

-- --------------------------------------------------------

--
-- Table structure for table `contact_lists`
--

CREATE TABLE `contact_lists` (
  `id` int(11) NOT NULL,
  `list_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_lists`
--

INSERT INTO `contact_lists` (`id`, `list_name`, `description`, `created_at`, `user_id`) VALUES
(2, 'ideal shool', NULL, '2026-01-14 20:48:09', 1),
(4, 'customer', NULL, '2026-01-15 11:00:33', 1),
(5, 'rayhan', NULL, '2026-01-15 13:27:20', 1),
(6, 'rayhan2', NULL, '2026-01-15 13:31:08', 1),
(7, 'ismail', NULL, '2026-01-15 15:18:25', 1),
(8, 'sharif', NULL, '2026-01-15 22:13:34', 1);

-- --------------------------------------------------------

--
-- Table structure for table `marketing_user_number`
--

CREATE TABLE `marketing_user_number` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `is_sent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `marketing_user_number`
--

INSERT INTO `marketing_user_number` (`id`, `user_id`, `list_id`, `phone_number`, `is_sent`, `created_at`) VALUES
(3, 1, 2, '8801303818165', 1, '2026-01-14 22:17:03'),
(4, 1, 2, '8801303818165', 1, '2026-01-14 22:17:03'),
(5, 1, 2, '8801303818165', 1, '2026-01-14 22:17:03'),
(6, 1, 2, '8801303818165', 1, '2026-01-14 22:17:03'),
(7, 1, 2, '8801303818165', 1, '2026-01-14 22:17:03'),
(8, 1, 2, '8801303818165', 1, '2026-01-14 22:17:03'),
(9, 1, 2, '8801303818165', 1, '2026-01-14 22:17:03'),
(10, 1, 2, '8801303818165', 1, '2026-01-14 22:17:03'),
(11, 1, 2, '8801303818165', 1, '2026-01-14 22:17:03'),
(12, 1, 2, '8801303818165', 1, '2026-01-14 22:17:03'),
(23, 1, 5, '8801124072733', 0, '2026-01-15 13:27:35'),
(24, 1, 5, '8801124072733', 0, '2026-01-15 13:27:35'),
(25, 1, 5, '8801124072733', 0, '2026-01-15 13:27:35'),
(26, 1, 5, '8801124072733', 0, '2026-01-15 13:27:35'),
(27, 1, 6, '8801343265403', 1, '2026-01-15 13:31:21'),
(28, 1, 6, '8801343265403', 1, '2026-01-15 13:31:21'),
(29, 1, 6, '8801343265403', 1, '2026-01-15 13:31:21'),
(30, 1, 6, '8801343265403', 1, '2026-01-15 13:31:21'),
(31, 1, 7, '8801872085773', 1, '2026-01-15 15:18:41'),
(32, 1, 7, '8801872085773', 1, '2026-01-15 15:18:41'),
(33, 1, 7, '8801872085773', 1, '2026-01-15 15:18:41'),
(34, 1, 7, '8801872085773', 1, '2026-01-15 15:18:41'),
(35, 1, 7, '8801872085773', 1, '2026-01-15 15:18:41'),
(36, 1, 7, '8801872085773', 1, '2026-01-15 15:18:41'),
(37, 1, 7, '8801872085773', 1, '2026-01-15 15:18:41'),
(38, 1, 7, '8801872085773', 1, '2026-01-15 15:18:41'),
(39, 1, 8, '8801303818165', 1, '2026-01-15 22:13:46'),
(40, 1, 8, '8801303818165', 1, '2026-01-15 22:13:46'),
(41, 1, 8, '8801303818165', 1, '2026-01-15 22:13:46'),
(42, 1, 8, '8801303818165', 0, '2026-01-15 22:13:46'),
(43, 1, 8, '8801303818165', 0, '2026-01-15 22:13:46'),
(44, 1, 8, '8801303818165', 0, '2026-01-15 22:13:46'),
(45, 1, 8, '8801303818165', 0, '2026-01-15 22:13:46'),
(46, 1, 8, '8801303818165', 0, '2026-01-15 22:13:46'),
(47, 1, 8, '8801303818165', 0, '2026-01-15 22:13:46'),
(48, 1, 8, '8801303818165', 0, '2026-01-15 22:13:46'),
(49, 1, 8, '8801303818165', 0, '2026-01-15 22:13:46');

-- --------------------------------------------------------

--
-- Table structure for table `message_templates`
--

CREATE TABLE `message_templates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `group_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `message_templates`
--

INSERT INTO `message_templates` (`id`, `user_id`, `content`, `created_at`, `group_id`) VALUES
(9, 1, 'msg 1', '2026-01-14 22:22:56', 2),
(10, 1, 'msg 2', '2026-01-14 22:23:03', 2),
(11, 1, 'msg 3', '2026-01-14 22:23:12', 2),
(12, 1, 'msg 4', '2026-01-14 22:23:18', 2),
(13, 1, 'msg 5', '2026-01-14 22:23:25', 2),
(14, 1, 'msg 6', '2026-01-15 00:22:32', 2),
(15, 1, 'msg 7', '2026-01-15 00:22:38', 2),
(16, 1, 'msg 8', '2026-01-15 00:22:42', 2),
(17, 1, 'msg 9', '2026-01-15 00:22:56', 2),
(18, 1, 'msg 10', '2026-01-15 00:23:04', 2),
(19, 1, 'inv 1', '2026-01-15 11:00:03', 3),
(20, 1, 'inv 2', '2026-01-15 11:00:06', 3),
(21, 1, 'msg 1', '2026-01-15 13:25:48', 4),
(22, 1, 'sdkfl 2', '2026-01-15 13:25:56', 4),
(23, 1, '35', '2026-01-15 13:26:01', 4),
(24, 1, 'kaha testy', '2026-01-15 15:17:07', 5),
(25, 1, 'k moja', '2026-01-15 15:17:15', 5);

-- --------------------------------------------------------

--
-- Table structure for table `template_groups`
--

CREATE TABLE `template_groups` (
  `id` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `template_groups`
--

INSERT INTO `template_groups` (`id`, `group_name`, `user_id`, `created_at`) VALUES
(2, 'test group', 1, '2026-01-14 22:19:02'),
(3, 'invoice', 1, '2026-01-15 10:59:55'),
(4, 'rayhan', 1, '2026-01-15 13:25:38'),
(5, 'khana', 1, '2026-01-15 15:16:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','manager','viewer') DEFAULT 'manager',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'admin', 'admin@example.com', '$2y$12$DrqmbZRAGd0UgVg4qiiAcu8AhC7WCHh0hk6qFMb62DfkFqy1PK6JC', 'admin', '2026-01-14 15:25:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `channel_id` (`channel_id`),
  ADD KEY `list_id` (`list_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `channels`
--
ALTER TABLE `channels`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `contact_lists`
--
ALTER TABLE `contact_lists`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `marketing_user_number`
--
ALTER TABLE `marketing_user_number`
  ADD PRIMARY KEY (`id`),
  ADD KEY `list_id` (`list_id`);

--
-- Indexes for table `message_templates`
--
ALTER TABLE `message_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_group_message` (`group_id`);

--
-- Indexes for table `template_groups`
--
ALTER TABLE `template_groups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `campaigns`
--
ALTER TABLE `campaigns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `channels`
--
ALTER TABLE `channels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_lists`
--
ALTER TABLE `contact_lists`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `marketing_user_number`
--
ALTER TABLE `marketing_user_number`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `message_templates`
--
ALTER TABLE `message_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `template_groups`
--
ALTER TABLE `template_groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `campaigns`
--
ALTER TABLE `campaigns`
  ADD CONSTRAINT `1` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`),
  ADD CONSTRAINT `2` FOREIGN KEY (`list_id`) REFERENCES `contact_lists` (`id`),
  ADD CONSTRAINT `4` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `channels`
--
ALTER TABLE `channels`
  ADD CONSTRAINT `1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contact_lists`
--
ALTER TABLE `contact_lists`
  ADD CONSTRAINT `1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `marketing_user_number`
--
ALTER TABLE `marketing_user_number`
  ADD CONSTRAINT `1` FOREIGN KEY (`list_id`) REFERENCES `contact_lists` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `message_templates`
--
ALTER TABLE `message_templates`
  ADD CONSTRAINT `fk_group_message` FOREIGN KEY (`group_id`) REFERENCES `template_groups` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
