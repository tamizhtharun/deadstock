<?php
session_start();
require_once('../db_connection.php');

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log the received data
error_log("Received POST data: " . print_r($_POST, true));

// Check if seller is logged in
if (!isset($_SESSION['seller_session']['seller_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and validate input
$order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$new_status = isset($_POST['status']) ? trim($_POST['status']) : '';
$seller_id = $_SESSION['seller_session']['seller_id'];

// Log the processed input
error_log("Processing order update - Order ID: $order_id, New Status: $new_status, Seller ID: $seller_id");

// Validate inputs
if (!$order_id || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Validate status
$valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'canceled'];
if (!in_array($new_status, $valid_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Invalid status provided']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Check if order exists and belongs to seller
    $stmt = $pdo->prepare("SELECT order_status FROM tbl_orders WHERE id = ? AND seller_id = ?");
    $stmt->execute([$order_id, $seller_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception('Order not found or unauthorized');
    }

    // Log current order status
    error_log("Current order status: " . $order['order_status']);

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
            $valid_transition = ($new_status === 'delivered' || $new_status === 'canceled');
            break;
    }

    if (!$valid_transition) {
        throw new Exception("Invalid status transition from {$order['order_status']} to {$new_status}");
    }

    // Handle shipment creation when status changes to 'shipped'
    if ($new_status === 'shipped' && $order['order_status'] !== 'shipped') {
        require_once('../services/DelhiveryService.php');
        $delhiveryService = new DelhiveryService();
        
        // Get customer details for shipment
        $stmt = $pdo->prepare("
            SELECT u.username as customer_name, u.phone_number as customer_phone, u.email as customer_email,
                   a.address, a.city, a.state, a.pincode
            FROM users u 
            LEFT JOIN users_addresses a ON u.id = a.user_id
            WHERE u.id = ?
        ");
        $stmt->execute([$order['user_id']]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($customer) {
            // Prepare shipment data
            $shipmentData = [
                'reference_no' => $order['order_id'],
                'name' => $customer['customer_name'] ?: 'Customer',
                'address' => $customer['address'] ?: 'Address not provided',
                'city' => $customer['city'] ?: 'City not provided',
                'state' => $customer['state'] ?: 'State not provided',
                'pincode' => $customer['pincode'] ?: '000000',
                'phone' => $customer['customer_phone'] ?: '0000000000',
                'email' => $customer['customer_email'] ?: 'customer@example.com',
                'cod_amount' => $order['price'] ?: '0',
                'declared_value' => $order['price'] ?: '0'
            ];
            
            // Log the shipment data being sent
            error_log('Shipment data: ' . json_encode($shipmentData));
            
            // Create shipment with Delhivery
            $shipmentResult = $delhiveryService->createShipment($shipmentData);
            
            if ($shipmentResult['success']) {
                $awbNumber = $shipmentResult['data']['packages'][0]['waybill'] ?? null;
                
                // Update order with Delhivery AWB
                $stmt = $pdo->prepare("
                    UPDATE tbl_orders 
                    SET order_status = ?, 
                        delhivery_awb = ?, 
                        delhivery_shipment_status = 'created',
                        delhivery_created_at = CURRENT_TIMESTAMP,
                        updated_at = CURRENT_TIMESTAMP 
                    WHERE id = ? AND seller_id = ?
                ");
                $result = $stmt->execute([$new_status, $awbNumber, $order_id, $seller_id]);
            } else {
                throw new Exception('Failed to create shipment: ' . $shipmentResult['message']);
            }
        } else {
            throw new Exception('Customer details not found');
        }
    } else {
        // Update order status only
        if ($new_status === 'processing') {
            $stmt = $pdo->prepare("UPDATE tbl_orders SET order_status = ?, updated_at = CURRENT_TIMESTAMP, processing_time = CURRENT_TIMESTAMP WHERE id = ? AND seller_id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE tbl_orders SET order_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND seller_id = ?");
        }
        $result = $stmt->execute([$new_status, $order_id, $seller_id]);
    }

    if (!$result || $stmt->rowCount() === 0) {
        throw new Exception('Failed to update order status in database');
    }

    $pdo->commit();
    
    // Log successful update
    error_log("Successfully updated order $order_id to status $new_status");
    
    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully',
        'new_status' => $new_status
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Order status update error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    error_log("Database error in order status update: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
}
?>

