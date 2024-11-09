<?php require_once('header.php'); ?>

<section class="content-header">
	<h1>Dashboard</h1>
</section>

<?php
$total_top_category = $conn->query("SELECT * FROM tbl_top_category")->num_rows;
$total_mid_category = $conn->query("SELECT * FROM tbl_mid_category")->num_rows;
$total_end_category = $conn->query("SELECT * FROM tbl_end_category")->num_rows;
$total_product = $conn->query("SELECT * FROM tbl_product")->num_rows;
// $total_customers = $conn->query("SELECT * FROM tbl_customer WHERE seller_status='1'")->num_rows;
// $total_subscriber = $conn->query("SELECT * FROM tbl_subscriber WHERE subs_active='1'")->num_rows;
// $available_shipping = $conn->query("SELECT * FROM tbl_shipping_cost")->num_rows;
// $total_order_completed = $conn->query("SELECT * FROM tbl_payment WHERE payment_status='Completed'")->num_rows;
// $total_shipping_completed = $conn->query("SELECT * FROM tbl_payment WHERE shipping_status='Completed'")->num_rows;
// $total_order_pending = $conn->query("SELECT * FROM tbl_payment WHERE payment_status='Pending'")->num_rows;
// $total_order_complete_shipping_pending = $conn->query("SELECT * FROM tbl_payment WHERE payment_status='Completed' AND shipping_status='Pending'")->num_rows;
?>

<section class="content">
<div class="row">
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-primary">
                <div class="inner">
                  <h3><?php echo $total_product; ?></h3>

                  <p>Products</p>
                </div>
                <div class="icon">
                  <i class="ionicons ion-android-cart"></i>
                </div>
                
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-maroon">
                <div class="inner">
                  <h3><?php echo $total_order_pending; ?></h3>

                  <p>Pending Orders</p>
                </div>
                <div class="icon">
                  <i class="ionicons ion-clipboard"></i>
                </div>
                
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-green">
                <div class="inner">
                  <h3><?php echo $total_order_completed; ?></h3>

                  <p>Completed Orders</p>
                </div>
                <div class="icon">
                  <i class="ionicons ion-android-checkbox-outline"></i>
                </div>
               
              </div>
            </div>
            <!-- ./col -->
            <div class="col-lg-3 col-xs-6">
              <!-- small box -->
              <div class="small-box bg-aqua">
                <div class="inner">
                  <h3><?php echo $total_shipping_completed; ?></h3>

                  <p>Completed Shipping</p>
                </div>
                <div class="icon">
                  <i class="ionicons ion-checkmark-circled"></i>
                </div>
                
              </div>
            </div>
			<!-- ./col -->
			
			<div class="col-lg-3 col-xs-6">
				<!-- small box -->
				<div class="small-box bg-orange">
				  <div class="inner">
					<h3><?php echo $total_order_complete_shipping_pending; ?></h3>
  
					<p>Pending Shippings</p>
				  </div>
				  <div class="icon">
					<i class="ionicons ion-load-a"></i>
				  </div>
				  
				</div>
			  </div>

			  <div class="col-lg-3 col-xs-6">
				<!-- small box -->
				<div class="small-box bg-red">
				  <div class="inner">
					<h3><?php echo $total_customers; ?></h3>
  
					<p>Active Customers</p>
				  </div>
				  <div class="icon">
					<i class="ionicons ion-person-stalker"></i>
				  </div>
				  
				</div>
			  </div>

			  <div class="col-lg-3 col-xs-6">
				<!-- small box -->
				<div class="small-box bg-yellow">
				  <div class="inner">
					<h3><?php echo $total_subscriber; ?></h3>
  
					<p>Subscriber</p>
				  </div>
				  <div class="icon">
					<i class="ionicons ion-person-add"></i>
				  </div>
				  
				</div>
			  </div>

			  <div class="col-lg-3 col-xs-6">
				<!-- small box -->
				<div class="small-box bg-teal">
				  <div class="inner">
					<h3><?php echo $available_shipping; ?></h3>
  
					<p>Available Shippings</p>
				  </div>
				  <div class="icon">
					<i class="ionicons ion-location"></i>
				  </div>
				  
				</div>
			  </div>

			  <div class="col-lg-3 col-xs-6">
				<!-- small box -->
				<div class="small-box bg-olive">
				  <div class="inner">
					<h3><?php echo $total_top_category; ?></h3>
  
					<p>Top Categories</p>
				  </div>
				  <div class="icon">
					<i class="ionicons ion-arrow-up-b"></i>
				  </div>
				  
				</div>
			  </div>

			  <div class="col-lg-3 col-xs-6">
				<!-- small box -->
				<div class="small-box bg-blue">
				  <div class="inner">
					<h3><?php echo $total_mid_category; ?></h3>
  
					<p>Mid Categories</p>
				  </div>
				  <div class="icon">
					<i class="ionicons ion-android-menu"></i>
				  </div>
				  
				</div>
			  </div>

			  <div class="col-lg-3 col-xs-6">
				<!-- small box -->
				<div class="small-box bg-maroon">
				  <div class="inner">
					<h3><?php echo $total_end_category; ?></h3>
  
					<p>End Categories</p>
				  </div>
				  <div class="icon">
					<i class="ionicons ion-arrow-down-b"></i>
				  </div>
				  
				</div>
			  </div>

		  </div>
		  
</section>

<?php require_once('footer.php'); ?>