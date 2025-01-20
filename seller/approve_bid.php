<?php
// approve_bid.php
require_once('../db_connection.php');

$data = json_decode(file_get_contents('php://input'), true);
$bid_id = $data['bid_id'];
$action = $data['action'];

try {
    $pdo->beginTransaction();

    // First reset all bids to pending
    if ($action === 'approve') {
        $stmt = $pdo->prepare("
            UPDATE bidding 
            SET bid_status = 1 
            WHERE product_id = (
                SELECT product_id 
                FROM bidding 
                WHERE bid_id = :bid_id
            )
        ");
        $stmt->execute([':bid_id' => $bid_id]);
    }

    // Then update the specific bid
    $new_status = ($action === 'approve') ? 2 : 1;
    $stmt = $pdo->prepare("
        UPDATE bidding 
        SET bid_status = :status 
        WHERE bid_id = :bid_id
    ");
    $stmt->execute([
        ':status' => $new_status,
        ':bid_id' => $bid_id
    ]);

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>