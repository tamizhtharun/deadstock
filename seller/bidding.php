<?php
// bidding.php
require_once('header.php');

$statement = $pdo->prepare("SELECT * FROM bid_settings WHERE id = '1'");
$statement->execute();
$settings = $statement->fetch(PDO::FETCH_ASSOC);
$send_time = $settings['send_time'];
$close_time = $settings['close_time'];

// Enhanced Bid Time Manager Class
class BidTimeManager {
    private $pdo;
    private $serverTimeZone;

    public function __construct(PDO $pdo, string $timeZone = 'Asia/Kolkata') {
        $this->pdo = $pdo;
        $this->serverTimeZone = $timeZone;
        date_default_timezone_set($this->serverTimeZone);
    }

    private function getBidSettings(): array {
        $settingsQuery = "SELECT send_time, close_time FROM bid_settings WHERE id = 1";
        $settings = $this->pdo->query($settingsQuery)->fetch(PDO::FETCH_ASSOC);
        
        if (!$settings) {
            throw new Exception("Bid settings not found");
        }

        return $settings;
    }

    public function getCurrentServerTime(): DateTime {
        return new DateTime('now', new DateTimeZone($this->serverTimeZone));
    }

    public function isBiddingActive(): bool {
        $settings = $this->getBidSettings();
        $currentTime = $this->getCurrentServerTime();
        
        $todayDate = $currentTime->format('Y-m-d');
        $sendDateTime = new DateTime($todayDate . ' ' . $settings['send_time'], new DateTimeZone($this->serverTimeZone));
        $closeDateTime = new DateTime($todayDate . ' ' . $settings['close_time'], new DateTimeZone($this->serverTimeZone));

        return ($currentTime >= $sendDateTime && $currentTime <= $closeDateTime);
    }

    public function getRemainingBiddingTime(): array {
        if (!$this->isBiddingActive()) {
            return [
                'hours' => 0,
                'minutes' => 0,
                'seconds' => 0,
                'total_seconds' => 0
            ];
        }

        $settings = $this->getBidSettings();
        $currentTime = $this->getCurrentServerTime();
        $todayDate = $currentTime->format('Y-m-d');
        $closeDateTime = new DateTime($todayDate . ' ' . $settings['close_time'], new DateTimeZone($this->serverTimeZone));
        
        $interval = $currentTime->diff($closeDateTime);
        $totalSeconds = 
            $interval->h * 3600 + 
            $interval->i * 60 + 
            $interval->s;
        
        return [
            'hours' => $interval->h,
            'minutes' => $interval->i,
            'seconds' => $interval->s,
            'total_seconds' => $totalSeconds,
            'send_time' => $settings['send_time'],
            'close_time' => $settings['close_time']
        ];
    }
}

// Main Bidding Page Logic
try {
    // Validate seller is logged in
    if (!isset($_SESSION['seller_session']['seller_id'])) {
        throw new Exception("Unauthorized access");
    }

    $seller_id = $_SESSION['seller_session']['seller_id'];
    $bidTimeManager = new BidTimeManager($pdo);
    
    // Check if bidding is active
    $biddingActive = $bidTimeManager->isBiddingActive();
    $remainingTime = $bidTimeManager->getRemainingBiddingTime();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bidding Dashboard</title>
    <link rel="stylesheet" href="css/timer.css">
</head>
<body>
    <section class="content-header">
        <div class="content-header-left">
            <h1>Bidding Dashboard</h1>
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
                        <?php if (!$biddingActive): ?>
                            <div class="alert no-details">
                                <i class="fa fa-gavel" aria-hidden="true"></i>
                                <h2>Bidding is currently closed</h2>
                                <p>Please wait for the next bidding period.</p>
                                <br />
                                <h4>
                                    Next bidding period: 
                                    <?php 
                                    echo date('h:i A', strtotime($send_time)) . 
                                         ' to ' . 
                                         date('h:i A', strtotime($close_time)); 
                                    ?>
                                </h4>
                            </div>
                        <?php else: 
                            // Fetch products with bids
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

                            $statement = $pdo->prepare($query);
                            $statement->execute([
                               'seller_id' => $seller_id,
                               'today_send_time' => $bidTimeManager->getCurrentServerTime()->format('Y-m-d H:i:s')
                            ]);
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                        ?>
                            <?php if (empty($result)): ?>
                                <div class="alert no-details">
                                    <i class="fa fa-gavel" aria-hidden="true"></i>
                                    <h2>Sorry, there is no bid found for your products</h2>
                                    <p>Please wait for the next bidding period.</p>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <strong>Bidding Time:</strong> 
                                    Open: <?php echo date('d-m-Y H:i:s', strtotime($remainingTime['send_time'])); ?>, 
                                    Close: <?php echo date('d-m-Y H:i:s', strtotime($remainingTime['close_time'])); ?>
                                    <br />
                                    Make a decision quickly
                                </div>

                                <table class="table table-bordered table-hover table-striped">
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
                                        <?php foreach ($result as $i => $row): ?>
                                            <tr>
                                                <td><?php echo $i + 1; ?></td>
                                                <td>
                                                    <img src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>" 
                                                         alt="Product Photo" 
                                                         style="width:70px;">
                                                </td>
                                                <td><?php echo htmlspecialchars($row['p_name']); ?></td>
                                                <td><?php echo $row['no_of_bids']; ?></td>
                                                <td><a href="view_bid.php?id=<?php echo $row['product_id']; ?>">View all Bids</a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const biddingActive = <?php echo $biddingActive ? 'true' : 'false'; ?>;
        const remainingTime = <?php echo json_encode($remainingTime); ?>;

        function updateTimer() {
            if (!biddingActive) {
                ['hours', 'minutes', 'seconds'].forEach(unit => {
                    document.getElementById(unit).textContent = '00';
                });
                return;
            }

            let totalSeconds = remainingTime.total_seconds;
            
            if (totalSeconds > 0) {
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;

                document.getElementById('hours').textContent = String(hours).padStart(2, '0');
                document.getElementById('minutes').textContent = String(minutes).padStart(2, '0');
                document.getElementById('seconds').textContent = String(seconds).padStart(2, '0');

                remainingTime.total_seconds--;
            } else {
                ['hours', 'minutes', 'seconds'].forEach(unit => {
                    document.getElementById(unit).textContent = '00';
                });
            }
        }

        // Initial update
        updateTimer();
        
        // Update every second
        setInterval(updateTimer, 1000);
    });
    </script>

    <style>
        .alert {
            padding: 10px;
            width: 100%;
            text-align: center;
        }
        .alert i {
            font-size: 70px;
        }
    </style>
</body>
</html>

<?php 
} catch (Exception $e) {
    // Log the error securely
    error_log('Bidding Page Error: ' . $e->getMessage());
    
    // Redirect to an error page or show a generic error
    header('Location: error.php');
    exit();
}

require_once('footer.php');
?>