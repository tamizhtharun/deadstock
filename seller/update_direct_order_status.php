<?php
//update_direct_order_status.php
session_start();
require_once('../db_connection.php');

header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Validate inputs
if (!$order_id || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// Validate status
$valid_statuses = ['processing'];
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

    // Check if order can be updated
    if ($order['order_status'] !== 'pending') {
        throw new Exception('Can only update pending orders to processing');
    }

    // Update order status
    $stmt = $pdo->prepare("UPDATE tbl_orders SET order_status = ?, updated_at = CURRENT_TIMESTAMP, processing_time = CURRENT_TIMESTAMP WHERE id = ? AND seller_id = ?");
    $result = $stmt->execute([$new_status, $order_id, $seller_id]);

    if (!$result || $stmt->rowCount() === 0) {
        throw new Exception('Failed to update order status in database');
    }

    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully',
        'new_status' => $new_status
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred: ' . $e->getMessage()
    ]);
}
?>