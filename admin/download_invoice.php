<?php
/**
 * Download Invoice as PDF (Browser-based)
 * Uses browser's print-to-PDF capability with proper filename
 */

require_once('../db_connection.php');

// Get invoice_number from GET
$invoice_number = isset($_GET['invoice_number']) ? $_GET['invoice_number'] : null;

if (!$invoice_number) {
    die("Invalid invoice number");
}

// Get invoice number for filename
$invoice_filename = $invoice_number;

// Load invoice HTML
ob_start();
include('generate_invoice.php');
$invoiceHtml = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($invoice_filename); ?></title>
    <style>
        body {
            margin: 0;
            padding: 0;
        }
        
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
            
            .invoice-container {
                width: 100%;
                max-width: 100%;
                padding: 15mm !important;
                margin: 0 !important;
                box-sizing: border-box;
            }
        }
    </style>
</head>
<body>
    <?php echo $invoiceHtml; ?>
    
    <script>
        // Auto-trigger print dialog on load
        window.onload = function() {
            // Small delay to ensure page is fully rendered
            setTimeout(function() {
                window.print();
            }, 500);
        };
        
        // Close window after print dialog is closed
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>
