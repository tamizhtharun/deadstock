<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;
	$error_message = '';

	// Check if a file was uploaded
	if(isset($_FILES['photo']) && $_FILES['photo']['name'] != '') {
		$path = $_FILES['photo']['name'];
		$path_tmp = $_FILES['photo']['tmp_name'];
		$ext = pathinfo($path, PATHINFO_EXTENSION);

		// Validate file extension
		if(!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
			$valid = 0;
			$error_message .= 'You must upload a file in jpg, jpeg, gif, or png format.<br>';
		}
	} else {
		$valid = 0;
		$error_message .= 'You must select a photo.<br>';
	}

	// Additional validations
	if(empty($_POST['tcat_name'])) {
		$valid = 0;
		$error_message .= "Top Category Name cannot be empty.<br>";
	} else {
		// Duplicate category check
		$statement = $pdo->prepare("SELECT * FROM tbl_top_category WHERE tcat_name=?");
		$statement->execute([$_POST['tcat_name']]);
		$total = $statement->rowCount();
		if($total) {
			$valid = 0;
			$error_message .= "Top Category Name already exists.<br>";
		}
	}

	// If validation passes
	if($valid == 1) {
		// Get auto increment id for naming the file
		$statement = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_top_category'");
		$statement->execute();
		$result = $statement->fetch();
		$ai_id = $result['Auto_increment'];

		$final_name = 'top-category-image' . $ai_id . '.' . $ext;
		move_uploaded_file($path_tmp, '../assets/uploads/top-categories-images/' . $final_name);

		// Insert into the database
		$statement = $pdo->prepare("INSERT INTO tbl_top_category (tcat_name, show_on_menu, photo) VALUES (:tcat_name, :show_on_menu, :photo)");
		$statement->execute([
			':tcat_name' => $_POST['tcat_name'],
			':show_on_menu' => $_POST['show_on_menu'],
			':photo' => $final_name
		]);

		$success_message = 'Top Category is added successfully.';
	}
}
?>


<section class="content-header">
	<div class="content-header-left">
		<h1>Add Top Level Category</h1>
	</div>
	<div class="content-header-right">
		<a href="top-category.php" class="btn btn-primary btn-sm">View All</a>
	</div>
</section>


<section class="content">

	<div class="row">
		<div class="col-md-12">

			<?php if($error_message): ?>
			<div class="callout callout-danger">
			
			<p>
			<?php echo $error_message; ?>
			</p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success">
			
			<p><?php echo $success_message; ?></p>
			</div>
			<?php endif; ?>

			<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Top Category Name <span>*</span></label>
							<div class="col-sm-4">
								<input type="text" class="form-control" name="tcat_name">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Photo <span>*</span></label>
							<div class="col-sm-9" style="padding-top:5px">
								<input type="file" name="photo">(Only jpg, jpeg, gif and png are allowed)
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Show on Menu? <span>*</span></label>
							<div class="col-sm-4">
								<select name="show_on_menu" class="form-control" style="width:auto;">
									<option value="0">No</option>
									<option value="1">Yes</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
							<div class="col-sm-6">
								<button type="submit" class="btn btn-success pull-left" name="form1">Submit</button>
							</div>
						</div>
					</div>
				</div>

			</form>


		</div>
	</div>

</section>

<?php require_once('footer.php'); ?>