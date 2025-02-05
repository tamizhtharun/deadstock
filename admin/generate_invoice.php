<?php
require_once 'header.php';
require_once 'InvoiceGenerator.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log errors without exposing system paths
function logError($message, $exception = null) {
    $error_details = date('Y-m-d H:i:s') . " - " . $message;
    if ($exception) {
        $error_details .= "\nException: " . $exception->getMessage();
        $error_details .= "\nStack trace: " . $exception->getTraceAsString();
    }
    error_log($error_details . "\n", 3, dirname(__DIR__) . '/logs/invoice_errors.log');
    return "An error occurred while generating the invoice. Please contact support.";
}

try {
    // Verify order ID
    if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
        throw new Exception("Invalid or missing order ID");
    }

    // Database connection check
    if (!isset($pdo)) {
        throw new Exception("Database connection not available");
    }

    // Initialize the invoice generator
    $invoice_generator = new InvoiceGenerator($pdo);
    
    // Generate the invoice HTML
    $invoice_html = $invoice_generator->generateBiddingInvoice($_GET['order_id']);
    
    if (!$invoice_html) {
        throw new Exception("Failed to generate invoice HTML");
    }

    // Configure mPDF with specific settings for reliability
    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'tempDir' => sys_get_temp_dir(),
        'default_font' => 'dejavusans',
        'debug' => true
    ]);
    
    // Set PDF metadata
    $mpdf->SetTitle('Invoice-' . $_GET['order_id']);
    $mpdf->SetAuthor('Your Company Name');
    $mpdf->SetCreator('Your Company Name');
    
    // Clear any previous output
    if (ob_get_length()) ob_clean();
    
    // Add the HTML content
    $mpdf->WriteHTML($invoice_html);
    
    // Generate a unique filename
    $filename = 'Invoice-' . $_GET['order_id'] . '-' . date('YmdHis') . '.pdf';
    
    // Set appropriate headers
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    
    // Output the PDF
    $mpdf->Output($filename, 'D');
    exit;
    
} catch (\Mpdf\MpdfException $e) {
    // Handle mPDF specific errors
    echo logError("PDF generation failed: " . $e->getMessage(), $e);
} catch (Exception $e) {
    // Handle general errors
    echo logError("General error: " . $e->getMessage(), $e);
}