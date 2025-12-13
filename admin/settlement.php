<?php
/**
 * Settlement Management Page
 * Manages seller settlements with ranking system, invoice tracking, and payment processing
 */

// Start session and handle settlement actions before any output
session_start();
require_once('../db_connection.php');

// Check admin authentication
if (!isset($_SESSION['admin_session'])) {
    header('location: ../index.php');
    exit;
}

// Configuration: Ranking Thresholds (easily configurable)
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

/**
 * Get rank badge HTML with appropriate styling
 * @param string $rank Seller rank
 * @param PDO $pdo Database connection
 * @return string HTML badge element
 */
function getRankBadge($rank, $pdo) {
    $fee_percent = getPlatformFeePercent($rank, $pdo);
    $badges = [
        'GOLD' => '<span class="badge rank-badge rank-gold" title="Top Performer - ' . $fee_percent . '% Platform Fee"><i class="fa fa-trophy"></i> GOLD</span>',
        'SILVER' => '<span class="badge rank-badge rank-silver" title="High Performer - ' . $fee_percent . '% Platform Fee"><i class="fa fa-medal"></i> SILVER</span>',
        'BRONZE' => '<span class="badge rank-badge rank-bronze" title="Active Seller - ' . $fee_percent . '% Platform Fee"><i class="fa fa-certificate"></i> BRONZE</span>'
    ];
    return $badges[$rank] ?? '';
}

/**
 * Get settlement amount indicator with color coding
 * @param float $amount Settlement amount
 * @return string HTML with color-coded amount
 */
function getSettlementIndicator($amount) {
    if ($amount == 0) {
        return '<span class="settlement-amount settlement-settled">₹0.00</span>';
    } elseif ($amount <= 10000) {
        return '<span class="settlement-amount settlement-low">₹' . number_format($amount, 2) . '</span>';
    } else {
        return '<span class="settlement-amount settlement-high">₹' . number_format($amount, 2) . '</span>';
    }
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
        <h1>Seller Settlements</h1>
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
/* Ranking Badges */
.rank-badge {
    padding: 6px 12px;
    font-size: 13px;
    font-weight: bold;
    border-radius: 20px;
    display: inline-block;
    text-shadow: 0 1px 2px rgba(0,0,0,0.2);
}

.rank-gold {
    background: linear-gradient(135deg, #FFD700, #FFA500);
    color: #fff;
    box-shadow: 0 2px 4px rgba(255, 215, 0, 0.4);
}

.rank-silver {
    background: linear-gradient(135deg, #C0C0C0, #A8A8A8);
    color: #fff;
    box-shadow: 0 2px 4px rgba(192, 192, 192, 0.4);
}

.rank-bronze {
    background: linear-gradient(135deg, #CD7F32, #B8860B);
    color: #fff;
    box-shadow: 0 2px 4px rgba(205, 127, 50, 0.4);
}

/* Settlement Amount Indicators */
.settlement-amount {
    font-weight: bold;
    padding: 4px 10px;
    border-radius: 4px;
    display: inline-block;
}

.settlement-settled {
    background-color: #d4edda;
    color: #155724;
}

.settlement-low {
    background-color: #fff3cd;
    color: #856404;
}

.settlement-high {
    background-color: #f8d7da;
    color: #721c24;
}

/* Action Buttons */
.action-btns {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.btn-view-invoices {
    background-color: #3c8dbc;
    color: white;
    border: none;
}

.btn-view-invoices:hover {
    background-color: #2e6da4;
    color: white;
}

.settle-btn {
    background-color: #00a65a;
    color: white;
    border: none;
}

.settle-btn:hover {
    background-color: #008d4c;
    color: white;
}

/* Invoice Modal Styling */
.invoice-modal .modal-dialog {
    max-width: 900px;
}

.invoice-table {
    margin-top: 15px;
}

.invoice-table th {
    background-color: #f4f4f4;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .action-btns {
        flex-direction: column;
    }
    
    .rank-badge {
        font-size: 11px;
        padding: 4px 8px;
    }
}
</style>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th width="30">#</th>
                                <th width="180">Seller Name</th>
                                <th width="100">Total Sales</th>
                                <th width="120">Total Earnings</th>
                                <th width="140">Pending Settlement</th>
                                <th width="100">Rank</th>
                                <th width="150">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            
                            // Fetch all sellers with their earnings and settlement data
                            // Join sellers with orders to calculate total earnings and pending settlements
                            $statement = $pdo->prepare("
                                SELECT 
                                    s.seller_id,
                                    s.seller_name,
                                    s.seller_email,
                                    COUNT(DISTINCT CASE WHEN o.order_status = 'delivered' THEN o.order_id END) as total_sales,
                                    COALESCE(SUM(CASE WHEN o.order_status = 'delivered' THEN o.price * o.quantity ELSE 0 END), 0) as total_earnings,
                                    COALESCE(SUM(CASE WHEN o.order_status = 'delivered' AND COALESCE(o.settlement_status, 0) = 0 
                                                      THEN o.price * o.quantity ELSE 0 END), 0) as remaining_settlement
                                FROM sellers s
                                LEFT JOIN tbl_orders o ON s.seller_id = o.seller_id
                                WHERE s.seller_status = 1
                                GROUP BY s.seller_id, s.seller_name, s.seller_email
                                ORDER BY total_earnings DESC
                            ");
                            
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            
                            foreach ($result as $row) {
                                $i++;
                                
                                // Calculate seller rank
                                $rank = getSellerRank($row['total_earnings'], $row['total_sales']);
                                $rankBadge = getRankBadge($rank, $pdo);
                                
                                // Get settlement indicator
                                $settlementIndicator = getSettlementIndicator($row['remaining_settlement']);
                                
                                // Determine if settlement button should be disabled
                                $canSettle = $row['remaining_settlement'] > 0;
                            ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['seller_name']); ?></strong>
                                    <br>
                                    <small class="text-muted"><?php echo htmlspecialchars($row['seller_email']); ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-primary"><?php echo number_format($row['total_sales']); ?></span>
                                </td>
                                <td>
                                    <strong>₹<?php echo number_format($row['total_earnings'], 2); ?></strong>
                                </td>
                                <td>
                                    <?php echo $settlementIndicator; ?>
                                </td>
                                <td>
                                    <?php echo $rankBadge; ?>
                                </td>
                                <td>
                                    <!-- Settlement Details Button (Invoice-based) -->
                                    <a href="settlement_invoices.php?seller_id=<?php echo $row['seller_id']; ?>" 
                                       class="btn btn-sm btn-primary"
                                       title="View invoice-based settlement details">
                                        <i class="fa fa-file-text"></i> View Invoices
                                    </a>
                                </td>
                            </tr>
                            <?php
                            }
                            
                            // Display message if no sellers found
                            if (count($result) == 0) {
                                echo '<tr><td colspan="7" class="text-center">No active sellers found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>


<?php require_once('footer.php'); ?>