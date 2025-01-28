<?php
session_start();
require_once '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_session']['id'])) {
    $user_id = $_SESSION['user_session']['id'];
    $address_id = $_POST['address_id'];
    
    try {
        // First check if this is the default address
        $check_query = "SELECT is_default FROM users_addresses WHERE id = :id AND user_id = :user_id";
        $check_stmt = $pdo->prepare($check_query);
        $check_stmt->execute([
            ':id' => $address_id,
            ':user_id' => $user_id
        ]);
        $address = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($address && $address['is_default']) {
            header('Location: profile.php?tab=addresses&error=Cannot delete default address. Please set another address as default first');
            exit;
        }
        
        // Delete the address
        $query = "DELETE FROM users_addresses WHERE id = :id AND user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute([
            ':id' => $address_id,
            ':user_id' => $user_id
        ]);
        
        if ($result) {
            header('Location: profile.php?tab=addresses&success=Address deleted successfully');
        } else {
            header('Location: profile.php?tab=addresses&error=Failed to delete address');
        }
    } catch (PDOException $e) {
        header('Location: profile.php?tab=addresses&error=Database error occurred');
    }
    exit;
}