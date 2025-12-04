<?php
include 'db_connection.php';
include 'config.php';
require 'vendor/autoload.php';
session_start();

date_default_timezone_set('Asia/Kolkata');
// $bid_time = date("Y-m-d H:i:s");
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

// Check if the user is logged in
if (!isset($_SESSION['user_session']['id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in to submit a bid."
    ]);
    exit;
}

$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the POST data
    $postData = json_decode(file_get_contents('php://input'), true);
    
    // If regular POST data exists, use that instead
    if (empty($postData)) {
        $postData = $_POST;
    }

    // Log the received data for debugging
    error_log('Received payment data: ' . print_r($postData, true));

    if (isset($postData['quantity']) && isset($postData['proposed_price'])) {
        // This is the initial bid submission
        $quantity = intval($postData['quantity']);
        $proposed_price = floatval($postData['proposed_price']);
        $product_id = intval($postData['product_id']);
        $user_id = $_SESSION['user_session']['id'];

        // Check if user has already bid on this product
        $check_stmt = $conn->prepare("SELECT user_id FROM bidding WHERE product_id = ? AND user_id = ? AND bid_status != '3'");
        $check_stmt->bind_param("ii", $product_id, $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'You have already submitted a bid for this product'
            ]);
            exit;
        }
        $check_stmt->close();

        // Store bid details in session for later use
        $_SESSION['pending_bid'] = [
            'product_id' => $product_id,
            'quantity' => $quantity,
            'price' => $proposed_price
        ];

        // Create Razorpay Order
        $totalAmount = $quantity * $proposed_price * 100; // Convert to paise
        
        try {
            $order = $api->order->create([
                'amount' => $totalAmount,
                'currency' => 'INR',
                'payment_capture' => 1
            ]);

            echo json_encode([
                'status' => 'success',
                'order_id' => $order->id,
                'amount' => $totalAmount
            ]);
            exit;
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
            exit;
        }
    } 
    // Handle payment verification
    elseif (isset($postData['razorpay_payment_id']) && 
            isset($postData['razorpay_order_id']) && 
            isset($postData['razorpay_signature'])) {
        
        try {
            $attributes = [
                'razorpay_payment_id' => $postData['razorpay_payment_id'],
                'razorpay_order_id' => $postData['razorpay_order_id'],
                'razorpay_signature' => $postData['razorpay_signature']
            ];

            $api->utility->verifyPaymentSignature($attributes);

            // Payment verified, now insert the bid
            if (isset($_SESSION['pending_bid'])) {
                $bid = $_SESSION['pending_bid'];
                
                // Double-check for existing bids before inserting
                $check_stmt = $conn->prepare("SELECT user_id FROM bidding WHERE product_id = ? AND user_id = ? AND bid_status != '3'");
                $check_stmt->bind_param("ii", $bid['product_id'], $_SESSION['user_session']['id']);
                $check_stmt->execute();
                $result = $check_stmt->get_result();
                
                if ($result->num_rows > 0) {
                    throw new Exception("You have already submitted a bid for this product");
                }
                $check_stmt->close();
                $bid_time = date("Y-m-d H:i:s");
                $stmt = $conn->prepare(
                    "INSERT INTO bidding (product_id, user_id, bid_price, bid_quantity, payment_id, order_id, bid_status,bid_time) 
                     VALUES (?, ?, ?, ?, ?, ?, '0', ?)"
                );

                $stmt->bind_param(
                    "iidisss",
                    $bid['product_id'],
                    $_SESSION['user_session']['id'],
                    $bid['price'],
                    $bid['quantity'],
                    $postData['razorpay_payment_id'],
                    $postData['razorpay_order_id'],
                    $bid_time
                );

                if ($stmt->execute()) {
                    unset($_SESSION['pending_bid']);
                    echo json_encode([
                        "status" => "success",
                        "message" => "Bid submitted successfully"
                    ]);
                } else {
                    throw new Exception("Database error: " . $stmt->error);
                }
                
                $stmt->close();
            } else {
                throw new Exception("No pending bid found in session");
            }
        } catch (SignatureVerificationError $e) {
            echo json_encode([
                "status" => "error",
                "message" => "Payment verification failed: " . $e->getMessage()
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid request parameters"
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid request method"
    ]);
}

$conn->close();
?>