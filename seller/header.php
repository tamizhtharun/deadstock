<?php
ob_start();
session_start();
include("../db_connection.php");
$error_message = '';
$success_message = '';
$error_message1 = '';
$success_message1 = '';


// echo "<pre>";
// print_r($_SESSION['seller_session']); // Display all session variables
// echo "</pre>";

// Check if the seller is logged in or not
if (!isset($_SESSION['seller_session'])) {
	header('location: ../index.php');
	exit;
} else {
	// If customer is logged in, but admin make him inactive, then force logout this user.
	$stmt = $conn->prepare("SELECT * FROM sellers WHERE seller_id=? AND seller_status=?");

	// Bind parameters
	$seller_id = $_SESSION['seller_session']['seller_id'];
	$seller_status = 2;
	$stmt->bind_param("ii", $seller_id, $seller_status);

	// Execute the statement
	$stmt->execute();

	// Get the result
	$result = $stmt->get_result();
	$total = $result->num_rows;

	// Check if any rows were returned
	if ($total) {
		header('location: logout.php');
		exit;
	}

	// Close the statement and connection
	$stmt->close();
	// $conn->close();
}

?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Seller Panel</title>

	<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
	<link rel="icon" href="../icons\dead stock.png" type="image/x-icon">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
	<link rel="stylesheet" href="css/bootstrap.min.css">
	<link rel="stylesheet" href="css/font-awesome.min.css">
	<link rel="stylesheet" href="css/ionicons.min.css">
	<link rel="stylesheet" href="css/datepicker3.css">
	<link rel="stylesheet" href="css/all.css">
	<link rel="stylesheet" href="css/select2.min.css">
	<link rel="stylesheet" href="css/dataTables.bootstrap.css">
	<link rel="stylesheet" href="css/jquery.fancybox.css">
	<link rel="stylesheet" href="css/AdminLTE.min.css">
	<link rel="stylesheet" href="css/_all-skins.min.css">
	<link rel="stylesheet" href="css/on-off-switch.css" />
	<link rel="stylesheet" href="css/summernote.css">
	<link rel="stylesheet" href="style.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.24/css/jquery.dataTables.css">
	<script type="text/javascript" src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.js"></script>


</head>

<body class="hold-transition fixed skin-blue sidebar-mini">

	<div class="wrapper">

		<header class="main-header">

			<a href="index.php" class="logo">
				<span class="logo-lg">DeadStock</span>
			</a>

			<nav class="navbar navbar-static-top d-flex justify-content-between align-items-center">
				<div class="d-flex align-items-center profile-type">
					<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
						<span class="sr-only">Toggle navigation</span>
					</a>
					<span style="line-height:50px;color:#fff;padding-left:15px;font-size:18px; font-weight:800;">Seller
						Panel</span>
				</div>

				<!-- Alert Message -->
				<div id="message"
					style="display:none; position: fixed; top: 10px; left: 50%; transform: translateX(-50%); z-index: 1000; padding: 10px; border-radius: 5px; color: #fff; font-size: 14px;">
				</div>
				<!-- Alert Message end -->

				<!-- Top Bar ... User Information .. Login/Log out Area -->
				<div class="navbar-custom-menu">
    <div class="dropdown profile-dropdown">
        <button class="btn d-flex align-items-center profile-hover" 
                type="button" 
                id="newProfileDropdown" 
                data-bs-toggle="dropdown" 
                aria-expanded="false" 
                style="background:none; border:none; padding:0;">
            <?php
            // Check if the profile photo exists; if not, use the Font Awesome user icon
            if (!empty($_SESSION['seller_session']['seller_photo'])) {
                $profile_photo = $_SESSION['seller_session']['seller_photo'];
                echo '<img src="../assets/uploads/profile-pictures/' . $profile_photo . '" style="width:35px; height:35px; border-radius:50%; margin-right:8px;">';
            } else {
                echo '<i class="fa fa-user" style="font-size:20px; margin-right:8px;"></i>';
            }
            ?>
            <span style="font-weight:800; margin-right:6px;">
                <?php echo $_SESSION['seller_session']['seller_name']; ?>
            </span>
            <i class="fa fa-chevron-down"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="newProfileDropdown">
            <li><a class="dropdown-item" href="profile-edit.php">Edit Profile</a></li>
            <li><a class="dropdown-item text-danger" href="logout.php">Log out</a></li>
        </ul>
    </div>
