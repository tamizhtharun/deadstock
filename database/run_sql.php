<?php
// Database connection settings
$host = 'localhost';
$dbname = 'deadstock';
$username = 'root';
$password = '';

// Read SQL file
$sqlFile = __DIR__ . '/create_warehouse_table.sql';
if (!file_exists($sqlFile)) {
    die("Error: SQL file not found at: " . $sqlFile);
}
$sql = file_get_contents($sqlFile);

try {
    // Create connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Execute SQL
    $pdo->exec($sql);
    
    echo "SQL executed successfully. Warehouse table created or already exists.";
} catch(PDOException $e) {
    die("Error executing SQL: " . $e->getMessage());
}
?>
