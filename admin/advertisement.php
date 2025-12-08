<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Manage Advertisements</h1>
	</div>
	<div class="content-header-right">
		<a href="advertisement-add.php" class="btn btn-primary btn-sm">Add Advertisement</a>
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
								<th style="width: 5%;">#</th>
								<th style="width: 20%;">Photo</th>
								<th style="width: 40%;">Title</th>
								<th style="width: 15%;">Category</th>
								<th style="width: 10%;">Status</th>
								<th style="width: 10%;">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT a.*, t.tcat_name FROM tbl_advertisements a LEFT JOIN tbl_top_category t ON a.tcat_id = t.tcat_id ORDER BY a.id DESC");
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								$i++;
								?>
								<tr>
									<td><?php echo $i; ?></td>
									<td style="width:150px;"><img src="../assets/uploads/advertisements/<?php echo $row['photo']; ?>" alt="<?php echo $row['title']; ?>" style="width:140px;"></td>
									<td><?php echo $row['title']; ?></td>
									<td><?php echo $row['tcat_name']; ?></td>
									<td>
										<?php if($row['status'] == 1): ?>
											<span class="badge badge-success">Active</span>
										<?php else: ?>
											<span class="badge badge-danger">Inactive</span>
										<?php endif; ?>
									</td>
									<td>
										<a href="advertisement-edit.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-xs">Edit</a>
										<a href="#" class="btn btn-danger btn-xs" data-href="advertisement-delete.php?id=<?php echo $row['id']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>
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
                <p>Are you sure want to delete this advertisement?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
$('#confirm-delete').on('show.bs.modal', function(e) {
	var button = $(e.relatedTarget);
	var href = button.data('href');
	var modal = $(this);
	modal.find('.btn-ok').attr('href', href);
});
</script>

<?php require_once('footer.php'); ?>
