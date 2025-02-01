<?php require_once('header.php'); ?>

<?php
// Initialize error and success messages
$error_message = '';
$success_message = '';
$active_tab = isset($_POST['form4']) ? 'tab_4' : 'tab_1';

    // Get seller status from database
    $statement = $pdo->prepare("SELECT seller_status FROM sellers WHERE seller_id = ?");
    $statement->execute([$seller_id]);
    $seller_status = $statement->fetchColumn();
    


    if(isset($_POST['form1'])) {
        if($_SESSION['seller_session']) {
            $valid = 1;
            $error_message = '';
            
            // Name validation
            if(empty($_POST['full_name'])) {
                $valid = 0;
                $error_message .= "Name can not be empty. ";
            }
            
            // Email validation
            if(empty($_POST['email'])) {
                $valid = 0;
                $error_message .= 'Email address can not be empty. ';
            } else {
                if (filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) === false) {
                    $valid = 0;
                    $error_message .= 'Email address must be valid. ';
                } else {
                    // Check for duplicate email
                    $statement = $pdo->prepare("SELECT * FROM sellers WHERE seller_id=?");
                    $statement->execute(array($_SESSION['seller_session']['seller_id']));
                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                    $current_email = $result[0]['seller_email'];
                    
                    $statement = $pdo->prepare("SELECT * FROM sellers WHERE seller_email=? AND seller_email!=?");
                    $statement->execute(array($_POST['email'], $current_email));
                    if($statement->rowCount() > 0) {
                        $valid = 0;
                        $error_message .= 'Email address already exists. ';
                    }
                }
            }
            
            // Phone validation
            if(empty($_POST['seller_phone'])) {
                $valid = 0;
                $error_message .= 'Phone number cannot be empty. ';
            } else {
                // Assuming Indian phone number format
                if(!preg_match('/^[6-9]\d{9}$/', $_POST['seller_phone'])) {
                    $valid = 0;
                    $error_message .= 'Invalid phone number format. Must be 10 digits starting with 6-9. ';
                }
            }
            
            // GST validation
            if(!empty($_POST['seller_gst'])) {
                // GST format: 2 digits state code + 10 digits PAN + 1 digit entity number + 1 digit check sum
                if(!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/', $_POST['seller_gst'])) {
                    $valid = 0;
                    $error_message .= 'Invalid GST format. ';
                }
            }
            
            // Zipcode validation
            if(!empty($_POST['seller_zipcode'])) {
                // Assuming Indian PIN code
                if(!preg_match('/^[1-9][0-9]{5}$/', $_POST['seller_zipcode'])) {
                    $valid = 0;
                    $error_message .= 'Invalid PIN code format. ';
                }
            }
            
            if($valid == 1) {
                try {
                    // Update session data
                    $_SESSION['seller_session']['seller_name'] = $_POST['full_name'];
                    $_SESSION['seller_session']['seller_email'] = $_POST['email'];
                    $_SESSION['seller_session']['seller_phone'] = $_POST['seller_phone'];
                    
                    // Update database
                    $statement = $pdo->prepare("UPDATE sellers SET 
                        seller_name = ?,
                        seller_cname = ?,
                        seller_email = ?,
                        seller_phone = ?,
                        seller_gst = ?,
                        seller_address = ?,
                        seller_state = ?,
                        seller_city = ?,
                        seller_zipcode = ?
                        WHERE seller_id = ?");
                    
                    $statement->execute(array(
                        $_POST['full_name'],
                        $_POST['seller_cname'],
                        $_POST['email'],
                        $_POST['seller_phone'],
                        $_POST['seller_gst'],
                        $_POST['seller_address'],
                        $_POST['seller_state'],
                        $_POST['seller_city'],
                        $_POST['seller_zipcode'],
                        $_SESSION['seller_session']['seller_id']
                    ));
                    
                    $success_message = 'User Information is updated successfully.';
                } catch(PDOException $e) {
                    $error_message = 'Database error: ' . $e->getMessage();
                }
            }
        } else {
            // Handle phone update for non-seller session
            if(!empty($_POST['phone']) && preg_match('/^[6-9]\d{9}$/', $_POST['phone'])) {
                $_SESSION['seller_session']['seller_phone'] = $_POST['phone'];
                
                $statement = $pdo->prepare("UPDATE sellers SET seller_phone=? WHERE seller_id=?");
                $statement->execute(array($_POST['phone'], $_SESSION['seller_session']['seller_id']));
                
                $success_message = 'Phone number updated successfully.';
            } else {
                $error_message = 'Invalid phone number format.';
            }
        }
    }

