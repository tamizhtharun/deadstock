<?php
/**
 * Invoice Helper Functions
 * Handles invoice number generation for orders
 */

/**
 * Generate a sequential invoice number
 * Format: INV-YYYY-0001
 * 
 * @param PDO $pdo Database connection
 * @return string Generated invoice number
 */
function generateInvoiceNumber($pdo) {
    try {
        // Lock the table to prevent race conditions
        $pdo->exec("LOCK TABLES tbl_orders WRITE");
        
        // Get the last invoice number
        $stmt = $pdo->query("SELECT invoice_number FROM tbl_orders 
                              WHERE invoice_number IS NOT NULL 
                              AND invoice_number LIKE 'INV-%'
                              ORDER BY id DESC LIMIT 1");
        $lastInvoice = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $currentYear = date('Y');
        
        if ($lastInvoice && $lastInvoice['invoice_number']) {
            // Extract year and number from INV-2024-0001
            if (preg_match('/INV-(\d{4})-(\d+)/', $lastInvoice['invoice_number'], $matches)) {
                $lastYear = $matches[1];
                $lastNumber = intval($matches[2]);
                
                // If new year, reset to 1, else increment
                if ($currentYear != $lastYear) {
                    $newNumber = 1;
                } else {
                    $newNumber = $lastNumber + 1;
                }
            } else {
                // Invalid format, start fresh
                $newNumber = 1;
            }
        } else {
            // First invoice
            $newNumber = 1;
        }
        
        $invoiceNumber = sprintf('INV-%s-%04d', $currentYear, $newNumber);
        
        // Unlock tables
        $pdo->exec("UNLOCK TABLES");
        
        return $invoiceNumber;
        
    } catch (Exception $e) {
        // Make sure to unlock tables in case of error
        $pdo->exec("UNLOCK TABLES");
        throw $e;
    }
}
?>
