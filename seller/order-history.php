<?php
require_once('../db_connection.php');
require_once('includes/pagination.php');
session_start();

// Check if export_csv button is clicked - must be before any output
if (isset($_POST['export_csv'])) {
    $seller_id = $_SESSION['seller_session']['seller_id'] ?? null;
    
    // Build query with filters
    $query = "SELECT o.id, o.order_id, p.p_name,
               o.quantity, (o.price * o.quantity) AS total_price,
               o.order_status AS status, DATE(o.created_at) AS order_date,
               o.order_type
        FROM tbl_orders o
        JOIN tbl_product p ON o.product_id = p.id
        WHERE o.seller_id = :seller_id";
    
    $params = array(':seller_id' => $seller_id);
    
    // Apply status filter
    if (!empty($_POST['status_filter'])) {
        $query .= " AND o.order_status = :status";
        $params[':status'] = $_POST['status_filter'];
    }
    
    // Apply order type filter
    if (!empty($_POST['order_type_filter'])) {
        $query .= " AND o.order_type = :order_type";
        $params[':order_type'] = $_POST['order_type_filter'];
    }
    
    $query .= " ORDER BY o.created_at DESC";
    
    try {
        $statement = $pdo->prepare($query);
        $statement->execute($params);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="seller-order-history.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('#', 'Product', 'Order ID', 'Quantity', 'Price', 'Status', 'Date', 'Order Type'));
        
        $i = 0;
        foreach ($result as $row) {
            $i++;
            $order_date = !empty($row['order_date']) && $row['order_date'] != '0000-00-00' ? '="' . date('d/m/Y', strtotime($row['order_date'])) . '"' : 'N/A';
            fputcsv($output, array($i, $row['p_name'], $row['order_id'], $row['quantity'], number_format($row['total_price'], 2), $row['status'], $order_date, $row['order_type']));
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
$orderTypeFilter = isset($_GET['order_type']) ? $_GET['order_type'] : '';

// Build query with search and filters
$whereConditions = ["o.seller_id = :seller_id"];
$params = [':seller_id' => $seller_id];

if (!empty($searchQuery)) {
    $whereConditions[] = "(p.p_name LIKE :search OR o.order_id LIKE :search)";
    $params[':search'] = '%' . $searchQuery . '%';
}

if ($statusFilter !== '') {
    $whereConditions[] = "o.order_status = :status";
    $params[':status'] = $statusFilter;
}

if ($orderTypeFilter !== '') {
    $whereConditions[] = "o.order_type = :order_type";
    $params[':order_type'] = $orderTypeFilter;
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
            o.order_type,
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
        <h1>Order History</h1>
    </div>
    <div class="content-header-right">
        <form method="POST" action="" id="exportForm" style="display: inline;">
            <input type="hidden" name="status_filter" id="hiddenStatusFilter">
            <input type="hidden" name="order_type_filter" id="hiddenOrderTypeFilter">
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
                        <select id="orderTypeFilter" class="filter-select">
                            <option value="">All Order Types</option>
                            <option value="direct" <?php echo $orderTypeFilter === 'direct' ? 'selected' : ''; ?>>Direct</option>
                            <option value="bid" <?php echo $orderTypeFilter === 'bid' ? 'selected' : ''; ?>>Bid</option>
                        </select>
                        
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
                        <i class="fa fa-history"></i>
                        <h3>No Orders Found</h3>
                        <p>No orders match your search criteria.</p>
                    </div>
                <?php else: ?>
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Product Photo</th>
                                <th>Product</th>
                                <th>Order ID</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Order Type</th>
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
                                         alt="Product Photo" 
                                         style="width:60px;">
                                </td>
                                <td><?php echo htmlspecialchars($row['p_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                <td class="quantity-cell"><?php echo number_format($row['quantity']); ?></td>
                                <td class="price-cell">₹<?php echo number_format($row['price'] * $row['quantity'], 2); ?></td>
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
                                <td><?php echo (!empty($row['created_at']) && $row['created_at'] != '0000-00-00 00:00:00') ? date('M d, Y', strtotime($row['created_at'])) : 'N/A'; ?></td>
                                <td>
                                    <?php if ($row['order_type'] == 'bid'): ?>
                                        <span class="status-badge status-processing">Bid</span>
                                    <?php else: ?>
                                        <span class="status-badge status-approved">Direct</span>
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
    const orderTypeFilter = document.getElementById('orderTypeFilter');
    const statusFilter = document.getElementById('statusFilter');
    const perPageSelect = document.getElementById('perPageSelect');
    
    let searchTimeout;
    
    // Search with debounce
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            applyFilters();
        }, 800);
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
        window.location.href = 'order-history.php' + (params.toString() ? '?' + params.toString() : '');
    }
    
    // Handle CSV export
    document.getElementById('exportForm').addEventListener('submit', function() {
        document.getElementById('hiddenStatusFilter').value = statusFilter.value;
        document.getElementById('hiddenOrderTypeFilter').value = orderTypeFilter.value;
    });
});
</script>

<?php require_once('footer.php'); ?>