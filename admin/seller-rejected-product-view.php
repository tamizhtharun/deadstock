<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>View Rejected Products</h1>
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
								<th width="10">#</th>
								<th>Photo</th>
								<th width="160">Product Name</th>
								<th width="60">Old Price</th>
								<th width="60">(C) Price</th>
								<th width="60">Quantity</th>
								<th>Featured?</th>
								<th>Active?</th>
								<th>Category</th>
								<th width="80">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0; // Get seller_id from URL
							
              $statement = $pdo->prepare("SELECT
                      t1.id,
                      t1.p_name,
                      t1.p_old_price,
                      t1.p_current_price,
                      t1.p_qty,
                      t1.p_featured_photo,
                      t1.p_is_featured,
                      t1.p_is_active,
                      t1.p_is_approve,
                      t1.ecat_id,
                      t2.ecat_id,
                      t2.ecat_name,
                      t3.mcat_id,
                      t3.mcat_name,
                      t4.tcat_id,
                      t4.tcat_name
                  FROM tbl_product t1
                  JOIN tbl_end_category t2 ON t1.ecat_id = t2.ecat_id
                  JOIN tbl_mid_category t3 ON t2.mcat_id = t3.mcat_id
                  JOIN tbl_top_category t4 ON t3.tcat_id = t4.tcat_id
                  WHERE t1.seller_id = :seller_id AND t1.p_is_approve = 0  -- Filter by seller_id and approved products
                  ORDER BY t1.id DESC");
							$statement->bindParam(':seller_id', $seller_id, PDO::PARAM_INT); // Bind seller_id parameter
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td style="width:82px;"><img src="../assets/uploads/<?php echo $row['p_featured_photo']; ?>" alt="<?php echo $row['p_name']; ?>" style="width:80px;"></td>
									<td><?php echo $row['p_name']; ?></td>
									<td>$<?php echo $row['p_old_price']; ?></td>
									<td>$<?php echo $row['p_current_price']; ?></td>
									<td><?php echo $row['p_qty']; ?></td>
									<td>
										<?php if($row['p_is_featured'] == 1) {echo '<span class="badge badge-success" style="background-color:green;">Yes</span>';} else {echo '<span class="badge badge-success" style="background-color:red;">No</span>';} ?>
									</td>
									<td>
										<?php if($row['p_is_active'] == 1) {echo '<span class="badge badge-success" style="background-color:green;">Yes</span>';} else {echo '<span class="badge badge-danger" style="background-color:red;">No</span>';} ?>
									</td>
									<td><?php echo $row['tcat_name']; ?><br><?php echo $row['mcat_name']; ?><br><?php echo $row['ecat_name']; ?></td>
                  <td><?php echo $row['p_is_approve'] == 1 ? '<span class="badge badge-success" style="background-color:green;">Approved</span>' : '<span class="badge badge-danger" style="background-color:red;">Rejected</span>'; ?></td>

                  <!-- <td>										
                  <button class="btn btn-danger btn-xs" disabled>Rejected</button>
                      <a href="product-delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                  </td> -->
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>

<?php require_once('footer.php'); ?>