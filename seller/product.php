<?php require_once('header.php'); ?>
<!-- <?php
echo "<pre>";
print_r($_SESSION['seller_session']); // Display all session variables
echo "</pre>";
?> -->


<section class="content-header">
	<div class="content-header-left">
		<h1>View Products</h1>
	</div>
	<div class="content-header-right">
		<a href="product-add.php" class="btn btn-primary btn-sm">Add Product</a>
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
								<th width="40">Old Price</th>
								<th width="40">(C) Price</th>
								<th width="40">Quantity</th>
								<th>Featured?</th>
								<th>Approval Status</th>
								<th>Category</th>
								<th>Product Catalogue</th>
								<th width="80">Action</th>
							</tr>
						</thead>
						<tbody>
						<?php
						// Assuming the session has already been started and seller_id is set
						$seller_id = $_SESSION['seller_session']['seller_id']; // Get the seller ID from the session
						$i = 0;
						$statement = $pdo->prepare("SELECT
																						t1.id,
																						t1.p_name,
																						t1.p_old_price,
																						t1.p_current_price,
																						t1.p_qty,
																						t1.p_featured_photo,
																						t1.p_is_featured,
																						t1.p_is_approve,
																						t1.product_catalogue,
																						t1.product_brand,
																						t1.ecat_id,
																						t2.ecat_id,
																						t2.ecat_name,
																						t3.mcat_id,
																						t3.mcat_name,
																						t4.tcat_id,
																						t4.tcat_name,
																						t5.brand_id,
																						t5.brand_name

																				FROM tbl_product t1
																				JOIN tbl_end_category t2 ON t1.ecat_id = t2.ecat_id
																				JOIN tbl_mid_category t3 ON t2.mcat_id = t3.mcat_id
																				JOIN tbl_top_category t4 ON t3.tcat_id = t4.tcat_id
																				JOIN tbl_brands t5 ON t1.product_brand = t5.brand_id
																				WHERE t1.seller_id = :seller_id  -- Filter by seller_id
																				ORDER BY t1.id DESC");
						$statement->bindParam(':seller_id', $seller_id, PDO::PARAM_INT); // Bind the seller_id parameter
						$statement->execute();
						$result = $statement->fetchAll(PDO::FETCH_ASSOC);
						foreach ($result as $row) {
								$i++;
								?>
								<tr>
										<td><?php echo $i; ?></td>
										<td style="width:82px;"><img src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>" alt="<?php echo $row['p_name']; ?>" style="width:80px;"></td>
										<td><?php echo $row['brand_name']; ?></td>
										<td><?php echo $row['p_name']; ?></td>
										<td>₹<?php echo $row['p_old_price']; ?></td>
										<td>₹<?php echo $row['p_current_price']; ?></td>
										<td><?php echo $row['p_qty']; ?></td>
										<td>
												<?php if($row['p_is_featured'] == 1) {echo '<span class="badge badge-success" style="background-color:green;">Yes</span>';} else {echo '<span class="badge badge-success" style="background-color:red;">No</span>';} ?>
										</td>
										<td>
												<?php if($row['p_is_approve'] == 1) {echo '<span class="badge badge-success" style="background-color:green;">Approved</span>';} else {echo '<span class="badge badge-danger" style="background-color:red;">Not Approved</span>';} ?>
										</td>
										<td><?php echo $row['tcat_name']; ?><br><?php echo $row['mcat_name']; ?><br><?php echo $row['ecat_name']; ?></td>
										<td><a href="../assets/uploads/product-catalogues/<?php echo $row['product_catalogue']?>">View Uploaded catalogue</a> </td>
										<td>										
												<a href="product-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-xs">Edit</a>
												<a href="#" class="btn btn-danger btn-xs" data-href="product-delete.php?id=<?php echo $row['id']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>  
										</td>
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


<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Delete Confirmation</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure want to delete this item?</p>
                <p style="color:red;">Be careful! This product will be deleted from the list it cannot be retrieved</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>

<?php require_once('footer.php'); ?>