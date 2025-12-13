<?php
/**
 * Settlement Invoices Page
 * Shows all invoices (pending and settled) for a specific seller
 * Each invoice shows: products, gross amount, platform fee, delivery charge, net settlement
 */

session_start();
require_once('../db_connection.php');

// Check admin authentication
if (!isset($_SESSION['admin_session'])) {
    header('location: ../index.php');
    exit;
}

// Get and validate seller ID
$seller_id = filter_input(INPUT_GET, 'seller_id', FILTER_VALIDATE_INT);

if (!$seller_id) {
    $_SESSION['error_message'] = 'Invalid seller ID provided.';
    header('Location: settlement.php');
    exit;
}

// Configuration: Ranking Thresholds
define('GOLD_EARNINGS_THRESHOLD', 50000);
define('GOLD_SALES_THRESHOLD', 50);
define('SILVER_EARNINGS_THRESHOLD', 20000);
define('SILVER_SALES_THRESHOLD', 20);

/**
 * Get platform fee percentage from database based on rank
 */
function getPlatformFeePercent($rank, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT fee_percentage FROM platform_fee_config WHERE rank = ?");
        $stmt->execute([$rank]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return (float)$result['fee_percentage'];
        }
        
        // Fallback
        return match($rank) {
            'GOLD' => 5.00,
            'SILVER' => 7.00,
            default => 10.00
        };
    } catch (PDOException $e) {
        return 10.00;
    }
}

/**
 * Calculate seller rank
 */
function getSellerRank($earnings, $sales) {
    if ($earnings >= GOLD_EARNINGS_THRESHOLD || $sales >= GOLD_SALES_THRESHOLD) {
        return 'GOLD';
    } elseif ($earnings >= SILVER_EARNINGS_THRESHOLD || $sales >= SILVER_SALES_THRESHOLD) {
        return 'SILVER';
    } else {
        return 'BRONZE';
    }
}

