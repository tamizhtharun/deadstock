-- SQL Schema for Settlement System
-- 
-- This file contains the required database schema changes for the settlement system

-- Table: platform_fee_config
-- Purpose: Store platform fee percentages based on seller rank
-- Centralized configuration for GOLD, SILVER, BRONZE ranks

CREATE TABLE IF NOT EXISTS `platform_fee_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rank` enum('GOLD','SILVER','BRONZE') NOT NULL,
  `fee_percentage` decimal(5,2) NOT NULL DEFAULT 10.00,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rank` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default platform fee percentages
INSERT INTO `platform_fee_config` (`rank`, `fee_percentage`) VALUES
('GOLD', 5.00),
('SILVER', 7.00),
('BRONZE', 10.00)
ON DUPLICATE KEY UPDATE 
  `fee_percentage` = VALUES(`fee_percentage`);

-- Add settlement_status column to tbl_orders if it doesn't exist
-- This tracks whether an order has been settled with the seller

ALTER TABLE `tbl_orders` 
ADD COLUMN IF NOT EXISTS `settlement_status` tinyint(1) NOT NULL DEFAULT 0 
COMMENT '0 = pending, 1 = settled';

-- Add settlement_date column to tbl_orders if it doesn't exist
-- This records when the settlement was completed

ALTER TABLE `tbl_orders` 
ADD COLUMN IF NOT EXISTS `settlement_date` datetime DEFAULT NULL;

-- Add delivery_charge column to tbl_orders if it doesn't exist
-- This stores the delivery charge to be deducted from seller settlement

ALTER TABLE `tbl_orders` 
ADD COLUMN IF NOT EXISTS `delivery_charge` decimal(10,2) NOT NULL DEFAULT 0.00;

-- Add index for faster queries
ALTER TABLE `tbl_orders` 
ADD INDEX IF NOT EXISTS `idx_seller_settlement` (`seller_id`, `settlement_status`, `order_status`);
