<?php require_once('header.php'); ?>
<?php require_once('includes/pagination.php'); ?>

<?php
$statement = $pdo->prepare("SELECT seller_status FROM sellers WHERE seller_id = ?");
$statement->execute([$seller_id]);
$seller_status = $statement->fetchColumn();

// Get items per page from request or use default
$itemsPerPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
$itemsPerPage = in_array($itemsPerPage, [10, 25, 50, 100]) ? $itemsPerPage : 10;

// Get search query
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get filter values
$approvalFilter = isset($_GET['approval']) ? $_GET['approval'] : '';
$featuredFilter = isset($_GET['featured']) ? $_GET['featured'] : '';
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>View Products</h1>
	</div>
	<div class="content-header-right">
		<?php if($seller_status == 1){?>
			<a href="product-add.php" class="add-new-btn">
				<i class="fa fa-plus"></i> Add Product
			</a>
			<a href="product-bulk-upload.php" class="add-new-btn" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
				<i class="fa fa-upload"></i> Bulk Upload
			</a>
		<?php }else{ ?>
			<a href="profile-edit.php" class="add-new-btn" style="opacity: 0.6; cursor: not-allowed;">
				<i class="fa fa-plus"></i> Add Product
			</a>
			<a href="product-bulk-upload.php" class="add-new-btn" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); opacity: 0.6; cursor: not-allowed;">
				<i class="fa fa-upload"></i> Bulk Upload
			</a>
		<?php } ?>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<?php if($seller_status==0){ ?>
				<div class="alert no-details">
					<i class="fa fa-exclamation-triangle"></i>
					<h2>Profile Incomplete</h2>
					<h5>Complete your profile to add products</h5>
					<a href="profile-edit.php" class="btn btn-primary btn-sm">Complete Your Profile</a>
				</div>
			<?php } else { 
				// Build query with filters
				$whereConditions = ["t1.seller_id = :seller_id"];
				$params = [':seller_id' => $seller_id];
				
				if (!empty($searchQuery)) {
					$whereConditions[] = "(t1.p_name LIKE :search OR t5.brand_name LIKE :search)";
					$params[':search'] = '%' . $searchQuery . '%';
				}
				
				if ($approvalFilter !== '') {
					$whereConditions[] = "t1.p_is_approve = :approval";
					$params[':approval'] = $approvalFilter;
				}
				
				if ($featuredFilter !== '') {
					$whereConditions[] = "t1.p_is_featured = :featured";
					$params[':featured'] = $featuredFilter;
				}
				
				$whereClause = implode(' AND ', $whereConditions);
				
				// Count query
				$countQuery = "SELECT COUNT(*) 
							   FROM tbl_product t1
							   LEFT JOIN tbl_brands t5 ON t1.product_brand=t5.brand_id
							   WHERE " . $whereClause;
				
				// Main query
				$query = "SELECT
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
						LEFT JOIN tbl_end_category t2 ON t1.ecat_id = t2.ecat_id
						LEFT JOIN tbl_mid_category t3 ON t1.mcat_id = t3.mcat_id
						LEFT JOIN tbl_top_category t4 ON t1.tcat_id = t4.tcat_id
						LEFT JOIN tbl_brands t5 ON t1.product_brand=t5.brand_id
						WHERE " . $whereClause . "
						ORDER BY t1.id DESC";
				
				// Initialize pagination
				$pagination = new ModernPagination($pdo, $itemsPerPage);
				$paginatedData = $pagination->paginate($query, $countQuery, $params);
				$result = $paginatedData['data'];
				$paginationInfo = $paginatedData['pagination'];
			?>
			
			<div class="modern-table-container">
				<!-- Table Controls -->
				<div class="table-controls">
					<div class="search-box">
						<input type="text" id="searchInput" placeholder="Search products or brands..." value="<?php echo htmlspecialchars($searchQuery); ?>">
						<i class="fa fa-search"></i>
					</div>
					
					<div class="filter-group">
						<select id="approvalFilter" class="filter-select">
							<option value="">All Status</option>
							<option value="1" <?php echo $approvalFilter === '1' ? 'selected' : ''; ?>>Approved</option>
							<option value="0" <?php echo $approvalFilter === '0' ? 'selected' : ''; ?>>Rejected</option>
						</select>
						
						<select id="featuredFilter" class="filter-select">
							<option value="">All Products</option>
							<option value="1" <?php echo $featuredFilter === '1' ? 'selected' : ''; ?>>Featured</option>
							<option value="0" <?php echo $featuredFilter === '0' ? 'selected' : ''; ?>>Not Featured</option>
						</select>
						
						<div class="entries-selector">
							<label>Show</label>
							<select id="perPageSelect">
								<option value="10" <?php echo $itemsPerPage == 10 ? 'selected' : ''; ?>>10</option>
								<option value="25" <?php echo $itemsPerPage == 25 ? 'selected' : ''; ?>>25</option>
								<option value="50" <?php echo $itemsPerPage == 50 ? 'selected' : ''; ?>>50</option>
								<option value="100" <?php echo $itemsPerPage == 100 ? 'selected' : ''; ?>>100</option>
							</select>
							<label>entries</label>
						</div>
					</div>
				</div>
				
				<?php if(empty($result)): ?>
					<div class="empty-state">
						<i class="fa fa-box-open"></i>
						<h3>No Products Found</h3>
						<p>No products match your search criteria. Try adjusting your filters.</p>
					</div>
				<?php else: ?>
					<table class="modern-table">
						<thead>
							<tr>
								<th>#</th>
								<th>Photo</th>
								<th>Brand</th>
								<th>Product Name</th>
								<th>Old Price</th>
								<th>Current Price</th>
								<th>Quantity</th>
								<th>Featured</th>
								<th>Status</th>
								<th>Category</th>
								<th>Catalogue</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = $paginationInfo['start_item'] - 1;
							foreach ($result as $row) {
								$i++;
							?>
							<tr>
								<td><?php echo $i; ?></td>
								<td>
									<img src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>" 
										 alt="<?php echo htmlspecialchars($row['p_name']); ?>" 
										 style="width:80px;">
								</td>
								<td><?php echo htmlspecialchars($row['brand_name']); ?></td>
								<td><?php echo htmlspecialchars($row['p_name']); ?></td>
								<td class="price-cell">₹<?php echo number_format($row['p_old_price'], 2); ?></td>
								<td class="price-cell">₹<?php echo number_format($row['p_current_price'], 2); ?></td>
								<td class="quantity-cell"><?php echo $row['p_qty']; ?></td>
								<td>
									<?php if($row['p_is_featured'] == 1): ?>
										<span class="status-badge status-approved">Yes</span>
									<?php else: ?>
										<span class="status-badge status-canceled">No</span>
									<?php endif; ?>
								</td>
								<td>
									<?php if($row['p_is_approve'] == 1): ?>
										<span class="status-badge status-approved">Approved</span>
									<?php else: ?>
										<span class="status-badge status-canceled">Rejected</span>
									<?php endif; ?>
								</td>
								<td>
									<small>
										<?php echo $row['tcat_name']; ?><br>
										<?php echo $row['mcat_name']; ?><br>
										<?php echo $row['ecat_name']; ?>
									</small>
								</td>
								<td>
									<?php if(!empty($row['product_catalogue'])): ?>
										<a href="../assets/uploads/product-catalogues/<?php echo $row['product_catalogue']?>" 
										   target="_blank" class="action-btn action-btn-success" style="font-size: 11px;">
											<i class="fa fa-file-pdf"></i> View
										</a>
									<?php else: ?>
										<span style="color: #999;">N/A</span>
									<?php endif; ?>
								</td>
								<td>
									<a href="product-edit.php?id=<?php echo $row['id']; ?>" class="action-btn action-btn-primary">
										<i class="fa fa-edit"></i> Edit
									</a>
									<a href="#" class="action-btn action-btn-danger" 
									   data-href="product-delete.php?id=<?php echo $row['id']; ?>" 
									   data-toggle="modal" data-target="#confirm-delete">
										<i class="fa fa-trash"></i> Delete
									</a>
								</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
					
					<!-- Pagination -->
					<?php echo $pagination->renderPagination(); ?>
				<?php endif; ?>
			</div>
			<?php } ?>
		</div>
	</div>
</section>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="confirmDeleteLabel"></h4>
            </div>
            <div class="modal-body">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fa fa-exclamation-circle" style="font-size: 72px; color: #f0ad4e;"></i>
                </div>
                <h4 style="text-align: center;">Are you sure?</h4>
                <p style="text-align: center;">This product will be permanently deleted and cannot be recovered.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a href="#" class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>

<script>
// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
	const searchInput = document.getElementById('searchInput');
	const approvalFilter = document.getElementById('approvalFilter');
	const featuredFilter = document.getElementById('featuredFilter');
	const perPageSelect = document.getElementById('perPageSelect');

	let searchTimeout;

	// Search with debounce
	searchInput.addEventListener('input', function() {
		clearTimeout(searchTimeout);
		searchTimeout = setTimeout(function() {
			applyFilters();
		}, 500);
	});

	// Filter changes
	approvalFilter.addEventListener('change', applyFilters);
	featuredFilter.addEventListener('change', applyFilters);
	perPageSelect.addEventListener('change', applyFilters);

	function applyFilters() {
		const params = new URLSearchParams();

		if (searchInput.value.trim()) {
			params.set('search', searchInput.value.trim());
		}

		if (approvalFilter.value) {
			params.set('approval', approvalFilter.value);
		}

		if (featuredFilter.value) {
			params.set('featured', featuredFilter.value);
		}

		if (perPageSelect.value) {
			params.set('per_page', perPageSelect.value);
		}

		// Redirect with new parameters
		window.location.href = 'product.php' + (params.toString() ? '?' + params.toString() : '');
	}

	// Delete confirmation modal functionality
	$('#confirm-delete').on('show.bs.modal', function(e) {
		var button = $(e.relatedTarget); // Button that triggered the modal
		var href = button.data('href'); // Extract info from data-* attributes
		var modal = $(this);
		modal.find('.btn-ok').attr('href', href);
	});
});
</script>

<style>
.alert {
	padding: 10px;
	width: 100%;
	text-align: center;
}
.alert i {
	font-size: 70px;
}
</style>

<?php require_once('footer.php'); ?>