if(isset($_POST['form2'])) {
    $valid = 1;

    // Check if a file was actually selected
    if(empty($_FILES['photo']['name'])) {
        $valid = 0;
        $error_message .= 'Please select a photo to upload<br>';
    } else {
        $path = $_FILES['photo']['name'];
        $path_tmp = $_FILES['photo']['tmp_name'];

        $ext = pathinfo($path, PATHINFO_EXTENSION );
        $file_name = basename( $path, '.' . $ext );
        
        if($ext!='jpg' && $ext!='png' && $ext!='jpeg' && $ext!='gif' ) {
            $valid = 0;
            $error_message .= 'You must have to upload jpg, jpeg, gif or png file<br>';
        }
    }

    if($valid == 1) {
        // removing the existing photo
        // if($_SESSION['seller_session']['seller_id']!='') {
        //     unlink('../assets/uploads/profile-pictures/'.$_SESSION['seller_session']['seller_id']); 
        // }

        // updating the data
        $final_name = 'seller-'.$_SESSION['seller_session']['seller_id'].'.'.$ext;
        move_uploaded_file( $path_tmp, '../assets/uploads/profile-pictures/'.$final_name );
        $_SESSION['seller_session']['seller_photo'] = $final_name;

        // updating the database
        $statement = $pdo->prepare("UPDATE sellers SET seller_photo=? WHERE seller_id=?");
        $statement->execute(array($final_name,$_SESSION['seller_session']['seller_id']));

        $success_message = 'User Photo is updated successfully.';
    }
}

if(isset($_POST['form3'])) {
    $valid = 1;
    if(empty($_POST['current_password'])) {
		$valid = 0;
		$error_message .= "Current Password can not be empty<br>";
	} else {
		$statement = $pdo->prepare("SELECT * FROM sellers WHERE seller_id=?");
		$statement->execute(array($_SESSION['seller_session']['seller_id']));
		$result = $statement->fetch(PDO::FETCH_ASSOC);
		if(!password_verify($_POST['current_password'], $result['seller_password'])) {
			$valid = 0;
			$error_message .= "Current Password is incorrect<br>";
		}
	}
    if(empty($_POST['password']) || empty($_POST['re_password'])) {
        $valid = 0;
        $error_message .= "Password can not be empty<br>";
    }

    if(!empty($_POST['password']) && !empty($_POST['re_password'])) {
        if($_POST['password'] != $_POST['re_password']) {
            $valid = 0;
            $error_message .= "Passwords do not match<br>";  
        }
        
        // Add password strength validation
        if(strlen($_POST['password']) < 8) {
            $valid = 0;
            $error_message .= "Password must be at least 8 characters long<br>";
        }
    }

    if($valid == 1) {
        // Generate secure password hash
        $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Update session with new password hash
        $_SESSION['seller_session']['seller_password'] = $password_hash;

        // Update database with new password hash
        $statement = $pdo->prepare("UPDATE sellers SET seller_password=? WHERE seller_id=?");
        $statement->execute(array(
            $password_hash,
            $_SESSION['seller_session']['seller_id']
        ));

        $success_message = 'Password has been updated successfully.';
    }
}

