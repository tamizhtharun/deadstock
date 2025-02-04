<?php
// finalize_bid.php
require_once('../db_connection.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];
$is_time_over = isset($data['is_time_over']) ? $data['is_time_over'] : false;

try {
    $pdo->beginTransaction();
    
    if (!$is_time_over) {
        // Existing logic for manual finalization
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
    }

    // Get product details
    $stmt = $pdo->prepare("SELECT p_name FROM tbl_product WHERE id = :product_id");
    $stmt->execute([':product_id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

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

    // Notify users with approved bids
    $stmt = $pdo->prepare("
        SELECT user_id, bid_price, bid_quantity
        FROM bidding 
        WHERE product_id = :product_id 
        AND bid_status = 2
        AND DATE(bid_time) = CURRENT_DATE()
    ");
    $stmt->execute([':product_id' => $product_id]);
    $approved_bids = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($approved_bids as $bid) {
        $title = "Bid Accepted";
        $message = "Congratulations! Your bid for {$product['p_name']} has been accepted. Bid details: ₹{$bid['bid_price']} for {$bid['bid_quantity']} unit(s). We'll be in touch soon with next steps. Thank you for your business!";
        $type = "success";
        addNotification($pdo, $bid['user_id'], $title, $message, $type);
    }

    // Notify users with rejected bids
    $stmt = $pdo->prepare("
        SELECT user_id, bid_price, bid_quantity
        FROM bidding 
        WHERE product_id = :product_id 
        AND bid_status = 3
        AND DATE(bid_time) = CURRENT_DATE()
    ");
    $stmt->execute([':product_id' => $product_id]);
    $rejected_bids = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rejected_bids as $bid) {
        $title = "Bid Rejected";
        $message = "Thank you for your interest in {$product['p_name']} was not accepted. Bid details: ₹{$bid['bid_price']} for {$bid['bid_quantity']} unit(s). Your account will be refunded within 3-5 business days. We appreciate your participation and hope you'll try again soon.";
        $type = "error";
        addNotification($pdo, $bid['user_id'], $title, $message, $type);
    }

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

function addNotification($pdo, $recipient_id, $title, $message, $type) {
    $stmt = $pdo->prepare("
        INSERT INTO notifications (recipient_id, recipient_type, title, message, type)
        VALUES (:recipient_id, 'user', :title, :message, :type)
    ");
    
    $stmt->execute([
        ':recipient_id' => $recipient_id,
        ':title' => $title,
        ':message' => $message,
        ':type' => $type
    ]);
}
?>

