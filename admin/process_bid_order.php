<?php
//process_bid_order.php
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering to catch any unexpected output
ob_start();

// Set JSON header
header('Content-Type: application/json');

function sendJsonResponse($data) {
    // Clear any output buffered content
    ob_clean();
    echo json_encode($data);
    exit;
}

try {
    require_once('header.php');

    if (!isset($_GET['action'])) {
        throw new Exception('Invalid action');
    }

    function processOrder($bid_id, $product_id, $user_id, $seller_id, $quantity, $price) {
        global $pdo;
        
        try {
            $pdo->beginTransaction();

            // Get order_id and payment_id from bidding table
            $stmt = $pdo->prepare("SELECT order_id, payment_id FROM bidding WHERE bid_id = ?");
            $stmt->execute([$bid_id]);
            $bidData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Generate a new order ID if not exists
            $order_id = $bidData['order_id'] ?? 'ORD' . time() . rand(1000, 9999);
            
            // Check if the order already exists
            $stmt = $pdo->prepare("SELECT id FROM tbl_orders WHERE bid_id = ?");
            $stmt->execute([$bid_id]);
            
            if ($stmt->rowCount() == 0) {
                // Insert the order into the orders table
                $stmt = $pdo->prepare("INSERT INTO tbl_orders (
                    product_id, user_id, seller_id, quantity, price, 
                    order_id, order_status, bid_id, payment_id, order_type
                ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?, 'bid')");
                
                $stmt->execute([
                    $product_id, 
                    $user_id, 
                    $seller_id, 
                    $quantity, 
                    $price,
                    $order_id,
                    $bid_id,
                    $bidData['payment_id']
                ]);
                
                $new_order_id = $pdo->lastInsertId();

                // Update bidding table with order_id if not exists
                if (!$bidData['order_id']) {
                    $stmt = $pdo->prepare("UPDATE bidding SET order_id = ? WHERE bid_id = ?");
                    $stmt->execute([$order_id, $bid_id]);
                }

                $pdo->commit();
                return [
                    'success' => true, 
                    'message' => 'Order sent to seller successfully.', 
                    'order_id' => $new_order_id,
                    'order_status' => 'pending'
                ];
            } else {
                $pdo->rollBack();
                return ['success' => false, 'message' => 'Order already exists.'];
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error processing order: " . $e->getMessage());
            throw $e;
        }
    }

    function updateOrderStatus($order_id, $new_status, $tracking_id) {
        global $pdo;

        $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'canceled'];
        if (!in_array($new_status, $valid_statuses)) {
            error_log("Invalid status provided: " . $new_status);
            throw new Exception('Invalid status provided');
        }

        try {
            $pdo->beginTransaction();

            // Check if order exists and get current status
            $stmt = $pdo->prepare("SELECT order_status, tracking_id FROM tbl_orders WHERE id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
                error_log("Order not found: " . $order_id);
                throw new Exception('Order not found');
            }

            // Check if order can be updated
            if ($order['order_status'] === 'delivered' || $order['order_status'] === 'canceled') {
                throw new Exception('Cannot update status of delivered or canceled orders');
            }

            // Validate status transition
            $valid_transition = false;
            switch ($order['order_status']) {
                case 'pending':
                    $valid_transition = ($new_status === 'processing');
                    break;
                case 'processing':
                    $valid_transition = ($new_status === 'shipped' || $new_status === 'canceled');
                    break;
                case 'shipped':
                    $valid_transition = ($new_status === 'delivered' || $new_status === 'canceled' || ($new_status === 'shipped' && $tracking_id));
                    break;
            }

            // Allow updating tracking ID for already shipped orders
            if ($order['order_status'] === 'shipped' && $new_status === 'shipped' && $tracking_id) {
                $valid_transition = true;
            }

            if (!$valid_transition) {
                throw new Exception("Invalid status transition from {$order['order_status']} to {$new_status}");
            }

            // Update order status and tracking ID if provided
            if ($new_status === 'shipped' && $tracking_id) {
                $stmt = $pdo->prepare("UPDATE tbl_orders SET order_status = ?, tracking_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $result = $stmt->execute([$new_status, $tracking_id, $order_id]);
            } elseif ($order['order_status'] === 'shipped' && $new_status === 'shipped' && $tracking_id) {
                // Only update tracking ID if the order is already shipped
                $stmt = $pdo->prepare("UPDATE tbl_orders SET tracking_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $result = $stmt->execute([$tracking_id, $order_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE tbl_orders SET order_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $result = $stmt->execute([$new_status, $order_id]);
            }

            if (!$result || $stmt->rowCount() === 0) {
                throw new Exception('Failed to update order status in database');
            }

            // If the new status is 'processing', update the processing_time
            if ($new_status === 'processing') {
                $stmt = $pdo->prepare("UPDATE tbl_orders SET processing_time = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$order_id]);
            }

            $pdo->commit();

            // Fetch the updated_at timestamp, processing_time, and tracking_id
            $stmt = $pdo->prepare("SELECT updated_at, processing_time, tracking_id FROM tbl_orders WHERE id = ?");
            $stmt->execute([$order_id]);
            $updatedOrder = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true, 
                'message' => 'Order status updated successfully', 
                'new_status' => $new_status,
                'processing_time' => $updatedOrder['processing_time'],
                'tracking_id' => $updatedOrder['tracking_id']
            ];

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Order status update error: " . $e->getMessage());
            throw $e;
        }
    }

    function sendAllOrders() {
        global $pdo;
        
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                SELECT 
                    b.bid_id, 
                    b.product_id, 
                    b.user_id, 
                    p.seller_id, 
                    b.bid_quantity, 
                    b.bid_price 
                FROM 
                    bidding b
                JOIN 
                    tbl_product p ON b.product_id = p.id
                LEFT JOIN 
                    tbl_orders o ON b.bid_id = o.bid_id
                WHERE 
                    b.bid_status = 2 
                    AND o.id IS NULL
            ");
            $stmt->execute();
            $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $results = [];
            foreach ($orders as $order) {
                $result = processOrder(
                    $order['bid_id'], 
                    $order['product_id'], 
                    $order['user_id'], 
                    $order['seller_id'], 
                    $order['bid_quantity'], 
                    $order['bid_price']
                );
                $results[] = $result;
            }

            $pdo->commit();
            return [
                'success' => true, 
                'message' => 'All orders sent successfully.', 
                'results' => $results,
                'total_orders' => count($results)
            ];
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error sending all orders: " . $e->getMessage());
            throw $e;
        }
    }

    // Route actions based on GET parameters
    switch ($_GET['action']) {
        case 'send':
            if (!isset($_GET['bid_id'])) {
                throw new Exception('Missing required parameters');
            }
            $result = processOrder(
                $_GET['bid_id'],
                $_GET['product_id'],
                $_GET['user_id'],
                $_GET['seller_id'],
                $_GET['quantity'],
                $_GET['price']
            );
            sendJsonResponse($result);
            break;

        case 'update_status':
            if (!isset($_GET['order_id']) || !isset($_GET['status'])) {
                throw new Exception('Missing required parameters');
            }
            $tracking_id = isset($_GET['tracking_id']) ? $_GET['tracking_id'] : null;
            $result = updateOrderStatus($_GET['order_id'], $_GET['status'], $tracking_id);
            sendJsonResponse($result);
            break;

        case 'sendall':
            $result = sendAllOrders();
            sendJsonResponse($result);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    // Log the full error details to a file
    error_log("Caught exception in process_bid_order.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());

    // Return a clean error response
    sendJsonResponse([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

// If we get here, something went wrong
sendJsonResponse([
    'success' => false,
    'message' => 'An unexpected error occurred'
]);
?>