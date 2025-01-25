<?php require_once('header.php'); ?>
<link rel="stylesheet" href="index.css">

<?php
if(!isset($_REQUEST['search_text']) || trim($_REQUEST['search_text']) === '') {
    header('location: index.php');
    exit;
} else {
    $search_text = strip_tags($_REQUEST['search_text']);
}
?>


<?php
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);                            
foreach ($result as $row) {
    $banner_search = $row['banner_search'];
}
?>
<?php
    $search_text = '%'.$search_text.'%';
?>

<?php
/* ===================== Pagination Code Starts ================== */
$adjacents = 5;
$limit = 12; // Define the limit for items per page
$page = @$_GET['page']; // Get the current page number
if ($page) 
    $start = ($page - 1) * $limit; // Calculate the starting item for the current page
else
    $start = 0; // Default to 0 if no page is set

// Modify the query to include both active and inactive products
$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_name LIKE ? LIMIT $start, $limit");
$statement->execute(array('%' . $search_text . '%')); // Include % for partial matching in LIKE clause
$total_pages = $statement->rowCount();

$targetpage = 'search-result.php?search_text=' . $_REQUEST['search_text']; // your file name (the name of this file)
$page = @$_GET['page'];
if ($page) 
    $start = ($page - 1) * $limit; // first item to display on this page
else
    $start = 0;

$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE p_name LIKE ? LIMIT $start, $limit");
$statement->execute(array('%' . $search_text . '%')); // Include % for partial matching in LIKE clause
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

if ($page == 0) $page = 1; // if no page var is given, default to 1.
$prev = $page - 1; // previous page is page - 1
$next = $page + 1; // next page is page + 1
$lastpage = ceil($total_pages / $limit); // lastpage is = total pages / items per page, rounded up.
$lpm1 = $lastpage - 1;
$pagination = "";
if ($lastpage > 1) {   
    $pagination .= "<div class=\"pagination\">";
    if ($page > 1) 
        $pagination .= "<a href=\"$targetpage&page=$prev\">&#171; previous</a>";
    else
        $pagination .= "<span class=\"disabled\">&#171; previous</span>";    
    if ($lastpage < 7 + ($adjacents * 2)) {   
        for ($counter = 1; $counter <= $lastpage; $counter++) {
            if ($counter == $page)
                $pagination .= "<span class=\"current\">$counter</span>";
            else
                $pagination .= "<a href=\"$targetpage&page=$counter\">$counter</a>";                 
        }
    } elseif ($lastpage > 5 + ($adjacents * 2)) {
        if ($page < 1 + ($adjacents * 2)) {
            for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
                if ($counter == $page)
                    $pagination .= "<span class=\"current\">$counter</span>";
                else
                    $pagination .= "<a href=\"$targetpage&page=$counter\">$counter</a>";                 
            }
            $pagination .= "...";
            $pagination .= "<a href=\"$targetpage&page=$lpm1\">$lpm1</a>";
            $pagination .= "<a href=\"$targetpage&page=$lastpage\">$lastpage</a>";       
        } elseif ($lastpage - ($adjacents * 2) > $page && $page > ($adjacents * 2)) {
            $pagination .= "<a href=\"$targetpage&page=1\">1</a>";
            $pagination .= "<a href=\"$targetpage&page=2\">2</a>";
            $pagination .= "...";
            for ($counter = $page - $adjacents; $counter <= $page + $adjacents; $counter++) {
                if ($counter == $page)
                    $pagination .= "<span class=\"current\">$counter</span>";
                else
                    $pagination .= "<a href=\"$targetpage&page=$counter\">$counter</a>";                 
            }
            $pagination .= "...";
            $pagination .= "<a href=\"$targetpage&page=$lpm1\">$lpm1</a>";
            $pagination .= "<a href=\"$targetpage&page=$lastpage\">$lastpage</a>";       
        } else {
            $pagination .= "<a href=\"$targetpage&page=1\">1</a>";
            $pagination .= "<a href=\"$targetpage&page=2\">2</a>";
            $pagination .= "...";
            for ($counter = $lastpage - (2 + ($adjacents * 2)); $counter <= $lastpage; $counter++) {
                if ($counter == $page)
                    $pagination .= "<span class=\"current\">$counter</span>";
                else
                    $pagination .= "<a href=\"$targetpage&page=$counter\">$counter</a>";                 
            }
        }
    }
    if ($page < $counter - 1) 
        $pagination .= "<a href=\"$targetpage&page=$next\">next &#187;</a>";
    else
        $pagination .= "<span class=\"disabled\">next &#187;</span>";
    $pagination .= "</div>\n";       
}
/* ===================== Pagination Code Ends ================== */
?>
<?php
// Database connection

// Fetch top categories from the database
$statement = $pdo->prepare("SELECT * FROM tbl_top_category");
$statement->execute();
$top_categories = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .checkbox-label {
        margin-left: 10px; /* Adjust the value as needed */
    }
    .filter-section {
        margin-bottom: 20px; /* Add space between sections */
    }
</style>
        
