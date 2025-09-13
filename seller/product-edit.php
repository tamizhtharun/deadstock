<?php require_once('header.php'); ?>
<?php
if (isset($_POST['form1'])) {
	$valid = 1;

	// if(empty($_POST['tcat_id'])) {
	//     $valid = 0;
	//     $error_message .= "You must have to select a top level category<br>";
	// }

	// if(empty($_POST['mcat_id'])) {
	//     $valid = 0;
	//     $error_message .= "You must have to select a mid level category<br>";
	// }

	// if(empty($_POST['ecat_id'])) {
	//     $valid = 0;
	//     $error_message .= "You must have to select an end level category<br>";
	// }

	// if(empty($_POST['p_name'])) {
	//     $valid = 0;
	//     $error_message .= "Product name can not be empty<br>";
	// }

	// Remove strict validation for most fields to allow partial updates
	// if (empty($_POST['tcat_id']) || $_POST['tcat_id'] == '') {
	// 	$valid = 0;
	// 	$error_message .= "You must have to select a top level category<br>";
	// }

	// if (empty($_POST['mcat_id'])) {
	// 	$valid = 0;
	// 	$error_message .= "You must have to select a mid level category<br>";
	// }

	// if (empty($_POST['p_name'])) {
	// 	$valid = 0;
	// 	$error_message .= "Product name can not be empty<br>";
	// }

	// if (empty($_POST['p_current_price'])) {
	// 	$valid = 0;
	// 	$error_message .= "Current Price can not be empty<br>";
	// }

	// Keep validation for HSN Code: if provided, must be 8 digits
	if (!empty($_POST['hsn_code'])) {
		$hsn_code = $_POST['hsn_code'];
		if (!preg_match('/^\d{8}$/', $hsn_code)) {
			$valid = 0;
			$error_message .= "HSN code should be exactly 8 digits<br>";
		}
	}

	// Keep validation for GST Percentage: if provided, must be up to 18%
	if (!empty($_POST['gst_percentage'])) {
		$gst_percentage = $_POST['gst_percentage'];
		if ($gst_percentage > 18) {
			$valid = 0;
			$error_message .= "GST percentage can not be more than 18<br>";
		}
	}

	// if (empty($_POST['p_qty'])) {
	// 	$valid = 0;
	// 	$error_message .= "Quantity can not be empty<br>";
	// }

	// if (empty($_POST['product_brand'])) {
	// 	$valid = 0;
	// 	$error_message .= "Product Brand should be selected<br>";
	// }

	$product_brand = $_POST['product_brand'];
	if ($_POST['product_brand'] === 'others') {
		if (empty($_POST['other_brand'])) {
			$valid = 0;
			$error_message .= "You must specify the brand name<br>";
		} else {
			$product_brand = $_POST['other_brand'];
		}
	}

	$path = $_FILES['p_featured_photo']['name'];
	$path_tmp = $_FILES['p_featured_photo']['tmp_name'];

	$pdf_path = $_FILES['product_catalogue']['name'];
	$pdf_path_tmp = $_FILES['product_catalogue']['tmp_name'];
	if ($pdf_path != '') {
		$pdf_ext = pathinfo($pdf_path, PATHINFO_EXTENSION);
		if ($pdf_ext != 'pdf') {
			$valid = 0;
			$error_message .= 'You must have to upload a PDF file for the product catalogue<br>';
		}
	}

	$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_product'");
	$statement->execute();
	$result = $statement->fetchAll();
	foreach ($result as $row) {
		$ai_id = $row[10];
	}
	// Assuming $_REQUEST['id'] contains the current product ID
	$current_product_id = $_REQUEST['id'];
	$pdf_final_name = 'product-catalogue-' . $current_product_id . '.pdf';

	// Move the uploaded PDF file to the server
	move_uploaded_file($pdf_path_tmp, '../assets/uploads/product-catalogues/' . $pdf_final_name);

	// $old_pdf_path = '../assets/uploads/product-catalogue-'.$current_product_id.'.pdf';
	// if (file_exists($old_pdf_path)) {
	//     unlink($old_pdf_path); // Delete the old file
	// }

	if ($path != '') {
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		$file_name = basename($path, '.' . $ext);
		if ($ext != 'jpg' && $ext != 'png' && $ext != 'jpeg' && $ext != 'gif') {
			$valid = 0;
			$error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
		}
	}


	if ($valid == 1) {

		if (isset($_FILES['photo']["name"]) && isset($_FILES['photo']["tmp_name"])) {

			$photo = array();
			$photo = $_FILES['photo']["name"];
			$photo = array_values(array_filter($photo));

			$photo_temp = array();
			$photo_temp = $_FILES['photo']["tmp_name"];
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
					$statement = $pdo->prepare("INSERT INTO tbl_product_photo (photo,p_id) VALUES (?,?)");
					$statement->execute(array($final_name1[$i], $_REQUEST['id']));
				}
			}
		}

		if ($path == '') {
			$statement = $pdo->prepare("UPDATE tbl_product SET
								p_name=?,
								tcat_id=?,
								mcat_id=?,
								ecat_id=?,
								product_brand=?,
								hsn_code=?,
								gst_percentage=?,
        						p_old_price=?,
        						p_current_price=?,
        						p_qty=?,
        						p_description=?,
        						product_catalogue=?,
        						p_is_featured = CASE WHEN p_is_featured = 1 THEN 0 ELSE p_is_featured END,
        						p_is_approve=0
        						WHERE id=?");
			$statement->execute(array(
				$_POST['p_name'],
				$_POST['tcat_id'],
				$_POST['mcat_id'],
				$_POST['ecat_id'],
				$product_brand,
				$_POST['hsn_code'],
				$_POST['gst_percentage'],
				$_POST['p_old_price'],
				$_POST['p_current_price'],
				$_POST['p_qty'],
				$_POST['p_description'],
				$pdf_final_name,
				$_REQUEST['id']
			));
		} else {

			$current_photo_path = '../assets/uploads/product-photos/' . $_POST['current_photo'];
			if (!empty($_POST['current_photo']) && file_exists($current_photo_path) && !is_dir($current_photo_path)) {
				unlink($current_photo_path);
			}

			$final_name = 'product-featured-' . $_REQUEST['id'] . '.' . $ext;
			move_uploaded_file($path_tmp, '../assets/uploads/product-photos/' . $final_name);


			$statement = $pdo->prepare("UPDATE tbl_product SET
								p_name=?,
								tcat_id=?,
								mcat_id=?,
								ecat_id=?,
								product_brand=?,
								hsn_code=?,
								gst_percentage=?,
        						p_old_price=?,
        						p_current_price=?,
        						p_qty=?,
        						p_featured_photo=?,
        						p_description=?,
        						product_catalogue=?,
        						p_is_featured = CASE WHEN p_is_featured = 1 THEN 0 ELSE p_is_featured END,
        						p_is_approve=0
        						WHERE id=?");
			$statement->execute(array(
				$_POST['p_name'],
				$_POST['tcat_id'],
				$_POST['mcat_id'],
				$_POST['ecat_id'],
				$product_brand,
				$_POST['hsn_code'],
				$_POST['gst_percentage'],
				$_POST['p_old_price'],
				$_POST['p_current_price'],
				$_POST['p_qty'],
				$final_name,
				$_POST['p_description'],
				$pdf_final_name,
				$_REQUEST['id']
			));
		}


		// if(isset($_POST['size'])) {

		// 	$statement = $pdo->prepare("DELETE FROM tbl_product_size WHERE p_id=?");
		// 	$statement->execute(array($_REQUEST['id']));

		// 	foreach($_POST['size'] as $value) {
		// 		$statement = $pdo->prepare("INSERT INTO tbl_product_size (size_id,p_id) VALUES (?,?)");
		// 		$statement->execute(array($value,$_REQUEST['id']));
		// 	}
		// } else {
		// 	$statement = $pdo->prepare("DELETE FROM tbl_product_size WHERE p_id=?");
		// 	$statement->execute(array($_REQUEST['id']));
		// }

		// if(isset($_POST['color'])) {

		// 	$statement = $pdo->prepare("DELETE FROM tbl_product_color WHERE p_id=?");
		// 	$statement->execute(array($_REQUEST['id']));

		// 	foreach($_POST['color'] as $value) {
		// 		$statement = $pdo->prepare("INSERT INTO tbl_product_color (color_id,p_id) VALUES (?,?)");
		// 		$statement->execute(array($value,$_REQUEST['id']));
		// 	}
		// } else {
		// 	$statement = $pdo->prepare("DELETE FROM tbl_product_color WHERE p_id=?");
		// 	$statement->execute(array($_REQUEST['id']));
		// }

		$success_message = 'Product is updated successfully.';
	}
}
?>

