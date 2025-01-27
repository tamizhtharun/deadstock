<?php
// view_bid.php
require_once('header.php');

$seller_id = $_SESSION['seller_session']['seller_id'];
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    die('Product ID is required');
}

$settingsQuery = "SELECT send_time, close_time FROM bid_settings WHERE id = 1";
$settingsResult = $pdo->query($settingsQuery)->fetch(PDO::FETCH_ASSOC);
$sendTime = $settingsResult['send_time'];
$closeTime = $settingsResult['close_time'];

date_default_timezone_set('Asia/Kolkata');
$current_time = time();

$statement = $pdo->prepare("
    SELECT 
        b.bid_id,
        b.bid_price,
        b.bid_quantity,
        b.bid_status,
        b.user_id,
        b.bid_time,
        u.username,
        EXISTS (
            SELECT 1 
            FROM bidding b2 
            WHERE b2.product_id = :product_id
            AND DATE(b2.bid_time) = CURRENT_DATE()
            AND b2.bid_status = 2
        ) as is_finalized
    FROM 
        bidding b
    JOIN
        users u ON b.user_id = u.id
    WHERE
        b.product_id = :product_id 
        AND DATE(b.bid_time) = CURRENT_DATE()
    ORDER BY 
        b.bid_time DESC
");

$statement->execute([':product_id' => $product_id]);

$bids = $statement->fetchAll(PDO::FETCH_ASSOC);
$is_finalized = !empty($bids) ? $bids[0]['is_finalized'] : false;

?>
<link rel="stylesheet" href="css/timer.css">

<style>
/* General button styles */
button {
    font-family: 'Arial', sans-serif;
    font-size: 14px;
    padding: 10px 20px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

/* Approve button (now green) */
.btn-primary {
    background-color: #007bff; /* Bootstrap blue */
    color: #fff;
}

.btn-primary:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

/* Keep the success button green */
.btn-success {
    background-color: #28a745;
    color: #fff;
}

.btn-success:hover {
    background-color: #218838;
}

/* Danger button */
.btn-danger {
    background-color: #dc3545;
    color: #fff;
}

.btn-danger:hover {
    background-color: #c82333;
}

/* Secondary button */
.btn-secondary {
    background-color: #6c757d;
    color: #fff;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

/* Disabled button */
button:disabled {
    background-color: #e0e0e0;
    color: #6c757d;
    cursor: not-allowed;
}

/* Add space between Approve and Reject buttons */
.btn-group button:not(:last-child) {
    margin-right: 15px; /* Ensure spacing between buttons in the group */
}

/* Table styling */
.table {
    border-collapse: collapse;
    width: 100%;
    margin-bottom: 20px;
}

.table th, .table td {
    text-align: center;
    vertical-align: middle;
    padding: 12px;
    border: 1px solid #ddd;
}

.table th {
    background-color: #f8f9fa;
    color: #343a40;
    font-weight: bold;
}

.table tr:nth-child(even) {
    background-color: #f9f9f9;
}

.table tr:hover {
    background-color: #f1f1f1;
}

/* Final approve button styling */
#finalApproveBtn {
    font-size: 16px;
    padding: 10px 15px;
    margin-top: 20px;
    border-radius: 10px; /* Reduced border-radius */
    font-weight: bold;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

/* Add hover effects for final approve button */
#finalApproveBtn:not(:disabled):hover {
    transform: scale(1.05);
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
}

/* Ensure spacing between buttons and surrounding elements */
.mt-4 {
    margin-top: 1.5rem;
}

/* Style for indication text */
#finalizeInfo {
    margin-top: 10px;
    font-size: 14px;
    color: #6c757d;
    font-style: italic;
}
.final-approve-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.button-container {
    margin-bottom: 5px;
}

.final-approve-text span {
    font-weight: bold;
    color: #666;
    font-size: 14px;
}

#finalApproveBtn {
    min-width: 150px;
}
</style>
<section class="content-header">
    <div class="content-header-left">
        <h1>Bidding</h1>
    </div>
    <div class="content-header-right">
    <div class="timer-display">
            <div class="time-segment">
                <div class="time-value" id="info">Ends</div>
                <div class="time-label">in</div>
            </div>
            <div class="time-segment">
                <div class="time-value" id="hours">00</div>
                <div class="time-label">Hours</div>
            </div>
            <div class="time-segment">
                <div class="time-value" id="minutes">00</div>
                <div class="time-label">Minutes</div>
            </div>
            <div class="time-segment">
                <div class="time-value" id="seconds">00</div>
                <div class="time-label">Seconds</div>
            </div>
        </div>
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
                            <th style="width: 5%;">#</th>
                            <th style="width: 20%;">Bid Price</th>
                            <th style="width: 15%;">Quantity</th>
                            <th style="width: 20%;">Status</th>
                            <th style="width: 20%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 
                        $i = 0;
                        foreach ($bids as $bid): 
                            $buttonClass = 'btn-primary'; // Default to blue for pending
                            $buttonText = 'Approve'; // Default button text

                            if ($bid['bid_status'] == 4) {
                                // If the bid is partially approved, use green for approved
                                $buttonClass = 'btn-success';
                                $buttonText = 'Approved'; // Change the text to "Approved"
                            } else if ($is_finalized) {
                                $buttonClass = 'btn-secondary'; // Disabled or finalized
                                $buttonText = $bid['bid_status'] == 2 ? 'Final Approved' : 'Not Approved';
                            }
                        ?>
                        <tr>
                            <td><?php echo ++$i; ?></td>
                            <td>₹<?php echo number_format($bid['bid_price'], 2); ?></td>
                            <td><?php echo $bid['bid_quantity']; ?></td>
                            <td>
                                <?php
                                switch($bid['bid_status']) {
                                    case 2:
                                        echo '<span class="label label-success">Final Approved</span>';
                                        break;
                                    case 3:
                                        echo '<span class="label label-danger">Refunded</span>';
                                        break;
                                    case 4:
                                        echo '<span class="label label-info">Partial Approve</span>';
                                        break;
                                    default:
                                        echo '<span class="label label-warning">Pending</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button 
                                        onclick="handleIndividualApprove(<?php echo $bid['bid_id']; ?>, 'approve')" 
                                        class="btn <?php echo $buttonClass; ?> me-2"
                                        id="approve-btn-<?php echo $bid['bid_id']; ?>"
                                        <?php echo ($bid['bid_status'] == 3 || $is_finalized) ? 'disabled' : ''; ?>>
                                        <?php echo $buttonText; ?>
                                    </button>
                                    <button 
                                        onclick="handleIndividualApprove(<?php echo $bid['bid_id']; ?>, 'reject')" 
                                        class="btn btn-danger"
                                        id="reject-btn-<?php echo $bid['bid_id']; ?>"
                                        <?php echo ($bid['bid_status'] == 3 || $is_finalized) ? 'disabled' : ''; ?>>
                                        Reject
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>

                    </table>

                    <div class="text-center mt-4">
                        <div class="final-approve-container">
                            <div class="button-container">
                                <button 
                                    onclick="handleFinalApprove(<?php echo $product_id; ?>)"
                                    class="btn btn-success btn-lg"
                                    id="finalApproveBtn"
                                    <?php echo $is_finalized ? 'disabled style="cursor: not-allowed;"' : ''; ?>>
                                    Final Approve
                                </button>
                            </div>
                            <div class="final-approve-text">
                                <span>(Final Approval - Cannot Modify Approved Bids)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function handleIndividualApprove(bidId, action) {
    const approveButton = document.getElementById(`approve-btn-${bidId}`);
    const rejectButton = document.getElementById(`reject-btn-${bidId}`);
    
    approveButton.disabled = true;
    rejectButton.disabled = true;

    fetch('approve_bid.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ 
            bid_id: bidId, 
            action: action 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.error || 'Failed to update bid status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your request.');
    })
    .finally(() => {
        approveButton.disabled = false;
        rejectButton.disabled = false;
    });
}

