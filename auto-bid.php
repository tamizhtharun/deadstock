<?php
require'db_connection.php';
//auto-bid.php

// Set timezone and get current date/time
date_default_timezone_set('Asia/Kolkata');
$currentTime = date('H:i:s', time());
$currentDate = date('Y-m-d');

// Get times from bid_settings
$settingsQuery = "SELECT send_time, close_time, last_updated, notifications_sent FROM bid_settings WHERE id = '1'";
$settings = $conn->query($settingsQuery);
$timeRow = $settings->fetch_assoc();

if ($timeRow) {
    $sendTime = $timeRow['send_time'];
    $closeTime = $timeRow['close_time'];
    $lastUpdated = $timeRow['last_updated'];
    $notificationsSent = $timeRow['notifications_sent'];
    
    // Flag to track if any updates were made
    $updatedToday = false;
    
    if($currentDate > $lastUpdated){
        // Reset notifications_sent flag for the new day
        $resetNotificationsQuery = "UPDATE bid_settings SET notifications_sent = 0 WHERE id = '1'";
        $conn->query($resetNotificationsQuery);
        $notificationsSent = 0;

        // Update status from 0 to 1 at send_time
        if ($currentTime >= $sendTime) {
            $updateQuery1 = "UPDATE bidding SET bid_status = '1' WHERE bid_status = '0'";
            if ($conn->query($updateQuery1)) {
                if ($conn->affected_rows > 0) {
                    $updatedToday = true;
                }
            }
        }
    } else {
        echo "Already updated today" ."<br>";
    }

    // Update status and send notifications at close_time
    if ($currentTime >= $closeTime && $notificationsSent == 0) {
        // Update partially approved bids to fully approved
        $updatePartialQuery = "UPDATE bidding SET bid_status = '2' WHERE bid_status = '4' AND DATE(bid_time) = CURRENT_DATE()";
        $conn->query($updatePartialQuery);

        // Update pending bids to rejected
        $updatePendingQuery = "UPDATE bidding SET bid_status = '3' WHERE bid_status = '1' AND DATE(bid_time) = CURRENT_DATE()";
        $conn->query($updatePendingQuery);

        // Add notifications for finalized bids
        $finalizedBidsQuery = "SELECT b.user_id, b.product_id, b.bid_status, b.bid_price, b.bid_quantity, p.p_name 
                               FROM bidding b 
                               JOIN tbl_product p ON b.product_id = p.id 
                               WHERE DATE(b.bid_time) = CURRENT_DATE() AND b.bid_status IN (2, 3)";
        $finalizedBidsResult = $conn->query($finalizedBidsQuery);
        
        while ($bid = $finalizedBidsResult->fetch_assoc()) {
            if ($bid['bid_status'] == 2) {
                $title = "Bid Accepted";
                $message = "Congratulations! Your bid for {$bid['p_name']} has been accepted. Bid details: ₹{$bid['bid_price']} for {$bid['bid_quantity']} unit(s). We'll be in touch soon with next steps. Thank you for your business!";
                $type = "success";
            } else {
                $title = "Bid Rejected";
                $message = "Thank you for your interest in {$bid['p_name']} was not accepted. Bid details: ₹{$bid['bid_price']} for {$bid['bid_quantity']} unit(s). Your account will be refunded within 3-5 business days. We appreciate your participation and hope you'll try again soon.";
                $type = "error";
            }
            
            $insertNotificationQuery = "INSERT INTO notifications (recipient_id, recipient_type, title, message, type) 
                                        VALUES ('{$bid['user_id']}', 'user', '$title', '$message', '$type')";
            $conn->query($insertNotificationQuery);
        }

        // Set notifications_sent flag to 1
        $updateNotificationsSentQuery = "UPDATE bid_settings SET notifications_sent = 1 WHERE id = '1'";
        $conn->query($updateNotificationsSentQuery);

        $updatedToday = true;
    }
    
    // Update last_updated date if any changes were made
    if ($updatedToday) {
        $updateDateQuery = "UPDATE bid_settings SET last_updated = '$currentDate' WHERE id = '1'";
        $conn->query($updateDateQuery);
        echo "Statuses updated and date set to: " . $currentDate . "<br>";
    }
    
    echo "Current Time: " . $currentTime . "<br>";
    echo "Send Time: " . $sendTime . "<br>";
    echo "Close Time: " . $closeTime . "<br>";
    echo "Last Updated: " . ($lastUpdated ? $lastUpdated : "Not yet updated") . "<br>";
    echo "Notifications Sent: " . ($notificationsSent ? "Yes" : "No") . "<br>";
    echo "Current Date: " . $currentDate;
    
} else {
    echo "No settings found in bid_settings table";
}
?>

<script>
    setTimeout(function() {
        location.reload();
    }, 1000);
</script>

