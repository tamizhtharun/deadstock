<?php
/**
 * Comprehensive Delhivery API Test
 * Tests actual API endpoints with your JWT token
 */

require_once('config/delhivery_config.php');
require_once('services/DelhiveryService.php');

echo "<h2>Delhivery API Integration Test</h2>\n";

try {
    $delhiveryService = new DelhiveryService();
    
    echo "<h3>1. Testing Shipment Creation (Mock Data)</h3>\n";
    echo "Creating a test shipment...<br>\n";
    
    $testShipmentData = [
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
    
    $shipmentResult = $delhiveryService->createShipment($testShipmentData);
    
    if ($shipmentResult['success']) {
        echo "✅ Shipment creation successful<br>\n";
        echo "<pre>" . json_encode($shipmentResult['data'], JSON_PRETTY_PRINT) . "</pre>\n";
        
        // If we got an AWB, test tracking
        if (isset($shipmentResult['data']['packages'][0]['waybill'])) {
            $awbNumber = $shipmentResult['data']['packages'][0]['waybill'];
            echo "<h3>2. Testing Shipment Tracking</h3>\n";
            echo "Tracking AWB: " . $awbNumber . "<br>\n";
            
            $trackingResult = $delhiveryService->trackShipment($awbNumber);
            
            if ($trackingResult['success']) {
                echo "✅ Shipment tracking successful<br>\n";
                echo "<pre>" . json_encode($trackingResult['data'], JSON_PRETTY_PRINT) . "</pre>\n";
            } else {
                echo "❌ Shipment tracking failed: " . $trackingResult['message'] . "<br>\n";
            }
        }
    } else {
        echo "❌ Shipment creation failed: " . $shipmentResult['message'] . "<br>\n";
    }
    
    echo "<h3>3. API Logs</h3>\n";
    $logFile = 'logs/delhivery_api.log';
    if (file_exists($logFile)) {
        echo "Recent API logs:<br>\n";
        $logs = file_get_contents($logFile);
        $recentLogs = array_slice(explode("\n", $logs), -10);
        echo "<pre>" . implode("\n", $recentLogs) . "</pre>\n";
    } else {
        echo "No API logs found yet.<br>\n";
    }
    
} catch (Exception $e) {
    echo "❌ Test failed with exception: " . $e->getMessage() . "<br>\n";
    echo "Stack trace: <pre>" . $e->getTraceAsString() . "</pre>\n";
}

echo "<h3>Test Summary</h3>\n";
echo "<p>This test validates the complete Delhivery API integration including:</p>\n";
echo "<ul>\n";
echo "<li>JWT Bearer token authentication</li>\n";
echo "<li>Shipment creation with proper warehouse assignment</li>\n";
echo "<li>Shipment tracking</li>\n";
echo "<li>API logging</li>\n";
echo "</ul>\n";

echo "<p><strong>Next Steps:</strong></p>\n";
echo "<ul>\n";
echo "<li>If all tests pass, your integration is ready for production use</li>\n";
echo "<li>Test with real orders by updating their status to 'shipped'</li>\n";
echo "<li>Monitor the API logs for any issues</li>\n";
echo "<li>Switch to production environment when ready</li>\n";
echo "</ul>\n";
?>
