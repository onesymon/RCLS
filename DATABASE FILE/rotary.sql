-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 31, 2025 at 05:05 AM
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
-- Database: `rotary`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` enum('insert','update','delete') NOT NULL,
  `table_name` varchar(255) NOT NULL,
  `record_id` int(11) NOT NULL,
  `changes` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `seen` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `club_attendances`
--

CREATE TABLE `club_attendances` (
  `id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `category` enum('Club Project','Club Event') NOT NULL,
  `activity_id` int(11) NOT NULL,
  `attendance_date` datetime NOT NULL DEFAULT current_timestamp(),
  `status` enum('Present','Absent','Late') NOT NULL DEFAULT 'Present',
  `remarks` varchar(255) DEFAULT NULL,
  `encoded_by` int(11) NOT NULL,
  `encoded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_attendances`
--

INSERT INTO `club_attendances` (`id`, `member_id`, `category`, `activity_id`, `attendance_date`, `status`, `remarks`, `encoded_by`, `encoded_at`) VALUES
(11, 23, 'Club Event', 2, '2025-05-08 09:26:04', 'Present', '', 100, '2025-05-08 01:26:04'),
(13, 24, 'Club Event', 8, '2025-05-16 11:38:24', 'Absent', '', 100, '2025-05-16 03:38:24'),
(14, 26, 'Club Event', 2, '2025-05-16 11:40:58', 'Late', '', 100, '2025-05-16 03:40:58'),
(15, 30, 'Club Project', 13, '2025-05-17 12:38:36', 'Present', '', 100, '2025-05-17 04:38:36'),
(18, 18, 'Club Project', 10, '2025-05-28 01:07:29', 'Present', NULL, 23, '2025-05-27 17:07:29'),
(19, 30, 'Club Project', 10, '2025-05-28 01:07:29', 'Present', NULL, 23, '2025-05-27 17:07:29'),
(20, 23, 'Club Project', 10, '2025-05-28 01:07:29', 'Present', NULL, 23, '2025-05-27 17:07:29'),
(21, 26, 'Club Project', 10, '2025-05-28 01:07:29', 'Present', NULL, 23, '2025-05-27 17:07:29'),
(22, 17, 'Club Event', 8, '2025-05-28 01:38:15', 'Present', NULL, 23, '2025-05-27 17:38:15'),
(23, 18, 'Club Event', 8, '2025-05-28 01:56:05', 'Present', NULL, 23, '2025-05-27 17:56:05'),
(24, 30, 'Club Event', 8, '2025-05-28 01:56:05', 'Present', NULL, 23, '2025-05-27 17:56:05'),
(25, 23, 'Club Event', 8, '2025-05-28 01:56:05', 'Present', NULL, 23, '2025-05-27 17:56:05'),
(26, 26, 'Club Event', 8, '2025-05-28 01:56:05', 'Present', NULL, 23, '2025-05-27 17:56:05'),
(27, 22, 'Club Event', 8, '2025-05-28 01:56:05', 'Present', NULL, 23, '2025-05-27 17:56:05'),
(28, 31, 'Club Event', 8, '2025-05-28 01:56:05', 'Absent', NULL, 23, '2025-05-27 17:56:05'),
(29, 24, 'Club Event', 8, '2025-05-28 01:56:05', 'Absent', NULL, 23, '2025-05-27 17:56:05'),
(30, 20, 'Club Event', 8, '2025-05-28 01:56:05', 'Absent', NULL, 23, '2025-05-27 17:56:05'),
(31, 39, 'Club Event', 8, '2025-05-28 01:56:05', 'Absent', NULL, 23, '2025-05-27 17:56:05'),
(32, 17, 'Club Event', 8, '2025-05-28 01:56:05', 'Absent', NULL, 23, '2025-05-27 17:56:05'),
(33, 18, 'Club Project', 10, '2025-05-28 02:14:47', 'Late', NULL, 23, '2025-05-27 18:14:47'),
(34, 30, 'Club Project', 10, '2025-05-28 02:14:47', 'Late', NULL, 23, '2025-05-27 18:14:47'),
(35, 23, 'Club Project', 10, '2025-05-28 02:14:47', 'Late', NULL, 23, '2025-05-27 18:14:47'),
(36, 26, 'Club Project', 10, '2025-05-28 02:14:47', 'Late', NULL, 23, '2025-05-27 18:14:47'),
(37, 22, 'Club Project', 10, '2025-05-28 02:14:47', 'Late', NULL, 23, '2025-05-27 18:14:47'),
(38, 31, 'Club Project', 10, '2025-05-28 02:14:47', 'Late', NULL, 23, '2025-05-27 18:14:47'),
(39, 24, 'Club Project', 10, '2025-05-28 02:14:47', 'Late', NULL, 23, '2025-05-27 18:14:47'),
(40, 20, 'Club Project', 10, '2025-05-28 02:14:47', 'Late', NULL, 23, '2025-05-27 18:14:47'),
(41, 39, 'Club Project', 10, '2025-05-28 02:14:47', 'Late', NULL, 23, '2025-05-27 18:14:47'),
(42, 17, 'Club Project', 10, '2025-05-28 02:14:47', 'Late', NULL, 23, '2025-05-27 18:14:47'),
(43, 18, 'Club Project', 10, '2025-05-28 02:30:49', 'Present', NULL, 23, '2025-05-27 18:30:49'),
(44, 30, 'Club Project', 10, '2025-05-28 02:30:49', 'Present', NULL, 23, '2025-05-27 18:30:49'),
(45, 23, 'Club Project', 10, '2025-05-28 02:30:49', 'Present', NULL, 23, '2025-05-27 18:30:49'),
(46, 26, 'Club Project', 10, '2025-05-28 02:30:49', 'Present', NULL, 23, '2025-05-27 18:30:49'),
(47, 22, 'Club Project', 10, '2025-05-28 02:30:49', 'Present', NULL, 23, '2025-05-27 18:30:49'),
(48, 31, 'Club Project', 10, '2025-05-28 02:30:49', 'Present', NULL, 23, '2025-05-27 18:30:49'),
(49, 24, 'Club Project', 10, '2025-05-28 02:30:49', 'Present', NULL, 23, '2025-05-27 18:30:49'),
(50, 20, 'Club Project', 10, '2025-05-28 02:30:49', 'Late', NULL, 23, '2025-05-27 18:30:49'),
(51, 39, 'Club Project', 10, '2025-05-28 02:30:49', 'Present', NULL, 23, '2025-05-27 18:30:49'),
(52, 17, 'Club Project', 10, '2025-05-28 02:30:49', 'Present', NULL, 23, '2025-05-27 18:30:49'),
(53, 18, 'Club Event', 8, '2025-05-28 02:31:45', 'Present', NULL, 23, '2025-05-27 18:31:45'),
(54, 30, 'Club Event', 8, '2025-05-28 02:31:45', 'Present', NULL, 23, '2025-05-27 18:31:45'),
(55, 23, 'Club Event', 8, '2025-05-28 02:31:45', 'Present', NULL, 23, '2025-05-27 18:31:45'),
(56, 26, 'Club Event', 8, '2025-05-28 02:31:45', 'Present', NULL, 23, '2025-05-27 18:31:45'),
(57, 22, 'Club Event', 8, '2025-05-28 02:31:45', 'Present', NULL, 23, '2025-05-27 18:31:45'),
(58, 31, 'Club Event', 8, '2025-05-28 02:31:45', 'Present', NULL, 23, '2025-05-27 18:31:45'),
(59, 24, 'Club Event', 8, '2025-05-28 02:31:45', 'Present', NULL, 23, '2025-05-27 18:31:45'),
(60, 20, 'Club Event', 8, '2025-05-28 02:31:45', 'Present', NULL, 23, '2025-05-27 18:31:45'),
(61, 39, 'Club Event', 8, '2025-05-28 02:31:45', 'Present', NULL, 23, '2025-05-27 18:31:45'),
(62, 17, 'Club Event', 8, '2025-05-28 02:31:45', 'Present', NULL, 23, '2025-05-27 18:31:45'),
(63, 18, 'Club Event', 11, '2025-05-28 02:32:14', 'Late', NULL, 23, '2025-05-27 18:32:14'),
(64, 30, 'Club Event', 11, '2025-05-28 02:32:14', 'Late', NULL, 23, '2025-05-27 18:32:14'),
(65, 23, 'Club Event', 11, '2025-05-28 02:32:14', 'Late', NULL, 23, '2025-05-27 18:32:14'),
(66, 26, 'Club Event', 11, '2025-05-28 02:32:14', 'Late', NULL, 23, '2025-05-27 18:32:14'),
(67, 22, 'Club Event', 11, '2025-05-28 02:32:14', 'Late', NULL, 23, '2025-05-27 18:32:14'),
(68, 31, 'Club Event', 11, '2025-05-28 02:32:14', 'Late', NULL, 23, '2025-05-27 18:32:14'),
(69, 24, 'Club Event', 11, '2025-05-28 02:32:14', 'Late', NULL, 23, '2025-05-27 18:32:14'),
(70, 20, 'Club Event', 11, '2025-05-28 02:32:14', 'Late', NULL, 23, '2025-05-27 18:32:14'),
(71, 39, 'Club Event', 11, '2025-05-28 02:32:14', 'Late', NULL, 23, '2025-05-27 18:32:14'),
(72, 17, 'Club Event', 11, '2025-05-28 02:32:14', 'Late', NULL, 23, '2025-05-27 18:32:14'),
(73, 18, 'Club Project', 10, '2025-05-28 02:33:05', 'Absent', NULL, 23, '2025-05-27 18:33:05'),
(74, 30, 'Club Project', 10, '2025-05-28 02:33:05', 'Absent', NULL, 23, '2025-05-27 18:33:05'),
(75, 23, 'Club Project', 10, '2025-05-28 02:33:05', 'Absent', NULL, 23, '2025-05-27 18:33:05'),
(76, 26, 'Club Project', 10, '2025-05-28 02:33:05', 'Absent', NULL, 23, '2025-05-27 18:33:05'),
(77, 22, 'Club Project', 10, '2025-05-28 02:33:05', 'Absent', NULL, 23, '2025-05-27 18:33:05'),
(78, 31, 'Club Project', 10, '2025-05-28 02:33:05', 'Absent', NULL, 23, '2025-05-27 18:33:05'),
(79, 24, 'Club Project', 10, '2025-05-28 02:33:05', 'Absent', NULL, 23, '2025-05-27 18:33:05'),
(80, 20, 'Club Project', 10, '2025-05-28 02:33:05', 'Absent', NULL, 23, '2025-05-27 18:33:05'),
(81, 39, 'Club Project', 10, '2025-05-28 02:33:05', 'Absent', NULL, 23, '2025-05-27 18:33:05'),
(82, 17, 'Club Project', 10, '2025-05-28 02:33:05', 'Absent', NULL, 23, '2025-05-27 18:33:05'),
(83, 18, 'Club Project', 10, '2025-05-28 02:42:32', 'Absent', NULL, 23, '2025-05-27 18:42:32'),
(84, 30, 'Club Project', 10, '2025-05-28 02:42:32', 'Absent', NULL, 23, '2025-05-27 18:42:32'),
(85, 23, 'Club Project', 10, '2025-05-28 02:42:32', 'Absent', NULL, 23, '2025-05-27 18:42:32'),
(86, 26, 'Club Project', 10, '2025-05-28 02:42:32', 'Absent', NULL, 23, '2025-05-27 18:42:32'),
(87, 22, 'Club Project', 10, '2025-05-28 02:42:32', 'Absent', NULL, 23, '2025-05-27 18:42:32'),
(88, 31, 'Club Project', 10, '2025-05-28 02:42:32', 'Absent', NULL, 23, '2025-05-27 18:42:32'),
(89, 24, 'Club Project', 10, '2025-05-28 02:42:32', 'Absent', NULL, 23, '2025-05-27 18:42:32'),
(90, 20, 'Club Project', 10, '2025-05-28 02:42:32', 'Absent', NULL, 23, '2025-05-27 18:42:32'),
(91, 39, 'Club Project', 10, '2025-05-28 02:42:32', 'Present', NULL, 23, '2025-05-27 18:42:32'),
(92, 17, 'Club Project', 10, '2025-05-28 02:42:32', 'Absent', NULL, 23, '2025-05-27 18:42:32'),
(93, 18, 'Club Event', 8, '2025-05-28 02:44:01', 'Absent', NULL, 23, '2025-05-27 18:44:01'),
(94, 30, 'Club Event', 8, '2025-05-28 02:44:01', 'Absent', NULL, 23, '2025-05-27 18:44:01'),
(95, 23, 'Club Event', 8, '2025-05-28 02:44:01', 'Absent', NULL, 23, '2025-05-27 18:44:01'),
(96, 26, 'Club Event', 8, '2025-05-28 02:44:01', 'Absent', NULL, 23, '2025-05-27 18:44:01'),
(97, 22, 'Club Event', 8, '2025-05-28 02:44:01', 'Absent', NULL, 23, '2025-05-27 18:44:01'),
(98, 31, 'Club Event', 8, '2025-05-28 02:44:01', 'Absent', NULL, 23, '2025-05-27 18:44:01'),
(99, 24, 'Club Event', 8, '2025-05-28 02:44:01', 'Absent', NULL, 23, '2025-05-27 18:44:01'),
(100, 20, 'Club Event', 8, '2025-05-28 02:44:01', 'Absent', NULL, 23, '2025-05-27 18:44:01'),
(101, 39, 'Club Event', 8, '2025-05-28 02:44:01', 'Absent', NULL, 23, '2025-05-27 18:44:01'),
(102, 17, 'Club Event', 8, '2025-05-28 02:44:01', 'Absent', NULL, 23, '2025-05-27 18:44:01'),
(103, 18, 'Club Project', 10, '2025-05-28 06:06:38', 'Late', NULL, 23, '2025-05-27 22:06:38'),
(104, 30, 'Club Project', 10, '2025-05-28 06:06:38', 'Late', NULL, 23, '2025-05-27 22:06:38'),
(105, 23, 'Club Project', 10, '2025-05-28 06:06:38', 'Late', NULL, 23, '2025-05-27 22:06:38'),
(106, 26, 'Club Project', 10, '2025-05-28 06:06:38', 'Late', NULL, 23, '2025-05-27 22:06:38'),
(107, 22, 'Club Project', 10, '2025-05-28 06:06:38', 'Late', NULL, 23, '2025-05-27 22:06:38'),
(108, 31, 'Club Project', 10, '2025-05-28 06:06:38', 'Late', NULL, 23, '2025-05-27 22:06:38'),
(109, 24, 'Club Project', 10, '2025-05-28 06:06:38', 'Late', NULL, 23, '2025-05-27 22:06:38'),
(110, 20, 'Club Project', 10, '2025-05-28 06:06:38', 'Late', NULL, 23, '2025-05-27 22:06:38'),
(111, 39, 'Club Project', 10, '2025-05-28 06:06:38', 'Late', NULL, 23, '2025-05-27 22:06:38'),
(112, 17, 'Club Project', 10, '2025-05-28 06:06:38', 'Late', NULL, 23, '2025-05-27 22:06:38'),
(113, 18, 'Club Project', 10, '2025-05-28 07:41:19', 'Present', NULL, 24, '2025-05-27 23:41:19'),
(114, 30, 'Club Project', 10, '2025-05-28 07:41:19', 'Present', NULL, 24, '2025-05-27 23:41:19'),
(115, 23, 'Club Project', 10, '2025-05-28 07:41:19', 'Present', NULL, 24, '2025-05-27 23:41:19'),
(116, 26, 'Club Project', 10, '2025-05-28 07:41:19', 'Present', NULL, 24, '2025-05-27 23:41:19'),
(117, 22, 'Club Project', 10, '2025-05-28 07:41:19', 'Present', NULL, 24, '2025-05-27 23:41:19'),
(118, 31, 'Club Project', 10, '2025-05-28 07:41:19', 'Present', NULL, 24, '2025-05-27 23:41:19'),
(119, 24, 'Club Project', 10, '2025-05-28 07:41:19', 'Present', NULL, 24, '2025-05-27 23:41:19'),
(120, 20, 'Club Project', 10, '2025-05-28 07:41:19', 'Present', NULL, 24, '2025-05-27 23:41:19'),
(121, 39, 'Club Project', 10, '2025-05-28 07:41:19', 'Present', NULL, 24, '2025-05-27 23:41:19'),
(122, 17, 'Club Project', 10, '2025-05-28 07:41:19', 'Present', NULL, 24, '2025-05-27 23:41:19');

