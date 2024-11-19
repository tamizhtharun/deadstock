<?php
include 'db_connection.php';
session_start(); 

if (!isset($_SESSION['user_session']['id'])) {
  echo 'please login' ;
  
}
$user_id = $_SESSION['user_session']['id'];
?>

<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shopping Cart</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
 
</head>
<body class="bg-light my-5">
  <!-- Cart + Summary -->
  <section class="bg-light my-5">
    <div class="container">
      <div class="row">
        <!-- Cart -->
        <div class="col-lg-9">
          <div class="card border shadow-sm cart-container">
            <div class="card-body">
              <h4 class="card-title mb-4">Your shopping cart</h4>
              <div class="shopping-cart">
        <h1 class="heading">Shopping Cart</h1>
        <table>
            <thead>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Action</th>
            </thead>
            <tbody>
            <?php
            $cart_query = mysqli_query($conn, "SELECT * FROM `tbl_cart` WHERE user_id = '$user_id'") or die('Query failed');
            $grand_total = 0;
            if (mysqli_num_rows($cart_query) > 0) {
                while ($fetch_cart = mysqli_fetch_assoc($cart_query)) {
                    $sub_total = $fetch_cart['price'] * $fetch_cart['quantity'];
                    $grand_total += $sub_total;
            ?>
                <tr>
                    <td><img src="assets/uploads/<?php echo $fetch_cart['image']; ?>" height="100" alt=""></td>
                    <td><?php echo $fetch_cart['name']; ?></td>
                    <td>₹<?php echo $fetch_cart['price']; ?> </td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="cart_id" value="<?php echo $fetch_cart['id']; ?>">
                            <input type="number" min="1" name="cart_quantity" value="<?php echo $fetch_cart['quantity']; ?>">
                            <input type="submit" name="update_cart" value="Update" class="option-btn">
                        </form>
                    </td>
                    <td>$<?php echo $sub_total; ?>/-</td>
                    <td><a href="product_landing.php?remove=<?php echo $fetch_cart['id']; ?>" class="delete-btn" onclick="return confirm('Remove item from cart?');">Remove</a></td>
                </tr>
            <?php
                }
            } else {
                echo '<tr><td colspan="6" style="padding:20px; text-transform:capitalize;">No items added</td></tr>';
            }
            ?>
            <tr class="table-bottom">
                <td colspan="4">Grand Total:</td>
                <td>$<?php echo $grand_total; ?>/-</td>
                <td><a href="product_landing.php?delete_all" onclick="return confirm('Delete all from cart?');" class="delete-btn <?php echo ($grand_total > 1) ? '' : 'disabled'; ?>">Delete All</a></td>
            </tr>
            </tbody>
        </table>
        <div class="cart-btn">
            <a href="#" class="btn <?php echo ($grand_total > 1) ? '' : 'disabled'; ?>">Proceed to Checkout</a>
        </div>
    </div>
</body>
</html>