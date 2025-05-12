<?php
require_once('header.php');
require_once('../config.php'); // Assuming config.php has the PDO $pdo connection

// Helper function to fetch settings
function getSettings($pdo) {
    $stmt = $pdo->prepare("SELECT paypal_email, bank_detail, seller_tc FROM tbl_settings WHERE id=1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Helper function to fetch direct order details
function getDirectOrderDetails($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT 
        o.order_id,
        o.price,
        o.quantity,
        o.order_status,
        o.processing_time,
        o.tracking_id,
        p.p_name,
        p.p_featured_photo,
        s.seller_name,
        s.seller_cname,
        u.username,
        u.email,
        u.phone_number,
        ua.full_name,
        ua.phone_number as delivery_phone,
        ua.address,
        ua.city,
        ua.state,
        ua.pincode
        FROM tbl_orders o
        JOIN tbl_product p ON o.product_id = p.id
        JOIN sellers s ON p.seller_id = s.seller_id
        JOIN users u ON o.user_id = u.id
        LEFT JOIN users_addresses ua ON o.address_id = ua.id
        WHERE o.order_id = ? AND o.order_type = 'direct'
        LIMIT 1");
    $stmt->execute([$order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Helper function to fetch bidding order details
function getBiddingOrderDetails($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT 
        o.order_id,
        b.bid_price AS price,
        b.bid_quantity AS quantity,
        o.order_status,
        o.processing_time,
        o.tracking_id,
        p.p_name,
        p.p_featured_photo,
        s.seller_name,
        s.seller_cname,
        u.username,
        u.email,
        u.phone_number,
        ua.full_name,
        ua.phone_number as delivery_phone,
        ua.address,
        ua.city,
        ua.state,
        ua.pincode
        FROM tbl_orders o
        JOIN bidding b ON o.bid_id = b.bid_id
        JOIN tbl_product p ON b.product_id = p.id
        JOIN sellers s ON p.seller_id = s.seller_id
        JOIN users u ON b.user_id = u.id
        LEFT JOIN users_addresses ua ON u.id = ua.user_id AND ua.is_default = 1
        WHERE o.id = ? AND o.order_type = 'bid'
        LIMIT 1");
    $stmt->execute([$order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get order_id from GET
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

if (!$order_id) {
    echo "<h2>Invalid order ID.</h2>";
    exit;
}

// Try to get direct order details first
$order = getDirectOrderDetails($pdo, $order_id);

$order_type = 'direct';

if (!$order) {
    // Try bidding order details
    $order = getBiddingOrderDetails($pdo, $order_id);
    $order_type = 'bid';
}

if (!$order) {
    echo "<h2>Order not found.</h2>";
    exit;
}

// Get settings
$settings = getSettings($pdo);

// Prepare dummy data placeholders
function dummyIfEmpty($value, $fieldName) {
    if (empty($value)) {
        return "<span style='color:red;'>[No {$fieldName} provided - please update]</span>";
    }
    return htmlspecialchars($value);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - Order #<?php echo htmlspecialchars($order['order_id']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
        h1, h2, h3 { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        .text-right { text-align: right; }
        .download-button { margin-bottom: 20px; padding: 8px 16px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .download-button:hover { background-color: #0056b3; }
        .note { color: red; font-style: italic; }
    </style>
</head>
<body>
<div class="invoice-box">
    <button class="download-button" onclick="downloadInvoice()">Download Invoice</button>
    <h1>Invoice</h1>
    <h3>Order Details (<?php echo ucfirst($order_type); ?> Order)</h3>
    <table>
        <tr>
            <th>Order ID</th>
            <td><?php echo dummyIfEmpty($order['order_id'], 'Order ID'); ?></td>
        </tr>
        <tr>
            <th>Product</th>
            <td><?php echo dummyIfEmpty($order['p_name'], 'Product Name'); ?></td>
        </tr>
        <tr>
            <th>Quantity</th>
            <td><?php echo dummyIfEmpty($order['quantity'], 'Quantity'); ?></td>
        </tr>
        <tr>
            <th>Price</th>
            <td>₹<?php echo dummyIfEmpty(number_format($order['price'], 2), 'Price'); ?></td>
        </tr>
        <tr>
            <th>Status</th>
            <td><?php echo dummyIfEmpty(ucfirst($order['order_status']), 'Order Status'); ?></td>
        </tr>
        <tr>
            <th>Processing Time</th>
            <td><?php echo dummyIfEmpty($order['processing_time'], 'Processing Time'); ?></td>
        </tr>
        <tr>
            <th>Tracking ID</th>
            <td><?php echo dummyIfEmpty($order['tracking_id'], 'Tracking ID'); ?></td>
        </tr>
    </table>

    <h3>Seller Details</h3>
    <table>
        <tr>
            <th>Seller Name</th>
            <td><?php echo dummyIfEmpty($order['seller_name'], 'Seller Name'); ?></td>
        </tr>
        <tr>
            <th>Company Name</th>
            <td><?php echo dummyIfEmpty($order['seller_cname'], 'Seller Company Name'); ?></td>
        </tr>
    </table>

    <h3>Customer Details</h3>
    <table>
        <tr>
            <th>Username</th>
            <td><?php echo dummyIfEmpty($order['username'], 'Username'); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo dummyIfEmpty($order['email'], 'Email'); ?></td>
        </tr>
        <tr>
            <th>Phone Number</th>
            <td><?php echo dummyIfEmpty($order['phone_number'], 'Phone Number'); ?></td>
        </tr>
    </table>

    <h3>Delivery Address</h3>
    <table>
        <tr>
            <th>Full Name</th>
            <td><?php echo dummyIfEmpty($order['full_name'], 'Full Name'); ?></td>
        </tr>
        <tr>
            <th>Phone Number</th>
            <td><?php echo dummyIfEmpty($order['delivery_phone'], 'Delivery Phone Number'); ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?php echo dummyIfEmpty($order['address'], 'Address'); ?></td>
        </tr>
        <tr>
            <th>City</th>
            <td><?php echo dummyIfEmpty($order['city'], 'City'); ?></td>
        </tr>
        <tr>
            <th>State</th>
            <td><?php echo dummyIfEmpty($order['state'], 'State'); ?></td>
        </tr>
        <tr>
            <th>Pincode</th>
            <td><?php echo dummyIfEmpty($order['pincode'], 'Pincode'); ?></td>
        </tr>
    </table>

    <h3>Payment Information</h3>
    <table>
        <tr>
            <th>PayPal Email</th>
            <td><?php echo dummyIfEmpty($settings['paypal_email'], 'PayPal Email'); ?></td>
        </tr>
        <tr>
            <th>Bank Details</th>
            <td><?php echo nl2br(dummyIfEmpty($settings['bank_detail'], 'Bank Details')); ?></td>
        </tr>
    </table>

    <h3>Seller Terms and Conditions</h3>
    <div class="note">
        <?php echo nl2br(dummyIfEmpty($settings['seller_tc'], 'Seller Terms and Conditions')); ?>
    </div>

    <p class="note">* If any information is missing, please update it in the admin settings.</p>
</div>

<script>
function downloadInvoice() {
    const orderId = <?php echo json_encode($order_id); ?>;
    const url = 'generate_invoice_pdf.php?order_id=' + encodeURIComponent(orderId);
    window.open(url, '_blank');
}
</script>
</body>
</html>
?>
