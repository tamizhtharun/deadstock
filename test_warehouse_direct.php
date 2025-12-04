<?php
// Load configuration
require_once __DIR__ . '/config/delhivery_config.php';

// Check if required constants are defined
if (!defined('DELHIVERY_API_BASE_URL') || !defined('DELHIVERY_JWT_TOKEN')) {
    die("Error: Required configuration constants are not defined. Please check your delhivery_config.php file.\n");
}

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers for JSON response
header('Content-Type: application/json');

// Log function
function logMessage($message) {
    $logFile = __DIR__ . '/logs/warehouse_direct_test_' . date('Y-m-d_His') . '.log';
    $timestamp = '[' . date('Y-m-d H:i:s') . '] ';
    file_put_contents($logFile, $timestamp . $message . "\n", FILE_APPEND);
    echo $message . "\n";
}

// Test data for warehouse creation - matching the working Postman request
$warehouseData = [
    'phone' => '9999999999',
    'city' => 'Kota',
    'name' => 'test_name_' . time(),
    'pin' => '110042',
    'address' => 'Test Address',
    'country' => 'India',
    'email' => 'test.warehouse@example.com',
    'registered_name' => 'Test Registered Name',
    'return_address' => 'Test Return Address',
    'return_pin' => '110042',
    'return_city' => 'Kota',
    'return_state' => 'Delhi',
    'return_country' => 'India'
];

// Log test data
logMessage("Test Data: " . json_encode($warehouseData, JSON_PRETTY_PRINT));

// API endpoint from the working Postman request
$apiUrl = DELHIVERY_API_BASE_URL . 'api/backend/clientwarehouse/create/';
logMessage("API URL: " . $apiUrl);

// Headers with Bearer token as per working Postman request
$headers = [
    'Authorization: Bearer ' . DELHIVERY_JWT_TOKEN,
    'Content-Type: application/json',
    'Accept: application/json'
];

// Log headers
logMessage("Request Headers: " . json_encode($headers, JSON_PRETTY_PRINT));

// Initialize cURL
$ch = curl_init();

// Set cURL options
$options = [
    CURLOPT_URL => $apiUrl,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($warehouseData),
    CURLOPT_HTTPHEADER => $headers,
    CURLOPT_SSL_VERIFYPEER => false, // Only for testing, remove in production
    CURLOPT_VERBOSE => true,
    CURLOPT_HEADER => true
];

// Set cURL options
curl_setopt_array($ch, $options);

// Execute cURL request
$response = curl_exec($ch);

// Get HTTP status code
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Get request headers
$requestHeaders = curl_getinfo($ch, CURLINFO_HEADER_OUT);

// Log request details
logMessage("=== Request Details ===");
logMessage("URL: " . $apiUrl);
logMessage("Method: POST");
logMessage("Request Headers: " . print_r($requestHeaders, true));
logMessage("Request Body: " . json_encode($warehouseData, JSON_PRETTY_PRINT));

// Log response details
logMessage("=== Response Details ===");
logMessage("HTTP Status: " . $httpCode);
logMessage("Response Headers: " . print_r(curl_getinfo($ch), true));
logMessage("Response Body: " . $response);

// Close cURL
curl_close($ch);

// Output response
echo "Test completed. Check the log file for details.\n";
?>
