<?php
$host = 'mail.thedeadstock.in'; // Change this to your actual SMTP host
$port = 587; // Try 587 if using TLS
$connection = fsockopen($host, $port, $errno, $errstr, 10);
if (!$connection) {
    echo "SMTP Connection Failed: $errstr ($errno)";
} else {
    echo "SMTP Connection Successful!";
    fclose($connection);
}
?>