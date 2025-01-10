<?php require_once('header.php'); ?>

<section class="content-header">
	<h1>Dashboard</h1>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_top_category");
$statement->execute();
$total_top_category = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_mid_category");
$statement->execute();
$total_mid_category = $statement->rowCount();

$statement = $pdo->prepare("SELECT * FROM tbl_end_category");
$statement->execute();
$total_end_category = $statement->rowCount();

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
 <!-- <div class="col-xl-3 col-lg-3">
    <div class="card l-bg-green-dark">
        <div class="card-statistic-3 p-4">
            <div class="card-icon card-icon-large"><i class="fas fa-ticket-alt"></i></div>
            <div class="mb-4">
                <h5 class="card-title mb-0">Ticket Resolved</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-8">
                    <h2 class="d-flex align-items-center mb-0">
                        578
                    </h2>
                </div>
                <div class="col-4 text-right">
                    <span>10% <i class="fa fa-arrow-up"></i></span>
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
            <div class="card-icon card-icon-large"><i class="fas fa-dollar-sign"></i></div>
            <div class="mb-4">
                <h5 class="card-title mb-0">Revenue Today</h5>
            </div>
            <div class="row align-items-center mb-2 d-flex">
                <div class="col-8">
                    <h2 class="d-flex align-items-center mb-0">
                        $11.61k
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
</div> -->
    </div>
              
            </div>
            
		  
</section>

<?php require_once('footer.php'); ?>