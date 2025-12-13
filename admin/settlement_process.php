<?php
/**
 * Settlement Process Handler
 * Handles the settlement action for a seller's portion of an invoice
 * Includes validation, transaction safety, and prevents double settlement
 */

session_start();
require_once('../db_connection.php');

// Check admin authentication
if (!isset($_SESSION['admin_session'])) {
    header('location: ../index.php');
    exit;
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['settle_invoice'])) {
    $_SESSION['error_message'] = 'Invalid request method.';
    header('Location: settlement.php');
    exit;
}

// Get and validate inputs
$invoice_number = filter_input(INPUT_POST, 'invoice_number', FILTER_SANITIZE_STRING);
$seller_id = filter_input(INPUT_POST, 'seller_id', FILTER_VALIDATE_INT);

if (!$invoice_number || !$seller_id) {
    $_SESSION['error_message'] = 'Invalid invoice number or seller ID.';
    header('Location: settlement.php');
    exit;
}

try {
    // Start transaction for data consistency
    $pdo->beginTransaction();
    
    // Validation 1: Check if seller exists
    $stmt = $pdo->prepare("SELECT seller_name FROM sellers WHERE seller_id = ?");
    $stmt->execute([$seller_id]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$seller) {
        throw new Exception('Seller not found.');
    }
    
    // Validation 2: Check if all products are delivered
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) as total_products,
            SUM(CASE WHEN order_status = 'delivered' THEN 1 ELSE 0 END) as delivered_products
        FROM tbl_orders
        WHERE invoice_number = ? AND seller_id = ?
    ");
    $stmt->execute([$invoice_number, $seller_id]);
    $status_check = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($status_check['total_products'] == 0) {
        throw new Exception('No products found for this invoice and seller.');
    }
    
    if ($status_check['total_products'] != $status_check['delivered_products']) {
        throw new Exception('Cannot settle: Not all products are delivered yet.');
    }
    
    // Validation 3: Check if already settled
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as settled_count
        FROM tbl_orders
        WHERE invoice_number = ? 
        AND seller_id = ? 
        AND settlement_status = 1
    ");
    $stmt->execute([$invoice_number, $seller_id]);
    $settled_check = $stmt->fetchColumn();
    
    if ($settled_check > 0) {
        throw new Exception('This invoice has already been settled for this seller.');
    }
    
    // All validations passed - proceed with settlement
    $stmt = $pdo->prepare("
        UPDATE tbl_orders
        SET 
            settlement_status = 1,
            settlement_date = NOW()
        WHERE 
            invoice_number = ?
            AND seller_id = ?
            AND order_status = 'delivered'
            AND COALESCE(settlement_status, 0) = 0
    ");
    
    $stmt->execute([$invoice_number, $seller_id]);
    $affected_rows = $stmt->rowCount();
    
    if ($affected_rows === 0) {
        throw new Exception('No records were updated. Settlement may have already been processed.');
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Success message
    $_SESSION['success_message'] = sprintf(
        'Settlement completed successfully for Invoice #%s. %d product(s) marked as settled for %s.',
        htmlspecialchars($invoice_number),
        $affected_rows,
        htmlspecialchars($seller['seller_name'])
    );
    
    // Redirect back to invoice list
    header("Location: settlement_invoices.php?seller_id=$seller_id");
    exit;
    
} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Error message
    $_SESSION['error_message'] = 'Settlement failed: ' . $e->getMessage();
    
    // Redirect back
    header("Location: settlement_invoices.php?seller_id=$seller_id");
    exit;
    
} catch (PDOException $e) {
    // Rollback on database error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Log error (in production, use proper logging)
    error_log("Settlement Process Error: " . $e->getMessage());
    
    $_SESSION['error_message'] = 'Database error occurred. Please contact technical support.';
    header("Location: settlement_invoices.php?seller_id=$seller_id");
    exit;
}
?>
