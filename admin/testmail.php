<?php
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // STARTTLS (preferred)
$mail->isSMTP();
$mail->Host       = 'smtp.zoho.in';      // try smtp.zoho.in, or smtp.zoho.com if that fails
$mail->SMTPAuth   = true;
$mail->Username   = 'support@destock.in';
$mail->Password   = '3q7Y4a0bnfni';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 'tls'
$mail->Port       = 587;
$mail->SMTPDebug  = 3; // very verbose - shows server replies
$mail->Debugoutput = 'html'; // or 'echo' for CLI
    
    // Disable SSL verification for development (remove in production)
    // $mail->SMTPOptions = array(
    //     'ssl' => array(
    //         'verify_peer' => false,
    //         'verify_peer_name' => false,
    //         'allow_self_signed' => true
    //     )
    // );
    
    // Enable debug mode for testing
    $mail->SMTPDebug = 2;  // 2 for detailed debug output
    $mail->Debugoutput = 'error_log';
    
    // Sender & Recipient
    $mail->setFrom('support@destock.in', 'Destock');
    $mail->addAddress('mailtotharun23@gmail.com', 'Tamil');
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Test Email from PHPMailer';
    $mail->Body = '<h1>PHPMailer Test Successful</h1><p>This is a test email sent using cPanel SMTP.</p>';
    
    $mail->send();
    echo '✅ Email has been sent successfully!';
    
    echo '✅ Email has been sent successfully!';
    
} catch (Exception $e) {
    echo "❌ Email could not be sent. Error: {$mail->ErrorInfo}";
    error_log("Mail Error: " . $mail->ErrorInfo);
}
?>