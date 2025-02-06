<?php
// session_start();
require_once 'header.php';
require_once 'db_connection.php';

// Redirect if no order ID in session
if (!isset($_SESSION['last_order_id'])) {
    header('Location: index.php');
    exit;
}

$order_id = $_SESSION['last_order_id'];

// Fetch order details
$stmt = $pdo->prepare("
    SELECT 
        o.*,
        p.p_name,
        p.p_featured_photo,
        ua.full_name,
        ua.address,
        ua.city,
        ua.state,
        ua.pincode,
        ua.phone_number
    FROM tbl_orders o
    JOIN tbl_product p ON o.product_id = p.id
    JOIN users_addresses ua ON o.address_id = ua.id
    WHERE o.order_id = ? AND o.user_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_session']['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Clear the session order ID
unset($_SESSION['last_order_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .order-confirmation {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        .order-item {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="order-confirmation">
            <div class="text-center mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 48px;"></i>
                <h1 class="mt-3">Order Confirmed!</h1>
                <p class="text-muted">Order ID: <?php echo htmlspecialchars($order_id); ?></p>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Delivery Address</h5>
                    <?php if (!empty($orders)): ?>
                        <?php $address = $orders[0]; // All orders have same address ?>
                        <p class="mb-1"><?php echo htmlspecialchars($address['full_name']); ?></p>
                        <p class="mb-1"><?php echo htmlspecialchars($address['address']); ?></p>
                        <p class="mb-1">
                            <?php echo htmlspecialchars($address['city'] . ', ' . $address['state'] . ' - ' . $address['pincode']); ?>
                        </p>
                        <p class="mb-1">Phone: <?php echo htmlspecialchars($address['phone_number']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Order Details</h5>
                    <?php
                    $total = 0;
                    foreach ($orders as $order):
                        $total += $order['price'] * $order['quantity'];
                    ?>
                        <div class="order-item">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="assets/uploads/product-photos/<?php echo htmlspecialchars($order['p_featured_photo']); ?>"
                                         alt="<?php echo htmlspecialchars($order['p_name']); ?>"
                                         class="product-image">
                                </div>
                                <div class="col-md-6">
                                    <h6><?php echo htmlspecialchars($order['p_name']); ?></h6>
                                    <p class="text-muted mb-0">Quantity: <?php echo $order['quantity']; ?></p>
                                </div>
                                <div class="col-md-4 text-end">
                                    <p class="mb-0">₹<?php echo number_format($order['price'] * $order['quantity'], 2); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="mt-4">
                        <div class="d-flex justify-content-between">
                            <h5>Total Amount</h5>
                            <h5>₹<?php echo number_format($total, 2); ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>