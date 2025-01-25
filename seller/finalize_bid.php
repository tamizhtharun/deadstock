<?php
// finalize_bid.php
require_once('../db_connection.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];

try {
    $pdo->beginTransaction();
    
    // Check if any bids from today are approved
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as approved_count
        FROM bidding 
        WHERE product_id = :product_id 
        AND bid_status = 4
        AND DATE(bid_time) = CURRENT_DATE()
    ");
    $stmt->execute([':product_id' => $product_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['approved_count'] == 0) {
        throw new Exception('Please approve at least one bid before finalizing');
    }

    // Update approved bids to final approved status (2)
    $stmt = $pdo->prepare("
        UPDATE bidding 
        SET bid_status = 2
        WHERE product_id = :product_id 
        AND bid_status = 4
        AND DATE(bid_time) = CURRENT_DATE()
    ");
    $stmt->execute([':product_id' => $product_id]);

    // Update remaining non-approved bids to refunded status (3)
    $stmt = $pdo->prepare("
        UPDATE bidding 
        SET bid_status = 3
        WHERE product_id = :product_id 
        AND bid_status = 1
        AND DATE(bid_time) = CURRENT_DATE()
    ");
    $stmt->execute([':product_id' => $product_id]);

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Bids finalized successfully'
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