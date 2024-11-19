<?php include 'header.php'; ?>
Hello
<?php include 'footer.php'; ?>

<?php
include 'db_connection.php';
include 'product_landing.php';

if (isset($_POST['update_cart'])) {
    $update_quantity = $_POST['cart_quantity'];
    $update_id = $_POST['cart_id'];
    mysqli_query($conn, "UPDATE `tbl_cart` SET quantity = '$update_quantity' WHERE id = '$update_id'") or die('Query failed');
    $message[] = 'Cart quantity updated successfully!';
  }
  
  if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    mysqli_query($conn, "DELETE FROM `tbl_cart` WHERE id = '$remove_id'") or die('Query failed');
  }
  
  if (isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM `tbl_cart` WHERE user_id = '$user_id'") or die('Query failed');
  }
  

?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/cart.css">
    <title>Shopping Cart</title>
</head>
<body>
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
