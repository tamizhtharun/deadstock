<?php
// auto_update_seller_status.php

// Database connection
require_once('../db_connection.php'); // Adjust this path to your database configuration file

try {
    // Get all sellers and their brand certification validity dates
    $query = "
        SELECT 
            s.seller_id,
            s.seller_status,
            s.seller_name,
            s.seller_email,
            MIN(sb.valid_to) as certification_end_date
        FROM 
            sellers s
        LEFT JOIN 
            seller_brands sb ON s.seller_id = sb.seller_id
        GROUP BY 
            s.seller_id
    ";
    
    $statement = $pdo->prepare($query);
    $statement->execute();
    $sellers = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Current date for comparison
    $current_date = date('Y-m-d');
    
    foreach ($sellers as $seller) {
        $seller_id = $seller['seller_id'];
        $valid_to_date = $seller['certification_end_date'] ? date('Y-m-d', strtotime($seller['certification_end_date'])) : null;
        
        // Determine the new status
        $new_status = null;
        
        if ($valid_to_date) {
            if ($current_date > $valid_to_date) {
                // If certification has expired, set status to 0
                $new_status = 0;
            } else {
                // If certification is still valid, set status to 1
                $new_status = 1;
            }
        }
        
        // Update status if it needs to be changed
        if ($new_status !== null && $new_status != $seller['seller_status']) {
            $update_query = "UPDATE sellers SET seller_status = ? WHERE seller_id = ?";
            $update_stmt = $pdo->prepare($update_query);
            $update_stmt->execute([$new_status, $seller_id]);
            
            // Log the status change
            $log_message = sprintf(
                "Seller ID: %d status changed to %d on %s (Certification valid to: %s)",
                $seller_id,
                $new_status,
                date('Y-m-d H:i:s'),
                $valid_to_date
            );
            error_log($log_message, 3, "logs/seller_status_updates.log");
            
            // If status changed to 1, send notification email
            if ($new_status == 1) {
                require 'phpmailer/src/PHPMailer.php';
                require 'phpmailer/src/SMTP.php';
                require 'phpmailer/src/Exception.php';
                
                try {
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'nithiishhh@gmail.com';
                    $mail->Password = 'meknepblkzosmavu';
                    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('nithiishhh@gmail.com', 'Deadstock');
                    $mail->addAddress($seller['seller_email'], $seller['seller_name']);

                    $mail->isHTML(true);
                    $mail->Subject = 'Account Activation Status Update';
                    $mail->Body = "<h1>Hello, {$seller['seller_name']}!</h1>
                                 <p>Your account has been activated as your certification is valid.</p>
                                 <p>Your certification is valid until: {$valid_to_date}</p>
                                 <br>
                                 <p>Best regards,<br>Deadstock</p>";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email could not be sent to {$seller['seller_email']}. Error: " . $mail->ErrorInfo);
                }
            } else if ($new_status == 0) {
                // Send notification for deactivation due to expired certification
                try {
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    // ... (same email setup as above)
                    
                    $mail->Subject = 'Account Deactivation Notice';
                    $mail->Body = "<h1>Hello, {$seller['seller_name']}!</h1>
                                 <p>Your account has been deactivated as your certification has expired on: {$valid_to_date}</p>
                                 <p>Please renew your certification to reactivate your account.</p>
                                 <br>
                                 <p>Best regards,<br>Deadstock</p>";

                    $mail->send();
                } catch (Exception $e) {
                    error_log("Email could not be sent to {$seller['seller_email']}. Error: " . $mail->ErrorInfo);
                }
            }
        }
    }
    
    echo "Seller status update process completed successfully.\n";
    
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo "An error occurred during the update process.\n";
}
?>