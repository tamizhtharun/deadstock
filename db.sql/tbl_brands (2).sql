-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 14, 2024 at 05:35 PM
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
-- Table structure for table `tbl_brands`
--

CREATE TABLE `tbl_brands` (
  `brand_id` int(11) NOT NULL,
  `tcat_id` int(11) NOT NULL,
  `brand_name` varchar(255) NOT NULL,
  `brand_description` varchar(5000) NOT NULL,
  `brand_logo` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_brands`
--

INSERT INTO `tbl_brands` (`brand_id`, `tcat_id`, `brand_name`, `brand_description`, `brand_logo`) VALUES
(41, 10, 'Allied Machines and Engineering', '', 'brand-logo-41.png'),
(42, 10, 'Big Diashowa', '', 'brand-logo-42.png'),
(43, 10, 'Birla Precision Tooling ', '', 'brand-logo-43.jpg'),
(44, 10, 'Carmax', '', 'brand-logo-44.gif'),
(45, 10, 'Certizit', '', 'brand-logo-45.png'),
(46, 10, 'Dormar', '', 'brand-logo-46.png'),
(47, 10, 'Emuge', '', 'brand-logo-47.png'),
(48, 10, 'Haimer', '', 'brand-logo-48.jpg'),
(49, 10, 'Ingersoll', '', 'brand-logo-49.png'),
(50, 10, 'Iscar', '', 'brand-logo-50.jpg'),
(51, 10, 'Kennametal', '', 'brand-logo-51.jpg'),
(52, 10, 'Korloy', '', 'brand-logo-52.png'),
(53, 10, 'LMT', '', 'brand-logo-53.png'),
(54, 10, 'OSG', '', 'brand-logo-54.png'),
(55, 10, 'Ph Horn', '', 'brand-logo-55.png'),
(56, 10, 'Sandvik Coromant', '', 'brand-logo-56.png'),
(57, 10, 'Schunk', '', 'brand-logo-57.png'),
(58, 10, 'Sumitomo', '', 'brand-logo-58.png'),
(59, 10, 'Taegutec', '', 'brand-logo-59.png'),
(61, 10, 'ToolFlo', '', 'brand-logo-61.jpg'),
(62, 10, 'Tungaloy', '', 'brand-logo-62.png'),
(63, 10, 'Walter Tools', '', 'brand-logo-63.png'),
(64, 10, 'Widia', '', 'brand-logo-64.png'),
(65, 10, 'YG1', '', 'brand-logo-65.png'),
(66, 10, 'Dormar', '', 'brand-logo-66.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_brands`
--
ALTER TABLE `tbl_brands`
  ADD PRIMARY KEY (`brand_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_brands`
--
ALTER TABLE `tbl_brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
