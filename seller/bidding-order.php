<?php
require_once('../db_connection.php');
require_once('includes/pagination.php');
session_start();

// Check if export_csv button is clicked - must be before any output
if (isset($_POST['export_csv'])) {
    $seller_id = $_SESSION['seller_session']['seller_id'] ?? null;
    
    // Build query with filters
    $query = "SELECT
                o.id,
                o.order_id,
                o.price,
                o.quantity,
                o.order_status,
                o.created_at,
                p.p_name
            FROM
                tbl_orders o
            JOIN
                tbl_product p ON o.product_id = p.id
            WHERE
                o.seller_id = :seller_id
            AND
                o.order_type = 'bid'";
    
    $bindings = ['seller_id' => $seller_id];
    
    // Apply date filter
    if (!empty($_POST['from_date']) && !empty($_POST['to_date'])) {
        $query .= " AND DATE(o.created_at) BETWEEN :fromDate AND :toDate";
        $bindings['fromDate'] = $_POST['from_date'];
        $bindings['toDate'] = $_POST['to_date'];
    } elseif (!empty($_POST['from_date'])) {
        $query .= " AND DATE(o.created_at) >= :fromDate";
        $bindings['fromDate'] = $_POST['from_date'];
    } elseif (!empty($_POST['to_date'])) {
        $query .= " AND DATE(o.created_at) <= :toDate";
        $bindings['toDate'] = $_POST['to_date'];
    }
    
    // Apply status filter
    if (!empty($_POST['status_filter'])) {
        $query .= " AND o.order_status = :status";
        $bindings['status'] = $_POST['status_filter'];
    }
    
    $query .= " ORDER BY o.created_at DESC";
    
    try {
        $statement = $pdo->prepare($query);
        $statement->execute($bindings);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bidding-orders.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('#', 'Order ID', 'Product Name', 'Price', 'Quantity', 'Status', 'Order Date'));
        
        $i = 0;
        foreach ($result as $row) {
            $i++;
            $order_date = !empty($row['created_at']) && $row['created_at'] != '0000-00-00 00:00:00' ? '="' . date('d/m/Y', strtotime($row['created_at'])) . '"' : 'N/A';
            fputcsv($output, array($i, $row['order_id'], $row['p_name'], number_format($row['price'], 2), $row['quantity'], ucfirst($row['order_status']), $order_date));
        }
        
        fclose($output);
        exit();
    } catch (Exception $e) {
        error_log("CSV Export Error: " . $e->getMessage());
        header('Location: ' . $_SERVER['PHP_SELF'] . '?error=export_failed');
        exit();
    }
}

require_once('header.php');

$seller_id = $_SESSION['seller_session']['seller_id'];

// Get items per page from request or use default
$itemsPerPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
$itemsPerPage = in_array($itemsPerPage, [10, 25, 50, 100]) ? $itemsPerPage : 10;

// Get search query and filters
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';

// Build query with search and filters
$whereConditions = ["o.seller_id = :seller_id", "o.order_type = 'bid'"];
$params = [':seller_id' => $seller_id];

if (!empty($searchQuery)) {
    $whereConditions[] = "(p.p_name LIKE :search OR o.order_id LIKE :search)";
    $params[':search'] = '%' . $searchQuery . '%';
}

if ($statusFilter !== '') {
    $whereConditions[] = "o.order_status = :status";
    $params[':status'] = $statusFilter;
}

$whereClause = implode(' AND ', $whereConditions);

// Count query
$countQuery = "SELECT COUNT(*) 
               FROM tbl_orders o
               JOIN tbl_product p ON o.product_id = p.id
               WHERE " . $whereClause;

// Main query
$query = "SELECT 
            o.id,
            o.order_id,
            o.price,
            o.quantity,
            o.order_status,
            o.created_at,
            o.updated_at,
            p.p_name,
            p.p_featured_photo
          FROM tbl_orders o
          JOIN tbl_product p ON o.product_id = p.id
          WHERE " . $whereClause . "
          ORDER BY o.created_at DESC";