-- --------------------------------------------------------

--
-- Table structure for table `club_events`
--

CREATE TABLE `club_events` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `target_funding` decimal(10,2) NOT NULL DEFAULT 0.00,
  `current_funding` decimal(10,2) NOT NULL DEFAULT 0.00,
  `remaining_funding` decimal(10,2) GENERATED ALWAYS AS (`target_funding` - `current_funding`) STORED,
  `event_date` date NOT NULL,
  `event_time` time DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `status` enum('Upcoming','Ongoing','Completed') DEFAULT 'Upcoming',
  `encoded_by` int(11) NOT NULL,
  `encoded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_events`
--

INSERT INTO `club_events` (`id`, `title`, `description`, `target_funding`, `current_funding`, `event_date`, `event_time`, `location`, `status`, `encoded_by`, `encoded_at`) VALUES
(2, 'Maintenance Meeting', 'hehe', 1000.00, 0.00, '2025-04-27', '13:00:00', 'Lipa', 'Ongoing', 16, '2025-04-27 10:43:23'),
(8, 'Christmas  Party', 'xmas', 1000.00, 0.00, '2025-05-15', '12:00:00', 'Lipa', 'Ongoing', 100, '2025-05-15 02:35:50'),
(11, 'induction', 'as', 1001.00, 0.00, '2025-05-05', '00:00:00', '', 'Upcoming', 23, '2025-05-25 14:57:21');

