<?php include 'header.php'; ?>
<?php
// Include database connection and PHPMailer files
require 'db_connection.php'; // Update with actual DB connection code if inline is needed
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';
require_once('track_view.php');
trackPageView('SRF', 'Seller Registration Form');

// Initialize variables for error messages and success message
$errorMessages = [];
$successMessage = "";

$seller_name = $seller_cname = $seller_email = $seller_phone = $seller_gst = "";
$seller_address = $seller_state = $seller_city = $seller_zipcode = "";



// Add this at the top of your PHP file
$query = "SELECT seller_tc FROM tbl_settings LIMIT 1";
$result = mysqli_query($conn, $query);
$terms = '';
if ($row = mysqli_fetch_assoc($result)) {
    $terms = htmlspecialchars($row['seller_tc']);
}


// Process form submission if POST request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input data
    $seller_name = trim($_POST['seller_name']);
    $seller_cname = trim($_POST['seller_cname']);
    $seller_email = trim($_POST['seller_email']);
    $seller_phone = trim($_POST['seller_phone']);
    $seller_gst = trim($_POST['seller_gst']);
    $seller_address = trim($_POST['seller_address']);
    $seller_state = trim($_POST['seller_state']);
    $seller_city = trim($_POST['seller_city']);
    $seller_zipcode = trim($_POST['seller_zipcode']);
    $seller_password = $_POST['seller_password'];
    $seller_status = 0; // Default status (inactive)

    // Backend validations
    if (empty($seller_name)) {
        $errorMessages[] = "Seller name is required.";
    }
    if (!filter_var($seller_email, FILTER_VALIDATE_EMAIL)) {
        $errorMessages[] = "Invalid email address.";
    }
    if (!preg_match('/^[6-9]\d{9}$/', $seller_phone)) {
        $errorMessages[] = "Invalid phone number. Must be a 10-digit number starting with 6-9.";
    }
    if (strlen($seller_zipcode) !== 6 || !ctype_digit($seller_zipcode)) {
        $errorMessages[] = "ZIP code must be a 6-digit number.";
    }

    // Validate GST format
    $gstRegex = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}[Z]{1}[A-Z0-9]{1}$/';
    if (!preg_match($gstRegex, $seller_gst)) {
        $errorMessages[] = "Invalid GST Number format.";
    }

    // Stop processing if there are errors
    if (empty($errorMessages)) {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT seller_email FROM sellers WHERE seller_email = ?");
        $stmt->bind_param("s", $seller_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errorMessages[] = "Email already exists. Please use a different email.";
        } else {
            // Hash the password for security
            $hashed_password = password_hash($seller_password, PASSWORD_DEFAULT);

            // Insert seller data into `sellers` table
            $stmt = $conn->prepare("INSERT INTO sellers (seller_name, seller_cname, seller_email, seller_phone, seller_gst, seller_address, seller_state, seller_city, seller_zipcode, seller_password, seller_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssssss", $seller_name, $seller_cname, $seller_email, $seller_phone, $seller_gst, $seller_address, $seller_state, $seller_city, $seller_zipcode, $hashed_password, $seller_status);

            if ($stmt->execute()) {
                // Insert login credentials into `user_login` table
                $stmt_login = $conn->prepare("INSERT INTO user_login (user_name, user_email, user_password, user_role) VALUES (?, ?, ?, ?)");
                $user_role = 'seller';
                $stmt_login->bind_param("ssss", $seller_name, $seller_email, $hashed_password, $user_role);

                if ($stmt_login->execute()) {
                    $successMessage = "New seller registered successfully! Please verify your email address to complete the registration.";

                    // Sending email logic...
                    try {
                        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'deaddstock@gmail.com';
                        $mail->Password = 'fnsdadrefmosspym';
                        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        $mail->setFrom('deaddstock@gmail.com', 'Deadstock');
                        $mail->addAddress($seller_email, $seller_name);

                        $mail->isHTML(true);
                        $mail->Subject = 'Seller Registration Confirmation';
                        $mail->Body = "<h1>Welcome, $seller_name!</h1>
                        <p>Thank you for registering as a seller on our platform. Your account is currently under review and will be activated soon.</p>
                        <br><p>Best regards,<br>Deadstock</p>";

                        $mail->send();
                    } catch (Exception $e) {
                        error_log("Email error: " . $mail->ErrorInfo);
                    }
                } else {
                    $errorMessages[] = "Error in inserting login credentials: " . $stmt_login->error;
                }

                $stmt_login->close();
            } else {
                $errorMessages[] = "Error in seller registration: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}
?>




<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('sellerRegistrationForm');
    const gstInput = document.getElementById('seller_gst');
    const gstError = document.getElementById('gst-error');
    const phoneInput = document.getElementById('seller_phone');
    const phoneError = document.getElementById('phone-error');
    const passwordInput = document.getElementById('seller_password');
    const confirmPasswordInput = document.getElementById('seller_confirm_password');
    const passwordError = document.getElementById('password-error');
    


    // GST validation
    function validateGST(gstNumber) {
        const gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}[Z]{1}[A-Z0-9]{1}$/;
        return gstRegex.test(gstNumber);
    }

    // Indian phone number validation
    function validatePhone(phoneNumber) {
        const phoneRegex = /^(?:(?:\+|0{0,2})91(\s*[-]\s*)?|[0]?)?[6789]\d{9}$/;
        return phoneRegex.test(phoneNumber);
    }

    // Password validation
    function validatePasswords() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (confirmPassword.length > 0) {
            if (password !== confirmPassword) {
                passwordError.style.display = 'block';
                return false;
            } else {
                passwordError.style.display = 'none';
                return true;
            }
        }
        return true;
    }

    gstInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        if (this.value.length > 0) {
            if (!validateGST(this.value)) {
                gstError.style.display = 'block';
            } else {
                gstError.style.display = 'none';
            }
        } else {
            gstError.style.display = 'none';
        }
    });

    phoneInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 0) {
            if (!validatePhone(this.value)) {
                phoneError.style.display = 'block';
            } else {
                phoneError.style.display = 'none';
            }
        } else {
            phoneError.style.display = 'none';
        }
    });

    confirmPasswordInput.addEventListener('input', validatePasswords);
    passwordInput.addEventListener('input', validatePasswords);

    // ZIP code validation
    const zipcodeInput = document.getElementById('seller_zipcode');
    zipcodeInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });

    // Form submission
    form.addEventListener('submit', function(event) {
        // Validate GST
        if (!validateGST(gstInput.value)) {
            event.preventDefault();
            gstError.style.display = 'block';
            gstInput.focus();
            return;
        }

        // Validate Phone
        if (!validatePhone(phoneInput.value)) {
            event.preventDefault();
            phoneError.style.display = 'block';
            phoneInput.focus();
            return;
        }

        // Validate Passwords
        if (!validatePasswords()) {
            event.preventDefault();
            passwordError.style.display = 'block';
            confirmPasswordInput.focus();
            return;
        }

        // Validate ZIP code
        const zipcode = zipcodeInput.value;
        if (!/^\d{6}$/.test(zipcode)) {
            event.preventDefault();
            alert('Please enter a valid 6-digit ZIP code.');
            zipcodeInput.focus();
            return;
        }
    });
});
gstInput.addEventListener('input', function() {
    this.value = this.value.toUpperCase();
    if (this.value.length > 0) {
        if (!validateGST(this.value)) {
            gstError.classList.add('active');
        } else {
            gstError.classList.remove('active');
        }
    } else {
        gstError.classList.remove('active');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const zipcodeInput = document.getElementById('seller_zipcode');
    const zipcodeError = document.getElementById('zipcode-error');

    // ZIP code validation
    zipcodeInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, ''); // Allow only numbers
        if (this.value.length > 0 && this.value.length !== 6) {
            zipcodeError.textContent = 'Please enter a valid 6-digit ZIP code.';
        } else {
            zipcodeError.textContent = '';
        }
    });

    // Form submission
    form.addEventListener('submit', function(event) {
        const zipcode = zipcodeInput.value;
        if (!/^\d{6}$/.test(zipcode)) {
            event.preventDefault();
            zipcodeError.textContent = 'Please enter a valid 6-digit ZIP code.';
            zipcodeInput.focus();
        }
    });
});

