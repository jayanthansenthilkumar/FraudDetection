-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 19, 2026 at 02:45 AM
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
-- Database: `fraud`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `account_type` enum('savings','checking','credit','business') DEFAULT 'savings',
  `balance` decimal(15,2) DEFAULT 0.00,
  `credit_limit` decimal(15,2) DEFAULT 0.00,
  `status` enum('active','frozen','closed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `user_id`, `account_number`, `account_type`, `balance`, `credit_limit`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, 'ACC0030001', 'savings', 15000.00, 0.00, 'active', '2026-02-19 01:24:28', '2026-02-19 01:24:28'),
(2, 3, 'ACC0030002', 'checking', 3500.00, 0.00, 'active', '2026-02-19 01:24:28', '2026-02-19 01:24:28');

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `details`, `ip_address`, `created_at`) VALUES
(1, 1, 'login', NULL, NULL, 'User logged in', '::1', '2026-02-19 01:24:50'),
(2, 1, 'logout', NULL, NULL, 'User logged out', '::1', '2026-02-19 01:25:40'),
(3, 2, 'login', NULL, NULL, 'User logged in', '::1', '2026-02-19 01:25:42'),
(4, 2, 'update_alert', 'fraud_alert', 1, 'Status changed to: investigating', '::1', '2026-02-19 01:25:58'),
(5, 2, 'logout', NULL, NULL, 'User logged out', '::1', '2026-02-19 01:26:06'),
(6, 3, 'login', NULL, NULL, 'User logged in', '::1', '2026-02-19 01:26:08'),
(7, 3, 'logout', NULL, NULL, 'User logged out', '::1', '2026-02-19 01:27:11'),
(8, 3, 'login', NULL, NULL, 'User logged in', '::1', '2026-02-19 01:27:42'),
(9, 3, 'create_transaction', 'transaction', 6, 'Amount: 22222, Type: withdrawal, Status: flagged, Fraud Score: 60', '::1', '2026-02-19 01:28:03'),
(10, 3, 'logout', NULL, NULL, 'User logged out', '::1', '2026-02-19 01:29:02');

-- --------------------------------------------------------

--
-- Table structure for table `fraud_alerts`
--

CREATE TABLE `fraud_alerts` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `alert_type` varchar(50) NOT NULL,
  `severity` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('new','investigating','resolved','dismissed') DEFAULT 'new',
  `description` text DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `resolved_by` int(11) DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `resolution_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fraud_alerts`
--

INSERT INTO `fraud_alerts` (`id`, `transaction_id`, `user_id`, `alert_type`, `severity`, `status`, `description`, `assigned_to`, `resolved_by`, `resolved_at`, `resolution_notes`, `created_at`) VALUES
(1, 4, 3, 'high_value', 'high', 'investigating', 'Large withdrawal of $8,500 from unusual location (Chicago)', 2, NULL, NULL, NULL, '2026-02-19 01:24:28'),
(2, 6, 3, 'high_value', 'high', 'new', 'High value transaction: $22222 exceeds $5000.00 threshold', NULL, NULL, NULL, NULL, '2026-02-19 01:28:03');

-- --------------------------------------------------------

--
-- Table structure for table `fraud_rules`
--

