<?php require_once('header.php'); ?>

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
    } else {
        $valid = 0;
        $error_message .= 'You must have to select a photo<br>';
    }

    // Check if tcat_id is valid
    if(isset($_POST['tcat_id']) && !empty($_POST['tcat_id'])) {
        $statement = $pdo->prepare("SELECT tcat_id FROM tbl_top_category WHERE tcat_id=?");
        $statement->execute([$_POST['tcat_id']]);
        if($statement->rowCount() == 0) {
            $valid = 0;
            $error_message .= 'Invalid category selected<br>';
        }
    } else {
        $valid = 0;
        $error_message .= 'You must have to select a category<br>';
    }

	if($valid == 1) {
		$final_name = 'advertisement-'.time().'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/advertisements/'.$final_name );

        $statement = $pdo->prepare("INSERT INTO tbl_advertisements (title,photo,tcat_id,status) VALUES (?,?,?,?)");
        $statement->execute(array($_POST['title'],$final_name, $_POST['tcat_id'] ?? '', $_POST['status']));

	    $success_message = 'Advertisement is added successfully!';
	}
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Add Advertisement</h1>
	</div>
	<div class="content-header-right">
		<a href="advertisement.php" class="btn btn-primary btn-sm">View All</a>
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
							<label for="" class="col-sm-2 control-label">Title <span>*</span></label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="title" required>
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Photo <span>*</span></label>
							<div class="col-sm-6" style="padding-top:5px">
								<input type="file" name="photo" required>(Only jpg, jpeg, gif and png are allowed)
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
									foreach ($result as $row) {
										?>
										<option value="<?php echo $row['tcat_id']; ?>"><?php echo $row['tcat_name']; ?></option>
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
									<option value="1">Active</option>
									<option value="0">Inactive</option>
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
