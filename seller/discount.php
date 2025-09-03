<?php require_once('header.php'); ?>
<!-- <?php
echo "<pre>";
print_r($_SESSION['seller_session']); // Display all session variables
echo "</pre>";
?> -->
<?php
$statement = $pdo->prepare("SELECT seller_status FROM sellers WHERE seller_id = ?");
$statement->execute([$seller_id]);
$seller_status = $statement->fetchColumn();
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Product Discount</h1>
        <!-- <?php echo $seller_status; ?> -->
    </div>
    <div class="content-header-right">
        <?php if ($seller_status == 1) { ?>
            <button class="btn btn-primary btn-sm" type="button" onclick="toggleDiscountForm()">
                Overall Discount
            </button>

            <div id="overallDiscountForm" class="mt-3 p-3 border rounded bg-light" style="display:none; max-width:350px;">
                <form action="overall-discount.php" method="post">
                    <div class="form-group">
                        <label for="discountInput"><b>Enter Discount Percentage (%)</b></label>
                        <input type="number" name="discount" id="discountInput" class="form-control mt-2" min="0" max="100"
                            required placeholder="e.g. 20">
                    </div>
                    <button type="submit" class="btn btn-success btn-sm mt-2">Update</button>
                    <button type="button" class="btn btn-secondary btn-sm mt-2"
                        onclick="toggleDiscountForm()">Cancel</button>
                </form>
            </div>
        <?php } else { ?>
            <a href="profile-edit.php" class="btn btn-primary btn-sm disabled">Add Product</a>
        <?php } ?>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-body table-responsive">
                    <?php if ($seller_status == 0) { ?>
                        <div class="alert no-details">
                            <i class="fa fa-exclamation-triangle"></i>
                            <h2>Profile Incomplete</h2>
                            <h5>Complete your profile to add products</h5>
                            <a href="profile-edit.php" class="btn btn-primary btn-sm">Complete Your Profile</a>
                        </div>
                    <?php } else { ?>
                        <table id="example1" class="table table-bordered table-hover table-striped">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Photo</th>
                                    <th width="140">Product Brand</th>
                                    <th width="140">Product Name</th>
                                    <th width="90">Old Price</th>
                                    <th width="90">Discounted Price</th>
                                    <th width="90">Quantity</th>
                                    <th width="200">Approval Status</th>
                                    <th width="100">Current Discount</th>
                                    <th width="160">Update Discount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php

                                $seller_id = $_SESSION['seller_session']['seller_id']; // Get the seller ID from the session
                                $i = 0;
                                $statement = $pdo->prepare("SELECT
																						t1.id,
																						t1.p_name,
																						t1.p_old_price,
                                                                                        t1.p_current_price,
																						t1.p_discount_price,
																						t1.p_qty,
																						t1.p_featured_photo,
																						t1.p_is_featured,
																						t1.p_is_approve,
                                                                                        t1.is_discount,
																						t1.product_catalogue,
																						t1.product_brand,
																						t1.ecat_id,
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
                                                                                AND t1.p_is_approve = 1   -- Filter by seller_id
																				ORDER BY t1.id DESC");
                                $statement->bindParam(':seller_id', $seller_id, PDO::PARAM_INT); // Bind the seller_id parameter
                                $statement->execute();
                                $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                                foreach ($result as $row) {
                                    $discount = 0;
                                    if ($row['p_old_price'] > 0) {
                                        $discount = round((($row['p_old_price'] - $row['p_discount_price']) / $row['p_old_price']) * 100);
                                    }
                                    $i++;
                                    ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td style="width:82px;"><img
                                                src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>"
                                                alt="<?php echo $row['p_name']; ?>" style="width:80px;"></td>
                                        <td><?php echo $row['brand_name']; ?></td>
                                        <td><?php echo $row['p_name']; ?></td>
                                        <td>₹<?php echo $row['p_old_price']; ?></td>
                                        <td>₹<?php echo $row['p_discount_price']; ?></td>
                                        <td><?php echo $row['p_qty']; ?></td>
                                        <td>
                                            <?php if ($row['is_discount'] == 0) {
                                                echo '<span class="badge badge-success" style="background-color:green;">Approved</span>';
                                            } else {
                                                echo '<span class="badge badge-danger"  style="background-color:6689C6;">Waiting for Approval</span>';
                                            } ?>
                                        </td>
                                        <td><b><?php echo $discount; ?>%</b></td>
                                        <td>
                                            <form action="update-discount.php" method="post"
                                                style="margin-top:5px; display:flex; gap:5px;">
                                                <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                                                <input type="number" name="discount" value="<?php echo $discount; ?>" min="0"
                                                    max="100" class="form-control" style="width:70px;">
                                                <button type="submit" class="btn btn-success btn-xs">Update</button>
                                            </form>
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
<?php } ?>


<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg rounded-lg">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-4">
                    <i class="fa fa-exclamation-circle text-warning" style="font-size: 72px;"></i>
                </div>
                <h4>Are you sure?</h4>
                <p class="text-muted">This product will be permanently deleted and cannot be recovered.</p>
            </div>
            <div class="modal-footer border-top-0 justify-content-center">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <a href="#" class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
    </div>
</div>


<style>
    .alert {
        padding: 10px;
        width: 100%;
        text-align: center;
    }

    .alert i {
        font-size: 70px;
    }
</style>
<script>
    function toggleDiscountForm() {
        var form = document.getElementById("overallDiscountForm");
        if (form.style.display === "none") {
            form.style.display = "block";
        } else {
            form.style.display = "none";
        }
    }
</script>
<?php require_once('footer.php'); ?>