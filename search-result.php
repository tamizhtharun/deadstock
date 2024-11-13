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
                    function truncateDescription($description, $wordLimit = 7) {
                        $words = explode(' ', $description); // Split the description into words
                        if (count($words) > $wordLimit) {
                            return implode(' ', array_slice($words, 0, $wordLimit)) . '...'; // Truncate and append "..."
                        }
                        return $description; // Return the original description if within limit
                    }
                    ?>

<div class="main-content">
    <!-- Product Listings -->
    <div class="product-list">
        <div class="search-results">
            <!-- Product Cards -->
            <?php foreach ($result as $row): ?>
                <div class="product-card">
                    <a href="product_landing.php?id=<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>" style="text-decoration: none; color: inherit;">
                        <div class="product-card-left">
                            <img src="assets/uploads/<?php echo htmlspecialchars($row['p_featured_photo'], ENT_QUOTES, 'UTF-8'); ?>" 
                                 alt="<?php echo htmlspecialchars($row['p_name'], ENT_QUOTES, 'UTF-8'); ?>" 
                                 width="150px" height="150px" style="margin-bottom: 10px;">
                            
                            <h3 class="product-name" style="margin-bottom: 10px;">
                                <?php echo htmlspecialchars($row['p_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </h3>
                            
                            <div class="product-specs" style="margin-bottom: 20px;">
                                <p><?php echo htmlspecialchars(truncateDescription(strip_tags($row['p_description'])), ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            
                            <div class="product-pricing" style="margin-top: -20px;">
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
                            <div class="button-container" style="display: flex; margin-top: 10px; margin-right: 15px; margin-left: 15px;">
                <button class="action-button" style="margin-right: 10px;" onclick="startBid(<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>)">Start Bid</button>
                <button class="action-button" onclick="startBid(<?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?>)">Add to Cart</button>
            </div>
                            
                            
                            

                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
                                    

                                    <div class="pagination">
                                        <?php echo $pagination; ?>
                                    </div>
                                </div>
                            </div>
                        </div>