</script>

<style>
  :root {
    --primary-color: #0071e3;
    --error-color: #ff3b30;
    --success-color: #34c759;
    --text-color: #1d1d1f;
    --secondary-text: #86868b;
    --background-color: #fbfbfd;
    --input-background: #ffffff;
    --border-color: #d2d2d7;
}

body {
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    background-color: var(--background-color);
    color: var(--text-color);
    line-height: 1.5;
}

.container {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.registration-wrapper {
    background: var(--input-background);
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.1);
}

.form-header {
    text-align: center;
    margin-bottom: 40px;
}

.form-header h1 {
    font-size: 32px;
    font-weight: 600;
    margin-bottom: 16px;
    color: var(--text-color);
}

.success-message {
    background-color: var(--success-color);
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    margin-top: 16px;
    animation: fadeIn 0.3s ease-in-out;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

.input-group {
    position: relative;
    margin-bottom: 8px;
}

.input-group.full-width,  {
    grid-column: 1 / -1;
}

.input-group .icon {
    position: absolute;
    top: 50%;
    left: 12px; /* Adjust the left position as needed */
    transform: translateY(-50%);
    color: #86868b; /* Adjust icon color */
    font-size: 16px; /* Adjust icon size */
}

.input-group .icon-address {
    position: absolute;
    top: 25%;
    left: 12px; /* Adjust the left position as needed */
    transform: translateY(-50%);
    color: #86868b; /* Adjust icon color */
    font-size: 16px; /* Adjust icon size */
    grid-column: 1 / -1;
}

.input-group input,
.input-group textarea {
    width: 100%;
    
    padding: 12px 16px; /* Default padding for text */
    padding-left: 40px; /* Extra left padding to ensure the text does not overlap with the icon */
    font-size: 16px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background-color: var(--input-background);
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.input-group textarea {
    min-height: 100px;
    resize: vertical;
}


.input-group input:focus,
.input-group textarea:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 2px rgba(0, 113, 227, 0.2);
    outline: none;
}


.error-message {
    display: none;
    color: var(--error-color);
    font-size: 12px;
    margin-top: 4px;
    margin-left: 16px;
}

.form-footer {
    margin-top: 32px;
    text-align: center;
}

.submit-btn {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 14px 28px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 160px;
}

.submit-btn:hover {
    background-color: #0077ed;
    transform: translateY(-1px);
}

.submit-btn:active {
    transform: translateY(0);
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .registration-wrapper {
        padding: 24px;
    }
}

.form-header {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.form-header h1 {
    color: #1a1a1a;
    font-size: 28px;
    margin-bottom: 24px;
    text-align: center;
}

.message {
    padding: 16px 20px;
    border-radius: 8px;
    margin: 16px 0;
    margin-top:-10px;
    margin-bottom:30px;
    position: relative;
    animation: slideDown 0.3s ease-out;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.message-success {
    background-color: #ecfdf5;
    /* border-left: 4px solid #10b981; */
    color: #065f46;
}

.message-error {
    background-color: #fef2f2;
    /* border-left: 4px solid #ef4444; */
    color: #991b1b;

}

.message ul {
    margin: 8px 0 0 0;
    padding-left: 20px;
}

.message li {
    margin: 4px 0;
    font-size: 14px;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Terms and Conditions Styles */
.terms-checkbox-container {
    margin: 20px 0;
    text-align: left;
    padding: 0 20px;
}

.terms-checkbox-container label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-size: 14px;
    color: var(--text-color);
}

.terms-checkbox-container input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    border: 2px solid var(--border-color);
    border-radius: 0; /* Removed border radius */
}

.terms-link {
    color: var(--primary-color);
    text-decoration: underline;
    cursor: pointer;
    font-weight: 500;
}

.terms-link:hover {
    color: #005bbf;
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
   
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.75);
    z-index: 99999;
    animation: fadeIn 0.2s ease-out;
}
.modal-content {
    position: relative;
    background-color: var(--input-background);
    margin: 40px auto;
    padding: 24px;
    width: 90%;
    max-width: 600px;
    max-height: calc(100vh - 80px);
    border-radius: 0; /* Ensure no border-radius */
    /* box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);  */
    overflow-y: auto;
}

.modal-close {
    position: absolute;
    top: 16px;
    right: 16px;
    font-size: 24px;
    cursor: pointer;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;      
    justify-content: center;
    color: var(--secondary-text);
    transition: color 0.2s ease;
    background: none;
    border: none;
    padding: 0;
}

.modal-close:hover {
    color: var(--text-color);
}

.modal-title {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 16px;
    padding-right: 40px;
    color: var(--text-color);
}

.modal-dialog {
    border-radius: 0; /* Remove rounding from parent container */
}

.modal-header, .modal-body, .modal-footer {
    border-radius: 0; /* Remove rounding from child elements */
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

@media (max-width: 768px) {
    .modal-content {
        margin: 20px;
        width: auto;
        max-height: calc(100vh - 40px);
    }
    
    .terms-checkbox-container {
        padding: 0 16px;
    }
}

</style>

<!-- Font awesome for icons -->
<script src="https://kit.fontawesome.com/dbb791f861.js" crossorigin="anonymous"></script>
<body>
<div class="container">
    <div class="registration-wrapper">
        <div class="form-header">
            <h1>Seller Registration</h1>
        </div>
        

        <!-- Display messages -->
        <?php if (!empty($successMessage)): ?>
            <div class="message message-success">
                <?= htmlspecialchars($successMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMessages)): ?>
            <div class="message message-error">
                <ul>
                    <?php foreach ($errorMessages as $message): ?>
                        <li><?= htmlspecialchars($message) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

            <form class="registration-form" method="POST" action="">
        <div class="form-grid">
            <div class="input-group">
            <span class="icon">
                <i class="fa-sharp-duotone fa-solid fa-user"></i>
            </span>  
                <input type="text" id="seller_name" name="seller_name" placeholder="Full Name" value="<?= htmlspecialchars($seller_name) ?>" required>
            </div>

            <div class="input-group">
            <span class="icon">
            <i class="fa-regular fa-building"></i>
            </span> 
                <input type="text" id="seller_cname" name="seller_cname" placeholder="Company Name" value="<?= htmlspecialchars($seller_cname) ?>" required>
            </div>

            <div class="input-group">
            <span class="icon">
                <i class="fa-sharp-duotone fa-solid fa-envelope"></i>
            </span> 
                <input type="email" id="seller_email" name="seller_email" placeholder="Email Address" value="<?= htmlspecialchars($seller_email) ?>" required>
            </div>

            <div class="input-group">
                <span class = "icon">
                    <i class="fa-sharp-duotone fa-solid fa-phone"></i>
                </span>

                <input type="text" id="seller_phone" name="seller_phone" placeholder="Phone Number" value="<?= htmlspecialchars($seller_phone) ?>" required>
            </div>

            <div class="input-group">
            <span class="icon">
                <i class="fa-solid fa-scale-balanced"></i>
            </span> 
                <input type="text" id="seller_gst" name="seller_gst" placeholder="GST Number" value="<?= htmlspecialchars($seller_gst) ?>" required>
            </div>

            <div class="input-group">
            <span class="icon">
                <i class="fa-solid fa-lock"></i>
            </span>
                <input type="password" id="seller_password" name="seller_password" placeholder="Password" required>
            </div>

            <div class="input-group full-width">
            <span class="icon-address">
                <i class="fa-sharp-duotone fa-solid fa-location-dot"></i>
            </span>
                <textarea id="seller_address" name="seller_address" placeholder="Address" required>    <?= htmlspecialchars($seller_address) ?></textarea>
            </div>

            <div class="input-group">
            <span class="icon">
            <i class="fa-solid fa-globe"></i>
            </span>
                <input type="text" id="seller_state" name="seller_state" placeholder="State" value="<?= htmlspecialchars($seller_state) ?>" required>
            </div>

            <div class="input-group">
            <span class="icon">
                <i class="fa-solid fa-city"></i>
            </span>
                <input type="text" id="seller_city" name="seller_city" placeholder="City" value="<?= htmlspecialchars($seller_city) ?>" required>
            </div>

            <div class="input-group">
            <span class="icon">
                <i class="fa-solid fa-truck"></i>
            </span>
                <input type="text" id="seller_zipcode" name="seller_zipcode" placeholder="ZIP Code" value="<?= htmlspecialchars($seller_zipcode) ?>" required maxlength="6">
            </div>
        </div>

        <div class="terms-checkbox-container">
    <label>
        <input type="checkbox" id="terms-checkbox" name="terms_accepted" required>
        <span>I agree to the <span class="terms-link" onclick="showTermsModal()">Terms and Conditions</span></span>
    </label>
</div>

        <div class="form-footer">
            <button type="submit" class="submit-btn">Register</button>
        </div>
    </form>

    </div>
</div>

<div id="terms-modal" class="modal">
    <div class="modal-content">
        <button type="button" class="modal-close" onclick="closeTermsModal()" aria-label="Close modal">&times;</button>
        <h2 class="modal-title">Terms and Conditions</h2>
        <div class="modal-body">
            <?php echo nl2br($terms); ?>
        </div>
    </div>
</div>
</body>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('sellerRegistrationForm');
    const gstInput = document.getElementById('seller_gst');
    const gstError = document.getElementById('gst-error');
    const phoneInput = document.getElementById('seller_phone');
    const phoneError = document.getElementById('phone-error');
    const passwordInput = document.getElementById('seller_password');
    const confirmPasswordInput = document.getElementById('seller_confirm_password');
    const passwordError = document.getElementById('password-error');
    


    // GST validation
    function validateGST(gstNumber) {
        const gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}[Z]{1}[A-Z0-9]{1}$/;
        return gstRegex.test(gstNumber);
    }

    // Indian phone number validation
    function validatePhone(phoneNumber) {
        const phoneRegex = /^(?:(?:\+|0{0,2})91(\s*[-]\s*)?|[0]?)?[6789]\d{9}$/;
        return phoneRegex.test(phoneNumber);
    }

    // Password validation
    function validatePasswords() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (confirmPassword.length > 0) {
            if (password !== confirmPassword) {
                passwordError.style.display = 'block';
                return false;
            } else {
                passwordError.style.display = 'none';
                return true;
            }
        }
        return true;
    }

    gstInput.addEventListener('input', function() {
        this.value = this.value.toUpperCase();
        if (this.value.length > 0) {
            if (!validateGST(this.value)) {
                gstError.style.display = 'block';
            } else {
                gstError.style.display = 'none';
            }
        } else {
            gstError.style.display = 'none';
        }
    });

    phoneInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
        if (this.value.length > 0) {
            if (!validatePhone(this.value)) {
                phoneError.style.display = 'block';
            } else {
                phoneError.style.display = 'none';
            }
        } else {
            phoneError.style.display = 'none';
        }
    });

    confirmPasswordInput.addEventListener('input', validatePasswords);
    passwordInput.addEventListener('input', validatePasswords);

    // ZIP code validation
    const zipcodeInput = document.getElementById('seller_zipcode');
    zipcodeInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, '');
    });

    // Form submission
    form.addEventListener('submit', function(event) {
        // Validate GST
        if (!validateGST(gstInput.value)) {
            event.preventDefault();
            gstError.style.display = 'block';
            gstInput.focus();
            return;
        }

        // Validate Phone
        if (!validatePhone(phoneInput.value)) {
            event.preventDefault();
            phoneError.style.display = 'block';
            phoneInput.focus();
            return;
        }

        // Validate Passwords
        if (!validatePasswords()) {
            event.preventDefault();
            passwordError.style.display = 'block';
            confirmPasswordInput.focus();
            return;
        }

        // Validate ZIP code
        const zipcode = zipcodeInput.value;
        if (!/^\d{6}$/.test(zipcode)) {
            event.preventDefault();
            alert('Please enter a valid 6-digit ZIP code.');
            zipcodeInput.focus();
            return;
        }
    });
});

