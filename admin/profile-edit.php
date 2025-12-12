<?php require_once('header.php'); ?>

<?php
if(isset($_POST['form1'])) {

	if($_SESSION['admin_session']['user_role'] == 'admin') {

		$valid = 1;

	    if(empty($_POST['user_name'])) {
	        $valid = 0;
	        $error_message .= "Name can not be empty<br>";
	    }

	    if(empty($_POST['user_email'])) {
	        $valid = 0;
	        $error_message .= 'Email address can not be empty<br>';
	    } else {
	    	if (filter_var($_POST['user_email'], FILTER_VALIDATE_EMAIL) === false) {
		        $valid = 0;
		        $error_message .= 'Email address must be valid<br>';
		    } else {
		    	// current email address that is in the database
		    	$statement = $pdo->prepare("SELECT * FROM user_login WHERE id=?");
				$statement->execute(array($_SESSION['admin_session']['id']));
				$result = $statement->fetchAll(PDO::FETCH_ASSOC);
				foreach($result as $row) {
					$current_email = $row['user_email'];
				}

		    	$statement = $pdo->prepare("SELECT * FROM user_login WHERE user_email=? and user_email!=?");
		    	$statement->execute(array($_POST['user_email'],$current_email));
		    	$total = $statement->rowCount();							
		    	if($total) {
		    		$valid = 0;
		        	$error_message .= 'Email address already exists<br>';
		    	}
		    }
	    }

	    if($valid == 1) {
			
			$_SESSION['admin_session']['user_name'] = $_POST['user_name'];
	    	$_SESSION['admin_session']['user_email'] = $_POST['user_email'];

			// updating the database
			$statement = $pdo->prepare("UPDATE user_login SET user_name=?, user_email=?, user_phone=? WHERE id=?");
			$statement->execute(array($_POST['user_name'],$_POST['user_email'],$_POST['user_phone'],$_SESSION['admin_session']['id']));

	    	$success_message = 'User Information is updated successfully.';
	    }
	}
	else {
		$_SESSION['admin_session']['user_phone'] = $_POST['user_phone'];

		// updating the database
		$statement = $pdo->prepare("UPDATE user_login SET user_phone=? WHERE id=?");
		$statement->execute(array($_POST['user_phone'],$_SESSION['admin_session']['id']));

		$success_message = 'User Information is updated successfully.';	
	}
}

