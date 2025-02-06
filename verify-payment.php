<?php
// verify-payment.php
header('Content-Type: application/json');
session_start();
require_once 'config.php';
// require_once 'header.php';
require_once 'db_connection.php';
require_once 'vendor/autoload.php';

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
    
    // Insert orders
    $stmt = $pdo->prepare("
        INSERT INTO tbl_orders (
            order_id,
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
    
    // Insert each item as a separate order
    foreach ($items as $item) {
        $orderData = [
            'order_id' => $_POST['razorpay_order_id'],
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
    
    // Clear cart
    $stmt = $pdo->prepare("DELETE FROM tbl_cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_session']['id']]);
    
    // Store order ID in session for order confirmation page
    $_SESSION['last_order_id'] = $_POST['razorpay_order_id'];
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode(['success' => true]);
    
} catch (SignatureVerificationError $e) {
    $pdo->rollBack();
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid payment signature']);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}