// Terms and Conditions Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.registration-form');
    const termsCheckbox = document.getElementById('terms-checkbox');
    const submitBtn = document.querySelector('.submit-btn');
    const modal = document.getElementById('terms-modal');

    // Prevent modal from closing when clicking inside modal content
    modal.querySelector('.modal-content').addEventListener('click', function(event) {
        event.stopPropagation();
    });

    // Form submission handler
    form.addEventListener('submit', function(event) {
        if (!termsCheckbox.checked) {
            event.preventDefault();
            alert('Please agree to the Terms and Conditions before registering.');
            return;
        }
        // Your existing form validation continues here...
    });
});

function showTermsModal() {
    const modal = document.getElementById('terms-modal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeTermsModal() {
    const modal = document.getElementById('terms-modal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('terms-modal');
    if (event.target === modal) {
        closeTermsModal();
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeTermsModal();
    }
});

gstInput.addEventListener('input', function() {
    this.value = this.value.toUpperCase();
    if (this.value.length > 0) {
        if (!validateGST(this.value)) {
            gstError.classList.add('active');
        } else {
            gstError.classList.remove('active');
        }
    } else {
        gstError.classList.remove('active');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const zipcodeInput = document.getElementById('seller_zipcode');
    const zipcodeError = document.getElementById('zipcode-error');

    // ZIP code validation
    zipcodeInput.addEventListener('input', function() {
        this.value = this.value.replace(/\D/g, ''); // Allow only numbers
        if (this.value.length > 0 && this.value.length !== 6) {
            zipcodeError.textContent = 'Please enter a valid 6-digit ZIP code.';
        } else {
            zipcodeError.textContent = '';
        }
    });

    // Form submission
    form.addEventListener('submit', function(event) {
        const zipcode = zipcodeInput.value;
        if (!/^\d{6}$/.test(zipcode)) {
            event.preventDefault();
            zipcodeError.textContent = 'Please enter a valid 6-digit ZIP code.';
            zipcodeInput.focus();
        }
    });
});

</script>
<?php include 'footer.php'; ?>
