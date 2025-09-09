<?php include("../db_connection.php"); ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = intval($_POST['id']);
        $p_is_featured = isset($_POST['p_is_featured']) ? intval($_POST['p_is_featured']) : null;
        $p_is_active = isset($_POST['p_is_active']) ? intval($_POST['p_is_active']) : null;

        // Check if product is approved before allowing featured to be set to 1
        if ($p_is_featured === 1) {
            $stmtCheck = $pdo->prepare("SELECT p_is_approve FROM tbl_product WHERE id = :id");
            $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtCheck->execute();
            $product = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if (!$product || $product['p_is_approve'] != 1) {
                // Product not approved, cannot set featured to 1
                $p_is_featured = 0;
            }
        }

        // Prepare the update statement
        $updateFields = [];
        if ($p_is_featured !== null) {
            $updateFields[] = "p_is_featured = :p_is_featured";
        }
        if ($p_is_active !== null) {
            $updateFields[] = "p_is_active = :p_is_active";
        }

        if (!empty($updateFields)) {
            $sql = "UPDATE tbl_product SET " . implode(", ", $updateFields) . " WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            if ($p_is_featured !== null) {
                $stmt->bindParam(':p_is_featured', $p_is_featured, PDO::PARAM_INT);
            }
            if ($p_is_active !== null) {
                $stmt->bindParam(':p_is_active', $p_is_active, PDO::PARAM_INT);
            }
            $stmt->execute();
        }
    } catch (PDOException $e) {
        // Handle the exception (log it, display a user-friendly message, etc.)
        echo "Error: " . $e->getMessage();
    }
}
?>