-- --------------------------------------------------------

--
-- Table structure for table `club_operations`
--

CREATE TABLE `club_operations` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `paid_to` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('Paid','Unpaid') DEFAULT 'Unpaid',
  `encoded_by` int(11) NOT NULL,
  `encoded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_operations`
--

INSERT INTO `club_operations` (`id`, `category`, `amount`, `payment_date`, `paid_to`, `notes`, `status`, `encoded_by`, `encoded_at`) VALUES
(9, 'Internet Subscription', 1000.00, '2025-05-04', 'IP', '', 'Unpaid', 23, '2025-05-04 00:18:16');

-- --------------------------------------------------------

--
-- Table structure for table `club_position`
--

CREATE TABLE `club_position` (
  `id` int(11) NOT NULL,
  `position_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_position`
--

INSERT INTO `club_position` (`id`, `position_name`) VALUES
(1, 'President'),
(2, 'Vice President'),
(3, 'Secretary'),
(4, 'Treasurer'),
(5, 'Auditor'),
(6, 'Member'),
(100, 'superadmin'),
(104, 'chairman');

-- --------------------------------------------------------

--
-- Table structure for table `club_projects`
--

CREATE TABLE `club_projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `type` enum('project','event','community service','other') NOT NULL,
  `target_funding` decimal(10,2) DEFAULT 0.00,
  `current_funding` decimal(10,2) DEFAULT 0.00,
  `remaining_funding` decimal(10,2) GENERATED ALWAYS AS (`target_funding` - `current_funding`) STORED,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `status` enum('Planned','Ongoing','Completed') NOT NULL DEFAULT 'Planned',
  `encoded_by` int(11) NOT NULL,
  `encoded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_projects`
--

INSERT INTO `club_projects` (`id`, `title`, `description`, `type`, `target_funding`, `current_funding`, `start_date`, `end_date`, `location`, `status`, `encoded_by`, `encoded_at`, `updated_at`) VALUES
(10, 'Cleaning', 'cleaning', 'community service', 10000.00, 0.00, '2025-05-04', '2025-06-04', 'Lipa', 'Planned', 23, '2025-05-04 00:19:23', '2025-05-25 14:53:26'),
(13, 'Tree Planting', 'Tree Planting', 'community service', 1000.00, 0.00, '2025-05-15', NULL, 'Lipa', 'Completed', 23, '2025-05-15 03:17:09', '2025-05-17 07:34:33');

-- --------------------------------------------------------

--
-- Table structure for table `club_transactions`
--

CREATE TABLE `club_transactions` (
  `id` int(11) NOT NULL,
  `entry_type` enum('Income','Expense','Contribution') NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` int(11) DEFAULT NULL,
  `category` enum('Club Project','Club Event','Club Operation','Club Fund') NOT NULL,
  `activity_id` int(11) NOT NULL,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `remarks` varchar(255) DEFAULT NULL,
  `reference_number` varchar(100) DEFAULT NULL,
  `encoded_by` varchar(255) NOT NULL,
  `payment_status` enum('Paid','Rejected','Pending') NOT NULL DEFAULT 'Pending',
  `external_source` varchar(255) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_transactions`
--

INSERT INTO `club_transactions` (`id`, `entry_type`, `member_id`, `amount`, `payment_method`, `category`, `activity_id`, `transaction_date`, `remarks`, `reference_number`, `encoded_by`, `payment_status`, `external_source`, `project_id`) VALUES
(91, 'Income', 22, 123.00, 2, 'Club Project', 10, '2025-05-28 07:54:13', '', 'BT-22838990', '24', 'Paid', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `club_wallet_categories`
--

CREATE TABLE `club_wallet_categories` (
  `id` int(11) NOT NULL,
  `fund_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `current_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(10) DEFAULT 'PHP',
  `status` varchar(20) DEFAULT 'Active',
  `owner` varchar(100) DEFAULT NULL,
  `encoded_by` int(11) NOT NULL,
  `encoded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `club_wallet_categories`
--

INSERT INTO `club_wallet_categories` (`id`, `fund_name`, `description`, `current_balance`, `currency`, `status`, `owner`, `encoded_by`, `encoded_at`) VALUES
(2, 'Club Donations', 'Overall Donations', 1500.00, 'PHP', 'Active', '', 100, '2025-05-04 00:55:09'),
(4, 'Club Memberships', 'all members membership dues', 20000.00, 'PHP', 'Active', '', 100, '2025-05-15 00:54:29');

-- --------------------------------------------------------

--
-- Table structure for table `club_wallet_transactions`
--

CREATE TABLE `club_wallet_transactions` (
  `id` int(11) NOT NULL,
  `fund_id` int(11) NOT NULL,
  `transaction_type` enum('deposit','withdrawal') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `remarks` varchar(255) DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `encoded_by` int(11) NOT NULL,
  `encoded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `fullname` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `gender` varchar(10) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `role` int(11) DEFAULT 6,
  `occupation` varchar(255) NOT NULL,
  `membership_number` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo` varchar(255) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `fullname`, `dob`, `gender`, `contact_number`, `email`, `address`, `role`, `occupation`, `membership_number`, `created_at`, `photo`, `expiry_date`, `password`) VALUES
(17, 'recto', '0909-09-09', 'Female', '0909', 'recto@gmail.com', 'spc', 5, 'student', 'CA-892267', '2025-04-11 17:05:06', 'default.jpg', NULL, '$2y$10$DnO9B4EA6Lrbd8yk2p9USOxNZk35LhhTmgITSduiLlNpcsr.yocc2'),
(18, 'bonc', '0001-01-01', 'Male', '22222', 'bonc@gmail.com', 'country', 6, 'country', 'CA-566295', '2025-04-12 05:00:11', 'default.jpg', NULL, '$2y$10$gAJcvK2ISeuF5yBoLjMdBOcC.uqrZEHqVrYeKLfpvIMCxIg.sS7OS'),
(20, 'lilo', '0009-09-09', 'Male', '999', 'lilo@gmail.com', 'lilo', 6, 'lilo', 'CA-263712', '2025-04-12 07:15:16', 'default.jpg', NULL, '$2y$10$C0mQwV46pZsdWfNGGRYKCeFuGr9lfoQ.0dNWSeC1Q6cwccHqnHZtu'),
(22, 'John Andrei Recto', '2004-05-08', 'Male', '09483749010', 'iyjeeee.recto@gmail.com', 'Bawi', 3, 'Developer', 'CA-307994', '2025-04-15 11:04:29', 'default.jpg', NULL, '$2y$10$cbkt5.EdpzZQYFYEplSLXe45NripMTtbaRU6UKN3yLn8fuclgZd/W'),
(23, 'Christian Lescanos', '2000-01-01', 'Male', '09945143251', 'lescano@gmail.com', 'Mataas na Kahoy', 3, 'Teacher', 'CA-793927', '2025-04-24 23:36:42', 'default.jpg', NULL, '$2y$10$yPsUvjcVFpcGsGV4s9uvgegWM3uWgKlvw86uRMm0C.gGRVZTfC/4G'),
(24, 'JV LIM', '2002-09-09', 'Male', '09412456499', 'jv@gmail.com', 'Lipa c', 1, 'Profss', 'CA-929817', '2025-04-25 07:55:24', 'default.jpg', NULL, '$2y$10$I5vjC4eTIZXBY7rSsH.vSeG1kxom9/eFDDIOdKWxuqLQdXbaezYU2'),
(26, 'jhed', '2003-01-09', 'Male', '123567', 'jhed@gmail.com', '1234', 6, '1234', 'CA-023029', '2025-04-29 07:06:31', 'Screenshot 2025-04-29 173730_1745919465.png', NULL, '$2y$10$eldx0QmkQyIKnBS5SFmYdOmyk6fSTtGtDQNBGTcGTH05umxAwuYTe'),
(30, 'Chano', '2025-05-17', 'Male', '09216106069', 'chano@gmail.com', 'Mataas na Kahoy', 6, 'Teacher', 'CA-274718', '2025-05-17 04:38:21', 'default.jpg', NULL, '$2y$10$JK/VromZJ6hXSTaZQ1ttfu3K2yEG6XQc//Qfzdoxns0uRdHhPhKRa'),
(31, 'jude cornejo', '2001-01-09', 'Male', '09934101658', 'jude@gmail.com', 'lipa', 104, 'sudent', 'CA-572333', '2025-05-17 07:17:15', 'default.jpg', NULL, '$2y$10$Q3i5qq1zHtczOhR4kbLBROSPyoKysadnsUrGVeXNX4vosO/kQs4wa'),
(39, 'mon', '2003-01-01', 'Male', '09934101657', 'mon@gmail.com', 'san pablo city', 4, 'student', 'CA-133549', '2025-05-25 15:22:04', 'default.jpg', NULL, '$2y$10$R5mAUE4JOQwcVgf8jAa6Ju.vDaJrj4A.azKIPTstxfMrdqIAM5RAC'),
(40, 'Super Admin', '2000-01-01', 'Male', '09123456789', 'admin@gmail.com', 'N/A', 100, 'N/A', 'CA-665409', '2025-05-30 14:10:39', 'default.jpg', NULL, '$2y$10$A8iqLjAHBPBfbr4SLkotx.NdStbgZO6wA5KFsoMckXuzo.N4VbZfy'),
(43, 'neo', '2000-01-01', 'Male', '09999999999', 'neo@gmail.com', 'dfbn', 6, 'sd', 'CA-194364', '2025-05-30 15:40:34', 'default.jpg', NULL, '$2y$10$HvyKvkOKLQGzwRrkNagisueX9K4kViQSl2FB.NPni48LRrO0qPtxq');

-- --------------------------------------------------------

--
-- Table structure for table `member_points`
--

CREATE TABLE `member_points` (
  `id` int(11) NOT NULL,
  `points` int(11) DEFAULT 0,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `member_points`
--

INSERT INTO `member_points` (`id`, `points`, `description`) VALUES
(1, 1, 'attending an event'),
(2, 1, 'attending a project');

-- --------------------------------------------------------

--
-- Table structure for table `payment_method`
--

CREATE TABLE `payment_method` (
  `id` int(11) NOT NULL,
  `method_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_method`
--

INSERT INTO `payment_method` (`id`, `method_name`, `description`) VALUES
(1, 'Cash', NULL),
(2, 'Bank Transfer', NULL),
(3, 'Gcash', NULL),
(4, 'Maya', NULL),
(5, 'System', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `renew`
--

CREATE TABLE `renew` (
  `id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `renew_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `system_name` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `currency` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `system_name`, `logo`, `currency`) VALUES
(1, 'Rotary Club Lipa South', 'rotary.png', 'P');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `club_attendances`
--
ALTER TABLE `club_attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attendance_member` (`member_id`),
  ADD KEY `fk_attendance_encoded_by` (`encoded_by`);

--
-- Indexes for table `club_events`
--
ALTER TABLE `club_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `club_operations`
--
ALTER TABLE `club_operations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`encoded_by`);

--
-- Indexes for table `club_position`
--
ALTER TABLE `club_position`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `club_projects`
--
ALTER TABLE `club_projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`encoded_by`);

--
-- Indexes for table `club_transactions`
--
ALTER TABLE `club_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `fk_payment_method` (`payment_method`),
  ADD KEY `fk_project` (`project_id`);

--
-- Indexes for table `club_wallet_categories`
--
ALTER TABLE `club_wallet_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `club_wallet_transactions`
--
ALTER TABLE `club_wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fund_id` (`fund_id`),
  ADD KEY `transaction_id_ibfk` (`reference_id`),
  ADD KEY `member_id_ibfk` (`member_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_club_position` (`role`);

--
-- Indexes for table `member_points`
--
ALTER TABLE `member_points`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_method`
--
ALTER TABLE `payment_method`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `renew`
--
ALTER TABLE `renew`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `club_attendances`
--
ALTER TABLE `club_attendances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `club_events`
--
ALTER TABLE `club_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `club_operations`
--
ALTER TABLE `club_operations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `club_position`
--
ALTER TABLE `club_position`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `club_projects`
--
ALTER TABLE `club_projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `club_transactions`
--
ALTER TABLE `club_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- AUTO_INCREMENT for table `club_wallet_categories`
--
ALTER TABLE `club_wallet_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `club_wallet_transactions`
--
ALTER TABLE `club_wallet_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `member_points`
--
ALTER TABLE `member_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payment_method`
--
ALTER TABLE `payment_method`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `renew`
--
ALTER TABLE `renew`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `club_attendances`
--
ALTER TABLE `club_attendances`
  ADD CONSTRAINT `fk_attendance_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `club_operations`
--
ALTER TABLE `club_operations`
  ADD CONSTRAINT `club_operations_ibfk_1` FOREIGN KEY (`encoded_by`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `club_projects`
--
ALTER TABLE `club_projects`
  ADD CONSTRAINT `club_projects_ibfk_1` FOREIGN KEY (`encoded_by`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `club_transactions`
--
ALTER TABLE `club_transactions`
  ADD CONSTRAINT `club_transactions_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payment_method` FOREIGN KEY (`payment_method`) REFERENCES `payment_method` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_project` FOREIGN KEY (`project_id`) REFERENCES `club_projects` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `club_wallet_transactions`
--
ALTER TABLE `club_wallet_transactions`
  ADD CONSTRAINT `club_wallet_transactions_ibfk_1` FOREIGN KEY (`fund_id`) REFERENCES `club_wallet_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `member_id_ibfk` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaction_id_ibfk` FOREIGN KEY (`reference_id`) REFERENCES `club_transactions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `members`
--
ALTER TABLE `members`
  ADD CONSTRAINT `fk_club_position` FOREIGN KEY (`role`) REFERENCES `club_position` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `renew`
--
ALTER TABLE `renew`
  ADD CONSTRAINT `renew_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
