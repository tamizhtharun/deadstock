<?php require_once('header.php'); ?>

<?php
// Get seller_id from the URL
$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;

// Check if approve_all button is clicked
if (isset($_POST['approve_all'])) {
    try {
        // Prepare the SQL statement to update all products to approved
        $stmt = $pdo->prepare("UPDATE tbl_product SET p_is_approve = 1 WHERE seller_id = :seller_id");
        $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
        $stmt->execute();
        echo '<script>
            document.getElementById("message").style.backgroundColor = "green";
            document.getElementById("message").innerHTML = "Success! All products have been Approved.";
            document.getElementById("message").style.display = "block";
            setTimeout(function(){ document.getElementById("message").style.display = "none"; }, 1500);
        </script>';
    } catch (PDOException $e) {
        echo '<script>
            document.getElementById("message").style.backgroundColor = "red";
            document.getElementById("message").innerHTML = "Error: ' . $e->getMessage() . '";
            document.getElementById("message").style.display = "block";
            setTimeout(function(){ document.getElementById("message").style.display = "none"; }, 2000);
        </script>';
    }
}

// Check if reject_all button is clicked
if (isset($_POST['reject_all'])) {
    try {
        // Prepare the SQL statement to update all products to rejected
        $stmt = $pdo->prepare("UPDATE tbl_product SET p_is_approve = 0 WHERE seller_id = :seller_id");
        $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
        $stmt->execute();
        echo '<script>
            document.getElementById("message").style.backgroundColor = "Red";
            document.getElementById("message").innerHTML = "Success! All products have been Rejected.";
            document.getElementById("message").style.display = "block";
            setTimeout(function(){ document.getElementById("message").style.display = "none"; }, 2000);
        </script>';
    } catch (PDOException $e) {
        echo '<script>
            document.getElementById("message").style.backgroundColor = "red";
            document.getElementById("message").innerHTML = "Error: ' . $e->getMessage() . '";
            document.getElementById("message").style.display = "block";
            setTimeout(function(){ document.getElementById("message").style.display = "none"; }, 2000);
        </script>';
    }
}
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>View Products</h1>

    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header">
                    <h3 class="box-title"></h3>
                    <div class="box-tools pull-right">
                        <form method="POST" action="">
                            <input type="hidden" name="seller_id" value="<?php echo $seller_id; ?>">
                            <button type="submit" name="approve_all" class="btn btn-success btn-xs">Approve All</button>
                            <button type="submit" name="reject_all" class="btn btn-danger btn-xs">Reject All</button>
                        </form>
                    </div>
                </div>
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th width="10">#</th>
                                <th>Photo</th>
                                <th>Product Brand</th>
                                <th >Product Name</th>
                                <th >Old Price</th>
                                <th >(C) Price</th>
                                <th >Quantity</th>
                                <th>Featured?</th>
                                <th>Active?</th>
                                <th>Category</th>
                                <th>Product Catalogue</th>
                                <th >Approval Status</th>
                                <th >Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0;
                        $i = 0;

                        $statement = $pdo->prepare("SELECT
                                                        t1.id,
                                                        t1.p_name,
                                                        t1.p_old_price,
                                                        t1.p_current_price,
                                                        t1.p_qty,
                                                        t1.p_featured_photo,
                                                        t1.p_is_featured,
                                                        t1.p_is_active,
                                                        t1.p_is_approve,
                                                        t1.product_catalogue,
                                                        t1.product_brand,
                                                        t2.ecat_id,
                                                        t2.ecat_name,
                                                        t3.mcat_id,
                                                        t3.mcat_name,
                                                        t4.tcat_id,
                                                        t4.tcat_name,
                                                        t5.brand_id,
                                                        t5.brand_name
                                                    FROM tbl_product t1
                                                    LEFT JOIN tbl_end_category t2 ON t1.ecat_id = t2.ecat_id
                                                    LEFT JOIN tbl_mid_category t3 ON t1.mcat_id = t3.mcat_id
                                                    LEFT JOIN tbl_top_category t4 ON t1.tcat_id = t4.tcat_id
                                                    LEFT JOIN tbl_brands t5 ON t1.product_brand=t5.brand_id
                                                    WHERE t1.seller_id = :seller_id
                                                    ORDER BY t1.id DESC");
                        $statement->bindParam(':seller_id', $seller_id, PDO::PARAM_INT);
                        $statement->execute();
                        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($result as $row) {
                            $i++;
                            ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td style="width:82px;"><img src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>" alt="<?php echo $row['p_name']; ?>" style="width:80px;"></td>
                                <td><?php echo $row['brand_name']; ?></td>
                                <td><?php echo $row['p_name']; ?></td>
                                <td>₹<?php echo $row['p_old_price']; ?></td>
                                <td>₹<?php echo $row['p_current_price']; ?></td>
                                <td><?php echo $row['p_qty']; ?></td>
                                <!-- Update the Featured column -->
                                <td>
                                    <select class="form-control" style="width:auto;" onchange="updateFeatured(<?php echo $row['id']; ?>, this.value)">
                                        <option value="0" <?php echo $row['p_is_featured'] == 0 ? 'selected' : ''; ?>>No</option>
                                        <option value="1" <?php echo $row['p_is_featured'] == 1 ? 'selected' : ''; ?>>Yes</option>
                                    </select>
                                </td>
                                
                                <!-- Update the Active column -->
                                <td>
                                <select class="form-control" style="width:auto;" onchange="updateActive(<?php echo $row['id']; ?>, this.value)">
                                    <option value="0" <?php echo $row['p_is_active'] == 0 ? 'selected' : ''; ?>>No</option>
                                    <option value="1" <?php echo $row['p_is_active'] == 1 ? 'selected' : ''; ?>>Yes</option>
                                </select>
                                </td>

                                <td><?php echo $row['tcat_name']; ?><br><?php echo $row['mcat_name']; ?><br><?php echo $row['ecat_name']; ?></td>
                                <td><a href="../assets/uploads/product-catalogues/<?php echo $row['product_catalogue']?>">View catalogue</a> </td>
                                <td><?php echo $row['p_is_approve'] == 1 ? '<span class="badge badge-success" style="background-color:green;">Approved</span>' : '<span class="badge badge-danger" style="background-color:red;">Rejected</span>'; ?></td>
                                <td>
                                    <?php if ($row['p_is_approve'] == 1) { ?>
                                        <a href="seller-product-approve-status.php?id=<?php echo $row['id']; ?>&status=0&seller_id=<?php echo $seller_id; ?>" class="btn btn-warning btn-xs">Reject</a>
                                    <?php } else { ?>
                                        <a href="seller-product-approve-status.php?id=<?php echo $row['id']; ?>&status=1&seller_id=<?php echo $seller_id; ?>" class="btn btn-success btn-xs">Approve</a>
                                    <?php } ?>
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
<script>
function updateFeatured(productId, value) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "update_product_status.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            console.log(xhr.responseText); // Optional: handle response
        }
    };
    xhr.send("id=" + productId + "&p_is_featured=" + value);
}

function updateActive(productId, value) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "update_product_status.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            console.log(xhr.responseText); // Optional: handle response
        }
    };
    xhr.send("id=" + productId + "&p_is_active=" + value);
}
</script>


<?php require_once('footer.php'); ?>
