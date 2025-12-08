<?php require_once('header.php'); ?>
<?php require_once('includes/pagination.php'); ?>

<?php
// Get items per page from request or use default
$itemsPerPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
$itemsPerPage = in_array($itemsPerPage, [10, 25, 50, 100]) ? $itemsPerPage : 10;

// Get search query
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>View FAQs</h1>
	</div>
	<div class="content-header-right">
		<a href="faq-add.php" class="add-new-btn">
			<i class="fa fa-plus"></i> Add FAQ
		</a>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<?php
			// Build query with search
			$whereConditions = [];
			$params = [];
			
			if (!empty($searchQuery)) {
				$whereConditions[] = "(faq_title LIKE :search OR faq_content LIKE :search)";
				$params[':search'] = '%' . $searchQuery . '%';
			}
			
			$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
			
			// Count query
			$countQuery = "SELECT COUNT(*) FROM tbl_faq " . $whereClause;
			
			// Main query
			$query = "SELECT * FROM tbl_faq " . $whereClause . " ORDER BY faq_id DESC";
			
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
						<input type="text" id="searchInput" placeholder="Search FAQs..." value="<?php echo htmlspecialchars($searchQuery); ?>">
						<i class="fa fa-search"></i>
					</div>
					
					<div class="filter-group">
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
						<i class="fa fa-question-circle"></i>
						<h3>No FAQs Found</h3>
						<p>No FAQs match your search criteria.</p>
					</div>
				<?php else: ?>
					<table class="modern-table">
						<thead>
							<tr>
								<th width="50">#</th>
								<th>Title</th>
								<th width="150">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = $paginationInfo['start_item'];
							foreach ($result as $row) {
							?>
							<tr>
								<td><?php echo $i; ?></td>
								<td><?php echo htmlspecialchars($row['faq_title']); ?></td>
								<td>
									<a href="faq-edit.php?id=<?php echo $row['faq_id']; ?>" class="action-btn action-btn-primary">
										<i class="fa fa-edit"></i> Edit
									</a>
									<a href="#" class="action-btn action-btn-danger" 
									   data-href="faq-delete.php?id=<?php echo $row['faq_id']; ?>" 
									   data-toggle="modal" data-target="#confirm-delete">
										<i class="fa fa-trash"></i> Delete
									</a>
								</td>
							</tr>
							<?php
								$i++;
							}
							?>
						</tbody>
					</table>
					
					<!-- Pagination -->
					<?php echo $pagination->renderPagination(); ?>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<!-- Delete Confirmation Modal -->
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

<script>
// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
	const searchInput = document.getElementById('searchInput');
	const perPageSelect = document.getElementById('perPageSelect');
	
	let searchTimeout;
	
	// Search with debounce
	searchInput.addEventListener('input', function() {
		clearTimeout(searchTimeout);
		searchTimeout = setTimeout(function() {
			applyFilters();
		}, 800);
	});
	
	// Per page change
	perPageSelect.addEventListener('change', applyFilters);
	
	function applyFilters() {
		const params = new URLSearchParams();
		
		if (searchInput.value.trim()) {
			params.set('search', searchInput.value.trim());
		}
		
		if (perPageSelect.value) {
			params.set('per_page', perPageSelect.value);
		}
		
		// Redirect with new parameters
		window.location.href = 'faq.php' + (params.toString() ? '?' + params.toString() : '');
	}
});
</script>

<?php require_once('footer.php'); ?>