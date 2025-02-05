<?php require_once('header.php'); ?>

<?php
try {
    if (isset($_GET['id'])) {
        $product_id = intval($_GET['id']); // Get the product ID from the URL

        // Prepare the SQL statement to get the current approval status
        $statusStatement = $pdo->prepare("SELECT p_is_approve FROM tbl_product WHERE id = :id");
        $statusStatement->bindParam(':id', $product_id, PDO::PARAM_INT);
        $statusStatement->execute();
        $product = $statusStatement->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Toggle approval status
            $new_status = $product['p_is_approve'] ? 0 : 1; // If approved (1), set to disapproved (0), and vice versa

            // Prepare the SQL statement to update the approval status
            $updateStatement = $pdo->prepare("UPDATE tbl_product SET p_is_approve = :new_status WHERE id = :id");
            $updateStatement->bindParam(':new_status', $new_status, PDO::PARAM_INT);
            $updateStatement->bindParam(':id', $product_id, PDO::PARAM_INT);

            if ($updateStatement->execute()) {
                // Check if the product was rejected
                if ($new_status == 0) { // If the product is rejected
                    // Update the product to set featured and active to 0
                    $updateFeaturedActiveStatement = $pdo->prepare("UPDATE tbl_product SET p_is_featured = 0 WHERE id = :id");
                    $updateFeaturedActiveStatement->bindParam(':id', $product_id, PDO::PARAM_INT);
                    $updateFeaturedActiveStatement->execute();
                }
                // Successfully updated, redirect back to the seller products page
                $seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0; // Get seller_id from URL
                header("Location: seller-products.php?seller_id=" . $seller_id);
                exit();
            } else {
                throw new Exception("Failed to update the product approval status.");
            }
        } else {
            throw new Exception("Product not found.");
        }
    } else {
        throw new Exception("No product ID provided.");
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>