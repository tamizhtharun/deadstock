<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>View Brands</h1>
	</div>
	<div class="content-header-right">
		<a href="brand-add.php" class="btn btn-primary btn-sm">Add Brand</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-hover table-striped">
          <thead>
            <tr>
              <th style="width: 5%;">S.no</th>
              <th style="width: 20%;">Brand Logo</th>
              <th>Brand Name</th>
			  <th>Category</th>
              <th>Brand Description</th>
              <th style="width: 15%;">Action</th>
            </tr>
          </thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT t1.brand_id, t1.brand_name, t1.tcat_id, 
    																	t1.brand_description, t1.brand_logo, t2.tcat_name 
    																	FROM tbl_brands t1
    																		JOIN tbl_top_category t2 ON t1.tcat_id = t2.tcat_id");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);							
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td ><img src="../assets/uploads/brand-logos/<?php echo $row['brand_logo']; ?>" alt="<?php echo $row['brand_name'];?>" style="width:60px"></td>
                                    <td><?php echo $row['brand_name']; ?></td>
                                    <td><?php echo $row['tcat_name']; ?></td>
                                    <td><?php echo $row['brand_description']; ?></td>
									<td>										
										<a href="brand-edit.php?id=<?php echo $row['brand_id']; ?>" class="btn btn-primary btn-xs">Edit</a>
										<a href="#" class="btn btn-danger btn-xs" data-href="brand-delete.php?id=<?php echo $row['brand_id']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>  
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
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>
								
<?php require_once('footer.php'); ?>
