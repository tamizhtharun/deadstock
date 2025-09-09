<?php
session_start();
require_once 'db_connection.php';
require_once 'messages.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
date_default_timezone_set('Asia/Kolkata');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    $check_email_query = "SELECT * FROM user_login WHERE user_email = ?";
    $stmt = $pdo->prepare($check_email_query);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour', strtotime(date('Y-m-d H:i:s'))));

        $insert_token_query = "UPDATE user_login SET reset_token = ?, reset_token_expires = ? WHERE user_email = ?";
        $stmt = $pdo->prepare($insert_token_query);
        $stmt->execute([$token, $expires, $email]);

        $reset_link = "http://localhost/deadstock/reset_password.php?token=" . $token;

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'deaddstock@gmail.com';
            $mail->Password = 'fnsdadrefmosspym';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('deaddstock@gmail.com', 'Deadstock');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = "Reset Your Deadstock Password";

            $mail->Body = "
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border: 1px solid #e1e1e1;
            border-radius: 6px;
            overflow: hidden;
        }
        .header {
            background-color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid #e1e1e1;
        }
        .header h2 {
            color: #333333;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 35px;
            background-color: #ffffff;
        }
        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333333;
        }
        .message {
            font-size: 16px;
            color: #555555;
            margin-bottom: 25px;
            line-height: 1.6;
        }
        .button-container {
            text-align: center;
            margin: 35px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 35px;
            background-color: #0071e3;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }
        
        .button:hover {
            background-color: #005bb5; /* Slightly darker shade for a smooth effect */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15); /* Subtle shadow effect */
        }
        
        .notice {
            font-size: 14px;
            color: #666666;
            margin-top: 25px;
            padding: 15px 20px;
            background-color: #f8f9ff;
            border: 1px solid #e0e3f6;
            border-radius: 4px;
            line-height: 1.5;
        }
        .footer {
            text-align: center;
            padding: 20px;
            background-color: #fafafa;
            border-top: 1px solid #e1e1e1;
        }
        .footer p {
            margin: 0;
            color: #666666;
            font-size: 13px;
            line-height: 1.6;
        }
        .copyright {
            display: block;
            margin-top: 10px;
            color: #666666;
            font-size: 13px;
        }
        .divider {
            height: 1px;
            background-color: #e1e1e1;
            margin: 25px 0;
        }
        .signature {
            color: #333333;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class='email-container'>
        <div class='header'>
            <h2>Reset Your Password</h2>
        </div>
        <div class='content'>
            <div class='greeting'>Hello,</div>
            <div class='message'>
                We received a request to reset your Deadstock password. To create a new password, click the button below.
            </div>
            <div class='button-container'>
                <a href='$reset_link' class='button'>Reset Password</a>
            </div>
            <div class='notice'>
                Note: This password reset link will expire in 1 hour for security purposes. If you didn't request this reset, please ignore this email.
            </div>
            <div class='divider'></div>
            <div class='message' style='margin-bottom: 0;'>
                Best regards,<br>
                <span class='signature'>Deadstock Team</span>
            </div>
        </div>
        <div class='footer'>
            <p>This is an automated message. Please do not reply to this email.</p>
            <span class='copyright'>&copy; " . date('Y') . " Deadstock. All rights reserved.</span>
        </div>
    </div>
</body>
</html>";

            if ($mail->send()) {
                echo json_encode(['success' => true, 'message' => 'Password reset link has been sent to your email.', 'type' => 'success']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to send password reset email. Please try again.']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Email error: ' . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No user found with this email address.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>