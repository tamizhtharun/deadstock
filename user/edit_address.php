<?php
session_start();
require_once '../db_connection.php';

// Validate phone number function
function validateIndianPhoneNumber($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    return (strlen($phone) === 10 && in_array($phone[0], ['6', '7', '8', '9']));
}

// Handle GET request to fetch address details
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $address_id = $_GET['id'];
    $user_id = $_SESSION['user_session']['id'];
    
    // Use PDO for consistency
    $query = "SELECT * FROM users_addresses WHERE id = :id AND user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':id' => $address_id,
        ':user_id' => $user_id
    ]);
    
    $address = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($address) {
        header('Content-Type: application/json');
        echo json_encode($address);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Address not found']);
    }
    exit;
}

// Handle POST request to update address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_session']['id'])) {
    $user_id = $_SESSION['user_session']['id'];
    $address_id = $_POST['address_id'];
    
    // Validate phone number
    $phone_number = $_POST['addr_phone'];
    if (!validateIndianPhoneNumber($phone_number)) {
        header('Location: profile.php?tab=addresses&error=Invalid phone number. Please enter a valid 10-digit mobile number');
        exit;
    }

    // If this address is being set as default, unset other default addresses
    $is_default = isset($_POST['default']) ? 1 : 0;
    if ($is_default) {
        $reset_query = "UPDATE users_addresses SET is_default = 0 WHERE user_id = :user_id";
        $reset_stmt = $pdo->prepare($reset_query);
        $reset_stmt->execute([':user_id' => $user_id]);
    }

    // Update address
    $query = "UPDATE users_addresses 
              SET full_name = :full_name, 
                  phone_number = :phone_number, 
                  address = :address, 
                  city = :city, 
                  state = :state, 
                  pincode = :pincode, 
                  is_default = :is_default,
                  updated_at = CURRENT_TIMESTAMP
              WHERE id = :id AND user_id = :user_id";
    
    try {
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([
            ':full_name' => $_POST['addr_name'],
            ':phone_number' => $phone_number,
            ':address' => $_POST['address'],
            ':city' => $_POST['city'],
            ':state' => $_POST['state'],
            ':pincode' => $_POST['pincode'],
            ':is_default' => $is_default,
            ':id' => $address_id,
            ':user_id' => $user_id
        ]);

        if ($result) {
            header('Location: profile.php?tab=addresses&success=Address updated successfully');
        } else {
            header('Location: profile.php?tab=addresses&error=Failed to update address');
        }
    } catch (PDOException $e) {
        header('Location: profile.php?tab=addresses&error=Database error occurred');
    }
    exit;
}