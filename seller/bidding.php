<?php
require_once('header.php');?>

<link rel="stylesheet" href="css/timer.css">

<?php

$seller_id = $_SESSION['seller_session']['seller_id']; // Retrieve seller's ID from session

// Query to get the global send_time and close_time set by admin
$settingsQuery = "SELECT send_time, close_time FROM bid_settings WHERE id = 1";
$settingsResult = $pdo->query($settingsQuery)->fetch(PDO::FETCH_ASSOC);
$sendTime = $settingsResult['send_time'];
$closeTime = $settingsResult['close_time'];

// Set the query to get products with bid_status = 1 (sent to seller) and belonging to the logged-in seller
$query = "
    SELECT 
        p.id AS product_id,
        p.p_name,
        p.p_featured_photo,
        (SELECT COUNT(*) FROM bidding WHERE product_id = p.id AND bid_status = 1) AS no_of_bids
    FROM 
        tbl_product p
    WHERE 
        p.seller_id = :seller_id
    HAVING 
        no_of_bids > 0
";

// Execute the query
$statement = $pdo->prepare($query);
$statement->execute(['seller_id' => $seller_id]);
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

date_default_timezone_set('Asia/Kolkata');
$target_time = strtotime($closeTime);
$current_time = time();
$remaining_seconds = $target_time - $current_time;
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Bidding</h1>
    </div>
    <div class="content-header-right">
    <div class="timer-display">
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

                    <!-- Display global send_time and close_time at the top of the table with red background -->
                    <div class="alert alert-danger">
                        <strong>Bidding Time:</strong> 
                        Open: <?php echo $sendTime ? date('d-m-Y H:i:s', strtotime($sendTime)) : 'N/A'; ?>, 
                        Close: <?php echo $closeTime ? date('d-m-Y H:i:s', strtotime($closeTime)) : 'N/A'; ?>
                    </div>

                    <!-- Product Bidding Table -->
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
                            // Initialize the $i variable
                            $i = 0;
                            ?>

                            <?php foreach ($result as $row): ?>
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
                </div>
            </div>
        </div>
    </div>
</section>



<script>
        // Initialize the countdown with PHP variables
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
