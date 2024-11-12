<?php include("../db_connection.php"); ?>

<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $id = intval($_POST['id']);
        $p_is_featured = isset($_POST['p_is_featured']) ? intval($_POST['p_is_featured']) : null;
        $p_is_active = isset($_POST['p_is_active']) ? intval($_POST['p_is_active']) : null;

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