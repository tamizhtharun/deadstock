<?php require_once('header.php'); ?>

<?php
$p_ids = [];
// Preventing the direct access of this page.
if (!isset($_REQUEST['id'])) {
	header('location: logout.php');
	exit;
} else {
	// Check the id is valid or not
	$statement = $pdo->prepare("SELECT * FROM tbl_end_category WHERE ecat_id=?");
	$statement->execute(array($_REQUEST['id']));
	$total = $statement->rowCount();
	if ($total == 0) {
		header('location: logout.php');
		exit;
	}
}
?>

<?php


// Getting all ecat ids
$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE ecat_id=?");
$statement->execute(array($_REQUEST['id']));
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
	$p_ids[] = $row['id'];
}


for ($i = 0; $i < count($p_ids); $i++) {

	// Getting photo ID to unlink from folder
	$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE id=?");
	$statement->execute(array($p_ids[$i]));
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	foreach ($result as $row) {
		$p_featured_photo = $row['p_featured_photo'];
		unlink('../assets/uploads/' . $p_featured_photo);
	}

	// Getting other photo ID to unlink from folder
	$statement = $pdo->prepare("SELECT * FROM tbl_product_photo WHERE p_id=?");
	$statement->execute(array($p_ids[$i]));
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	foreach ($result as $row) {
		$photo = $row['photo'];
		unlink('../assets/uploads/product_photos/' . $photo);
	}

	// Delete from tbl_photo
	$statement = $pdo->prepare("DELETE FROM tbl_product WHERE id=?");
	$statement->execute(array($p_ids[$i]));

	// Delete from tbl_product_photo
	$statement = $pdo->prepare("DELETE FROM tbl_product_photo WHERE p_id=?");
	$statement->execute(array($p_ids[$i]));
}

// Delete from tbl_end_category
$statement = $pdo->prepare("DELETE FROM tbl_end_category WHERE ecat_id=?");
$statement->execute(array($_REQUEST['id']));

header('location: end-category.php');
?>