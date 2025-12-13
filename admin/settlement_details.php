<?php
/**
 * Settlement Details Page
 * Shows detailed settlement breakdown for a specific seller
 * Including: orders, platform fees, delivery charges, and final settlement amount
 */

session_start();
require_once('../db_connection.php');

// Check admin authentication
if (!isset($_SESSION['admin_session'])) {
    header('location: ../index.php');
    exit;
}

// Configuration: Ranking Thresholds
define('GOLD_EARNINGS_THRESHOLD', 50000);   // ₹50,000
define('GOLD_SALES_THRESHOLD', 50);         // 50 orders
define('SILVER_EARNINGS_THRESHOLD', 20000); // ₹20,000
define('SILVER_SALES_THRESHOLD', 20);       // 20 orders

/**
 * Get platform fee percentage from database based on rank
 * @param string $rank Seller rank
 * @param PDO $pdo Database connection
 * @return float Platform fee percentage
 */
function getPlatformFeePercent($rank, $pdo) {
    try {
        $stmt = $pdo->prepare("SELECT fee_percentage FROM platform_fee_config WHERE rank = ?");
        $stmt->execute([$rank]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result) {
            return (float)$result['fee_percentage'];
        }
        
        // Fallback to default if not found in database
        switch ($rank) {
            case 'GOLD': return 5.00;
            case 'SILVER': return 7.00;
            case 'BRONZE': return 10.00;
            default: return 10.00;
        }
    } catch (PDOException $e) {
        // Fallback to default on error
        switch ($rank) {
            case 'GOLD': return 5.00;
            case 'SILVER': return 7.00;
            case 'BRONZE': return 10.00;
            default: return 10.00;
        }
    }
}

