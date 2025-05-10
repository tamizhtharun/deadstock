<?php include 'header.php'; ?>
<?php
//seller_registration.php
// Include database connection and PHPMailer files
require 'db_connection.php'; // Update with actual DB connection code if inline is needed
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
        // Check if email already exists in sellers or users table
        $stmt = $conn->prepare("SELECT seller_email FROM sellers WHERE seller_email = ? UNION SELECT email FROM users WHERE email = ?");
        $stmt->bind_param("ss", $seller_email, $seller_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errorMessages[] = "Email already exists. Please use a different email.";
        } else {
            // Hash the password for security
            $hashed_password = password_hash($seller_password, PASSWORD_DEFAULT);


            // Generate a unique verification token
            $verification_token = bin2hex(random_bytes(32));
            // Insert seller data into `sellers` table
            $stmt = $conn->prepare("INSERT INTO sellers (seller_name, seller_cname, seller_email, seller_phone, seller_gst, seller_address, seller_state, seller_city, seller_zipcode, seller_password, seller_status, seller_verification_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $unique_seller_id = generateUniqueSellerId($conn);
            $stmt->bind_param("ssssssssssss", $seller_name, $seller_cname, $seller_email, $seller_phone, $seller_gst, $seller_address, $seller_state, $seller_city, $seller_zipcode, $hashed_password, $seller_status, $verification_token);

            if ($stmt->execute()) {
                $seller_id = $stmt->insert_id;

                // Update the seller record with the unique seller ID
                $update_stmt = $conn->prepare("UPDATE sellers SET unique_seller_id = ? WHERE seller_id = ?");
                $update_stmt->bind_param("si", $unique_seller_id, $seller_id);
                $update_stmt->execute();
                $update_stmt->close();

                // Insert login credentials into `user_login` table
                $stmt_login = $conn->prepare("INSERT INTO user_login (user_name, user_email, user_password, user_role) VALUES (?, ?, ?, ?)");
                $user_role = 'seller';
                $stmt_login->bind_param("ssss", $seller_name, $seller_email, $hashed_password, $user_role);

                if ($stmt_login->execute()) {
                    $successMessage = "New seller registered successfully! Please verify your email address to complete the registration.";

                    // Sending email logic...
                    try {
                        $mail = new PHPMailer(true);
                        $mail->isSMTP();
                        $mail->Host = 'p3plzcpnl508868.prod.phx3.secureserver.net';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'support@thedeadstock.in';
                        $mail->Password = 'Deadstock@2025';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                        $mail->Port = 465;

                        $mail->setFrom('support@thedeadstock.in', 'Deadstock');
                        $mail->addAddress($seller_email, $seller_name);

                        $mail->isHTML(true);
                        $mail->Subject = 'Seller Registration Confirmation';
                        $verification_link = "http://localhost/deadstock/verify_seller.php?token=$verification_token";

                        $mail->Body = "
                            <div style='max-width: 480px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; font-family: Arial, sans-serif; background-color: #ffffff;'>
                                <div style='text-align: center;'>
                                    <img src='https://yourdomain.com/uploads/logo.png' alt='Deadstock' style='max-width: 80px; margin-bottom: 20px;'>
                                    <h2 style='color: #333;'>Confirm Your Seller Account</h2>
                                </div>
                                <div style='color: #666; font-size: 14px; line-height: 1.6; text-align: left;'>
                                    <p>Hi <strong>$seller_name</strong>,</p>
                                    <p>Welcome to <strong>Deadstock</strong>! We're excited to have you as a seller on our platform.</p>
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
            margin-bottom: 24px;
        }

        .input-group.full-width {
            grid-column: 1 / -1;
        }

        .input-group .icon {
            position: absolute;
            top: 50%;
            left: 12px;
            transform: translateY(-50%);
            color: #86868b;
            font-size: 16px;
        }

        .input-group .icon-address {
            position: absolute;
            top: 25%;
            left: 12px;
            transform: translateY(-50%);
            color: #86868b;
            font-size: 16px;
            grid-column: 1 / -1;
        }

        .input-group input,
        .input-group textarea {
            width: 100%;
            padding: 12px 16px;
            padding-left: 40px;
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
            position: absolute;
            bottom: -20px;
            left: 0;
        }

        .error-message.active {
            display: block;
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
            margin-top: -10px;
            margin-bottom: 30px;
            position: relative;
            animation: slideDown 0.3s ease-out;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .message-success {
            background-color: #ecfdf5;
            color: #065f46;
        }

        .message-error {
            background-color: #fef2f2;
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
            border-radius: 0;
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

        @media (max-width: 768px) {
            .terms-checkbox-container {
                padding: 0 16px;
            }
        }
    </style>
</head>

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
                        <input type="text" id="seller_name" name="seller_name" placeholder="Full Name"
                            value="<?= htmlspecialchars($seller_name) ?>" required>
                    </div>

                    <div class="input-group">
                        <span class="icon">
                            <i class="fa-regular fa-building"></i>
                        </span>
                        <input type="text" id="seller_cname" name="seller_cname" placeholder="Company Name"
                            value="<?= htmlspecialchars($seller_cname) ?>" required>
                    </div>

                    <div class="input-group">
                        <span class="icon">
                            <i class="fa-sharp-duotone fa-solid fa-envelope"></i>
                        </span>
                        <input type="email" id="seller_email" name="seller_email" placeholder="Email Address"
                            value="<?= htmlspecialchars($seller_email) ?>" required>
                    </div>

                    <div class="input-group">
                        <span class="icon">
                            <i class="fa-sharp-duotone fa-solid fa-phone"></i>
                        </span>
                        <input type="text" id="seller_phone" name="seller_phone" placeholder="Phone Number"
                            value="<?= htmlspecialchars($seller_phone) ?>" required>
                    </div>

                    <div class="input-group">
                        <span class="icon">
                            <i class="fa-solid fa-scale-balanced"></i>
                        </span>
                        <input type="text" id="seller_gst" name="seller_gst" placeholder="GST Number"
                            value="<?= htmlspecialchars($seller_gst) ?>" required>
                    </div>

                    <div class="input-group">
                        <span class="icon">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="seller_password" name="seller_password" placeholder="Password"
                            required>
                    </div>

                    <div class="input-group full-width">
                        <span class="icon-address">
                            <i class="fa-sharp-duotone fa-solid fa-location-dot"></i>
                        </span>
                        <textarea id="seller_address" name="seller_address" placeholder="Address"
                            required><?= htmlspecialchars($seller_address) ?></textarea>
                    </div>

                    <div class="input-group">
                        <span class="icon">
                            <i class="fa-solid fa-globe"></i>
                        </span>
                        <input type="text" id="seller_state" name="seller_state" placeholder="State"
                            value="<?= htmlspecialchars($seller_state) ?>" required>
                    </div>

                    <div class="input-group">
                        <span class="icon">
                            <i class="fa-solid fa-city"></i>
                        </span>
                        <input type="text" id="seller_city" name="seller_city" placeholder="City"
                            value="<?= htmlspecialchars($seller_city) ?>" required>
                    </div>

                    <div class="input-group">
                        <span class="icon">
                            <i class="fa-solid fa-truck"></i>
                        </span>
                        <input type="text" id="seller_zipcode" name="seller_zipcode" placeholder="ZIP Code"
                            value="<?= htmlspecialchars($seller_zipcode) ?>" required maxlength="6">
                    </div>
                </div>

                <div class="terms-checkbox-container">
                    <label>
                        <input type="checkbox" id="terms-checkbox" name="terms_accepted" required>
                        <span>I agree to the <span class="terms-link" onclick="openTermsPage(event)">Terms and
                                Conditions</span></span>
                    </label>
                </div>

                <div class="form-footer">
                    <button type="submit" class="submit-btn">Register</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/dbb791f861.js" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('.registration-form');
            const inputs = form.querySelectorAll('input, textarea');

            inputs.forEach(input => {
                const errorElement = document.createElement('span');
                errorElement.className = 'error-message';
                input.parentNode.appendChild(errorElement);

                input.addEventListener('input', function() {
                    validateInput(this);
                });
            });

            function validateInput(input) {
                const errorElement = input.parentNode.querySelector('.error-message');
                let isValid = true;
                let errorMessage = '';

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
                }

                if (!isValid && input.value.length > 0) {
                    errorElement.textContent = errorMessage;
                    errorElement.classList.add('active');
                } else {
                    errorElement.classList.remove('active');
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

            form.addEventListener('submit', function(event) {
                let hasError = false;
                inputs.forEach(input => {
                    validateInput(input);
                    if (input.parentNode.querySelector('.error-message.active')) {
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