function handleFinalApprove(productId) {
    fetch('finalize_bid.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ product_id: productId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert(data.error || 'Failed to finalize bids');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while finalizing bids.');
    });
}

let sendTime = <?php echo strtotime($sendTime); ?>;
let closeTime = <?php echo strtotime($closeTime); ?>;
let currentTime = <?php echo $current_time; ?>;

function updateTimer() {
    currentTime = Math.floor(Date.now() / 1000);
    if (currentTime >= sendTime && currentTime <= closeTime) {
        let remainingTime = closeTime - currentTime;
        if (remainingTime <= 0) {
            document.getElementById('hours').textContent = String('00');
            document.getElementById('minutes').textContent = String('00');
            document.getElementById('seconds').textContent = String('00');
            return;
        }

        const hours = Math.floor((remainingTime % (24 * 60 * 60)) / (60 * 60));
        const minutes = Math.floor((remainingTime % (60 * 60)) / 60);
        const seconds = Math.floor(remainingTime % 60);

        document.getElementById('hours').textContent = String(hours).padStart(2, '0');
        document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
        document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
    } else {
        document.getElementById('hours').textContent = String('00');
        document.getElementById('minutes').textContent = String('00');
        document.getElementById('seconds').textContent = String('00');
    }
}

// Update timer every second
updateTimer();
setInterval(updateTimer, 1000);
</script>

<?php require_once('footer.php'); ?>