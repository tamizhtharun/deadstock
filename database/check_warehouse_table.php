<?php
require_once(__DIR__ . '/../db_connection.php');

// Check if seller_warehouses table exists
$result = $conn->query("SHOW TABLES LIKE 'seller_warehouses'");
if ($result->num_rows > 0) {
    echo "Table 'seller_warehouses' exists.\n";
    
    // Show table structure
    echo "\nTable structure:\n";
    $structure = $conn->query("DESCRIBE seller_warehouses");
    if ($structure) {
        echo "+------------------+------------------+------+-----+---------+----------------+\n";
        echo "| Field            | Type             | Null | Key | Default | Extra          |\n";
        echo "+------------------+------------------+------+-----+---------+----------------+\n";
        while ($row = $structure->fetch_assoc()) {
            printf("| %-16s | %-16s | %-4s | %-3s | %-7s | %-14s |\n", 
                $row['Field'], 
                $row['Type'],
                $row['Null'],
                $row['Key'],
                $row['Default'] ?? 'NULL',
                $row['Extra']
            );
        }
        echo "+------------------+------------------+------+-----+---------+----------------+\n";
    }
    
    // Show sample data
    echo "\nSample data (first 5 rows):\n";
    $data = $conn->query("SELECT * FROM seller_warehouses LIMIT 5");
    if ($data && $data->num_rows > 0) {
        $first = true;
        while ($row = $data->fetch_assoc()) {
            if ($first) {
                // Print headers
                echo implode("\t", array_keys($row)) . "\n";
                $first = false;
            }
            // Print data
            echo implode("\t", array_values($row)) . "\n";
        }
    } else {
        echo "No data found in seller_warehouses table.\n";
    }
} else {
    echo "Table 'seller_warehouses' does not exist.\n";
    
    // Offer to create the table
    echo "\nWould you like to create the table? (y/n): ";
    $handle = fopen('php://stdin', 'r');
    $input = trim(fgets($handle));
    
    if (strtolower($input) === 'y') {
        $sql = file_get_contents('create_warehouse_table.sql');
        if ($conn->multi_query($sql)) {
            echo "Table 'seller_warehouses' created successfully.\n";
        } else {
            echo "Error creating table: " . $conn->error . "\n";
        }
    }
}

$conn->close();
?>
