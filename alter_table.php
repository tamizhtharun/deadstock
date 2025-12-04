<?php
// Database connection (adjust credentials if needed)
$host = 'localhost';
$dbname = 'deadstock'; // Replace with your actual database name
$username = 'root'; // Replace with your MySQL username
$password = ''; // Replace with your MySQL password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Alter columns back to NOT NULL
    $columns = ['P', 'M', 'K', 'N', 'S', 'H', 'O'];
    foreach ($columns as $col) {
        $sql = "ALTER TABLE tbl_key MODIFY COLUMN $col INT NOT NULL;";
        $pdo->exec($sql);
        echo "Column $col modified back to NOT NULL.\n";
    }

    echo "All columns in tbl_key have been modified back to NOT NULL values.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
