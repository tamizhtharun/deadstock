<?php require_once('header.php'); ?>
<?php require_once('includes/pagination.php'); ?>

<?php
$statement = $pdo->prepare("SELECT seller_status FROM sellers WHERE seller_id = ?");
$statement->execute([$seller_id]);
$seller_status = $statement->fetchColumn();

// Get items per page from request or use default
$itemsPerPage = isset($_GET['per_page']) ? intval($_GET['per_page']) : 10;
$itemsPerPage = in_array($itemsPerPage, [10, 25, 50, 100]) ? $itemsPerPage : 10;

// Get search query and filters
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$approvalFilter = isset($_GET['approval']) ? $_GET['approval'] : '';
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Product Discount</h1>
    </div>
    <div class="content-header-right">
        <?php if ($seller_status == 1) { ?>
            <button class="add-new-btn" type="button" onclick="toggleDiscountForm()">
                <i class="fa fa-percent"></i> Overall Discount
            </button>

            <div id="overallDiscountForm" class="mt-3 p-3 border rounded bg-light" style="display:none; max-width:350px; position: absolute; right: 20px; z-index: 1000; background: white; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
                <form action="overall-discount.php" method="post">
                    <div class="form-group">
                        <label for="discountInput"><b>Enter Discount Percentage (%)</b></label>
                        <input type="number" name="discount" id="discountInput" class="form-control mt-2" min="0" max="100"
                            required placeholder="e.g. 20">
                    </div>
                    <button type="submit" class="btn btn-success btn-sm mt-2">Update</button>
                    <button type="button" class="btn btn-secondary btn-sm mt-2"
                        onclick="toggleDiscountForm()">Cancel</button>
                </form>
            </div>
        <?php } else { ?>
            <a href="profile-edit.php" class="add-new-btn" style="opacity: 0.6; cursor: not-allowed;">
                <i class="fa fa-percent"></i> Overall Discount
            </a>
        <?php } ?>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if ($seller_status == 0) { ?>
                <div class="alert no-details">
                    <i class="fa fa-exclamation-triangle"></i>
                    <h2>Profile Incomplete</h2>
                    <h5>Complete your profile to add products</h5>
                    <a href="profile-edit.php" class="btn btn-primary btn-sm">Complete Your Profile</a>
                </div>
            <?php } else {
                $seller_id = $_SESSION['seller_session']['seller_id'];
                
                // Build query with search and filters
                $whereConditions = ["t1.seller_id = :seller_id", "t1.p_is_approve = 1"];
                $params = [':seller_id' => $seller_id];
                
                if (!empty($searchQuery)) {
                    $whereConditions[] = "(t1.p_name LIKE :search OR t5.brand_name LIKE :search)";
                    $params[':search'] = '%' . $searchQuery . '%';
                }
                
                if ($approvalFilter !== '') {
                    $whereConditions[] = "t1.is_discount = :approval";
                    $params[':approval'] = $approvalFilter;
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
                            t1.p_discount_price,
                            t1.p_qty,
                            t1.p_featured_photo,
                            t1.p_is_featured,
                            t1.p_is_approve,
                            t1.is_discount,
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
                            <option value="">All Discount Status</option>
                            <option value="0" <?php echo $approvalFilter === '0' ? 'selected' : ''; ?>>Approved</option>
                            <option value="1" <?php echo $approvalFilter === '1' ? 'selected' : ''; ?>>Waiting for Approval</option>
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
                        <i class="fa fa-percent"></i>
                        <h3>No Products Found</h3>
                        <p>No approved products match your search criteria.</p>
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
                                <th>Discounted Price</th>
                                <th>Quantity</th>
                                <th>Approval Status</th>
                                <th>Current Discount</th>
                                <th>Update Discount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = $paginationInfo['start_item'];
                            foreach ($result as $row) {
                                $discount = 0;
                                if ($row['p_old_price'] > 0) {
                                    $discount = round((($row['p_old_price'] - $row['p_discount_price']) / $row['p_old_price']) * 100);
                                }
                            ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td>
                                    <img src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>"
                                         alt="<?php echo htmlspecialchars($row['p_name']); ?>" style="width:60px;">
                                </td>
                                <td><?php echo htmlspecialchars($row['brand_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['p_name']); ?></td>
                                <td class="price-cell">₹<?php echo number_format($row['p_old_price'], 2); ?></td>
                                <td class="price-cell">₹<?php echo number_format($row['p_discount_price'], 2); ?></td>
                                <td class="quantity-cell"><?php echo $row['p_qty']; ?></td>
                                <td>
                                    <?php if ($row['is_discount'] == 0): ?>
                                        <span class="status-badge status-approved">Approved</span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">Waiting for Approval</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong style="color: #667eea; font-size: 16px;"><?php echo $discount; ?>%</strong></td>
                                <td>
                                    <form action="update-discount.php" method="post" style="margin-top:5px; display:flex; gap:5px; align-items: center;">
                                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                        <input type="number" name="discount" value="<?php echo $discount; ?>" min="0"
                                            max="100" class="form-control" style="width:70px; height: 35px;">
                                        <button type="submit" class="action-btn action-btn-success" style="padding: 6px 12px;">
                                            <i class="fa fa-check"></i> Update
                                        </button>
                                    </form>
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
            <?php } ?>
        </div>
    </div>
</section>

<script>
function toggleDiscountForm() {
    var form = document.getElementById("overallDiscountForm");
    if (form.style.display === "none") {
        form.style.display = "block";
    } else {
        form.style.display = "none";
    }
}

// Search and filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const approvalFilter = document.getElementById('approvalFilter');
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
    approvalFilter.addEventListener('change', applyFilters);
    perPageSelect.addEventListener('change', applyFilters);
    
    function applyFilters() {
        const params = new URLSearchParams();
        
        if (searchInput.value.trim()) {
            params.set('search', searchInput.value.trim());
        }
        
        if (approvalFilter.value) {
            params.set('approval', approvalFilter.value);
        }
        
        if (perPageSelect.value) {
            params.set('per_page', perPageSelect.value);
        }
        
        // Redirect with new parameters
        window.location.href = 'discount.php' + (params.toString() ? '?' + params.toString() : '');
    }
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