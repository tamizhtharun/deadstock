<?php
// Prevent any output before headers
ob_start();

include '../db_connection.php';

// Check if PDO is available
if (!isset($pdo)) {
    die('Database connection failed.');
}

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=customer_list.csv');

$output = fopen("php://output", "w");
fputcsv($output, array('S.N', 'Username', 'Email Address', 'Contact Number', 'Joining Date'));

$statement = $pdo->prepare("SELECT * FROM users");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

$sn = 1;
foreach ($result as $row) {
    $joining_date = !empty($row['created_at']) && $row['created_at'] != '0000-00-00' ? '="' . date('d/m/Y', strtotime($row['created_at'])) . '"' : 'N/A';
    fputcsv($output, array($sn, $row['username'], $row['email'], $row['phone_number'], $joining_date));
    $sn++;
}

fclose($output);
ob_end_flush();
?>
