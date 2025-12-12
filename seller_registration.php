<?php include 'header.php'; ?>
<?php
//seller_registration.php
// Include database connection and PHPMailer files
require 'db_connection.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

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

function generateUniqueSellerId($conn)
{
    $year = date('Y');
    $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING(unique_seller_id, 8) AS UNSIGNED)) as max_id FROM sellers WHERE unique_seller_id LIKE ?");
    $like_pattern = "SLR{$year}%";
    $stmt->bind_param("s", $like_pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ?? 0;
    $next_id = $max_id + 1;
    return "SLR{$year}" . str_pad($next_id, 4, '0', STR_PAD_LEFT);
}

function isStrongPassword($password)
{
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password);
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
    $seller_status = 0;

    // Backend validations
    if (empty($seller_name)) {
        $errorMessages[] = "Seller name is required.";
    }
    if (empty($seller_password)) {
        $errorMessages[] = "Password is required.";
    } elseif (!isStrongPassword($seller_password)) {
        $errorMessages[] = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
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
        // Check if email already exists in sellers or users table
        $stmt = $conn->prepare("SELECT seller_email FROM sellers WHERE seller_email = ? UNION SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("ss", $seller_email, $seller_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errorMessages[] = "Email already exists. Please use a different email.";
        } else {
            $hashed_password = password_hash($seller_password, PASSWORD_DEFAULT);
            $verification_token = bin2hex(random_bytes(32));
            
            $stmt = $conn->prepare("INSERT INTO sellers (seller_name, seller_cname, seller_email, seller_phone, seller_gst, seller_address, seller_state, seller_city, seller_zipcode, seller_password, seller_status, seller_verification_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $unique_seller_id = generateUniqueSellerId($conn);
            $stmt->bind_param("ssssssssssss", $seller_name, $seller_cname, $seller_email, $seller_phone, $seller_gst, $seller_address, $seller_state, $seller_city, $seller_zipcode, $hashed_password, $seller_status, $verification_token);

            if ($stmt->execute()) {
                $seller_id = $stmt->insert_id;

                $update_stmt = $conn->prepare("UPDATE sellers SET unique_seller_id = ? WHERE seller_id = ?");
                $update_stmt->bind_param("si", $unique_seller_id, $seller_id);
                $update_stmt->execute();
                $update_stmt->close();

                $stmt_login = $conn->prepare("INSERT INTO user_login (user_name, user_email, user_password, user_role) VALUES (?, ?, ?, ?)");
                $user_role = 'seller';
                $stmt_login->bind_param("ssss", $seller_name, $seller_email, $hashed_password, $user_role);

                if ($stmt_login->execute()) {
                    $successMessage = "New seller registered successfully! Please verify your email address to complete the registration.";

                    try {
                        $mail->isSMTP();
                    $mail->Host       = 'smtp.zoho.in';      // try smtp.zoho.in, or smtp.zoho.com if that fails
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'support@destock.in';
                    $mail->Password   = '3q7Y4a0bnfni';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 'tls'
                    $mail->Port       = 587;

                        $mail->setFrom('support@destock.in', 'Destock-Support');
                        $mail->addAddress($seller_email, $seller_name);

                        $mail->isHTML(true);
                        $mail->Subject = 'Seller Registration Confirmation';
                        $verification_link = "https://destock.in/verify_seller.php?token=$verification_token";

                        $mail->Body = "
                            <div style='max-width: 480px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; font-family: Arial, sans-serif; background-color: #ffffff;'>
                                <div style='text-align: center;'>
                                    <img src='https://destock.in/assets/uploads/logo.png' alt='Destock' style='max-width: 80px; margin-bottom: 20px;'>
                                    <h2 style='color: #333;'>Confirm Your Seller Account</h2>
                                </div>
                                <div style='color: #666; font-size: 14px; line-height: 1.6; text-align: left;'>
                                    <p>Hi <strong>$seller_name</strong>,</p>
                                    <p>Welcome to <strong>Destock</strong>! We're excited to have you as a seller on our platform.</p>
                                    <p>To complete your registration and start listing your products, please verify your email by clicking the button below:</p>
                                </div>
                                <div style='text-align: center; margin-top: 20px;'>
                                    <a href='$verification_link' 
                                    style='display: inline-block; background-color: #000000; color: #ffffff; padding: 10px 20px; font-size: 14px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                                        Verify Email
                                    </a>
                                </div>
                                <div style='color: #999; font-size: 12px; text-align: center; margin-top: 20px;'>
                                    <p>If you did not sign up as a seller, you can safely ignore this email.</p>
                                    <p>For any assistance, feel free to reach out to our support team.</p>
                                </div>
                            </div>";
                        $mail->Body .= "<p>Your unique seller ID is: <strong>$unique_seller_id</strong></p>";
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

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seller Registration</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --copper-primary: #b87333;
            --copper-light: #d4a574;
            --copper-dark: #8b5a2b;
            --black: #1a1a1a;
            --cream: #faf8f3;
            --white: #ffffff;
            --error: #dc2626;
            --success: #16a34a;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, var(--cream) 0%, #f0ebe3 100%);
            color: var(--black);
            /* line-height: 1.6;
            min-height: 100vh;
            padding: 40px 20px; */
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
        }

        .registration-card {
            background: var(--white);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(184, 115, 51, 0.08);
        }

        .card-header {
            background: linear-gradient(135deg, var(--copper-primary) 0%, var(--copper-dark) 100%);
            padding: 48px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 15s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .card-header h1 {
            font-size: 36px;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
            letter-spacing: -0.5px;
        }

        .card-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            position: relative;
            z-index: 1;
        }

        .card-body {
            padding: 48px 40px;
        }

        .message {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 32px;
            font-size: 14px;
            animation: slideIn 0.4s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message-success {
            background: #dcfce7;
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .message-error {
            background: #fee2e2;
            color: var(--error);
            border-left: 4px solid var(--error);
        }

        .message ul {
            margin: 8px 0 0 20px;
        }

        .message li {
            margin: 4px 0;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 28px;
        }

        .input-group {
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .input-group.full-width {
            grid-column: 1 / -1;
        }

        .input-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--copper-dark);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper {
            position: relative;
            top:10px;
            left:0px;
        }

        .input-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--copper-light);
            font-size: 18px;
            pointer-events: none;
            z-index: 1;
            transition: color 0.3s ease;
        }

        .input-wrapper.textarea-wrapper .input-icon {
            top: 20px;
            transform: translateY(0);
        }

        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 14px 16px 14px 48px;
            font-size: 15px;
            border: 2px solid #e5e5e5;
            border-radius: 12px;
            background: var(--white);
            color: var(--black);
            transition: all 0.3s ease;
            font-family: inherit;
            display: block;
        }

        .input-group textarea {
            min-height: 120px;
            resize: vertical;
            line-height: 1.6;
        }

        .input-group input:focus,
        .input-group textarea:focus {
            outline: none;
            border-color: var(--copper-primary);
            box-shadow: 0 0 0 4px rgba(184, 115, 51, 0.1);
        }

        .input-group input:focus ~ .input-icon,
        .input-group textarea:focus ~ .input-icon {
            color: var(--copper-primary);
        }

        .error-message {
            display: none;
            color: var(--error);
            font-size: 12px;
            margin-top: 6px;
            animation: fadeIn 0.2s ease-in;
        }

        .error-message.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .terms-container {
            margin: 32px 0;
            padding: 20px;
            background: var(--cream);
            border-radius: 12px;
            border: 2px solid rgba(184, 115, 51, 0.15);
        }

        .terms-container label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            cursor: pointer;
            font-size: 14px;
            color: var(--black);
        }

        .terms-container input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            cursor: pointer;
            accent-color: var(--copper-primary);
        }

        .terms-link {
            color: var(--copper-primary);
            text-decoration: none;
            font-weight: 600;
            border-bottom: 1px solid transparent;
            transition: border-color 0.2s ease;
        }

        .terms-link:hover {
            border-bottom-color: var(--copper-primary);
        }

        .form-footer {
            text-align: center;
            margin-top: 32px;
        }

        .submit-btn {
            background: linear-gradient(135deg, var(--copper-primary) 0%, var(--copper-dark) 100%);
            color: var(--white);
            border: none;
            padding: 16px 48px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 16px rgba(184, 115, 51, 0.3);
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(184, 115, 51, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            body {
                padding: 20px 16px;
            }

            .card-header {
                padding: 32px 24px;
            }

            .card-header h1 {
                font-size: 28px;
            }

            .card-body {
                padding: 32px 24px;
            }

            .form-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .submit-btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .card-header h1 {
                font-size: 24px;
            }

            .card-header p {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="registration-card">
            <div class="card-header">
                <h1>Become a Seller</h1>
                <p>Join our platform and start selling today</p>
            </div>

            <div class="card-body">
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
                            <label class="input-label">Full Name</label>
                            <div class="input-wrapper">
                                <input type="text" id="seller_name" name="seller_name" placeholder="Enter your full name"
                                    value="<?= htmlspecialchars($seller_name) ?>" required>
                                <i class="fa-sharp-duotone fa-solid fa-user input-icon"></i>
                            </div>
                            <span class="error-message"></span>
                        </div>

                        <div class="input-group">
                            <label class="input-label">Company Name</label>
                            <div class="input-wrapper">
                                <input type="text" id="seller_cname" name="seller_cname" placeholder="Enter company name"
                                    value="<?= htmlspecialchars($seller_cname) ?>" required>
                                <i class="fa-regular fa-building input-icon"></i>
                            </div>
                            <span class="error-message"></span>
                        </div>

                        <div class="input-group">
                            <label class="input-label">Email Address</label>
                            <div class="input-wrapper">
                                <input type="email" id="seller_email" name="seller_email" placeholder="your@email.com"
                                    value="<?= htmlspecialchars($seller_email) ?>" required>
                                <i class="fa-sharp-duotone fa-solid fa-envelope input-icon"></i>
                            </div>
                            <span class="error-message"></span>
                        </div>

                        <div class="input-group">
                            <label class="input-label">Phone Number</label>
                            <div class="input-wrapper">
                                <input type="text" id="seller_phone" name="seller_phone" placeholder="10-digit number"
                                    value="<?= htmlspecialchars($seller_phone) ?>" required>
                                <i class="fa-sharp-duotone fa-solid fa-phone input-icon"></i>
                            </div>
                            <span class="error-message"></span>
                        </div>

                        <div class="input-group">
                            <label class="input-label">GST Number</label>
                            <div class="input-wrapper">
                                <input type="text" id="seller_gst" name="seller_gst" placeholder="GST identification"
                                    value="<?= htmlspecialchars($seller_gst) ?>" required>
                                <i class="fa-solid fa-scale-balanced input-icon"></i>
                            </div>
                            <span class="error-message"></span>
                        </div>

                        <div class="input-group">
                            <label class="input-label">Password</label>
                            <div class="input-wrapper">
                                <input type="password" id="seller_password" name="seller_password" placeholder="Create strong password" required>
                                <i class="fa-solid fa-lock input-icon"></i>
                            </div>
                            <span class="error-message"></span>
                        </div>

                        <div class="input-group full-width">
                            <label class="input-label">Address</label>
                            <div class="input-wrapper textarea-wrapper">
                                <textarea id="seller_address" name="seller_address" placeholder="Enter your complete address" required><?= htmlspecialchars($seller_address) ?></textarea>
                                <i class="fa-sharp-duotone fa-solid fa-location-dot input-icon"></i>
                            </div>
                            <span class="error-message"></span>
                        </div>

                        <div class="input-group">
                            <label class="input-label">State</label>
                            <div class="input-wrapper">
                                <input type="text" id="seller_state" name="seller_state" placeholder="Enter state"
                                    value="<?= htmlspecialchars($seller_state) ?>" required>
                                <i class="fa-solid fa-globe input-icon"></i>
                            </div>
                            <span class="error-message"></span>
                        </div>

                        <div class="input-group">
                            <label class="input-label">City</label>
                            <div class="input-wrapper">
                                <input type="text" id="seller_city" name="seller_city" placeholder="Enter city"
                                    value="<?= htmlspecialchars($seller_city) ?>" required>
                                <i class="fa-solid fa-city input-icon"></i>
                            </div>
                            <span class="error-message"></span>
                        </div>

                        <div class="input-group">
                            <label class="input-label">ZIP Code</label>
                            <div class="input-wrapper">
                                <input type="text" id="seller_zipcode" name="seller_zipcode" placeholder="6-digit code"
                                    value="<?= htmlspecialchars($seller_zipcode) ?>" required maxlength="6">
                                <i class="fa-solid fa-truck input-icon"></i>
                            </div>
                            <span class="error-message"></span>
                        </div>
                    </div>

                    <div class="terms-container">
                        <label>
                            <input type="checkbox" id="terms-checkbox" name="terms_accepted" required>
                            <span>I agree to the <a href="#" class="terms-link" onclick="openTermsPage(event)">Terms and Conditions</a> and acknowledge that I have read the privacy policy</span>
                        </label>
                    </div>

                    <div class="form-footer">
                        <button type="submit" class="submit-btn">Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/dbb791f861.js" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.registration-form');
            const inputs = form.querySelectorAll('input, textarea');

            inputs.forEach(input => {
                const errorElement = input.parentElement.nextElementSibling;

                input.addEventListener('input', function() {
                    validateInput(this, errorElement);
                });

                input.addEventListener('blur', function() {
                    validateInput(this, errorElement);
                });
            });

            function validateInput(input, errorElement) {
                let isValid = true;
                let errorMessage = '';

                if (input.value.trim() === '' && input.hasAttribute('required')) {
                    return;
                }

                switch (input.id) {
                    case 'seller_gst':
                        isValid = validateGST(input.value);
                        errorMessage = 'Invalid GST Number format';
                        break;
                    case 'seller_phone':
                        isValid = validatePhone(input.value);
                        errorMessage = 'Invalid phone number';
                        break;
                    case 'seller_zipcode':
                        isValid = /^\d{6}$/.test(input.value);
                        errorMessage = 'Please enter a valid 6-digit ZIP code';
                        break;
                    case 'seller_email':
                        isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value);
                        errorMessage = 'Invalid email address';
                        break;
                    case 'seller_password':
                        isValid = validatePassword(input.value);
                        errorMessage = 'Password must be at least 8 characters, include uppercase, lowercase, number, and special character';
                        break;
                }

                if (!isValid && input.value.length > 0) {
                    errorElement.textContent = errorMessage;
                    errorElement.classList.add('active');
                    input.style.borderColor = 'var(--error)';
                } else {
                    errorElement.classList.remove('active');
                    input.style.borderColor = '';
                }
            }

            function validateGST(gstNumber) {
                const gstRegex = /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}[Z]{1}[A-Z0-9]{1}$/;
                return gstRegex.test(gstNumber);
            }

            function validatePhone(phoneNumber) {
                const phoneRegex = /^(?:(?:\+|0{0,2})91(\s*[-]\s*)?|[0]?)?[6789]\d{9}$/;
                return phoneRegex.test(phoneNumber);
            }

            function validatePassword(password) {
                const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
                return passwordRegex.test(password);
            }

            form.addEventListener('submit', function(event) {
                let hasError = false;
                inputs.forEach(input => {
                    const errorElement = input.parentElement.nextElementSibling;
                    validateInput(input, errorElement);
                    if (errorElement.classList.contains('active')) {
                        hasError = true;
                    }
                });

                if (hasError) {
                    event.preventDefault();
                }
            });
        });

        function openTermsPage(event) {
            event.preventDefault();
            event.stopPropagation();
            window.open('terms.php?source=seller', '_blank');
        }
    </script>
</body>

</html>
<?php include 'footer.php'; ?>