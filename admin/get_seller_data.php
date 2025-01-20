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

    // Prepare chart data (using placeholder data for simplicity)
    $products_chart = [
        'labels' => ['Day 1', 'Day 2', 'Day 3', 'Day 4', 'Day 5', 'Day 6'],
        'values' => [5, 8, 12, 8, 10, 5]
    ];
    $bidding_chart = [
        'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        'values' => [25, 35, 28, 45, 38, 42, 35]
    ];
    $orders_chart = [
        'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
        'values' => [18, 25, 20, 30, 22]
    ];
    $order_status_chart = [
        'labels' => ['Processing', 'Shipped', 'Delivered', 'Canceled'],
        'values' => [34, 85, 170, 15]
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