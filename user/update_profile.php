<?php
session_start();
require '../db_connection.php'; // Include your database connection file

// Ensure the user is logged in
if (!isset($_SESSION['user_session']['id'])) {
    die('Error: User not logged in.');
}

// Get the user ID from the session
$userId = $_SESSION['user_session']['id'];

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input fields
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $profilePhoto = $_FILES['profile_photo'] ?? null;

    // Validate mandatory fields
    if (empty($name) || empty($email) || empty($phone)) {
        die('Error: All fields are required.');
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
            die('Error: Invalid file format. Only JPG, JPEG, PNG, and GIF are allowed.');
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
            die('Error: Failed to upload the profile photo.');
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
        header('Location: profile.php?message=Profile updated successfully');
        exit();
    } catch (PDOException $e) {
        // Rollback on error
        $pdo->rollBack();
        die('Error: ' . $e->getMessage());
    }
} else {
    die('Error: Invalid request method.');
}
