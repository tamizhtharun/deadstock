<?php require_once('header.php'); ?>
<?php
require 'C:/xampp/htdocs/deadstock/phpmailer/src/PHPMailer.php';
require 'C:/xampp/htdocs/deadstock/phpmailer/src/SMTP.php';
require 'C:/xampp/htdocs/deadstock/phpmailer/src/Exception.php';

if(!isset($_REQUEST['id'])) {
    header('location: logout.php');
    exit;
} else {
    // Check if the id is valid or not
    $statement = $pdo->prepare("SELECT * FROM sellers WHERE seller_id=?");
    $statement->execute(array($_REQUEST['id']));
    $total = $statement->rowCount();
    if( $total == 0 ) {
        header('location: logout.php');
        exit;
    } else {
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);							
        foreach ($result as $row) {
            $seller_status = $row['seller_status'];
            $seller_name = $row['seller_name'];
            $seller_email = $row['seller_email'];
        }
    }
}

if($seller_status == 0) {
    $final = 1;
} else {
    $final = 0;
}

// Update seller status in the database
$statement = $pdo->prepare("UPDATE sellers SET seller_status=? WHERE seller_id=?");
$statement->execute(array($final, $_REQUEST['id']));

// Send email only if the status is changed to active
if ($final == 1) {
    $admin_name = 'Mr.Arun krishnan'; // Replace this with the logged-in admin's name if available

    try {
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'nithiishhh@gmail.com'; // Replace with your email
        $mail->Password = 'meknepblkzosmavu'; // Replace with your email's app-specific password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email sender and recipient
        $mail->setFrom('nithiishhh@gmail.com', 'Deadstock'); // Replace with sender details
        $mail->addAddress($seller_email, $seller_name); // Recipient email and name

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Account Activation';
        $mail->Body = "<h1>Hello, $seller_name!</h1>
                       <p>Your account has been activated by $admin_name. You can now log in and start using our platform.</p>
                       <br>
                       <p>Best regards,<br>Deadstock</p>";

        // Send the email
        $mail->send();
    } catch (Exception $e) {
        // Log the email error (optional) and continue
        error_log("Email could not be sent to $seller_email. Error: " . $mail->ErrorInfo);
    }
}

// Redirect to the seller page after status update
header('location: seller.php');
exit;
?>