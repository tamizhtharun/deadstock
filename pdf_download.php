<?php
include('db_connection.php');
$action = isset($_GET['action']) ? $_GET['action'] : '';
$product_id = isset($_GET['id']) ? $_GET['id'] : '';
if ($product_id) {
    
    $query = "SELECT product_catalogue FROM tbl_product WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product_data = $result->fetch_assoc();
        $pdf_name = $product_data['product_catalogue'];
        $file_path = 'assets/uploads/'. $pdf_name;  
     if (file_exists($file_path)) {
            if ($action === 'view') {
                // View PDF in a new tab (inline)
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
                readfile($file_path);
            } elseif ($action === 'download') {
                // Trigger PDF download
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
                readfile($file_path);
            } else {
                echo 'Invalid action specified';
            }
        } else {
            echo 'File not found: ' . htmlspecialchars($file_path);
        }
    } else {
        echo 'Product not found with ID: ' . htmlspecialchars($product_id);
    }
} else {
    echo 'Invalid or missing Product ID';
}
?>
