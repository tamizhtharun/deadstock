<?php
include '../db_connection.php';
if($_POST['id'])
{
	$id = $_POST['id'];
	
	$statement = $pdo->prepare("SELECT * FROM tbl_brands WHERE tcat_id=?");
	$statement->execute(array($id));
	$result = $statement->fetchAll(PDO::FETCH_ASSOC);
	?><option value="">Select Brand</option><?php						
	foreach ($result as $row) {
		?>
        <option value="<?php echo $row['brand_id']; ?>"><?php echo $row['brand_name']; ?></option>
		
        <?php
	}
	?>
	<option value="others">Others</option>
	<?php
}
?>
