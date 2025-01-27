<?php require_once('header.php'); ?>

<section class="content-header">
	<h1>Dashboard</h1>
</section>

<?php
// $statement = $pdo->prepare("SELECT * FROM tbl_top_category");
// $statement->execute();
// $total_top_category = $statement->rowCount();

// $statement = $pdo->prepare("SELECT * FROM tbl_mid_category");
// $statement->execute();
// $total_mid_category = $statement->rowCount();

// $statement = $pdo->prepare("SELECT * FROM tbl_end_category");
// $statement->execute();
// $total_end_category = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_product");
$statement->execute();
$total_product = $statement->rowCount();

$today_date = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

// $statement = $pdo->prepare("SELECT * FROM tbl_customer WHERE seller_status='1'");
// $statement->execute();
// $total_customers = $statement->rowCount();

// $statement = $pdo->prepare("SELECT * FROM tbl_subscriber WHERE subs_active='1'");
// $statement->execute();
// $total_subscriber = $statement->rowCount();

// $statement = $pdo->prepare("SELECT * FROM tbl_shipping_cost");
// $statement->execute();
// $available_shipping = $statement->rowCount();

// $statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_status=?");
// $statement->execute(array('Completed'));
// $total_order_completed = $statement->rowCount();

// $statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE shipping_status=?");
// $statement->execute(array('Completed'));
// $total_shipping_completed = $statement->rowCount();

// $statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_status=?");
// $statement->execute(array('Pending'));
// $total_order_pending = $statement->rowCount();

// $statement = $pdo->prepare("SELECT * FROM tbl_payment WHERE payment_status=? AND shipping_status=?");
// $statement->execute(array('Completed','Pending'));
// $total_order_complete_shipping_pending = $statement->rowCount();
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
<div class="row ">
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
                        $statement =$pdo->prepare("SELECT COUNT(*) FROM tbl_product where p_is_approve=1");
                        $statement->execute();
                        $total_approved_product = $statement->fetchColumn();
                        echo $total_approved_product
                        ?> 
                        
                    </h2>Products
                </div>
                <?php
            $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_product");
            $statement->execute();
            $total_product = $statement->fetchColumn();
            if($total_product!=0){
            $percentage_of_approved_products = ($total_approved_product / $total_product) * 100;
            }else{
                $percentage_of_approved_products = 0;
            }
            ?>
                <div class="col-4 text-right">
                    <span><?php echo number_format($percentage_of_approved_products,1)?>% <i class="fa fa-check"></i></span>
                </div>
            </div>
            <div class="progress mt-1 " data-height="8" style="height: 8px;">

            <?php
            $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_product");
            $statement->execute();
            $total_product = $statement->fetchColumn();
            if($total_product!=0){
            $percentage_of_approved_products = ($total_approved_product / $total_product) * 100;
            }else{
                $percentage_of_approved_products = 0;
            }
            ?>


                <div class="progress-bar l-bg-cyan" role="progressbar" data-width="25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percentage_of_approved_products?>%;"></div>
            </div>
        </div>
    </div>
</div>
<div class="col-xl-3 col-lg-3">
    <div class="card l-bg-blue-dark">
        <div class="card-statistic-3 p-3">
            <div class="card-icon card-icon-large"><i class="fas fa-users"></i></div>
            <div class="mb-6">
                <h5 class="card-title mb-0">Active Sellers</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-8">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php 
                        $statement = $pdo->prepare("SELECT  COUNT(*) FROM sellers WHERE seller_status=1");
                        $statement->execute();
                        $total_seller_active = $statement->fetchColumn();
                        echo $total_seller_active
                        ?><?php ?> 
                    </h2>Sellers
                </div>
                <div class="col-4 text-right">
                <?php
               $statement = $pdo->prepare("SELECT  COUNT(*) FROM sellers");
               $statement->execute();
              $total_sellers= $statement->fetchColumn();
              $percentage_of_active_sellers= ($total_seller_active/$total_sellers) * 100;
              ?>
                    <span><?php echo number_format($percentage_of_active_sellers,1)?>% <i class="fa fa-check"></i></span>
                </div>
            </div>
            <div class="progress mt-1 " data-height="8" style="height: 8px;">
              
                <div class="progress-bar l-bg-green" role="progressbar" data-width="25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo$percentage_of_active_sellers?>%;"></div>
            </div>
        </div>
    </div>
