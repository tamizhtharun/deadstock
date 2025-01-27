<?php
require_once('header.php');

// Check if id exists
if(!isset($_REQUEST['id'])) {
    header('location: profile-edit.php');
    exit;
}

// Get the certificate file name before deleting
$statement = $pdo->prepare("SELECT brand_certificate FROM seller_brands WHERE brand_id=? AND seller_id=?");
$statement->execute(array($_REQUEST['id'], $_SESSION['seller_session']['seller_id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

if(count($result) > 0) {
    // Delete the certificate file
    unlink('../assets/uploads/certificates/'.$result[0]['brand_certificate']);
    
    // Delete from database
    $statement = $pdo->prepare("DELETE FROM seller_brands WHERE brand_id=? AND seller_id=?");
    $statement->execute(array($_REQUEST['id'], $_SESSION['seller_session']['seller_id']));
}

header('location: profile-edit.php');
?>