<?php
session_start(); // Start session for user data

// Check if the user is logged in
if (!isset($_SESSION['user_session']['id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in to submit a bid."
    ]);
    exit;
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_session']['id'];
$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : null;

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
        exit;
    }

    // Validate the POST inputs
    $bid_quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $bid_price = filter_input(INPUT_POST, 'proposed_price', FILTER_VALIDATE_FLOAT);

    if ($bid_quantity === false || $bid_price === false || $bid_quantity <= 0 || $bid_price <= 0) {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid input values. Please check and try again."
        ]);
        exit;
    }

    // Insert the bid into the database
    $stmt = $pdo->prepare("
        INSERT INTO bidding (product_id, user_id, bid_price, bid_quantity)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$product_id, $user_id, $bid_price, $bid_quantity]);

    echo json_encode([
        "status" => "success",
        "message" => "Your bid has been successfully submitted!"
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to submit bid: " . $e->getMessage()
    ]);
}
?>
