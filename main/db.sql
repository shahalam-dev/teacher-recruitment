-- Database Schema for Marketing Manager
-- Host: localhost    Database: mehedi

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `channels`
--

CREATE TABLE IF NOT EXISTS `channels` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `api_endpoint` varchar(255) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_channels_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `contact_lists`
--

CREATE TABLE IF NOT EXISTS `contact_lists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `list_name` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_lists_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `marketing_user_number`
--

CREATE TABLE IF NOT EXISTS `marketing_user_number` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(20) NOT NULL,
  `list_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_sent` tinyint(4) DEFAULT 0 COMMENT '0=pending, 1=success, 2=failed, 3=processing',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `list_id` (`list_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_numbers_list` FOREIGN KEY (`list_id`) REFERENCES `contact_lists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_numbers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `template_groups`
--

CREATE TABLE IF NOT EXISTS `template_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_groups_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `message_templates`
--

CREATE TABLE IF NOT EXISTS `message_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_templates_group` FOREIGN KEY (`group_id`) REFERENCES `template_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_templates_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `campaigns`
--

CREATE TABLE IF NOT EXISTS `campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_name` varchar(100) NOT NULL,
  `channel_id` int(11) NOT NULL,
  `list_id` int(11) NOT NULL,
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('running','completed','stopped') DEFAULT 'running',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `channel_id` (`channel_id`),
  KEY `list_id` (`list_id`),
  KEY `group_id` (`group_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_campaigns_channel` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_campaigns_list` FOREIGN KEY (`list_id`) REFERENCES `contact_lists` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_campaigns_group` FOREIGN KEY (`group_id`) REFERENCES `template_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_campaigns_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
