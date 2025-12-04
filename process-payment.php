<?php
// process-payment.php - Ensure this is the very first line with no spaces before it
header('Content-Type: application/json');
session_start();

// Basic error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Required files
require_once 'config.php';
require_once 'header.php';
require_once 'vendor/autoload.php';

use Razorpay\Api\Api;

// Create a response array
$response = [];

try {
    // Check session
    if (!isset($_SESSION['user_session']['id'])) {
        throw new Exception("Please login to continue");
    }

    // Get order items and calculate total
    function getOrderItems($pdo) {
        $user_id = $_SESSION['user_session']['id'];
        $stmt = $pdo->prepare("
            SELECT 
                p.id,
                p.p_current_price,
                c.quantity
            FROM tbl_cart c
            JOIN tbl_product p ON p.id = c.id
            WHERE c.user_id = ?
            AND p.p_is_approve = 1
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function calculateTotal($items) {
        $subtotal = 0;
        $total_gst = 0;
        $gst_rate = 18; // GST rate in percentage

        foreach ($items as $item) {
            $item_total = $item['p_current_price'] * $item['quantity'];
            $product_gst = $item_total * ($gst_rate / 100);
            $subtotal += $item_total;
            $total_gst += $product_gst;
        }

        $total = $subtotal + $total_gst;
        return $total;
    }

    $items = getOrderItems($pdo);
    $totalAmount = calculateTotal($items);

    // Initialize Razorpay
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    // Create order with actual amount
    $orderData = [
        'amount' => $totalAmount * 100, // Convert to paise
        'currency' => 'INR',
        'receipt' => 'order_' . time()
    ];

    // Create order
    $razorpayOrder = $api->order->create($orderData);

    // Set success response
    $response = [
        'success' => true,
        'key' => RAZORPAY_KEY_ID,
        'amount' => $orderData['amount'],
        'currency' => 'INR',
        'order_id' => $razorpayOrder->id,
        'total_items' => count($items)
    ];

} catch (Exception $e) {
    // Set error response
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
    http_response_code(500);
}

// Clean any output buffers
while (ob_get_level()) {
    ob_end_clean();
}

// Send JSON response
echo json_encode($response);
exit;
?>