<?php 
require_once('header.php');
// require_once('../track_view.php');
// trackPageView('dashboard', 'Admin Dashboard');

?>


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

// New code for fetching view statistics
$statement = $pdo->prepare("SELECT SUM(view_count) FROM page_views");
$statement->execute();
$total_views = $statement->fetchColumn();

$statement = $pdo->prepare("SELECT SUM(view_count) FROM page_views WHERE view_date = CURDATE()");
$statement->execute();
$today_views = $statement->fetchColumn();

$statement = $pdo->prepare("
    SELECT page_id, page_title, SUM(view_count) as total_views 
    FROM page_views 
    GROUP BY page_id, page_title 
    ORDER BY total_views DESC 
    LIMIT 10
");
$statement->execute();
$top_pages = $statement->fetchAll(PDO::FETCH_ASSOC);

// Fetch data for views over time (last 6 months)
$statement = $pdo->prepare("
    SELECT DATE_FORMAT(view_date, '%Y-%m') as month, SUM(view_count) as views
    FROM page_views
    WHERE view_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(view_date, '%Y-%m')
    ORDER BY month ASC
");
$statement->execute();
$views_over_time = $statement->fetchAll(PDO::FETCH_ASSOC);

// Total orders
$query = "SELECT COUNT(*) AS total_orders FROM tbl_orders WHERE order_status != 'canceled'";
$result = $conn->query($query);

$total_orders = 0;
if ($result && $row = $result->fetch_assoc()) {
    $total_orders = $row['total_orders'];
}



// Function to format numbers as 1k, 1M, etc.
function format_number_short($number) {
    if ($number >= 1000000) {
        return round($number / 1000000, 1) . 'M';
    } elseif ($number >= 1000) {
        return round($number / 1000, 1) . 'k';
    }
    return $number;
}

// Query to calculate total revenue
$query = "SELECT SUM(price * quantity) AS total_revenue 
          FROM tbl_orders 
          WHERE order_status != 'canceled'";
$result = $conn->query($query);

$total_revenue = 0;
if ($result && $row = $result->fetch_assoc()) {
    $total_revenue = $row['total_revenue'];
}


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

  <!-- Orders card -->
  <div class="col-xl-3 col-lg-3">
  <div class="card l-bg-orange-dark">
        <div class="card-statistic-3 p-4">
            <div class="card-icon card-icon-large"><i class="fas fa-shopping-cart"></i></div>
            <div class="mb-4">
                <h5 class="card-title mb-0">Total Orders</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-8">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php echo number_format($total_orders); ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>



          <!-- New card for Total Views -->
          <div class="col-xl-3 col-lg-3">
          <div class="card l-bg-cherry">
                <div class="card-statistic-3 p-4">
                    <div class="card-icon card-icon-large"><i class="fas fa-eye"></i></div>
                    <div class="mb-4">
                        <h5 class="card-title mb-0">Total Views</h5>
                    </div>
                    <div class="row align-items-center mb-2 d-flex">
                        <div class="col-8">
                            <h2 class="d-flex align-items-center mb-0">
                                <?php echo number_format($total_views); ?>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- New card for Today's Views -->
        <div class="col-xl-3 col-lg-3">
        <div class="card l-bg-blue-dark">
                <div class="card-statistic-3 p-4">
                    <div class="card-icon card-icon-large"><i class="fas fa-chart-line"></i></div>
                    <div class="mb-4">
                        <h5 class="card-title mb-0">Today's Views</h5>
                    </div>
                    <div class="row align-items-center mb-2 d-flex">
                        <div class="col-8">
                            <h2 class="d-flex align-items-center mb-0">
                                <?php echo number_format($today_views); ?>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-3">
    <div class="card l-bg-green-dark">
        <div class="card-statistic-3 p-4">
            <div class="card-icon card-icon-large"><i class="fas fa-rupee-sign"></i></div>
            <div class="mb-4">
                <h5 class="card-title mb-0">Total Revenue</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-8">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php echo "₹" .format_number_short($total_revenue); ?>

                    </h2>
                </div>
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
    <div class="row mx-0">
        <!-- Views Over Time Graph -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Monthly Page Views<span class="ms-2 badge bg-light text-secondary" style="font-size: 11px; font-weight: normal;">Last 6 months</span></h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:45vh;">
                        <canvas id="viewsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page-wise View Counts with Pagination -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Page-wise View Counts</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 45vh;">
                        <table class="table table-striped table-hover">
                            <thead class="sticky-top bg-white">
                                <tr>
                                    <th>Page Title</th>
                                    <th class="text-end">Views</th>
                                </tr>
                            </thead>
                            <tbody id="pageViewsTableBody">
                                <!-- Table content will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted small">
                            Showing <span id="startRange">1</span>-<span id="endRange">10</span> of <span id="totalItems">0</span>
                        </div>
                        <div class="pagination-container">
                            <button class="btn btn-sm btn-outline-primary me-2" id="prevPage" disabled>
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-primary" id="nextPage" disabled>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
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

    // Pagination and table data handling
let currentPage = 1;
const itemsPerPage = 6;
let pageViewsData = <?php echo json_encode($top_pages); ?>;

function updateTable() {
    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, pageViewsData.length);
    const tableBody = document.getElementById('pageViewsTableBody');
    
    // Clear existing rows
    tableBody.innerHTML = '';
    
    // Add new rows
    for(let i = startIndex; i < endIndex; i++) {
        const row = pageViewsData[i];
        tableBody.innerHTML += `
            <tr>
                <td>${row.page_title}</td>
                <td class="text-end">${number_format(row.total_views)}</td>
            </tr>
        `;
    }
    
    // Update pagination info
    document.getElementById('startRange').textContent = startIndex + 1;
    document.getElementById('endRange').textContent = endIndex;
    document.getElementById('totalItems').textContent = pageViewsData.length;
    
    // Update button states
    document.getElementById('prevPage').disabled = currentPage === 1;
    document.getElementById('nextPage').disabled = endIndex >= pageViewsData.length;
}

// Pagination event listeners
document.getElementById('prevPage').addEventListener('click', () => {
    if(currentPage > 1) {
        currentPage--;
        updateTable();
    }
});

document.getElementById('nextPage').addEventListener('click', () => {
    if((currentPage * itemsPerPage) < pageViewsData.length) {
        currentPage++;
        updateTable();
    }
});

// Format numbers with commas
function number_format(number) {
    return new Intl.NumberFormat().format(number);
}

// Initialize table
updateTable();

// Improved Views Chart
const viewsCtx = document.getElementById('viewsChart').getContext('2d');
const viewsChart = new Chart(viewsCtx, {
    type: 'line',
    data: {
        labels: <?php 
            // Convert YYYY-MM format to abbreviated month name
            echo json_encode(array_map(function($date) {
                return date('M', strtotime($date['month']));
            }, $views_over_time)); 
        ?>,
        datasets: [{
            label: 'Monthly Views',
            data: <?php echo json_encode(array_column($views_over_time, 'views')); ?>,
            borderColor: '#2196F3',
            backgroundColor: 'rgba(33, 150, 243, 0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#2196F3',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                backgroundColor: 'rgba(255, 255, 255, 0.9)',
                titleColor: '#000',
                bodyColor: '#666',
                borderColor: '#ddd',
                borderWidth: 1,
                padding: 10,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return 'Views: ' + number_format(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)',
                    drawBorder: false
                },
                ticks: {
                    callback: function(value) {
                        return number_format(value);
                    }
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    }
});

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
    
/* Add these styles to your existing CSS */
.chart-container {
    background: white;
    border-radius: 4px;
    padding: 10px;
}

.table-responsive {
    scrollbar-width: thin;
    scrollbar-color: rgba(0, 0, 0, 0.2) transparent;
}

.table-responsive::-webkit-scrollbar {
    width: 6px;
}

.table-responsive::-webkit-scrollbar-track {
    background: transparent;
}

.table-responsive::-webkit-scrollbar-thumb {
    background-color: rgba(0, 0, 0, 0.2);
    border-radius: 3px;
}

.sticky-top {
    top: 0;
    z-index: 1;
    box-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.pagination-container .btn {
    padding: 0.25rem 0.5rem;
}

.pagination-container .btn i {
    font-size: 0.75rem;
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