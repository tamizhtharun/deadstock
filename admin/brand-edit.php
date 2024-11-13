<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {
	$valid = 1;

	
    $path = $_FILES['photo']['name'];
    $path_tmp = $_FILES['photo']['tmp_name'];

    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

	if($valid == 1) {

		if($path == '') {
			$statement = $pdo->prepare("UPDATE tbl_brands SET brand_name=?,brand_description=? WHERE brand_id=?");
    		$statement->execute(array($_POST['brand_name'],$_POST['brand_description'],$_REQUEST['id']));
		} else {

			unlink('../assets/uploads/brand-logos/'.$_POST['current_logo']);

			$final_name = 'brand-logo-'.$_REQUEST['id'].'.'.$ext;
        	move_uploaded_file( $path_tmp, '../assets/uploads/brand-logos/'.$final_name );

        	$statement = $pdo->prepare("UPDATE tbl_brands SET brand_name=?, brand_logo=?, brand_logo=? WHERE brand_id=?");
    		$statement->execute(array($_POST['brand_name'],$_POST['brand_description'],$final_name,$_REQUEST['id']));
		}	   

	    $success_message = 'Brand updated successfully!';
	}
}
?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_brands WHERE brand_id=?");
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
		<h1>Edit Brand</h1>
	</div>
	<div class="content-header-right">
		<a href="brand-management.php" class="btn btn-primary btn-sm">View All</a>
	</div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM tbl_brands WHERE brand_id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$brand_logo     = $row['brand_logo'];
	$brand_name     = $row['brand_name'];
    $brand_description = $row['brand_description'];

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
				<input type="hidden" name="current_logo" value="<?php echo $brand_logo; ?>">
				<div class="box box-info">
					<div class="box-body">
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Existing Logo</label>
							<div class="col-sm-9" style="padding-top:5px">
								<img src="../assets/uploads/brand-logos/<?php echo $brand_logo; ?>" alt="Brand Logo" style="width:250px;">
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Brand Logo </label>
							<div class="col-sm-6" style="padding-top:5px">
								<input type="file" name="photo">(Only jpg, jpeg, gif and png are allowed)
							</div>
						</div>
						<div class="form-group">
							<label for="" class="col-sm-2 control-label">Brand Name </label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="brand_name" value="<?php echo $brand_name; ?>">
							</div>
						</div>
                        <div class="form-group">
							<label for="" class="col-sm-2 control-label">Brand Description </label>
							<div class="col-sm-6">
								<input type="text" autocomplete="off" class="form-control" name="brand_description" value="<?php echo $brand_description; ?>">
							</div>
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