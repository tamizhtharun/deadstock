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
<div class="product-page">
            <div class="col-md-12">
                <!-- Filter Sidebar -->
                <div class="filter-sidebar">
    <h3>Filters</h3>
    <div class="filter-section">
        <h4>Categories</h4>
        <label><input type="checkbox" name="Categories[]" value="Cutting Tools"> Cutting Tools</label><br>
        <label><input type="checkbox" name="Categories[]" value="Saw Blades"> Saw Blades </label><br>
        <label><input type="checkbox" name="Categories[]" value="Hand Tools"> Hand Tools </label><br>
        <label><input type="checkbox" name="Categories[]" value="Abrasive Wheels"> Abrasive Wheels </label><br>
        <label><input type="checkbox" name="Categories[]" value="Power Tools"> Power Tools </label><br>
    </div>

    <div class="filter-section">
        <h4>Price</h4>
        <div class="price-range">
            <input type="number" id="min-price" name="min-price" placeholder="Min" class="price-input">
            <span>to</span>
            <input type="number" id="max-price" name="max-price" placeholder="Max" class="price-input">
        </div>
    </div>
</div>

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
                            <div class="product-specs" style="margin-bottom: 10px;">
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
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>

            <div class="pagination">
                <?php echo $pagination; ?>
            </div>
        </div>
    </div>
</div>