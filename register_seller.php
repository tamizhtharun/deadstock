<?php
// Database connection parameters
include 'db_connection.php';

// Prepare and bind for sellers table
$stmt = $conn->prepare("INSERT INTO sellers (seller_name, seller_cname, seller_email, seller_phone, seller_gst, seller_address, seller_state, seller_city, seller_zipcode, seller_password, seller_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssssssss", $seller_name, $seller_cname, $seller_email, $seller_phone, $seller_gst, $seller_address, $seller_state, $seller_city, $seller_zipcode, $seller_password, $seller_status);

// Set parameters and execute for sellers table
$seller_name = $_POST['seller_name'];
$seller_cname = $_POST['seller_cname'];
$seller_email = $_POST['seller_email'];
$seller_phone = $_POST['seller_phone'];
$seller_gst = $_POST['seller_gst'];
$seller_address = $_POST['seller_address'];
$seller_state = $_POST['seller_state'];
$seller_city = $_POST['seller_city'];
$seller_zipcode = $_POST['seller_zipcode'];
$seller_password = password_hash($_POST['seller_password'], PASSWORD_DEFAULT); // Hash the password for security
$seller_status = 0; // Set default status to 0 (inactive)

if ($stmt->execute()) {
    // Prepare and bind for user_login table
    $stmt_login = $conn->prepare("INSERT INTO user_login (user_name, user_email, user_password, user_role) VALUES (?, ?, ?, ?)");
    $stmt_login->bind_param("ssss", $user_name, $user_email, $user_password_hashed, $user_role);

    // Set parameters for user_login
    $user_name = $seller_name; // Use seller_name as user_name
    $user_email = $seller_email; // Use seller_email as user_email
    $user_password_hashed = $seller_password; // Use the hashed password
    $user_role = 'seller'; // Default role

    // Execute the user_login insertion
    if ($stmt_login->execute()) {
        echo "<script>alert('New seller registered successfully');window.location.href='index.php';</script>";
    } else {
        echo "Error in user login: " . $stmt_login->error;
    }

    // Close user_login statement
    $stmt_login->close();
} else {
    echo "Error in seller registration: " . $stmt->error;
}

// Close connections
$stmt->close();
$conn->close();
?>