if(isset($_POST['form2'])) {

	$valid = 1;

	$path = $_FILES['user_photo']['name'];

    if($path!='') {
        $ext = pathinfo( $path, PATHINFO_EXTENSION );
        if( $ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
    	// Remove the existing photo
    	if($_SESSION['admin_session']['user_photo']!='') {
    		unlink('../assets/uploads/profile-pictures/'.$_SESSION['admin_session']['user_photo']);	
    	}
    	
    	// Use FileOptimizer to process and optimize the image (will convert to WebP)
    	require_once '../includes/file_optimizer.php';
    	$final_name = 'user-'.$_SESSION['admin_session']['id'].'.'.$ext;
    	$uploadDir = '../assets/uploads/profile-pictures/';
    	
    	$optimizedFilename = FileOptimizer::processUploadedFile($_FILES['user_photo'], $uploadDir, $final_name);
    	
    	if ($optimizedFilename) {
    		$_SESSION['admin_session']['user_photo'] = $optimizedFilename;
    		
    		// updating the database
			$statement = $pdo->prepare("UPDATE user_login SET user_photo=? WHERE id=?");
			$statement->execute(array($optimizedFilename,$_SESSION['admin_session']['id']));

        	$success_message = 'User Photo is updated successfully.';
    	} else {
    		$error_message .= 'Failed to upload photo<br>';
    	}
    }
}

if(isset($_POST['form3'])) {
	$valid = 1;
	if(empty($_POST['current_password'])) {
		$valid = 0;
		$error_message .= "Current Password can not be empty<br>";
	} else {
		$statement = $pdo->prepare("SELECT * FROM user_login WHERE id=?");
		$statement->execute(array($_SESSION['admin_session']['id']));
		$result = $statement->fetch(PDO::FETCH_ASSOC);
		if(!password_verify($_POST['current_password'], $result['user_password'])) {
			$valid = 0;
			$error_message .= "Current Password is incorrect<br>";
		}
	}
	if( empty($_POST['user_password']) || empty($_POST['re_password']) ) {
        $valid = 0;
        $error_message .= "Password can not be empty<br>";
    }

    if( !empty($_POST['user_password']) && !empty($_POST['re_password']) ) {
    	if($_POST['user_password'] != $_POST['re_password']) {
	    	$valid = 0;
	        $error_message .= "Passwords do not match<br>";	
    	}        
    }
	// Add password strength validation
	if(strlen($_POST['password']) < 8) {
		$valid = 0;
		$error_message .= "Password must be at least 8 characters long<br>";
	}

    if($valid == 1) {

    	$_SESSION['admin_session']['user_password'] = password_hash($_POST['user_password'],PASSWORD_DEFAULT);

    	// updating the database
		$statement = $pdo->prepare("UPDATE user_login SET user_password=? WHERE id=?");
		$statement->execute(array(password_hash($_POST['user_password'],PASSWORD_DEFAULT) ,$_SESSION['admin_session']['id']));

    	$success_message = 'User Password is updated successfully.';
    }
}
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Edit Profile</h1>
	</div>
</section>

<?php
$statement = $pdo->prepare("SELECT * FROM user_login WHERE id=?");
$statement->execute(array($_SESSION['admin_session']['id']));
$statement->rowCount();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
foreach ($result as $row) {
	$full_name = $row['user_name'];
	$email     = $row['user_email'];
	$phone     = $row['user_phone'];
	$photo     = $row['user_photo'];
	$role      = $row['user_role'];
}
?>


<section class="content">

	<div class="row">
		<div class="col-md-12">
			<?php if($error_message): ?>
			<div class="callout callout-danger alert-box">
				<p>
					<?php echo $error_message; ?>
				</p>
			</div>
			<?php endif; ?>

			<?php if($success_message): ?>
			<div class="callout callout-success alert-box">
				<p><?php echo $success_message; ?></p>
			</div>
			<?php endif; ?>
				
				<div class="nav-tabs-custom">
					<ul class="nav nav-tabs">
						<li class="active"><a href="#tab_1" data-toggle="tab">Update Information</a></li>
						<li><a href="#tab_2" data-toggle="tab">Update Photo</a></li>
						<li><a href="#tab_3" data-toggle="tab">Update Password</a></li>
					</ul>
					<div class="tab-content">
          				<div class="tab-pane active" id="tab_1">
							
							<form class="form-horizontal" action="" method="post">
							<div class="box box-info">
								<div class="box-body">
									<div class="form-group">
										<label for="" class="col-sm-2 control-label">Name <span>*</span></label>
										<?php
										if($_SESSION['admin_session']['user_role'] == 'admin') {
											?>
												<div class="col-sm-4">
													<input type="text" class="form-control" name="user_name" value="<?php echo $full_name; ?>">
												</div>
											<?php
										} else {
											?>
												<div class="col-sm-4" style="padding-top:7px;">
													<?php echo $full_name; ?>
												</div>
											<?php
										}
										?>
										
									</div>
									<div class="form-group">
							            <label for="" class="col-sm-2 control-label">Existing Photo</label>
							            <div class="col-sm-6" style="padding-top:6px;">
							                <img src="../assets/uploads/profile-pictures/<?php echo $photo; ?>" class="existing-photo" width="140">
							            </div>
							        </div>
									
									<div class="form-group">
										<label for="" class="col-sm-2 control-label">Email Address <span>*</span></label>
										<?php
										if($_SESSION['admin_session']['user_role'] == 'admin') {
											?>
												<div class="col-sm-4">
													<input type="email" class="form-control" name="user_email" value="<?php echo $email; ?>">
												</div>
											<?php
										} else {
											?>
											<div class="col-sm-4" style="padding-top:7px;">
												<?php echo $email; ?>
											</div>
											<?php
										}
										?>
										
									</div>
									<div class="form-group">
										<label for="" class="col-sm-2 control-label">Phone </label>
										<div class="col-sm-4">
											<input type="text" class="form-control" name="user_phone" value="<?php echo $phone; ?>">
										</div>
									</div>
									<div class="form-group">
										<label for="" class="col-sm-2 control-label">Role <span>*</span></label>
										<div class="col-sm-4" style="padding-top:7px;">
											<?php echo $role; ?>
										</div>
									</div>
									<div class="form-group">
										<label for="" class="col-sm-2 control-label"></label>
										<div class="col-sm-6">
											<button type="submit" class="btn btn-success pull-left" name="form1">Update Information</button>
										</div>
									</div>
								</div>
							</div>
							</form>
          				</div>
          				<div class="tab-pane" id="tab_2">
							<form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
							<div class="box box-info">
								<div class="box-body">
									<div class="form-group">
							            <label for="" class="col-sm-2 control-label">New Photo</label>
							            <div class="col-sm-6" style="padding-top:6px;">
							                <input type="file" name="user_photo">
							            </div>
							        </div>
							        <div class="form-group">
										<label for="" class="col-sm-2 control-label"></label>
										<div class="col-sm-6">
											<button type="submit" class="btn btn-success pull-left" name="form2">Update Photo</button>
										</div>
									</div>
								</div>
							</div>
							</form>
          				</div>
          				<div class="tab-pane" id="tab_3">
							<form class="form-horizontal" action="" method="post">
							<div class="box box-info">
								<div class="box-body">
									<div class="form-group">
										<label for="" class="col-sm-2 control-label">Current Password </label>
										<div class="col-sm-4">
											<input type="password" class="form-control" name="current_password">
										</div>
									</div>

									<div class="form-group">
										<label for="" class="col-sm-2 control-label">Password </label>
										<div class="col-sm-4">
											<input type="password" class="form-control" name="user_password">
										</div>
									</div>
									<div class="form-group">
										<label for="" class="col-sm-2 control-label">Retype Password </label>
										<div class="col-sm-4">
											<input type="password" class="form-control" name="re_password">
										</div>
									</div>
							        <div class="form-group">
										<label for="" class="col-sm-2 control-label"></label>
										<div class="col-sm-6">
											<button type="submit" class="btn btn-success pull-left" name="form3">Update Password</button>
										</div>
									</div>
								</div>
							</div>
							</form>

          				</div>
          			</div>
				</div>			

		</div>
	</div>
</section>

<?php require_once('footer.php'); ?>