<?php require_once('header.php'); ?>
<?php require_once('includes/pagination.php'); ?>

<?php
// Get items per page from request or use default
$itemsPerPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
$itemsPerPage = in_array($itemsPerPage, [10, 25, 50, 100]) ? $itemsPerPage : 10;

// Get search query and filters
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$orderTypeFilter = isset($_GET['order_type']) ? $_GET['order_type'] : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Revenue</h1>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<?php
			$seller_id = $_SESSION['seller_session']['seller_id'];
			
			// Build query with search and filters
			$whereConditions = ["t1.seller_id = :seller_id", "t2.order_status != 'canceled'"];
			$params = [':seller_id' => $seller_id];
			
			if (!empty($searchQuery)) {
				$whereConditions[] = "(t1.p_name LIKE :search OR t3.brand_name LIKE :search)";
				$params[':search'] = '%' . $searchQuery . '%';
			}
			
			if ($orderTypeFilter !== '') {
				$whereConditions[] = "t2.order_type = :order_type";
				$params[':order_type'] = $orderTypeFilter;
			}
			
			if ($statusFilter !== '') {
				$whereConditions[] = "t2.order_status = :status";
				$params[':status'] = $statusFilter;
			}
			
			$whereClause = implode(' AND ', $whereConditions);
			
			// Count query
			$countQuery = "SELECT COUNT(*) 
						   FROM tbl_orders t2
						   JOIN tbl_product t1 ON t2.product_id = t1.id
						   JOIN tbl_brands t3 ON t1.product_brand = t3.brand_id
						   WHERE " . $whereClause;
			
			// Main query
			$query = "SELECT 
						t1.p_featured_photo,
						t1.product_brand,
						t1.p_name,
						t2.quantity,
						t2.order_status,
						t2.price,
						t2.updated_at,
						t2.order_type,
						t3.brand_name
					  FROM tbl_orders t2
					  JOIN tbl_product t1 ON t2.product_id = t1.id
					  JOIN tbl_brands t3 ON t1.product_brand = t3.brand_id
					  WHERE " . $whereClause . "
					  ORDER BY t2.updated_at DESC";
			
			// Initialize pagination
			$pagination = new ModernPagination($pdo, $itemsPerPage);
			$paginatedData = $pagination->paginate($query, $countQuery, $params);
			$result = $paginatedData['data'];
			$paginationInfo = $paginatedData['pagination'];
			
			// Calculate total revenue
			$totalRevenue = 0;
			foreach ($result as $row) {
				if ($row['order_status'] == 'delivered') {
					$totalRevenue += $row['price'] * $row['quantity'];
				}
			}
			?>
			
			<div class="modern-table-container">
				<!-- Table Controls -->
				<div class="table-controls">
					<div class="search-box">
						<input type="text" id="searchInput" placeholder="Search by product or brand..." value="<?php echo htmlspecialchars($searchQuery); ?>">
						<i class="fa fa-search"></i>
					</div>
					
					<div class="filter-group">
						<select id="orderTypeFilter" class="filter-select">
							<option value="">All Order Types</option>
							<option value="bid" <?php echo $orderTypeFilter === 'bid' ? 'selected' : ''; ?>>Bid Orders</option>
							<option value="direct" <?php echo $orderTypeFilter === 'direct' ? 'selected' : ''; ?>>Direct Orders</option>
						</select>
						
						<select id="statusFilter" class="filter-select">
							<option value="">All Status</option>
							<option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
							<option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
							<option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Processing</option>
							<option value="shipped" <?php echo $statusFilter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
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
				
				<!-- Revenue Summary -->
				<div style="padding: 20px; background: #f8f9fa; border: 2px solid #e2e8f0; border-radius: 6px; margin: 15px 20px;">
					<div style="display: flex; justify-content: space-between; align-items: center;">
						<div>
							<p style="margin: 0; font-size: 13px; color: #718096; font-weight: 500;">Total Revenue (Delivered Orders)</p>
							<h2 style="margin: 5px 0 0 0; font-size: 28px; color: #2d3748; font-weight: 700;">₹<?php echo number_format($totalRevenue, 2); ?></h2>
						</div>
						<div style="background: #48bb78; color: white; padding: 12px 20px; border-radius: 6px;">
							<i class="fa fa-chart-line" style="font-size: 24px;"></i>
						</div>
					</div>
				</div>
				
				<?php if(empty($result)): ?>
					<div class="empty-state">
						<i class="fa fa-chart-line"></i>
						<h3>No Revenue Data Found</h3>
						<p>No orders match your search criteria.</p>
					</div>
				<?php else: ?>
					<table class="modern-table">
						<thead>
							<tr>
								<th>#</th>
								<th>Photo</th>
								<th>Product Brand</th>
								<th>Product Name</th>
								<th>Quantity</th>
								<th>Product Price</th>
								<th>Order Time</th>
								<th>Order Type</th>
								<th>Final Price</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i = $paginationInfo['start_item'];
							foreach ($result as $row) {
							?>
							<tr>
								<td><?php echo $i; ?></td>
								<td>
									<img src="../assets/uploads/product-photos/<?php echo htmlspecialchars($row['p_featured_photo']); ?>" 
										 alt="<?php echo htmlspecialchars($row['p_name']); ?>" 
										 style="width:60px;">
								</td>
								<td><?php echo htmlspecialchars($row['brand_name']); ?></td>
								<td><?php echo htmlspecialchars($row['p_name']); ?></td>
								<td class="quantity-cell"><?php echo htmlspecialchars($row['quantity']); ?></td>
								<td class="price-cell">₹<?php echo number_format($row['price'], 2); ?></td>
								<td><?php echo date('M d, Y H:i', strtotime($row['updated_at'])); ?></td>
								<td>
									<?php if($row['order_type']== 'bid'): ?>
										<span class="status-badge status-processing">Bidded Order</span>
									<?php else: ?>
										<span class="status-badge status-approved">Direct Order</span>
									<?php endif; ?>
									<br><br>
									<?php if($row['order_status'] == 'delivered'): ?>
										<span class="status-badge status-delivered">Delivered</span>
									<?php else: ?>
										<span class="status-badge status-pending">Not Delivered</span>
									<?php endif; ?>
								</td>
								<td class="price-cell"><strong>₹<?php echo number_format($row['price'] * $row['quantity'], 2); ?></strong></td>
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

<script>
// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
	const searchInput = document.getElementById('searchInput');
	const orderTypeFilter = document.getElementById('orderTypeFilter');
	const statusFilter = document.getElementById('statusFilter');
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
	orderTypeFilter.addEventListener('change', applyFilters);
	statusFilter.addEventListener('change', applyFilters);
	perPageSelect.addEventListener('change', applyFilters);
	
	function applyFilters() {
		const params = new URLSearchParams();
		
		if (searchInput.value.trim()) {
			params.set('search', searchInput.value.trim());
		}
		
		if (orderTypeFilter.value) {
			params.set('order_type', orderTypeFilter.value);
		}
		
		if (statusFilter.value) {
			params.set('status', statusFilter.value);
		}
		
		if (perPageSelect.value) {
			params.set('per_page', perPageSelect.value);
		}
		
		// Redirect with new parameters
		window.location.href = 'revenue.php' + (params.toString() ? '?' + params.toString() : '');
	}
});
</script>

<?php require_once('footer.php'); ?>