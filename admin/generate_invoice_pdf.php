<?php
require_once('../config.php');
require_once('InvoiceGenerator.php');
require_once('../vendor/autoload.php');

use Dompdf\Dompdf;
use Dompdf\Options;

if (!isset($_GET['order_id'])) {
    die("Order ID is required");
}

$order_id = $_GET['order_id'];

try {
    global $pdo;
    if (!isset($pdo)) {
        throw new Exception("Database connection not found");
    }

    $invoiceGen = new InvoiceGenerator($pdo);
    $html = $invoiceGen->generateBiddingInvoice($order_id);

    // Setup Dompdf options
    $options = new Options();
    $options->set('isRemoteEnabled', true);
    $dompdf = new Dompdf($options);

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // Stream the PDF inline
    $dompdf->stream("invoice_{$order_id}.pdf", ["Attachment" => false]);
} catch (Exception $e) {
    echo "Error generating invoice: " . $e->getMessage();
}
?>
