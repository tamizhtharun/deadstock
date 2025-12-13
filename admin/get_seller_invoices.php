<?php
/**
 * AJAX Endpoint: Get Seller Invoices
 * Fetches all invoices/orders for a specific seller
 * Used by settlement.php modal
 */

session_start();
require_once('../db_connection.php');

// Security: Check admin authentication
if (!isset($_SESSION['admin_session'])) {
    http_response_code(403);
    exit('<div class="alert alert-danger">Unauthorized access. Please login as admin.</div>');
}

// Validate and sanitize input
$seller_id = filter_input(INPUT_POST, 'seller_id', FILTER_VALIDATE_INT);

if (!$seller_id) {
    http_response_code(400);
    exit('<div class="alert alert-danger">Invalid seller ID provided.</div>');
}

try {
    // Fetch all delivered orders for this seller with settlement status
    $stmt = $pdo->prepare("
        SELECT 
            o.order_id,
            o.payment_id,
            o.payment_date,
            o.price,
            o.quantity,
            (o.price * o.quantity) as amount,
            o.order_status,
            COALESCE(o.settlement_status, 0) as settlement_status,
            o.settlement_date,
            p.p_name as product_name,
            c.cust_name as customer_name
        FROM tbl_orders o
        LEFT JOIN tbl_product p ON o.product_id = p.id
        LEFT JOIN tbl_customer c ON o.customer_id = c.cust_id
        WHERE o.seller_id = ?
        AND o.order_status = 'delivered'
        ORDER BY o.payment_date DESC
    ");
    
    $stmt->execute([$seller_id]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate summary statistics
    $total_invoices = count($invoices);
    $total_amount = 0;
    $settled_amount = 0;
    $pending_amount = 0;
    
    foreach ($invoices as $invoice) {
        $total_amount += $invoice['amount'];
        if ($invoice['settlement_status'] == 1) {
            $settled_amount += $invoice['amount'];
        } else {
            $pending_amount += $invoice['amount'];
        }
    }
    
    ?>
    
    <!-- Summary Cards -->
    <div class="row" style="margin-bottom: 20px;">
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-file-text"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Invoices</span>
                    <span class="info-box-number"><?php echo $total_invoices; ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Settled</span>
                    <span class="info-box-number">₹<?php echo number_format($settled_amount, 2); ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Pending</span>
                    <span class="info-box-number">₹<?php echo number_format($pending_amount, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($total_invoices > 0): ?>
    <!-- Invoice Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped invoice-table">
            <thead>
                <tr>
                    <th width="50">#</th>
                    <th>Order ID</th>
                    <th>Product</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Qty</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $i = 0;
                foreach ($invoices as $invoice): 
                    $i++;
                    $status_class = $invoice['settlement_status'] == 1 ? 'success' : 'warning';
                    $status_text = $invoice['settlement_status'] == 1 ? 'Settled' : 'Pending';
                    $status_icon = $invoice['settlement_status'] == 1 ? 'check-circle' : 'clock-o';
                ?>
                <tr>
                    <td><?php echo $i; ?></td>
                    <td>
                        <strong>#<?php echo htmlspecialchars($invoice['order_id']); ?></strong>
                        <?php if ($invoice['payment_id']): ?>
                        <br><small class="text-muted">Payment: <?php echo htmlspecialchars($invoice['payment_id']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($invoice['product_name']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['customer_name']); ?></td>
                    <td><?php echo date('d-m-Y', strtotime($invoice['payment_date'])); ?></td>
                    <td class="text-center"><?php echo $invoice['quantity']; ?></td>
                    <td><strong>₹<?php echo number_format($invoice['amount'], 2); ?></strong></td>
                    <td>
                        <span class="label label-<?php echo $status_class; ?>">
                            <i class="fa fa-<?php echo $status_icon; ?>"></i> <?php echo $status_text; ?>
                        </span>
                        <?php if ($invoice['settlement_status'] == 1 && $invoice['settlement_date']): ?>
                        <br><small class="text-muted">on <?php echo date('d-m-Y', strtotime($invoice['settlement_date'])); ?></small>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="info">
                    <td colspan="6" class="text-right"><strong>Total:</strong></td>
                    <td colspan="2"><strong>₹<?php echo number_format($total_amount, 2); ?></strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <?php else: ?>
    <!-- No Invoices Message -->
    <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> No delivered orders found for this seller.
    </div>
    <?php endif; ?>
    
    <?php
    
} catch (PDOException $e) {
    // Log error (in production, use proper logging)
    error_log("Error fetching seller invoices: " . $e->getMessage());
    
    // Return user-friendly error message
    http_response_code(500);
    exit('<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> Database error occurred. Please contact administrator.</div>');
}
?>
