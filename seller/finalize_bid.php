<?php
// finalize_bid.php
session_start();
require_once('../db_connection.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];

try {
    $pdo->beginTransaction();

    // Check if at least one bid is approved
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as approved_count
        FROM bidding 
        WHERE product_id = :product_id 
        AND bid_status = 2
    ");
    $stmt->execute([':product_id' => $product_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['approved_count'] == 0) {
        throw new Exception('Approve at least one bid first');
    }

    // Update all pending bids (status 1) to rejected (status 3)
    $stmt = $pdo->prepare("
        UPDATE bidding 
        SET bid_status = 3
        WHERE product_id = :product_id 
        AND bid_status = 1
    ");
    $stmt->execute([':product_id' => $product_id]);

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Final approve action completed successfully.'
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>