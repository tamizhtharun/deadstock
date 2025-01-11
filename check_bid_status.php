<?php
session_start(); // Start session for user data

// Get the logged-in user's ID
$user_id = isset($_SESSION['user_session']['id']) ? $_SESSION['user_session']['id'] : null;
$product_id = isset($_GET['product_id']) ? $_GET['product_id'] : null;

if ($user_id && $product_id) {
    // Database connection
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=deadstock", "root", "");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if the user has already placed a bid for this product
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bidding WHERE product_id = ? AND user_id = ?");
        $stmt->execute([$product_id, $user_id]);
        $bid_count = $stmt->fetchColumn();

        if ($bid_count > 0) {
            // User has already placed a bid for this product
            echo json_encode([
                "status" => "error",
                "message" => "You have already placed a bid for this product."
            ]);
        } else {
            // User has not placed a bid yet
            echo json_encode([
                "status" => "success",
                "message" => "You can place a bid for this product."
            ]);
        }

    } catch (PDOException $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Failed to check bid status: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "User not logged in or product ID missing."
    ]);
}
?>
