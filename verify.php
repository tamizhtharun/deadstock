<?php
include 'db_connection.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Check if the token exists
    $query = "SELECT status FROM users WHERE token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($status);
        $stmt->fetch();

        if ($status == 1) {
            // User is already verified, redirect without alert
            header("Location: index.php?showLoginModal=true");
            exit();
        } else {
            // Update status to verified (1)
            $update_query = "UPDATE users SET status = 1 WHERE token = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("s", $token);
            $update_stmt->execute();

            // Redirect to index.php without showing an alert
            header("Location: index.php?showLoginModal=true");
            exit();
        }
    } else {
        // Invalid token, redirect to index.php
        header("Location: index.php");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // No token found, just redirect
    header("Location: index.php");
    exit();
}
?>