</div>

			</nav>
		</header>

		<?php $cur_page = substr($_SERVER["SCRIPT_NAME"], strrpos($_SERVER["SCRIPT_NAME"], "/") + 1); ?>
		<!-- Side Bar to Manage Shop Activities -->
		<aside class="main-sidebar">
			<section class="sidebar">

				<ul class="sidebar-menu">

					<li class="treeview <?php if ($cur_page == 'index.php') {
											echo 'active';
										} ?>">
						<a href="index.php">
							<i class="fa fa-dashboard"></i> <span>Dashboard</span>
						</a>
					</li>


					<!-- <li class="treeview <?php if (($cur_page == 'settings.php')) {
													echo 'active';
												} ?>">
								<a href="settings.php">
									<i class="fa fa-sliders"></i> <span>Website Settings</span>
								</a>
								</li> -->

					<li
						class="treeview <?php if (($cur_page == 'size.php') || ($cur_page == 'size-add.php') || ($cur_page == 'size-edit.php') || ($cur_page == 'color.php') || ($cur_page == 'color-add.php') || ($cur_page == 'color-edit.php') || ($cur_page == 'country.php') || ($cur_page == 'country-add.php') || ($cur_page == 'country-edit.php') || ($cur_page == 'shipping-cost.php') || ($cur_page == 'shipping-cost-edit.php') || ($cur_page == 'top-category.php') || ($cur_page == 'top-category-add.php') || ($cur_page == 'top-category-edit.php') || ($cur_page == 'mid-category.php') || ($cur_page == 'mid-category-add.php') || ($cur_page == 'mid-category-edit.php') || ($cur_page == 'end-category.php') || ($cur_page == 'end-category-add.php') || ($cur_page == 'end-category-edit.php')) {
											echo 'active';
										} ?>">
						<!-- <a href="#">
										<i class="fa fa-cogs"></i>
										<span>Shop Settings</span>
										<span class="pull-right-container">
											<i class="fa fa-angle-left pull-right"></i>
										</span>
									</a> -->
						<ul class="treeview-menu">

							<li><a href="country.php"><i class="fa fa-circle-o"></i> Country</a></li>
							<li><a href="shipping-cost.php"><i class="fa fa-circle-o"></i> Shipping Cost</a></li>
							<li><a href="top-category.php"><i class="fa fa-circle-o"></i> Top Level Category</a></li>
							<li><a href="mid-category.php"><i class="fa fa-circle-o"></i> Mid Level Category</a></li>
							<li><a href="end-category.php"><i class="fa fa-circle-o"></i> End Level Category</a></li>
						</ul>
					</li>


					<!-- <li class="treeview <?php if (in_array($cur_page, ['warehouse-management.php', 'list-warehouses.php', 'edit-warehouse.php'])) { echo 'active menu-open'; } ?>">
							<a href="#">
								<i class="fa fa-warehouse"></i><span>Warehouse Management</span>
								<span class="pull-right-container">
									<i class="fa fa-angle-left pull-right"></i>
								</span>
							</a>
							<ul class="treeview-menu">
								<li class="<?php echo ($cur_page == 'warehouse-management.php') ? 'active' : ''; ?>">
									<a href="warehouse-management.php"><i class="fa fa-plus-circle"></i> Create New Warehouse</a>
								</li>
								<li class="<?php echo (in_array($cur_page, ['list-warehouses.php', 'edit-warehouse.php'])) ? 'active' : ''; ?>">
									<a href="list-warehouses.php"><i class="fa fa-edit"></i> Manage Warehouses</a>
								</li>
							</ul>
						</li> -->

					<li
						class="treeview <?php if (($cur_page == 'product.php') || ($cur_page == 'product-add.php') || ($cur_page == 'product-edit.php')) {
											echo 'active';
										} ?>">
						<a href="product.php">
							<i class="fa fa-shopping-bag"></i> <span>Product Management</span>
						</a>
					</li>


					<!-- <li class="treeview <?php if (($cur_page == 'order.php')) {
													echo 'active';
												} ?>">
									<a href="order.php">
										<i class="fa fa-sticky-note"></i> <span>Order Management</span>
									</a>
								</li> -->



					<li class="treeview <?php if (($cur_page == 'bidding.php')) {
											echo 'active';
										} ?>">
						<a href="bidding.php">
							<i class="fa fa-gavel"></i> <span>Bid Management</span>
						</a>
					</li>
					<!-- Icons to be displayed on Shop -->


					<!-- <li class="treeview <?php if (($cur_page == 'service.php')) {
													echo 'active';
												} ?>">
									<a href="#">
										<i class="fa fa-list-ol"></i>
										<span>Services</span>
										<span class="pull-right-container">
											<i class="fa fa-angle-left pull-right"></i>
										</span>
									</a>
									<ul class="treeview-menu">

										<li><a href="#"><i class="fa fa-circle-o"></i> Scrolling Text</a></li>
										<li><a href="#"><i class="fa fa-circle-o"></i> Quote</a></li>
										<li><a href="#"><i class="fa fa-circle-o"></i> Category</a></li>
									</ul>
								</li> -->

					<li class="treeview <?php if ($cur_page == 'bids_history.php') echo 'active'; ?>">
						<a href="bids_history.php">
							<i class="nav-icon fas fa-history"></i>
							<span>All Bids History</span>
						</a>
					</li>

					<li class="treeview <?php if (($cur_page == 'bidding-order.php') || ($cur_page == 'direct-order.php')) {
											echo 'active';
										} ?>">
						<a href="#">
							<i class="nav-icon fas fa-shopping-cart"></i>
							<span>Order Management</span>
							<span class="pull-right-container">
								<i class="fa fa-angle-left pull-right"></i>
							</span>
						</a>
						<ul class="treeview-menu">
							<li><a href="direct-order.php"><i class="fa fa-circle-o"></i> Direct Orders</a></li>
							<li><a href="bidding-order.php"><i class="fa fa-circle-o"></i> Bid-Based Orders </a></li>
							<li><a href="order-history.php"><i class="fa fa-circle-o"></i> All Orders History </a></li>
						</ul>
					</li>

					<li
						class="treeview <?php if (($cur_page == 'discount.php')) {
											echo 'active';
										} ?>">
						<a href="discount.php">
							<i class="fa fa-percent"></i><span>Discount Management</span>
						</a>
					</li>

					<li class="treeview <?php if (($cur_page == 'revenue.php')) {
											echo 'active';
										} ?>">
						<a href="revenue.php">
							<i class="fas fa-indian-rupee-sign"></i> <span>Revenue Details</span>
						</a>
					</li>



					<!-- 
								<li class="treeview <?php if (($cur_page == 'slider.php')) {
														echo 'active';
													} ?>">
								<a href="slider.php">
									<i class="fa fa-picture-o"></i> <span>Manage Sliders</span>
								</a>
								</li> -->
					<!-- Icons to be displayed on Shop -->


					<!-- <li class="treeview <?php if (($cur_page == 'service.php')) {
													echo 'active';
												} ?>">
									<a href="#">
										<i class="fa fa-list-ol"></i>
										<span>Services</span>
										<span class="pull-right-container">
											<i class="fa fa-angle-left pull-right"></i>
										</span>
									</a>
									<ul class="treeview-menu">

										<li><a href="#"><i class="fa fa-circle-o"></i> Scrolling Text</a></li>
										<li><a href="#"><i class="fa fa-circle-o"></i> Quote</a></li>
										<li><a href="#"><i class="fa fa-circle-o"></i> Category</a></li>
									</ul>
								</li> -->

					<li class="treeview <?php if (($cur_page == 'faq.php')) {
											echo 'active';
										} ?>">
						<a href="faq.php">
							<i class="fa fa-question-circle"></i> <span>FAQ</span>
						</a>
					</li>

					<!-- <li class="treeview <?php if (($cur_page == 'seller.php') || ($cur_page == 'seller-add.php') || ($cur_page == 'seller-edit.php')) {
													echo 'active';
												} ?>">
												<a href="seller.php">
													<i class="fa fa-user-plus"></i> <span>Registered Seller</span>
												</a>
										</li>
										
										<li class="treeview <?php if (($cur_page == 'customer.php') || ($cur_page == 'customer-add.php') || ($cur_page == 'customer-edit.php')) {
																echo 'active';
															} ?>">
												<a href="customer.php">
													<i class="fa fa-user-plus"></i> <span>Customer</span>
												</a>
										</li>
								<li class="treeview <?php if (($cur_page == 'page.php')) {
														echo 'active';
													} ?>">
								<a href="page.php">
									<i class="fa fa-tasks"></i> <span>Page Settings</span>
								</a>
								</li> -->

					<!-- <li class="treeview <?php if (($cur_page == 'social-media.php')) {
													echo 'active';
												} ?>">
								<a href="social-media.php">
									<i class="fa fa-globe"></i> <span>Social Media</span>
								</a>
								</li>

								<li class="treeview <?php if (($cur_page == 'subscriber.php') || ($cur_page == 'subscriber.php')) {
														echo 'active';
													} ?>">
								<a href="subscriber.php">
									<i class="fa fa-hand-o-right"></i> <span>Subscriber</span>
								</a>
								</li> -->

				</ul>
			</section>
		</aside>

		<div class="content-wrapper">