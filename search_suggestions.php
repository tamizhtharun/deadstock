<?php
require 'db_connection.php'; // Include your database connection

if (isset($_GET['search_text'])) {
    $search_text = $_GET['search_text'];

    $stmt = $conn->prepare("SELECT id,p_name, p_featured_photo FROM tbl_product WHERE p_name LIKE ? LIMIT 5");
    $search_param = "%$search_text%";
    $stmt->bind_param("s", $search_param);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<a href="search-result.php?search_text=' . $row['p_name'] . '" class="suggestion-item">
            <img src="assets/uploads/product-photos/' . htmlspecialchars($row['p_featured_photo']) . '" alt="' . htmlspecialchars($row['p_name']) . '">
            <span>' . htmlspecialchars($row['p_name']) . '</span>
                  </li>';
        }
    } else {
        echo '<li class="suggestion-item">No results found</li>';
    }
    $stmt->close();
    $conn->close();
}
?>
