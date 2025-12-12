<?php
session_start();
require_once('header.php');

// Updated paths to go up one more directory level
require_once('../PHPMailer/src/PHPMailer.php');
require_once('../PHPMailer/src/SMTP.php');
require_once('../PHPMailer/src/Exception.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Rest of the code remains the same
if (!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
}

try {
    // Check if the id is valid
    $statement = $pdo->prepare("SELECT * FROM sellers WHERE seller_id = ?");
    $statement->execute([$_REQUEST['id']]);

    if ($statement->rowCount() === 0) {
        header('location: logout.php');
        exit;
    }

    $row = $statement->fetch(PDO::FETCH_ASSOC);
    $seller_status = $row['seller_status'];
    $seller_name = $row['seller_name'];
    $seller_email = $row['seller_email'];

    // Toggle status
    $final = $seller_status == 0 ? 1 : 0;

    // Update seller status
    $statement = $pdo->prepare("UPDATE sellers SET seller_status = ? WHERE seller_id = ?");
    $statement->execute([$final, $_REQUEST['id']]);

    // Send email only if account is activated
    if ($final == 1) {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
                    $mail->Host       = 'smtp.zoho.in';      // try smtp.zoho.in, or smtp.zoho.com if that fails
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'support@destock.in';
                    $mail->Password   = '3q7Y4a0bnfni';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // 'tls'
                    $mail->Port       = 587;

        // Recipients
        $mail->setFrom('support@destock.in', 'Deadstock');
        $mail->addAddress($seller_email, $seller_name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Account Activation';
        $mail->Body = <<<HTML
            <h1>Hello, {$seller_name}!</h1>
            <p>Your account has been activated by Admin. You can now log in and start using our platform.</p>
            <br>
            <p>Best regards,<br>Destock</p>
HTML;

        $mail->send();
        error_log("Activation email sent successfully to $seller_email");
    }

    // Set success message and redirect back
    $_SESSION['success_message'] = 'Seller status updated successfully.';
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: $referrer");
    exit;

} catch (Exception $e) {
    error_log("Error processing seller activation: " . $e->getMessage());
    // Set error message and redirect back to the same page
    $_SESSION['error_message'] = 'An error occurred while processing your request.';
    $referrer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    header("Location: $referrer");
    exit;
}
?>