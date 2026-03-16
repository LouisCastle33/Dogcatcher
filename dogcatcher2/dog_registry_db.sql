-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 16, 2026 at 09:59 AM
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
-- Database: `dog_registry_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_dogs`
--

CREATE TABLE `tbl_dogs` (
  `dog_id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `tag_serial` varchar(20) NOT NULL,
  `dog_name` varchar(50) DEFAULT NULL,
  `breed` varchar(50) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `sex` enum('Male','Female') DEFAULT 'Male',
  `last_rabies_vax` date DEFAULT NULL,
  `dog_photo` varchar(255) DEFAULT 'default_dog.png',
  `status` enum('Home','Impounded','Missing') DEFAULT 'Home',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_sync` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_dogs`
--

INSERT INTO `tbl_dogs` (`dog_id`, `owner_id`, `tag_serial`, `dog_name`, `breed`, `color`, `sex`, `last_rabies_vax`, `dog_photo`, `status`, `updated_at`, `last_sync`) VALUES
(2, 2, 'BAS-2026-0002', 'Cooper', 'Shih Tzu/Pomeranian', NULL, 'Male', NULL, 'dog_1773649316.jpg', 'Home', '2026-03-16 08:21:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_impound_logs`
--

CREATE TABLE `tbl_impound_logs` (
  `log_id` int(11) NOT NULL,
  `dog_id` int(11) DEFAULT NULL,
  `action_taken` varchar(50) DEFAULT NULL,
  `catcher_name` varchar(100) DEFAULT NULL,
  `gps_location` varchar(100) DEFAULT NULL,
  `log_timestamp` datetime DEFAULT current_timestamp(),
  `is_synced` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_owners`
--

CREATE TABLE `tbl_owners` (
  `owner_id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `barangay` varchar(50) NOT NULL,
  `contact_number` varchar(15) NOT NULL,
  `owner_photo` varchar(255) DEFAULT 'default_owner.png',
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_owners`
--

INSERT INTO `tbl_owners` (`owner_id`, `fullname`, `barangay`, `contact_number`, `owner_photo`, `address`, `created_at`) VALUES
(2, 'Louis Castle', 'Kaychanarianan', '09615431165', 'default_owner.png', NULL, '2026-03-16 08:21:56');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Catcher') DEFAULT 'Catcher',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_dogs`
--
ALTER TABLE `tbl_dogs`
  ADD PRIMARY KEY (`dog_id`),
  ADD UNIQUE KEY `tag_serial` (`tag_serial`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `tbl_impound_logs`
--
ALTER TABLE `tbl_impound_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `dog_id` (`dog_id`);

--
-- Indexes for table `tbl_owners`
--
ALTER TABLE `tbl_owners`
  ADD PRIMARY KEY (`owner_id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_dogs`
--
ALTER TABLE `tbl_dogs`
  MODIFY `dog_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_impound_logs`
--
ALTER TABLE `tbl_impound_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_owners`
--
ALTER TABLE `tbl_owners`
  MODIFY `owner_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_dogs`
--
ALTER TABLE `tbl_dogs`
  ADD CONSTRAINT `tbl_dogs_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `tbl_owners` (`owner_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_impound_logs`
--
ALTER TABLE `tbl_impound_logs`
  ADD CONSTRAINT `tbl_impound_logs_ibfk_1` FOREIGN KEY (`dog_id`) REFERENCES `tbl_dogs` (`dog_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
