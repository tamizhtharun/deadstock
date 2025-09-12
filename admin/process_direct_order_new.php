<?php
// process_direct_order.php - Updated for Delhivery Integration
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
    require_once('../services/DelhiveryService.php');

    if (!isset($_GET['action'])) {
        throw new Exception('Invalid action');
    }

    function updateOrderStatus($order_id, $new_status) {
        global $pdo;

        $valid_statuses = ['pending', 'processing', 'shipped', 'delivered', 'canceled'];
        if (!in_array($new_status, $valid_statuses)) {
            throw new Exception('Invalid status provided');
        }

        try {
            $pdo->beginTransaction();

            // Check if order exists and get current status with customer details
            $stmt = $pdo->prepare("
                SELECT o.*, u.name as customer_name, u.phone as customer_phone, u.email as customer_email,
                       a.address, a.city, a.state, a.pincode
                FROM tbl_orders o 
                LEFT JOIN tbl_users u ON o.user_id = u.id 
                LEFT JOIN tbl_addresses a ON o.address_id = a.id
                WHERE o.id = ? AND o.order_type = 'direct'
            ");
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
                    $valid_transition = ($new_status === 'delivered' || $new_status === 'canceled');
                    break;
            }

            if (!$valid_transition) {
                throw new Exception("Invalid status transition from {$order['order_status']} to {$new_status}");
            }

            // Handle shipment creation when status changes to 'shipped'
            if ($new_status === 'shipped' && $order['order_status'] !== 'shipped') {
                $delhiveryService = new DelhiveryService();
                
                // Prepare shipment data
                $shipmentData = [
                    'reference_no' => $order['order_id'],
                    'name' => $order['customer_name'],
                    'address' => $order['address'],
                    'city' => $order['city'],
                    'state' => $order['state'],
                    'pincode' => $order['pincode'],
                    'phone' => $order['customer_phone'],
                    'email' => $order['customer_email'],
                    'cod_amount' => $order['price'],
                    'declared_value' => $order['price']
                ];
                
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
                        WHERE id = ?
                    ");
                    $result = $stmt->execute([$new_status, $awbNumber, $order_id]);
                } else {
                    throw new Exception('Failed to create shipment: ' . $shipmentResult['message']);
                }
            } else {
                // Update order status only
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

            // Fetch the updated order details
            $stmt = $pdo->prepare("SELECT updated_at, processing_time, delhivery_awb, delhivery_shipment_status FROM tbl_orders WHERE id = ?");
            $stmt->execute([$order_id]);
            $updatedOrder = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'success' => true, 
                'message' => 'Order status updated successfully', 
                'new_status' => $new_status,
                'processing_time' => $updatedOrder['processing_time'],
                'awb_number' => $updatedOrder['delhivery_awb'],
                'shipment_status' => $updatedOrder['delhivery_shipment_status']
            ];

        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    function trackShipment($order_id) {
        global $pdo;
        
        try {
            // Get order details
            $stmt = $pdo->prepare("SELECT delhivery_awb FROM tbl_orders WHERE id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$order || !$order['delhivery_awb']) {
                throw new Exception('No AWB number found for this order');
            }
            
            $delhiveryService = new DelhiveryService();
            $trackingResult = $delhiveryService->trackShipment($order['delhivery_awb']);
            
            if ($trackingResult['success']) {
                // Update tracking history in database
                $trackingData = $trackingResult['data']['ShipmentData'][0] ?? null;
                if ($trackingData) {
                    $stmt = $pdo->prepare("
                        INSERT INTO delhivery_tracking_history 
                        (order_id, awb_number, status, status_description, location, timestamp) 
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $order_id,
                        $order['delhivery_awb'],
                        $trackingData['Status'] ?? 'Unknown',
                        $trackingData['StatusDescription'] ?? '',
                        $trackingData['Location'] ?? '',
                        $trackingData['StatusDateTime'] ?? date('Y-m-d H:i:s')
                    ]);
                }
                
                return [
                    'success' => true,
                    'data' => $trackingResult['data'],
                    'message' => 'Shipment tracking successful'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $trackingResult['message']
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to track shipment: ' . $e->getMessage()
            ];
        }
    }

    // Route actions based on GET parameters
    switch ($_GET['action']) {
        case 'update_status':
            if (!isset($_GET['order_id']) || !isset($_GET['status'])) {
                throw new Exception('Missing required parameters');
            }
            $result = updateOrderStatus($_GET['order_id'], $_GET['status']);
            sendJsonResponse($result);
            break;
            
        case 'track_shipment':
            if (!isset($_GET['order_id'])) {
                throw new Exception('Missing order ID');
            }
            $result = trackShipment($_GET['order_id']);
            sendJsonResponse($result);
            break;

        default:
            throw new Exception('Invalid action');
    }

} catch (Exception $e) {
    error_log("Caught exception in process_direct_order.php: " . $e->getMessage() . "\n" . $e->getTraceAsString());

    sendJsonResponse([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