// Initialize pagination
$pagination = new ModernPagination($pdo, $itemsPerPage);
$paginatedData = $pagination->paginate($query, $countQuery, $params);
$result = $paginatedData['data'];
$paginationInfo = $paginatedData['pagination'];
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Bid-Based Orders</h1>
    </div>
    <div class="content-header-right">
        <form method="POST" action="" id="exportForm" style="display: inline;">
            <input type="hidden" name="status_filter" id="hiddenStatusFilter">
            <input type="hidden" name="from_date" id="hiddenFromDate">
            <input type="hidden" name="to_date" id="hiddenToDate">
            <button type="submit" name="export_csv" class="export-btn">
                <i class="fa fa-file-csv"></i> Export to CSV
            </button>
        </form>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="modern-table-container">
                <!-- Table Controls -->
                <div class="table-controls">
                    <div class="search-box">
                        <input type="text" id="searchInput" placeholder="Search by product or order ID..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <i class="fa fa-search"></i>
                    </div>
                    
                    <div class="filter-group">
                        <select id="statusFilter" class="filter-select">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $statusFilter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $statusFilter === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $statusFilter === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="canceled" <?php echo $statusFilter === 'canceled' ? 'selected' : ''; ?>>Canceled</option>
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
                        <i class="fa fa-gavel"></i>
                        <h3>No Orders Found</h3>
                        <p>No bid-based orders match your search criteria.</p>
                    </div>
                <?php else: ?>
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Photo</th>
                                <th>Order ID</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Status</th>
                                <th>Order Date</th>
                                <th>Processing Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = $paginationInfo['start_item'];
                            foreach ($result as $row) {
                                // Calculate processing time
                                $processingTime = 'N/A';
                                if ($row['order_status'] == 'delivered' && !empty($row['updated_at'])) {
                                    $created = new DateTime($row['created_at']);
                                    $updated = new DateTime($row['updated_at']);
                                    $diff = $created->diff($updated);
                                    $processingTime = $diff->days . ' days';
                                }
                            ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td>
                                    <img src="../assets/uploads/product-photos/<?php echo htmlspecialchars($row['p_featured_photo']); ?>" 
                                         alt="Product Photo" 
                                         style="width:60px;">
                                </td>
                                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['p_name']); ?></td>
                                <td class="price-cell">₹<?php echo number_format($row['price'], 2); ?></td>
                                <td class="quantity-cell"><?php echo $row['quantity']; ?></td>
                                <td>
                                    <?php
                                    $status = $row['order_status'];
                                    $badgeClass = 'status-pending';
                                    if ($status == 'delivered') $badgeClass = 'status-delivered';
                                    elseif ($status == 'processing') $badgeClass = 'status-processing';
                                    elseif ($status == 'shipped') $badgeClass = 'status-shipped';
                                    elseif ($status == 'canceled') $badgeClass = 'status-canceled';
                                    ?>
                                    <span class="status-badge <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                <td><?php echo $processingTime; ?></td>
                                <td>
                                    <?php if ($status != 'delivered' && $status != 'canceled'): ?>
                                        <form method="POST" action="update-order-status.php" style="display: inline;">
                                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                            <select name="new_status" class="filter-select" style="width: 110px; font-size: 11px; padding: 4px 8px;">
                                                <option value="">Update Status</option>
                                                <option value="processing">Processing</option>
                                                <option value="shipped">Shipped</option>
                                                <option value="delivered">Delivered</option>
                                                <option value="canceled">Cancel</option>
                                            </select>
                                            <button type="submit" class="action-btn action-btn-success" style="padding: 4px 10px; font-size: 11px;">
                                                <i class="fa fa-check"></i>
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 11px;">No actions</span>
                                    <?php endif; ?>
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

<script>
// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
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
    statusFilter.addEventListener('change', applyFilters);
    perPageSelect.addEventListener('change', applyFilters);
    
    function applyFilters() {
        const params = new URLSearchParams();
        
        if (searchInput.value.trim()) {
            params.set('search', searchInput.value.trim());
        }
        
        if (statusFilter.value) {
            params.set('status', statusFilter.value);
        }
        
        if (perPageSelect.value) {
            params.set('per_page', perPageSelect.value);
        }
        
        // Redirect with new parameters
        window.location.href = 'bidding-order.php' + (params.toString() ? '?' + params.toString() : '');
    }
    
    // Handle CSV export
    document.getElementById('exportForm').addEventListener('submit', function() {
        document.getElementById('hiddenStatusFilter').value = statusFilter.value;
    });
});
</script>

<?php require_once('footer.php'); ?>
