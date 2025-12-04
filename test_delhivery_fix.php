<?php
// Test script to verify DelhiveryService fixes
require_once('config/delhivery_config.php');
require_once('services/DelhiveryService.php');

echo "=== DelhiveryService Test ===\n\n";

// Test 1: Shipment Data Validation
echo "Test 1: Shipment Data Validation\n";
$delhivery = new DelhiveryService();
$validShipmentData = [
    'reference_no' => 'TEST123',
    'name' => 'Test Customer',
    'address' => 'Test Address',
    'city' => 'Test City',
    'state' => 'Test State',
    'pincode' => '600001',
    'phone' => '9876543210'
];

try {
    $reflection = new ReflectionClass($delhivery);
    $validateMethod = $reflection->getMethod('validateShipmentData');
    $validateMethod->setAccessible(true);
    $validateMethod->invoke($delhivery, $validShipmentData);
    echo "Shipment data validation: PASSED\n";
} catch (Exception $e) {
    echo "Shipment data validation: FAILED - " . $e->getMessage() . "\n";
}

// Test 2: Invalid Shipment Data
echo "\nTest 2: Invalid Shipment Data\n";
$invalidShipmentData = [
    'reference_no' => 'TEST123',
    'name' => '', // Missing name
    'address' => 'Test Address',
    'city' => 'Test City',
    'state' => 'Test State',
    'pincode' => '110001',
    'phone' => '9876543210'
];

try {
    $reflection = new ReflectionClass($delhivery);
    $validateMethod = $reflection->getMethod('validateShipmentData');
    $validateMethod->setAccessible(true);
    $validateMethod->invoke($delhivery, $invalidShipmentData);
    echo "Invalid shipment data validation: FAILED - Should have thrown exception\n";
} catch (Exception $e) {
    echo "Invalid shipment data validation: PASSED - Correctly caught error: " . $e->getMessage() . "\n";
}

// Test 3: Shipment Creation (without pincode check)
echo "\nTest 3: Shipment Creation Test\n";
$shipmentData = [
    'reference_no' => 'TEST' . time(),
    'name' => 'Test Customer',
    'address' => '123 Test Street, Test Area',
    'city' => 'Delhi',
    'state' => 'Delhi',
    'pincode' => '600001',
    'phone' => '9876543210',
    'declared_value' => '1000'
];

try {
    $result = $delhivery->createShipment($shipmentData);
    echo "Shipment creation result: " . json_encode($result) . "\n";
    if ($result['success']) {
        echo "Shipment creation: PASSED\n";
    } else {
        echo "Shipment creation: FAILED - " . $result['message'] . "\n";
    }
} catch (Exception $e) {
    echo "Shipment creation: FAILED - Exception: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>
