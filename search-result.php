<link rel="stylesheet" href="css/search-result.css">
<?php
// session_start();
include 'header.php';
include 'db_connection.php';

// Get search query and filters from URL
$search_query = isset($_GET['search_text']) ? trim($_GET['search_text']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$price_filter = isset($_GET['price']) ? floatval($_GET['price']) : 0;
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'name_asc';

// Base query for products
$base_sql = "SELECT p.*, c.ecat_name, s.seller_cname,
             COALESCE(
                 (SELECT SUM(p_qty) FROM tbl_product WHERE id = p.id), 
                 0
             ) as stock_quantity
             FROM tbl_product p
             LEFT JOIN tbl_end_category c ON p.ecat_id = c.ecat_id
             LEFT JOIN sellers s ON p.seller_id = s.seller_id
             WHERE p.p_is_approve = '1'";

// Add search conditions
if (!empty($search_query)) {
    $base_sql .= " AND (p.p_name LIKE :search 
                  OR p.p_description LIKE :search 
                  OR c.ecat_name LIKE :search 
                  OR s.seller_cname LIKE :search)";
}

// Add category filter
if (!empty($category_filter)) {
    $base_sql .= " AND c.ecat_id = :category";
}

// Add price filter
if ($price_filter > 0) {
    $base_sql .= " AND p.p_current_price <= :price";
}

// Add sorting
switch ($sort_by) {
    case 'price_asc':
        $base_sql .= " ORDER BY p.p_current_price ASC";
        break;
    case 'price_desc':
        $base_sql .= " ORDER BY p.p_current_price DESC";
        break;
    case 'name_desc':
        $base_sql .= " ORDER BY p.p_name DESC";
        break;
    default:
        $base_sql .= " ORDER BY p.p_name ASC";
}

// Prepare and execute the query
$stmt = $pdo->prepare($base_sql);

if (!empty($search_query)) {
    $search_term = "%{$search_query}%";
    $stmt->bindParam(':search', $search_term, PDO::PARAM_STR);
}

if (!empty($category_filter)) {
    $stmt->bindParam(':category', $category_filter, PDO::PARAM_INT);
}

if ($price_filter > 0) {
    $stmt->bindParam(':price', $price_filter, PDO::PARAM_INT);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_results = count($products);

// Fetch categories for filter
$cat_stmt = $pdo->query("SELECT ecat_id, ecat_name FROM tbl_end_category ORDER BY ecat_name");
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get price range for filter
$price_stmt = $pdo->query("SELECT MIN(p_current_price) as min_price, MAX(p_current_price) as max_price FROM tbl_product WHERE p_is_approve = '1'");
$price_range = $price_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - <?php echo htmlspecialchars($search_query); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/search-result.css">
</head>
<body>
    <?php // include 'header.php'; ?>

    <div class="search-layout">
        <!-- Filters Sidebar -->
        <aside class="filters-sidebar">
            <div class="filter-section">
                <h3 class="filter-heading">Filters</h3>
                
                <div class="filter-group">
                    <label class="filter-label" for="sort">Sort by</label>
                    <select id="sort" class="filter-select">
                        <option value="name_asc" <?php echo $sort_by == 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="name_desc" <?php echo $sort_by == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                        <option value="price_asc" <?php echo $sort_by == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                        <option value="price_desc" <?php echo $sort_by == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="category">Category</label>
                    <select id="category" class="filter-select">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['ecat_id']); ?>"
                                    <?php echo $category_filter == $category['ecat_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['ecat_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="price-range">Maximum Price</label>
                    <input type="range" id="price-range" class="filter-select"
                           min="<?php echo floor($price_range['min_price']); ?>"
                           max="<?php echo ceil($price_range['max_price']); ?>"
                           value="<?php echo $price_filter ?: ceil($price_range['max_price']); ?>">
                    <span id="price-display">₹<?php echo number_format($price_filter ?: $price_range['max_price'], 2); ?></span>
                </div>

                <div class="filter-group">
                    <label class="filter-label" for="availability">Availability</label>
                    <select id="availability" class="filter-select">
                        <option value="all">All Items</option>
                        <option value="in-stock">In Stock</option>
                        <option value="out-of-stock">Out of Stock</option>
                    </select>
                </div>
            </div>
        </aside>

        <!-- Search Results Container -->
        <div class="search-results-container">
            <div class="search-header">
                <h1 class="search-title">Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h1>
                <p class="search-summary">Found <?php echo $total_results; ?> results</p>
            </div>

            <div class="search-grid">
                <?php if ($total_results > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" data-available="<?php echo $product['stock_quantity'] > 0 ? 'true' : 'false'; ?>">
                        <a href="product_landing.php?id=<?php echo $product['id']; ?>">
                            <div class="product-img">
                            <img src="assets/uploads/product-photos/<?php echo htmlspecialchars($product['p_featured_photo']); ?>"
                                 alt="<?php echo htmlspecialchars($product['p_name']); ?>"
                                 class="product-image">
                                 </div>
                            <div class="product-details">
                                <div class="product-name">
                                <h2 class="product-name"><?php echo htmlspecialchars($product['p_name']); ?></h2>
                                </div>
                                <div class="product-meta">
                                    <i class="fas fa-tag"></i>
                                    <span class="product-category"><?php echo htmlspecialchars($product['ecat_name']); ?></span>
                                </div>

                                <!-- <?php if (isset($product['seller_cname'])): ?>
                                    <div class="product-meta">
                                        <i class="fas fa-store"></i>
                                        <span class="product-seller"><?php echo htmlspecialchars($product['seller_cname']); ?></span>
                                    </div>
                                <?php endif; ?> -->

                                <div class="product-meta">
                                    <i class="fas fa-box"></i>
                                    <span class="product-stock">
                                        <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                    </span>
                                </div>
                                <div class="price-tag">
                                <p class="product-price">₹<?php echo number_format($product['p_current_price'], 2); ?></p>
                                <p class="product-old-price">₹<?php echo number_format($product['p_old_price'],2 ) ?></p>
                                </div>
                                <!-- <div class="product-actions">
                                    <a href="product_landing.php?id=<?php echo $product['id']; ?>"
                                       class="btn btn-primary">View Details</a>
                                    
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                        <button onclick="cart.add(<?php echo $product['id']; ?>)"
                                                class="btn btn-secondary">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    <?php endif; ?>
                                </div> -->
                            </div>
                        </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h2>No products found</h2>
                        <p>Try different keywords or browse our categories</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Login Required</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Please login to add items to your cart.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="login.php" class="btn btn-primary">Login</a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="js/search-result.js"></script>
    <script>
        // Update price display when range input changes
        const priceRange = document.getElementById('price-range');
        const priceDisplay = document.getElementById('price-display');
        
        if (priceRange && priceDisplay) {
            priceRange.addEventListener('input', function() {
                priceDisplay.textContent = '₹' + parseFloat(this.value).toLocaleString('en-IN', {
                    maximumFractionDigits: 2,
                    minimumFractionDigits: 2
                });
            });
        }
    </script>
</body>
</html>