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

// Calculate total revenue for the seller excluding canceled orders
$statement = $pdo->prepare("
    SELECT SUM(price * quantity) AS total_revenue 
    FROM tbl_orders 
    WHERE order_status != 'cancelled' 
      AND seller_id = ?
");
$statement->execute([$seller_id]);
$total_revenue = $statement->fetchColumn();
$total_revenue = $total_revenue ? $total_revenue : 0; // Handle null values

// Calculate today's total orders for the seller, excluding canceled orders
// Initialize variables to prevent errors
$todays_orders = 0;
$last_week_orders = 0;
$previous_month_orders = 0;

// Fetch today's total orders for the seller (excluding canceled orders)
$statement = $pdo->prepare("
    SELECT COUNT(*) AS total_orders 
    FROM tbl_orders 
    WHERE order_status != 'cancelled' 
      AND DATE(created_at) = CURDATE() 
      AND seller_id = ?
");
$statement->execute([$seller_id]);
$todays_orders = $statement->fetchColumn() ?? 0;

// Fetch last 7 days' total orders (excluding today)
$last_7_days_start = date('Y-m-d', strtotime('-7 days')); // 7 days ago from today
$last_7_days_end = date('Y-m-d', strtotime('-1 day')); // Yesterday

$statement = $pdo->prepare("
    SELECT COUNT(*) AS total_orders 
    FROM tbl_orders 
    WHERE order_status != 'cancelled' 
      AND DATE(created_at) BETWEEN ? AND ? 
      AND seller_id = ?
");
$statement->execute([$last_7_days_start, $last_7_days_end, $seller_id]);
$last_week_orders = $statement->fetchColumn() ?? 0;

// Fetch previous month's total orders (handle 28, 29, 30, 31 days correctly)
$previous_month_start = date('Y-m-01', strtotime('first day of last month')); // 1st day of last month
$previous_month_end = date('Y-m-t', strtotime('last day of last month')); // Last day of last month

$statement = $pdo->prepare("
    SELECT COUNT(*) AS total_orders 
    FROM tbl_orders 
    WHERE order_status != 'cancelled' 
      AND DATE(created_at) BETWEEN ? AND ? 
      AND seller_id = ?
");
$statement->execute([$previous_month_start, $previous_month_end, $seller_id]);
$previous_month_orders = $statement->fetchColumn() ?? 0;

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
$total_bids = $statement->fetchColumn()?: 0;


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
$today_direct_buys = $statement->fetchColumn() ?: 0;

// Fetch order status distribution
$statement = $pdo->prepare("
    SELECT order_status, COUNT(*) as count
    FROM tbl_orders
    WHERE seller_id = ?
    GROUP BY order_status
");
$statement->execute([$seller_id]);
$order_status_distribution = $statement->fetchAll(PDO::FETCH_ASSOC);

// Fetch top products by revenue
$statement = $pdo->prepare("
    SELECT p.p_name, SUM(o.price * o.quantity) as total_revenue, COUNT(DISTINCT o.id) as order_count
    FROM tbl_product p
    JOIN tbl_orders o ON p.id = o.product_id
    WHERE p.seller_id = ? AND o.order_status != 'cancelled'
    GROUP BY p.id
    ORDER BY total_revenue DESC
    LIMIT 10
");
$statement->execute([$seller_id]);
$top_products_by_revenue = $statement->fetchAll(PDO::FETCH_ASSOC);

// Fetch low stock products
$statement = $pdo->prepare("
    SELECT p_name, p_qty
    FROM tbl_product
    WHERE seller_id = ? AND p_qty <= 5
    ORDER BY p_qty ASC
    LIMIT 5
");
$statement->execute([$seller_id]);
$low_stock_products = $statement->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent orders
$statement = $pdo->prepare("
    SELECT o.id, o.order_status, o.price * o.quantity as total_amount, o.created_at, p.p_name
    FROM tbl_orders o
    JOIN tbl_product p ON o.product_id = p.id
    WHERE o.seller_id = ?
    ORDER BY o.created_at DESC
    LIMIT 5
");
$statement->execute([$seller_id]);
$recent_orders = $statement->fetchAll(PDO::FETCH_ASSOC);

// Fetch product category summary
$statement = $pdo->prepare("
    SELECT t.tcat_name as category_name, COUNT(p.id) as product_count
    FROM tbl_product p
    JOIN tbl_end_category e ON p.ecat_id = e.ecat_id
    JOIN tbl_mid_category m ON e.mcat_id = m.mcat_id
    JOIN tbl_top_category t ON m.tcat_id = t.tcat_id
    WHERE p.seller_id = ?
    GROUP BY t.tcat_id
    ORDER BY product_count DESC
    LIMIT 5
");
$statement->execute([$seller_id]);
$category_summary = $statement->fetchAll(PDO::FETCH_ASSOC);

// Fetch monthly sales data for the current year
$statement = $pdo->prepare("
    SELECT DATE_FORMAT(created_at, '%Y-%m') as month, SUM(price * quantity) as monthly_revenue
    FROM tbl_orders
    WHERE seller_id = ? AND order_status != 'cancelled' AND YEAR(created_at) = YEAR(CURDATE())
    GROUP BY month
    ORDER BY month
");
$statement->execute([$seller_id]);
$monthly_sales_data = $statement->fetchAll(PDO::FETCH_ASSOC);
// Fetch recent bids
$statement = $pdo->prepare("
    SELECT b.bid_id, p.p_name, b.bid_quantity, (b.bid_price * b.bid_quantity) AS total_price, 
           b.bid_status, DATE(b.bid_time) AS bid_date
    FROM bidding b
    JOIN tbl_product p ON b.product_id = p.id
    WHERE p.seller_id = ?
    ORDER BY b.bid_time DESC
    LIMIT 10
");
$statement->execute([$seller_id]);
$recent_bids = $statement->fetchAll(PDO::FETCH_ASSOC);

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
<style>
    .card-body div {
        scrollbar-width: thin; /* For Firefox */
    }

    /* For WebKit-based browsers (Chrome, Edge, Safari) */
    .card-body div::-webkit-scrollbar {
        width: 6px; /* Adjust width */
    }

    .card-body div::-webkit-scrollbar-thumb {
        background: #ccc; /* Scrollbar thumb color */
        border-radius: 10px; /* Rounded corners */
    }

    .card-body div::-webkit-scrollbar-track {
        background: #f1f1f1; /* Scrollbar track color */
    }
    .hidden-orders {
    display: none;
    transition: all 0.3s ease-in-out;
}

</style>

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
                <div class="col-7" style="padding-left: 20px;">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php
                        $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_product WHERE p_is_approve=1 AND seller_id=?");
                        $statement->execute([$seller_id]);
                        $total_approved_product = $statement->fetchColumn();
                        echo $total_approved_product;
                        ?>
                    </h2> Products
                </div>
                <?php
                $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_product WHERE seller_id=?");
                $statement->execute([$seller_id]);
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
            <div class="progress mt-1" data-height="8" style="height: 8px; position: relative;">
                <!-- Uploaded Products Progress (Red) with Tooltip -->
                <div class="progress-bar bg-danger uploaded-bar" role="progressbar"
                    style="width: <?php echo $percentage_uploaded; ?>%;" 
                    aria-valuenow="<?php echo $percentage_uploaded; ?>" 
                    aria-valuemin="0" 
                    aria-valuemax="100"
                    data-toggle="tooltip"
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

<!-- Enable Bootstrap Tooltip with Proper Hover Handling -->
<script>
    $(document).ready(function(){
        $('.uploaded-bar').tooltip({
            trigger: 'hover',
            placement: 'top',
            container: 'body'
        });
    });
</script>



<div class="col-xl-3 col-lg-3">
    <div class="card l-bg-blue-dark">
        <div class="card-statistic-3 p-3">
            <div class="card-icon card-icon-large"><i class="fas fa-box"></i></div>
            <div class="mb-6">
                <h5 class="card-title mb-0">Total Orders</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-7" style="padding-left: 20px;">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php 
                        // Fetch total orders
                        $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_orders WHERE seller_id = ?");
                        $statement->execute([$seller_id]);
                        $total_orders = $statement->fetchColumn();

                        echo $total_orders; // Display total orders
                        ?>
                    </h2> Orders
                </div>
                <div class="col-4 text-right">
                    <span>100% <i class="fa fa-check"></i></span>
                </div>
            </div>
            <div class="progress mt-1" data-height="8" style="height: 8px;">
                <?php
                // Define max expected orders for scaling (adjust as needed)
                $max_orders = 100;

                // Ensure progress bar is 0% if no orders exist
                $progress_width = ($total_orders > 0) ? min(100, ($total_orders / $max_orders) * 100) : 0;
                ?>
                <div class="progress-bar l-bg-green" role="progressbar" 
                    aria-valuenow="<?php echo $progress_width; ?>" 
                    aria-valuemin="0" 
                    aria-valuemax="100"
                    style="width: <?php echo $progress_width; ?>%;">
                </div>
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
                <div class="col-7" style="padding-left: 20px;">
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
                <h5 class="card-title mb-0">Today's Revenue</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-7" style="padding-left: 20px;">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php 
                        // Function to format numbers into shorter format
                        function format_number_short($n) {
                            if ($n >= 1000000) {
                                return round($n / 1000000, 1) . 'M'; // Millions
                            } elseif ($n >= 1000) {
                                return round($n / 1000, 1) . 'K'; // Thousands
                            }
                            return $n; // Less than 1K
                        }

                        // Get today's and yesterday's date
                        $today_date = date('Y-m-d');
                        $yesterday_date = date('Y-m-d', strtotime('-1 day'));

                        // Fetch today's total revenue for the seller
                        $statement = $pdo->prepare("
                            SELECT SUM(price * quantity) AS total_revenue 
                            FROM tbl_orders 
                            WHERE order_status != 'cancelled' 
                              AND DATE(created_at) = :today_date
                              AND seller_id = :seller_id
                        ");
                        $statement->bindParam(':today_date', $today_date, PDO::PARAM_STR);
                        $statement->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
                        $statement->execute();
                        $current_revenue = $statement->fetchColumn() ?? 0; // Handle null values

                        // Fetch yesterday's total revenue for the seller
                        $statement = $pdo->prepare("
                            SELECT SUM(price * quantity) AS total_revenue 
                            FROM tbl_orders 
                            WHERE order_status != 'cancelled' 
                              AND DATE(created_at) = :yesterday_date
                              AND seller_id = :seller_id
                        ");
                        $statement->bindParam(':yesterday_date', $yesterday_date, PDO::PARAM_STR);
                        $statement->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
                        $statement->execute();
                        $previous_revenue = $statement->fetchColumn() ?? 0; // Handle null values

                        echo "₹" . format_number_short($current_revenue);
                        ?>
                    </h2>
                </div>
                <div class="col-4 text-right">
                    <?php
                    // Calculate percentage change but restrict it within -100% to +100%
                    if ($previous_revenue > 0) {
                        $percentage_change = (($current_revenue - $previous_revenue) / $previous_revenue) * 100;
                    } else {
                        $percentage_change = ($current_revenue > 0) ? 100 : 0; // Assume 100% if no revenue yesterday
                    }

                    // Restrict the percentage strictly within -100% to 100%
                    if ($percentage_change > 100) {
                        $percentage_change = 100;
                    } elseif ($percentage_change < -100) {
                        $percentage_change = -100;
                    }

                    // Display percentage change with color-coded arrow
                    if ($percentage_change > 0) {
                        echo '<span style="color: green;">' . number_format($percentage_change, 1) . '% <i class="fa fa-arrow-up"></i></span>';
                    } elseif ($percentage_change < 0) {
                        echo '<span style="color: red;">' . number_format(abs($percentage_change), 1) . '% <i class="fa fa-arrow-down"></i></span>';
                    } else {
                        echo '<span>0% <i class="fa fa-minus"></i></span>';
                    }
                    ?>
                </div>
            </div>

            <!-- Progress Bar -->
            <?php
            // Set max revenue target (e.g., ₹100,000)
            $max_revenue = 100000;

            // Calculate progress percentage (ensure no increase if revenue is 0)
            $progress_percentage = ($current_revenue > 0) ? min(100, ($current_revenue / $max_revenue) * 100) : 0;
            ?>
            <div class="progress mt-1" data-height="8" style="height: 8px;">
                <div class="progress-bar l-bg-yellow" role="progressbar" 
                    aria-valuenow="<?php echo $progress_percentage; ?>" 
                    aria-valuemin="0" 
                    aria-valuemax="100"
                    style="width: <?php echo $progress_percentage; ?>%;">
                </div>
            </div>
        </div>
    </div>
</div>



  <div class="col-xl-3 col-lg-3">
    <div class="card l-bg-blue-dark">
        <div class="card-statistic-3 p-4">
            <div class="card-icon card-icon-large"><i class="fas fa-rupee-sign"></i></div>
            <div class="mb-4">
                <h5 class="card-title mb-0">Total Revenue</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-8">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php echo "₹" . format_number_short($total_revenue, 2); ?>
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-xl-3 col-lg-3">
    <div class="card l-bg-orange-dark" data-bs-toggle="tooltip" 
         data-bs-html="true"
         title="Last Week Orders: <?php echo $last_week_orders; ?><br>Previous Month Orders: <?php echo $previous_month_orders; ?>">
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
<!-- Include Bootstrap JS to Activate Tooltips -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>


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
                    <h5 class="mb-0">Order Status Distribution</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:40vh; width:100%">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
<div class="row">
    <!-- Top Products by Revenue -->
    <div class="col-xl-8 col-lg-8 mb-4">
        <div class="card shadow">
            <div class="card-header bg-light">
                <h5 class="mb-0">Top Products by Revenue</h5>
            </div>
            <div class="card-body">
                <div style="height: 300px; overflow-y: auto;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Total Revenue</th>
                                <th>Orders</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_products_by_revenue as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['p_name']); ?></td>
                                <td>₹<?php echo number_format($product['total_revenue'], 2); ?></td>
                                <td><?php echo number_format($product['order_count']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Products -->
    <div class="col-xl-4 col-lg-4 mb-4">
        <div class="card shadow">
            <div class="card-header bg-light">
                <h5 class="mb-0">Low Stock Products</h5>
            </div>
            <div class="card-body">
                <div style="height: 300px; overflow-y: auto;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($low_stock_products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['p_name']); ?></td>
                                <td><?php echo $product['p_qty']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Orders -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-light">
                <h5 class="mb-0">Recent Orders</h5>
            </div>
            <div class="card-body">
                <div style="height: 300px; overflow-y: auto;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['p_name']); ?></td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td><?php echo $order['order_status']; ?></td>
                                <td><?php echo date('Y-m-d', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Category Summary -->
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-light">
                <h5 class="mb-0">Product Category Summary</h5>
            </div>
            <div class="card-body">
                <canvas id="categorySummaryChart" style="height: 300px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Order Status Summary -->
<div class="row">
    <div class="col-xl-6 col-lg-6 mb-4">
        <div class="card shadow">
            <div class="card-header bg-light">
                <h5 class="mb-0">Order Status Summary</h5>
            </div>
            <div class="card-body">
                <div style="height: 300px; overflow-y: auto;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total_orders = 0;
                            foreach ($order_status_distribution as $status) {
                                $total_orders += $status['count']; // Summing order counts properly
                            }

                            foreach ($order_status_distribution as $status) {
                                $count = $status['count'];
                                $status_name = ucfirst($status['order_status']);
                                $percentage = ($total_orders > 0) ? ($count / $total_orders) * 100 : 0;
                                echo "<tr>
                                        <td>{$status_name}</td>
                                        <td>{$count}</td>
                                        <td>" . number_format($percentage, 2) . " %</td>
                                      </tr>";
                            }
                            ?>
                            <tr class="font-weight-bold">
                                <td>Total</td>
                                <td><?php echo $total_orders; ?></td>
                                <td>100%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6 col-lg-6 mb-4">
    <div class="card shadow">
        <div class="card-header bg-light">
            <h5 class="mb-0">Recent Bids</h5>
        </div>
        <div class="card-body">
            <div style="height: 300px; overflow-y: auto;">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_bids as $bid): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($bid['p_name']); ?></td>
                            <td><?php echo number_format($bid['bid_quantity']); ?></td>
                            <td>₹<?php echo number_format($bid['total_price'], 2); ?></td>
                            <td>
                                <?php 
                                    $status_text = '';
                                    switch ($bid['bid_status']) {
                                        case 1: $status_text = 'Pending'; break;
                                        case 2: $status_text = 'Approved'; break;
                                        case 3: $status_text = 'Rejected'; break;
                                        default: $status_text = 'Unknown';
                                    }
                                    echo $status_text;
                                ?>
                            </td>
                            <td><?php echo $bid['bid_date']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Order Status Distribution Chart
    var orderStatusCtx = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(orderStatusCtx, {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($order_status_distribution, 'order_status')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($order_status_distribution, 'count')); ?>,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            title: {
                display: true,
                text: 'Order Status Distribution'
            }
        }
    })
});
document.addEventListener('DOMContentLoaded', function() {
    // Existing chart code remains the same

    // Product Category Summary Chart
    var categoryCtx = document.getElementById('categorySummaryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($category_summary, 'category_name')); ?>,
            datasets: [{
                label: 'Number of Products',
                data: <?php echo json_encode(array_column($category_summary, 'product_count')); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Number of Products'
                    }
                }
            }
        }
    });
});
</script>

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