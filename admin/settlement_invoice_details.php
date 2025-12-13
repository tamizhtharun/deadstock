<?php
/**
 * Settlement Invoice Details Page
 * Shows all products belonging to a specific seller within a specific invoice
 * Includes detailed breakdown of costs and net settlement amount
 */

session_start();
require_once('../db_connection.php');

// Check admin authentication
if (!isset($_SESSION['admin_session'])) {
    header('location: ../index.php');
    exit;
}

// Get and validate parameters
$invoice_number = filter_input(INPUT_GET, 'invoice_number', FILTER_SANITIZE_STRING);
$seller_id = filter_input(INPUT_GET, 'seller_id', FILTER_VALIDATE_INT);

if (!$invoice_number || !$seller_id) {
    $_SESSION['error_message'] = 'Invalid invoice number or seller ID.';
    header('Location: settlement.php');
    exit;
}

// Configuration
define('GOLD_EARNINGS_THRESHOLD', 50000);
define('GOLD_SALES_THRESHOLD', 50);
define('SILVER_EARNINGS_THRESHOLD', 20000);
define('SILVER_SALES_THRESHOLD', 20);

function getPlatformFeePercent($rank, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT fee_percentage FROM platform_fee_config WHERE rank = ?");
        $stmt->execute([$rank]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? (float)$result['fee_percentage'] : 10.00;
    } catch (PDOException $e) {
        return 10.00;
    }
}

function getSellerRank($earnings, $sales) {
    if ($earnings >= GOLD_EARNINGS_THRESHOLD || $sales >= GOLD_SALES_THRESHOLD) {
        return 'GOLD';
    } elseif ($earnings >= SILVER_EARNINGS_THRESHOLD || $sales >= SILVER_SALES_THRESHOLD) {
        return 'SILVER';
    }
    return 'BRONZE';
}

