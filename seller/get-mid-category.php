<?php
// Simple test endpoint for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../db_connection.php';

header('Content-Type: text/html; charset=utf-8');

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    
    try {
        $statement = $pdo->prepare("SELECT * FROM tbl_mid_category WHERE tcat_id=?");
        $statement->execute(array($id));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<option value="">Select Mid Level Category</option>';
        foreach ($result as $row) {
            echo '<option value="' . htmlspecialchars($row['mcat_id']) . '">' . htmlspecialchars($row['mcat_name']) . '</option>';
        }
    } catch(PDOException $e) {
        echo '<option value="">Error: ' . htmlspecialchars($e->getMessage()) . '</option>';
    }
} else {
    echo '<option value="">No ID provided</option>';
}
?>