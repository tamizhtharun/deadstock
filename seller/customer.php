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
		<h1>View Customers</h1>
	</div>
	<div class="content-header-right">
		<a href="customer-csv.php" class="export-btn">
			<i class="fa fa-file-csv"></i> Export as CSV
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
				$whereConditions[] = "(username LIKE :search OR email LIKE :search OR phone_number LIKE :search)";
				$params[':search'] = '%' . $searchQuery . '%';
			}
			
			$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
			
			// Count query
			$countQuery = "SELECT COUNT(*) FROM users t1 " . $whereClause;
			
			// Main query
			$query = "SELECT * FROM users t1 " . $whereClause . " ORDER BY registered_at DESC";
			
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
						<input type="text" id="searchInput" placeholder="Search by username, email, or phone..." value="<?php echo htmlspecialchars($searchQuery); ?>">
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
						<i class="fa fa-users"></i>
						<h3>No Customers Found</h3>
						<p>No customers match your search criteria.</p>
					</div>
				<?php else: ?>
					<table class="modern-table">
						<thead>
							<tr>
								<th>S.N</th>
								<th>Username</th>
								<th>Email Address</th>
								<th>Contact Number</th>
								<th>Joining Date</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$count = $paginationInfo['start_item'];
							foreach ($result as $row) {
							?>
							<tr>
								<td><?php echo $count; ?></td>
								<td><?php echo htmlspecialchars($row['username']); ?></td>
								<td><?php echo htmlspecialchars($row['email']); ?></td>
								<td><?php echo htmlspecialchars($row['phone_number']); ?></td>
								<td><?php echo date('M d, Y', strtotime($row['registered_at'])); ?></td>
							</tr>
							<?php
								$count++;
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
		}, 500);
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
		window.location.href = 'customer.php' + (params.toString() ? '?' + params.toString() : '');
	}
});
</script>

<?php require_once('footer.php'); ?>