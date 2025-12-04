<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set content type to HTML with proper formatting
echo "<html><head><title>Warehouse API Test</title>
<style>
    body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; }
    pre { background: #f4f4f4; padding: 10px; border: 1px solid #ddd; border-radius: 4px; overflow-x: auto; }
    .success { color: green; }
    .error { color: red; }
    .section { margin-bottom: 30px; padding: 15px; border: 1px solid #eee; border-radius: 5px; }
</style>
</head><body>";

require_once('config/delhivery_config.php');
require_once('services/DelhiveryService.php');
require_once('db_connection.php');

// Initialize Delhivery service with detailed logging
$delhiveryService = new DelhiveryService();

// Log file setup
$logFile = __DIR__ . '/logs/warehouse_test_' . date('Y-m-d_His') . '.log';
if (!file_exists(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}

// Function to log messages to file and output
function logMessage($message, $isError = false) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] " . ($isError ? 'ERROR: ' : '') . $message . PHP_EOL;
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    // Also output to browser
    $class = $isError ? 'error' : '';
    echo "<div class='{$class}'>" . htmlspecialchars($logEntry) . "</div>";
    
    // Flush output buffer to see logs in real-time
    ob_flush();
    flush();
}

logMessage("Starting Warehouse API Test");

// Display current configuration
logMessage("Current Configuration:");
logMessage("- Environment: " . DELHIVERY_ENVIRONMENT);
logMessage("- Auth Type: " . DELHIVERY_AUTH_TYPE);
logMessage("- API Base URL: " . DELHIVERY_API_BASE_URL);

// Generate a unique warehouse name with timestamp
$timestamp = time();
$warehouseData = [
    'name' => 'TEST WAREHOUSE ' . $timestamp,
    'registered_name' => 'Test Warehouse Company ' . $timestamp,
    'email' => 'test.warehouse@example.com',
    'phone' => '9876543210',
    'address' => '123 Test Street, Test Area',
    'city' => 'Mumbai',
    'state' => 'Maharashtra',
    'country' => 'India',
    'pin' => '400001',
    'contact_person' => 'Test Manager',
    'is_return' => 1,
    'is_fulfillment' => 1,
    'is_rto_address' => 0,
    'return_address' => '123 Test Street, Test Area',
    'return_pin' => '400001',
    'return_city' => 'Mumbai',
    'return_state' => 'Maharashtra',
    'return_country' => 'India',
    'address_2' => 'Near Test Landmark',
    'landmark' => 'Opposite Test Tower',
    'gstin' => '22AAAAA0000A1Z5',
    'gst_company_name' => 'Test Company',
    'gst_company_address' => '123 Test Street, Test Area',
    'gst_state_code' => '27', // Maharashtra state code
    'gst_city' => 'Mumbai',
    'gst_pin_code' => '400001',
    'gst_email' => 'test.gst@example.com',
    'gst_phone' => '9876543210'
];

logMessage("Test Data:");
logMessage(print_r($warehouseData, true));

echo "<div class='section'>";
echo "<h1>Testing Warehouse API</h1>";
echo "<p>Log file: " . htmlspecialchars($logFile) . "</p>";
echo "</div>";

try {
    echo "<div class='section'>";
    echo "<h2>1. Creating a new warehouse</h2>";
    
    logMessage("Initiating warehouse creation...");
    
    try {
        logMessage("Sending request to create warehouse...");
        
        // Make a direct cURL request to debug
        $ch = curl_init();
        $url = 'https://track.delhivery.com/api/backend/clientwarehouse/create/';
        $apiToken = '90c9ac93c628af65837f4840162b5810c4d43102';
        
        $warehouseData['name'] = 'TEST WAREHOUSE ' . time(); // Ensure unique name
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Token ' . $apiToken
        ];
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($warehouseData),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $responseHeaders = substr($response, 0, $headerSize);
        $responseBody = substr($response, $headerSize);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        logMessage("HTTP Status Code: " . $httpCode);
        logMessage("Response Headers: " . $responseHeaders);
        logMessage("Response Body: " . $responseBody);
        
        if ($error) {
            throw new Exception("cURL Error: " . $error);
        }
        
        $createResponse = json_decode($responseBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $createResponse = $responseBody; // Return raw response if not JSON
        }
        
        logMessage("Raw API Response: " . print_r($createResponse, true));
        
        if (isset($createResponse['success']) && $createResponse['success']) {
            logMessage("Warehouse created successfully!");
            logMessage("Response: " . print_r($createResponse, true));
        } else {
            $errorMsg = $createResponse['message'] ?? 'Unknown error occurred';
            logMessage("Error creating warehouse: " . $errorMsg, true);
            
            // Additional debug info
            if (isset($createResponse['http_code'])) {
                logMessage("HTTP Status Code: " . $createResponse['http_code'], true);
            }
            
            // Log the full response for debugging
            logMessage("Full response: " . print_r($createResponse, true));
        }
    } catch (Exception $e) {
        logMessage("Exception while creating warehouse: " . $e->getMessage(), true);
        logMessage("Stack trace: " . $e->getTraceAsString(), true);
        $createResponse = ['success' => false, 'message' => $e->getMessage()];
    }
    
    echo "<pre>Create Response: " . htmlspecialchars(print_r($createResponse, true)) . "</pre>";
    echo "</div>";

    if (isset($createResponse['success']) && $createResponse['success'] && !empty($createResponse['data']['wh_id'])) {
        $warehouseId = $createResponse['data']['wh_id'];
        echo "<p>Warehouse created successfully with ID: $warehouseId</p>";
        
        // Test updating the warehouse
        echo "<h2>2. Updating the warehouse</h2>";
        $updateData = [
            'name' => 'Updated ' . $warehouseData['name'],
            'phone' => '9876543222',
            'is_fulfillment' => 0
        ];
        
        $updateResponse = $delhiveryService->updateWarehouse($warehouseId, $updateData);
        echo "<pre>Update Response: " . print_r($updateResponse, true) . "</pre>";
        
        if ($updateResponse['success']) {
            echo "<p>Warehouse updated successfully</p>";
        } else {
            echo "<p>Failed to update warehouse: " . ($updateResponse['message'] ?? 'Unknown error') . "</p>";
        }
        
        // Test getting warehouse details
        echo "<h2>3. Getting warehouse details</h2>";
        $getResponse = $delhiveryService->getWarehouse($warehouseId);
        echo "<pre>Get Response: " . print_r($getResponse, true) . "</pre>";
        
        if ($getResponse['success']) {
            echo "<p>Warehouse details retrieved successfully</p>";
        } else {
            echo "<p>Failed to get warehouse details: " . ($getResponse['message'] ?? 'Unknown error') . "</p>";
        }
    } else {
        echo "<p>Failed to create warehouse: " . ($createResponse['message'] ?? 'Unknown error') . "</p>";
    }
} catch (Exception $e) {
    logMessage("Test failed with exception: " . $e->getMessage(), true);
    logMessage("Stack trace: " . $e->getTraceAsString(), true);
}

