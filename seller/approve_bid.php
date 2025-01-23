<?php
// approve_bid.php
require_once('../db_connection.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$bid_id = isset($data['bid_id']) ? intval($data['bid_id']) : 0;
$action = isset($data['action']) ? $data['action'] : '';

if (!$bid_id || !$action) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid input parameters'
    ]);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        SELECT bid_id, product_id, bid_time, bid_status 
        FROM bidding 
        WHERE bid_id = :bid_id 
        AND DATE(bid_time) = CURRENT_DATE()
    ");
    $stmt->execute([':bid_id' => $bid_id]);
    $bid = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$bid) {
        throw new Exception('Invalid bid or cannot modify bids from previous days');
    }

    $new_status = $bid['bid_status'];
    
    if ($action === 'approve') {
        // 1 -> 4 (Pending to Partially Approved)
        if ($bid['bid_status'] == 1) {
            $new_status = 4;
        }
    } elseif ($action === 'reject') {
        // If currently 4 (Partially Approved), revert to 1 (Pending)
        if ($bid['bid_status'] == 4) {
            $new_status = 1;
        }
    }

    $stmt = $pdo->prepare("
        UPDATE bidding 
        SET bid_status = :status 
        WHERE bid_id = :bid_id
    ");
    
    $result = $stmt->execute([
        ':status' => $new_status,
        ':bid_id' => $bid_id
    ]);

    if (!$result) {
        throw new Exception('Failed to update bid status');
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'new_status' => $new_status,
        'bid_id' => $bid_id,
        'message' => 'Bid status updated successfully'
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