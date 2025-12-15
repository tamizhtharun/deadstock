<?php
// verify-payment.php - IMPORTANT: No whitespace before this tag
ob_start(); // Start output buffering to catch any stray output
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors, only log them
ini_set('log_errors', 1);

header('Content-Type: application/json');
session_start();

// Include required files
require_once 'config.php';
// require_once 'header.php';
require_once 'db_connection.php';
require_once 'vendor/autoload.php';
require_once 'admin/invoice_helper.php';

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

try {
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
    
    // Get the address_id from POST
    $address_id = isset($_POST['address_id']) ? $_POST['address_id'] : null;
    
    if (!$address_id) {
        throw new Exception('Address ID is required');
    }
    
    // Verify signature
    $attributes = [
        'razorpay_payment_id' => $_POST['razorpay_payment_id'],
        'razorpay_order_id' => $_POST['razorpay_order_id'],
        'razorpay_signature' => $_POST['razorpay_signature']
    ];
    
    $api->utility->verifyPaymentSignature($attributes);
    
    // Get payment details
    $payment = $api->payment->fetch($_POST['razorpay_payment_id']);
    
    // Get cart items with fresh prices
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.p_current_price,
            c.quantity,
            p.seller_id
        FROM tbl_cart c
        JOIN tbl_product p ON p.id = c.id
        WHERE c.user_id = ? AND p.p_is_approve = 1
    ");
    $stmt->execute([$_SESSION['user_session']['id']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($items)) {
        throw new Exception('No valid items found in cart');
    }
    
    // Begin transaction
    $pdo->beginTransaction();

    // Group items by seller_id
    $itemsBySeller = [];
    foreach ($items as $item) {
        $seller_id = $item['seller_id'];
        if (!isset($itemsBySeller[$seller_id])) {
            $itemsBySeller[$seller_id] = [];
        }
        $itemsBySeller[$seller_id][] = $item;
    }

    // Insert orders, generating separate invoice numbers for each seller
    $stmt = $pdo->prepare("
        INSERT INTO tbl_orders (
            order_id,
            invoice_number,
            product_id,
            user_id,
            seller_id,
            quantity,
            price,
            order_status,
            payment_id,
            created_at,
            updated_at,
            order_type,
            address_id,
            processing_time
        ) VALUES (
            :order_id,
            :invoice_number,
            :product_id,
            :user_id,
            :seller_id,
            :quantity,
            :price,
            :order_status,
            :payment_id,
            NOW(),
            NOW(),
            :order_type,
            :address_id,
            :processing_time
        )
    ");

    // Insert each item as a separate order, with invoice numbers per seller
    foreach ($itemsBySeller as $seller_id => $sellerItems) {
        // Generate a unique invoice number for this seller
        $invoice_number = generateInvoiceNumber($pdo);

        foreach ($sellerItems as $item) {
            $orderData = [
                'order_id' => $_POST['razorpay_order_id'],
                'invoice_number' => $invoice_number,
                'product_id' => $item['id'],
                'user_id' => $_SESSION['user_session']['id'],
                'seller_id' => $item['seller_id'],
                'quantity' => $item['quantity'],
                'price' => $item['p_current_price'],
                'order_status' => 'processing',
                'payment_id' => $_POST['razorpay_payment_id'],
                'address_id' => $address_id,
                'processing_time' => NULL,
                'order_type' => 'direct'
            ];

            $stmt->execute($orderData);
        }
    }
    
    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM tbl_cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_session']['id']]);
    
    // Store order ID in session for order confirmation page
    $_SESSION['last_order_id'] = $_POST['razorpay_order_id'];
    
    // Commit transaction
    $pdo->commit();
    
    // Clean any output buffers before sending JSON
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    echo json_encode(['success' => true]);
    
} catch (SignatureVerificationError $e) {
    // Only rollback if transaction is active
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log the error
    error_log('Payment signature verification failed: ' . $e->getMessage());
    
    // Clean any output buffers before sending JSON
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payment signature']);
} catch (Exception $e) {
    // Only rollback if transaction is active
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log the error with full details
    error_log('Payment verification error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    
    // Clean any output buffers before sending JSON
    while (ob_get_level()) {
        ob_end_clean();
    }
    
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}