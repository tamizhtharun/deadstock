<?php
include 'db_connection.php';
include 'header.php';

if (!isset($_SESSION['user_session']['id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_session']['id'];

// Handle cart updates
if (isset($_POST['update_cart'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = $_POST['cart_quantity'];
    mysqli_query($conn, "UPDATE tbl_cart SET quantity = '$quantity' WHERE id = '$cart_id' AND user_id = '$user_id'");
}

// Handle item removal
if (isset($_GET['remove'])) {
    $remove_id = $_GET['remove'];
    mysqli_query($conn, "DELETE FROM tbl_cart WHERE id = '$remove_id' AND user_id = '$user_id'");
}

// Handle clear cart
if (isset($_GET['delete_all'])) {
    mysqli_query($conn, "DELETE FROM tbl_cart WHERE user_id = '$user_id'");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart | Your Store</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/cart.css">
</head>

<body>
    <div class="cart-page">
        <div class="container py-5">
            <!-- Cart Header -->
            <div class="cart-header mb-4">
                <h1 class="cart-title">Shopping Cart</h1>
                <div class="cart-steps">
                    <div class="step active">
                        <span class="step-number">1</span>
                        <span class="step-text">Cart</span>
                    </div>
                    <div class="step-divider"></div>
                    <div class="step">
                        <span class="step-number">2</span>
                        <span class="step-text">Shipping</span>
                    </div>
                    <div class="step-divider"></div>
                    <div class="step">
                        <span class="step-number">3</span>
                        <span class="step-text">Payment</span>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <!-- Cart Items -->
                <div class="col-lg-8">
                    <div class="cart-items card shadow-sm">
                        <div class="card-body">
                            <?php
                            $cart_query = mysqli_query($conn, "
                                SELECT c.*, p.p_name, p.p_featured_photo, p.p_current_price, p.p_old_price 
                                FROM tbl_cart c
                                INNER JOIN tbl_product p ON c.id = p.id
                                WHERE c.user_id = '$user_id'
                            ") or die('Query failed');

                            $grand_total = 0;
                            $total_savings = 0;

                            if (mysqli_num_rows($cart_query) > 0):
                                while ($item = mysqli_fetch_assoc($cart_query)):
                                    $sub_total = $item['p_current_price'] * $item['quantity'];
                                    $savings = ($item['p_old_price'] - $item['p_current_price']) * $item['quantity'];
                                    $grand_total += $sub_total;
                                    $total_savings += $savings;
                                    ?>
                                    <div class="cart-item" data-id="<?php echo $item['id']; ?>">
                                        <!-- Remove button moved to top right -->
                                        <a href="cart.php?remove=<?php echo $item['id']; ?>" class="remove-item"
                                            onclick="return confirm('Remove this item?')">
                                            <i class="fas fa-times"></i>
                                        </a>

                                        <div class="row align-items-center">
                                            <div class="col-md-2">
                                                <div class="item-image">
                                                    <img src="assets/uploads/product-photos/<?php echo $item['p_featured_photo']; ?>"
                                                        alt="<?php echo $item['p_name']; ?>" class="img-fluid rounded">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                            <h3 class="item-title"><a href="product_landing.php?id=<?php echo $item['id']; ?>">
                                                <?php echo $item['p_name']; ?></a></h3>
                                                <div class="item-meta">
                                                    <span class="text-muted">SKU: <?php echo $item['id']; ?></span>
                                                </div>
                                                <div class="price-section">
                                                    <div class="current-price">
                                                        ₹<?php echo number_format($item['p_current_price'], 2); ?></div>
                                                    <!-- <?php if ($item['p_old_price'] > $item['p_current_price']): ?> -->
                                                        <div class="old-price">
                                                            ₹<?php echo number_format($item['p_old_price'], 2); ?></div>

                                                    <?php endif; ?>
                                                </div>
                                                <?php if ($savings > 0): ?>
                                                    <div class="item-savings text-success">
                                                        You save: ₹<?php echo number_format($savings, 2); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="quantity-controls">
                                                    <form action="" method="post" class="quantity-form"
                                                        data-price="<?php echo $item['p_current_price']; ?>">
                                                        <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                                        <div class="input-group">
                                                            <!-- <button type="button"
                                                                class="btn btn-outline-secondary quantity-btn minus">
                                                                <i class="fas fa-minus"></i>
                                                            </button> -->
                                                            <input type="number" name="cart_quantity"
                                                                value="<?php echo $item['quantity']; ?>" min="1" max="99"
                                                                class="form-control text-center quantity-input"
                                                                data-item-id="<?php echo $item['id']; ?>">
                                                            <!-- <button type="button"
                                                                class="btn btn-outline-secondary quantity-btn plus">
                                                                <i class="fas fa-plus"></i>
                                                            </button> -->
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="total-price">
                                                    Total:₹
                                                    <span class="item-total"><?php echo number_format($sub_total, 2); ?></span>
                                                </div>
                                                <button class="bid-btn btn btn-success custom-btn">Bid</button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                endwhile;
                            else:
                                ?>
                                <div class="empty-cart text-center py-5">
                                    <i class="fas fa-shopping-cart fa-3x mb-3"></i>
                                    <h3>Your cart is empty</h3>
                                    <p class="text-muted">Browse our products and add items to your cart</p>
                                    <a href="index.php" class="btn btn-primary mt-3">Continue Shopping</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="col-lg-4">
    <div class="order-summary card shadow-sm sticky-top">
        <div class="card-body">
            <h3 class="card-title mb-4">Order Summary</h3>

            <div class="summary-item d-flex justify-content-between mb-3">
                <span>Subtotal</span>
                <span class="amount">₹<?php echo number_format($grand_total, 2); ?></span>
            </div>

            <?php if ($total_savings > 0): ?>
                <div class="summary-item d-flex justify-content-between mb-3 text-success">
                    <span>Total Savings</span>
                    <span class="amount">-₹<?php echo number_format($total_savings, 2); ?></span>
                </div>
            <?php endif; ?>

            <div class="summary-item d-flex justify-content-between mb-3">
                <span>Shipping</span>
                <span class="amount">₹<?php echo number_format(14, 2); ?></span>
            </div>

            <div class="total-amount d-flex justify-content-between mb-4">
                <span class="fw-bold">Total</span>
                <span class="amount fw-bold">₹<?php echo number_format($grand_total + 14, 2); ?></span>
            </div>

            <button class="btn btn-primary w-100 mb-3 checkout-btn">
                Proceed to Checkout
            </button>

            <div class="payment-methods text-center mt-4">
                <p class="text-muted mb-2">Secure Payment Methods</p>
                <div class="payment-icons">
                    <i class="fab fa-cc-visa mx-2"></i>
                    <i class="fab fa-cc-mastercard mx-2"></i>
                    <i class="fab fa-cc-amex mx-2"></i>
                    <i class="fab fa-cc-paypal mx-2"></i>
                </div>
            </div>
        </div>
    </div>
</div>
            </div>

            <!-- Recently Viewed -->
            <div class="recently-viewed mt-5">
                <h3 class="section-title mb-4">Recently Viewed</h3>
                <div class="row row-cols-2 row-cols-md-4 g-4">
                    <!-- Add your recently viewed items here -->
                    <?php
        // Get recently viewed products from session
        $recently_viewed = isset($_SESSION['recently_viewed']) ? $_SESSION['recently_viewed'] : array();
        
        if (!empty($recently_viewed)) {
            // Get the last 4 viewed products
            $recent_ids = array_slice(array_reverse($recently_viewed), 0, 4);
            $ids_string = implode(',', $recent_ids);
            
            $recent_query = mysqli_query($conn, "
                SELECT id, p_name, p_featured_photo, p_current_price, p_old_price 
                FROM tbl_product 
                WHERE id IN ($ids_string)
                LIMIT 4
            ");

            while ($product = mysqli_fetch_assoc($recent_query)):
        ?>
            <div class="col">
                <div class="recently-viewed-item">
                    <a href="product_landing.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                        <div class="recently-viewed-image">
                            <img src="assets/uploads/product-photos/<?php echo $product['p_featured_photo']; ?>"
                                 alt="<?php echo $product['p_name']; ?>">
                        </div>
                        <h4 class="recently-viewed-title"><?php echo $product['p_name']; ?></h4>
                        <div class="d-flex align-items-center">
                            <span class="recently-viewed-price">₹<?php echo number_format($product['p_current_price'], 2); ?></span>
                            <?php if ($product['p_old_price'] > $product['p_current_price']): ?>
                                <span class="recently-viewed-old-price">₹<?php echo number_format($product['p_old_price'], 2); ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            </div>
        <?php 
            endwhile;
        } else {
            echo '<div class="col-12 text-center text-muted">No recently viewed products</div>';
        }
        ?>

                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/cart.js"></script>
</body>

</html>