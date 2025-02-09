<?php
include 'db_connection.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $phone_number = $_POST['phone_number'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $user_gst = $_POST['user_gst'];
    $token = bin2hex(random_bytes(50)); // Generate unique verification token

    // Check if email already exists in users or sellers table
    $check_sql = "SELECT email FROM users WHERE email = ? UNION SELECT seller_email FROM sellers WHERE seller_email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $email, $email);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        echo '<script>
                alert("Email already exists! Please use a different email.");
                window.location.href = "index.php"; 
              </script>';
        $check_stmt->close();
    } else {
        $check_stmt->close();

        // Insert user into the users table (status set to 0 - unverified)
        $sql = "INSERT INTO users (username, phone_number, email, password, user_gst, token, status) 
                VALUES (?, ?, ?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $username, $phone_number, $email, $password, $user_gst, $token);

        if ($stmt->execute()) {
            // Insert into user_login table
            $user_role = 'user'; // Set user role to 'user'
            $sql_login = "INSERT INTO user_login (user_name, user_email, user_password, user_role) 
                          VALUES (?, ?, ?, ?)";
            $stmt_login = $conn->prepare($sql_login);
            $stmt_login->bind_param("ssss", $username, $email, $password, $user_role);

            if ($stmt_login->execute()) {
                // Send verification email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com'; // Change to your mail server
                    $mail->SMTPAuth = true;
                    $mail->Username = 'deaddstock@gmail.com'; // Your email
                    $mail->Password = 'fnsdadrefmosspym'; // App password if using Gmail
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('deaddstock@gmail.com', 'Deadstock');
                    $mail->addAddress($email, $username);
                    $mail->isHTML(true);
                    $mail->Subject = 'Email Verification';
                    $mail->Body = "
                       <div style='max-width: 480px; margin: auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; font-family: Arial, sans-serif; background-color: #ffffff;'>
                            <div style='text-align: center;'>
                                <img src='https://yourdomain.com/uploads/logo.png' alt='Deadstock' style='max-width: 80px; margin-bottom: 20px;'>
                                <h2 style='color: #333;'>Confirm Your Email Address</h2>
                            </div>
                            <div style='color: #666; font-size: 14px; line-height: 1.6; text-align: left;'>
                                <p>Hi <strong>$username</strong>,</p>
                                <p>Welcome to <strong>Deadstock</strong>! You're just one step away from accessing all our exclusive wholesale stock bidding features.</p>
                                <p>To get started, please verify your email by clicking the button below. This ensures you have full access to your account and can participate in our bidding platform.</p>
                            </div>
                            <div style='text-align: center; margin-top: 20px;'>
                                <a href='http://localhost/deadstock/verify.php?token=$token' 
                                style='display: inline-block; background-color: #000000; color: #ffffff; padding: 10px 20px; font-size: 14px; text-decoration: none; border-radius: 5px; font-weight: bold;'>
                                    Verify Email
                                </a>
                            </div>
                            <div style='color: #999; font-size: 12px; text-align: center; margin-top: 20px;'>
                                <p>If you didn’t sign up for Deadstock, you can safely ignore this email.</p>
                                <p>For any assistance, feel free to reach out to our support team.</p>
                            </div>
                        </div>";
                    $mail->send();
                    
                    echo '<script>
                        alert("Registration successful! Please check your email for verification.");
                        window.location.href = "index.php";
                      </script>';
                } catch (Exception $e) {
                    echo "Error sending email: {$mail->ErrorInfo}";
                }
            } else {
                echo "Error: " . $stmt_login->error;
            }

            $stmt_login->close();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}

$conn->close();
?>
