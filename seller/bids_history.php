<?php
require_once('../db_connection.php');
require_once('includes/pagination.php');
session_start();

// Check if export_csv button is clicked - must be before any output
if (isset($_POST['export_csv'])) {
    $seller_id = $_SESSION['seller_session']['seller_id'] ?? null;
    
    // Build query with filters
    $query = "
        SELECT
            p.p_name,
            b.bid_quantity,
            b.bid_price,
            b.bid_status,
            b.bid_time
        FROM
            bidding b
        JOIN
            tbl_product p ON b.product_id = p.id
        WHERE
            p.seller_id = :seller_id
            AND (b.bid_status = 2 OR b.bid_status = 3)
    ";
    
    $bindings = ['seller_id' => $seller_id];
    
    // Apply date filter
    if (!empty($_POST['from_date']) && !empty($_POST['to_date'])) {
        $query .= " AND DATE(b.bid_time) BETWEEN :fromDate AND :toDate";
        $bindings['fromDate'] = $_POST['from_date'];
        $bindings['toDate'] = $_POST['to_date'];
    } elseif (!empty($_POST['from_date'])) {
        $query .= " AND DATE(b.bid_time) >= :fromDate";
        $bindings['fromDate'] = $_POST['from_date'];
    } elseif (!empty($_POST['to_date'])) {
        $query .= " AND DATE(b.bid_time) <= :toDate";
        $bindings['toDate'] = $_POST['to_date'];
    }
    
    // Apply status filter
    if (!empty($_POST['status_filter'])) {
        if ($_POST['status_filter'] == 'approved') {
            $query .= " AND b.bid_status = 2";
        } elseif ($_POST['status_filter'] == 'refunded') {
            $query .= " AND b.bid_status = 3";
        }
    }
    
    $query .= " ORDER BY b.bid_time DESC";
    
    try {
        $statement = $pdo->prepare($query);
        $statement->execute($bindings);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bids-history.csv"');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, array('#', 'Product Name', 'Quantity', 'Price', 'Status', 'Bid Date'));
        
        $i = 0;
        foreach ($result as $row) {
            $i++;
            $bid_date = !empty($row['bid_time']) && $row['bid_time'] != '0000-00-00 00:00:00' ? '="' . date('d/m/Y', strtotime($row['bid_time'])) . '"' : 'N/A';
            $status = $row['bid_status'] == 2 ? 'Approved' : 'Refunded';
            fputcsv($output, array($i, $row['p_name'], $row['bid_quantity'], number_format($row['bid_price'], 2), $status, $bid_date));
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
$whereConditions = ["p.seller_id = :seller_id", "(b.bid_status = 2 OR b.bid_status = 3)"];
$params = [':seller_id' => $seller_id];

if (!empty($searchQuery)) {
    $whereConditions[] = "p.p_name LIKE :search";
    $params[':search'] = '%' . $searchQuery . '%';
}

if ($statusFilter !== '') {
    if ($statusFilter == 'approved') {
        $whereConditions[] = "b.bid_status = 2";
    } elseif ($statusFilter == 'refunded') {
        $whereConditions[] = "b.bid_status = 3";
    }
}

$whereClause = implode(' AND ', $whereConditions);

// Count query
$countQuery = "SELECT COUNT(*) 
               FROM bidding b
               JOIN tbl_product p ON b.product_id = p.id
               WHERE " . $whereClause;

// Main query
$query = "SELECT 
            p.p_featured_photo,
            p.p_name,
            b.bid_quantity,
            b.bid_price,
            b.bid_status,
            b.bid_time
          FROM bidding b
          JOIN tbl_product p ON b.product_id = p.id
          WHERE " . $whereClause . "
          ORDER BY b.bid_time DESC";

// Initialize pagination
$pagination = new ModernPagination($pdo, $itemsPerPage);
$paginatedData = $pagination->paginate($query, $countQuery, $params);
$result = $paginatedData['data'];
$paginationInfo = $paginatedData['pagination'];
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Bidding History</h1>
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
                        <input type="text" id="searchInput" placeholder="Search by product name..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                        <i class="fa fa-search"></i>
                    </div>
                    
                    <div class="filter-group">
                        <select id="statusFilter" class="filter-select">
                            <option value="">All Status</option>
                            <option value="approved" <?php echo $statusFilter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="refunded" <?php echo $statusFilter === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
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
                        <h3>No Bids Found</h3>
                        <p>No bids match your search criteria.</p>
                    </div>
                <?php else: ?>
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product Photo</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Bid Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = $paginationInfo['start_item'];
                            foreach ($result as $bid) {
                            ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td>
                                    <img src="../assets/uploads/product-photos/<?php echo $bid['p_featured_photo']; ?>" 
                                         alt="Product Photo" 
                                         style="width:60px;">
                                </td>
                                <td><?php echo htmlspecialchars($bid['p_name']); ?></td>
                                <td class="quantity-cell"><?php echo $bid['bid_quantity']; ?></td>
                                <td class="price-cell">₹<?php echo number_format($bid['bid_price'], 2); ?></td>
                                <td>
                                    <?php if($bid['bid_status'] == 2): ?>
                                        <span class="status-badge status-approved">Approved</span>
                                    <?php else: ?>
                                        <span class="status-badge status-canceled">Refunded</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo (!empty($bid['bid_time']) && $bid['bid_time'] != '0000-00-00 00:00:00') ? date('M d, Y', strtotime($bid['bid_time'])) : 'N/A'; ?></td>
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
        }, 800);
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
        window.location.href = 'bids_history.php' + (params.toString() ? '?' + params.toString() : '');
    }
    
    // Handle CSV export
    document.getElementById('exportForm').addEventListener('submit', function() {
        document.getElementById('hiddenStatusFilter').value = statusFilter.value;
    });
});
</script>

<?php require_once('footer.php'); ?>