CREATE TABLE `fraud_rules` (
  `id` int(11) NOT NULL,
  `rule_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `rule_type` enum('amount','frequency','location','velocity','pattern') NOT NULL,
  `threshold_value` decimal(15,2) DEFAULT NULL,
  `score_weight` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fraud_rules`
--

INSERT INTO `fraud_rules` (`id`, `rule_name`, `description`, `rule_type`, `threshold_value`, `score_weight`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'High Value Transaction', 'Flags transactions exceeding the threshold amount', 'amount', 5000.00, 60, 1, NULL, '2026-02-19 01:24:28', '2026-02-19 01:24:28'),
(2, 'Rapid Transactions', 'Detects multiple transactions within a short time window', 'frequency', 3.00, 40, 1, NULL, '2026-02-19 01:24:28', '2026-02-19 01:24:28'),
(3, 'Location Mismatch', 'Flags transactions from unusual locations', 'location', 0.00, 50, 1, NULL, '2026-02-19 01:24:28', '2026-02-19 01:24:28'),
(4, 'Velocity Check', 'Detects sudden increase in transaction frequency', 'velocity', 10.00, 35, 1, NULL, '2026-02-19 01:24:28', '2026-02-19 01:24:28'),
(5, 'Large Transfer Pattern', 'Flags large transfers to new recipients', 'pattern', 10000.00, 55, 1, NULL, '2026-02-19 01:24:28', '2026-02-19 01:24:28');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `recipient_id`, `subject`, `message`, `is_read`, `created_at`) VALUES
(1, 3, 1, 'Hello', 'Hiii', 0, '2026-02-19 01:28:43');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
(1, 'system_name', 'FraudShield', '2026-02-19 01:24:28'),
(2, 'max_transaction_amount', '50000', '2026-02-19 01:24:28'),
(3, 'alert_threshold_score', '50', '2026-02-19 01:24:28'),
(4, 'session_timeout', '1800', '2026-02-19 01:24:28'),
(5, 'registration_open', '1', '2026-02-19 01:24:28'),
(6, 'high_value_threshold', '5000', '2026-02-19 01:24:28'),
(7, 'rapid_txn_window_minutes', '5', '2026-02-19 01:24:28'),
(8, 'rapid_txn_count', '3', '2026-02-19 01:24:28'),
(9, 'location_check_enabled', '1', '2026-02-19 01:24:28');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `type` enum('deposit','withdrawal','transfer','payment','refund') NOT NULL,
  `recipient_account` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','declined','flagged','reversed') DEFAULT 'pending',
  `location` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `device_info` varchar(255) DEFAULT NULL,
  `is_fraud` tinyint(1) DEFAULT 0,
  `fraud_score` decimal(5,2) DEFAULT 0.00,
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `account_id`, `amount`, `type`, `recipient_account`, `description`, `status`, `location`, `ip_address`, `device_info`, `is_fraud`, `fraud_score`, `reviewed_by`, `reviewed_at`, `review_notes`, `created_at`) VALUES
(1, 1, 2500.00, 'deposit', NULL, 'Salary deposit', 'approved', 'New York', NULL, NULL, 0, 0.00, NULL, NULL, NULL, '2026-02-19 01:24:28'),
(2, 1, 75.50, 'withdrawal', NULL, 'ATM withdrawal', 'approved', 'New York', NULL, NULL, 0, 0.00, NULL, NULL, NULL, '2026-02-19 01:24:28'),
(3, 1, 1200.00, 'transfer', 'ACC0040001', 'Rent payment', 'approved', 'New York', NULL, NULL, 0, 15.00, NULL, NULL, NULL, '2026-02-19 01:24:28'),
(4, 1, 8500.00, 'withdrawal', NULL, 'Large cash withdrawal', 'flagged', 'Chicago', NULL, NULL, 1, 75.00, NULL, NULL, NULL, '2026-02-19 01:24:28'),
(5, 1, 45.99, 'payment', NULL, 'Online purchase', 'approved', 'New York', NULL, NULL, 0, 0.00, NULL, NULL, NULL, '2026-02-19 01:24:28'),
(6, 2, 22222.00, 'withdrawal', '', '', 'flagged', 'New York', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, 60.00, NULL, NULL, NULL, '2026-02-19 01:28:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','analyst','customer') DEFAULT 'customer',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `phone`, `address`, `status`, `created_at`, `updated_at`) VALUES
(1, 'System Admin', 'admin', 'admin@fraudshield.com', 'admin123', 'admin', NULL, NULL, 'active', '2026-02-19 01:24:28', '2026-02-19 01:24:28'),
(2, 'Sarah Analyst', 'analyst', 'analyst@fraudshield.com', 'analyst123', 'analyst', NULL, NULL, 'active', '2026-02-19 01:24:28', '2026-02-19 01:24:28'),
(3, 'John Doe', 'johndoe', 'john@example.com', 'user123', 'customer', '+1-555-0123', '123 Main St, New York, NY', 'active', '2026-02-19 01:24:28', '2026-02-19 01:24:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_number` (`account_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `fraud_alerts`
--
ALTER TABLE `fraud_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `resolved_by` (`resolved_by`);

--
-- Indexes for table `fraud_rules`
--
ALTER TABLE `fraud_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

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
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `fraud_alerts`
--
ALTER TABLE `fraud_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `fraud_rules`
--
ALTER TABLE `fraud_rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fraud_alerts`
--
ALTER TABLE `fraud_alerts`
  ADD CONSTRAINT `fraud_alerts_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fraud_alerts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fraud_alerts_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fraud_alerts_ibfk_4` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fraud_rules`
--
ALTER TABLE `fraud_rules`
  ADD CONSTRAINT `fraud_rules_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
