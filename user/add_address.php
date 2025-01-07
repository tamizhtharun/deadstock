<?php
session_start();
require_once '../db_connection.php'; // Include your database connection script

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_session']['id'])) {
    $user_id = $_SESSION['user_session']['id'];
    $full_name = $_POST['addr_name'];
    $phone_number = $_POST['addr_phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $pincode = $_POST['pincode'];
    $address_type = $_POST['type'];
    $is_default = isset($_POST['default']) ? 1 : 0;

    // If the new address is set as default, unset other default addresses for the user
    if ($is_default) {
        $query = "UPDATE users_addresses SET is_default = 0 WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
    }

    // Insert the new address
    $query = "INSERT INTO users_addresses (user_id, full_name, phone_number, address, city, state, pincode, address_type, is_default) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('isssssssi', $user_id, $full_name, $phone_number, $address, $city, $state, $pincode, $address_type, $is_default);

    if ($stmt->execute()) {
        header('Location: profile.php?tab=addresses&success=Address added successfully');
    } else {
        header('Location: profile.php?tab=addresses&error=Failed to add address');
    }
    exit;
}
