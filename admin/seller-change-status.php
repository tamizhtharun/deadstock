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
	} else {
		$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
		foreach ($result as $row) {
			$seller_status = $row['seller_status'];
		}
	}
}
?>

<?php
if($seller_status == 0) {$final = 1;} else {$final = 0;}
$statement = $pdo->prepare("UPDATE sellers SET seller_status=? WHERE seller_id=?");
$statement->execute(array($final,$_REQUEST['id']));

header('location: seller.php');
?>