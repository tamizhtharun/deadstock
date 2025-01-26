<?php
include("../db_connection.php");

// Disable error reporting for production
error_reporting(0);
ini_set('display_errors', 0);

// Start output buffering
ob_start();

header('Content-Type: application/json');

function sendJsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    ob_end_clean();
    echo json_encode($data);
    exit;
}

if (!isset($_GET['seller_id'])) {
    sendJsonResponse(['error' => 'Seller ID not provided'], 400);
}

$seller_id = $_GET['seller_id'];

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch seller profile
    $stmt = $pdo->prepare("SELECT * FROM sellers WHERE seller_id = ?");
    $stmt->execute([$seller_id]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$seller) {
        sendJsonResponse(['error' => 'Seller not found'], 404);
    }

    // Fetch product data
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total, 
            SUM(p_is_active) as active, 
            COUNT(DISTINCT ecat_id) as categories
        FROM tbl_product 
        WHERE seller_id = ?
    ");
    $stmt->execute([$seller_id]);
    $products = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch product chart data grouped by date
    $stmt = $pdo->prepare("
        SELECT 
            DATE(p_date) as date,
            COUNT(*) as product_count
        FROM tbl_product
        WHERE seller_id = ?
        GROUP BY DATE(p_date)
        ORDER BY p_date ASC
    ");
    $stmt->execute([$seller_id]);
    $product_chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $chart_labels = [];
    $chart_values = [];
    foreach ($product_chart_data as $row) {
        $chart_labels[] = $row['date'];
        $chart_values[] = (int)$row['product_count'];
    }

    $products_chart = [
        'labels' => $chart_labels,
        'values' => $chart_values
    ];

    // Fetch bidding data
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total, 
            SUM(CASE WHEN bid_status = 2 THEN 1 ELSE 0 END) as winning,
            COALESCE(AVG(bid_price), 0) as avg_amount
        FROM bidding b
        JOIN tbl_product p ON b.product_id = p.id
        WHERE p.seller_id = ?
    ");
    $stmt->execute([$seller_id]);
    $bidding = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch bidding chart data grouped by date
    $stmt = $pdo->prepare("
        SELECT 
            DATE(bid_time) as date,
            COUNT(*) as bid_count
        FROM bidding b
        JOIN tbl_product p ON b.product_id = p.id
        WHERE p.seller_id = ?
        GROUP BY DATE(bid_time)
        ORDER BY bid_time ASC
        LIMIT 7
    ");
    $stmt->execute([$seller_id]);
    $bidding_chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $predefined_labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $bidding_chart_values = array_fill(0, 7, 0);

    $day_map = [
        'Monday' => 0, 
        'Tuesday' => 1, 
        'Wednesday' => 2, 
        'Thursday' => 3, 
        'Friday' => 4, 
        'Saturday' => 5, 
        'Sunday' => 6
    ];

    foreach ($bidding_chart_data as $row) {
        $day_name = date('l', strtotime($row['date']));
        $index = $day_map[$day_name];
        $bidding_chart_values[$index] = intval($row['bid_count']);
    }

    $bidding_chart = [
        'labels' => $predefined_labels,
        'values' => $bidding_chart_values
    ];

    // Fetch orders data
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN order_status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered
        FROM tbl_orders
        WHERE seller_id = ?
    ");
    $stmt->execute([$seller_id]);
    $orders = $stmt->fetch(PDO::FETCH_ASSOC);

    $success_rate = $orders['total'] > 0 ? ($orders['delivered'] / $orders['total']) * 100 : 0;

    // Maintain predefined labels and map actual data
    $stmt = $pdo->prepare("
        SELECT 
            DAYNAME(created_at) as day_name,
            COUNT(*) as order_count
        FROM tbl_orders
        WHERE seller_id = ? AND created_at >= DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY)
        GROUP BY day_name
    ");
    $stmt->execute([$seller_id]);
    $orders_chart_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $predefined_labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    $orders_chart_values = array_fill(0, 7, 0);

    $day_map = [
        'Monday' => 0, 
        'Tuesday' => 1, 
        'Wednesday' => 2, 
        'Thursday' => 3, 
        'Friday' => 4, 
        'Saturday' => 5, 
        'Sunday' => 6
    ];

    foreach ($orders_chart_data as $row) {
        $index = $day_map[$row['day_name']];
        $orders_chart_values[$index] = intval($row['order_count']);
    }

    $orders_chart = [
        'labels' => $predefined_labels,
        'values' => $orders_chart_values
    ];

    // Fetch order status chart data
    $stmt = $pdo->prepare("
        SELECT 
            order_status,
            COUNT(*) as status_count
        FROM tbl_orders
        WHERE seller_id = ?
        GROUP BY order_status
    ");
    $stmt->execute([$seller_id]);
    $order_status_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $order_status_labels = [];
    $order_status_values = [];
    foreach ($order_status_data as $row) {
        $order_status_labels[] = ucfirst($row['order_status']);
        $order_status_values[] = (int)$row['status_count'];
    }

    $order_status_chart = [
        'labels' => $order_status_labels,
        'values' => $order_status_values
    ];

    $response = [
        'seller' => $seller,
        'products' => [
            'total' => (int)$products['total'],
            'active' => (int)$products['active'],
            'categories' => (int)$products['categories'],
            'chart_data' => $products_chart
        ],
        'bidding' => [
            'total' => (int)$bidding['total'],
            'winning' => (int)$bidding['winning'],
            'avg_amount' => round((float)$bidding['avg_amount'], 2),
            'chart_data' => $bidding_chart
        ],
        'orders' => [
            'total' => (int)$orders['total'],
            'pending' => (int)$orders['pending'],
            'delivered' => (int)$orders['delivered'],
            'success_rate' => round($success_rate, 2),
            'chart_data' => $orders_chart,
            'status_data' => $order_status_chart
        ]
    ];

    sendJsonResponse($response);
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    sendJsonResponse(['error' => 'Database error occurred'], 500);
}