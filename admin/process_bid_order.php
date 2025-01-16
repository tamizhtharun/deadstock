<?php
require_once('header.php');

if (!isset($_GET['action'])) {
    header('Location: bidding-order.php');
    exit;
}

function processOrder($bid_id, $product_id, $user_id, $seller_id, $quantity, $price) {
    global $pdo;
    
    try {
        $pdo->beginTransaction();

        // Get order_id and payment_id from bidding table
        $stmt = $pdo->prepare("SELECT order_id, payment_id FROM bidding WHERE bid_id = ?");
        $stmt->execute([$bid_id]);
        $bidData = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if the order already exists
        $stmt = $pdo->prepare("SELECT id FROM tbl_orders WHERE bid_id = ?");
        $stmt->execute([$bid_id]);
        
        if ($stmt->rowCount() == 0) {
            // If no order_id exists in bidding table, generate a new one
            $order_id = $bidData['order_id'] ?? 'ORD' . time() . rand(1000, 9999);
            
            // Insert the order into the orders table with order_id and payment_id
            $stmt = $pdo->prepare("INSERT INTO tbl_orders (
                product_id, user_id, seller_id, quantity, price, 
                order_id, order_status, bid_id, payment_id
            ) VALUES (?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
            
            $stmt->execute([
                $product_id, 
                $user_id, 
                $seller_id, 
                $quantity, 
                $price,
                $order_id,
                $bid_id,
                $bidData['payment_id']
            ]);
            
            // If order_id was generated new, update it in the bidding table
            if (!$bidData['order_id']) {
                $stmt = $pdo->prepare("UPDATE bidding SET order_id = ? WHERE bid_id = ?");
                $stmt->execute([$order_id, $bid_id]);
            }
        }

        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        error_log("Error processing order: " . $e->getMessage());
        return false;
    }
}

if ($_GET['action'] === 'send' && isset($_GET['bid_id'])) {
    $success = processOrder(
        $_GET['bid_id'],
        $_GET['product_id'],
        $_GET['user_id'],
        $_GET['seller_id'],
        $_GET['quantity'],
        $_GET['price']
    );
    
    if ($success) {
        $_SESSION['success_message'] = "Order sent to seller successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to send order. Please try again.";
    }
    
    header('Location: bidding-order.php');
    exit;
}

if ($_GET['action'] === 'sendall') {
    try {
        // Get all approved bids that haven't been processed yet
        $stmt = $pdo->prepare("SELECT 
            b.bid_id, b.product_id, b.user_id, p.seller_id, 
            b.bid_quantity, b.bid_price 
        FROM bidding b 
        JOIN tbl_product p ON b.product_id = p.id 
        LEFT JOIN tbl_orders o ON b.bid_id = o.bid_id
        WHERE b.bid_status = 2 AND o.id IS NULL");
        
        $stmt->execute();
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $successCount = 0;
        foreach ($orders as $order) {
            if (processOrder(
                $order['bid_id'],
                $order['product_id'],
                $order['user_id'],
                $order['seller_id'],
                $order['bid_quantity'],
                $order['bid_price']
            )) {
                $successCount++;
            }
        }
        
        if ($successCount > 0) {
            $_SESSION['success_message'] = "$successCount orders sent to sellers successfully.";
        } else {
            $_SESSION['error_message'] = "No new orders to send.";
        }
    } catch (Exception $e) {
        error_log("Error processing bulk orders: " . $e->getMessage());
        $_SESSION['error_message'] = "Failed to send orders. Please try again.";
    }
    
    header('Location: bidding-order.php');
    exit;
}

header('Location: bidding-order.php');
?>