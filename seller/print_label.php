<?php
session_start();
require_once('../db_connection.php');
require_once('../services/DelhiveryService.php');
require_once('header.php');
require_once('../config/delhivery_config.php');

try {
    if (!isset($_SESSION['seller_session']['seller_id'])) { throw new Exception('Unauthorized'); }
    $sellerId = (int)$_SESSION['seller_session']['seller_id'];
    $orderId = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
    if (!$orderId) throw new Exception('Missing order_id');

    $stmt = $pdo->prepare("SELECT delhivery_awb FROM tbl_orders WHERE id=? AND seller_id=?");
    $stmt->execute([$orderId, $sellerId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order || empty($order['delhivery_awb'])) throw new Exception('AWB not found');

    $svc = new DelhiveryService();
    $res = $svc->generateShippingLabel($order['delhivery_awb'], ['format' => 'pdf']);
    if (!$res['success']) { throw new Exception($res['message'] ?? 'Failed to generate label'); }

    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="label_'.$order['delhivery_awb'].'.pdf"');
    echo $res['content'];
    exit;
} catch (Exception $e) {
    header('Content-Type: text/plain');
    http_response_code(400);
    echo 'Error: ' . $e->getMessage();
}

if(!isset($_GET['order_id'])) {
    die("Order ID not provided");
}

$order_id = $_GET['order_id'];

// Get waybill number for this order
$statement = $pdo->prepare("SELECT delhivery_awb FROM tbl_orders WHERE id = :order_id");
$statement->execute([':order_id' => $order_id]);
$result = $statement->fetch(PDO::FETCH_ASSOC);

if(!$result || !$result['delhivery_awb']) {
    die("Waybill number not found for this order");
}

$waybill = $result['delhivery_awb'];

// Set API URL based on environment
$api_url = DELHIVERY_API_BASE_URL . "api/p/packing_slip?wbns={$waybill}&pdf=true&pdf_size=";

// Initialize cURL
$ch = curl_init();

// Set authorization header based on environment
$headers = ['Content-Type: application/json', 'Accept: application/json'];
if (DELHIVERY_ENVIRONMENT === 'staging') {
    $headers[] = 'Authorization: Bearer ' . DELHIVERY_JWT_TOKEN;
} else {
    $headers[] = 'Authorization: Token ' . DELHIVERY_API_TOKEN;
}

curl_setopt_array($ch, [
    CURLOPT_URL => $api_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_TIMEOUT => DELHIVERY_CURL_TIMEOUT
]);

// Execute request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Check for cURL errors
if(curl_errno($ch)) {
    curl_close($ch);
    die("API Request Error: " . curl_error($ch));
}

curl_close($ch);

// Decode JSON response
$data = json_decode($response, true);

if($http_code != 200 || !isset($data['packages'][0]['pdf_download_link'])) {
    if(DELHIVERY_LOG_ENABLED) {
        error_log(date('[Y-m-d H:i:s] ') . "Failed to get shipping label. Response: " . $response . PHP_EOL, 
                 3, 
                 DELHIVERY_LOG_FILE);
    }
    die("Failed to get shipping label from Delhivery API");
}

// Get PDF download URL and redirect
$pdf_url = $data['packages'][0]['pdf_download_link'];
header("Location: " . $pdf_url);
exit;





