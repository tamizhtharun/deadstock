<?php
session_start();
include 'db_connection.php'; // Include your DB connection file

$user_id = $_SESSION['user_session']['id'] ?? null;
$cart_count = 0;

if ($user_id) {
    $cart_query = mysqli_query($conn, "SELECT COUNT(*) AS count FROM tbl_cart WHERE user_id = '$user_id'");

    if ($cart_query) {
        $row = mysqli_fetch_assoc($cart_query);
        $cart_count = $row['count'] ?? 0;
    }
}

if ($cart_count > 0) {
    echo ($cart_count > 5) ? '5+' : $cart_count;
} else {
    echo ''; // Empty output if count is 0 (hides badge)
}
?>
