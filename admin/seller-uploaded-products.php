<?php require_once('header.php'); ?>

<section class="content-header">
    <div class="content-header-left">
        <h1>View Uploaded Products</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th width="5px">#</th>
                                <th width="18%">Seller Name</th>
                                <th>Seller Email</th>
                                <th>Seller Address</th>
                                <th>Product Count</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $statement = $pdo->prepare("SELECT s.seller_id, s.seller_name, s.seller_email, s.seller_address, COUNT(p.seller_id) AS product_count
                                                         FROM sellers s
                                                         LEFT JOIN tbl_product p ON s.seller_id = p.seller_id
                                                         GROUP BY s.seller_id
                                                         ORDER BY s.seller_id DESC");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                $i++;
                                ?>
                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo htmlspecialchars($row['seller_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['seller_email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['seller_address']); ?></td>
                                    <td><?php echo $row['product_count']; ?></td>
                                    <td>
                                        <a href="seller-products.php?seller_id=<?php echo $row['seller_id']; ?>">View Products</a>
                                    </td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once('footer.php'); ?>