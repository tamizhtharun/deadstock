<?php
require 'db_connection.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if token exists in the database
    $stmt = $conn->prepare("SELECT seller_id FROM sellers WHERE seller_verification_token = ? AND seller_verified = 0");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update seller_verified to 1
        $stmt_update = $conn->prepare("UPDATE sellers SET seller_verified = 1, seller_verification_token = NULL WHERE seller_verification_token = ?");
        $stmt_update->bind_param("s", $token);
        if ($stmt_update->execute()) {
            // Redirect to index.php after successful verification
            header("Location: index.php?showLoginModal=true");
            exit();
        }
        $stmt_update->close();
    }
    
    // Redirect to index.php if the token is invalid or already verified
    header("Location: index.php");
    exit();

    $stmt->close();
} else {
    // Redirect to index.php if the request is invalid
    header("Location: index.php");
    exit();
}

$conn->close();
?>
