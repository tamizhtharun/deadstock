<?php
require_once('../db_connection.php');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

$bid_id = $data['bid_id'] ?? null;
$product_id = $data['product_id'] ?? null;
$action = $data['action'] ?? null;

if (!$bid_id || !$product_id || !$action) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

try {
    if ($action === 'approve') {
        // Unapprove any previously approved bids for this product
        $stmt = $pdo->prepare("UPDATE bidding SET bid_status = 1 WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $product_id]);

        // Approve the selected bid
        $stmt = $pdo->prepare("UPDATE bidding SET bid_status = 2 WHERE bid_id = :bid_id");
        $stmt->execute([':bid_id' => $bid_id]);

        echo json_encode(['success' => true]);
    } elseif ($action === 'unapprove') {
        // Set the selected bid back to pending
        $stmt = $pdo->prepare("UPDATE bidding SET bid_status = 1 WHERE bid_id = :bid_id");
        $stmt->execute([':bid_id' => $bid_id]);

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Error in bid approval process: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred while processing your request.']);
}
?>