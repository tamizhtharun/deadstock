<?php
include 'db_connection.php';
$stmt = $pdo->prepare('SELECT p_slug FROM tbl_product WHERE p_slug IS NOT NULL AND p_slug != "" LIMIT 1');
$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if($row) {
    echo $row['p_slug'];
} else {
    echo 'no-slug-found';
}
?>
