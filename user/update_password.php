<?php
session_start();
include '../db_connection.php';

if (!isset($_SESSION['user_session']['id'])) {
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_session']['id'];
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    // Validate password requirements
    if (strlen($newPassword) < 8 || 
        !preg_match("/[A-Z]/", $newPassword) || 
        !preg_match("/[a-z]/", $newPassword) || 
        !preg_match("/[0-9]/", $newPassword)) {
        header('Location: profile.php?tab=password&error=Password must be at least 8 characters and contain uppercase, lowercase, and numbers');
        exit;
    }

    // Verify passwords match
    if ($newPassword !== $confirmPassword) {
        header('Location: profile.php?tab=password&error=New passwords do not match');
        exit;
    }

    try {
        // First verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        // Debug statement (remove in production)
        // error_log("Stored hash: " . $user['password']);
        // error_log("Current password: " . $currentPassword);
        
        // Try both MD5 and password_verify for compatibility
        if (!$user || (!password_verify($currentPassword, $user['password']) && password_hash($currentPassword, PASSWORD_DEFAULT) !== $user['password'])) {
            header('Location: profile.php?tab=password&error=Current password is incorrect');
            exit;
        }

        // Update password - use the same hashing method as your login system
        // If your login system uses MD5:
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        // If your login system uses password_hash:
        // $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $updateStmt->execute([$hashedPassword, $userId]);

        header('Location: profile.php?tab=password&success=Password updated successfully');
        exit;

    } catch (PDOException $e) {
        error_log("Password update error: " . $e->getMessage());
        header('Location: profile.php?tab=password&error=An error occurred while updating password');
        exit;
    }
}
?>