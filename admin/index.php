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




// fetching view statistics
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

try {
    // Top Performing Products
    $statement = $pdo->prepare("
        SELECT p.p_name, p.p_featured_photo, SUM(o.quantity) as total_sold, SUM(o.quantity * o.price) as total_revenue
        FROM tbl_product p
        JOIN tbl_orders o ON p.id = o.product_id
        WHERE o.order_status != 'canceled'
        GROUP BY p.id
        ORDER BY total_sold DESC
        LIMIT 10
    ");
    $statement->execute();
    $top_products = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Seller Performance Dashboard
    $statement = $pdo->prepare("
        SELECT s.seller_name, COUNT(DISTINCT o.order_id) as total_orders, SUM(o.quantity * o.price) as total_revenue
        FROM sellers s
        LEFT JOIN tbl_orders o ON s.seller_id = o.seller_id
        WHERE o.order_status != 'canceled'
        GROUP BY s.seller_id
        ORDER BY total_revenue DESC
        LIMIT 10
    ");
    $statement->execute();
    $top_sellers = $statement->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle the error (e.g., log it or display a user-friendly message)
    error_log("Database Error: " . $e->getMessage());
    // You might want to set $top_products and $top_sellers to empty arrays here
    $top_products = $top_sellers = [];
}
// Fetch order status distribution from tbl_orders
$statement = $pdo->prepare(" 
    SELECT order_status, COUNT(*) as count
    FROM tbl_orders
    GROUP BY order_status
");
$statement->execute();
$order_status_distribution = $statement->fetchAll(PDO::FETCH_ASSOC);

// Fetch count of Not Sent Orders (bids with bid_status = 2 but not in tbl_orders)
$not_sent_statement = $pdo->prepare(" 
    SELECT COUNT(*) as count
    FROM bidding b
    WHERE b.bid_status = 2 
    AND NOT EXISTS (
        SELECT 1 FROM tbl_orders o 
        WHERE o.bid_id = b.bid_id
    )
");
$not_sent_statement->execute();
$not_sent_orders = $not_sent_statement->fetch(PDO::FETCH_ASSOC)['count'];

// Append Not Sent Orders to the distribution array
$order_status_distribution[] = ['order_status' => 'Not Sended Orders', 'count' => $not_sent_orders];

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
<!-- Approved products card -->
<div class="col-xl-3 col-lg-3">
    <div class="card l-bg-cherry">
        <div class="card-statistic-3 p-3">
            <div class="card-icon card-icon-large"><i class="fas fa-shopping-cart"></i></div>
            <div class="mb-0">
                <h5 class="card-title mb-0">Approved Products</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-7" style="padding-left: 20px;">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php
                        // Fetch total approved products across all sellers
                        $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_product WHERE p_is_approve=1");
                        $statement->execute();
                        $total_approved_product = $statement->fetchColumn();
                        echo $total_approved_product;
                        ?> 
                    </h2> Products
                </div>
                <?php
                // Fetch total products (uploaded + approved)
                $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_product");
                $statement->execute();
                $total_product = $statement->fetchColumn();

                // Calculate uploaded products
                $total_uploaded_product = $total_product - $total_approved_product;

                // Calculate progress percentages
                $percentage_approved = ($total_product != 0) ? ($total_approved_product / $total_product) * 100 : 0;
                $percentage_uploaded = ($total_product != 0) ? ($total_uploaded_product / $total_product) * 100 : 0;
                ?>
                <div class="col-4 text-right">
                    <span><?php echo number_format($percentage_approved, 1); ?>% <i class="fa fa-check"></i></span>
                </div>
            </div>
            <div class="progress mt-1" data-height="8" style="height: 8px;">
                <!-- Uploaded Products Progress (Red) with Tooltip -->
                <div class="progress-bar bg-danger" role="progressbar"
                    style="width: <?php echo $percentage_uploaded; ?>%;" 
                    aria-valuenow="<?php echo $percentage_uploaded; ?>" 
                    aria-valuemin="0" 
                    aria-valuemax="100"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="Uploaded Products: <?php echo $total_uploaded_product; ?>">
                </div>
                <!-- Approved Products Progress (Cyan) -->
                <div class="progress-bar l-bg-cyan" role="progressbar"
                    style="width: <?php echo $percentage_approved; ?>%;" 
                    aria-valuenow="<?php echo $percentage_approved; ?>" 
                    aria-valuemin="0" 
                    aria-valuemax="100">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enable Bootstrap Tooltip -->
<script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip(); 
    });
</script>

<!-- Active sellers card -->
<div class="col-xl-3 col-lg-3">
    <div class="card l-bg-blue-dark">
        <div class="card-statistic-3 p-3">
            <div class="card-icon card-icon-large"><i class="fas fa-users"></i></div>
            <div class="mb-6">
                <h5 class="card-title mb-0">Active Sellers</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-7" style="padding-left: 20px;">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php 
                        // Get Active Sellers
                        $statement = $pdo->prepare("SELECT COUNT(*) FROM sellers WHERE seller_status=1");
                        $statement->execute();
                        $total_seller_active = $statement->fetchColumn();
                        echo $total_seller_active;
                        ?>
                    </h2> Sellers
                </div>
                <div class="col-4 text-right">
                    <?php
                    // Get Total Sellers
                    $statement = $pdo->prepare("SELECT COUNT(*) FROM sellers");
                    $statement->execute();
                    $total_sellers = $statement->fetchColumn();

                    // Get Inactive Sellers
                    $total_seller_inactive = $total_sellers - $total_seller_active;

                    // Calculate Percentage
                    $percentage_of_active_sellers = ($total_sellers != 0) ? ($total_seller_active / $total_sellers) * 100 : 0;
                    $percentage_of_inactive_sellers = ($total_sellers != 0) ? ($total_seller_inactive / $total_sellers) * 100 : 0;
                    ?>
                    <span><?php echo number_format($percentage_of_active_sellers, 1); ?>% <i class="fa fa-check"></i></span>
                </div>
            </div>
            <div class="progress mt-1" data-height="8" style="height: 8px;">
                <!-- Inactive Sellers Progress (Red) -->
                <div class="progress-bar bg-danger" role="progressbar"
                    style="width: <?php echo max($percentage_of_inactive_sellers, 1); ?>%;" 
                    aria-valuenow="<?php echo $percentage_of_inactive_sellers; ?>" 
                    aria-valuemin="0" 
                    aria-valuemax="100"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="Inactive Sellers: <?php echo $total_seller_inactive; ?>">
                </div>
                <!-- Active Sellers Progress (Green) -->
                <div class="progress-bar l-bg-green" role="progressbar"
                    style="width: <?php echo $percentage_of_active_sellers; ?>%;" 
                    aria-valuenow="<?php echo $percentage_of_active_sellers; ?>" 
                    aria-valuemin="0" 
                    aria-valuemax="100"
                    data-toggle="tooltip"
                    data-placement="top"
                    title="Active Sellers: <?php echo $total_seller_active; ?>">
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Enable Bootstrap Tooltip -->
<script>
    $(document).ready(function(){
        $('[data-toggle="tooltip"]').tooltip(); 
    });
</script>

<div class="col-xl-3 col-lg-3">
    <div class="card l-bg-green-dark">
        <div class="card-statistic-3 p-4">
            <div class="card-icon card-icon-large"><i class="fas fa-gavel"></i></div>
            <div class="mb-4">
                <h5 class="card-title mb-0">Today's Bids</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-7" style="padding-left: 20px;">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php
                        // Get today's total bids
                        $statement = $pdo->prepare("SELECT COUNT(*) FROM bidding WHERE DATE(bid_time) = CURDATE()");
                        $statement->execute();
                        $today_bids = $statement->fetchColumn();
                        echo $today_bids ? $today_bids : 0;
                        ?>
                    </h2>
                </div>
                <div class="col-4 text-right">
                    <?php
                    // Get yesterday's total bids
                    $statement = $pdo->prepare("SELECT COUNT(*) FROM bidding WHERE DATE(bid_time) = CURDATE() - INTERVAL 1 DAY");
                    $statement->execute();
                    $yesterday_bids = $statement->fetchColumn();
                    $yesterday_bids = $yesterday_bids ? $yesterday_bids : 0;

                    // Calculate percentage change
                    if ($yesterday_bids > 0) {
                        $percentage_change = (($today_bids - $yesterday_bids) / $yesterday_bids) * 100;
                    } else {
                        $percentage_change = $today_bids > 0 ? 100 : 0;
                    }
                    ?>
                    <span><?php echo number_format($percentage_change, 1); ?>% 
                        <?php echo $percentage_change >= 0 ? '<i class="fa fa-arrow-up"></i>' : '<i class="fa fa-arrow-down"></i>'; ?>
                    </span>
                </div>
            </div>

            <div class="progress mt-1" data-height="8" style="height: 8px;">
                <?php
                // Define a target value (adjust as needed)
                $target_bids = 100; 
                $progress_percentage = $today_bids > 0 ? min(($today_bids / $target_bids) * 100, 100) : 0;
                ?>
                <div class="progress-bar l-bg-orange" role="progressbar" 
                     aria-valuenow="<?php echo $progress_percentage; ?>" 
                     aria-valuemin="0" aria-valuemax="100" 
                     style="width: <?php echo $progress_percentage; ?>%;">
                </div>
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
            <div class="col-7" style="padding-left: 20px;">
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
            <div class="col-7" style="padding-left: 20px;">
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
                    <div class="col-7" style="padding-left: 20px;">
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
                    <div class="col-7" style="padding-left: 20px;">
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
            <div class="col-7" style="padding-left: 20px;">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php echo "₹" .format_number_short($total_revenue); ?>

                    </h2>
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

<!-- order status pie chart -->
<div class="col-lg-4 mb-4">
    <div class="card shadow">
        <div class="card-header bg-light">
            <h5 class="mb-0">Order Status Distribution</h5>
        </div>
        <div class="card-body">
            <!-- Legend at the top -->
            <div class="chart-container" style="position: relative; height:40vh; width:100%">
                <canvas id="orderStatusChart"></canvas>
            </div>
        </div>
    </div>
</div>

    <div class="row">
        <!-- Top Performing Products -->
        <div class="col-md-6 mb-4">
        <div class="card shadow">

                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"> Top Performing Products</h5>
                </div>
                <div class="box-body">
                    <div class="table-responsive" style="height: 300px; overflow-y: auto;">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Total Sold</th>
                                    <th>Total Revenue</th>
                                </tr>
                            </thead>
                            <!-- <?php echo $product['p_featured_photo']; ?> -->
                            <tbody>
                                <?php foreach ($top_products as $product): ?>
                                <tr>
                                    <td>
                                        <img src="../assets/uploads/product-featured-161.png" alt="<?php echo $product['p_name']; ?>" style="width: 50px; height: 50px; object-fit: cover;">
                                        <?php echo $product['p_name']; ?>
                                    </td>
                                    <td><?php echo number_format($product['total_sold']); ?></td>
                                    <td>₹<?php echo number_format($product['total_revenue'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seller Performance Dashboard -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">

                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Seller Performance Dashboard</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="height: 300px; overflow-y: auto;">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Seller Name</th>
                                    <th>Total Orders</th>
                                    <th>Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_sellers as $seller): ?>
                                <tr>
                                    <td><?php echo $seller['seller_name']; ?></td>
                                    <td><?php echo number_format($seller['total_orders']); ?></td>
                                    <td>₹<?php echo number_format($seller['total_revenue'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
    <div class="row mx-0">
    <!-- Users Distribution -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-light">
                <h5 class="mb-0">Users Distribution by District</h5>
            </div>
            <div class="card-body" style="height: 400px; overflow-y: auto;">
                <?php
                // Get users distribution by district
                $stmt = $pdo->prepare("
                    SELECT 
                        TRIM(SUBSTRING_INDEX(city, ',', -1)) as district,
                        COUNT(DISTINCT user_id) as user_count,
                        (COUNT(DISTINCT user_id) * 100.0 / (
                            SELECT COUNT(DISTINCT user_id) FROM users_addresses
                        )) as percentage
                    FROM users_addresses
                    WHERE city REGEXP '^[A-Za-z]'
                    GROUP BY district
                    HAVING district != ''
                    ORDER BY user_count DESC
                ");
                $stmt->execute();
                $userDistricts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <div class="district-analytics">
                    <?php foreach ($userDistricts as $district): ?>
                    <div class="district-item mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-medium"><?php echo htmlspecialchars($district['district']); ?></span>
                            <span class="text-muted"><?php echo number_format($district['percentage'], 1); ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" 
                                 role="progressbar" 
                                 style="width: <?php echo $district['percentage']; ?>%" 
                                 aria-valuenow="<?php echo $district['percentage']; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                        <div class="small text-muted mt-1">
                            <?php echo number_format($district['user_count']); ?> users
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sellers Distribution -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-light">
                <h5 class="mb-0">Sellers Distribution by District</h5>
            </div>
            <div class="card-body" style="height: 400px; overflow-y: auto;">
                <?php
                // Get sellers distribution by district
                $stmt = $pdo->prepare("
                    SELECT 
                        TRIM(SUBSTRING_INDEX(seller_city, ',', -1)) as district,
                        COUNT(*) as seller_count,
                        (COUNT(*) * 100.0 / (
                            SELECT COUNT(*) FROM sellers
                        )) as percentage
                    FROM sellers
                    WHERE seller_city REGEXP '^[A-Za-z]'
                    GROUP BY district
                    HAVING district != ''
                    ORDER BY seller_count DESC
                ");
                $stmt->execute();
                $sellerDistricts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>
                
                <div class="district-analytics">
                    <?php foreach ($sellerDistricts as $district): ?>
                    <div class="district-item mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="fw-medium"><?php echo htmlspecialchars($district['district']); ?></span>
                            <span class="text-muted"><?php echo number_format($district['percentage'], 1); ?>%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" 
                                 role="progressbar" 
                                 style="width: <?php echo $district['percentage']; ?>%" 
                                 aria-valuenow="<?php echo $district['percentage']; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                            </div>
                        </div>
                        <div class="small text-muted mt-1">
                            <?php echo number_format($district['seller_count']); ?> sellers
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>

    // order status distribution pie chart
document.addEventListener('DOMContentLoaded', function() {
    var orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');

    var orderLabels = <?php echo json_encode(array_column($order_status_distribution, 'order_status')); ?>;
    var orderData = <?php echo json_encode(array_column($order_status_distribution, 'count')); ?>;
    var colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#8B0000'];

    var orderChart = new Chart(orderStatusCtx, {
        type: 'pie',
        data: {
            labels: orderLabels,
            datasets: [{
                data: orderData,
                backgroundColor: colors
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'  // Legend is now placed at the top
                }
            }
        }
    });
});
</script>

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