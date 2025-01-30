<?php
// process_direct_order.php
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

    function updateOrderStatus($order_id, $new_status, $tracking_id = null) {
        global $pdo;

        $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'canceled'];
        if (!in_array($new_status, $valid_statuses)) {
            throw new Exception('Invalid status provided');
        }

        try {
            $pdo->beginTransaction();

            // Check if order exists and get current status
            $stmt = $pdo->prepare("SELECT order_status, tracking_id FROM tbl_orders WHERE id = ? AND order_type = 'direct'");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$order) {
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
                $stmt = $pdo->prepare("UPDATE tbl_orders SET tracking_id = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $result = $stmt->execute([$tracking_id, $order_id]);
            } else {
                $stmt = $pdo->prepare("UPDATE tbl_orders SET order_status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $result = $stmt->execute([$new_status, $order_id]);
            }

            if (!$result || $stmt->rowCount() === 0) {
                throw new Exception('Failed to update order status in database');
            }

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
            throw $e;
        }
    }

    // Route actions based on GET parameters
    switch ($_GET['action']) {
        case 'update_status':
            if (!isset($_GET['order_id']) || !isset($_GET['status'])) {
                throw new Exception('Missing required parameters');
            }
            $tracking_id = isset($_GET['tracking_id']) ? $_GET['tracking_id'] : null;
            $result = updateOrderStatus($_GET['order_id'], $_GET['status'], $tracking_id);
            sendJsonResponse($result);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    error_log("Caught exception in process_direct_order.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());

    sendJsonResponse([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}

sendJsonResponse([
    'success' => false,
    'message' => 'An unexpected error occurred'
]);
?>
