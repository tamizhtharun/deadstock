
<?php include 'header.php'; ?>
<?php
// session_start();
include 'db_connection.php';

// Get search query from URL
$search_query = isset($_GET['search_text']) ? trim($_GET['search_text']) : '';

// Prepare the search query with multiple conditions
$sql = "SELECT p.*, c.ecat_name, s.seller_cname 
        FROM tbl_product p
        LEFT JOIN tbl_end_category c ON p.ecat_id = c.ecat_id
        LEFT JOIN sellers s ON p.seller_id = s.seller_id
        WHERE (p.p_name LIKE ? 
        OR p.p_description LIKE ?
        OR c.ecat_name LIKE ?
        OR s.seller_cname LIKE ?)
        AND p.p_is_approve = '1'
        ORDER BY p.p_name ASC";

$search_term = "%{$search_query}%";
$stmt = $pdo->prepare($sql);
$stmt->execute([$search_term, $search_term, $search_term, $search_term]);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total_results = count($products);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Dead Stock</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Search Results Styles */
        .search-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .search-header {
            margin-bottom: 2rem;
        }

        .search-summary {
            color: #666;
            margin-bottom: 1rem;
        }

        .search-results {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
        }

        .product-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-details {
            padding: 1rem;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .product-category {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .product-seller {
            font-size: 0.9rem;
            color: #888;
            margin-bottom: 0.5rem;
        }

        .product-price {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2ecc71;
            margin-bottom: 1rem;
        }

        .product-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.2s;
            text-decoration: none;
            text-align: center;
            flex: 1;
        }

        .btn-primary {
            background-color: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background-color: #2980b9;
        }

        .btn-secondary {
            background-color: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background-color: #d0d0d0;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .filters {
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            outline: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .search-results {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 1rem;
            }

            .filters {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    

    <div class="search-container">
        <div class="search-header">
            <h4>Search Results for "<?php echo htmlspecialchars($search_query); ?>"</h4>
            <p class="search-summary">Found <?php echo $total_results; ?> results</p>
        </div>

        <div class="filters">
            <div class="filter-group">
                <label for="sort">Sort by:</label>
                <select id="sort" class="filter-select">
                    <option value="name_asc">Name (A-Z)</option>
                    <option value="name_desc">Name (Z-A)</option>
                    <option value="price_low">Price (Low to High)</option>
                    <option value="price_high">Price (High to Low)</option>
                </select>
            </div>

            <!-- <div class="filter-group">
                <label for="category">Category:</label>
                <select id="category" class="filter-select">
                    <option value="">All Categories</option>
                    <?php
                    $cat_stmt = $pdo->query("SELECT DISTINCT ecat_name FROM tbl_end_category");
                    while ($category = $cat_stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='" . htmlspecialchars($category['ecat_name']) . "'>" . 
                             htmlspecialchars($category['ecat_name']) . "</option>";
                    }
                    ?>
                </select>
            </div> -->
        </div>

        <div class="search-results">
            <?php if ($total_results > 0): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="assets/uploads/product-photos/<?php echo htmlspecialchars($product['p_featured_photo']); ?>" 
                             alt="<?php echo htmlspecialchars($product['p_name']); ?>" 
                             class="product-image" width="30px">
                        <div class="product-details">
                            <h2 class="product-name"><?php echo htmlspecialchars($product['p_name']); ?></h2>
                            <p class="product-category">
                                <i class="fas fa-tag"></i> 
                                <?php echo htmlspecialchars($product['ecat_name']); ?>
                            </p>
                            <!-- <p class="product-seller">
                                <i class="fas fa-store"></i> 
                                <?php echo htmlspecialchars($product['seller_cname']); ?>
                            </p> -->
                            <p class="product-price">₹<?php echo number_format($product['p_current_price'], 2); ?></p>
                            <div class="product-actions">
                                <a href="product_landing.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-primary">View Details</a>
                                <button onclick="addToCart(<?php echo $product['id']; ?>)" 
                                        class="btn btn-secondary">
                                    <i class="fas fa-cart-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <i class="fas fa-search" style="font-size: 3rem; color: #ddd; margin-bottom: 1rem;"></i>
                    <h2>No products found</h2>
                    <p>Try different keywords or browse our categories</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Function to add product to cart
        function addToCart(productId) {
            fetch('add-to-cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `product_id=${productId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product added to cart successfully!');
                    // Update cart count in header if needed
                    updateCartBadge();
                } else {
                    if (data.message === 'login_required') {
                        // Show login modal
                        $('#staticBackdrop').modal('show');
                    } else {
                        alert(data.message);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the product to cart.');
            });
        }

        // Sort and filter functionality
        document.getElementById('sort').addEventListener('change', filterProducts);
        document.getElementById('category').addEventListener('change', filterProducts);

        function filterProducts() {
            const sortValue = document.getElementById('sort').value;
            const categoryValue = document.getElementById('category').value;
            const products = document.querySelectorAll('.product-card');
            const productsArray = Array.from(products);

            productsArray.sort((a, b) => {
                const aValue = getSortValue(a, sortValue);
                const bValue = getSortValue(b, sortValue);
                
                if (sortValue.includes('name')) {
                    return sortValue === 'name_asc' ? 
                        aValue.localeCompare(bValue) : 
                        bValue.localeCompare(aValue);
                } else {
                    return sortValue === 'price_low' ? 
                        aValue - bValue : 
                        bValue - aValue;
                }
            });

            const results = document.querySelector('.search-results');
            results.innerHTML = '';
            
            productsArray.forEach(product => {
                if (!categoryValue || 
                    product.querySelector('.product-category').textContent.includes(categoryValue)) {
                    results.appendChild(product);
                }
            });
        }

        function getSortValue(product, sortType) {
            if (sortType.includes('name')) {
                return product.querySelector('.product-name').textContent;
            } else {
                return parseFloat(product.querySelector('.product-price').textContent.replace('₹', ''));
            }
        }
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>