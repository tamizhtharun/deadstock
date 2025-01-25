<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['user_session']['id'])) {
    header('Location: profile.php?error=User not logged in');
    exit;
}

$userId = $_SESSION['user_session']['id'];

function validateIndianPhoneNumber($phone) {
    // Remove any non-digit characters
    $phone = preg_replace('/\D/', '', $phone);
    
    // Check if the number is exactly 10 digits and starts with 6-9
    return (
        strlen($phone) === 10 && 
        in_array($phone[0], ['6', '7', '8', '9'])
    );
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input fields
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $profilePhoto = $_FILES['profile_photo'] ?? null;

    // Validate mandatory fields
    if (empty($name) || empty($email) || empty($phone)) {
        header('Location: profile.php?error=All fields are required');
        exit;
    }

    // Initialize variable for profile image path
    $profileImagePath = null;
    
    if ($profilePhoto && $profilePhoto['error'] === UPLOAD_ERR_OK) {
        // Validate the file extension
        $path = $profilePhoto['name'];
        $path_tmp = $profilePhoto['tmp_name'];
        $ext = pathinfo($path, PATHINFO_EXTENSION);
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
        if (!in_array(strtolower($ext), $allowedExtensions)) {
            header('Location: profile.php?error=Invalid file format. Only JPG, JPEG, PNG, and GIF are allowed');
            exit;
        }
    
        // Generate the unique filename based on the user ID
        $final_name = 'profile-' . $userId . '.' . $ext;
    
        // Define the upload directory (use absolute path to avoid errors)
        $uploadDir = __DIR__ . '/uploads/profile_photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
    
        $targetFilePath = $uploadDir . $final_name;
    
        // Move the uploaded file to the target directory
        if (move_uploaded_file($path_tmp, $targetFilePath)) {
            // Save the relative path for storing in the database
            $profileImagePath = '/uploads/profile_photos/' . $final_name;
        } else {
            header('Location: profile.php?error=Failed to upload the profile photo');
            exit;
        }
    }
    
    // Prepare the SQL query
    try {
        // Start a transaction
        $pdo->beginTransaction();

        // Update user details in the database
        $query = "UPDATE users SET username = :name, email = :email, phone_number = :phone";
        $params = [
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':user_id' => $userId
        ];

        // Update the profile photo if uploaded
        if ($profileImagePath) {
            $query .= ", profile_image = :profile_image";
            $params[':profile_image'] = $profileImagePath;
        }

        $query .= " WHERE id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        // Commit the transaction
        $pdo->commit();

        // Update session variables for live updates on the profile page
        $_SESSION['user_session']['username'] = $name;
        $_SESSION['user_session']['email'] = $email;
        $_SESSION['user_session']['phone_number'] = $phone;
        if ($profileImagePath) {
            $_SESSION['user_session']['profile_image'] = $profileImagePath;
        }

        // Redirect to the profile page with a success message
        header('Location: profile.php?success=Profile updated successfully');
        exit();
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        header('Location: profile.php?error=Failed to update profile');
        exit();
    }
} else {
    header('Location: profile.php?error=Invalid request method');
    exit();
}