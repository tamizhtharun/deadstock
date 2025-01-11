<?php require_once('header.php'); ?>


<section class="content-header">
	<div class="content-header-left">
		<h1>Bidding</h1>
	</div>
	
</section>

<section class="content">

  <div class="row">
    <div class="col-md-12">


      <div class="box box-info">
        
        <div class="box-body table-responsive">
          <table id="example1" class="table table-bordered table-hover table-striped">
			<thead>`
			    <tr>
			        <th>#</th>
			        <!-- <th>Bid ID</th> -->
                    <th>Product Photo</th>
			        <th>Product Name</th>
			        <th>Seller Details</th>
			        <th>No. of Bids</th>
			        <!-- <th>Bid Status</th> -->
			        <th>View</th>
			    </tr>
			</thead>
            <tbody>
			<?php
$i = 0;
$no_of_bids = 0;
$statement = $pdo->prepare("
    SELECT 
		b.bid_id,
        p.id AS product_id,
        p.p_name,
        p.p_featured_photo,
        p.seller_id,
        s.seller_name,
		s.seller_cname,
        (SELECT COUNT(*) FROM bidding WHERE product_id = p.id) AS no_of_bids,
        MAX(b.bid_status) AS bid_status
    FROM 
        tbl_product p
    JOIN 
        sellers s ON p.seller_id = s.seller_id
    LEFT JOIN 
        bidding b ON p.id = b.product_id
    GROUP BY 
        p.id
");
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
<?php foreach ($result as $row): ?>
        <tr>
            <td><?php echo ++$i; ?></td>
            <td><img src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>" alt="Product Photo" style="width:70px;"></td>
            <td><?php echo $row['p_name']; ?></td>
            <td><?php echo $row['seller_name'];?> ,<br><?php echo$row['seller_cname']; ?></td>
            <td><?php echo $row['no_of_bids']; ?></td>
            <!-- <td><?php echo getBidStatusLabel ($row['bid_status']); ?></td> -->
            <td><a href="view_bid.php?id=<?php echo $row['bid_id']; ?>">View all Bids</a></td>
        </tr>
    <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
  

</section>




<?php require_once('footer.php'); ?>