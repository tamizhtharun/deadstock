<?php require_once('header.php'); ?>

<?php
if(!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_brands WHERE brand_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	if( $total == 0 ) {
		header('location: logout.php');
		exit;
	}
}
?>

<?php
	// Getting photo ID to unlink from folder
	$statement = $pdo->prepare("SELECT * FROM tbl_brands WHERE brand_id=?");
	$statement->execute(array($_REQUEST['id']));
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	foreach ($result as $row) {
		$brand_logo = $row['brand_logo'];
		unlink('../assets/uploads/brand-logos/'.$brand_logo);
	}


	// Delete from tbl_brands
	$statement = $pdo->prepare("DELETE FROM tbl_brands WHERE brand_id=?");
	$statement->execute(array($_REQUEST['id']));

	// Delete from tbl_product_photo
	// $statement = $pdo->prepare("DELETE FROM tbl_product_photo WHERE p_id=?");
	// $statement->execute(array($_REQUEST['id']));

	// Delete from tbl_product_size
	// $statement = $pdo->prepare("DELETE FROM tbl_product_size WHERE p_id=?");
	// $statement->execute(array($_REQUEST['id']));

	// // Delete from tbl_product_color
	// $statement = $pdo->prepare("DELETE FROM tbl_product_color WHERE p_id=?");
	// $statement->execute(array($_REQUEST['id']));

	// // Delete from tbl_rating
	// $statement = $pdo->prepare("DELETE FROM tbl_rating WHERE p_id=?");
	// $statement->execute(array($_REQUEST['id']));

	// // Delete from tbl_payment
	// $statement = $pdo->prepare("SELECT * FROM tbl_order WHERE product_id=?");
	// $statement->execute(array($_REQUEST['id']));
	// $result = $statement->fetchAll(PDO::FETCH_ASSOC);							
	// foreach ($result as $row) {
	// 	$statement1 = $pdo->prepare("DELETE FROM tbl_payment WHERE payment_id=?");
	// 	$statement1->execute(array($row['payment_id']));
	// }

	// Delete from tbl_order
	// $statement = $pdo->prepare("DELETE FROM tbl_order WHERE product_id=?");
	// $statement->execute(array($_REQUEST['id']));

	header('location: brand-management.php');
?>