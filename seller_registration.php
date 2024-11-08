<?php include 'header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Registration</title>
    <!-- <link rel="stylesheet" href="./css/seller_registration.css"> -->
</head>
<body>
<div class="seller_reg_form" style="max-width: 600px; margin: auto;">
<div class="modal-dialog modal-dialog-centered">
<div class="modal-content">
        <div class="modal-header">
          <!-- <h1 class="modal-title fs-5" id="staticBackdropLabel">Dead Stock</h1> -->
          <!-- <button type="button" id="btn-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
        </div>
<div id="modal-body" class="modal-body">
<form class="registration-form" action="register_seller.php" method="POST">
    <h1 class="modal-title fs-5" id="box-header">Register</h1>
    
    <div class="input-box">
        <input type="text" id="seller_name" name="seller_name" class="input-field" placeholder="Name" required autocomplete="off" />
    </div>
    
    <div class="input-box">
        <input type="text" id="seller_cname" name="seller_cname" class="input-field" placeholder="Company Name" required autocomplete="off" />
    </div>
    
    <div class="input-box">
        <input type="email" id="seller_email" name="seller_email" class="input-field" placeholder="Email" required autocomplete="off" />
    </div>
    
    <div class="input-box">
        <input type="text" id="seller_phone" name="seller_phone" class="input-field" placeholder="Phone" required autocomplete="off" />
    </div>
    
    <div class="input-box">
        <input type="text" id="seller_gst" name="seller_gst" class="input-field" placeholder="GST Number" required autocomplete="off" />
    </div>
    
    <div class="input-box">
        <textarea id="seller_address" name="seller_address" class="input-field" placeholder="Address" required></textarea>
    </div>
    
    <div class="input-box">
        <input type="text" id="seller_state" name="seller_state" class="input-field" placeholder="State" required autocomplete="off" />
    </div>
    
    <div class="input-box">
        <input type="text" id="seller_city" name="seller_city" class="input-field" placeholder="City" required autocomplete="off" />
    </div>
    
    <div class="input-box">
        <input type="text" id="seller_zipcode" name="seller_zipcode" class="input-field" placeholder="Zip Code" required autocomplete="off" />
    </div>
    
    <div class="input-box">
        <input type="password" id="seller_password" name="seller_password" class="input-field" placeholder="Password" required autocomplete="off" />
    </div>
    
    <div class="input-submit">
        <button class="submit-btn" id="register-btn" name="register">
            <label for="submit">Register</label>
        </button>
    </div>
    
</form>
</div>
</div>
</div>
</div>
</body>
</html>



<?php include 'footer.php'; ?>