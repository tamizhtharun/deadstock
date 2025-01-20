<?php
// view_bid.php
require_once('header.php');

$seller_id = $_SESSION['seller_session']['seller_id'];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    die('Product ID is required');
}

// Get all bids for the product and check if any bid is finalized
$statement = $pdo->prepare("
    SELECT 
        b.bid_id,
        b.bid_price,
        b.bid_quantity,
        b.bid_status,
        b.user_id,
        u.username,
        EXISTS (
            SELECT 1 FROM tbl_orders o 
            WHERE o.product_id = b.product_id
        ) as is_finalized
    FROM 
        bidding b
    JOIN
        users u ON b.user_id = u.id
    WHERE
        b.product_id = :product_id 
    ORDER BY 
        b.bid_time DESC
");
$statement->execute([':product_id' => $product_id]);
$bids = $statement->fetchAll(PDO::FETCH_ASSOC);
$is_finalized = !empty($bids) ? $bids[0]['is_finalized'] : false;
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
                            foreach ($bids as $bid): 
                                $buttonClass = $bid['bid_status'] == 2 ? 'btn-success' : 'btn-primary';
                                $buttonText = $bid['bid_status'] == 2 ? 'Approved' : 'Approve';
                                
                                if ($is_finalized) {
                                    $buttonClass = 'btn-secondary';
                                    $buttonText = $bid['bid_status'] == 2 ? 'Bid Won' : 'Bid Lost';
                                }
                            ?>
                                <tr>
                                    <td><?php echo ++$i; ?></td>
                                    <td><?php echo htmlspecialchars($bid['username']); ?></td>
                                    <td>₹<?php echo number_format($bid['bid_price'], 2); ?></td>
                                    <td><?php echo $bid['bid_quantity']; ?></td>
                                    <td>
                                        <?php
                                        switch($bid['bid_status']) {
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
                                    </td>
                                    <td>
                                        <button 
                                            onclick="handleIndividualApprove(<?php echo $bid['bid_id']; ?>)" 
                                            class="btn <?php echo $buttonClass; ?>"
                                            id="btn-<?php echo $bid['bid_id']; ?>"
                                            <?php echo ($bid['bid_status'] == 3 || $is_finalized) ? 'disabled' : ''; ?>
                                            <?php echo $is_finalized ? 'style="cursor: not-allowed;"' : ''; ?>
                                        >
                                            <?php echo $buttonText; ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <div class="text-center mt-4">
                        <button 
                            onclick="handleFinalApprove(<?php echo $product_id; ?>)"
                            class="btn btn-success btn-lg" 
                            id="finalApproveBtn"
                            <?php echo $is_finalized ? 'disabled style="cursor: not-allowed;"' : ''; ?>>
                            <?php echo $is_finalized ? 'Finalized' : 'Final Approve'; ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function handleIndividualApprove(bidId) {
    const button = document.getElementById(`btn-${bidId}`);
    const currentStatus = button.classList.contains('btn-success') ? 2 : 1;
    const action = currentStatus === 2 ? 'unapprove' : 'approve';

    fetch('approve_bid.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            bid_id: bidId, 
            action: action 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Failed to update bid status'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your request.');
    });
}

function handleFinalApprove(productId) {
    if (!confirm('Are you sure you want to finalize the approved bid?')) {
        return;
    }

    fetch('finalize_bid.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Final approve action completed successfully!');
            // Disable all buttons after finalization
            document.querySelectorAll('.btn').forEach(button => {
                button.disabled = true;
                button.style.cursor = 'not-allowed';
            });
            document.getElementById('finalApproveBtn').disabled = true;
        } else {
            alert('Error: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while finalizing the bid.');
    });
}
</script>

<?php require_once('footer.php'); ?>