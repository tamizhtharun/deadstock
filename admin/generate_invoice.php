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

// Function to convert number to words (Indian currency format)
function numberToWords($number) {
    $hyphen      = '-';
    $conjunction = ' and ';
    $separator   = ', ';
    $negative    = 'negative ';
    $decimal     = ' point ';
    $dictionary  = [
        0                   => 'zero',
        1                   => 'one',
        2                   => 'two',
        3                   => 'three',
        4                   => 'four',
        5                   => 'five',
        6                   => 'six',
        7                   => 'seven',
        8                   => 'eight',
        9                   => 'nine',
        10                  => 'ten',
        11                  => 'eleven',
        12                  => 'twelve',
        13                  => 'thirteen',
        14                  => 'fourteen',
        15                  => 'fifteen',
        16                  => 'sixteen',
        17                  => 'seventeen',
        18                  => 'eighteen',
        19                  => 'nineteen',
        20                  => 'twenty',
        30                  => 'thirty',
        40                  => 'forty',
        50                  => 'fifty',
        60                  => 'sixty',
        70                  => 'seventy',
        80                  => 'eighty',
        90                  => 'ninety',
        100                 => 'hundred',
        1000                => 'thousand',
        100000              => 'lakh',
        10000000            => 'crore'
    ];

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'numberToWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . numberToWords(abs($number));
    }

    $string = $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens   = ((int) ($number / 10)) * 10;
            $units  = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds  = (int) ($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . numberToWords($remainder);
            }
            break;
        default:
            $baseUnit = pow(10, floor(log($number, 10) / 2) * 2);
            $numBaseUnits = (int) ($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= numberToWords($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = [];
        foreach (str_split((string) $fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }

    return ucfirst($string);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - Order #<?php echo htmlspecialchars($order['order_id']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f7f7f7; }
        .invoice-box { max-width: 900px; margin: auto; padding: 30px; border: 1px solid #eee; background: #fff; box-shadow: 0 0 10px rgba(0,0,0,0.15); }
        h1, h2, h3 { margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 12px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .download-button { margin-bottom: 20px; padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        .download-button:hover { background-color: #0056b3; }
        .section-title { background-color: #007bff; color: white; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .note { color: red; font-style: italic; }
        .flex-container { display: flex; justify-content: space-between; flex-wrap: wrap; }
        .flex-item { flex: 1 1 45%; margin-bottom: 20px; }
        .no-border { border: none !important; }
    </style>
</head>
<body>
<div class="invoice-box">
    <button class="download-button" onclick="downloadInvoice()">Download Invoice</button>
    <h1 style="text-align:center; margin-bottom: 30px;">Tax Invoice</h1>

    <div class="flex-container">
        <div class="flex-item">
            <h3 class="section-title">Company Details</h3>
            <p><strong>Name:</strong> <?php echo dummyIfEmpty($settings['paypal_email'], 'Company Name'); ?></p>
            <p><strong>Bank Details:</strong><br><?php echo nl2br(dummyIfEmpty($settings['bank_detail'], 'Bank Details')); ?></p>
            <p><strong>Terms & Conditions:</strong><br><?php echo nl2br(dummyIfEmpty($settings['seller_tc'], 'Terms & Conditions')); ?></p>
        </div>
        <div class="flex-item">
            <h3 class="section-title">Buyer / Shipping Details</h3>
            <p><strong>Buyer Name:</strong> <?php echo dummyIfEmpty($order['full_name'], 'Buyer Name'); ?></p>
            <p><strong>Shipping Address:</strong><br>
                <?php 
                if (!empty($order['address'])) {
                    echo nl2br(htmlspecialchars($order['address'] . ", " . $order['city'] . ", " . $order['state'] . " - " . $order['pincode']));
                } else {
                    echo "<span style='color:red;'>[No Shipping Address provided - please update]</span>";
                }
                ?>
            </p>
            <p><strong>GSTIN:</strong> <?php echo dummyIfEmpty($settings['paypal_email'], 'GSTIN'); ?></p>
        </div>
    </div>

    <div class="flex-container">
        <div class="flex-item">
            <h3 class="section-title">Invoice Info</h3>
            <p><strong>Invoice No. / Date:</strong> <?php echo dummyIfEmpty($order['order_id'], 'Invoice No.'); ?> / <?php echo dummyIfEmpty($order['processing_time'], 'Date'); ?></p>
            <p><strong>Order ID / Customer PO:</strong> <?php echo dummyIfEmpty($order['order_id'], 'Order ID'); ?></p>
            <p><strong>IRN Number / Ack Number:</strong> N/A</p>
            <p><strong>Payment Terms:</strong> Payment due within 30 days</p>
        </div>
    </div>

    <h3 class="section-title">Item Table</h3>
    <table>
        <thead>
            <tr>
                <th>Sr</th>
                <th>Item Name</th>
                <th>HSN/SAC</th>
                <th>Qty</th>
                <th>Rate</th>
                <th>Tax %</th>
                <th>Tax Amt</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td><?php echo dummyIfEmpty($order['p_name'], 'Item Name'); ?></td>
                <td><?php echo dummyIfEmpty($order['hsn_code'] ?? '1234', 'HSN/SAC'); ?></td>
                <td><?php echo dummyIfEmpty($order['quantity'], 'Quantity'); ?></td>
                <td>₹<?php echo dummyIfEmpty(number_format($order['price'], 2), 'Rate'); ?></td>
                <td>18%</td>
                <td>₹<?php echo number_format(($order['price'] * $order['quantity'] * 18 / 100), 2); ?></td>
                <td>₹<?php echo number_format(($order['price'] * $order['quantity'] * 118 / 100), 2); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="flex-container" style="justify-content: flex-end;">
        <div class="flex-item" style="flex: 0 0 300px;">
            <table>
                <tbody>
                    <tr>
                        <th>Subtotal</th>
                        <td>₹<?php echo number_format($order['price'] * $order['quantity'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>GST/IGST Breakdown</th>
                        <td>₹<?php echo number_format($order['price'] * $order['quantity'] * 18 / 100, 2); ?></td>
                    </tr>
                    <tr>
                        <th>Final Total</th>
                        <td>₹<?php echo number_format($order['price'] * $order['quantity'] * 118 / 100, 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-right" style="font-style: italic; font-size: 12px;">
                            (In words: <?php echo ucfirst(numberToWords($order['price'] * $order['quantity'] * 118 / 100)); ?> only)
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <h3 class="section-title">Footer</h3>
    <p><strong>Bank details:</strong> <?php echo nl2br(dummyIfEmpty($settings['bank_detail'], 'Bank details')); ?></p>
    <p><strong>Terms & Conditions:</strong> <?php echo nl2br(dummyIfEmpty($settings['seller_tc'], 'Terms & Conditions')); ?></p>
    <p><strong>Signature/Company Seal:</strong> ___________________________</p>

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
