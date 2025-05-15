<?php require_once('header.php');
?>
<?php
$seller_id = $_SESSION['seller_session'];
if (isset($_SESSION['seller_session'])) {
	$seller_id = $_SESSION['seller_session']['seller_id'];

	// Get seller status from database
	$statement = $pdo->prepare("SELECT seller_status FROM sellers WHERE seller_id = ?");
	$statement->execute([$seller_id]);
	$seller_status = $statement->fetchColumn();

	if ($seller_status == 0) {
		header('Location: profile-edit.php');
		exit;
	}
}


$ai_id = 0;
if (isset($_POST['form1'])) {
	$valid = 1;
	$seller_id = $_SESSION['seller_session']['seller_id'];

	if (empty($_POST['tcat_id'])) {
		$valid = 0;
		$error_message .= "You must have to select a top level category<br>";
	}

	if (empty($_POST['mcat_id'])) {
		$valid = 0;
		$error_message .= "You must have to select a mid level category<br>";
	}

	// if(empty($_POST['ecat_id'])) {
	//     $valid = 0;
	//     $error_message .= "You must have to select an end level category<br>";
	// }

	if (empty($_POST['p_name'])) {
		$valid = 0;
		$error_message .= "Product name can not be empty<br>";
	}

	if (empty($_POST['p_current_price'])) {
		$valid = 0;
		$error_message .= "Current Price can not be empty<br>";
	}

	if (empty($_POST['hsn_code'])) {
		$valid = 0;
		$error_message .= "Enter the HSN code<br>";
	} else {
		$hsn_code = $_POST['hsn_code'];
		if (!preg_match('/^\d{8}$/', $hsn_code)) {
			$valid = 0;
			$error_message .= "HSN code should be exactly 8 digits<br>";
		}
	}
	if (empty($_POST['gst_percentage'])) {
		$valid = 0;
		$error_message .= "Enter the GST percentage<br>";
	} else {
		$gst_percentage = $_POST['gst_percentage'];
		if ($gst_percentage > 18) {
			$valid = 0;
			$error_message .= "GST percentage can not be more than 18<br>";
		}
	}

	if (empty($_POST['p_qty'])) {
		$valid = 0;
		$error_message .= "Quantity can not be empty<br>";
	}
	if (empty($_POST['product_brand'])) {
		$valid = 0;
		$error_message .= "Product Brand should be selected<br>";
	}
	if ($_POST['tcat_id'] === 'others') {
		if (empty($_POST['other_brand'])) {
			$valid = 0;
			$error_message .= "You must specify the brand name<br>";
		} else {

			$product_brand = $_POST['other_brand'];
		}
	}


	$path = $_FILES['p_featured_photo']['name'];
	$path_tmp = $_FILES['p_featured_photo']['tmp_name'];

	if ($path != '') {
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		$file_name = basename($path, '.' . $ext);
		if ($ext != 'jpg' && $ext != 'png' && $ext != 'jpeg' && $ext != 'gif') {
			$valid = 0;
			$error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
		}
	} else {
		$valid = 0;
		$error_message .= 'You must have to select a featured photo<br>';
	}

	// Handle PDF file upload
	$pdf_path = $_FILES['product_catalogue']['name'];
	$pdf_path_tmp = $_FILES['product_catalogue']['tmp_name'];
	if ($pdf_path != '') {
		$pdf_ext = pathinfo($pdf_path, PATHINFO_EXTENSION);
		if ($pdf_ext != 'pdf') {
			$valid = 0;
			$error_message .= 'You must have to upload a PDF file for the product catalogue<br>';
		}
	} else {
		$valid = 0;
		$error_message .= 'You must have to select a product catalogue PDF file<br>';
	}

	if ($valid == 1) {
		$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_product'");
		$statement->execute();
		$result = $statement->fetchAll();
		foreach ($result as $row) {
			$ai_id = $row[10];
		}

		// Move the uploaded PDF file to the server
		$pdf_final_name = 'product-catalogue-' . $ai_id . '.pdf';
		move_uploaded_file($pdf_path_tmp, '../assets/uploads/product-catalogues/' . $pdf_final_name);

		if (isset($_FILES['photo']['name']) && isset($_FILES['photo']['tmp_name'])) {
			$photo = array();
			$photo = $_FILES['photo']['name'];
			$photo = array_values(array_filter($photo));

			$photo_temp = array();
			$photo_temp = $_FILES['photo']['tmp_name'];
			$photo_temp = array_values(array_filter($photo_temp));

			$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_product_photo'");
			$statement->execute();
			$result = $statement->fetchAll();
			foreach ($result as $row) {
				$next_id1 = $row[10];
			}
			$z = $next_id1;

			$m = 0;
			for ($i = 0; $i < count($photo); $i++) {
				$my_ext1 = pathinfo($photo[$i], PATHINFO_EXTENSION);
				if ($my_ext1 == 'jpg' || $my_ext1 == 'png' || $my_ext1 == 'jpeg' || $my_ext1 == 'gif') {
					$final_name1[$m] = $z . '.' . $my_ext1;
					move_uploaded_file($photo_temp[$i], "../assets/uploads/product-photos/" . $final_name1[$m]);
					$m++;
					$z++;
				}
			}

			if (isset($final_name1)) {
				for ($i = 0; $i < count($final_name1); $i++) {
					$statement = $pdo->prepare("INSERT INTO tbl_product_photo (photo, p_id) VALUES (?, ?)");
					$statement->execute(array($final_name1[$i], $ai_id));
				}
			}
		}

		$final_name = 'product-featured-' . $ai_id . '.' . $ext;
		move_uploaded_file($path_tmp, '../assets/uploads/product-photos/' . $final_name);


		//Saving data into the waiting products table tbl_waiting_products
		$statement = $pdo->prepare("INSERT INTO tbl_product(
			seller_id,
			p_name,
			p_old_price,
			p_current_price,
			hsn_code,
			gst_percentage,
			p_qty,
			p_featured_photo,
			p_description,
			p_total_view,
			tcat_id,
			mcat_id,
			ecat_id,
			product_catalogue,
			product_brand,
			p_date
		) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

		$statement->execute(array(
			$seller_id,
			$_POST['p_name'],
			$_POST['p_old_price'],
			$_POST['p_current_price'],
			$_POST['hsn_code'],
			$_POST['gst_percentage'],
			$_POST['p_qty'],
			$final_name,
			$_POST['p_description'],
			0, // Assuming total view is 0 initially
			$_POST['tcat_id'],
			$_POST['mcat_id'],
			$_POST['ecat_id'],
			$pdf_final_name,
			$_POST['product_brand'],
			date('Y-m-d H:i:s')
		));

		$success_message = 'Product is added successfully, wait for your administrator approval.';
	}

	$selected_values = [];
	$keys = ['P', 'M', 'K', 'N', 'S', 'H', 'O'];

	foreach ($keys as $key) {
		// Check if the key exists in the POST data, otherwise set default value to 0
		if (isset($_POST[$key])) {
			// $_POST[$key] is now an array of selected values (checkboxes)
			// Convert to a comma-separated string or store as needed
			$selected_values[$key] = implode(',', $_POST[$key]);
		} else {
			$selected_values[$key] = '';
		}
	}

	$statement = $pdo->prepare("INSERT INTO tbl_key (
		id,  
		P, M, K, N, S, H, O
	) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

	$statement->execute([
		$ai_id,
		$selected_values['P'],     // Comma-separated values for P or empty string
		$selected_values['M'],     // Comma-separated values for M or empty string
		$selected_values['K'],     // Comma-separated values for K or empty string
		$selected_values['N'],     // Comma-separated values for N or empty string
		$selected_values['S'],     // Comma-separated values for S or empty string
		$selected_values['H'],     // Comma-separated values for H or empty string
		$selected_values['O'],     // Comma-separated values for O or empty string
	]);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
	<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> -->
	<!-- <script src="https://cdn.tailwindcss.com"></script>  -->
	<style>
		.grid-wrapper {
			display: flex;
			align-items: flex-start;
		}

		.product-grid {
			display: grid;
			grid-template-columns: repeat(7, 30px);
			/* 7 columns for each category */
			gap: 1px;
			/* Space between cells */
			margin-bottom: 1rem;

			/* Space below the grid */
		}

		.material-suitability-icon-container {
			text-align: center;
			margin: 5px;
		}

		/* Common styles for icons */
		.material-suitability-icon {
			width: 30px;
			height: 30px;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 14px;
			/* Text size inside headers */
			font-weight: bold;
			/* Text emphasis */
			border: 1px solid #ddd;
			margin-bottom: 5px;
			/* Border for better visibility */
		}

		/* Header styles */
		.material-suitability-icon.header {
			background-color: #f0f0f0;
			/* Neutral background for headers */
		}

		/* Background colors for each column */
		.p {
			background-color: #E6F3FF;
			/* Light blue */
		}

		.m {
			background-color: #FFFDE6;
			/* Light yellow */
		}

		.k {
			background-color: #FFE6F7;
			/* Light pink */
		}

		.n {
			background-color: #E6FFE6;
			/* Light green */
		}

		.s {
			background-color: #E6E6E6;
			/* Light gray */
		}

		.h {
			background-color: #E6F3FF;
			/* Light blue (repeated intentionally) */
		}

		.o {
			background-color: #F0F0F0;
			/* Neutral gray */
		}

		.radio-group {
			display: flex;
			flex-direction: column;
			align-items: center;
		}

		.radio-group label {
			margin: 2px 0;
		}

		.l-info-icons-container {
			position: relative;
			/* For alignment */
			margin-left: 20px;
			/* Distance from the grid */
			padding: 12px;
			background-color: rgb(253, 253, 253);
			border-radius: 4px;
			/* box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15); */
			z-index: 1000;
			min-width: 200px;

		}

		.l-info-icons-container.show {
			display: block;
		}

		/* Info Row Styling */
		.info-row {
			display: flex;
			align-items: center;
			gap: 10px;
			/* Space between icon and description */
			margin-bottom: 8px;
			/* Space between rows */
		}

		/* Icon Styling */
		.icon.small {
			background-color: rgb(240, 236, 236);
			border-radius: 4px;
			box-shadow: 0 2px 8px rgba(16, 15, 15, 0.15);
			width: 24px;
			/* Adjust size as needed */
			height: 24px;
			display: inline-flex;
			justify-content: center;
			align-items: center;
			position: relative;
		}

		/* Rank-Specific Icons */
		.icon-rank-2::before,
		.icon-rank-1::before {
			content: "";
			position: absolute;
			width: 6px;
			height: 6px;
			background-color: #000;
			/* Dot color */
			border-radius: 50%;
		}

		/* Main Application (2 Dots) */
		.icon-rank-2::before {
			left: 5px;
		}

		.icon-rank-2::after {
			content: "";
			position: absolute;
			width: 6px;
			height: 6px;
			background-color: #000;
			/* Dot color */
			border-radius: 50%;
			right: 5px;
		}

		/* Additional Application (1 Dot) */
		.icon-rank-1::before {
			left: 50%;
			transform: translateX(-50%);
		}
	</style>


</head>

<body>

	<section class="content-header">
		<div class="content-header-left">
			<h1>Add Product</h1>
		</div>
		<div class="content-header-right">
			<a href="product.php" class="btn btn-primary btn-sm">View All</a>
		</div>
	</section>


	<section class="content">

		<div class="row">
			<div class="col-md-12">

				<?php if ($error_message): ?>
					<div class="callout callout-danger">

						<p>
							<?php echo $error_message; ?>
						</p>
					</div>
				<?php endif; ?>

				<?php if ($success_message): ?>
					<div class="callout callout-success">

						<p><?php echo $success_message; ?></p>
					</div>
				<?php endif; ?>

				<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">

					<div class="box box-info">
						<div class="box-body">

							<div class="form-group">
								<label for="" class="col-sm-3 control-label">Top Level Category Name
									<span>*</span></label>
								<div class="col-sm-4">
									<select name="tcat_id" class="form-control select2 top-cat">
										<option value="">Select Top Level Category</option>
										<?php
										$statement = $pdo->prepare("SELECT * FROM tbl_top_category ORDER BY tcat_name ASC");
										$statement->execute();
										$result = $statement->fetchAll(PDO::FETCH_ASSOC);
										foreach ($result as $row) {
										?>
											<option value="<?php echo $row['tcat_id']; ?>" <?php if (isset($_POST['tcat_id']) && $_POST['tcat_id'] == $row['tcat_id']) echo 'selected'; ?>><?php echo $row['tcat_name']; ?></option>
										<?php
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="" class="col-sm-3 control-label">Mid Level Category Name
									<span>*</span></label>
								<div class="col-sm-4">
									<select name="mcat_id" class="form-control select2 mid-cat">
										<option value="">Select Mid Level Category</option>
										<?php
										// This is where you need to add code to populate mid-level categories
										// based on the selected top-level category
										if (isset($_POST['tcat_id']) && !empty($_POST['tcat_id'])) {
											$statement = $pdo->prepare("SELECT * FROM tbl_mid_category 
                                           WHERE tcat_id = ? 
                                           ORDER BY mcat_name ASC");
											$statement->execute(array($_POST['tcat_id']));
											$result = $statement->fetchAll(PDO::FETCH_ASSOC);
											foreach ($result as $row) {
										?>
												<option value="<?php echo $row['mcat_id']; ?>"
													<?php if (isset($_POST['mcat_id']) && $_POST['mcat_id'] == $row['mcat_id']) echo 'selected'; ?>>
													<?php echo $row['mcat_name']; ?>
												</option>
										<?php
											}
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label for="" class="col-sm-3 control-label">End Level Category</label>
								<div class="col-sm-4">
									<select name="ecat_id" class="form-control select2 end-cat">
										<option value="">Select End Level Category</option>
										<?php
										if (isset($_POST['mcat_id']) && !empty($_POST['mcat_id'])) {
											$statement = $pdo->prepare("SELECT * FROM tbl_end_category WHERE mcat_id = ? ORDER BY ecat_name ASC");
											$statement->execute([$_POST['mcat_id']]);
											$result = $statement->fetchAll(PDO::FETCH_ASSOC);
											foreach ($result as $row) {
										?>
												<option value="<?php echo $row['ecat_id']; ?>"
													<?php if (isset($_POST['ecat_id']) && $_POST['ecat_id'] == $row['ecat_id']) echo 'selected'; ?>>
													<?php echo $row['ecat_name']; ?>
												</option>
										<?php
											}
										}
										?>
									</select>
								</div>
							</div>

							<div class="form-group">
								<label for="" class="col-sm-3 control-label">Brand <span>*</span></label>
								<div class="col-sm-4">
									<select name="product_brand" class="form-control select2 brand-cat">
										<option value="">Select Brand</option>
										<?php
										$seller_id = $_SESSION['seller_session']['seller_id'];
										$statement = $pdo->prepare("SELECT sb.brand_id,
                                    b.brand_name
                                    FROM seller_brands sb
                                     JOIN tbl_brands b ON sb.brand_id = b.brand_id
                                     WHERE sb.seller_id = :seller_id");
										$statement->bindParam(':seller_id', $seller_id);
										$statement->execute();
										$result = $statement->fetchAll(PDO::FETCH_ASSOC);
										foreach ($result as $row) {
										?>
											<option value="<?php echo $row['brand_id']; ?>" <?php if (isset($_POST['product_brand']) && $_POST['product_brand'] == $row['brand_id']) echo 'selected'; ?>>
												<?php echo $row['brand_name']; ?>
											</option>
										<?php
										}
										?>
										<option value="Others" <?php if (isset($_POST['product_brand']) && $_POST['product_brand'] == 'Others') echo 'selected'; ?>>Others</option>
									</select>

									<!-- <input type="text" name="other_brand" class="form-control" id="other-brand" style="margin-top:10px;" placeholder="Please specify brand"> -->
								</div>
							</div>
							<div class="form-group">
								<label for="" class="col-sm-3 control-label">Product Name <span>*</span></label>
								<div class="col-sm-4">
									<input type="text" name="p_name"
										value="<?php echo isset($_POST['p_name']) ? htmlspecialchars($_POST['p_name']) : ''; ?>"
										required class="form-control">
								</div>
							</div>
							<div class="form-group">
								<label for="" class="col-sm-3 control-label">Product Catalogue (PDF)
									<span>*</span></label>
								<div class="col-sm-4" style="padding-top:4px;">
									<input type="file" name="product_catalogue">
								</div>
							</div>

							<div class="form-group">
								<label for="" class="col-sm-3 control-label">Key <span>*</span></label>
								<div class="col-sm-9">
									<div class="grid-wrapper">
										<!-- Product Grid -->
										<div class="product-grid">
											<div class="material-suitability-icon-container">
												<div class="material-suitability-icon p">P</div>
												<div class="radio-group">
													<label><input type="checkbox" name="P[]" value="1" <?php if (isset($_POST['P']) && in_array('1', (array)$_POST['P'])) echo 'checked'; ?>> 1</label>
													<label><input type="checkbox" name="P[]" value="2" <?php if (isset($_POST['P']) && in_array('2', (array)$_POST['P'])) echo 'checked'; ?>> 2</label>
												</div>
											</div>
											<div class="material-suitability-icon-container">
												<div class="material-suitability-icon m">M</div>
												<div class="radio-group">
													<label><input type="checkbox" name="M[]" value="1" <?php if (isset($_POST['M']) && in_array('1', (array)$_POST['M'])) echo 'checked'; ?>> 1</label>
													<label><input type="checkbox" name="M[]" value="2" <?php if (isset($_POST['M']) && in_array('2', (array)$_POST['M'])) echo 'checked'; ?>> 2</label>
												</div>
											</div>
											<div class="material-suitability-icon-container">
												<div class="material-suitability-icon k">K</div>
												<div class="radio-group">
													<label><input type="checkbox" name="K[]" value="1" <?php if (isset($_POST['K']) && in_array('1', (array)$_POST['K'])) echo 'checked'; ?>> 1</label>
													<label><input type="checkbox" name="K[]" value="2" <?php if (isset($_POST['K']) && in_array('2', (array)$_POST['K'])) echo 'checked'; ?>> 2</label>
												</div>
											</div>
											<div class="material-suitability-icon-container">
												<div class="material-suitability-icon n">N</div>
												<div class="radio-group">
													<label><input type="checkbox" name="N[]" value="1" <?php if (isset($_POST['N']) && in_array('1', (array)$_POST['N'])) echo 'checked'; ?>> 1</label>
													<label><input type="checkbox" name="N[]" value="2" <?php if (isset($_POST['N']) && in_array('2', (array)$_POST['N'])) echo 'checked'; ?>> 2</label>
												</div>
											</div>
											<div class="material-suitability-icon-container">
												<div class="material-suitability-icon s">S</div>
												<div class="radio-group">
													<label><input type="checkbox" name="S[]" value="1" <?php if (isset($_POST['S']) && in_array('1', (array)$_POST['S'])) echo 'checked'; ?>> 1</label>
													<label><input type="checkbox" name="S[]" value="2" <?php if (isset($_POST['S']) && in_array('2', (array)$_POST['S'])) echo 'checked'; ?>> 2</label>
												</div>
											</div>
											<div class="material-suitability-icon-container">
												<div class="material-suitability-icon h">H</div>
												<div class="radio-group">
													<label><input type="checkbox" name="H[]" value="1" <?php if (isset($_POST['H']) && in_array('1', (array)$_POST['H'])) echo 'checked'; ?>> 1</label>
													<label><input type="checkbox" name="H[]" value="2" <?php if (isset($_POST['H']) && in_array('2', (array)$_POST['H'])) echo 'checked'; ?>> 2</label>
												</div>
											</div>
											<div class="material-suitability-icon-container">
												<div class="material-suitability-icon o">O</div>
												<div class="radio-group">
													<label><input type="checkbox" name="O[]" value="1" <?php if (isset($_POST['O']) && in_array('1', (array)$_POST['O'])) echo 'checked'; ?>> 1</label>
													<label><input type="checkbox" name="O[]" value="2" <?php if (isset($_POST['O']) && in_array('2', (array)$_POST['O'])) echo 'checked'; ?>> 2</label>
												</div>
											</div>
										</div>
										<!-- Info Container -->
										<div class="l-info-icons-container">
											<!-- Your information or content here -->
											<div class="info-row">
												<div class="icon small icon-rank-2"></div>
												<div class="description">Main application</div>
											</div>
											<div class="info-row">
												<div class="icon small icon-rank-1"></div>
												<div class="description">Additional application</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="form-group">
								<label for="" class="col-sm-3 control-label">Old Price <br><span
										style="font-size:10px;font-weight:normal;">(In INR)</span></label>
								<div class="col-sm-4">
									<input type="text" name="p_old_price"
										value="<?php echo isset($_POST['p_old_price']) ? htmlspecialchars($_POST['p_old_price']) : ''; ?>"
										required class="form-control">
								</div>
							</div>
							<div class="form-group">
								<label for="" class="col-sm-3 control-label">Current Price <span>*</span><br><span
										style="font-size:10px;font-weight:normal;">(In INR)</span></label>
								<div class="col-sm-4">
									<input type="text" name="p_current_price"
										value="<?php echo isset($_POST['p_current_price']) ? htmlspecialchars($_POST['p_current_price']) : ''; ?>"
										required class="form-control">
								</div>
							</div>
							<div class="form-group">
								<label for="" class="col-sm-3 control-label">HSN Code <span>*</span><br></label>
								<div class="col-sm-4">
									<input type="text" name="hsn_code"
										value="<?php echo isset($_POST['hsn_code']) ? htmlspecialchars($_POST['hsn_code']) : ''; ?>"
										required class="form-control" placeholder="8 digit code">
								</div>
							</div>
							<div class="form-group">
								<label for="" class="col-sm-3 control-label">GST % <span>*</span><br></label>
								<div class="col-sm-4">
									<input type="text" name="gst_percentage"
										value="<?php echo isset($_POST['gst_percentage']) ? htmlspecialchars($_POST['gst_percentage']) : ''; ?>"
										required class="form-control" placeholder="Max upto 18%">
								</div>
							</div>

							<div class="form-group">
								<label for="" class="col-sm-3 control-label">Quantity <span>*</span></label>
								<div class="col-sm-4">
									<input type="text" name="p_qty"
										value="<?php echo isset($_POST['p_qty']) ? htmlspecialchars($_POST['p_qty']) : ''; ?>"
										required class="form-control">
								</div>
							</div>

							<div class="form-group">
								<label for="" class="col-sm-3 control-label">Featured Photo <span>*</span></label>
								<div class="col-sm-4" style="padding-top:4px;">
									<input type="file" name="p_featured_photo">
								</div>
							</div>
							<div class="form-group">
								<label for="" class="col-sm-3 control-label">Other Photos</label>
								<div class="col-sm-4" style="padding-top:4px;">
									<table id="ProductTable" style="width:100%;">
										<tbody>
											<tr>
												<td>
													<div class="upload-btn">
														<input type="file" name="photo[]" style="margin-bottom:5px;">
													</div>
												</td>
												<td style="width:28px;"><a href="javascript:void()"
														class="Delete btn btn-danger btn-xs">X</a></td>
											</tr>
										</tbody>
									</table>
								</div>
								<div class="col-sm-2">
									<input type="button" id="btnAddNew" value="Add Item"
										style="margin-top: 5px;margin-bottom:10px;border:0;color: #fff;font-size: 14px;border-radius:3px;"
										class="btn btn-warning btn-xs">
								</div>
							</div>
							<div class="form-group">
								<label for="" class="col-sm-3 control-label">Description</label>
								<div class="col-sm-8">

									<textarea name="p_description" class="form-control"><?php echo isset($_POST['p_description']) ? htmlspecialchars($_POST['p_description']) : ''; ?></textarea>
								</div>
							</div>

						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Add
									Product</button>
							</div>
						</div>
					</div>
			</div>

			</form>


		</div>
		</div>

	</section>



	<script>
		$(document).ready(function() {
			// Initial Select2 initialization
			$('.select2').select2({
				width: '100%',
				closeOnSelect: true
			});

			// When mid-category changes
			$(document).on('change', '.mid-cat', function(e) {
				var mid_cat_id = $(this).val();
				var $endCat = $('.end-cat');

				$.ajax({
					url: "get_end_category.php",
					type: "POST",
					data: {
						id: mid_cat_id
					},
					success: function(response) {
						// Destroy existing select2 instance
						$endCat.select2('destroy');

						// Update the HTML
						$endCat.html(response);

						// Check for real options (excluding the default option)
						var hasOptions = $endCat.find('option[value!=""]').length > 0;

						// Set disabled state based on options
						$endCat.prop('disabled', !hasOptions);

						// Reinitialize select2
						$endCat.select2({
							width: '100%'
						});
					}
				});
			});

			// Document click handler for closing dropdowns
			$(document).on('click', function(e) {
				if (!$(e.target).closest('.select2-container').length) {
					$('.select2').select2('close');
				}
			});
		});
	</script>
</body>

</html>
<?php require_once('footer.php'); ?>