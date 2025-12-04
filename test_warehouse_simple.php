<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once('config/delhivery_config.php');
require_once('services/DelhiveryService.php');

// Initialize Delhivery service
$delhiveryService = new DelhiveryService();

// Function to log messages
function logMessage($message, $isError = false) {
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] " . ($isError ? 'ERROR: ' : '') . $message . PHP_EOL;
    echo "<pre>".htmlspecialchars($logEntry)."</pre>";
    flush();
}

logMessage("Starting Warehouse API Test");

// Test data for warehouse creation
$warehouseData = [
    'name' => 'TEST WAREHOUSE ' . time(),
    'registered_name' => 'Test Company ' . time(),
    'email' => 'test@example.com',
    'phone' => '9876543210',
    'address' => '123 Test Street',
    'city' => 'Mumbai',
    'state' => 'Maharashtra',
    'country' => 'India',
    'pin' => '400001',
    'contact_person' => 'Test Manager',
    'is_return' => 1,
    'is_fulfillment' => 1,
    'is_rto_address' => 0,
    'return_address' => '123 Test Street',
    'return_pin' => '400001',
    'return_city' => 'Mumbai',
    'return_state' => 'Maharashtra',
    'return_country' => 'India'
];

logMessage("Attempting to create warehouse...");
try {
    $response = $delhiveryService->createWarehouse($warehouseData);
    logMessage("Response: " . print_r($response, true));
    
    if (isset($response['success']) && $response['success']) {
        logMessage("Warehouse created successfully!");
        logMessage("Warehouse ID: " . ($response['data']['wh_id'] ?? 'N/A'));
    } else {
        logMessage("Failed to create warehouse: " . ($response['message'] ?? 'Unknown error'), true);
    }
} catch (Exception $e) {
    logMessage("Exception: " . $e->getMessage(), true);
    logMessage("Stack trace: " . $e->getTraceAsString(), true);
}

logMessage("Test completed");
?>
