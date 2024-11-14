<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	$path = $_FILES['photo']['name'];
	$path_tmp = $_FILES['photo']['tmp_name'];
	
	if($path != '') {
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		$file_name = basename($path, '.' . $ext);
		if($ext != 'jpg' && $ext != 'png' && $ext != 'jpeg' && $ext != 'gif') {
			$valid = 0;
			$error_message .= 'You must have to upload jpg, jpeg, gif, or png file<br>';
		}
	}
	
	if(empty($_POST['tcat_name'])) {
		$valid = 0;
		$error_message .= "Top Category Name cannot be empty<br>";
	} else {
		// Duplicate Top Category checking
		// current Top Category name that is in the database
		$statement = $pdo->prepare("SELECT * FROM tbl_top_category WHERE tcat_id=?");
		$statement->execute(array($_REQUEST['id']));
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);
		foreach($result as $row) {
			$current_tcat_name = $row['tcat_name'];
		}

		$statement = $pdo->prepare("SELECT * FROM tbl_top_category WHERE tcat_name=? AND tcat_name!=?");
		$statement->execute(array($_POST['tcat_name'], $current_tcat_name));
		$total = $statement->rowCount();							
		if($total) {
			$valid = 0;
			$error_message .= 'Top Category name already exists<br>';
		}
	}

	if($valid == 1) {
		// Update category details in the database
		$statement = $pdo->prepare("UPDATE tbl_top_category SET tcat_name=?, show_on_menu=? WHERE tcat_id=?");
		$statement->execute(array($_POST['tcat_name'], $_POST['show_on_menu'], $_REQUEST['id']));
		
		// Handle photo upload
		if($path != '') {
			// Get the current photo to delete
			$statement = $pdo->prepare("SELECT * FROM tbl_top_category WHERE tcat_id=?");
			$statement->execute(array($_REQUEST['id']));
			$result = $statement->fetchAll(PDO::FETCH_ASSOC);
			foreach($result as $row) {
				$current_photo = $row['photo'];
			}

			// Remove the current photo if it exists
			if($current_photo != '') {
				unlink('../assets/uploads/top-categories-images/' . $current_photo);
			}

			// Upload the new photo
			$final_name = 'top-category-image' . $_REQUEST['id'] . '.' . $ext;
			move_uploaded_file($path_tmp, '../assets/uploads/top-categories-images/' . $final_name);

			// Update the database with the new photo name
			$statement = $pdo->prepare("UPDATE tbl_top_category SET photo=? WHERE tcat_id=?");
			$statement->execute(array($final_name, $_REQUEST['id']));
		}

		$success_message = 'Top Category is updated successfully.';
	}
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check if the id is valid
	$statement = $pdo->prepare("SELECT * FROM tbl_top_category WHERE tcat_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if($total == 0) {
		header('location: logout.php');
		exit;
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Edit Top Level Category</h1>
	</div>
	<div class="content-header-right">
		<a href="top-category.php" class="btn btn-primary btn-sm">View All</a>
	</div>
</section>

<?php
foreach ($result as $row) {
	$tcat_name = $row['tcat_name'];
	$show_on_menu = $row['show_on_menu'];
	$photo = $row['photo'];
}
?>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<?php if($error_message): ?>
				<div class="callout callout-danger">
					<p><?php echo $error_message; ?></p>
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
								<input type="text" class="form-control" name="tcat_name" value="<?php echo $tcat_name; ?>">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Existing Photo</label>
							<div class="col-sm-9" style="padding-top:5px">
								<img src="../assets/uploads/top-categories-images/<?php echo $photo; ?>" alt="Top Category Photo" style="width:400px;">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Photo</label>
							<div class="col-sm-6" style="padding-top:5px">
								<input type="file" name="photo">(Only jpg, jpeg, gif, and png are allowed)
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Show on Menu? <span>*</span></label>
							<div class="col-sm-4">
								<select name="show_on_menu" class="form-control" style="width:auto;">
									<option value="0" <?php if($show_on_menu == 0) {echo 'selected';} ?>>No</option>
									<option value="1" <?php if($show_on_menu == 1) {echo 'selected';} ?>>Yes</option>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label"></label>
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

<?php require_once('footer.php'); ?>
