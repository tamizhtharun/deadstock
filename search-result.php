<?php
include 'header.php';
include 'db_connection.php';

// Get search query and filters from URL
$search_query = isset($_GET['search_text']) ? trim($_GET['search_text']) : '';
$category_type = isset($_GET['type']) ? trim($_GET['type']) : '';
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$selected_category = isset($_GET['category']) ? trim($_GET['category']) : '';
$price_filter = isset($_GET['price']) ? floatval($_GET['price']) : 0;
$sort_by = isset($_GET['sort']) ? trim($_GET['sort']) : 'name_asc';

// Base query for products with mid-category filtering
$base_sql = "SELECT p.*, m.mcat_name, s.seller_cname,
             COALESCE(
                 (SELECT SUM(p_qty) FROM tbl_product WHERE id = p.id), 
                 0
             ) as stock_quantity
             FROM tbl_product p
             LEFT JOIN tbl_mid_category m ON p.mcat_id = m.mcat_id
             LEFT JOIN tbl_top_category t ON p.tcat_id = t.tcat_id
             LEFT JOIN sellers s ON p.seller_id = s.seller_id
             WHERE p.p_is_approve = '1'";

// Dynamic category filtering
if ($category_type && $category_id) {
    switch ($category_type) {
        case 'top-category':
            $base_sql .= " AND t.tcat_id = :category_id";
            break;
        case 'mid-category':
            $base_sql .= " AND m.mcat_id = :category_id";
            break;
    }
}

// Add search conditions
if (!empty($search_query)) {
    $base_sql .= " AND (p.p_name LIKE :search 
                  OR p.p_description LIKE :search 
                  OR m.mcat_name LIKE :search 
                  OR s.seller_cname LIKE :search)";
}

// Add additional category filter
if (!empty($selected_category)) {
    $base_sql .= " AND m.mcat_id = :selected_category";
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

// Bind category ID if present
if ($category_type && $category_id) {
    $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
}

if (!empty($search_query)) {
    $search_term = "%{$search_query}%";
    $stmt->bindParam(':search', $search_term, PDO::PARAM_STR);
}

if (!empty($selected_category)) {
    $stmt->bindParam(':selected_category', $selected_category, PDO::PARAM_INT);
}

if ($price_filter > 0) {
    $stmt->bindParam(':price', $price_filter, PDO::PARAM_INT);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_results = count($products);

// Fetch categories for filter based on current category context
$cat_stmt = null;
if ($category_type && $category_id) {
    switch ($category_type) {
        case 'top-category':
            $cat_stmt = $pdo->prepare("
                SELECT DISTINCT m.mcat_id, m.mcat_name 
                FROM tbl_mid_category m
                WHERE m.tcat_id = ?
                ORDER BY m.mcat_name
            ");
            $cat_stmt->execute([$category_id]);
            break;
    }
} else {
    // Default: fetch all mid categories
    $cat_stmt = $pdo->query("SELECT mcat_id, mcat_name FROM tbl_mid_category ORDER BY mcat_name");
}

$categories = $cat_stmt ? $cat_stmt->fetchAll(PDO::FETCH_ASSOC) : [];

// Get price range for filter
$price_stmt = $pdo->query("SELECT MIN(p_current_price) as min_price, MAX(p_current_price) as max_price FROM tbl_product WHERE p_is_approve = '1'");
$price_range = $price_stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Product Results</title>
    <link rel="stylesheet" href="css/search-result.css">
</head>
<body>
    <div class="search-layout">
        <!-- Filters Sidebar -->
        <aside class="filters-sidebar">
            <div class="filter-section">
                <h3 class="filter-heading">Filters</h3>
                
                <form id="filter-form" method="GET">
                    <!-- Hidden fields to preserve original category context -->
                    <input type="hidden" name="type" value="<?php echo htmlspecialchars($category_type); ?>">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($category_id); ?>">
                    <input type="hidden" name="search_text" value="<?php echo htmlspecialchars($search_query); ?>">

                    <div class="filter-group">
                        <label class="filter-label" for="sort">Sort by</label>
                        <select id="sort" name="sort" class="filter-select">
                            <option value="name_asc" <?php echo $sort_by == 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="name_desc" <?php echo $sort_by == 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                            <option value="price_asc" <?php echo $sort_by == 'price_asc' ? 'selected' : ''; ?>>Price (Low to High)</option>
                            <option value="price_desc" <?php echo $sort_by == 'price_desc' ? 'selected' : ''; ?>>Price (High to Low)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="category">Category</label>
                        <select id="category" name="category" class="filter-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo htmlspecialchars($category['mcat_id']); ?>"
                                        <?php echo $selected_category == $category['mcat_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['mcat_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="price-range">Maximum Price</label>
                        <input type="range" id="price-range" name="price" class="filter-select"
                               min="<?php echo floor($price_range['min_price']); ?>"
                               max="<?php echo ceil($price_range['max_price']); ?>"
                               value="<?php echo $price_filter ?: ceil($price_range['max_price']); ?>">
                        <span id="price-display">₹<?php echo number_format($price_filter ?: $price_range['max_price'], 2); ?></span>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Apply Filters</button>
                </form>
            </div>
        </aside>

        <!-- Search Results Container -->
        <div class="search-results-container">
            <div class="search-header">
                <h1 class="search-title">
                    <?php 
                    if ($category_type && $category_id) {
                        echo "Products based on category";
                    } elseif (!empty($search_query)) {
                        echo "Search Results for \"" . htmlspecialchars($search_query) . "\"";
                    } else {
                        echo "All Products";
                    }
                    ?>
                </h1>
                <p class="search-summary">Found <?php echo $total_results; ?> results</p>
            </div>
            
            <?php if ($total_results > 0): ?>
            <div class="search-grid">
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
                                    <span class="product-category"><?php echo htmlspecialchars($product['mcat_name']); ?></span>
                                </div>

                                <div class="product-meta">
                                    <i class="fas fa-box"></i>
                                    <span class="product-stock">
                                        <?php echo $product['stock_quantity'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                    </span>
                                </div>
                                <div class="price-tag">
                                    <p class="product-price">₹<?php echo number_format($product['p_current_price'], 2); ?></p>
                                    <p class="product-old-price">₹<?php echo number_format($product['p_old_price'], 2); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search"></i>   
                    <h2>No products found</h2>
                    <p>Try different keywords or browse our categories</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

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
    <?php include 'footer.php' ?>
</body>
</html> 