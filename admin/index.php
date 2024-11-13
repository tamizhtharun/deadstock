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
<div class="row">
<div class="g-6 mb-6">
  <div class="col-xl-3 col-sm-6 col-12">
    <div class="card shadow border-0">
      <div class="card-body">
        <div class="row">
          <div class="col">
            <span class="card-title h4 font-semibold text-muted text-lg d-block mb-2">Budget</span>
            <span class="h1 font-bold mb-0">$750.90</span>
          </div>
          <div class="col-auto">
            <div class="icon icon-shape bg-tertiary text-white text-2xl rounded-circle">
              <i class="bi bi-credit-card text-2xl"></i>
            </div>
          </div>
        </div>
        <div class="card-lower mt-2 mb-0 text-lg">
          <span class="badge badge-pill bg-soft-success text-success me-2">
            <i class="bi bi-arrow-up me-1 text-lg"></i>13%
          </span>
          <span class="text-nowrap text-base text-muted">Since last month</span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 col-12">
    <div class="card shadow border-0">
      <div class="card-body">
        <div class="row">
          <div class="col">
            <span class="card-title h4 font-semibold text-muted text-lg d-block mb-2">New projects</span>
            <span class="h1 font-bold mb-0">215</span>
          </div>
          <div class="col-auto">
            <div class="icon icon-shape bg-primary text-white text-2xl rounded-circle">
              <i class="bi bi-people text-2xl"></i>
            </div>
          </div>
        </div>
        <div class="card-lower mt-2 mb-0 text-lg">
          <span class="badge badge-pill bg-soft-success text-success me-2">
            <i class="bi bi-arrow-up me-1 text-lg"></i>30%
          </span>
          <span class="text-nowrap text-base text-muted">Since last month</span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 col-12">
    <div class="card shadow border-0">
      <div class="card-body">
        <div class="row">
          <div class="col">
            <span class="card-title h4 font-semibold text-muted text-lg d-block mb-2">Total hours</span>
            <span class="h1 font-bold mb-0">1.400</span>
          </div>
          <div class="col-auto">
            <div class="icon icon-shape bg-info text-white text-2xl rounded-circle">
              <i class="bi bi-clock-history text-2xl"></i>
            </div>
          </div>
        </div>
        <div class="card-lower mt-2 mb-0 text-lg">
          <span class="badge badge-pill bg-soft-danger text-danger me-2">
            <i class="bi bi-arrow-down me-1 text-lg"></i>-5%
          </span>
          <span class="text-nowrap text-base text-muted">Since last month</span>
        </div>
      </div>
    </div>
  </div>
  <div class="col-xl-3 col-sm-6 col-12">
    <div class="card shadow border-0">
      <div class="card-body">
        <div class="row">
          <div class="col">
            <span class="card-title h4 font-semibold text-muted text-lg d-block mb-2">Work load</span>
            <span class="h1 font-bold mb-0">95%</span>
          </div>
          <div class="col-auto">
            <div class="icon icon-shape bg-warning text-white text-2xl rounded-circle">
              <i class="bi bi-minecart-loaded text-2xl"></i>
            </div>
          </div>
        </div>
        <div class="card-lower mt-2 mb-0 text-lg">
          <span class="badge badge-pill bg-soft-success text-success me-2">
            <i class="bi bi-arrow-up me-1 text-lg"></i>10%
          </span>
          <span class="text-nowrap text-base text-muted">Since last month</span>
        </div>
      </div>
    </div>
  </div>
</div>
              
            </div>
            
		  
</section>

<?php require_once('footer.php'); ?>