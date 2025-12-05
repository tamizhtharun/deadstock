<?php
$env = parse_ini_file('.env');
    $servername = $env['DB_HOST'];
    $username = $env['DB_USER'];
    $password = $env['DB_PASS'];
    $dbname = $env['DB_NAME'];

// MySQLi Connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check MySQLi connection
if ($conn->connect_error) {
    die("MySQLi connection failed: " . $conn->connect_error);
}
// else {
//     echo "Connected successfully using MySQLi<br>";
// }

// PDO Connection
try {
    // Create a PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully using PDO<br>";
} catch (PDOException $e) {
    echo "PDO connection failed: " . $e->getMessage();
}

?>