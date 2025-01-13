<?php
require_once('../db_connection.php');

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$product_id = isset($input['product_id']) ? (int)$input['product_id'] : 0;

try {
    $pdo->beginTransaction();

    // Get the approved bid
    $stmt = $pdo->prepare("
        SELECT b.*, p.seller_id 
        FROM bidding b
        JOIN tbl_product p ON b.product_id = p.id
        WHERE b.product_id = :product_id AND b.bid_status = 2
        LIMIT 1
    ");
    $stmt->execute([':product_id' => $product_id]);
    $approved_bid = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$approved_bid) {
        throw new Exception('No approved bid found');
    }

    // Create order
    $stmt = $pdo->prepare("
        INSERT INTO tbl_orders (
            product_id, quantity, price, seller_id, user_id, 
            order_status, created_at
        ) VALUES (
            :product_id, :quantity, :price, :seller_id, :user_id,
            'pending', NOW()
        )
    ");
    $stmt->execute([
        ':product_id' => $product_id,
        ':quantity' => $approved_bid['bid_quantity'],
        ':price' => $approved_bid['bid_price'],
        ':seller_id' => $approved_bid['seller_id'],
        ':user_id' => $approved_bid['user_id']
    ]);

    // Mark other bids as rejected (status = 3) and trigger refunds
    $stmt = $pdo->prepare("
        UPDATE bidding 
        SET bid_status = 3 
        WHERE product_id = :product_id AND bid_id != :approved_bid_id
    ");
    $stmt->execute([
        ':product_id' => $product_id,
        ':approved_bid_id' => $approved_bid['bid_id']
    ]);

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