/**
 * Calculate seller rank based on earnings and sales count
 * @param float $earnings Total earnings amount
 * @param int $sales Total sales count
 * @return string Rank: 'GOLD', 'SILVER', or 'BRONZE'
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

$seller_id = filter_input(INPUT_GET, 'seller_id', FILTER_VALIDATE_INT);

if (!$seller_id) {
    $_SESSION['error_message'] = 'Invalid seller ID provided.';
    header('Location: settlement.php');
    exit;
}

// Handle platform fee update for ranks
if (isset($_POST['update_platform_fees'])) {
    $gold_fee = filter_input(INPUT_POST, 'gold_fee', FILTER_VALIDATE_FLOAT);
    $silver_fee = filter_input(INPUT_POST, 'silver_fee', FILTER_VALIDATE_FLOAT);
    $bronze_fee = filter_input(INPUT_POST, 'bronze_fee', FILTER_VALIDATE_FLOAT);
    
    if ($gold_fee !== false && $silver_fee !== false && $bronze_fee !== false &&
        $gold_fee >= 0 && $gold_fee <= 100 &&
        $silver_fee >= 0 && $silver_fee <= 100 &&
        $bronze_fee >= 0 && $bronze_fee <= 100) {
        
        try {
            // Update platform fees for all ranks
            $stmt = $pdo->prepare("UPDATE platform_fee_config SET fee_percentage = ? WHERE rank = ?");
            
            $stmt->execute([$gold_fee, 'GOLD']);
            $stmt->execute([$silver_fee, 'SILVER']);
            $stmt->execute([$bronze_fee, 'BRONZE']);
            
            $_SESSION['success_message'] = 'Platform fees updated successfully for all ranks!';
            header("Location: settlement_details.php?seller_id=$seller_id");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error updating platform fees: ' . $e->getMessage();
        }
    } else {
        $_SESSION['error_message'] = 'Invalid platform fees. All values must be between 0 and 100.';
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
            COUNT(DISTINCT CASE WHEN order_status = 'delivered' THEN order_id END) as total_sales,
            COALESCE(SUM(CASE WHEN order_status = 'delivered' THEN price * quantity ELSE 0 END), 0) as total_earnings
        FROM tbl_orders
        WHERE seller_id = ?
    ");
    $stmt->execute([$seller_id]);
    $seller_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $rank = getSellerRank($seller_stats['total_earnings'], $seller_stats['total_sales']);
    
    // Get platform fee for this seller's rank from database
    $platform_fee_percent = getPlatformFeePercent($rank, $pdo);
    
    // Fetch all platform fees for display
    $stmt = $pdo->prepare("SELECT rank, fee_percentage FROM platform_fee_config ORDER BY FIELD(rank, 'GOLD', 'SILVER', 'BRONZE')");
    $stmt->execute();
    $all_fees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $fee_config = [];
    foreach ($all_fees as $fee) {
        $fee_config[$fee['rank']] = $fee['fee_percentage'];
    }

    
    // Fetch all delivered orders for this seller
    $stmt = $pdo->prepare("
        SELECT 
            o.order_id,
            o.payment_id,
            o.created_at,
            o.price,
            o.quantity,
            (o.price * o.quantity) as order_amount,
            COALESCE(o.delivery_charge, 0) as delivery_charge,
            COALESCE(o.settlement_status, 0) as settlement_status,
            o.settlement_date,
            p.p_name as product_name,
            c.username as customer_name
        FROM tbl_orders o
        LEFT JOIN tbl_product p ON o.product_id = p.id
        LEFT JOIN users c ON o.id = c.id
        WHERE o.seller_id = ?
        AND o.order_status = 'delivered'
        ORDER BY o.settlement_status ASC, o.created_at DESC
    ");
    
    $stmt->execute([$seller_id]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate settlement totals
    $total_orders = count($orders);
    $total_order_amount = 0;
    $total_delivery_charges = 0;
    $total_platform_fees = 0;
    $total_settled_amount = 0;
    $total_pending_amount = 0;
    $settled_count = 0;
    $pending_count = 0;
    
    foreach ($orders as &$order) {
        $order_amount = $order['order_amount'];
        $delivery_charge = $order['delivery_charge'];
        
        // Calculate platform fee for this order
        $platform_fee = ($order_amount * $platform_fee_percent) / 100;
        
        // Calculate net settlement for this order (order amount - platform fee - delivery charge)
        $net_settlement = $order_amount - $platform_fee - $delivery_charge;
        
        // Add calculated values to order array
        $order['platform_fee'] = $platform_fee;
        $order['net_settlement'] = $net_settlement;
        
        // Update totals
        $total_order_amount += $order_amount;
        $total_delivery_charges += $delivery_charge;
        $total_platform_fees += $platform_fee;
        
        if ($order['settlement_status'] == 1) {
            $total_settled_amount += $net_settlement;
            $settled_count++;
        } else {
            $total_pending_amount += $net_settlement;
            $pending_count++;
        }
    }
    
    $total_net_settlement = $total_order_amount - $total_platform_fees - $total_delivery_charges;
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    header('Location: settlement.php');
    exit;
}

require_once('header.php');

// Retrieve session messages
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

// Clear session messages
unset($_SESSION['error_message']);
unset($_SESSION['success_message']);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Settlement Details - <?php echo htmlspecialchars($seller['seller_name']); ?></h1>
    </div>
    <div class="content-header-right">
        <a href="settlement.php" class="btn btn-primary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to Settlements
        </a>
    </div>
</section>

<!-- Display messages -->
<?php if ($error_message): ?>
<div class="alert alert-danger alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h4><i class="icon fa fa-ban"></i> Error!</h4>
    <?php echo htmlspecialchars($error_message); ?>
</div>
<?php endif; ?>

<?php if ($success_message): ?>
<div class="alert alert-success alert-dismissible">
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <h4><i class="icon fa fa-check"></i> Success!</h4>
    <?php echo htmlspecialchars($success_message); ?>
</div>
<?php endif; ?>

<style>
.info-box {
    min-height: 90px;
    background: #fff;
    width: 100%;
    box-shadow: 0 1px 1px rgba(0,0,0,0.1);
    border-radius: 2px;
    margin-bottom: 15px;
}

.info-box-icon {
    border-radius: 2px 0 0 2px;
    display: block;
    float: left;
    height: 90px;
    width: 90px;
    text-align: center;
    font-size: 45px;
    line-height: 90px;
    background: rgba(0,0,0,0.2);
}

.info-box-content {
    padding: 5px 10px;
    margin-left: 90px;
}

.settlement-summary {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #ddd;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.summary-row:last-child {
    border-bottom: none;
    font-weight: bold;
    font-size: 18px;
    color: #00a65a;
}

.summary-label {
    font-weight: 600;
}

.summary-value {
    text-align: right;
}

.platform-fee-section {
    background: #fff3cd;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #ffc107;
}

.custom-fee-badge {
    background: #ff9800;
    color: white;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    margin-left: 10px;
}

.rank-based-badge {
    background: #28a745;
    color: white;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    margin-left: 10px;
}
</style>

<section class="content">
    <!-- Seller Info & Summary Cards -->
    <div class="row">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Orders</span>
                    <span class="info-box-number"><?php echo $total_orders; ?></span>
                    <small>Settled: <?php echo $settled_count; ?> | Pending: <?php echo $pending_count; ?></small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-green"><i class="fa fa-inr"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Total Order Amount</span>
                    <span class="info-box-number">₹<?php echo number_format($total_order_amount, 2); ?></span>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-percent"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Platform Fees</span>
                    <span class="info-box-number">₹<?php echo number_format($total_platform_fees, 2); ?></span>
                    <small><?php echo $platform_fee_percent; ?>% of order amount</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-truck"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Delivery Charges</span>
                    <span class="info-box-number">₹<?php echo number_format($total_delivery_charges, 2); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Platform Fee Configuration -->
    <div class="row">
        <div class="col-md-12">
            <div class="platform-fee-section">
                <h4>
                    <i class="fa fa-cog"></i> Platform Fee Configuration
                    <span class="rank-based-badge">This seller's rank: <?php echo $rank; ?> (<?php echo $platform_fee_percent; ?>%)</span>
                </h4>
                <p>Configure platform fees for all seller ranks. These are applied globally to all sellers based on their rank.</p>
                
                <form method="POST" action="" style="margin-top: 15px;">
                    <input type="hidden" name="seller_id" value="<?php echo $seller_id; ?>">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="gold_fee">
                                    <i class="fa fa-trophy text-warning"></i> GOLD Sellers (%)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       name="gold_fee" 
                                       id="gold_fee" 
                                       value="<?php echo $fee_config['GOLD'] ?? 5; ?>" 
                                       min="0" 
                                       max="100" 
                                       step="0.01" 
                                       required>
                                <small class="text-muted">Current: <?php echo $fee_config['GOLD'] ?? 5; ?>%</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="silver_fee">
                                    <i class="fa fa-medal text-secondary"></i> SILVER Sellers (%)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       name="silver_fee" 
                                       id="silver_fee" 
                                       value="<?php echo $fee_config['SILVER'] ?? 7; ?>" 
                                       min="0" 
                                       max="100" 
                                       step="0.01" 
                                       required>
                                <small class="text-muted">Current: <?php echo $fee_config['SILVER'] ?? 7; ?>%</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="bronze_fee">
                                    <i class="fa fa-certificate text-danger"></i> BRONZE Sellers (%)
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       name="bronze_fee" 
                                       id="bronze_fee" 
                                       value="<?php echo $fee_config['BRONZE'] ?? 10; ?>" 
                                       min="0" 
                                       max="100" 
                                       step="0.01" 
                                       required>
                                <small class="text-muted">Current: <?php echo $fee_config['BRONZE'] ?? 10; ?>%</small>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="update_platform_fees" class="btn btn-warning">
                        <i class="fa fa-save"></i> Update Platform Fees for All Ranks
                    </button>
                    <small class="text-muted" style="display: block; margin-top: 10px;">
                        <i class="fa fa-info-circle"></i> These changes will apply to ALL sellers of each rank globally.
                    </small>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Settlement Summary -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-calculator"></i> Settlement Summary</h3>
                </div>
                <div class="box-body">
                    <div class="settlement-summary">
                        <div class="summary-row">
                            <span class="summary-label">Total Order Amount:</span>
                            <span class="summary-value">₹<?php echo number_format($total_order_amount, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Less: Platform Fees (<?php echo $platform_fee_percent; ?>%):</span>
                            <span class="summary-value text-danger">- ₹<?php echo number_format($total_platform_fees, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Less: Delivery Charges:</span>
                            <span class="summary-value text-danger">- ₹<?php echo number_format($total_delivery_charges, 2); ?></span>
                        </div>
                        <div class="summary-row">
                            <span class="summary-label">Net Settlement Amount:</span>
                            <span class="summary-value">₹<?php echo number_format($total_net_settlement, 2); ?></span>
                        </div>
                    </div>
                    
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-6">
                            <div class="alert alert-success">
                                <h4><i class="fa fa-check-circle"></i> Settled Amount</h4>
                                <h3 style="margin: 10px 0;">₹<?php echo number_format($total_settled_amount, 2); ?></h3>
                                <small><?php echo $settled_count; ?> orders</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-warning">
                                <h4><i class="fa fa-clock-o"></i> Pending Settlement</h4>
                                <h3 style="margin: 10px 0;">₹<?php echo number_format($total_pending_amount, 2); ?></h3>
                                <small><?php echo $pending_count; ?> orders</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Orders Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-list"></i> Order Details</h3>
                </div>
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="30">#</th>
                                <th>Order ID</th>
                                <th>Product</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Qty</th>
                                <th>Order Amount</th>
                                <th>Delivery Charge</th>
                                <th>Platform Fee</th>
                                <th>Net Settlement</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 0;
                            foreach ($orders as $order): 
                                $i++;
                                $status_class = $order['settlement_status'] == 1 ? 'success' : 'warning';
                                $status_text = $order['settlement_status'] == 1 ? 'Settled' : 'Pending';
                                $status_icon = $order['settlement_status'] == 1 ? 'check-circle' : 'clock-o';
                            ?>
                            <tr class="<?php echo $order['settlement_status'] == 1 ? '' : 'warning'; ?>">
                                <td><?php echo $i; ?></td>
                                <td>
                                    <strong>#<?php echo htmlspecialchars($order['order_id']); ?></strong>
                                    <?php if ($order['payment_id']): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($order['payment_id']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($order['payment_date'])); ?></td>
                                <td class="text-center"><?php echo $order['quantity']; ?></td>
                                <td class="text-right"><strong>₹<?php echo number_format($order['order_amount'], 2); ?></strong></td>
                                <td class="text-right text-danger">₹<?php echo number_format($order['delivery_charge'], 2); ?></td>
                                <td class="text-right text-danger">₹<?php echo number_format($order['platform_fee'], 2); ?></td>
                                <td class="text-right"><strong class="text-success">₹<?php echo number_format($order['net_settlement'], 2); ?></strong></td>
                                <td>
                                    <span class="label label-<?php echo $status_class; ?>">
                                        <i class="fa fa-<?php echo $status_icon; ?>"></i> <?php echo $status_text; ?>
                                    </span>
                                    <?php if ($order['settlement_status'] == 1 && $order['settlement_date']): ?>
                                    <br><small class="text-muted"><?php echo date('d-m-Y', strtotime($order['settlement_date'])); ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="info">
                                <td colspan="6" class="text-right"><strong>Totals:</strong></td>
                                <td class="text-right"><strong>₹<?php echo number_format($total_order_amount, 2); ?></strong></td>
                                <td class="text-right"><strong>₹<?php echo number_format($total_delivery_charges, 2); ?></strong></td>
                                <td class="text-right"><strong>₹<?php echo number_format($total_platform_fees, 2); ?></strong></td>
                                <td class="text-right"><strong class="text-success">₹<?php echo number_format($total_net_settlement, 2); ?></strong></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>
