<?php
session_start();
require_once('../db_connection.php');

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['seller_session']['seller_id'])) {
        throw new Exception('Unauthorized');
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid method');
    }
    $sellerId = (int)$_SESSION['seller_session']['seller_id'];
    $orderId = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
    if (!$orderId) throw new Exception('Missing order_id');

    // Ensure order belongs to seller and is eligible
    $stmt = $pdo->prepare("SELECT id, seller_id, order_status FROM tbl_orders WHERE id=? AND seller_id=?");
    $stmt->execute([$orderId, $sellerId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$order) throw new Exception('Order not found');

    // Mark packed
    $up = $pdo->prepare("UPDATE tbl_orders SET seller_packed=1, updated_at=CURRENT_TIMESTAMP WHERE id=? AND seller_id=?");
    $ok = $up->execute([$orderId, $sellerId]);
    if (!$ok) throw new Exception('Failed to update');

    echo json_encode(['success'=>true,'message'=>'Marked as packed']);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
?>




