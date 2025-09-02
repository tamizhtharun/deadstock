<?php
include 'header.php'; 

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = intval($_GET['id']);
    $status = intval($_GET['status']);

    // Approve logic
    if ($status == 0) {
        // Update is_discount to 0 and set current price = discount price
        $sql = "UPDATE tbl_product 
                SET is_discount = 0, 
                p_current_price = p_discount_price
                WHERE id = ?";
    } 
    // Reject logic
    else {
        // Just update approve flag back to 1 (or whatever logic you use)
        $sql = "UPDATE tbl_product 
                SET p_is_approve = 1 
                WHERE id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: discount-products.php?msg=success");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>
