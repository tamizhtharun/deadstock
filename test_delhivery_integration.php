<?php
/**
 * Test script for Delhivery integration
 * This script tests the basic functionality of the Delhivery service
 */

require_once('config/delhivery_config.php');
require_once('services/DelhiveryService.php');

echo "<h2>Delhivery Integration Test</h2>\n";

// Test 1: Check configuration
echo "<h3>1. Configuration Test</h3>\n";
echo "Environment: " . DELHIVERY_ENVIRONMENT . "<br>\n";
echo "Auth Type: " . DELHIVERY_AUTH_TYPE . "<br>\n";

if (DELHIVERY_AUTH_TYPE === 'bearer') {
    if (defined('DELHIVERY_JWT_TOKEN') && DELHIVERY_JWT_TOKEN !== 'YOUR_STAGING_JWT_BEARER_TOKEN_HERE') {
        echo "✅ JWT Bearer Token is configured<br>\n";
    } else {
        echo "❌ JWT Bearer Token is not configured properly<br>\n";
    }
} else {
    if (defined('DELHIVERY_API_TOKEN') && DELHIVERY_API_TOKEN !== 'YOUR_PRODUCTION_API_TOKEN_HERE') {
        echo "✅ API Token is configured<br>\n";
    } else {
        echo "❌ API Token is not configured properly<br>\n";
    }
}

echo "ℹ️ Client ID not required for JWT Bearer authentication<br>\n";

// Test 2: Service instantiation
echo "<h3>2. Service Instantiation Test</h3>\n";
try {
    $delhiveryService = new DelhiveryService();
    echo "✅ DelhiveryService instantiated successfully<br>\n";
} catch (Exception $e) {
    echo "❌ Failed to instantiate DelhiveryService: " . $e->getMessage() . "<br>\n";
}

// Test 3: Shipment creation test
echo "<h3>3. Shipment Creation Test</h3>\n";
try {
    $testData = [
        'reference_no' => 'TEST_' . time(),
        'name' => 'Test Customer',
        'address' => '123 Test Street',
        'city' => 'Delhi',
        'state' => 'Delhi',
        'pincode' => '110001',
        'phone' => '9999999999',
        'email' => 'test@example.com',
        'cod_amount' => 100,
        'declared_value' => 100
    ];
    
    $result = $delhiveryService->createShipment($testData);
    if ($result['success']) {
        echo "✅ Shipment creation test successful<br>\n";
        echo "Response: " . json_encode($result['data'], JSON_PRETTY_PRINT) . "<br>\n";
    } else {
        echo "❌ Shipment creation test failed: " . $result['message'] . "<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Exception during shipment creation: " . $e->getMessage() . "<br>\n";
}

// Test 4: Database connection test
echo "<h3>4. Database Connection Test</h3>\n";
try {
    require_once('db_connection.php');
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM tbl_orders");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Database connection successful<br>\n";
    echo "Total orders in database: " . $result['count'] . "<br>\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>\n";
}

// Test 5: Check if new database columns exist
echo "<h3>5. Database Schema Test</h3>\n";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_orders LIKE 'delhivery_%'");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($columns) >= 4) {
        echo "✅ Delhivery database columns exist<br>\n";
        foreach ($columns as $column) {
            echo "- " . $column['Field'] . " (" . $column['Type'] . ")<br>\n";
        }
    } else {
        echo "❌ Delhivery database columns are missing. Please run the database update script.<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Database schema check failed: " . $e->getMessage() . "<br>\n";
}

// Test 6: Check if tracking history table exists
echo "<h3>6. Tracking History Table Test</h3>\n";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'delhivery_tracking_history'");
    $table = $stmt->fetch();
    
    if ($table) {
        echo "✅ Tracking history table exists<br>\n";
    } else {
        echo "❌ Tracking history table is missing. Please run the database update script.<br>\n";
    }
} catch (Exception $e) {
    echo "❌ Tracking history table check failed: " . $e->getMessage() . "<br>\n";
}

// Test 7: File permissions test
echo "<h3>7. File Permissions Test</h3>\n";
$logFile = 'logs/delhivery_api.log';
$logDir = dirname($logFile);

if (!is_dir($logDir)) {
    if (mkdir($logDir, 0755, true)) {
        echo "✅ Log directory created successfully<br>\n";
    } else {
        echo "❌ Failed to create log directory<br>\n";
    }
} else {
    echo "✅ Log directory exists<br>\n";
}

if (is_writable($logDir)) {
    echo "✅ Log directory is writable<br>\n";
} else {
    echo "❌ Log directory is not writable<br>\n";
}

echo "<h3>Test Summary</h3>\n";
echo "<p>If all tests pass, your Delhivery integration is ready to use!</p>\n";
echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>Configure your actual Delhivery API credentials in config/delhivery_config.php</li>\n";
echo "<li>Run the database update script if any schema tests failed</li>\n";
echo "<li>Test with a real order by updating its status to 'shipped'</li>\n";
echo "<li>Check the tracking functionality using the new track_shipment.php page</li>\n";
echo "</ul>\n";

echo "<p><strong>Important:</strong> This is a test script. Remove it from production after testing.</p>\n";
?>
