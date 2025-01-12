<?php
include('C:/xampp/htdocs/deadstock/db_connection.php');

// Set timezone and get current date/time
date_default_timezone_set('Asia/Kolkata');
$currentTime = date('H:i:s', time());
$currentDate = date('Y-m-d');

// Get times from bid_settings
$settingsQuery = "SELECT send_time, close_time, last_updated FROM bid_settings where id ='1'";
$settings = $conn->query($settingsQuery);
$timeRow = $settings->fetch_assoc();

if ($timeRow) {
    $sendTime = $timeRow['send_time'];
    $closeTime = $timeRow['close_time'];
    $lastUpdated = $timeRow['last_updated'];
    
    // Flag to track if any updates were made
    $updatedToday = false;
    
    if($currentDate > $lastUpdated ){
    // Update status from 0 to 1 at send_time
    if ($currentTime >= $sendTime) {
        $updateQuery1 = "UPDATE bidding SET bid_status = '1' WHERE bid_status = '0'";
        if ($conn->query($updateQuery1)) {
            if ($conn->affected_rows > 0) {
                $updatedToday = true;
            }
        }
    }
    
    
}else{
    echo "Already updates today" ."<br>";

}

    // Update status from 1 to 3 at close_time
    if ($currentTime >= $closeTime) {
        $updateQuery2 = "UPDATE bidding SET bid_status = '3' WHERE bid_status = '1'";
        if ($conn->query($updateQuery2)) {
            if ($conn->affected_rows > 0) {
                $updatedToday = true;
            }
        }
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
    echo "current date: ". ($currentDate);
    
} else {
    echo "No settings found in bid_settings table";
}
?>
<script>
        setTimeout(function() {
            location.reload();
        }, 1000);
</script>