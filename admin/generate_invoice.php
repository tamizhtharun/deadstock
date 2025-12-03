<?php
require_once('../db_connection.php');

// Helper function to fetch settings
function getSettings($pdo) {
    $stmt = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Helper function to fetch direct order details (with HSN code)
function getDirectOrderDetails($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT 
        o.id, o.order_id, o.invoice_number, o.price, o.quantity, o.order_status,
        o.processing_time, o.tracking_id, o.created_at,
        p.id AS product_id, p.p_name, p.hsn_code, p.p_featured_photo,
        s.seller_name, s.seller_cname, s.seller_email, s.seller_phone, s.seller_address,
        u.username, u.email, u.phone_number,
        ua.full_name, ua.phone_number as delivery_phone, ua.address, ua.city, ua.state, ua.pincode
        FROM tbl_orders o
        JOIN tbl_product p ON o.product_id = p.id
        JOIN sellers s ON p.seller_id = s.seller_id
        JOIN users u ON o.user_id = u.id
        LEFT JOIN users_addresses ua ON o.address_id = ua.id
        WHERE o.id = ? AND o.order_type = 'direct' LIMIT 1");
    $stmt->execute([$order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Helper function to fetch bidding order details (with HSN code)
function getBiddingOrderDetails($pdo, $order_id) {
    $stmt = $pdo->prepare("SELECT 
        o.id, o.order_id, o.invoice_number, b.bid_price AS price, b.bid_quantity AS quantity,
        o.order_status, o.processing_time, o.tracking_id, o.created_at,
        p.id AS product_id, p.p_name, p.hsn_code, p.p_featured_photo,
        s.seller_name, s.seller_cname, s.seller_email, s.seller_phone, s.seller_address,
        u.username, u.email, u.phone_number,
        ua.full_name, ua.phone_number as delivery_phone, ua.address, ua.city, ua.state, ua.pincode
        FROM tbl_orders o
        JOIN bidding b ON o.bid_id = b.bid_id
        JOIN tbl_product p ON b.product_id = p.id
        JOIN sellers s ON p.seller_id = s.seller_id
        JOIN users u ON b.user_id = u.id
        LEFT JOIN users_addresses ua ON u.id = ua.user_id AND ua.is_default = 1
        WHERE o.id = ? AND o.order_type = 'bid' LIMIT 1");
    $stmt->execute([$order_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
if (!$order_id) { echo "<h2>Invalid order ID.</h2>"; exit; }

$order = getDirectOrderDetails($pdo, $order_id);
$order_type = 'direct';

if (!$order) {
    $order = getBiddingOrderDetails($pdo, $order_id);
    $order_type = 'bid';
}

if (!$order) { echo "<h2>Order not found.</h2>"; exit; }

$settings = getSettings($pdo);
$subtotal = $order['price'] * $order['quantity'];
$tax_rate = 18;
$tax_amount = $subtotal * ($tax_rate / 100);
$grand_total = $subtotal + $tax_amount;

function numberToWords($number) {
    $hyphen = '-'; $conjunction = ' and '; $separator = ', '; $negative = 'negative ';
    $dictionary = [
        0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four', 5 => 'five',
        6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine', 10 => 'ten', 11 => 'eleven',
        12 => 'twelve', 13 => 'thirteen', 14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen',
        17 => 'seventeen', 18 => 'eighteen', 19 => 'nineteen', 20 => 'twenty', 30 => 'thirty',
        40 => 'forty', 50 => 'fifty', 60 => 'sixty', 70 => 'seventy', 80 => 'eighty',
        90 => 'ninety', 100 => 'hundred', 1000 => 'thousand', 100000 => 'lakh', 10000000 => 'crore'
    ];

    if (!is_numeric($number)) return false;
    if ($number < 0) return $negative . numberToWords(abs($number));
    $string = null;
    if (strpos($number, '.') !== false) list($number, $fraction) = explode('.', $number);

    switch (true) {
        case $number < 21: $string = $dictionary[$number]; break;
        case $number < 100:
            $tens = ((int) ($number / 10)) * 10; $units = $number % 10;
            $string = $dictionary[$tens]; if ($units) $string .= $hyphen . $dictionary[$units];
            break;
        case $number < 1000:
            $hundreds = (int) ($number / 100); $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) $string .= $conjunction . numberToWords($remainder);
            break;
        default:
            $baseUnit = pow(10, floor(log($number, 10) / 2) * 2);
            $numBaseUnits = (int) ($number / $baseUnit); $remainder = $number % $baseUnit;
            $string = numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= numberToWords($remainder);
            }
            break;
    }
    return ucfirst($string);
}

$invoice_number = $order['invoice_number'] ?? $order['order_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo htmlspecialchars($invoice_number); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f5f5;
            color: #000;
            padding: 20px 0;
        }
        
        .invoice-wrapper {
            width: 210mm;
            margin: 0 auto;
            background: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        
        .invoice-container {
            width: 100%;
            height: 100%;
            padding: 12mm;
            padding-bottom: 25mm;
        }
        
        /* Header */
        .invoice-header {
            width: 100%;
            margin-bottom: 15px;
            padding-bottom: 12px;
            border-bottom: 2px solid #000;
        }
        
        .invoice-header::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .logo-section {
            float: left;
            width: 50%;
        }
        
        .logo-section img { max-width: 150px; height: auto; }
        .logo-section h2 { color: #000; font-size: 24px; }
        
        .company-details {
            float: right;
            width: 50%;
            text-align: right;
        }
        .company-details h1 { color: #000; font-size: 24px; margin-bottom: 8px; font-weight: 700; }
        .company-details p { color: #000; line-height: 1.5; font-size: 12px; margin: 2px 0; }
        
        /* Info Grid */
        .invoice-info-grid {
            width: 100%;
            margin-bottom: 15px;
        }
        
        .invoice-info-grid::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .info-box {
            float: left;
            width: 48%;
            border: 1px solid #000;
            padding: 12px;
        }
        
        .info-box:first-child {
            margin-right: 4%;
        }
        
        .info-box h3 {
            color: #000;
            font-size: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 700;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        
        .info-box p {
            color: #000;
            line-height: 1.6;
            font-size: 11px;
            margin: 3px 0;
        }
        
        .info-box strong {
            display: inline-block;
            min-width: 90px;
        }
        
        /* Product Table */
        .product-section {
            margin: 15px 0;
        }
        
        .product-section h3 {
            background: #949494ff;
            color: #fff;
            padding: 8px 10px;
            font-size: 12px;
            margin-bottom: 0;
            text-transform: uppercase;
        }
        
        .product-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
        }
        
        .product-table th {
            padding: 8px;
            text-align: left;
            font-weight: 700;
            color: #000;
            border: 1px solid #000;
            background: #f9f9f9;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .product-table td {
            padding: 8px;
            border: 1px solid #000000ff;
            color: #000;
            font-size: 11px;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        /* Summary */
        .summary-section {
            width: 100%;
            margin: 15px 0;
        }
        
        .summary-section::after {
            content: "";
            display: table;
            clear: both;
        }
        
        .summary-box {
            float: right;
            width: 350px;
            max-width: 350px;
            border: 2px solid #000;
            padding: 12px;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 12px;
            border-bottom: 1px solid #ddd;
        }
        
        .summary-row:last-child { border-bottom: none; }
        
        .summary-row.total {
            border-top: 2px solid #000;
            margin-top: 8px;
            padding-top: 8px;
            font-size: 14px;
            font-weight: 700;
        }
        
        .amount-words {
            background: #f9f9f9;
            padding: 8px;
            margin-top: 8px;
            font-style: italic;
            color: #000;
            font-size: 10px;
            border: 1px solid #000;
        }
        
        /* Footer */
        .invoice-footer {
            /* position: relative; */
            /* bottom: 0; */
            /* right: 0; */
            /* margin-top: 50px; */
            padding-top: 12px;
            border-top: 2px solid #000;
        }
        
        .signature-section {
            margin-top: 20px;
            text-align: right;
        }
        
        .signature-box {
            display: inline-block;
            text-align: center;
            background-color: transparent;
        }
        
        .signature-box img {
            max-width: 100px;
            height: auto;
            margin-bottom: 5px;
            background-color: transparent;
        }
        
        .signature-line {
            border-top: 2px solid #000;
            padding-top: 5px;
            min-width: 200px;
            font-weight: 600;
            font-size: 11px;
        }
        
        .computer-generated {
            position: absolute;
            bottom: 8mm;
            left: 12mm;
            right: 12mm;
            text-align: center;
            color: #666;
            font-size: 10px;
            font-style: italic;
        }
        
        /* Print Styles */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            
            body {
                background: white;
                padding: 0;
            }
            
            .invoice-wrapper {
                width: 210mm;
                margin: 0;
                box-shadow: none;
                position: relative;
            }
            
            .invoice-container {
                padding: 12mm;
                padding-bottom: 25mm;
            }

            .company-details {
            float: right;
            width: 50%;
            text-align: right;
            }

            

            
        }
        
        @media (max-width: 768px) {
            .invoice-wrapper {
                width: 100%;
                min-height: auto;
            }
            
            .invoice-info-grid {
                grid-template-columns: 1fr;
            }
            
            .invoice-header {
                flex-direction: column;
            }
            
            .company-details {
                float: right;
                text-align: right;
                margin-top: 15px;
                max-width: 100%;
            }
            
            .summary-box {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-wrapper">
        <div class="invoice-container">
            <!-- Header -->
            <div class="invoice-header">
                <div class="logo-section">
                    <?php
                    $logo_path = '../assets/uploads/logo.png';
                    if (file_exists($logo_path)):
                    ?>
                        <img src="<?php echo $logo_path; ?>" alt="Company Logo">
                    <?php else: ?>
                        <h2>DEADSTOCK</h2>
                    <?php endif; ?>
                </div>
                <div class="company-details">
                    <h1>TAX INVOICE</h1>
                    <p><strong><?php echo !empty($settings['site_name']) ? htmlspecialchars($settings['site_name']) : 'Destock'; ?></strong></p>
                    <p><?php echo !empty($settings['footer_address']) ? nl2br(htmlspecialchars($settings['footer_address'])) : 'Imet Tooling India Pvt Ltd'; ?></p>
                    <p>Email: <?php echo !empty($settings['contact_email']) ? htmlspecialchars($settings['contact_email']) : 'support@destock.in'; ?></p>
                    <p>Phone: <?php echo !empty($settings['contact_phone']) ? htmlspecialchars($settings['contact_phone']) : '+91 xxxxxxx'; ?></p>
                    <p>GST: <?php echo !empty($settings['gstin']) ? htmlspecialchars($settings['gstin']) : 'XXXXXXXXXXXX'; ?></p>
                </div>
            </div>

            <!-- Invoice & Customer Info -->
            <div class="invoice-info-grid">
                <div class="info-box">
                    <h3>Invoice Details</h3>
                    <p><strong>Invoice No:</strong> <?php echo htmlspecialchars($invoice_number); ?></p>
                    <p><strong>Invoice Date:</strong> <?php echo !empty($order['processing_time']) ? date('d M, Y', strtotime($order['processing_time'])) : date('d M, Y'); ?></p>
                    <p><strong>Order ID:</strong> <?php echo htmlspecialchars($order['order_id']); ?></p>
                    <p><strong>Order Type:</strong> <?php echo ucfirst($order_type); ?></p>
                    <?php if (!empty($order['tracking_id'])): ?>
                    <p><strong>Tracking ID:</strong> <?php echo htmlspecialchars($order['tracking_id']); ?></p>
                    <?php endif; ?>
                </div>

                <div class="info-box">
                    <h3>Bill To / Ship To</h3>
                    <p><strong>Name:</strong> <?php echo !empty($order['full_name']) ? htmlspecialchars($order['full_name']) : htmlspecialchars($order['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo !empty($order['delivery_phone']) ? htmlspecialchars($order['delivery_phone']) : htmlspecialchars($order['phone_number']); ?></p>
                    <?php if (!empty($order['address'])): ?>
                    <p><strong>Address:</strong><br>
                        <?php echo htmlspecialchars($order['address']); ?><br>
                        <?php echo htmlspecialchars($order['city']) . ', ' . htmlspecialchars($order['state']) . ' - ' . htmlspecialchars($order['pincode']); ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Product Details -->
            <div class="product-section">
                <h3>Product Details</h3>
                <table class="product-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th class="text-center">Quantity</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-center">HSN Code</th>
                            <th class="text-right">Tax (<?php echo $tax_rate; ?>%)</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo htmlspecialchars($order['p_name']); ?></td>
                            <td class="text-center"><?php echo $order['quantity']; ?></td>
                            <td class="text-right">₹<?php echo number_format($order['price'], 2); ?></td>
                            <td class="text-center"><?php echo htmlspecialchars($order['hsn_code'] ?? '1234'); ?></td>
                            <td class="text-right">₹<?php echo number_format($tax_amount, 2); ?></td>
                            <td class="text-right"><strong>₹<?php echo number_format($grand_total, 2); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Summary -->
            <div class="summary-section">
                <div class="summary-box">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>₹<?php echo number_format($subtotal, 2); ?></span>
                    </div>
                    <div class="summary-row">
                        <span>Tax (GST <?php echo $tax_rate; ?>%):</span>
                        <span>₹<?php echo number_format($tax_amount, 2); ?></span>
                    </div>
                    <div class="summary-row total">
                        <span>Grand Total:</span>
                        <span>₹<?php echo number_format($grand_total, 2); ?></span>
                    </div>
                    <div class="amount-words">
                        <strong>Amount in Words:</strong><br>
                        <?php echo ucfirst(numberToWords(floor($grand_total))); ?> Rupees Only
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="invoice-footer">
                <div class="signature-section">
                    <div class="signature-box">
                        <?php
                        $signature_path = '../assets/uploads/signature.png';
                        if (file_exists($signature_path)):
                        ?>
                            <img src="<?php echo $signature_path; ?>" alt="Signature">
                        <?php endif; ?>
                        <div class="signature-line">
                            Authorized Signature
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Computer Generated Text - Absolute positioned at bottom -->
        <div class="computer-generated">
            This is a computer-generated invoice and does not require a physical signature.
        </div>
    </div>
</body>
</html>
