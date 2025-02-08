<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'p3plzcpnl508868.prod.phx3.secureserver.net';  // Use the actual server hostname from your error message
    $mail->SMTPAuth = true;
    $mail->Username = 'support@thedeadstock.in';
    $mail->Password = 'Deadstock@2025';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  // Use SMTPS instead of STARTTLS
    $mail->Port = 465;  // Port 465 for SMTPS
    
    // Disable SSL verification for development (remove in production)
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    );
    
    // Enable debug mode for testing
    $mail->SMTPDebug = 2;  // 2 for detailed debug output
    $mail->Debugoutput = 'error_log';
    
    // Sender & Recipient
    $mail->setFrom('support@thedeadstock.in', 'Deadstock');
    $mail->addAddress('mailtotharun23@gmail.com', 'Tamil');
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from PHPMailer';
    $mail->Body = '<h1>PHPMailer Test Successful</h1><p>This is a test email sent using cPanel SMTP.</p>';
    
    $mail->send();
    echo '✅ Email has been sent successfully!';
    
} catch (Exception $e) {
    echo "❌ Email could not be sent. Error: {$mail->ErrorInfo}";
    error_log("Mail Error: " . $mail->ErrorInfo);
}
?>