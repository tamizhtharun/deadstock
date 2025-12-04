<?php
session_start();
require '../db_connection.php';

if (!isset($_SESSION['user_session']['id'])) {
    header('Location: profile.php?error=User not logged in');
    exit;
}

$userId = $_SESSION['user_session']['id'];

function validateIndianPhoneNumber($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    return (
        strlen($phone) === 10 && 
        in_array($phone[0], ['6', '7', '8', '9'])
    );
}

function validateGST($gst) {
    // Check if GST is empty (as it might be optional)
    if (empty($gst)) {
        return true;
    }

    // Basic GST format validation
    // Format: 2 digits state code + 10 digits PAN + 1 digit entity number + 1 digit check sum
    $pattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';
    
    if (!preg_match($pattern, $gst)) {
        return false;
    }

    // Validate state code (first two digits)
    $validStateCodes = [
        '01', '02', '03', '04', '05', '06', '07', '08', '09', '10',
        '11', '12', '13', '14', '15', '16', '17', '18', '19', '20',
        '21', '22', '23', '24', '25', '26', '27', '28', '29', '30',
        '31', '32', '33', '34', '35', '36', '37'
    ];
    
    $stateCode = substr($gst, 0, 2);
    if (!in_array($stateCode, $validStateCodes)) {
        return false;
    }

    return true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $user_gst = htmlspecialchars(trim($_POST['user_gst']));
    $profilePhoto = $_FILES['profile_photo'] ?? null;

    if (empty($name) || empty($email) || empty($phone)) {
        header('Location: profile.php?error=All fields are required');
        exit;
    }

    // Validate phone number
    if (!validateIndianPhoneNumber($phone)) {
        header('Location: profile.php?error=Invalid phone number format');
        exit;
    }

    // Validate GST number
    if (!validateGST($user_gst)) {
        header('Location: profile.php?error=Invalid GST number format');
        exit;
    }

    // Initialize variable for profile image filename
    $profileImageFilename = null;
    
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
        $profileImageFilename = 'profile-' . $userId . '-' . time() . '.' . $ext;
    
        // Define the upload directory (relative to this file)
        $uploadDir = __DIR__ . '/uploads/profile-photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
    
        $targetFilePath = $uploadDir . $profileImageFilename;
    
        // Move the uploaded file to the target directory
        if (!move_uploaded_file($path_tmp, $targetFilePath)) {
            header('Location: profile.php?error=Failed to upload the profile photo');
            exit;
        }
    }
    
    try {
        $pdo->beginTransaction();

        // Update user details in the database
        $query = "UPDATE users SET username = :name, email = :email, phone_number = :phone, user_gst = :user_gst";
        $params = [
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':user_id' => $userId,
            ':user_gst' => $user_gst
        ];

        // Update the profile photo filename if uploaded
        if ($profileImageFilename) {
            $query .= ", profile_image = :profile_image";
            $params[':profile_image'] = $profileImageFilename; // Store only filename
        }

        $query .= " WHERE id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        $pdo->commit();

        // Update session variables
        $_SESSION['user_session']['username'] = $name;
        $_SESSION['user_session']['email'] = $email;
        $_SESSION['user_session']['phone_number'] = $phone;
        $_SESSION['user_session']['user_gst'] = $user_gst;
        if ($profileImageFilename) {
            $_SESSION['user_session']['profile_image'] = $profileImageFilename;
        }

        header('Location: profile.php?success=Profile updated successfully');
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        header('Location: profile.php?error=Failed to update profile');
        exit();
    }
} else {
    header('Location: profile.php?error=Invalid request method');
    exit();
}