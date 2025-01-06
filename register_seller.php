<?php
// Include required files
include 'db_connection.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

// Check if the form was submitted
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
    $seller_password = $_POST['seller_password']; // Password hashing happens later
    $seller_status = 0; // Default status (inactive)

    // Backend validations
    $errors = [];

    // 1. Validate name
    if (empty($seller_name)) {
        $errors[] = "Seller name is required.";
    }

    // 2. Validate company name
    if (empty($seller_cname)) {
        $errors[] = "Company name is required.";
    }

    // 3. Validate email
    if (empty($seller_email) || !filter_var($seller_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid email address is required.";
    }

    // 4. Validate phone number (10 digits)
    if (empty($seller_phone) || !preg_match("/^[0-9]{10}$/", $seller_phone)) {
        $errors[] = "A valid 10-digit phone number is required.";
    }

    // 5. Validate GST number
    $gstRegex = "/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[A-Z0-9]{1}[Z]{1}[A-Z0-9]{1}$/";
    if (empty($seller_gst) || !preg_match($gstRegex, $seller_gst)) {
        $errors[] = "A valid GST number is required.";
    }

    // 6. Validate address, state, city, and zipcode
    if (empty($seller_address)) {
        $errors[] = "Address is required.";
    }
    if (empty($seller_state)) {
        $errors[] = "State is required.";
    }
    if (empty($seller_city)) {
        $errors[] = "City is required.";
    }
    if (empty($seller_zipcode) || !preg_match("/^\d{6}$/", $seller_zipcode)) {
        $errors[] = "A valid 6-digit ZIP code is required.";
    }

    // 7. Validate password
    if (empty($seller_password) || strlen($seller_password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    // Stop processing if there are errors
    if (!empty($errors)) {
        echo "<script>
                alert('" . implode("\\n", $errors) . "');
                window.location.href = 'seller_registration.php';
              </script>";
        exit();
    }

    // Check if the email already exists
    $email_check_stmt = $conn->prepare("SELECT seller_email FROM sellers WHERE seller_email = ?");
    $email_check_stmt->bind_param("s", $seller_email);
    $email_check_stmt->execute();
    $email_check_stmt->store_result();

    if ($email_check_stmt->num_rows > 0) {
        // Email already exists
        echo "<script>
                alert('Email already exists. Please use a different email.');
                window.location.href = 'seller_registration.php';
              </script>";
        exit();
    }

    // Hash the password for security
    $hashed_password = password_hash($seller_password, PASSWORD_DEFAULT);

    // Insert seller data into `sellers` table
    $stmt = $conn->prepare("INSERT INTO sellers (seller_name, seller_cname, seller_email, seller_phone, seller_gst, seller_address, seller_state, seller_city, seller_zipcode, seller_password, seller_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssssssss", $seller_name, $seller_cname, $seller_email, $seller_phone, $seller_gst, $seller_address, $seller_state, $seller_city, $seller_zipcode, $hashed_password, $seller_status);

    if ($stmt->execute()) {
        // Insert login credentials into `user_login` table
        $stmt_login = $conn->prepare("INSERT INTO user_login (user_name, user_email, user_password, user_role) VALUES (?, ?, ?, ?)");
        $user_role = 'seller'; // Set default role to 'seller'
        $stmt_login->bind_param("ssss", $seller_name, $seller_email, $hashed_password, $user_role);

        if ($stmt_login->execute()) {
            // Send email notification to the seller
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'nithiishhh@gmail.com'; // Replace with your email
                $mail->Password = 'meknepblkzosmavu'; // Replace with your app-specific password
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Email sender and recipient
                $mail->setFrom('nithiishhh@gmail.com', 'Deadstock');
                $mail->addAddress($seller_email, $seller_name);

                // Email content
                $mail->isHTML(true);
                $mail->Subject = 'Seller Registration Confirmation';
                $mail->Body = "<h1>Welcome, $seller_name!</h1>
                               <p>Thank you for registering as a seller on our platform. Your account is currently under review and will be activated soon.</p>
                               <br><p>Best regards,<br>Deadstock</p>";

                // Send email
                $mail->send();               
                echo "<script>
                        alert('New seller registered successfully,Please check your email!');
                        window.location.href = 'index.php';
                      </script>";
            } catch (Exception $e) {
                error_log("Email could not be sent. Error: " . $mail->ErrorInfo);
            }
        } else {
            echo "Error in user login: " . $stmt_login->error;
        }

        $stmt_login->close();
    } else {
        echo "Error in seller registration: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
