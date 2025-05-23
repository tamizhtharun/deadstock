<?php
ob_start();
session_start();
include("../db_connection.php");
// include("inc/functions.php");
// include("inc/CSRF_Protect.php");
// $csrf = new CSRF_Protect();
$error_message = '';
$success_message = '';
$error_message1 = '';
$success_message1 = '';

// Check if the user is logged in or not
if (!isset($_SESSION['admin_session'])) {
	header('location: ../index.php');
	exit;
}
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Admin Panel</title>

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
	<link rel="preload" href="https://fonts.gstatic.com/s/roboto/v47/KFO7CnqEu92Fr1ME7kSn66aGLdTylUAMa3yUBA.woff2" as="font" crossorigin>
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

	<style>
		/* Make dropdown icon always visible */
		.dropdown-icon {
			opacity: 1 !important;
			visibility: visible !important;
		}
	</style>

</head>

<body class="hold-transition fixed skin-blue sidebar-mini">

	<div class="wrapper">

		<header class="main-header">

			<a href="index.php" class="logo">
				<span class="logo-lg">DeadStock</span>
			</a>

			<nav class="navbar navbar-static-top d-flex justify-content-between px-3">
				<div class="d-flex align-items-center">
					<a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
						<span class="sr-only">Toggle navigation</span>
					</a>
					<span style="line-height:50px;color:#fff;padding-left:15px;font-size:18px; font-weight:800;">Admin Panel</span>
				</div>

				<!-- Alert Message -->
				<div id="message" style="display:none; position: fixed; top: 10px; left: 50%; transform: translateX(-50%); z-index: 1000; padding: 10px; border-radius: 5px; color: #fff; font-size: 14px;"></div>
				<!-- Alert Message end -->

				<!-- Top Bar ... User Information .. Login/Log out Area -->
				<div class="navbar-custom-menu">
					<div class="dropdown profile-dropdown">
						<button class="btn btn-secondary dropdown-toggle d-flex align-items-center profile-hover" type="button" id="newProfileDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="background: none; border: none; padding: 0;">
							<?php
							if (!empty($_SESSION['admin_session']['user_photo'])) {
								$profile_photo = $_SESSION['admin_session']['user_photo'];
								echo '<img src="../assets/uploads/profile-pictures/' . $profile_photo . '" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 8px;">';
							} else {
								echo '<i class="fa fa-user" style="font-size: 20px; margin-right: 8px;"></i>';
							}
							?>
							<span style="font-weight:800; margin-right: 6px;"><?php echo $_SESSION['admin_session']['user_name']; ?></span>
						</button>
						<ul class="dropdown-menu dropdown-menu-end profile-menu" aria-labelledby="newProfileDropdown">
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


					<li class="treeview <?php if (($cur_page == 'settings.php')) {
											echo 'active';
										} ?>">
						<a href="settings.php">
							<i class="fa fa-sliders"></i> <span>Website Settings</span>
						</a>
					</li>

					<li class="treeview <?php if (($cur_page == 'brand-management.php') || ($cur_page == 'brand-edit.php') || ($cur_page == 'brand-add.php') || ($cur_page == 'brand-delete.php')) {
											echo 'active';
										} ?>">
						<a href="brand-management.php">
							<i class="fa fa-user-plus"></i> <span>Brand Management</span>
						</a>
					</li>

					<li class="treeview <?php if (($cur_page == 'size.php') || ($cur_page == 'size-add.php') || ($cur_page == 'size-edit.php') || ($cur_page == 'color.php') || ($cur_page == 'color-add.php') || ($cur_page == 'color-edit.php') || ($cur_page == 'country.php') || ($cur_page == 'country-add.php') || ($cur_page == 'country-edit.php') || ($cur_page == 'shipping-cost.php') || ($cur_page == 'shipping-cost-edit.php') || ($cur_page == 'top-category.php') || ($cur_page == 'top-category-add.php') || ($cur_page == 'top-category-edit.php') || ($cur_page == 'mid-category.php') || ($cur_page == 'mid-category-add.php') || ($cur_page == 'mid-category-edit.php') || ($cur_page == 'end-category.php') || ($cur_page == 'end-category-add.php') || ($cur_page == 'end-category-edit.php')) {
											echo 'active';
										} ?>">
						<a href="#">
							<i class="fa fa-cogs"></i>
							<span>Category Management</span>
							<span class="pull-right-container">
								<i class="fa fa-angle-left pull-right"></i>
							</span>
						</a>
						<ul class="treeview-menu">

							<!-- <li><a href="country.php"><i class="fa fa-circle-o"></i> Country</a></li> -->
							<!-- <li><a href="shipping-cost.php"><i class="fa fa-circle-o"></i> Shipping Cost</a></li> -->
							<li><a href="top-category.php"><i class="fa fa-circle-o"></i> Top Level Category</a></li>
							<li><a href="mid-category.php"><i class="fa fa-circle-o"></i> Mid Level Category</a></li>
							<li><a href="end-category.php"><i class="fa fa-circle-o"></i> End Level Category</a></li>
						</ul>
					</li>


					<li class="treeview <?php if (($cur_page == '#') || ($cur_page == 'seller-uploaded-products.php') || ($cur_page == 'seller-approved-products.php')  || ($cur_page == 'seller-rejected-products.php')  || ($cur_page == 'seller-products.php') || ($cur_page == 'seller-approved-product-view.php') || ($cur_page == 'seller-rejected-product-view.php')) {
											echo 'active';
										} ?>">
						<a href="#">
							<i class="fa fa-shopping-bag"></i> <span>Product Management</span>
							<span class="pull-right-container">
								<i class="fa fa-angle-left pull-right"></i>
							</span>
						</a>
						<ul class="treeview-menu">
							<li><a href="all-products.php"><i class="fa fa-circle-o"></i> All Products</a></li>
							<li><a href="seller-uploaded-products.php"><i class="fa fa-circle-o"></i> Products by Seller</a></li>
							<li><a href="seller-approved-products.php"><i class="fa fa-circle-o"></i> Approved Products</a></li>
							<li><a href="seller-rejected-products.php"><i class="fa fa-circle-o"></i> Rejected Products</a></li>


						</ul>

					</li>

					<li class="treeview <?php if (($cur_page == 'seller.php') || ($cur_page == 'seller-incomplete.php')) {
											echo 'active';
										} ?>">
						<a href="#">
							<i class="fa fa-user-plus"></i> <span>Registered Sellers</span>
							<span class="pull-right-container"><i class="fa fa-angle-left pull-right"></i>
							</span>
						</a>
						<ul class="treeview-menu">
							<li><a href="seller.php"><i class="fa fa-circle-o"></i> Completed Profiles</a></li>
							<li><a href="seller-incomplete.php"><i class="fa fa-circle-o"></i> Incomplete Profiles</a></li>


						</ul>
					</li>
					<li class="treeview <?php if (($cur_page == 'customer.php') || ($cur_page == 'customer-add.php') || ($cur_page == 'customer-edit.php')) {
											echo 'active';
										} ?>">
						<a href="customer.php">
							<i class="fa fa-user-plus"></i> <span>Buyers</span>
						</a>
					</li>


					<li class="treeview <?php if (($cur_page == 'bidding.php')) {
											echo 'active';
										} ?>">
						<a href="bidding.php">
							<i class="fa fa-bell"></i> <span>Bid Management</span>
						</a>
					</li>

					<li class="treeview <?php if (($cur_page == 'bidding-order.php') || ($cur_page == 'direct-order.php') || ($cur_page == 'order-history.php')) {
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
							<li><a href="order-history.php"><i class="fa fa-circle-o"></i> All Orders History</a></li>
						</ul>
					</li>

					<li class="treeview <?php if (($cur_page == 'slider.php')) {
											echo 'active';
										} ?>">
						<a href="slider.php">
							<i class="fa fa-picture-o"></i> <span>Manage Sliders</span>
						</a>
					</li>
					<!-- Icons to be displayed on Shop -->


					<li class="treeview <?php if (($cur_page == 'mail.php') || ($cur_page == 'notification.php')) {
											echo 'active';
										} ?>">
						<a href="#">
							<i class="fa fa-list-ol"></i>
							<span>Communication</span>
							<span class="pull-right-container">
								<i class="fa fa-angle-left pull-right"></i>
							</span>
						</a>
						<ul class="treeview-menu">
							<li><a href="mail.php"><i class="fa fa-circle-o"></i> Mail box</a></li>
							<li><a href="notification.php"><i class="fa fa-circle-o"></i> Notification</a></li>
						</ul>
					</li>

					<li class="treeview <?php if (($cur_page == 'settlement.php')) {
											echo 'active';
										} ?>">
						<a href="settlement.php">
							<i class="fa fa-money"></i> <span>Settlements</span>
						</a>
					</li>
					<!-- <li class="treeview <?php if (($cur_page == 'faq.php')) {
													echo 'active';
												} ?>">
			          <a href="faq.php">
			            <i class="fa fa-question-circle"></i> <span>FAQ</span>
			          </a>
			        </li> -->




					<!-- <li class="treeview <?php if (($cur_page == 'page.php')) {
													echo 'active';
												} ?>">
			          <a href="page.php">
			            <i class="fa fa-tasks"></i> <span>Page Settings</span>
			          </a>
			        </li> -->

					<!-- <li class="treeview <?php if (($cur_page == 'social-media.php')) {
													echo 'active';
												} ?>">
			          <a href="#">
			            <i class="fa fa-globe"></i> <span>Social Media</span>
			          </a>
			        </li> -->

					<!-- <li class="treeview <?php if (($cur_page == 'subscriber.php') || ($cur_page == 'subscriber.php')) {
													echo 'active';
												} ?>">
			          <a href="#">
			            <i class="fa fa-hand-o-right"></i> <span>Subscriber</span>
			          </a>
			        </li> -->

				</ul>
			</section>
		</aside>

		<div class="content-wrapper">