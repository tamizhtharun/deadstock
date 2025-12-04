<?php
/**
 * Database Update Script for Delhivery Integration
 * This script will update the database schema to support Delhivery integration
 */

require_once('db_connection.php');

echo "<h2>Database Update for Delhivery Integration</h2>\n";

try {
    $pdo->beginTransaction();
    
    echo "<h3>1. Adding Delhivery columns to tbl_orders table...</h3>\n";
    
    // Check if columns already exist
    $stmt = $pdo->query("SHOW COLUMNS FROM tbl_orders LIKE 'delhivery_%'");
    $existingColumns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $columnsToAdd = [
        'delhivery_awb' => 'VARCHAR(50) NULL',
        'delhivery_pickup_token' => 'VARCHAR(100) NULL',
        'delhivery_shipment_status' => 'VARCHAR(50) NULL',
        'delhivery_created_at' => 'TIMESTAMP NULL',
        'delhivery_updated_at' => 'TIMESTAMP NULL'
    ];
    
    foreach ($columnsToAdd as $columnName => $columnDefinition) {
        if (!in_array($columnName, $existingColumns)) {
            $sql = "ALTER TABLE tbl_orders ADD COLUMN {$columnName} {$columnDefinition}";
            $pdo->exec($sql);
            echo "✅ Added column: {$columnName}<br>\n";
        } else {
            echo "ℹ️ Column already exists: {$columnName}<br>\n";
        }
    }
    
    echo "<h3>2. Creating indexes...</h3>\n";
    
    // Create indexes
    $indexes = [
        'idx_delhivery_awb' => 'CREATE INDEX idx_delhivery_awb ON tbl_orders(delhivery_awb)',
        'idx_delhivery_pickup_token' => 'CREATE INDEX idx_delhivery_pickup_token ON tbl_orders(delhivery_pickup_token)'
    ];
    
    foreach ($indexes as $indexName => $sql) {
        try {
            $pdo->exec($sql);
            echo "✅ Created index: {$indexName}<br>\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate key name') !== false) {
                echo "ℹ️ Index already exists: {$indexName}<br>\n";
            } else {
                echo "❌ Failed to create index {$indexName}: " . $e->getMessage() . "<br>\n";
            }
        }
    }
    
    echo "<h3>3. Creating delhivery_tracking_history table...</h3>\n";
    
    $createTrackingTable = "
        CREATE TABLE IF NOT EXISTS delhivery_tracking_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            awb_number VARCHAR(50) NOT NULL,
            status VARCHAR(100) NOT NULL,
            status_description TEXT,
            location VARCHAR(255),
            timestamp DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES tbl_orders(id) ON DELETE CASCADE,
            INDEX idx_order_id (order_id),
            INDEX idx_awb_number (awb_number),
            INDEX idx_timestamp (timestamp)
        )
    ";
    
    $pdo->exec($createTrackingTable);
    echo "✅ Created delhivery_tracking_history table<br>\n";
    
    echo "<h3>4. Creating delhivery_pickup_requests table...</h3>\n";
    
    $createPickupTable = "
        CREATE TABLE IF NOT EXISTS delhivery_pickup_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            pickup_token VARCHAR(100) NOT NULL,
            pickup_location VARCHAR(255) NOT NULL,
            pickup_date DATE NOT NULL,
            pickup_time TIME NOT NULL,
            status VARCHAR(50) DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (order_id) REFERENCES tbl_orders(id) ON DELETE CASCADE,
            INDEX idx_order_id (order_id),
            INDEX idx_pickup_token (pickup_token),
            INDEX idx_status (status)
        )
    ";
    
    $pdo->exec($createPickupTable);
    echo "✅ Created delhivery_pickup_requests table<br>\n";
    
    echo "<h3>5. Creating delhivery_api_logs table...</h3>\n";
    
    $createLogsTable = "
        CREATE TABLE IF NOT EXISTS delhivery_api_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NULL,
            api_endpoint VARCHAR(255) NOT NULL,
            request_data TEXT,
            response_data TEXT,
            status_code INT,
            success BOOLEAN DEFAULT FALSE,
            error_message TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_order_id (order_id),
            INDEX idx_api_endpoint (api_endpoint),
            INDEX idx_created_at (created_at)
        )
    ";
    
    $pdo->exec($createLogsTable);
    echo "✅ Created delhivery_api_logs table<br>\n";
    
    echo "<h3>6. Updating existing orders...</h3>\n";
    
    $updateOrders = "
        UPDATE tbl_orders SET 
            delhivery_shipment_status = 'pending',
            delhivery_created_at = CURRENT_TIMESTAMP
        WHERE delhivery_shipment_status IS NULL
    ";
    
    $result = $pdo->exec($updateOrders);
    echo "✅ Updated {$result} existing orders<br>\n";
    
    $pdo->commit();
    
    echo "<h3>✅ Database Update Completed Successfully!</h3>\n";
    echo "<p>All database changes have been applied. The Delhivery integration is now ready to use.</p>\n";
    
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "<h3>❌ Database Update Failed</h3>\n";
    echo "<p>Error: " . $e->getMessage() . "</p>\n";
    echo "<p>Please check your database connection and try again.</p>\n";
}

echo "<h3>Next Steps:</h3>\n";
echo "<ul>\n";
echo "<li>Configure your Delhivery API credentials in config/delhivery_config.php</li>\n";
echo "<li>Run the test script again to verify everything is working</li>\n";
echo "<li>Test with a real order by updating its status to 'shipped'</li>\n";
echo "</ul>\n";
?>