// Fetch all brands from tbl_brands
$statement = $pdo->prepare("SELECT * FROM tbl_brands");
$statement->execute();
$brands = $statement->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission for brands
if(isset($_POST['form4'])) {
    $valid = 1;
    
    // Validate brand selection
    if(empty($_POST['brand_id'])) {
        $valid = 0;
        $error_message .= "Please select a brand<br>";
    }

    // Validate certificate upload
    if(empty($_FILES['brand_certificate']['name'])) {
        $valid = 0;
        $error_message .= "Certificate is required<br>";
    } else {
        $certificate_name = $_FILES['brand_certificate']['name'];
        $certificate_tmp = $_FILES['brand_certificate']['tmp_name'];
        $ext = strtolower(pathinfo($certificate_name, PATHINFO_EXTENSION));

        if($ext != 'pdf') {
            $valid = 0;
            $error_message .= "Only PDF files are allowed for certificates<br>";
        }
    }

    // Validate valid until date
    if(empty($_POST['valid_to'])) {
        $valid = 0;
        $error_message .= "Valid until date is required<br>";
    }

    if($valid == 1) {
        try {
            // Generate unique filename
            $final_name = 'certificate-'.$_SESSION['seller_session']['seller_id'].'-'.$_POST['brand_id'].'-'.time().'.pdf';
            
            // Move uploaded file
            if(move_uploaded_file($certificate_tmp, '../assets/uploads/certificates/'.$final_name)) {
                // Check if brand already exists for seller
                $statement = $pdo->prepare("SELECT * FROM seller_brands WHERE seller_id = ? AND brand_id = ?");
                $statement->execute(array($_SESSION['seller_session']['seller_id'], $_POST['brand_id']));
                
                if($statement->rowCount() > 0) {
                    // Update existing record
                    $statement = $pdo->prepare("UPDATE seller_brands SET 
                        brand_certificate = ?, 
                        valid_to = ? 
                        WHERE seller_id = ? AND brand_id = ?");
                    $statement->execute(array(
                        $final_name,
                        $_POST['valid_to'],
                        $_SESSION['seller_session']['seller_id'],
                        $_POST['brand_id']
                    ));
                } else {
                    // Insert new record
                    $statement = $pdo->prepare("INSERT INTO seller_brands (seller_id, brand_id, brand_certificate, valid_to) VALUES (?, ?, ?, ?)");
                    $statement->execute(array(
                        $_SESSION['seller_session']['seller_id'],
                        $_POST['brand_id'],
                        $final_name,
                        $_POST['valid_to']
                    ));
                }
                $success_message = 'Brand certificate has been added successfully.';
            } else {
                $error_message .= "Error uploading file. Please try again.<br>";
            }
        } catch(PDOException $e) {
            $error_message .= "Database error: " . $e->getMessage() . "<br>";
        }
    }
}

// Fetch existing seller brands
$statement = $pdo->prepare("SELECT sb.*, b.brand_name 
                          FROM seller_brands sb 
                          JOIN tbl_brands b ON sb.brand_id = b.brand_id 
                          WHERE sb.seller_id = ?");
$statement->execute(array($_SESSION['seller_session']['seller_id']));
$seller_brands = $statement->fetchAll(PDO::FETCH_ASSOC);

// Fetch seller information
$statement = $pdo->prepare("SELECT * FROM sellers WHERE seller_id=?");
$statement->execute(array($_SESSION['seller_session']['seller_id']));
$statement->rowCount();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);             
foreach ($result as $row) {
    $full_name = $row['seller_name'];
    $email     = $row['seller_email'];
    $phone     = $row['seller_phone'];
    $address   = $row['seller_address'];
    $state    = $row['seller_state'];
    $city    = $row['seller_city'];
    $zipcode    = $row['seller_zipcode'];
    $gst    = $row['seller_gst'];
    $cname    = $row['seller_cname'];
    $status    = $row['seller_status'];
    $role      = 'Seller';
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Edit Profile</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if($seller_status == 0): ?>
                <div class="callout callout-info">Complete your profile to get Started</div>
            <?php endif; ?>

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

            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="<?php echo ($active_tab == 'tab_1') ? 'active' : ''; ?>">
                        <a href="#tab_1" data-toggle="tab">Update Information</a>
                    </li>
                    <li class="<?php echo ($active_tab == 'tab_2') ? 'active' : ''; ?>">
                        <a href="#tab_2" data-toggle="tab">Update Photo</a>
                    </li>
                    <li class="<?php echo ($active_tab == 'tab_3') ? 'active' : ''; ?>">
                        <a href="#tab_3" data-toggle="tab">Update Password</a>
                    </li>
                    <li class="<?php echo ($active_tab == 'tab_4') ? 'active' : ''; ?>">
                        <a href="#tab_4" data-toggle="tab">Brands</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane <?php echo ($active_tab == 'tab_1') ? 'active' : ''; ?>" id="tab_1">
                        <form class="form-horizontal" action="" method="post">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Name <span>*</span></label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" name="full_name" value="<?php echo $full_name; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Company Name <span>*</span></label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" name="seller_cname" value="<?php echo $cname; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">GST Number <span>*</span></label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" name="seller_gst" value="<?php echo $gst; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Email Address <span>*</span></label>
                                        <div class="col-sm-4">
                                            <input type="email" class="form-control" name="email" value="<?php echo $email; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Phone </label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" name="seller_phone" value="<?php echo $phone; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Address </label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" name="seller_address" value="<?php echo $address; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">City </label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" name="seller_city" value="<?php echo $city; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">State </label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" name="seller_state" value="<?php echo $state; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">Pincode </label>
                                        <div class="col-sm-4">
                                            <input type="text" class="form-control" name="seller_zipcode" value="<?php echo $zipcode; ?>">
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
                    <div class="tab-pane <?php echo ($active_tab == 'tab_2') ? 'active' : ''; ?>" id="tab_2">
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
									<label for="" class="col-sm-2 control-label">Existing Photo</label>
							            <div class="col-sm-6" style="padding-top:6px;">
							                <img src="../assets/uploads/profile-pictures/<?php echo $_SESSION['seller_session']['seller_photo']; ?>" class="existing-photo" width="140">
							            </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="" class="col-sm-2 control-label">New Photo</label>
                                        <div class="col-sm-6" style="padding-top:6px;">
                                            <input type="file" name="photo">
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
                    <div class="tab-pane <?php echo ($active_tab == 'tab_3') ? 'active' : ''; ?>" id="tab_3">
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
                                            <input type="password" class="form-control" name="password">
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
					<div class="tab-pane <?php echo ($active_tab == 'tab_4') ? 'active' : ''; ?>" id="tab_4">
                        <!-- Display error/success messages only if form4 was submitted -->
                        
                        <!-- Form to add new brand -->
                        <form class="form-horizontal" action="" method="post" enctype="multipart/form-data">
                            <div class="box box-info">
                                <div class="box-body">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Select Brand *</label>
                                        <div class="col-sm-4">
                                            <select name="brand_id" class="form-control">
                                                <option value="">Select a brand</option>
                                                <?php foreach($brands as $brand): ?>
                                                    <option value="<?php echo $brand['brand_id']; ?>">
                                                        <?php echo $brand['brand_name']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Certificate (PDF) *</label>
                                        <div class="col-sm-4">
                                            <input type="file" name="brand_certificate" class="form-control" accept=".pdf">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">Valid Until *</label>
                                        <div class="col-sm-4">
                                            <input type="date" name="valid_to" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>
                                        <div class="col-sm-6">
                                            <button type="submit" class="btn btn-success" name="form4">Add Brand</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <!-- Table showing existing brand certificates -->
                        <div class="box box-info">
                            <div class="box-body table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Brand Name</th>
                                            <th>Certificate</th>
                                            <th>Valid Until</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($seller_brands as $key => $brand): ?>
                                            <tr>
                                                <td><?php echo $key+1; ?></td>
                                                <td><?php echo $brand['brand_name']; ?></td>
                                                <td>
                                                    <a href="../assets/uploads/certificates/<?php echo $brand['brand_certificate']; ?>" target="_blank">
                                                        View Certificate
                                                    </a>
                                                </td>
                                                <td><?php echo date('d M, Y', strtotime($brand['valid_to'])); ?></td>
                                                <td>
                                                    <a href="seller-brand-delete.php?id=<?php echo $brand['brand_id']; ?>" 
                                                       class="btn btn-danger btn-xs" 
                                                       onclick="return confirm('Are you sure?');">Delete</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>