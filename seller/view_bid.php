<?php
// view_bid.php
require_once('header.php');

$seller_id = $_SESSION['seller_session']['seller_id'];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    die('Product ID is required');
}

// Get product details including send_time and close_time
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        COUNT(b.bid_id) as total_bids,
        COALESCE(p.close_time, NOW()) as close_time,
        (SELECT bid_id 
         FROM bidding 
         WHERE product_id = p.id AND bid_status = 2 
         LIMIT 1) as approved_bid
    FROM tbl_product p
    LEFT JOIN bidding b ON p.id = b.product_id
    WHERE p.id = :product_id AND p.seller_id = :seller_id
    GROUP BY p.id
");
$stmt->execute([
    ':product_id' => $product_id,
    ':seller_id' => $seller_id
]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Time validation
$current_time = new DateTime();
$send_time = new DateTime($product['send_time']);
$close_time = new DateTime($product['close_time']);

// Check various time conditions
$auction_active = $current_time >= $send_time && $current_time <= $close_time;
$auction_expired = $current_time > $close_time;
$auction_not_started = $current_time < $send_time;
$auction_ended = $auction_expired; // or any logic you need

// Process expired auction if needed
if ($auction_expired && $product['status'] != 'Sold' && $product['status'] != 'Expired') {
    try {
        $pdo->beginTransaction();

        if ($product['approved_bid']) {
            // Case 2: Has approved bid but not finalized
            $order_id = 'ORD' . date('Ymd') . rand(1000, 9999);
            
            // Get approved bid details
            $stmt = $pdo->prepare("
                SELECT * FROM bidding 
                WHERE bid_id = :bid_id
            ");
            $stmt->execute([':bid_id' => $product['approved_bid']]);
            $approved_bid = $stmt->fetch(PDO::FETCH_ASSOC);

            // Create order for approved bid
            $stmt = $pdo->prepare("
                INSERT INTO tbl_orders (
                    order_id, product_id, user_id, seller_id,
                    quantity, unit_price, total_amount,
                    order_date, payment_status, order_status, bid_id
                ) VALUES (
                    :order_id, :product_id, :user_id, :seller_id,
                    :quantity, :unit_price, :total_amount,
                    NOW(), 'Pending', 'Processing', :bid_id
                )
            ");
            
            $total_amount = $approved_bid['bid_price'] * $approved_bid['bid_quantity'];
            
            $stmt->execute([
                ':order_id' => $order_id,
                ':product_id' => $product_id,
                ':user_id' => $approved_bid['user_id'],
                ':seller_id' => $seller_id,
                ':quantity' => $approved_bid['bid_quantity'],
                ':unit_price' => $approved_bid['bid_price'],
                ':total_amount' => $total_amount,
                ':bid_id' => $approved_bid['bid_id']
            ]);

            // Reject all other bids
            $stmt = $pdo->prepare("
                UPDATE bidding 
                SET bid_status = 3,
                    refund_status = 'Pending'
                WHERE product_id = :product_id 
                AND bid_id != :approved_bid
            ");
            $stmt->execute([
                ':product_id' => $product_id,
                ':approved_bid' => $product['approved_bid']
            ]);
        } else {
            // Case 1: No approved bids, reject all
            $stmt = $pdo->prepare("
                UPDATE bidding 
                SET bid_status = 3,
                    refund_status = 'Pending'
                WHERE product_id = :product_id
            ");
            $stmt->execute([':product_id' => $product_id]);
        }

        // Update product status
        $stmt = $pdo->prepare("
            UPDATE tbl_product 
            SET status = :status
            WHERE id = :product_id
        ");
        $stmt->execute([
            ':status' => $product['approved_bid'] ? 'Sold' : 'Expired',
            ':product_id' => $product_id
        ]);

        $pdo->commit();
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log("Error processing expired auction: " . $e->getMessage());
    }
}

// Get all bids for the product within the time window
$statement = $pdo->prepare("
    SELECT 
        b.bid_id,
        b.bid_time,
        b.bid_price,
        b.bid_quantity,
        b.bid_status,
        b.user_id,
        p.p_name,
        p.p_featured_photo,
        p.seller_id,
        s.seller_name,
        s.seller_cname,
        u.username
    FROM 
        tbl_product p
    JOIN 
        sellers s ON p.seller_id = s.seller_id
    JOIN 
        bidding b ON p.id = b.product_id
    JOIN
        users u ON b.user_id = u.id
    WHERE
        p.id = :product_id 
        AND p.seller_id = :seller_id
        AND b.bid_time BETWEEN :send_time AND :close_time
    ORDER BY 
        b.bid_time DESC
");

$statement->execute([
    ':product_id' => $product_id,
    ':seller_id' => $seller_id,
    ':send_time' => $send_time->format('Y-m-d H:i:s'),
    ':close_time' => $close_time->format('Y-m-d H:i:s')
]);
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Bidding Details</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body table-responsive">
                    <?php if ($auction_expired): ?>
                        <div class="alert alert-warning">
                            This auction has ended. Final decisions must be made.
                        </div>
                    <?php endif; ?>
                    
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Buyer Name</th>
                                <th>Bid Price</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $i = 0;
                            foreach ($result as $row): 
                            ?>
                                <tr id="row-<?php echo $row['bid_id']; ?>">
                                    <td><?php echo ++$i; ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td>₹<?php echo number_format($row['bid_price'], 2); ?></td>
                                    <td><?php echo $row['bid_quantity']; ?></td>
                                    <td>
                                        <span class="status-label">
                                            <?php
                                            switch($row['bid_status']) {
                                                case 2:
                                                    echo '<span class="label label-success">Approved</span>';
                                                    break;
                                                case 3:
                                                    echo '<span class="label label-danger">Rejected/Refunded</span>';
                                                    break;
                                                default:
                                                    echo '<span class="label label-warning">Pending</span>';
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" 
                                            class="btn approve-individual-bid <?php echo $row['bid_status'] == 2 ? 'btn-success' : 'btn-primary'; ?>" 
                                            data-bid-id="<?php echo $row['bid_id']; ?>"
                                            data-product-id="<?php echo $product_id; ?>"
                                            data-status="<?php echo $row['bid_status']; ?>">
                                            <?php echo $row['bid_status'] == 2 ? 'Approved' : 'Approve'; ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if (!$auction_ended): ?>
                        <div class="text-center mt-4">
                            <button type="button" 
                                class="btn btn-success btn-lg common-approve-button" 
                                id="commonApproveButton" 
                                data-product-id="<?php echo $product_id; ?>">
                                Finalize Approved Bid
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add time check before any bid actions
    function isAuctionActive() {
        const currentTime = new Date();
        const closeTime = new Date('<?php echo $close_time->format('c'); ?>');
        const sendTime = new Date('<?php echo $send_time->format('c'); ?>');
        
        return currentTime >= sendTime && currentTime <= closeTime;
    }

    // Individual bid approval/reversal
    document.querySelectorAll('.approve-individual-bid').forEach(button => {
        button.addEventListener('click', function() {
            if (!isAuctionActive()) {
                alert('This auction has ended. No further actions can be taken.');
                return;
            }

            const bidId = this.getAttribute('data-bid-id');
            const productId = this.getAttribute('data-product-id');
            const currentStatus = parseInt(this.getAttribute('data-status'), 10);

            if (!bidId || !productId) {
                console.error('Missing data attributes');
                return;
            }

            const action = currentStatus === 2 ? 'unapprove' : 'approve'; // Decide action based on current status
            const confirmationMessage = action === 'approve'
                ? 'Are you sure you want to approve this bid? This will unapprove any previously approved bid.'
                : 'Are you sure you want to unapprove this bid?';

            if (confirm(confirmationMessage)) {
                fetch('approve_individual_bid.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        bid_id: bidId,
                        product_id: productId,
                        action: action, // Pass action as approve/unapprove
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // If unapproved, reset all buttons and status labels
                        if (action === 'unapprove') {
                            this.setAttribute('data-status', '1');
                            updateButtonState(this, '1');

                            const statusLabel = document.querySelector(`#row-${bidId} .status-label`);
                            if (statusLabel) {
                                statusLabel.innerHTML = '<span class="label label-warning">Pending</span>';
                            }
                        } else {
                            // Approve this bid
                            document.querySelectorAll('.approve-individual-bid').forEach(btn => {
                                btn.setAttribute('data-status', '1');
                                updateButtonState(btn, '1');
                            });

                            document.querySelectorAll('.status-label').forEach(label => {
                                label.innerHTML = '<span class="label label-warning">Pending</span>';
                            });

                            this.setAttribute('data-status', '2');
                            updateButtonState(this, '2');

                            const approvedRow = document.querySelector(`#row-${bidId}`);
                            if (approvedRow) {
                                const statusLabel = approvedRow.querySelector('.status-label');
                                if (statusLabel) {
                                    statusLabel.innerHTML = '<span class="label label-success">Approved</span>';
                                }
                            }
                        }

                        alert(`Bid has been successfully ${action === 'approve' ? 'approved' : 'unapproved'}.`);
                    } else {
                        alert(data.error || 'Failed to process the request.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while processing your request.');
                });
            }
        });
    });

    // Function to update button state
    function updateButtonState(button, status) {
        if (status === '2') {
            button.classList.remove('btn-primary');
            button.classList.add('btn-success');
            button.textContent = 'Approved';
        } else {
            button.classList.remove('btn-success');
            button.classList.add('btn-primary');
            button.textContent = 'Approve';
        }
    }
   // Common approve button - for finalizing the approved bid
const commonApproveButton = document.getElementById('commonApproveButton');
if (commonApproveButton) {
    const productId = commonApproveButton.getAttribute('data-product-id');
    
    commonApproveButton.addEventListener('click', function() {
        if (!isAuctionActive()) {
            alert('This auction has ended. No further actions can be taken.');
            return;
        }

        if (confirm('Are you sure you want to finalize the approved bid? This action cannot be undone.')) {
            fetch('finalize_bid.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    product_id: productId
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('The approved bid has been finalized and moved to orders.');
                    
                    // Update UI elements
                    document.querySelectorAll('.approve-individual-bid').forEach(btn => {
                        const status = btn.getAttribute('data-status');
                        if (status === '2') {
                            btn.textContent = 'Bid Won';
                        } else {
                            btn.textContent = 'Bid Lost';
                        }
                        btn.classList.remove('btn-primary', 'btn-success');
                        btn.classList.add('btn-secondary');
                        btn.setAttribute('data-status', '3');
                    });

                    // Update status labels
                    document.querySelectorAll('.status-label').forEach(label => {
                        if (!label.querySelector('.label-success')) {
                            label.innerHTML = '<span class="label label-danger">Rejected/Refunded</span>';
                        }
                    });

                    // Update finalize button
                    commonApproveButton.disabled = true;
                    commonApproveButton.textContent = 'Bid Finalized';
                    commonApproveButton.classList.remove('btn-success');
                    commonApproveButton.classList.add('btn-secondary');
                } else {
                    alert(data.error || 'Failed to finalize bid.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while processing your request.');
            });
        }
    });
}

});
</script>

<?php require_once('footer.php'); ?>