<div class="product-page">
    <div class="col-md-12">
        <!-- Filter Sidebar -->
        <div class="filter-sidebar">
            <!-- Existing filter structure -->
            <h3>Filters</h3>
            <div class="filter-section">
                <h4>Categories</h4>
                <?php foreach ($top_categories as $top_category): ?>
                    <label class="checkbox-label">
                        <input type="checkbox" class="top-category" value="<?php echo htmlspecialchars($top_category['tcat_id']); ?>">
                        <?php echo htmlspecialchars($top_category['tcat_name']); ?>
                    </label><br>
                <?php endforeach; ?>
            </div>

            <!-- Mid Categories (will be displayed dynamically) -->
            <div class="filter-section" id="mid-category-section" style="display: none;">
                <h4>Mid Categories</h4>
                <div id="mid-category-checkboxes"></div>
            </div>

            <!-- End Categories (will be displayed dynamically) -->
            <div class="filter-section" id="end-category-section" style="display: none;">
                <h4>End Categories</h4>
                <div id="end-category-checkboxes"></div>
            </div>

            <!-- Price Range Filters -->
            <div class="filter-section">
                <h4>Price</h4>
                <div class="price-range">
                    <label for="min-price">Min:</label>
                    <select id="min-price" name="min-price" class="price-input">
                        <option value="500">₹500</option>
                        <option value="1000">₹1000</option>
                        <option value="1500">₹1500</option>
                        <option value="2000">₹2000</option>
                        <option value="3000">₹3000</option>
                    </select>
                    <label for="max-price">Max:</label>
                    <select id="max-price" name="max-price" class="price-input">
                        <option value="500">₹500</option>
                        <option value="1000">₹1000</option>
                        <option value="1500">₹1500</option>
                        <option value="2000">₹2000</option>
                        <option value="3000">₹3000</option>
                        <option value="+3000">₹+3000</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.top-category').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
        const selectedTopCategoryId = this.value;

        // Fetch mid categories based on selected top category
        fetch('filter_mid_categories.php?tcat_id=' + selectedTopCategoryId)
            .then(response => response.json())
            .then(data => {
                const midCategorySection = document.getElementById('mid-category-section');
                const midCategoryCheckboxes = document.getElementById('mid-category-checkboxes');
                midCategoryCheckboxes.innerHTML = ''; // Clear previous checkboxes

                if (data.length > 0) {
                    midCategorySection.style.display = 'block';
                    data.forEach(midCategory => {
                        midCategoryCheckboxes.innerHTML += `
                            <label class="checkbox-label">
                                <input type="checkbox" class="mid-category" value="${midCategory.mcat_id}">
                                ${midCategory.mcat_name}
                            </label><br>
                        `;
                    });
                } else {
                    midCategorySection.style.display = 'none';
                }
            });

        // Fetch end categories based on selected top category
        fetch('filter_end_categories.php?tcat_id=' + selectedTopCategoryId)
            .then(response => response.json())
            .then(data => {
                const endCategorySection = document.getElementById('end-category-section');
                const endCategoryCheckboxes = document.getElementById('end-category-checkboxes');
                endCategoryCheckboxes.innerHTML = ''; // Clear previous checkboxes

                if (data.length > 0) {
                    endCategorySection.style.display = 'block';
                    data.forEach(endCategory => {
                        endCategoryCheckboxes.innerHTML += `
                            <label class="checkbox-label">
                                <input type="checkbox" class="end-category" value="${endCategory.ecat_id}">
                                ${endCategory.ecat_name}
                            </label><br>
                        `;
                    });
                } else {
                    endCategorySection.style.display = 'none';
                }
            });
    });
});
</script>


        <?php
            // Function to truncate the description to a specific word limit
            function truncateDescription($description, $wordLimit = 5) {
                $words = explode(' ', $description); // Split the description into words
                if (count($words) > $wordLimit) {
                    return implode(' ', array_slice($words, 0, $wordLimit)) . '...'; // Truncate and append "..."
                }
                return $description; // Return the original description if within limit
            }
            ?>

<?php
$total_statement = $pdo->prepare("SELECT COUNT(*) as total FROM tbl_product WHERE p_name LIKE ?");
$total_statement->execute(array('%' . $_REQUEST['search_text'] . '%'));
$total_result = $total_statement->fetch(PDO::FETCH_ASSOC);
$total_products = $total_result['total'];
?>

