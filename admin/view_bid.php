<?php require_once('header.php'); ?>

<?php
// Get product ID from URL parameter
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Validate product ID
// if (!$product_id) {
//     die('Product ID is required');
// }

$i = 0;
$no_of_bids = 0;
$statement = $pdo->prepare("
    SELECT 
        b.bid_id,
        b.bid_time,
        b.bid_price,
        b.bid_quantity,
        b.bid_status,
        b.refund_id,
        b.refund_status,
        b.refund_amount,
        b.refund_date,
        b.refund_error,
        b.payment_id,
        b.order_id,
        p.p_name,
        p.p_featured_photo,
        p.seller_id,
        s.seller_name,
        s.seller_cname,
        u.username,
        u.phone_number,
        u.email
    FROM 
        tbl_product p
    JOIN 
        sellers s ON p.seller_id = s.seller_id
    JOIN 
        bidding b ON p.id = b.product_id
    JOIN
        users u ON b.user_id = u.id
    WHERE
        p.id = :product_id
");

$statement->bindParam(':product_id', $product_id, PDO::PARAM_INT);
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
function getBidStatusLabel($status) {
    $statusLabels = [
        0 => 'Submitted',
        1 => 'Sent to Seller',
        2 => 'Accepted by Seller',
        3 => 'Rejected by Seller'
    ];
    return $statusLabels[$status] ?? 'Unknown Status';
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Bidding Details</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <!-- Product Details Section -->
            <div class="box box-info">
                <div class="box-body">
                    <?php if (!empty($result)): ?>
                        <?php $product = $result[0]; ?>
                        <div class="product-details" style="display: flex; align-items: center; gap: 20px;">
                            <!-- Product Image -->
                            <div>
                                <img src="../assets/uploads/product-photos/<?php echo $product['p_featured_photo']; ?>" 
                                     alt="<?php echo $product['p_name']; ?>" 
                                     style="max-width: 100px; height: auto;">
                            </div>
                            <!-- Product and Seller Details -->
                            <div>
                                <h2><?php echo $product['p_name']; ?></h2>
                                <p><strong>Seller:</strong> <?php echo $product['seller_name']; ?> 
                                   (<?php echo $product['seller_cname']; ?>)</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>No bids found for this product.</p>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($result)): ?>
                <div class="box box-info">
                    <div class="box-body table-responsive">
                        <table id="example1" class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th width='10px'>#</th>
                                    <th>Buyer Name</th>
                                    <th>Buyer Contact</th>
                                    <th>Bid Price</th>
                                    <th>Quantity</th>
                                    <th>Payment ID</th>
                                    <th>Order ID</th>
                                    <th>Bid Status</th>
                                    <th>Refund Status</th>
                                    <th>Refund ID</th>
                                    <th>Refund Amount</th>
                                    <th>Refund Date</th>
                                    <th>Refund Error</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($result as $row): ?>
                                    <tr>
                                        <td><?php echo ++$i; ?></td>
                                        <td><?php echo $row['username']; ?></td>
                                        <td><?php echo $row['phone_number']; ?><br>
                                            <?php echo $row['email']; ?></td>
                                        <td>₹<?php echo $row['bid_price']; ?></td>
                                        <td><?php echo $row['bid_quantity']; ?></td>
                                        <td><?php echo $row['payment_id']; ?></td>
                                        <td><?php echo $row['order_id']; ?></td>
                                        <td><?php echo getBidStatusLabel($row['bid_status']); ?></td>
                                        <td><?php echo $row['refund_status']; ?></td>
                                        <td><?php echo $row['refund_id']; ?></td>
                                        <td><?php echo $row['refund_amount']; ?></td>
                                        <td><?php echo $row['refund_date']; ?></td>
                                        <td><?php echo $row['refund_error']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>