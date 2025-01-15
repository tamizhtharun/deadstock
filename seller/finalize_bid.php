<?php
// finalize_bid.php
require_once('../db_connection.php');

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$product_id = $data['product_id'];

try {
    $pdo->beginTransaction();
    
    // Get the approved bid with seller information
    $stmt = $pdo->prepare("
        SELECT b.*, p.seller_id 
        FROM bidding b
        JOIN tbl_product p ON b.product_id = p.id
        WHERE b.product_id = :product_id 
        AND b.bid_status = 2
    ");
    $stmt->execute([':product_id' => $product_id]);
    $approved_bid = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$approved_bid) {
        throw new Exception('No approved bid found');
    }

    // Generate order ID
    $order_id = 'ORD' . date('Ymd') . rand(1000, 9999);
    
    // Insert into tbl_orders
    $stmt = $pdo->prepare("
        INSERT INTO tbl_orders (
            order_id,
            product_id,
            quantity,
            price,
            seller_id,
            user_id,
            order_status,
            created_at
        ) VALUES (
            :order_id,
            :product_id,
            :quantity,
            :price,
            :seller_id,
            :user_id,
            'Pending',
            NOW()
        )
    ");
    
    $stmt->execute([
        ':order_id' => $order_id,
        ':product_id' => $product_id,
        ':quantity' => $approved_bid['bid_quantity'],
        ':price' => $approved_bid['bid_price'],
        ':seller_id' => $approved_bid['seller_id'],
        ':user_id' => $approved_bid['user_id']
    ]);

    // Update all other bids to refunded status (status 3)
    $stmt = $pdo->prepare("
        UPDATE bidding 
        SET bid_status = 3,
            refund_status = 'Pending'
        WHERE product_id = :product_id 
        AND bid_id != :approved_bid_id
    ");
    $stmt->execute([
        ':product_id' => $product_id,
        ':approved_bid_id' => $approved_bid['bid_id']
    ]);

    $pdo->commit();
    echo json_encode([
        'success' => true,
        'message' => 'Bid finalized successfully',
        'order_id' => $order_id
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