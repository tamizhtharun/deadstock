-- Database schema updates for Delhivery integration
-- Run these SQL commands to update your existing database

-- Add Delhivery-related columns to tbl_orders table
ALTER TABLE tbl_orders 
ADD COLUMN delhivery_awb VARCHAR(50) NULL AFTER tracking_id,
ADD COLUMN delhivery_pickup_token VARCHAR(100) NULL AFTER delhivery_awb,
ADD COLUMN delhivery_shipment_status VARCHAR(50) NULL AFTER delhivery_pickup_token,
ADD COLUMN delhivery_created_at TIMESTAMP NULL AFTER delhivery_shipment_status,
ADD COLUMN delhivery_updated_at TIMESTAMP NULL AFTER delhivery_created_at;

-- Create index for better performance
CREATE INDEX idx_delhivery_awb ON tbl_orders(delhivery_awb);
CREATE INDEX idx_delhivery_pickup_token ON tbl_orders(delhivery_pickup_token);

-- Create table to store Delhivery shipment tracking history
CREATE TABLE IF NOT EXISTS delhivery_tracking_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    awb_number VARCHAR(50) NOT NULL,
    status VARCHAR(100) NOT NULL,
    status_description TEXT,
    location VARCHAR(255),
    timestamp DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES tbl_orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_awb_number (awb_number),
    INDEX idx_timestamp (timestamp)
);

-- Create table to store Delhivery pickup requests
CREATE TABLE IF NOT EXISTS delhivery_pickup_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    pickup_token VARCHAR(100) NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    pickup_date DATE NOT NULL,
    pickup_time TIME NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES tbl_orders(id) ON DELETE CASCADE,
    INDEX idx_order_id (order_id),
    INDEX idx_pickup_token (pickup_token),
    INDEX idx_status (status)
);

-- Create table to store Delhivery API logs
CREATE TABLE IF NOT EXISTS delhivery_api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NULL,
    api_endpoint VARCHAR(255) NOT NULL,
    request_data TEXT,
    response_data TEXT,
    status_code INT,
    success BOOLEAN DEFAULT FALSE,
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_order_id (order_id),
    INDEX idx_api_endpoint (api_endpoint),
    INDEX idx_created_at (created_at)
);

-- Update existing orders to set default values for new columns
UPDATE tbl_orders SET 
    delhivery_shipment_status = 'pending',
    delhivery_created_at = CURRENT_TIMESTAMP
WHERE delhivery_shipment_status IS NULL;
