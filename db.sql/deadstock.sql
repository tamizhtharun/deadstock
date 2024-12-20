-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2024 at 03:25 PM
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
(4, 'TAMIL SELVAN V', 'MKCE', 'mailtotharun23@gmail.com', '09597049879', '123', '4/97, Sullerumbu naalroad, Sullerumbu(post), Vedasandur, Dindigul.', 'Tamil Nadu', 'Dindigul', '624710', '$2y$10$Ja4zOaYUhFjhacu4S2hy3OuN1vfmmHRMMELE9SWyTg0J1ohO5RBre', '2024-11-12 09:44:32', 1),
(5, 'Jana', 'IMET TOOLING INDIA PVT LTD', 'imettrichy@gmail.com', '9500612277', '33AACCI1032M1ZW', 'No.8, Patel Complex, Mannarpuram', 'Tamilnadu', 'Trichy', '620020', '$2y$10$Ja4zOaYUhFjhacu4S2hy3OuN1vfmmHRMMELE9SWyTg0J1ohO5RBre', '2024-11-12 11:11:35', 1);

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

-- --------------------------------------------------------

--
-- Table structure for table `tbl_end_category`
--

CREATE TABLE `tbl_end_category` (
  `ecat_id` int(11) NOT NULL,
  `ecat_name` varchar(255) NOT NULL,
  `mcat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_end_category`
--

INSERT INTO `tbl_end_category` (`ecat_id`, `ecat_name`, `mcat_id`) VALUES
(97, 'ISO Inserts', 50),
(98, 'Milling Inserts', 50),
(99, 'Inserts', 51),
(100, 'External Turning ', 51),
(101, 'Internal Turning', 51),
(102, 'Face Milling', 53),
(103, 'Shoulder Milling ', 53),
(104, 'Thread Milling ', 53),
(105, 'Profile Milling ', 53),
(106, 'High Feed Milling ', 53),
(107, 'Die and Mould ', 53),
(108, 'Chamfer Milling', 53),
(109, 'Slot, Cut Off and Groove Milling', 53),
(110, 'Ball Nose End Mills', 53),
(111, 'Parting off Tools', 52),
(112, 'External Grooving Tools', 52),
(113, 'Internal Grooving Tools', 52),
(114, 'Face Grooving Tools', 52),
(115, 'Indexable Drills', 54),
(116, 'Exchangeable Drills Tips', 54),
(117, 'Solid Carbide Drills', 54),
(118, 'Solid Carbide Drills', 55),
(119, 'Solid Carbide End Mills', 55),
(120, 'Solid Carbide Ball Nose End Mills', 55),
(121, 'Tapping', 55),
(122, 'Solid Carbide Reamers', 55),
(123, 'Thread Turning Tools', 56),
(124, 'Thread Milling Tools', 56),
(125, 'Tapping', 56),
(126, 'Stationary adaptors tools', 57),
(127, 'Rotating adaptors tools', 57),
(128, 'Assembly parts and accessories – General adaptors tools', 57),
(129, 'Collets', 57),
(130, 'Rough Boring Tools', 54),
(131, 'Finish Boring Tools', 54),
(132, 'Inserts', 54),
(133, 'Insert Type Reamers', 58),
(134, 'Solid Carbide Reamers', 58),
(136, 'Bi-Metal M42', 59),
(137, 'Bi-Metal M51', 59),
(138, 'HSS ', 60),
(139, 'HSS-E', 60),
(140, 'Carbide Tipped (TCT)', 60),
(141, 'Hole Saw', 61),
(142, 'Impact Hole Saw', 61),
(143, 'Bi-Metal Hole Saw', 61),
(144, 'Step Drills', 62),
(145, 'Auger Bits', 62),
(146, 'Spade Bits', 62),
(147, 'Carbide Tipped Hole Cutters', 62),
(148, 'Light ', 63),
(149, 'Heavy ', 63),
(150, 'Hack Saw', 65),
(151, 'Bi Metal Hack Saw', 65),
(152, 'Hack Saw Frames', 65),
(153, 'Cordless screwdrivers', 82),
(154, 'Impact wrenches', 82),
(155, 'Torque shut-off screwdrivers', 82),
(156, 'Drills', 82),
(157, 'Grinders', 82),
(158, 'Saws', 82),
(159, 'Cordless riveting tools', 82),
(160, 'Pneumatic drills & pneumatic screwdriving tools', 83),
(161, 'Pneumatic grinding tools', 83),
(162, 'Impact pneumatic tools', 83),
(163, 'Pneumatic riveting tools', 83),
(164, 'Compressors', 83),
(165, 'Compressed air technology, spare parts & accessories', 83),
(166, 'Vacuum cleaners', 84),
(167, 'High-pressure cleaners', 84),
(168, 'Ultrasonic cleaning units', 84),
(169, 'Sweepers', 84),
(170, 'Vacuum cleaners & cleaning devices, spare parts & accessories', 84),
(171, 'Chop Saw', 82),
(172, 'Chop Saw', 82),
(174, 'Others', 89);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_faq`
--

CREATE TABLE `tbl_faq` (
  `faq_id` int(11) NOT NULL,
  `faq_title` varchar(255) NOT NULL,
  `faq_content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_faq`
--

INSERT INTO `tbl_faq` (`faq_id`, `faq_title`, `faq_content`) VALUES
(1, 'How to find an item?', '<h3 class=\"checkout-complete-box font-bold txt16\" style=\"box-sizing: inherit; text-rendering: optimizeLegibility; margin: 0.2rem 0px 0.5rem; padding: 0px; line-height: 1.4; background-color: rgb(250, 250, 250);\"><font color=\"#222222\" face=\"opensans, Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif\"><span style=\"font-size: 15.7143px;\">We have a wide range of fabulous products to choose from.</span></font></h3><h3 class=\"checkout-complete-box font-bold txt16\" style=\"box-sizing: inherit; text-rendering: optimizeLegibility; margin: 0.2rem 0px 0.5rem; padding: 0px; line-height: 1.4; background-color: rgb(250, 250, 250);\"><span style=\"font-size: 15.7143px; color: rgb(34, 34, 34); font-family: opensans, \"Helvetica Neue\", Helvetica, Helvetica, Arial, sans-serif;\">Tip 1: If you\'re looking for a specific product, use the keyword search box located at the top of the site. Simply type what you are looking for, and prepare to be amazed!</span></h3><h3 class=\"checkout-complete-box font-bold txt16\" style=\"box-sizing: inherit; text-rendering: optimizeLegibility; margin: 0.2rem 0px 0.5rem; padding: 0px; line-height: 1.4; background-color: rgb(250, 250, 250);\"><font color=\"#222222\" face=\"opensans, Helvetica Neue, Helvetica, Helvetica, Arial, sans-serif\"><span style=\"font-size: 15.7143px;\">Tip 2: If you want to explore a category of products, use the Shop Categories in the upper menu, and navigate through your favorite categories where we\'ll feature the best products in each.</span></font><br><br></h3>\r\n'),
(2, 'What is your return policy?', '<p><span style=\"color: rgb(10, 10, 10); font-family: opensans, &quot;Helvetica Neue&quot;, Helvetica, Helvetica, Arial, sans-serif; font-size: 14px; text-align: center;\">You have 15 days to make a refund request after your order has been delivered.</span><br></p>\r\n'),
(3, ' I received a defective/damaged item, can I get a refund?', '<p>In case the item you received is damaged or defective, you could return an item in the same condition as you received it with the original box and/or packaging intact. Once we receive the returned item, we will inspect it and if the item is found to be defective or damaged, we will process the refund along with any shipping fees incurred.<br></p>\r\n'),
(4, 'When are ‘Returns’ not possible?', '<p class=\"a  \" style=\"box-sizing: inherit; text-rendering: optimizeLegibility; line-height: 1.6; margin-bottom: 0.714286rem; padding: 0px; font-size: 14px; color: rgb(10, 10, 10); font-family: opensans, &quot;Helvetica Neue&quot;, Helvetica, Helvetica, Arial, sans-serif; background-color: rgb(250, 250, 250);\">There are a few certain scenarios where it is difficult for us to support returns:</p><ol style=\"box-sizing: inherit; line-height: 1.6; margin-right: 0px; margin-bottom: 0px; margin-left: 1.25rem; padding: 0px; list-style-position: outside; color: rgb(10, 10, 10); font-family: opensans, &quot;Helvetica Neue&quot;, Helvetica, Helvetica, Arial, sans-serif; font-size: 14px; background-color: rgb(250, 250, 250);\"><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Return request is made outside the specified time frame, of 15 days from delivery.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Product is used, damaged, or is not in the same condition as you received it.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Specific categories like innerwear, lingerie, socks and clothing freebies etc.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Defective products which are covered under the manufacturer\'s warranty.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Any consumable item which has been used or installed.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Products with tampered or missing serial numbers.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Anything missing from the package you\'ve received including price tags, labels, original packing, freebies and accessories.</li><li style=\"box-sizing: inherit; margin: 0px; padding: 0px; font-size: inherit;\">Fragile items, hygiene related items.</li></ol>\r\n'),
(5, 'What are the items that cannot be returned?', '<p>The items that can not be returned are:</p><p>Clearance items clearly marked as such and displaying a No-Return Policy<br></p><p>When the offer notes states so specifically are items that cannot be returned.</p><p>Items that fall into the below product types-</p><ul><li>Underwear</li><li>Lingerie</li><li>Socks</li><li>Software</li><li>Music albums</li><li>Books</li><li>Swimwear</li><li>Beauty &amp; Fragrances</li><li>Hosiery</li></ul><p>Also, any consumable items that are used or installed cannot be returned. As outlined in consumer Protection Rights and concerning section on non-returnable items<br></p>');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mid_category`
--

CREATE TABLE `tbl_mid_category` (
  `mcat_id` int(11) NOT NULL,
  `mcat_name` varchar(255) NOT NULL,
  `tcat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_mid_category`
--

INSERT INTO `tbl_mid_category` (`mcat_id`, `mcat_name`, `tcat_id`) VALUES
(50, 'Inserts and Grades', 10),
(51, 'Turning Tools', 10),
(52, 'Parting and Grooving tools', 10),
(53, 'Milling Tools', 10),
(54, 'Drilling  and Boring Tools', 10),
(55, 'Solid Round tools', 10),
(56, 'Threading Tools', 10),
(57, 'Tooling Systems', 10),
(58, 'Boring Reaming Tools', 10),
(59, 'Band Saw Blades', 11),
(60, 'Circular Saw Blades', 11),
(61, 'Hole Cutting and Boring', 11),
(62, 'Precision Hole Cutting ', 11),
(63, 'Reciprocating Saw Blades', 11),
(64, 'Portable Band Saw Blades', 11),
(65, 'Hand Saw Blades', 11),
(66, 'Screwdriving Tools', 12),
(67, 'Torque Drive Tools', 12),
(68, 'Pilers and Tweezers', 12),
(69, 'Deburrers and Scrapers', 12),
(70, 'Striking Tools', 12),
(71, 'Clamping Tools', 12),
(72, 'Tools Sets Mixed', 12),
(73, 'Hand Tools Storage', 12),
(74, 'Assembly and Disassembly Tools', 12),
(75, 'Pipe Processing Tools', 12),
(76, 'Rivet Tools', 12),
(77, 'Hand Tools, Spares and Acessories', 12),
(78, 'Cutting Off', 13),
(79, 'DC Wheels', 13),
(80, 'Tool and Cutter Grinding Wheels', 13),
(81, 'Flap Disc', 13),
(82, 'Power Tools', 14),
(83, 'Pneumatic Power tools', 14),
(84, 'Vacuum Cleaner and Cleaning Devices', 14),
(89, 'Others', 15);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product`
--

CREATE TABLE `tbl_product` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `p_name` varchar(255) NOT NULL,
  `p_old_price` varchar(10) NOT NULL,
  `p_current_price` varchar(10) NOT NULL,
  `p_qty` int(10) NOT NULL,
  `p_featured_photo` varchar(255) NOT NULL,
  `p_description` text NOT NULL,
  `p_feature` text NOT NULL,
  `p_condition` text NOT NULL,
  `p_return_policy` text NOT NULL,
  `p_total_view` int(11) NOT NULL,
  `p_is_featured` int(1) NOT NULL,
  `p_is_active` int(1) NOT NULL,
  `p_is_approve` int(1) NOT NULL,
  `ecat_id` int(11) NOT NULL,
  `product_catalogue` varchar(500) NOT NULL,
  `product_brand` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_product`
--

INSERT INTO `tbl_product` (`id`, `seller_id`, `p_name`, `p_old_price`, `p_current_price`, `p_qty`, `p_featured_photo`, `p_description`, `p_feature`, `p_condition`, `p_return_policy`, `p_total_view`, `p_is_featured`, `p_is_active`, `p_is_approve`, `ecat_id`, `product_catalogue`, `product_brand`) VALUES
(155, 4, 'Shank tool – Rigid clamping', '999', '400', 122, 'product-featured-155.png', '', '', '', '', 3, 1, 0, 1, 97, 'product-catalogue-155.pdf', '39'),
(158, 5, 'CNMG120408-NM4 WSM30', '896', '495', 100, 'product-featured-158.png', '', '', '', '', 13, 1, 1, 1, 97, 'product-catalogue-158.pdf', '63'),
(159, 5, 'VBMT160408-FM6 WSM20S', '1329', '697', 100, 'product-featured-159.png', '', '', '', '', 31, 1, 1, 1, 97, 'product-catalogue-159.pdf', '63'),
(160, 4, 'Shank tool – Rigid clamping', '986', '801', 122, 'product-featured-160.png', 'TTTTTTTTTTTTTTTTTTTTTTTTTTTTTTT', '', '', '', 21, 0, 0, 1, 97, 'product-catalogue-160.pdf', '50');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_product_photo`
--

CREATE TABLE `tbl_product_photo` (
  `pp_id` int(11) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `p_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_product_photo`
--

INSERT INTO `tbl_product_photo` (`pp_id`, `photo`, `p_id`) VALUES
(0, '.png', 104);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_rating`
--

CREATE TABLE `tbl_rating` (
  `rt_id` int(11) NOT NULL,
  `p_id` int(11) NOT NULL,
  `cust_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `rating` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_settings`
--

CREATE TABLE `tbl_settings` (
  `id` int(11) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `favicon` varchar(255) NOT NULL,
  `running_text` longtext NOT NULL,
  `footer_about` text NOT NULL,
  `footer_copyright` text NOT NULL,
  `contact_address` text NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_phone` varchar(255) NOT NULL,
  `contact_fax` varchar(255) NOT NULL,
  `contact_map_iframe` text NOT NULL,
  `receive_email` varchar(255) NOT NULL,
  `receive_email_subject` varchar(255) NOT NULL,
  `receive_email_thank_you_message` text NOT NULL,
  `forget_password_message` text NOT NULL,
  `total_recent_post_footer` int(10) NOT NULL,
  `total_popular_post_footer` int(10) NOT NULL,
  `total_recent_post_sidebar` int(11) NOT NULL,
  `total_popular_post_sidebar` int(11) NOT NULL,
  `total_featured_product_home` int(11) NOT NULL,
  `total_latest_product_home` int(11) NOT NULL,
  `total_popular_product_home` int(11) NOT NULL,
  `meta_title_home` text NOT NULL,
  `meta_keyword_home` text NOT NULL,
  `meta_description_home` text NOT NULL,
  `banner_login` varchar(255) NOT NULL,
  `banner_registration` varchar(255) NOT NULL,
  `banner_forget_password` varchar(255) NOT NULL,
  `banner_reset_password` varchar(255) NOT NULL,
  `banner_search` varchar(255) NOT NULL,
  `banner_cart` varchar(255) NOT NULL,
  `banner_checkout` varchar(255) NOT NULL,
  `banner_product_category` varchar(255) NOT NULL,
  `banner_blog` varchar(255) NOT NULL,
  `cta_title` varchar(255) NOT NULL,
  `cta_content` text NOT NULL,
  `cta_read_more_text` varchar(255) NOT NULL,
  `cta_read_more_url` varchar(255) NOT NULL,
  `cta_photo` varchar(255) NOT NULL,
  `featured_product_title` varchar(255) NOT NULL,
  `featured_product_subtitle` varchar(255) NOT NULL,
  `latest_product_title` varchar(255) NOT NULL,
  `latest_product_subtitle` varchar(255) NOT NULL,
  `popular_product_title` varchar(255) NOT NULL,
  `popular_product_subtitle` varchar(255) NOT NULL,
  `testimonial_title` varchar(255) NOT NULL,
  `testimonial_subtitle` varchar(255) NOT NULL,
  `testimonial_photo` varchar(255) NOT NULL,
  `blog_title` varchar(255) NOT NULL,
  `blog_subtitle` varchar(255) NOT NULL,
  `newsletter_text` text NOT NULL,
  `paypal_email` varchar(255) NOT NULL,
  `stripe_public_key` varchar(255) NOT NULL,
  `stripe_secret_key` varchar(255) NOT NULL,
  `bank_detail` text NOT NULL,
  `before_head` text NOT NULL,
  `after_body` text NOT NULL,
  `before_body` text NOT NULL,
  `home_service_on_off` int(11) NOT NULL,
  `home_welcome_on_off` int(11) NOT NULL,
  `home_featured_product_on_off` int(11) NOT NULL,
  `home_latest_product_on_off` int(11) NOT NULL,
  `home_popular_product_on_off` int(11) NOT NULL,
  `home_testimonial_on_off` int(11) NOT NULL,
  `home_blog_on_off` int(11) NOT NULL,
  `newsletter_on_off` int(11) NOT NULL,
  `ads_above_welcome_on_off` int(1) NOT NULL,
  `ads_above_featured_product_on_off` int(1) NOT NULL,
  `ads_above_latest_product_on_off` int(1) NOT NULL,
  `ads_above_popular_product_on_off` int(1) NOT NULL,
  `ads_above_testimonial_on_off` int(1) NOT NULL,
  `ads_category_sidebar_on_off` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_settings`
--

INSERT INTO `tbl_settings` (`id`, `logo`, `favicon`, `running_text`, `footer_about`, `footer_copyright`, `contact_address`, `contact_email`, `contact_phone`, `contact_fax`, `contact_map_iframe`, `receive_email`, `receive_email_subject`, `receive_email_thank_you_message`, `forget_password_message`, `total_recent_post_footer`, `total_popular_post_footer`, `total_recent_post_sidebar`, `total_popular_post_sidebar`, `total_featured_product_home`, `total_latest_product_home`, `total_popular_product_home`, `meta_title_home`, `meta_keyword_home`, `meta_description_home`, `banner_login`, `banner_registration`, `banner_forget_password`, `banner_reset_password`, `banner_search`, `banner_cart`, `banner_checkout`, `banner_product_category`, `banner_blog`, `cta_title`, `cta_content`, `cta_read_more_text`, `cta_read_more_url`, `cta_photo`, `featured_product_title`, `featured_product_subtitle`, `latest_product_title`, `latest_product_subtitle`, `popular_product_title`, `popular_product_subtitle`, `testimonial_title`, `testimonial_subtitle`, `testimonial_photo`, `blog_title`, `blog_subtitle`, `newsletter_text`, `paypal_email`, `stripe_public_key`, `stripe_secret_key`, `bank_detail`, `before_head`, `after_body`, `before_body`, `home_service_on_off`, `home_welcome_on_off`, `home_featured_product_on_off`, `home_latest_product_on_off`, `home_popular_product_on_off`, `home_testimonial_on_off`, `home_blog_on_off`, `newsletter_on_off`, `ads_above_welcome_on_off`, `ads_above_featured_product_on_off`, `ads_above_latest_product_on_off`, `ads_above_popular_product_on_off`, `ads_above_testimonial_on_off`, `ads_category_sidebar_on_off`) VALUES
(1, 'logo.png', 'favicon.png', 'Dead stock is inventory that is unsellable. A business may find itself with dead stock because it ordered or manufactured too many items and then found they didn\'t sell as anticipated. Dead stock can also include damaged items, incorrect deliveries, leftover seasonal products or expired raw materials.', '<p>Lorem ipsum dolor sit amet, omnis signiferumque in mei, mei ex enim concludaturque. Senserit salutandi euripidis no per, modus maiestatis scribentur est an.Â Ea suas pertinax has.</p>\r\n', '', 'Tamil', '', '', '', '', 'support@ecommercephp.com', 'Visitor Email Message from Ecommerce Site PHP', 'Thank you for sending email. We will contact you shortly.', 'A confirmation link is sent to your email address. You will get the password reset information in there.', 4, 4, 5, 5, 5, 6, 8, 'Ecommerce PHP', 'online fashion store, garments shop, online garments', 'ecommerce php project with mysql database', 'banner_login.jpg', 'banner_registration.jpg', 'banner_forget_password.jpg', 'banner_reset_password.jpg', 'banner_search.jpg', 'banner_cart.jpg', 'banner_checkout.jpg', 'banner_product_category.png', 'banner_blog.jpg', 'Welcome To Our Ecommerce Website', 'Lorem ipsum dolor sit amet, an labores explicari qui, eu nostrum copiosae argumentum has. Latine propriae quo no, unum ridens expetenda id sit, \r\nat usu eius eligendi singulis. Sea ocurreret principes ne. At nonumy aperiri pri, nam quodsi copiosae intellegebat et, ex deserunt euripidis usu. ', 'Read More', '#', 'cta.jpg', 'Featured Products', 'Our list on Top Featured Products', 'Latest Products', 'Our list of recently added products', 'Popular Products', 'Popular products based on customer\'s choice', 'Testimonials', 'See what our clients tell about us', 'testimonial.jpg', 'Latest Blog', 'See all our latest articles and news from below', 'Sign-up to our newsletter for latest promotions and discounts.', 'admin@ecom.com', 'pk_test_0SwMWadgu8DwmEcPdUPRsZ7b', 'sk_test_TFcsLJ7xxUtpALbDo1L5c1PN', 'Bank Name: WestView Bank\r\nAccount Number: CA100270589600\r\nBranch Name: CA Branch\r\nCountry: USA', '', '<div id=\"fb-root\"></div>\r\n<script>(function(d, s, id) {\r\n  var js, fjs = d.getElementsByTagName(s)[0];\r\n  if (d.getElementById(id)) return;\r\n  js = d.createElement(s); js.id = id;\r\n  js.src = \"//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.10&appId=323620764400430\";\r\n  fjs.parentNode.insertBefore(js, fjs);\r\n}(document, \'script\', \'facebook-jssdk\'));</script>', '<!--Start of Tawk.to Script-->\r\n<script type=\"text/javascript\">\r\nvar Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();\r\n(function(){\r\nvar s1=document.createElement(\"script\"),s0=document.getElementsByTagName(\"script\")[0];\r\ns1.async=true;\r\ns1.src=\'https://embed.tawk.to/5ae370d7227d3d7edc24cb96/default\';\r\ns1.charset=\'UTF-8\';\r\ns1.setAttribute(\'crossorigin\',\'*\');\r\ns0.parentNode.insertBefore(s1,s0);\r\n})();\r\n</script>\r\n<!--End of Tawk.to Script-->', 1, 1, 1, 1, 1, 1, 1, 0, 1, 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_slider`
--

CREATE TABLE `tbl_slider` (
  `id` int(11) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `heading` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_slider`
--

INSERT INTO `tbl_slider` (`id`, `photo`, `heading`) VALUES
(11, 'slider-11.jpg', ''),
(12, 'slider-12.jpg', ''),
(13, 'slider-13.jpg', '');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_top_category`
--

CREATE TABLE `tbl_top_category` (
  `tcat_id` int(11) NOT NULL,
  `tcat_name` varchar(255) NOT NULL,
  `show_on_menu` int(1) NOT NULL,
  `photo` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_top_category`
--

INSERT INTO `tbl_top_category` (`tcat_id`, `tcat_name`, `show_on_menu`, `photo`) VALUES
(10, 'Cutting Tools', 1, 'top-category-image10.png'),
(11, 'Saw Blades', 1, 'top-category-image11.png'),
(12, 'Hand Tools', 1, 'top-category-image12.jpg'),
(13, 'Abrasive Wheels', 1, 'top-category-image13.jpg'),
(14, 'Power Tools', 1, 'top-category-image14.jpg'),
(15, 'Others ', 1, 'top-category-image15.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_waiting_products`
--

CREATE TABLE `tbl_waiting_products` (
  `p_id` int(11) NOT NULL,
  `p_name` varchar(255) NOT NULL,
  `p_old_price` varchar(10) NOT NULL,
  `p_current_price` varchar(10) NOT NULL,
  `p_qty` int(10) NOT NULL,
  `p_featured_photo` varchar(255) NOT NULL,
  `p_description` text NOT NULL,
  `p_short_description` text NOT NULL,
  `p_feature` text NOT NULL,
  `p_condition` text NOT NULL,
  `p_return_policy` text NOT NULL,
  `p_total_view` int(11) NOT NULL,
  `p_is_featured` int(1) NOT NULL,
  `p_is_active` int(1) NOT NULL,
  `ecat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_waiting_products`
--

INSERT INTO `tbl_waiting_products` (`p_id`, `p_name`, `p_old_price`, `p_current_price`, `p_qty`, `p_featured_photo`, `p_description`, `p_short_description`, `p_feature`, `p_condition`, `p_return_policy`, `p_total_view`, `p_is_featured`, `p_is_active`, `ecat_id`) VALUES
(1, 'Nithish', '', '123', 12, 'product-featured-.png', '', '', '', '', '', 0, 1, 1, 80),
(2, 'Nithish', '', '123', 123, 'product-featured-108.png', '', '', '', '', '', 0, 0, 1, 93),
(3, 'Tamil', '', '123', 123, 'product-featured-108.png', '', '', '', '', '', 0, 0, 1, 89),
(4, 'Tamil', '', '123', 123, 'product-featured-108.png', '', '', '', '', '', 0, 0, 1, 89),
(5, 'Nirbiehiwf', '', '124', 1222, 'product-featured-108.png', '', '', '', '', '', 0, 0, 1, 93),
(6, 'ABCD', '', '987', 222, 'product-featured-108.png', '', '', '', '', '', 0, 0, 1, 80),
(7, 'tools', '123', '234', 46, 'product-featured-108.png', '', '', '', '', '', 0, 0, 1, 80),
(8, 'tTTT', '12', '123', 12313, 'product-featured-112.png', '', '', '', '', '', 0, 0, 0, 80);

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
  `id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_phone` varchar(13) NOT NULL,
  `user_photo` varchar(250) NOT NULL,
  `user_password` varchar(100) NOT NULL,
  `user_role` enum('admin','seller','user','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_login`
--

INSERT INTO `user_login` (`id`, `user_name`, `user_email`, `user_phone`, `user_photo`, `user_password`, `user_role`) VALUES
(1, 'தமிழ்', 'superadmin@deadstock.in', '9597049879', 'user-1.jpg', '$2y$10$tj6EZTOvNTR2h6Xed7AoDe7l23pXGUYc/ngxpIRCsb0bRobtTyjTi', 'admin'),
(3, 'Admin', 'admin@deadstock.in', '', '', '$2y$10$iDgyJWxWFVEC78SzKhsv9OkxMkkIA3brNQ.UtwBla0vFB.suhnSVS', 'admin'),
(11, 'Jana', 'imettrichy@gmail.com', '', '', '$2y$10$Ja4zOaYUhFjhacu4S2hy3OuN1vfmmHRMMELE9SWyTg0J1ohO5RBre', 'seller');

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
-- Indexes for table `tbl_brands`
--
ALTER TABLE `tbl_brands`
  ADD PRIMARY KEY (`brand_id`);

--
-- Indexes for table `tbl_end_category`
--
ALTER TABLE `tbl_end_category`
  ADD PRIMARY KEY (`ecat_id`);

--
-- Indexes for table `tbl_faq`
--
ALTER TABLE `tbl_faq`
  ADD PRIMARY KEY (`faq_id`);

--
-- Indexes for table `tbl_mid_category`
--
ALTER TABLE `tbl_mid_category`
  ADD PRIMARY KEY (`mcat_id`);

--
-- Indexes for table `tbl_product`
--
ALTER TABLE `tbl_product`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_product_photo`
--
ALTER TABLE `tbl_product_photo`
  ADD PRIMARY KEY (`pp_id`);

--
-- Indexes for table `tbl_rating`
--
ALTER TABLE `tbl_rating`
  ADD PRIMARY KEY (`rt_id`);

--
-- Indexes for table `tbl_settings`
--
ALTER TABLE `tbl_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_slider`
--
ALTER TABLE `tbl_slider`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbl_top_category`
--
ALTER TABLE `tbl_top_category`
  ADD PRIMARY KEY (`tcat_id`);

--
-- Indexes for table `tbl_waiting_products`
--
ALTER TABLE `tbl_waiting_products`
  ADD PRIMARY KEY (`p_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_login`
--
ALTER TABLE `user_login`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sellers`
--
ALTER TABLE `sellers`
  MODIFY `seller_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_brands`
--
ALTER TABLE `tbl_brands`
  MODIFY `brand_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `tbl_end_category`
--
ALTER TABLE `tbl_end_category`
  MODIFY `ecat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=175;

--
-- AUTO_INCREMENT for table `tbl_faq`
--
ALTER TABLE `tbl_faq`
  MODIFY `faq_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_mid_category`
--
ALTER TABLE `tbl_mid_category`
  MODIFY `mcat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `tbl_product`
--
ALTER TABLE `tbl_product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=161;

--
-- AUTO_INCREMENT for table `tbl_slider`
--
ALTER TABLE `tbl_slider`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tbl_top_category`
--
ALTER TABLE `tbl_top_category`
  MODIFY `tcat_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tbl_waiting_products`
--
ALTER TABLE `tbl_waiting_products`
  MODIFY `p_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_login`
--
ALTER TABLE `user_login`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