try {
    // Fetch seller information
    $stmt = $pdo->prepare("SELECT seller_id, seller_name FROM sellers WHERE seller_id = ?");
    $stmt->execute([$seller_id]);
    $seller = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$seller) {
        $_SESSION['error_message'] = 'Seller not found.';
        header('Location: settlement.php');
        exit;
    }
    
    // Calculate seller rank
    $stmt = $pdo->prepare("
        SELECT 
            COUNT(DISTINCT CASE WHEN order_status = 'delivered' THEN id END) as total_sales,
            COALESCE(SUM(CASE WHEN order_status = 'delivered' THEN price * quantity ELSE 0 END), 0) as total_earnings
        FROM tbl_orders
        WHERE seller_id = ?
    ");
    $stmt->execute([$seller_id]);
    $seller_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $rank = getSellerRank($seller_stats['total_earnings'], $seller_stats['total_sales']);
    $platform_fee_percent = getPlatformFeePercent($rank, $pdo);
    
    // Fetch all products for this seller in this invoice
    $stmt = $pdo->prepare("
        SELECT 
            o.id,
            o.order_id,
            p.p_name as product_name,
            o.quantity,
            o.price as unit_price,
            (o.price * o.quantity) as item_total,
            o.order_status,
            o.created_at as order_date,
            COALESCE(o.settlement_status, 0) as settlement_status,
            o.settlement_date
        FROM tbl_orders o
        LEFT JOIN tbl_product p ON o.product_id = p.id
        WHERE 
            o.invoice_number = ?
            AND o.seller_id = ?
        ORDER BY o.created_at ASC
    ");
    
    $stmt->execute([$invoice_number, $seller_id]);
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($products) == 0) {
        $_SESSION['error_message'] = 'No products found for this invoice and seller combination.';
        header("Location: settlement_invoices.php?seller_id=$seller_id");
        exit;
    }
    
    // Calculate totals
    $gross_amount = 0;
    $all_delivered = true;
    $any_settled = false;
    
    foreach ($products as $product) {
        $gross_amount += $product['item_total'];
        if ($product['order_status'] != 'delivered') {
            $all_delivered = false;
        }
        if ($product['settlement_status'] == 1) {
            $any_settled = true;
        }
    }
    
    // Calculate platform fee
    $platform_fee = round($gross_amount * ($platform_fee_percent / 100), 2);
    
    // Calculate proportional delivery charge
    $stmt = $pdo->prepare("
        SELECT 
            SUM(delivery_charge) as total_delivery,
            SUM(price * quantity) as total_invoice_amount
        FROM tbl_orders
        WHERE invoice_number = ?
    ");
    $stmt->execute([$invoice_number]);
    $invoice_totals = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $total_delivery = $invoice_totals['total_delivery'] ?? 0;
    $total_invoice_amount = $invoice_totals['total_invoice_amount'] ?? 1;
    
    // Proportional delivery charge for this seller
    $delivery_charge_allocated = 0;
    if ($total_invoice_amount > 0) {
        $delivery_charge_allocated = round(($gross_amount / $total_invoice_amount) * $total_delivery, 2);
    }
    
    // Net settlement
    $net_settlement = $gross_amount - $platform_fee - $delivery_charge_allocated;
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    header("Location: settlement_invoices.php?seller_id=$seller_id");
    exit;
}

require_once('header.php');
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Invoice Product Details</h1>
    </div>
    <div class="content-header-right">
        <a href="settlement_invoices.php?seller_id=<?php echo $seller_id; ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to Invoices
        </a>
    </div>
</section>

<style>
.summary-box {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 5px;
    border: 1px solid #ddd;
    margin-bottom: 20px;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.summary-row:last-child {
    border-bottom: none;
    padding-top: 15px;
    margin-top: 10px;
    border-top: 2px solid #333;
    font-size: 18px;
    font-weight: bold;
}
</style>

<section class="content">
    <!-- Invoice Summary Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Invoice Information</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Invoice Number:</strong><br>
                            <?php echo htmlspecialchars($invoice_number); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Seller:</strong><br>
                            <?php echo htmlspecialchars($seller['seller_name']); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Seller Rank:</strong><br>
                            <span class="badge <?php echo 'rank-' . strtolower($rank); ?>"><?php echo $rank; ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Platform Fee:</strong><br>
                            <?php echo $platform_fee_percent; ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Products Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-shopping-cart"></i> Product Breakdown</h3>
                </div>
                <div class="box-body table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="30">#</th>
                                <th>Order ID</th>
                                <th>Product Name</th>
                                <th>Date</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Item Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 0;
                            foreach ($products as $product): 
                                $i++;
                            ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td>
                                    <strong>#<?php echo htmlspecialchars($product['order_id']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($product['order_date'])); ?></td>
                                <td class="text-center"><?php echo $product['quantity']; ?></td>
                                <td class="text-right">₹<?php echo number_format($product['unit_price'], 2); ?></td>
                                <td class="text-right"><strong>₹<?php echo number_format($product['item_total'], 2); ?></strong></td>
                                <td>
                                    <?php if ($product['settlement_status'] == 1): ?>
                                        <span class="label label-success">Settled</span>
                                        <br><small><?php echo date('d-m-Y', strtotime($product['settlement_date'])); ?></small>
                                    <?php else: ?>
                                        <span class="label label-<?php echo $product['order_status'] == 'delivered' ? 'warning' : 'info'; ?>">
                                            <?php echo ucfirst($product['order_status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Settlement Calculation -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-calculator"></i> Settlement Calculation</h3>
                </div>
                <div class="box-body">
                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Gross Amount (<?php echo count($products); ?> products):</span>
                            <span><strong>₹<?php echo number_format($gross_amount, 2); ?></strong></span>
                        </div>
                        <div class="summary-row">
                            <span>Less: Platform Fee (<?php echo $platform_fee_percent; ?>%):</span>
                            <span class="text-danger"><strong>- ₹<?php echo number_format($platform_fee, 2); ?></strong></span>
                        </div>
                        <div class="summary-row">
                            <span>Less: Delivery Charge (Proportional):</span>
                            <span class="text-danger"><strong>- ₹<?php echo number_format($delivery_charge_allocated, 2); ?></strong></span>
                        </div>
                        <div class="summary-row">
                            <span>Net Payable Amount:</span>
                            <span class="text-success"><strong>₹<?php echo number_format($net_settlement, 2); ?></strong></span>
                        </div>
                    </div>
                    
                    <?php if ($any_settled): ?>
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i> This invoice has been settled for this seller.
                    </div>
                    <?php elseif (!$all_delivered): ?>
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> Cannot settle: Some products are not yet delivered.
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> All products delivered. Ready for settlement.
                    </div>
                    <form method="POST" action="settlement_process.php">
                        <input type="hidden" name="invoice_number" value="<?php echo htmlspecialchars($invoice_number); ?>">
                        <input type="hidden" name="seller_id" value="<?php echo $seller_id; ?>">
                        <button type="submit" 
                                name="settle_invoice" 
                                class="btn btn-success btn-lg"
                                onclick="return confirm('Confirm settlement of ₹<?php echo number_format($net_settlement, 2); ?> for Invoice <?php echo htmlspecialchars($invoice_number); ?>?');">
                            <i class="fa fa-check"></i> Settle Invoice for This Seller
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
