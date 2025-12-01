<?php
session_start();
include 'db_connection.php';

// Simulate logged in user - assuming user id 1 exists
$_SESSION['user_session']['id'] = 1;

// Get product id from slug
$slug = 'turning-insert-positive-rhombic-80';
$stmt = $pdo->prepare("SELECT id FROM tbl_product WHERE p_slug = ?");
$stmt->execute([$slug]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$product_id = $result['id'];

// Simulate POST data
$_POST['add_to_cart'] = true;
$_POST['product_id'] = $product_id;
$_POST['product_quantity'] = 1;

// Include the add_to_cart logic from product_landing.php
if (isset($_POST['add_to_cart'])) {
  header('Content-Type: application/json');
  if (isset($_SESSION['user_session']['id'])) {
    $user_id = $_SESSION['user_session']['id'];
    $product_id_cart = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $product_quantity = isset($_POST['product_quantity']) ? intval($_POST['product_quantity']) : 1;

    // Check if product already exists in the cart
    $stmt = $conn->prepare("SELECT * FROM tbl_cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $product_id_cart, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      echo json_encode(['status' => 'error', 'message' => 'Product already added to cart!']);
    } else {
      $stmt = $conn->prepare("INSERT INTO tbl_cart (id, user_id, quantity) VALUES (?, ?, ?)");
      $stmt->bind_param("iii", $product_id_cart, $user_id, $product_quantity);
      if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Product added to cart!']);
      } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add product to cart.']);
      }
    }
  } else {
    echo json_encode(['status' => 'login_required']);
  }
  exit;
}
?>
