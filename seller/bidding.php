<?php
//bidding.php
require_once('header.php');
?>

<link rel="stylesheet" href="css/timer.css">

<?php
date_default_timezone_set('Asia/Kolkata');
$seller_id = $_SESSION['seller_session']['seller_id']; // Retrieve seller's ID from session
$today_date = date('Y-m-d'); // Get today's date
// Query to get the global send_time and close_time set by admin
$settingsQuery = "SELECT send_time, close_time FROM bid_settings WHERE id = 1";
$settingsResult = $pdo->query($settingsQuery)->fetch(PDO::FETCH_ASSOC);
$sendTime = $settingsResult['send_time'];
$closeTime = $settingsResult['close_time'];
$today_send_time = date('Y-m-d H:i:s', strtotime($today_date . ' ' . $sendTime));

// echo $today_send_time;
// Set timezone

$current_time = time();
$target_send_time = strtotime($sendTime);
$target_close_time = strtotime($closeTime);

// Check if current time is between send time and close time
$bidding_active = ($current_time >= $target_send_time && $current_time <= $target_close_time);

// Only fetch bids if bidding is active
if ($bidding_active) {
    // Query to get products with bids submitted between yesterday's send time and today's send time
    $query = "
       SELECT 
           p.id AS product_id,
           p.p_name,
           p.p_featured_photo,
           (
               SELECT COUNT(*) 
               FROM bidding b
               WHERE b.product_id = p.id 
               AND b.bid_time BETWEEN 
                   DATE_SUB(:today_send_time, INTERVAL 1 DAY) 
                   AND :today_send_time
           ) AS no_of_bids
       FROM 
           tbl_product p
       WHERE 
           p.seller_id = :seller_id
       HAVING 
           no_of_bids > 0
    ";

    // Execute the query
    $statement = $pdo->prepare($query);
    $statement->execute([
       'seller_id' => $seller_id,
       'send_time' => $sendTime,
       'today_send_time' => $today_send_time
    ]);
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
} else {
    // If not in bidding time, set result to empty
    $result = [];
}

// Calculate remaining time
$remaining_seconds = $target_close_time - $current_time;
?>

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
                    <?php if (!$bidding_active): ?>
                        <div class="alert no-details">
                        <i class="fa fa-gavel" aria-hidden="true"></i>
                           <h2> Bidding is currently closed </h2>
                           <P> Please wait for the next bidding period.<p>
                            <h4>
                           <?php echo 'Next bidding period: ' . date('h:i A', strtotime($sendTime)) . ' to ' . date('h:i A', strtotime($closeTime)) ?>
                            </h4>
                        </div>
                    <?php elseif (!empty($result)): ?>
                        <div class="alert alert-info">
                            <strong>Bidding Time:</strong> 
                            Open: <?php echo $sendTime ? date('d-m-Y H:i:s', strtotime($sendTime)) : 'N/A'; ?>, 
                            Close: <?php echo $closeTime ? date('d-m-Y H:i:s', strtotime($closeTime)) : 'N/A'; ?>
                            <br />
                            Make a decision quickly
                        </div>

                        <table id="example1" class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Photo</th>
                                    <th>Product Name</th>
                                    <th>No. of Bids</th>
                                    <th>View</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 0;
                                foreach ($result as $row):
                                ?>
                                    <tr>
                                        <td><?php echo ++$i; ?></td>
                                        <td>
                                            <img src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>" 
                                                 alt="Product Photo" 
                                                 style="width:70px;">
                                        </td>
                                        <td><?php echo $row['p_name']; ?></td>
                                        <td><?php echo $row['no_of_bids']; ?></td>
                                        <td><a href="view_bid.php?id=<?php echo $row['product_id']; ?>">View all Bids</a></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="alert no-details">
                        <i class="fa fa-gavel" aria-hidden="true"></i>
                           <h2> Sorry there is no bid for your products </h2>
                           <P> Please wait for the next bidding period.<p>
                            <h4>
                           <?php echo 'Next bidding period: ' . date('h:i A', strtotime($sendTime)) . ' to ' . date('h:i A', strtotime($closeTime)) ?>
                            </h4>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Initialize the countdown with PHP variables
    let sendTime = <?php echo $target_send_time; ?>;
    let closeTime = <?php echo $target_close_time; ?>;
    let currentTime = <?php echo $current_time; ?>;
    let biddingActive = <?php echo $bidding_active ? 'true' : 'false'; ?>;

    function updateTimer() {
        currentTime = Math.floor(Date.now() / 1000);
        
        if (biddingActive && currentTime <= closeTime) {
            let remainingTime = closeTime - currentTime;
            if (remainingTime <= 0) {
                document.getElementById('hours').textContent = '00';
                document.getElementById('minutes').textContent = '00';
                document.getElementById('seconds').textContent = '00';
                return;
            }

            const hours = Math.floor((remainingTime % (24 * 60 * 60)) / (60 * 60));
            const minutes = Math.floor((remainingTime % (60 * 60)) / 60);
            const seconds = Math.floor(remainingTime % 60);

            document.getElementById('hours').textContent = String(hours).padStart(2, '0');
            document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
            document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');
        } else {
            document.getElementById('hours').textContent = '00';
            document.getElementById('minutes').textContent = '00';
            document.getElementById('seconds').textContent = '00';
        }
    }

    // Update timer every second
    updateTimer();
    setInterval(updateTimer, 1000);
</script>
<style>
    .alert{
        padding: 10px;
        width: 100%;
        height : 100%;
        text-align: center;
    }
    .alert i{
        font-size: 70px;
    }
</style>
<?php require_once('footer.php'); ?>