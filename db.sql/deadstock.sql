-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 08, 2024 at 09:29 AM
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
-- Database: `deadstock`
--

-- --------------------------------------------------------

--
-- Table structure for table `sellers`
--

CREATE TABLE `sellers` (
  `seller_id` int(11) NOT NULL,
  `seller_name` varchar(100) NOT NULL,
  `seller_cname` varchar(100) NOT NULL,
  `seller_email` varchar(100) NOT NULL,
  `seller_phone` varchar(15) NOT NULL,
  `seller_gst` varchar(15) NOT NULL,
  `seller_address` text NOT NULL,
  `seller_state` varchar(50) NOT NULL,
  `seller_city` varchar(50) NOT NULL,
  `seller_zipcode` varchar(10) NOT NULL,
  `seller_password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `seller_status` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sellers`
--

INSERT INTO `sellers` (`seller_id`, `seller_name`, `seller_cname`, `seller_email`, `seller_phone`, `seller_gst`, `seller_address`, `seller_state`, `seller_city`, `seller_zipcode`, `seller_password`, `created_at`, `seller_status`) VALUES
(1, 'TAMIL SELVAN V', 'VTS', 'mailtotharun23@gmail.com', '9597049879', '123123', '4/97, Sullerumbu naalroad, Sullerumbu(post), Vedasandur, Dindigul.', 'Tamil Nadu', 'Dindigul', '624710', '$2y$10$nHq8Rn/WjuMiiSjKycPAS.c05Vj6K6STifkcC6oCB1cZw3OKzleKi', '2024-11-08 06:29:45', 0),
(2, 'TAMIL SELVAN V', 'VTS', 'mailto23@gmail.com', '9597049879', '123123', '4/97, Sullerumbu naalroad, Sullerumbu(post), Vedasandur, Dindigul.', 'Tamil Nadu', 'Dindigul', '624710', '$2y$10$bBQCEDdd9C783tRiILTv2uV5PwkLFsKmGdnZACcVNAClYiFX9Ex7q', '2024-11-08 06:35:17', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_gst` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `phone_number`, `email`, `password`, `user_gst`, `created_at`) VALUES
(6, 'TAMIL', '1234567890', 'TH@mm.bb', '$2y$10$tj6EZTOvNTR2h6Xed7AoDe7l23pXGUYc/ngxpIRCsb0bRobtTyjTi', '', '2024-11-08 06:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `user_login`
--

CREATE TABLE `user_login` (
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_password` varchar(100) NOT NULL,
  `user_role` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_login`
--

INSERT INTO `user_login` (`user_name`, `user_email`, `user_password`, `user_role`) VALUES
('TAMIL', 'TH@mm.bb', '$2y$10$tj6EZTOvNTR2h6Xed7AoDe7l23pXGUYc/ngxpIRCsb0bRobtTyjTi', 'user'),
('TAMIL SELVAN V', 'mailto23@gmail.com', '$2y$10$bBQCEDdd9C783tRiILTv2uV5PwkLFsKmGdnZACcVNAClYiFX9Ex7q', 'seller');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sellers`
--
ALTER TABLE `sellers`
  ADD PRIMARY KEY (`seller_id`),
  ADD UNIQUE KEY `seller_email` (`seller_email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sellers`
--
ALTER TABLE `sellers`
  MODIFY `seller_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
