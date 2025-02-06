<?php
require 'db_connection.php';
if (isset($_GET['search_text'])) {
    $search_text = trim($_GET['search_text']);
    $stmt = $conn->prepare("
    SELECT p.id, p.p_name AS name, 'Product' AS type FROM tbl_product p WHERE p.p_name LIKE ? 
    UNION ALL
    SELECT tc.tcat_id AS id, tc.tcat_name AS name, 'Top Category' AS type FROM tbl_top_category tc WHERE tc.tcat_name LIKE ? 
    UNION ALL
    SELECT p.id, p.p_name AS name, 'Product' AS type FROM tbl_product p 
    JOIN tbl_top_category tc ON p.tcat_id = tc.tcat_id WHERE tc.tcat_name LIKE ?
    UNION ALL
    SELECT mc.mcat_id AS id, mc.mcat_name AS name, 'Mid Category' AS type FROM tbl_mid_category mc WHERE mc.mcat_name LIKE ? 
    UNION ALL
    SELECT p.id, p.p_name AS name, 'Product' AS type FROM tbl_product p 
    JOIN tbl_mid_category mc ON p.mcat_id = mc.mcat_id WHERE mc.mcat_name LIKE ?
    UNION ALL
    SELECT ec.ecat_id AS id, ec.ecat_name AS name, 'End Category' AS type FROM tbl_end_category ec WHERE ec.ecat_name LIKE ? 
    UNION ALL
    SELECT p.id, p.p_name AS name, 'Product' AS type FROM tbl_product p 
    JOIN tbl_end_category ec ON p.ecat_id = ec.ecat_id WHERE ec.ecat_name LIKE ?
    UNION ALL
    SELECT b.brand_id AS id, b.brand_name AS name, 'Brand' AS type FROM tbl_brands b WHERE b.brand_name LIKE ? 
    UNION ALL
    SELECT p.id, p.p_name AS name, 'Product' AS type FROM tbl_product p 
    JOIN tbl_brands b ON p.product_brand = b.brand_id WHERE b.brand_name LIKE ?
    LIMIT 6
");

$search_param = "%$search_text%"; // Add wildcard for LIKE query

// Bind parameters correctly
$stmt->bind_param("sssssssss", 
    $search_param, $search_param, $search_param, 
    $search_param, $search_param, $search_param, 
    $search_param, $search_param, $search_param
);

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<a href="search-result.php?search_text=' . urlencode($row['name']) . '" class="suggestion-item">
                <div class="search-icon"></div>
                <span>' . htmlspecialchars($row['name']) . '</span>
              </a>';
    }
} else {
    echo '<li class="suggestion-item">No results found</li>';
}

    $stmt->close();
    $conn->close();
}
?>