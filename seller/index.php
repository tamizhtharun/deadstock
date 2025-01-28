<?php require_once('header.php'); 

// require_once('../track_view.php');
// trackPageView('SRP', 'Seller Panel');
?>

<section class="content-header">
	<h1>Dashboard</h1>
</section>
<?php
if(isset($_SESSION['seller_session'])) {
    $seller_id = $_SESSION['seller_session']['seller_id'];
    
    // Get seller status from database
    $statement = $pdo->prepare("SELECT seller_status FROM sellers WHERE seller_id = ?");
    $statement->execute([$seller_id]);
    $seller_status = $statement->fetchColumn();
    
    if($seller_status == 0) {
        header('Location: profile-edit.php');
        exit;
    }
}

$statement = $pdo->prepare("SELECT * FROM tbl_product");
$statement->execute();
$total_product = $statement->rowCount();


// Query to calculate total revenue
$query = "SELECT SUM(price * quantity) AS total_revenue 
          FROM tbl_orders 
          WHERE order_status != 'canceled'";
$result = $conn->query($query);

$total_revenue = 0;
if ($result && $row = $result->fetch_assoc()) {
    $total_revenue = $row['total_revenue'];
}
// Calculate today's revenue for the seller
$statement = $pdo->prepare("
    SELECT SUM(price * quantity) AS total_revenue 
    FROM tbl_orders 
    WHERE order_status != 'cancelled' 
      AND DATE(created_at) = CURDATE() 
      AND seller_id = ?
");
$statement->execute([$seller_id]);
$today_revenue = $statement->fetchColumn();
$today_revenue = $today_revenue ? $today_revenue : 0; // Handle null values

// echo "₹" . format_number_short($today_revenue);
// Calculate today's total orders for the seller, excluding canceled orders
$statement = $pdo->prepare("
    SELECT COUNT(*) AS total_orders 
    FROM tbl_orders 
    WHERE order_status != 'cancelled' 
      AND DATE(created_at) = CURDATE() 
      AND seller_id = ?
");
$statement->execute([$seller_id]);
$todays_orders = $statement->fetchColumn();
$todays_orders = $todays_orders ? $todays_orders : 0; // Handle null values

// Calculate total bids for the seller, excluding canceled orders
$statement = $pdo->prepare("
    SELECT COUNT(*) AS total_bids 
    FROM tbl_orders 
    WHERE seller_id = ? 
      AND bid_id IS NOT NULL
      AND order_type = 'bid'
      AND order_status != 'canceled'
");
$statement->execute([$seller_id]);
$total_bids = $statement->fetchColumn();
$total_bids = $total_bids ? $total_bids : 0; // Handle null values


// Calculate today's direct buys for the seller, excluding canceled orders
$statement = $pdo->prepare("
    SELECT COUNT(*) AS today_direct_buys 
    FROM tbl_orders 
    WHERE seller_id = ? 
      AND order_type = 'direct' 
      AND order_status != 'cancelled'
      AND DATE(created_at) = CURDATE()
");
$statement->execute([$seller_id]);
$today_direct_buys = $statement->fetchColumn();
$today_direct_buys = $today_direct_buys ? $today_direct_buys : 0; // Handle null values
?>

<head>
    <!-- Include Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- <script>
        Chart.defaults.responsive = true;
        Chart.defaults.maintainAspectRatio = false;
    </script> -->
</head>
<section class="content">
<!-- <div class="container"> -->
<div class="row">
    <div class="col-xl-3 col-lg-3">
        <div class="card l-bg-cherry">
            <div class="card-statistic-3 p-3">
                <div class="card-icon card-icon-large"><i class="fas fa-shopping-cart"></i></div>
                <div class="mb-0">
                    <h5 class="card-title mb-0">Approved Products</h5>

                </div>
                <div class="row align-items-center mb-2 d-flex">
                    <div class="col-8">
                        <h2 class="d-flex align-items-center mb-0">
                            <?php
                            $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_product WHERE p_is_approve=1 AND seller_id=?");
                            $statement->execute([$seller_id]);
                            $total_approved_product = $statement->fetchColumn();
                            echo $total_approved_product;
                            ?>
                        </h2>Products
                    </div>
                    <?php
                    $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_product WHERE seller_id=?");
                    $statement->execute([$seller_id]);
                    $total_product = $statement->fetchColumn();
                    if ($total_product != 0) {
                        $percentage_of_approved_products = ($total_approved_product / $total_product) * 100;
                    } else {
                        $percentage_of_approved_products = 0;
                    }
                    ?>
                    <div class="col-4 text-right">
                        <span><?php echo number_format($percentage_of_approved_products, 1); ?>% <i class="fa fa-check"></i></span>
                    </div>
                </div>
                <div class="progress mt-1" data-height="8" style="height: 8px;">
                    <div class="progress-bar l-bg-cyan" role="progressbar" data-width="25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percentage_of_approved_products; ?>%;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-3">
        <div class="card l-bg-blue-dark">
            <div class="card-statistic-3 p-3">
                <div class="card-icon card-icon-large"><i class="fas fa-box"></i></div>
                <div class="mb-6">
                    <h5 class="card-title mb-0">Total Orders</h5>
                </div>
                <div class="row align-items-center mb-2 d-flex">
                    <div class="col-8">
                        <h2 class="d-flex align-items-center mb-0">
                            <?php 
                            // Fetch total number of orders for this seller
                            $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_orders WHERE seller_id = ?");
                            $statement->execute([$seller_id]);
                            $total_orders = $statement->fetchColumn();

                            // Fetch non-canceled orders for this seller
                            $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_orders WHERE seller_id = ? AND order_status != 'cancelled'");
                            $statement->execute([$seller_id]);
                            $total_non_cancelled_orders = $statement->fetchColumn();

                            echo $total_non_cancelled_orders;
                            ?>
                        </h2>Orders
                    </div>
                    <div class="col-4 text-right">
                        <?php
                        // Calculate the percentage of non-canceled orders
                        if ($total_orders != 0) {
                            $percentage_of_non_cancelled_orders = ($total_non_cancelled_orders / $total_orders) * 100;
                        } else {
                            $percentage_of_non_cancelled_orders = 0;
                        }
                        ?>
                        <span><?php echo number_format($percentage_of_non_cancelled_orders, 1); ?>% <i class="fa fa-check"></i></span>
                    </div>
                </div>
                <div class="progress mt-1" data-height="8" style="height: 8px;">
                    <div class="progress-bar l-bg-green" role="progressbar" data-width="25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percentage_of_non_cancelled_orders; ?>%;"></div>
                    </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-lg-3">
        <div class="card l-bg-green-dark">
            <div class="card-statistic-3 p-4">
                <div class="card-icon card-icon-large"><i class="fas fa-gavel"></i></div>
                <div class="mb-4">
                    <h5 class="card-title mb-0">Today's Bids</h5>
                </div>
                <div class="row align-items-center mb-2 d-flex">
                    <div class="col-8">
                        <h2 class="d-flex align-items-center mb-0">
                            <?php
                            // Calculate today's bids for the seller
                            $statement = $pdo->prepare("
                                SELECT COUNT(*) AS total_bids 
                                FROM bidding b
                                INNER JOIN tbl_product p ON b.product_id = p.id
                                WHERE DATE(b.bid_time) = CURDATE() AND p.seller_id = ?
                            ");
                            $statement->execute([$seller_id]);
                            $today_bids = $statement->fetchColumn();
                            $today_bids = $today_bids ? $today_bids : 0; // Handle null values

                            echo $today_bids;
                            ?>
                        </h2>
                    </div>
                    <div class="col-4 text-right">
                        <?php
                        // Calculate percentage change compared to yesterday's bids for the seller
                        $statement = $pdo->prepare("
                            SELECT COUNT(*) AS total_bids 
                            FROM bidding b
                            INNER JOIN tbl_product p ON b.product_id = p.id
                            WHERE DATE(b.bid_time) = CURDATE() - INTERVAL 1 DAY AND p.seller_id = ?
                        ");
                        $statement->execute([$seller_id]);
                        $yesterday_bids = $statement->fetchColumn();
                        $yesterday_bids = $yesterday_bids ? $yesterday_bids : 0; // Handle null values

                        if ($yesterday_bids > 0) {
                            $percentage_change = (($today_bids - $yesterday_bids) / $yesterday_bids) * 100;
                        } else {
                            $percentage_change = $today_bids > 0 ? 100 : 0; // If no bids yesterday, assume 100% increase
                        }
                        ?>
                        <span><?php echo number_format($percentage_change, 1); ?>% 
                            <?php echo $percentage_change >= 0 ? '<i class="fa fa-arrow-up"></i>' : '<i class="fa fa-arrow-down"></i>'; ?>
                        </span>
                    </div>
                </div>
                <div class="progress mt-1 " data-height="8" style="height: 8px;">
                    <?php
                    // Calculate progress percentage (example: target 100 bids per day)
                    $target_bids = 100; // Define a target value
                    $progress_percentage = $today_bids > 0 ? min(($today_bids / $target_bids) * 100, 100) : 0;
                    ?>
                    <div class="progress-bar l-bg-orange" role="progressbar" aria-valuenow="<?php echo $progress_percentage; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $progress_percentage; ?>%;"></div>
                </div>



            </div>
        </div>
    </div>
    

    <div class="col-xl-3 col-lg-3">
        <div class="card l-bg-orange-dark">
            <div class="card-statistic-3 p-4">
                <div class="card-icon card-icon-large"><i class="fas fa-rupee-sign"></i></div>
                <div class="mb-4">
                    <h5 class="card-title mb-0">Total Revenue</h5>
                </div>
                <div class="row align-items-center mb-2 d-flex">
                    <div class="col-8">
                        <h2 class="d-flex align-items-center mb-0">
                            <?php 
                            // Function to format numbers into shorter format
                            function format_number_short($n) {
                                if ($n >= 1000000) {
                                    return round($n / 1000000, 1) . 'M'; // Millions
                                } elseif ($n >= 1000) {
                                    return round($n / 1000, 1) . 'k'; // Thousands
                                }
                                return $n; // Less than 1k
                            }

                            // Calculate current total revenue for the seller
                            $statement = $pdo->prepare("
                                SELECT SUM(price * quantity) AS total_revenue 
                                FROM tbl_orders 
                                WHERE order_status != 'cancelled' 
                                  AND MONTH(created_at) = MONTH(CURRENT_DATE()) 
                                  AND YEAR(created_at) = YEAR(CURRENT_DATE()) 
                                  AND seller_id = ?
                            ");
                            $statement->execute([$seller_id]);
                            $current_revenue = $statement->fetchColumn();
                            $current_revenue = $current_revenue ? $current_revenue : 0; // Handle null values

                            // Calculate previous month's revenue for the seller
                            $statement = $pdo->prepare("
                                SELECT SUM(price * quantity) AS total_revenue 
                                FROM tbl_orders 
                                WHERE order_status != 'cancelled' 
                                  AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) 
                                  AND YEAR(created_at) = YEAR(CURRENT_DATE()) 
                                  AND seller_id = ?
                            ");
                            $statement->execute([$seller_id]);
                            $previous_revenue = $statement->fetchColumn();
                            $previous_revenue = $previous_revenue ? $previous_revenue : 0; // Handle null values

                            echo "₹" . format_number_short($current_revenue);
                            ?>
                        </h2>
                    </div>
                    <div class="col-4 text-right">
                        <?php
                        // Calculate percentage change
                        if ($previous_revenue > 0) {
                            $percentage_change = (($current_revenue - $previous_revenue) / $previous_revenue) * 100;
                        } else {
                            $percentage_change = $current_revenue > 0 ? 100 : 0; // If no revenue last month, assume 100% increase
                        }
                        ?>
                        <span><?php echo number_format($percentage_change, 1); ?>% 
                            <?php echo $percentage_change >= 0 ? '<i class="fa fa-arrow-up"></i>' : '<i class="fa fa-arrow-down"></i>'; ?>
                        </span>
                    </div>
                </div>
                <div class="progress mt-1 " data-height="8" style="height: 8px;">
                    <?php
                    // Set target revenue (for example, ₹50,000)
                    $target_revenue = 50000;
                    $progress_percentage = $current_revenue > 0 ? min(($current_revenue / $target_revenue) * 100, 100) : 0;
                    ?>
                    <div class="progress-bar l-bg-yellow" role="progressbar" aria-valuenow="<?php echo $progress_percentage; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $progress_percentage; ?>%;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-3">
    <div class="card l-bg-blue-dark">
        <div class="card-statistic-3 p-4">
            <div class="card-icon card-icon-large"><i class="fas fa-rupee-sign"></i>
            </div>
            <div class="mb-4">
                <h5 class="card-title mb-0">Today's Revenue</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-8">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php echo "₹" . format_number_short($today_revenue); ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-xl-3 col-lg-3">
 <div class="card l-bg-orange-dark">
        <div class="card-statistic-3 p-4">
            <div class="card-icon card-icon-large">
                <i class="fas fa-box"></i>
            </div>
            <div class="mb-4">
                <h5 class="card-title mb-0">Today's Orders</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-8">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php echo number_format($todays_orders); ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-xl-3 col-lg-3">
<div class="card l-bg-cherry">
<div class="card-statistic-3 p-4">
            <div class="card-icon card-icon-large">
                <i class="fas fa-gavel"></i>
            </div>
            <div class="mb-4">
                <h5 class="card-title mb-0">Total Bids</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-8">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php echo number_format($total_bids); ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-xl-3 col-lg-3">
    <div class="card l-bg-green-dark">
        <div class="card-statistic-3 p-4">
            <div class="card-icon card-icon-large">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="mb-4">
                <h5 class="card-title mb-0">Today's Direct Buys</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-8">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php echo $today_direct_buys; ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>




</div>




<!-- Revenue Distribution Pie Chart -->
<?php
function getRevenueData($pdo, $seller_id) {
    // First, get the most recent date from the database
    $getLatestDate = "
        SELECT DATE(created_at) as latest_date 
        FROM tbl_orders 
        WHERE order_status != 'canceled' 
        AND seller_id = ?
        ORDER BY created_at DESC 
        LIMIT 1
    ";

    try {
        $stmt = $pdo->prepare($getLatestDate);
        $stmt->execute([$seller_id]);
        $latestDate = $stmt->fetch(PDO::FETCH_COLUMN);

        // If no data found, return empty array
        if (!$latestDate) {
            return [];
        }

        // Get data for the last 6 days based on the latest order date
        $query = "
            SELECT 
                DATE(created_at) as sale_date,
                SUM(price * quantity) as daily_revenue,
                COUNT(DISTINCT id) as order_count
            FROM 
                tbl_orders
            WHERE 
                order_status != 'canceled'
                AND seller_id = ?
                AND DATE(created_at) BETWEEN DATE_SUB(?, INTERVAL 5 DAY) AND ?
            GROUP BY 
                DATE(created_at)
            ORDER BY 
                sale_date ASC
        ";

        $stmt = $pdo->prepare($query);
        $stmt->execute([$seller_id, $latestDate, $latestDate]);

        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'latest_date' => $latestDate
        ];
    } catch (PDOException $e) {
        error_log("Revenue data fetch error: " . $e->getMessage());
        return [];
    }
}


// Assuming seller_id is available in the session
$seller_id = $_SESSION['seller_session']['seller_id'];

// Initialize data arrays
$labels = [];
$revenues = [];
$orderCounts = [];

// Get the revenue data for the specific seller
$result = getRevenueData($pdo, $seller_id);
$revenueData = $result['data'] ?? [];
$latestDate = $result['latest_date'] ?? null;

if ($latestDate) {
    // Create a map for quick data lookup
    $revenueMap = [];
    $orderCountMap = [];
    foreach ($revenueData as $data) {
        $revenueMap[$data['sale_date']] = $data['daily_revenue'];
        $orderCountMap[$data['sale_date']] = $data['order_count'];
    }

    // Generate last 6 days data based on the latest order date
    for ($i = 5; $i >= 0; $i--) {
        $currentDate = date('Y-m-d', strtotime($latestDate . " -$i days"));
        $labels[] = date('M d', strtotime($currentDate));
        $revenues[] = $revenueMap[$currentDate] ?? 0;
        $orderCounts[] = $orderCountMap[$currentDate] ?? 0;
    }

    // Calculate summary statistics
    $totalRevenue = array_sum($revenues);
    $averageRevenue = $totalRevenue > 0 ? $totalRevenue / count(array_filter($revenues)) : 0;
    $totalOrders = array_sum($orderCounts);
} else {
    // Handle case when no orders exist
    $totalRevenue = 0;
    $averageRevenue = 0;
    $totalOrders = 0;
}

?>
<!-- Graph -->
<div class="container-fluid py-4 px-1">
    <div class="row mx-0 d-flex">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Revenue Analytics</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:40vh; width:100%">
                        <canvas id="myChart" style="max-height:100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>


        <div class="col-lg-4 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Revenue Distribution</h5>
                    </div>
                    <div class="card-body">
                        <div class="chart-container" style="position: relative; height:40vh; width:100%">
                            <canvas id="pieChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
    </div>
</div>

<script>
function createDynamicRevenueChart(initialLabels, initialRevenues, initialOrders) {
    const ctx = document.getElementById('myChart').getContext('2d');
    let chartInstance = null;

    function updateChart(labels, revenues, orders) {
        if (chartInstance) {
            chartInstance.destroy();
        }

        chartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: revenues,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgb(17, 18, 19)',
                    borderWidth: 1,
                    yAxisID: 'y'
                }, {
                    label: 'Orders',
                    data: orders,
                    type: 'line',
                    borderColor: 'rgba(75, 192, 192,0.8)',
                    borderWidth: 2,
                    fill: false,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Revenue (₹)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Orders'
                        },
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.label === 'Revenue (₹)') {
                                    return `Revenue: ₹${context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
                                }
                                return `Orders: ${context.parsed.y}`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Initial chart rendering
    updateChart(initialLabels, initialRevenues, initialOrders);

    // Update every 5 minutes
    setInterval(fetchAndUpdateRevenueData, 5 * 60 * 1000);

    return {
        update: fetchAndUpdateRevenueData,
        getCurrentChart: () => chartInstance
    };
}

document.addEventListener('DOMContentLoaded', function() {
    const initialLabels = <?php echo json_encode($labels); ?>;
    const initialRevenues = <?php echo json_encode($revenues); ?>;
    const initialOrders = <?php echo json_encode($orderCounts); ?>;
    
    const revenueChart = createDynamicRevenueChart(initialLabels, initialRevenues, initialOrders);
});

</script>

<style>
    /* .chart-container {
        width: 100% !important;
    } */
    .card {
        max-width: none !important;
    }
            /* Add this CSS for responsiveness */
    @media (max-width: 768px) {
        .chart-container {
            height: 250px; /* Adjust height for smaller screens */
        }
    }

</style>
              
            
  
</section>


<?php require_once('footer.php'); ?>