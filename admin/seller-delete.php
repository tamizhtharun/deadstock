<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM sellers WHERE seller_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<?php

	// Delete from tbl_seller
	$statement = $pdo->prepare("DELETE FROM sellers WHERE seller_id=?");
	$statement->execute(array($_REQUEST['id']));

	// Delete from tbl_rating
	// $statement = $pdo->prepare("DELETE FROM tbl_rating WHERE cust_id=?");
	// $statement->execute(array($_REQUEST['id']));

	header('location: seller.php');
?>