</div>
 <div class="col-xl-3 col-lg-3">
    <div class="card l-bg-green-dark">
        <div class="card-statistic-3 p-3">
            <div class="card-icon card-icon-large"><i class="fas fa fa-gavel"></i></div>
            <div class="mb-2">
                <h5 class="card-title mb-0">Bids Today</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-8">
                <h2 class="d-flex align-items-center mb-0">
                    <?php 
                        $statement = $pdo->prepare("SELECT  COUNT(*) FROM bidding WHERE DATE(bid_time) = '$today_date'");
                        $statement->execute();
                        $total_bids_today = $statement->fetchColumn();
                        echo $total_bids_today
                        ?> 
                    </h2>Bids
                </div>
                <div class="col-4 text-right">
                    <span>
                        <?php
                    $sql_yesterday = "SELECT COUNT(*) AS total_yesterday FROM bidding WHERE DATE(bid_time) = '$yesterday'";
                    $result_yesterday = $conn->query($sql_yesterday);
                    $row_yesterday = $result_yesterday->fetch_assoc();
                    $total_yesterday = $row_yesterday['total_yesterday'];

                    if ($total_yesterday > 0) {
                        $change = $total_bids_today - $total_yesterday;
                        $percentage_change = ($change / $total_yesterday) * 100;
                    } else {
                        $percentage_change = $total_bids_today > 0 ? 100 : 0;
                    }
                    if ($percentage_change > 0) {
                        echo $percentage_change . '% <i class="fa fa-arrow-up"></i>';
                    } else if ($percentage_change < 0) {
                        echo $percentage_change. '%<i class="fa fa-arrow-down"></i>';
                    }  else {
                        echo $percentage_change . '% <i class="fa fa-arrow-up"></i>';
                    }
                    ?>
                </div>
            </div>
            <div class="progress mt-1 " data-height="8" style="height: 8px;">
                <div class="progress-bar l-bg-orange" role="progressbar" data-width="25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: 50%;"></div>
            </div>
        </div>
    </div>
</div>
<div class="col-xl-3 col-lg-3">
    <div class="card l-bg-orange-dark">
        <div class="card-statistic-3 p-4">
            <div class="card-icon card-icon-large"><i class="fas fa-rupee-sign"></i></div>
            <div class="mb-4">
                <h5 class="card-title mb-0">Revenue Today</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-8">
                    <h2 class="d-flex align-items-center mb-0">
                    ₹<?php 
                        $statement = $pdo->prepare("SELECT * FROM tbl_orders WHERE DATE(created_at) = :today_date AND order_status!='canceled'");
                        $statement->bindParam(':today_date', $today_date, PDO::PARAM_STR);
                        $statement->execute();
                        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

                        $total_revenue_today = 0;

                        if ($result) {
                            foreach ($result as $row) {
                                 $total_revenue_today += $row['quantity'] * $row['price'];
                            }
                        }

                        // Format revenue: if >= 1000, convert to 'K' notation
                        if ($total_revenue_today >= 1000) {
                             $formatted_revenue = number_format($total_revenue_today / 1000, 1) . 'K';
                        } else {
                            $formatted_revenue = $total_revenue_today;
                        }

                        echo $formatted_revenue;
                        ?>
                    </h2>
                </div>
                <div class="col-4 text-right">
                    <span>2.5% <i class="fa fa-arrow-up"></i></span>
                </div>
            </div>
            <div class="progress mt-1 " data-height="8" style="height: 8px;">
                <div class="progress-bar l-bg-cyan" role="progressbar" data-width="25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100" style="width: 25%;"></div>
            </div>
        </div>
    </div>
</div>
    </div>


<!-- Revenue Distribution Pie Chart -->
<?php
function getRevenueData($pdo) {
    // First, get the most recent date from the database
    $getLatestDate = "
        SELECT DATE(created_at) as latest_date 
        FROM tbl_orders 
        WHERE order_status != 'canceled'
        ORDER BY created_at DESC 
        LIMIT 1
    ";
    
    try {
        $stmt = $pdo->query($getLatestDate);
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
                AND DATE(created_at) BETWEEN DATE_SUB(?, INTERVAL 5 DAY) AND ?
            GROUP BY 
                DATE(created_at)
            ORDER BY 
                sale_date ASC
        ";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$latestDate, $latestDate]);
        return [
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'latest_date' => $latestDate
        ];
    } catch (PDOException $e) {
        error_log("Revenue data fetch error: " . $e->getMessage());
        return [];
    }
}

// Initialize data arrays
$labels = [];
$revenues = [];
$orderCounts = [];

// Get the revenue data
$result = getRevenueData($pdo);
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
                    borderColor: 'rgba(255, 99, 132, 1)',
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

    function fetchAndUpdateRevenueData() {
        fetch('get_revenue_data.php')
            .then(response => response.json())
            .then(data => {
                updateChart(data.labels.slice(-6), data.revenues.slice(-6), data.orders.slice(-6));
            })
            .catch(error => console.error('Error fetching revenue data:', error));
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
</style>
<!-- <?php
// Get current UTC timestamp
$utc_timestamp = time(); // UNIX timestamp in UTC

// Convert to formatted UTC datetime
$utc_datetime = gmdate('Y-m-d H:i:s');

// Store or use UTC timestamp for consistent time tracking
echo "UTC Timestamp: " . $utc_timestamp;
echo "UTC Datetime: " . $utc_datetime;
?> -->
              
            
		  
</section>

<?php require_once('footer.php'); ?>