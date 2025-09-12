<?php
session_start();
require_once('db_connection.php');
require_once('services/DelhiveryService.php');

// Check if user is logged in
if (!isset($_SESSION['user_session']['id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_session']['id'];
$trackingResult = null;
$errorMessage = '';

// Handle tracking request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['track_order'])) {
    $order_id = $_POST['order_id'];
    
    try {
        // Get order details
        $stmt = $pdo->prepare("
            SELECT o.*, u.name as customer_name, u.phone as customer_phone, u.email as customer_email,
                   a.address, a.city, a.state, a.pincode, p.p_name as product_name
            FROM tbl_orders o 
            LEFT JOIN tbl_users u ON o.user_id = u.id 
            LEFT JOIN tbl_addresses a ON o.address_id = a.id
            LEFT JOIN tbl_products p ON o.product_id = p.id
            WHERE o.id = ? AND o.user_id = ?
        ");
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            throw new Exception('Order not found or unauthorized access');
        }
        
        if (!$order['delhivery_awb']) {
            throw new Exception('No shipment tracking information available for this order yet');
        }
        
        // Track shipment using Delhivery API
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
        } else {
            $errorMessage = $trackingResult['message'];
        }
        
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// Get user's orders for dropdown
$stmt = $pdo->prepare("
    SELECT o.id, o.order_id, o.order_status, o.delhivery_awb, o.created_at, p.p_name as product_name
    FROM tbl_orders o 
    LEFT JOIN tbl_products p ON o.product_id = p.id
    WHERE o.user_id = ? AND o.order_status IN ('shipped', 'delivered')
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$userOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Your Shipment - Deadstock</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .tracking-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .tracking-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group select, .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .btn-track {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        
        .btn-track:hover {
            background: #0056b3;
        }
        
        .tracking-result {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-top: 20px;
        }
        
        .tracking-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .info-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
        }
        
        .info-item strong {
            display: block;
            color: #333;
            margin-bottom: 5px;
        }
        
        .status-timeline {
            border-left: 3px solid #007bff;
            padding-left: 20px;
            margin-top: 20px;
        }
        
        .status-item {
            position: relative;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .status-item:last-child {
            border-bottom: none;
        }
        
        .status-item::before {
            content: '';
            position: absolute;
            left: -26px;
            top: 20px;
            width: 12px;
            height: 12px;
            background: #007bff;
            border-radius: 50%;
        }
        
        .status-item.completed::before {
            background: #28a745;
        }
        
        .status-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .status-description {
            color: #666;
            font-size: 14px;
        }
        
        .status-location {
            color: #007bff;
            font-size: 13px;
            margin-top: 5px;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        
        .no-orders {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .no-orders i {
            font-size: 48px;
            color: #ddd;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="tracking-container">
        <h1><i class="fa fa-truck"></i> Track Your Shipment</h1>
        
        <?php if (empty($userOrders)): ?>
            <div class="no-orders">
                <i class="fa fa-shipping-fast"></i>
                <h3>No Shipped Orders Found</h3>
                <p>You don't have any shipped orders to track yet.</p>
                <a href="index.php" class="btn-track">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="tracking-form">
                <h3>Select Order to Track</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="order_id">Select Order:</label>
                        <select name="order_id" id="order_id" required>
                            <option value="">Choose an order...</option>
                            <?php foreach ($userOrders as $order): ?>
                                <option value="<?php echo $order['id']; ?>" 
                                        data-awb="<?php echo $order['delhivery_awb']; ?>">
                                    Order #<?php echo $order['order_id']; ?> - <?php echo $order['product_name']; ?>
                                    <?php if ($order['delhivery_awb']): ?>
                                        (AWB: <?php echo $order['delhivery_awb']; ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="track_order" class="btn-track">
                        <i class="fa fa-search"></i> Track Shipment
                    </button>
                </form>
            </div>
            
            <?php if ($errorMessage): ?>
                <div class="error-message">
                    <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($errorMessage); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($trackingResult && $trackingResult['success']): ?>
                <div class="tracking-result">
                    <h3><i class="fa fa-map-marker-alt"></i> Tracking Information</h3>
                    
                    <?php if (isset($trackingResult['data']['ShipmentData']) && count($trackingResult['data']['ShipmentData']) > 0): ?>
                        <?php $shipment = $trackingResult['data']['ShipmentData'][0]; ?>
                        
                        <div class="tracking-info">
                            <div class="info-item">
                                <strong>AWB Number</strong>
                                <?php echo htmlspecialchars($shipment['AWB'] ?? 'N/A'); ?>
                            </div>
                            <div class="info-item">
                                <strong>Current Status</strong>
                                <?php echo htmlspecialchars($shipment['Status'] ?? 'N/A'); ?>
                            </div>
                            <div class="info-item">
                                <strong>Current Location</strong>
                                <?php echo htmlspecialchars($shipment['Location'] ?? 'N/A'); ?>
                            </div>
                            <div class="info-item">
                                <strong>Last Updated</strong>
                                <?php echo htmlspecialchars($shipment['StatusDateTime'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        
                        <?php if (isset($shipment['Scans']) && count($shipment['Scans']) > 0): ?>
                            <h4>Shipment Timeline</h4>
                            <div class="status-timeline">
                                <?php foreach ($shipment['Scans'] as $scan): ?>
                                    <div class="status-item <?php echo ($scan === $shipment['Scans'][0]) ? 'completed' : ''; ?>">
                                        <div class="status-title"><?php echo htmlspecialchars($scan['Status'] ?? 'Unknown'); ?></div>
                                        <div class="status-description"><?php echo htmlspecialchars($scan['StatusDescription'] ?? ''); ?></div>
                                        <div class="status-location">
                                            <i class="fa fa-map-marker-alt"></i> <?php echo htmlspecialchars($scan['Location'] ?? 'N/A'); ?>
                                            <span style="float: right;"><?php echo htmlspecialchars($scan['StatusDateTime'] ?? 'N/A'); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>No detailed tracking information available yet. Please check back later.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        // Auto-refresh tracking every 30 seconds if tracking result is shown
        <?php if ($trackingResult && $trackingResult['success']): ?>
            setTimeout(function() {
                location.reload();
            }, 30000);
        <?php endif; ?>
    </script>
</body>
</html>