// Test getting all warehouses
echo "<div class='section'>
    <h2>4. Getting all warehouses</h2>";
    
    logMessage("Fetching all warehouses...");
    $allWarehousesResponse = $delhiveryService->getWarehouse('');
    
    if (isset($allWarehousesResponse['success']) && $allWarehousesResponse['success']) {
        $count = is_array($allWarehousesResponse['data']) ? count($allWarehousesResponse['data']) : 0;
        logMessage("Found {$count} warehouses");
    } else {
        $errorMsg = $allWarehousesResponse['message'] ?? 'Unknown error occurred';
        logMessage("Failed to fetch warehouses: " . $errorMsg, true);
    }
    
    echo "<pre>All Warehouses Response: " . htmlspecialchars(print_r($allWarehousesResponse, true)) . "</pre>";
    echo "</div>";

// Add link to view log file
echo "<div class='section'>
    <h2>Test Complete</h2>
    <p>Check the log file for detailed information: <a href='file:///{$logFile}' target='_blank'>{$logFile}</a></p>
</div>";

echo "</body></html>";

// Test saving to local database
if (isset($warehouseId) && !empty($warehouseId)) {
    echo "<h2>5. Saving to local database</h2>";
    
    // Prepare data for local database
    $localData = array_merge($warehouseData, [
        'warehouse_id' => $warehouseId,
        'seller_id' => 1, // Assuming seller ID 1 for testing
        'pincode' => $warehouseData['pin'],
        'is_return' => $warehouseData['is_return'] ?? 0,
        'is_fulfillment' => $warehouseData['is_fulfillment'] ?? 0,
        'is_rto_address' => $warehouseData['is_rto_address'] ?? 0,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ]);
    
    // Insert into local database
    $columns = implode(', ', array_keys($localData));
    $placeholders = implode(', ', array_fill(0, count($localData), '?'));
    $values = array_values($localData);
    
    $sql = "INSERT INTO seller_warehouses ($columns) VALUES ($placeholders)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $types = str_repeat('s', count($values));
        $stmt->bind_param($types, ...$values);
        
        if ($stmt->execute()) {
            echo "<p>Warehouse saved to local database successfully</p>";
        } else {
            echo "<p>Error saving to local database: " . $stmt->error . "</p>";
        }
        
        $stmt->close();
    } else {
        echo "<p>Error preparing statement: " . $conn->error . "</p>";
    }
}

$conn->close();
?>
