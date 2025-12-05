<?php
require 'db_connection.php';

// Check if 'search_text' is set (empty when the search bar is clicked without input)
if (isset($_GET['search_text'])) {
    $search_text = trim($_GET['search_text']);

    // Query to fetch top 5 active products based on views (if search_text is empty)
    if (empty($search_text)) {
        $stmt = $conn->prepare("
            SELECT p.id, p.p_name 
            FROM tbl_product p 
            WHERE p.p_is_approve = 1
            ORDER BY p.p_total_view DESC
            LIMIT 5
        ");
    }

    $stmt->execute();
    $result = $stmt->get_result();

    // Display product suggestions in dropdown
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<a href="/search-result.php?search_text=' . urlencode($row['p_name']) . '" class="suggestion-item">
                    <div class="search-icon"></div>
                    <span>' . htmlspecialchars($row['p_name']) . '</span>
                  </a>';
        }
    } else {
        echo '<li class="suggestion-item">No results found</li>';
    }

    $stmt->close();
    $conn->close();
}
?>