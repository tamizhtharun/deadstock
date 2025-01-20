<?php require_once('header.php'); ?>
<!-- <?php
echo "<pre>";
print_r($_SESSION['seller_session']); // Display all session variables
echo "</pre>";
?> -->


<section class="content-header">
	<div class="content-header-left">
		<h1>Revenue</h1>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-hover table-striped">
					<thead class="thead-dark">
							<tr>
								<th>#</th>
								<th>Photo</th>
								<th>Product Brand</th>
								<th width="160">Product Name</th>
								<th width="40">(C) Final Price</th>
								<th width="40">Quantity</th>
                                <th>Delivery Status</th>
                                
								<th>Order Time</th>
							</tr>
						</thead>
						<tbody>
						<?php
						// Assuming the session has already been started and seller_id is set
						$seller_id = $_SESSION['seller_session']['seller_id']; // Get the seller ID from the session
                        $i = 0;
                        $statement = $pdo->prepare("SELECT 
                        t1.p_featured_photo,
                        t1.product_brand,
                        t1.p_name,
                        t2.quantity,
                        t2.delivery_status,
                        t2.price,
                        t2.updated_at
                    FROM tbl_orders t2
                    JOIN tbl_product t1 ON t2.product_id = t1.id
                    WHERE t1.seller_id = :seller_id
                    ORDER BY t2.updated_at DESC");
                    $statement->bindParam(':seller_id', $seller_id, PDO::PARAM_INT); // Bind the seller_id parameter
                    $statement->execute();
                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($result as $row) {
    $i++;
    ?>
    <tr>
        <td><?php echo $i; ?></td>
        <td style="width:82px;">
            <img src="../assets/uploads/product-photos/<?php echo htmlspecialchars($row['p_featured_photo']); ?>" 
                 alt="<?php echo htmlspecialchars($row['p_name']); ?>" 
                 style="width:80px;">
        </td>
        <td><?php echo htmlspecialchars($row['product_brand']); ?></td>
        <td><?php echo htmlspecialchars($row['p_name']); ?></td>
        <td>₹<?php echo htmlspecialchars($row['price']); ?></td>
        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
        <td>
			<?php if($row['delivery_status'] == 1) {echo '<span class="badge badge-success" style="background-color:green;">Delivered</span>';} else {echo '<span class="badge badge-warning" style="background-color:red;">Not delivered</span>';} ?>
		</td>
		<td><?php echo htmlspecialchars($row['updated_at']); ?></td>
    </tr>
    <?php
}
?>
			
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>



<?php require_once('footer.php'); ?>