<?php
if (!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if ($total == 0) {
		header('location: logout.php');
		exit;
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Edit Product</h1>
	</div>
	<div class="content-header-right">
		<a href="product.php" class="btn btn-primary btn-sm">View All</a>
	</div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$p_name = $row['p_name'];
	$p_old_price = $row['p_old_price'];
	$p_current_price = $row['p_current_price'];
	$p_qty = $row['p_qty'];
	$p_featured_photo = $row['p_featured_photo'];
	$p_description = $row['p_description'];
	// $p_short_description = $row['p_short_description'];
	// $p_condition = $row['p_condition'];
	// $p_return_policy = $row['p_return_policy'];
	$p_is_featured = $row['p_is_featured'];
	// $p_is_active = $row['p_is_active'];
	$ecat_id = $row['ecat_id'];
	$tcat_id = $row['tcat_id'];
	$mcat_id = $row['mcat_id'];
	$product_brand = $row['product_brand'];
	$hsn_code = $row['hsn_code'];
	$gst_percentage = $row['gst_percentage'];
	$p_is_approve = $row['p_is_approve'];
}

if (!isset($ecat_id)) {
    $ecat_id = 0;
}
$statement = $pdo->prepare("SELECT * 
                        FROM tbl_end_category t1
                        JOIN tbl_mid_category t2
                        ON t1.mcat_id = t2.mcat_id
                        JOIN tbl_top_category t3
                        ON t2.tcat_id = t3.tcat_id
                        WHERE t1.ecat_id=?");
$statement->execute(array($ecat_id));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$ecat_name = $row['ecat_name'];
	$mcat_id = $row['mcat_id'];
	$tcat_id = $row['tcat_id'];
}

// $statement = $pdo->prepare("SELECT * FROM tbl_product_size WHERE id=?");
// $statement->execute(array($_REQUEST['id']));
// $result = $statement->fetchAll(PDO::FETCH_ASSOC);							
// foreach ($result as $row) {
// 	$size_id[] = $row['size_id'];
// }

// $statement = $pdo->prepare("SELECT * FROM tbl_product_color WHERE id=?");
// $statement->execute(array($_REQUEST['id']));
// $result = $statement->fetchAll(PDO::FETCH_ASSOC);							
// foreach ($result as $row) {
// 	$color_id[] = $row['color_id'];
// }
?>


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
							<label for="" class="col-sm-3 control-label">Top Level Category Name <span>*</span></label>
							<div class="col-sm-4">
								<select name="tcat_id" class="form-control select2 top-cat">
		                            <option value="">Select Top Level Category</option>
		                            <?php
									$statement = $pdo->prepare("SELECT * FROM tbl_top_category ORDER BY tcat_name ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);
									foreach ($result as $row) {
									?>
		                                <option value="<?php echo $row['tcat_id']; ?>" <?php if ($row['tcat_id'] == $tcat_id) {
																							echo 'selected';
																						} ?>><?php echo $row['tcat_name']; ?></option>
		                                <?php
									}
										?>
		                        </select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Mid Level Category Name <span>*</span></label>
							<div class="col-sm-4">
								<select name="mcat_id" class="form-control select2 mid-cat">
		                            <option value="">Select Mid Level Category</option>
		                            <?php
									$statement = $pdo->prepare("SELECT * FROM tbl_mid_category WHERE tcat_id = ? ORDER BY mcat_name ASC");
									$statement->execute(array($tcat_id));
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);
									foreach ($result as $row) {
									?>
		                                <option value="<?php echo $row['mcat_id']; ?>" <?php if ($row['mcat_id'] == $mcat_id) {
																							echo 'selected';
																						} ?>><?php echo $row['mcat_name']; ?></option>
		                                <?php
									}
										?>
		                        </select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">End Level Category Name <span>*</span></label>
							<div class="col-sm-4">
								<select name="ecat_id" class="form-control select2 end-cat">
		                            <option value="">Select End Level Category</option>
		                            <?php
									$statement = $pdo->prepare("SELECT * FROM tbl_end_category WHERE mcat_id = ? ORDER BY ecat_name ASC");
									$statement->execute(array($mcat_id));
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);
									foreach ($result as $row) {
									?>
		                                <option value="<?php echo $row['ecat_id']; ?>" <?php if ($row['ecat_id'] == $ecat_id) {
																							echo 'selected';
																						} ?>><?php echo $row['ecat_name']; ?></option>
		                                <?php
									}
										?>
		                        </select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Product Name <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="p_name" class="form-control" value="<?php echo htmlspecialchars($p_name); ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Product Brand <span>*</span></label>
							<div class="col-sm-4">
								<select name="product_brand" class="form-control select2 brand-select">
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
										<option value="<?php echo $row['brand_id']; ?>" <?php if ($row['brand_id'] == $product_brand) {
																							echo 'selected';
																						} ?>><?php echo $row['brand_name']; ?></option>
									<?php
									}
									?>
									<option value="others" <?php if ($product_brand == 'others') echo 'selected'; ?>>Others</option>
								</select>
							</div>
						</div>
						<div class="form-group other-brand-group" style="display: none;">
							<label for="" class="col-sm-3 control-label">Specify Brand Name <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="other_brand" class="form-control" value="<?php echo isset($_POST['other_brand']) ? htmlspecialchars($_POST['other_brand']) : ''; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">HSN Code <span>*</span><br></label>
							<div class="col-sm-4">
								<input type="text" name="hsn_code"
									value="<?php echo isset($_POST['hsn_code']) ? htmlspecialchars($_POST['hsn_code']) : $hsn_code; ?>"
									required class="form-control" maxlength="8" placeholder="8 digit code">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">GST % <span>*</span><br></label>
							<div class="col-sm-4">
								<input type="text" name="gst_percentage"
									value="<?php echo isset($_POST['gst_percentage']) ? htmlspecialchars($_POST['gst_percentage']) : $gst_percentage; ?>"
									required class="form-control" placeholder="Max upto 18%">
							</div>
						</div>



						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Existing Featured Photo</label>
							<div class="col-sm-4" style="padding-top:4px;">
								<img src="../assets/uploads/product-photos/<?php echo $p_featured_photo; ?>" alt="" style="width:150px;">
								<input type="hidden" name="current_photo" value="<?php echo $p_featured_photo; ?>">
							</div>
						</div>

						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Change Featured Photo </label>
							<div class="col-sm-4" style="padding-top:4px;">
								<input type="file" name="p_featured_photo">
							</div>
						</div>

						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Change Product Catalogue</label>
							<div class="col-sm-4" style="padding-top:4px;">
								<input type="file" name="product_catalogue">
							</div>
						</div>

						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Old Price<br><span style="font-size:10px;font-weight:normal;">(In INR)</span></label>
							<div class="col-sm-4">
								<input type="text" name="p_old_price" class="form-control" value="<?php echo $p_old_price; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Current Price <span>*</span><br><span style="font-size:10px;font-weight:normal;">(In INR)</span></label>
							<div class="col-sm-4">
								<input type="text" name="p_current_price" class="form-control" value="<?php echo $p_current_price; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Quantity <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" name="p_qty" class="form-control" value="<?php echo $p_qty; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Other Photos</label>
							<div class="col-sm-4" style="padding-top:4px;">
								<table id="ProductTable" style="width:100%;">
									<tbody>
										<?php
										$statement = $pdo->prepare("SELECT * FROM tbl_product_photo WHERE p_id=?");
										$statement->execute(array($_REQUEST['id']));
										$result = $statement->fetchAll(PDO::FETCH_ASSOC);
										foreach ($result as $row) {
										?>
											<tr>
												<td>
													<img src="../assets/uploads/product-photos/<?php echo $row['photo']; ?>" alt="<?php echo $row['photo'] ?>" style="width:60px;">
												</td>
												<td style="width:28px;">
													<a onclick="return confirmDelete();" href="product-other-photo-delete.php?id=<?php echo $row['pp_id']; ?>&id1=<?php echo $_REQUEST['id']; ?>" class="btn btn-danger btn-xs">X</a>
												</td>
											</tr>
										<?php
										}
										?>
									</tbody>
								</table>
							</div>
							<div class="col-sm-2">
								<input type="button" id="btnAddNew" value="Add Item" style="margin-top: 5px;margin-bottom:10px;border:0;color: #fff;font-size: 14px;border-radius:3px;" class="btn btn-warning btn-xs">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Description</label>
							<div class="col-sm-8">
								<textarea name="p_description" class="form-control"><?php echo htmlspecialchars($p_description); ?></textarea>
							</div>
						</div>
						<!-- <div class="form-group">
							<label for="" class="col-sm-3 control-label">Short Description</label>
							<div class="col-sm-8">
								<textarea name="p_short_description" class="form-control" cols="30" rows="10" id="editor1"><?php echo $p_short_description; ?></textarea>
							</div>
						</div> -->
						<!-- <div class="form-group">
							<label for="" class="col-sm-3 control-label">Features</label>
							<div class="col-sm-8">
								<textarea name="p_feature" class="form-control" ><?php echo $p_feature; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Conditions</label>
							<div class="col-sm-8">
								<textarea name="p_condition" class="form-control" ><?php echo $p_condition; ?></textarea>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-3 control-label">Return Policy</label>
							<div class="col-sm-8">
								<textarea name="p_return_policy" class="form-control" ><?php echo $p_return_policy; ?></textarea>
							</div>
						</div> -->
						<!-- <div class="form-group">
							<label for="" class="col-sm-3 control-label">Is Featured?</label>
							<div class="col-sm-8">
								<select name="p_is_featured" class="form-control" style="width:auto;">
									<option value="0" <?php if ($p_is_featured == '0') {
															echo 'selected';
														} ?>>No</option>
									<option value="1" <?php if ($p_is_featured == '1') {
															echo 'selected';
														} ?>>Yes</option>
								</select> 
							</div>
						</div> -->
						<!-- <div class="form-group">
							<label for="" class="col-sm-3 control-label">Is Active?</label>
							<div class="col-sm-8">
								<select name="p_is_active" class="form-control" style="width:auto;">
									<option value="0" <?php if ($p_is_active == '0') {
															echo 'selected';
														} ?>>No</option>
									<option value="1" <?php if ($p_is_active == '1') {
															echo 'selected';
														} ?>>Yes</option>
								</select> 
							</div>
						</div> -->
						<div class="form-group">
							<label for="" class="col-sm-3 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Update</button>
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
    // Initialize Select2
    $('.select2').select2({
        width: '100%',
        closeOnSelect: true
    });

    // Toggle other brand input
    $('.brand-select').on('change', function() {
        if ($(this).val() === 'others') {
            $('.other-brand-group').show();
        } else {
            $('.other-brand-group').hide();
            $('input[name="other_brand"]').val('');
        }
    });

    // Check initial state
    if ($('.brand-select').val() === 'others') {
        $('.other-brand-group').show();
    }

    // When top category changes
    $(document).on('change', '.top-cat', function(e) {
        var tcat_id = $(this).val();
        var $midCat = $('.mid-cat');
        var $endCat = $('.end-cat');

        $.ajax({
            url: "get_mid_category.php",
            type: "POST",
            data: { id: tcat_id },
            success: function(response) {
                $midCat.select2('destroy');
                $midCat.html(response);
                $midCat.prop('disabled', false);
                $midCat.select2({ width: '100%' });

                // Reset end category
                $endCat.select2('destroy');
                $endCat.html('<option value="">Select End Level Category</option>');
                $endCat.prop('disabled', true);
                $endCat.select2({ width: '100%' });
            }
        });
    });

    // When mid category changes
    $(document).on('change', '.mid-cat', function(e) {
        var mid_cat_id = $(this).val();
        var $endCat = $('.end-cat');

        $.ajax({
            url: "get_end_category.php",
            type: "POST",
            data: { id: mid_cat_id },
            success: function(response) {
                $endCat.select2('destroy');
                $endCat.html(response);
                var hasOptions = $endCat.find('option[value!=""]').length > 0;
                $endCat.prop('disabled', !hasOptions);
                $endCat.select2({ width: '100%' });
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
<?php require_once('footer.php'); ?>
