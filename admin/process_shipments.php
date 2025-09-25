<?php
// process_shipments.php
header('Content-Type: application/json');
ob_start();
try {
    require_once('header.php');
    require_once('../services/DelhiveryService.php');

    if (!isset($_GET['action'])) { throw new Exception('Invalid action'); }
    if (!isset($pdo)) { throw new Exception('DB not available'); }

    $action = $_GET['action'];

    function send($d){ ob_clean(); echo json_encode($d); exit; }

    if ($action === 'create_shipment') {
        if (empty($_GET['order_id'])) throw new Exception('Missing order_id');
        $orderId = (int)$_GET['order_id'];
        
        // Get warehouse name from request, with validation
        $warehouseName = '';
        if (!empty($_GET['warehouse_name'])) {
            $warehouseName = trim($_GET['warehouse_name']);
            if (empty($warehouseName)) {
                throw new Exception('Warehouse name cannot be empty');
            }
        } else {
            // Fallback to default warehouse based on environment
            $warehouseName = (defined('DELHIVERY_ENVIRONMENT') && DELHIVERY_ENVIRONMENT === 'staging') ? 'TAMIL WAREHOUSE' : 'IMET WAREHOUSE';
        }

        $stmt = $pdo->prepare("SELECT o.*, a.full_name as customer_name, u.email as customer_email, a.phone_number as customer_phone,
            a.address, a.city, a.state, a.pincode, p.name as product_name
            FROM tbl_orders o
            LEFT JOIN users u ON o.user_id=u.id
            LEFT JOIN users_addresses a ON o.address_id=a.id
            LEFT JOIN tbl_products p ON o.product_id=p.id
            WHERE o.id=?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order) throw new Exception('Order not found');

        $svc = new DelhiveryService();
        $shipmentData = [
            'reference_no' => $order['order_id'],
            'name' => $order['customer_name'],
            'address' => $order['address'],
            'city' => $order['city'],
            'state' => $order['state'],
            'pincode' => $order['pincode'],
            'phone' => $order['customer_phone'],
            'email' => $order['customer_email'] ?: 'customer@example.com',
            'cod_amount' => $order['price'] ?: '0',
            'declared_value' => $order['price'] ?: '0',
            'warehouse_name' => $warehouseName,
            'product_description' => $order['product_name']
        ];
        $res = $svc->createShipment($shipmentData);
        if ($res['success']) {
            $awb = $res['awb_number'] ?? ($res['data']['packages'][0]['waybill'] ?? null);
            $up = $pdo->prepare("UPDATE tbl_orders SET delhivery_awb=?, delhivery_shipment_status='created', order_status='shipped', delhivery_created_at=CURRENT_TIMESTAMP, updated_at=CURRENT_TIMESTAMP WHERE id=?");
            $up->execute([$awb, $orderId]);
            send(['success'=>true,'message'=>'Shipment created with warehouse: ' . $warehouseName,'awb_number'=>$awb,'shipment_status'=>'created']);
        } else {
            send(['success'=>false,'message'=>$res['message'] ?? 'Shipment creation failed','serviceable'=>$res['serviceable'] ?? null]);
        }
    }

    if ($action === 'mark_packed') {
        if (empty($_GET['order_id'])) throw new Exception('Missing order_id');
        $orderId = (int)$_GET['order_id'];
        $up = $pdo->prepare("UPDATE tbl_orders SET seller_packed=1, updated_at=CURRENT_TIMESTAMP WHERE id=?");
        $ok = $up->execute([$orderId]);
        if (!$ok) throw new Exception('Failed to mark packed');
        send(['success'=>true,'message'=>'Order marked as packed']);
    }

    if ($action === 'request_pickup') {
        if (empty($_GET['order_id'])) throw new Exception('Missing order_id');
        $orderId = (int)$_GET['order_id'];
        $stmt = $pdo->prepare("SELECT delhivery_awb FROM tbl_orders WHERE id=?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order || empty($order['delhivery_awb'])) throw new Exception('AWB not found for order');

        $svc = new DelhiveryService();
        // Minimal pickup payload; set pickup location based on environment
        $pickupLocation = (defined('DELHIVERY_ENVIRONMENT') && DELHIVERY_ENVIRONMENT === 'staging') ? 'TAMIL WAREHOUSE' : 'IMET WAREHOUSE';
        $pickupData = [ 'pickup_location' => $pickupLocation, 'pickup_date' => date('Y-m-d'), 'pickup_time' => '10:00-18:00' ];
        $res = $svc->createPickup($pickupData);
        if ($res['success']) {
            $up = $pdo->prepare("UPDATE tbl_orders SET delhivery_shipment_status='manifested', updated_at=CURRENT_TIMESTAMP WHERE id=?");
            $up->execute([$orderId]);
            send(['success'=>true,'message'=>'Pickup requested','data'=>$res['data'] ?? null]);
        } else {
            send(['success'=>false,'message'=>$res['message'] ?? 'Pickup request failed']);
        }
    }

    if ($action === 'track') {
        if (empty($_GET['order_id'])) throw new Exception('Missing order_id');
        $orderId = (int)$_GET['order_id'];
        $stmt = $pdo->prepare("SELECT delhivery_awb FROM tbl_orders WHERE id=?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order || empty($order['delhivery_awb'])) throw new Exception('AWB not found for order');
        $svc = new DelhiveryService();
        $res = $svc->trackShipment($order['delhivery_awb']);
        send($res);
    }

    if ($action === 'print_label') {
        if (empty($_GET['order_id'])) throw new Exception('Missing order_id');
        $orderId = (int)$_GET['order_id'];
        $stmt = $pdo->prepare("SELECT delhivery_awb FROM tbl_orders WHERE id=?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$order || empty($order['delhivery_awb'])) throw new Exception('AWB not found for order');

        $svc = new DelhiveryService();
        $res = $svc->generateShippingLabel($order['delhivery_awb']);

        if ($res['success']) {
            // Set headers for PDF download
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="label_' . $order['delhivery_awb'] . '.pdf"');
            ob_clean();
            echo $res['content'];
            exit;
        } else {
            throw new Exception($res['message'] ?? 'Failed to generate label');
        }
    }

    throw new Exception('Invalid action');
} catch (Exception $e) {
    error_log('process_shipments: '.$e->getMessage());
    ob_clean();
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>