try {
    // Fetch seller information
    $stmt = $pdo->prepare("SELECT seller_id, seller_name, seller_email FROM sellers WHERE seller_id = ?");
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
    
    // Fetch all invoices for this seller with settlement calculations
    $stmt = $pdo->prepare("
        SELECT 
            o.invoice_number,
            COUNT(DISTINCT o.id) as product_count,
            SUM(o.price * o.quantity) as gross_amount,
            
            -- Platform fee calculation
            ROUND(SUM(o.price * o.quantity) * (? / 100), 2) as platform_fee,
            
            -- Proportional delivery charge calculation
            -- Seller's share = (Seller's gross / Total invoice gross) * Total delivery charge
            ROUND(
                COALESCE((
                    SELECT SUM(delivery_charge)
                    FROM tbl_orders 
                    WHERE invoice_number = o.invoice_number
                ), 0) * 
                (
                    SUM(o.price * o.quantity) / 
                    NULLIF((
                        SELECT SUM(price * quantity)
                        FROM tbl_orders
                        WHERE invoice_number = o.invoice_number
                    ), 0)
                )
            , 2) as delivery_charge_allocated,
            
            -- Net settlement = Gross - Platform Fee - Delivery
            ROUND(
                SUM(o.price * o.quantity) - 
                (SUM(o.price * o.quantity) * (? / 100)) -
                (
                    COALESCE((
                        SELECT SUM(delivery_charge)
                        FROM tbl_orders 
                        WHERE invoice_number = o.invoice_number
                    ), 0) * 
                    (
                        SUM(o.price * o.quantity) / 
                        NULLIF((
                            SELECT SUM(price * quantity)
                            FROM tbl_orders
                            WHERE invoice_number = o.invoice_number
                        ), 0)
                    )
                )
            , 2) as net_settlement,
            
            -- Settlement status (if ANY product is settled, consider it settled)
            MAX(COALESCE(o.settlement_status, 0)) as is_settled,
            MAX(o.settlement_date) as settled_on,
            MIN(o.created_at) as invoice_date,
            
            -- Check if all products are delivered
            COUNT(DISTINCT o.id) = SUM(CASE WHEN o.order_status = 'delivered' THEN 1 ELSE 0 END) as all_delivered
            
        FROM tbl_orders o
        
        WHERE 
            o.seller_id = ?
            AND o.invoice_number IS NOT NULL
            AND o.invoice_number != ''
            AND o.order_status IN ('delivered', 'pending', 'processing', 'shipped')
        
        GROUP BY o.invoice_number
        ORDER BY is_settled ASC, invoice_date DESC
    ");
    
    $stmt->execute([$platform_fee_percent, $platform_fee_percent, $seller_id]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    header('Location: settlement.php');
    exit;
}

require_once('header.php');

// Retrieve session messages
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

unset($_SESSION['error_message']);
unset($_SESSION['success_message']);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Invoice Settlement - <?php echo htmlspecialchars($seller['seller_name']); ?></h1>
    </div>
    <div class="content-header-right">
        <a href="settlement.php" class="btn btn-primary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to All Sellers
        </a>
    </div>
</section>

<!-- Display messages -->
<?php if ($error_message): ?>
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <h4><i class="icon fa fa-ban"></i> Error!</h4>
    <?php echo htmlspecialchars($error_message); ?>
</div>
<?php endif; ?>

<?php if ($success_message): ?>
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert">&times;</button>
    <h4><i class="icon fa fa-check"></i> Success!</h4>
    <?php echo htmlspecialchars($success_message); ?>
</div>
<?php endif; ?>

<style>
.settled-row {
    background-color: #d4edda !important;
}

.pending-row {
    background-color: #fff3cd !important;
}

.not-ready-row {
    background-color: #f8d7da !important;
}
</style>

<section class="content">
    <!-- Seller Info Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Seller Information</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong>Seller Name:</strong> <?php echo htmlspecialchars($seller['seller_name']); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Email:</strong> <?php echo htmlspecialchars($seller['seller_email']); ?>
                        </div>
                        <div class="col-md-3">
                            <strong>Rank:</strong> 
                            <span class="badge <?php echo 'rank-' . strtolower($rank); ?>"><?php echo $rank; ?></span>
                        </div>
                        <div class="col-md-3">
                            <strong>Platform Fee:</strong> <?php echo $platform_fee_percent; ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Invoices Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-file-text"></i> Invoice List</h3>
                </div>
                <div class="box-body table-responsive">
                    <?php if (count($invoices) > 0): ?>
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="30">#</th>
                                <th>Invoice Number</th>
                                <th>Date</th>
                                <th>Products</th>
                                <th>Gross Amount</th>
                                <th>Platform Fee</th>
                                <th>Delivery</th>
                                <th>Net Payable</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 0;
                            foreach ($invoices as $invoice): 
                                $i++;
                                $row_class = '';
                                if ($invoice['is_settled'] == 1) {
                                    $row_class = 'settled-row';
                                } elseif ($invoice['all_delivered'] == 1) {
                                    $row_class = 'pending-row';
                                } else {
                                    $row_class = 'not-ready-row';
                                }
                            ?>
                            <tr class="<?php echo $row_class; ?>">
                                <td><?php echo $i; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong>
                                </td>
                                <td><?php echo date('d-m-Y', strtotime($invoice['invoice_date'])); ?></td>
                                <td class="text-center">
                                    <span class="badge badge-info"><?php echo $invoice['product_count']; ?></span>
                                </td>
                                <td class="text-right">₹<?php echo number_format($invoice['gross_amount'], 2); ?></td>
                                <td class="text-right text-danger">
                                    -₹<?php echo number_format($invoice['platform_fee'], 2); ?>
                                    <br><small>(<?php echo $platform_fee_percent; ?>%)</small>
                                </td>
                                <td class="text-right text-danger">
                                    -₹<?php echo number_format($invoice['delivery_charge_allocated'], 2); ?>
                                </td>
                                <td class="text-right">
                                    <strong class="text-success">₹<?php echo number_format($invoice['net_settlement'], 2); ?></strong>
                                </td>
                                <td>
                                    <?php if ($invoice['is_settled'] == 1): ?>
                                        <span class="label label-success">
                                            <i class="fa fa-check-circle"></i> Settled
                                        </span>
                                        <br><small><?php echo date('d-m-Y', strtotime($invoice['settled_on'])); ?></small>
                                    <?php elseif ($invoice['all_delivered'] == 1): ?>
                                        <span class="label label-warning">
                                            <i class="fa fa-clock-o"></i> Pending
                                        </span>
                                    <?php else: ?>
                                        <span class="label label-danger">
                                            <i class="fa fa-exclamation-triangle"></i> Not Ready
                                        </span>
                                        <br><small>Some products not delivered</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="settlement_invoice_details.php?invoice_number=<?php echo urlencode($invoice['invoice_number']); ?>&seller_id=<?php echo $seller_id; ?>" 
                                       class="btn btn-sm btn-info"
                                       title="View product breakdown">
                                        <i class="fa fa-eye"></i> View Products
                                    </a>
                                    
                                    <?php if ($invoice['is_settled'] == 0 && $invoice['all_delivered'] == 1): ?>
                                    <form method="POST" action="settlement_process.php" style="display: inline;">
                                        <input type="hidden" name="invoice_number" value="<?php echo htmlspecialchars($invoice['invoice_number']); ?>">
                                        <input type="hidden" name="seller_id" value="<?php echo $seller_id; ?>">
                                        <button type="submit" 
                                                name="settle_invoice" 
                                                class="btn btn-sm btn-success"
                                                onclick="return confirm('Settle ₹<?php echo number_format($invoice['net_settlement'], 2); ?> for Invoice <?php echo htmlspecialchars($invoice['invoice_number']); ?>?');">
                                            <i class="fa fa-check"></i> Settle
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> No invoices found for this seller.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
