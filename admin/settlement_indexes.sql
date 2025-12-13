-- Database Optimization for Invoice-Based Settlement System
-- Run these queries to add necessary indexes

-- Index for invoice-seller grouping (most important)
CREATE INDEX IF NOT EXISTS idx_invoice_seller 
ON tbl_orders(invoice_number, seller_id);

-- Index for settlement status queries
CREATE INDEX IF NOT EXISTS idx_settlement_status 
ON tbl_orders(settlement_status, settlement_date);

-- Index for order status and invoice filtering
CREATE INDEX IF NOT EXISTS idx_order_status_invoice 
ON tbl_orders(order_status, invoice_number);

-- Composite index for common query pattern
CREATE INDEX IF NOT EXISTS idx_seller_invoice_status 
ON tbl_orders(seller_id, invoice_number, settlement_status, order_status);

-- Index for date-based queries
CREATE INDEX IF NOT EXISTS idx_created_at 
ON tbl_orders(created_at);
