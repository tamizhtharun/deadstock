<?php require_once('header.php'); ?>
<?php require_once('../includes/file_optimizer.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

	if($valid == 1) {

		if($path == '') {
			$statement = $pdo->prepare("UPDATE tbl_advertisements SET title=?, tcat_id=?, status=? WHERE id=?");
    		$statement->execute(array($_POST['title'], $_POST['tcat_id'] ?? '', $_POST['status'],$_REQUEST['id']));
		    $success_message = 'Advertisement is updated successfully!';
		} else {

			unlink('../assets/uploads/advertisements/'.$_POST['current_photo']);

			$final_name = 'advertisement-'.time().'.'.$ext;
        	$optimized_filename = FileOptimizer::processUploadedFile($_FILES['photo'], '../assets/uploads/advertisements/', $final_name);
        	if ($optimized_filename === false) {
        		$valid = 0;
        		$error_message .= 'Failed to optimize and upload the photo<br>';
        	} else {
        		$final_name = $optimized_filename;
        		$statement = $pdo->prepare("UPDATE tbl_advertisements SET title=?, photo=?, tcat_id=?, status=? WHERE id=?");
        		$statement->execute(array($_POST['title'],$final_name, $_POST['tcat_id'] ?? '', $_POST['status'],$_REQUEST['id']));
        		$success_message = 'Advertisement is updated successfully!';
        	}
		}
	}
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_advertisements WHERE id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Edit Advertisement</h1>
	</div>
	<div class="content-header-right">
		<a href="advertisement.php" class="btn btn-primary btn-sm">View All</a>
	</div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_advertisements WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$title       = $row['title'];
	$photo       = $row['photo'];
	$tcat_id     = $row['tcat_id'];
	$status      = $row['status'];
}
?>

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
				<input type="hidden" name="current_photo" value="<?php echo $photo; ?>">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Existing Photo</label>
							<div class="col-sm-9" style="padding-top:5px">
								<img src="../assets/uploads/advertisements/<?php echo $photo; ?>" alt="Advertisement Photo" class="responsive-img">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Title <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="title" value="<?php echo $title; ?>" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Photo </label>
							<div class="col-sm-6" style="padding-top:5px">
								<input type="file" name="photo">(Only jpg, jpeg, gif and png are allowed)
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Category <span>*</span></label>
							<div class="col-sm-6">
								<select name="tcat_id" class="form-control" required>
									<option value="">Select Category</option>
									<?php
									$statement = $pdo->prepare("SELECT * FROM tbl_top_category WHERE show_on_menu=1 ORDER BY tcat_name ASC");
									$statement->execute();
									$result = $statement->fetchAll(PDO::FETCH_ASSOC);
									foreach ($result as $row_cat) {
										?>
										<option value="<?php echo $row_cat['tcat_id']; ?>" <?php if($row_cat['tcat_id'] == $tcat_id) {echo 'selected';} ?>><?php echo $row_cat['tcat_name']; ?></option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Status </label>
							<div class="col-sm-6">
								<select name="status" class="form-control">
									<option value="1" <?php if($status == 1) {echo 'selected';} ?>>Active</option>
									<option value="0" <?php if($status == 0) {echo 'selected';} ?>>Inactive</option>
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
