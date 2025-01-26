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
                <h5 class="card-title mb-0">Orders</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-8">
                    <h2 class="d-flex align-items-center mb-0">
                        <?php 
                        // Fetch total number of orders
                        $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_orders");
                        $statement->execute();
                        $total_orders = $statement->fetchColumn();

                        // Fetch non-canceled orders
                        $statement = $pdo->prepare("SELECT COUNT(*) FROM tbl_orders WHERE order_status != 'cancelled'");
                        $statement->execute();
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
                        // Calculate today's bids
                        $statement = $pdo->prepare("
                            SELECT COUNT(*) AS total_bids 
                            FROM bidding 
                            WHERE DATE(bid_time) = CURDATE()
                        ");
                        $statement->execute();
                        $today_bids = $statement->fetchColumn();
                        $today_bids = $today_bids ? $today_bids : 0; // Handle null values

                        echo $today_bids;
                        ?>
                    </h2>
                </div>
                <div class="col-4 text-right">
                    <?php
                    // Calculate percentage change compared to yesterday's bids
                    $statement = $pdo->prepare("
                        SELECT COUNT(*) AS total_bids 
                        FROM bidding 
                        WHERE DATE(bid_time) = CURDATE() - INTERVAL 1 DAY
                    ");
                    $statement->execute();
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
                <h5 class="card-title mb-0">Revenue</h5>
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

                        // Calculate current total revenue (e.g., current month)
                        $statement = $pdo->prepare("
                            SELECT SUM(price * quantity) AS total_revenue 
                            FROM tbl_orders 
                            WHERE order_status != 'cancelled' AND MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())
                        ");
                        $statement->execute();
                        $current_revenue = $statement->fetchColumn();
                        $current_revenue = $current_revenue ? $current_revenue : 0; // Handle null values

                        // Calculate previous month's revenue
                        $statement = $pdo->prepare("
                            SELECT SUM(price * quantity) AS total_revenue 
                            FROM tbl_orders 
                            WHERE order_status != 'cancelled' AND MONTH(created_at) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND YEAR(created_at) = YEAR(CURRENT_DATE())
                        ");
                        $statement->execute();
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
                        $percentage_change = $current_revenue > 0 ? 100 : 0; // If no previous revenue, assume 100% increase
                    }
                    ?>
                    <span><?php echo number_format($percentage_change, 1); ?>% 
                        <?php echo $percentage_change >= 0 ? '<i class="fa fa-arrow-up"></i>' : '<i class="fa fa-arrow-down"></i>'; ?>
                    </span>
                </div>
            </div>
            <div class="progress mt-1 " data-height="8" style="height: 8px;">
                <?php
                // Calculate progress percentage (e.g., based on a target or maximum revenue)
                $progress_percentage = $current_revenue > 0 ? min(($current_revenue / 10000) * 100, 100) : 0; // Assuming 10,000 as a target
                ?>
                <div class="progress-bar l-bg-cyan" role="progressbar" aria-valuenow="<?php echo $progress_percentage; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $progress_percentage; ?>%;"></div>
            </div>
        </div>
    </div>
</div>




    <!-- Graph -->
  
<div class="container-fluid py-4 px-1">
    <div class="row mx-0 d-flex">
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Sales Analytics</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="position: relative; height:40vh; width:100%">
                        <canvas id="myChart"></canvas>
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

<style>
    /* .chart-container {
        width: 100% !important;
    } */
    .card {
        max-width: none !important;
    }
</style>
              
            
		  
</section>

<?php require_once('footer.php'); ?>