<div class="main-content">
    <div class="search-results-summary">
        <?php if ($total_products > 0): ?>
            <span style="font-weight: bold; font-family: Arial, sans-serif;">
                Showing <?php echo htmlspecialchars($total_products, ENT_QUOTES, 'UTF-8'); ?> results for "<?php echo htmlspecialchars($_REQUEST['search_text'], ENT_QUOTES, 'UTF-8'); ?>"
            </span>
        <?php else: ?>
            <span style="font-weight: bold; font-family: Arial, sans-serif;">
                No results found for "<?php echo htmlspecialchars($_REQUEST['search_text'], ENT_QUOTES, 'UTF-8'); ?>"
            </span>
        <?php endif; ?>
    </div>

    <div class="search-results <?php echo 'items-' . count($result); ?>">
        <?php foreach ($result as $row): ?>
            <div class="product-card">
                <div class="product-card-inner">
                    <div class="product-image">
                        <img src="assets/uploads/<?php echo htmlspecialchars($row['p_featured_photo'], ENT_QUOTES, 'UTF-8'); ?>" 
                             alt="<?php echo htmlspecialchars($row['p_name'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>
                    
                    <h3 class="product-name">
                        <?php echo htmlspecialchars($row['p_name'], ENT_QUOTES, 'UTF-8'); ?>
                    </h3>
                    
                    <div class="product-specs">
                        <p><?php echo htmlspecialchars(truncateDescription(strip_tags($row['p_description'])), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    
                    <div class="product-pricing">
                        <span class="current-price">₹<?php echo number_format($row['p_current_price'], 2); ?></span>
                        <?php if (!empty($row['p_old_price'])): ?>
                            <span class="original-price"><del>₹<?php echo number_format($row['p_old_price'], 2); ?></del></span>
                            <span class="discount">
                                <?php
                                $discount = (($row['p_old_price'] - $row['p_current_price']) / $row['p_old_price']) * 100;
                                echo round($discount) . '% OFF';
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="button-container">
                        <button class="action-button" onclick="startBid(<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>)">Start Bid</button>
                        <button class="action-button" onclick="addToCart(<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>)">Add to Cart</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="pagination">
        <?php echo $pagination; ?>
    </div>
</div>
<style>
 
.search-results-summary {
    font-size: 18px;
    margin-bottom: 20px;
    font-weight: bold;
}

.search-results {
    display: grid;
    gap: 15px; /* Reduced from 20px */
    padding: 20px 0;
    width: 100%;
    grid-template-columns: repeat(4, 1fr);
}

/* Specific layouts for 1-3 items */
.search-results.items-1 {
    grid-template-columns: minmax(auto, 650px); /* Increased from 600px */
    justify-content: center;
}

.search-results.items-2 {
    grid-template-columns: repeat(2, minmax(auto, 450px)); /* Increased from 400px */
    justify-content: center;
}

.search-results.items-3 {
    grid-template-columns: repeat(3, minmax(auto, 400px)); /* Increased from 350px */
    justify-content: center;
}

.product-card {
    background: white;
    border: 1px solid #eee;
    border-radius: 8px;
    transition: box-shadow 0.3s ease;
    height: 100%;
}

.product-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.product-card-inner {
    padding: 12px; /* Reduced from 15px */
    display: flex;
    flex-direction: column;
    height: 100%;
}

.product-image {
    aspect-ratio: 4/3; /* Changed from 1/1 to make image shorter */
    margin-bottom: 12px; /* Reduced from 15px */
    overflow: hidden;
    border-radius: 4px;
    max-height: 180px; /* Added max-height constraint */
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: contain; /* Changed from cover to contain */
}

.product-name {
    font-size: 15px; /* Reduced from 16px */
    line-height: 1.4;
    margin: 0 0 12px 0;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 42px; /* Reduced from 48px */
}

.product-specs {
    margin-bottom: 12px;
    flex-grow: 1;
}

.product-specs p {
    font-size: 13px; /* Reduced from 14px */
    line-height: 1.4;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    color: #666;
}

.product-pricing {
    display: flex;
    align-items: center;
    gap: 6px; /* Reduced from 8px */
    margin-bottom: 14px;
    flex-wrap: wrap;
}

.current-price {
    font-size: 12px; /* Reduced from 18px */
    font-weight: bold;
    color: #e41e31;
}

.original-price {
    color: #666;
    font-size: 10px; /* Reduced from 14px */
    text-decoration: line-through;
}

.discount {
    background: #e41e31;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 9px; /* Reduced from 12px */
}

.button-container {
    display: flex;
    gap: 8px; /* Reduced from 10px */
    margin-top: auto;
}
pp  
.action-button {
    flex: 1;
    padding: 8px 14px; /* Reduced horizontal padding */
    background: white;
    border: 1px solid #000;
    border-radius: 4px;
    color: #000;
    cursor: pointer;
    font-size: 13px; /* Reduced from 14px */
    transition: all 0.3s ease;
}

.action-button:hover {
    background: #000;
    color: white;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .search-results {
        grid-template-columns: repeat(3, 1fr);
        gap: 12px; /* Further reduced gap for smaller screens */
    }
    
    .search-results.items-3 {
        grid-template-columns: repeat(3, minmax(auto, 320px));
    }
}

@media (max-width: 900px) {
    .search-results {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px; /* Further reduced gap for smaller screens */
    }
    
    .search-results.items-2,
    .search-results.items-3 {
        grid-template-columns: repeat(2, minmax(auto, 380px));
    }
}

@media (max-width: 600px) {
    .search-results,
    .search-results.items-1,
    .search-results.items-2,
    .search-results.items-3 {
        grid-template-columns: 1fr;
        gap: 15px; /* Increased gap for single column */
    }
    
    .product-card {
        max-width: 100%;
    }
    
    .product-image {
        max-height: 200px; /* Slightly larger images on mobile */
    }
}
</style>