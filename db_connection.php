<?php
$servername = "sql12.freesqldatabase.com"; // Your server name
$username = "sql12743731";     // Your database username
$password = "YYFL11pyZs";     // Your database password
$dbname = "sql12743731";  // Your database name

// MySQLi Connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check MySQLi connection
if ($conn->connect_error) {
    die("MySQLi connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully using MySQLi<br>";
}

// PDO Connection
try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully using PDO<br>";
} catch (PDOException $e) {
    echo "PDO connection failed: " . $e->getMessage();
}

// Close MySQLi connection

?>
