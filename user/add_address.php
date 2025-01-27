<?php
session_start();
require_once '../db_connection.php';

// phone number validation
function validateIndianPhoneNumber($phone) {
    // Remove any non-digit characters
    $phone = preg_replace('/\D/', '', $phone);
    
    // Check if the number is exactly 10 digits and starts with 6-9
    return (
        strlen($phone) === 10 && 
        in_array($phone[0], ['6', '7', '8', '9'])
    );
}

// Add validation before processing the address
$phone_number = $_POST['addr_phone'];
if (!validateIndianPhoneNumber($phone_number)) {
    header('Location: profile.php?tab=addresses&error=Invalid phone number. Please enter a valid 10-digit mobile number');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_session']['id'])) {
    $user_id = $_SESSION['user_session']['id'];
    
    // Check if it's an edit or add action
    $action = $_POST['action'] ?? 'add';

    if ($action === 'add') {
        // Count existing addresses for the user
        $count_query = "SELECT COUNT(*) as addr_count FROM users_addresses WHERE user_id = ?";
        $count_stmt = $conn->prepare($count_query);
        $count_stmt->bind_param('i', $user_id);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result()->fetch_assoc();
        
        // Check if user already has 2 addresses
        if ($count_result['addr_count'] >= 2) {
            header('Location: profile.php?tab=addresses&error=Maximum of two addresses only allowed');
            exit;
        }

        // Determine address type based on existing addresses
        $type_query = "SELECT COUNT(*) as primary_count FROM users_addresses WHERE user_id = ? AND address_type = 'PRIMARY'";
        $type_stmt = $conn->prepare($type_query);
        $type_stmt->bind_param('i', $user_id);
        $type_stmt->execute();
        $type_result = $type_stmt->get_result()->fetch_assoc();
        
        $address_type = $type_result['primary_count'] == 0 ? 'PRIMARY' : 'SECONDARY';
    }

    $full_name = $_POST['addr_name'];
    $phone_number = $_POST['addr_phone'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $pincode = $_POST['pincode'];
    $is_default = isset($_POST['default']) ? 1 : 0;

    // If the new address is set as default, unset other default addresses for the user
    if ($is_default) {
        $reset_query = "UPDATE users_addresses SET is_default = 0 WHERE user_id = ?";
        $reset_stmt = $conn->prepare($reset_query);
        $reset_stmt->bind_param('i', $user_id);
        $reset_stmt->execute();
    }

    if ($action === 'add') {
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
}}