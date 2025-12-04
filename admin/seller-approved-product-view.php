<?php
include("../db_connection.php");
// Check if export_csv button is clicked - must be before any output
if (isset($_POST['export_csv'])) {
    $seller_id = isset($_POST['seller_id']) ? intval($_POST['seller_id']) : 0;
    // Build query with filters
    $query = "SELECT
                t1.id,
                t1.p_name,
                t1.p_old_price,
                t1.p_current_price,
                t1.p_qty,
                t1.p_featured_photo,
                t1.p_is_featured,
                t1.p_is_approve,
                t1.product_catalogue,
                t1.product_brand,
                t1.p_date,
                t1.seller_id,
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
            WHERE t1.seller_id = ? AND t1.p_is_approve = 1";

    $params = array($seller_id);

    // Apply date filter
    if (!empty($_POST['from_date']) && !empty($_POST['to_date'])) {
        $query .= " AND DATE(t1.p_date) BETWEEN ? AND ?";
        $params[] = $_POST['from_date'];
        $params[] = $_POST['to_date'];
    } elseif (!empty($_POST['from_date'])) {
        $query .= " AND DATE(t1.p_date) >= ?";
        $params[] = $_POST['from_date'];
    } elseif (!empty($_POST['to_date'])) {
        $query .= " AND DATE(t1.p_date) <= ?";
        $params[] = $_POST['to_date'];
    }

    $query .= " ORDER BY t1.id DESC";

    try {
        $statement = $pdo->prepare($query);
        $statement->execute($params);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Set headers for CSV download after successful query
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="approved_products.csv"');

        // Output CSV data
        $output = fopen('php://output', 'w');

        // Write headers
        fputcsv($output, array('#', 'Product Brand', 'Product Name', 'Old Price', 'Current Price', 'Quantity', 'Upload Date'));

        // Write data
        $i = 0;
        foreach ($result as $row) {
            $i++;
            $upload_date = !empty($row['p_date']) && $row['p_date'] != '0000-00-00' ? '="' . date('d/m/Y', strtotime($row['p_date'])) . '"' : 'N/A';
            fputcsv($output, array(
                $i,
                $row['brand_name'],
                $row['p_name'],
                $row['p_old_price'],
                $row['p_current_price'],
                $row['p_qty'],
                $upload_date
            ));
        }

        fclose($output);
        exit();
    } catch (Exception $e) {
        // Log the error
        error_log("CSV Export Error: " . $e->getMessage());
        // Redirect back with error message
        header('Location: ' . $_SERVER['PHP_SELF'] . '?seller_id=' . $seller_id . '&error=export_failed');
        exit();
    }
}
?>

<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>View Approved Products</h1>
	</div>

</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="filter-container" style="margin-left :-12px; display: flex; justify-content: space-between; align-items: center;">
				<div class="date-filter-group">
					<label class="date-filter-label">Filter by date range:</label>
					<input type="date" class="form-control input-sm" id="fromDate" style="display: inline-block; width: auto; margin: 0 10px;">
					<label>to</label>
					<input type="date" class="form-control input-sm" id="toDate" style="display: inline-block; width: auto; margin: 0 10px;">
					<button id="clearDates" class="btn btn-default btn-sm">Clear Dates</button>
				</div>

				<div class="export-group">
					<form method="POST" action="" id="exportForm">
						<input type="hidden" name="seller_id" value="<?php echo isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0; ?>">
						<input type="hidden" name="from_date" id="hiddenFromDate">
						<input type="hidden" name="to_date" id="hiddenToDate">
						<button type="submit" name="export_csv" class="btn btn-primary btn-xs">Export to CSV</button>
					</form>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-hover table-striped">
					<thead class="thead-dark">
							<tr>
								<th width="10">#</th>
								<th>Photo</th>
								<th>Product Brand</th>
								<th width="160">Product Name</th>
								<th width="60">Old Price</th>
								<th width="60">(C) Price</th>
								<th width="60">Quantity</th>
								<th>Featured?</th>
								<!--<th>Active?</th>-->
								<th>Category</th>
								<th width="100">Upload Date</th>
								<th width="80">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$seller_id = isset($_GET['seller_id']) ? intval($_GET['seller_id']) : 0; // Get seller_id from URL
							
              $statement = $pdo->prepare("SELECT
                      t1.id,
                      t1.p_name,
                      t1.p_old_price,
                      t1.p_current_price,
                      t1.p_qty,
                      t1.p_featured_photo,
                      t1.p_is_featured,
                      t1.p_is_approve,
                      -- t1.p_is_active,
					  t1.product_brand,
                      t1.p_date,
                      t1.ecat_id,
                      t2.ecat_id,
                      t2.ecat_name,
                      t3.mcat_id,
                      t3.mcat_name,
                      t4.tcat_id,
                      t4.tcat_name,
					  t5.brand_name
                  FROM tbl_product t1
                  LEFT JOIN tbl_end_category t2 ON t1.ecat_id = t2.ecat_id
				LEFT JOIN tbl_mid_category t3 ON t1.mcat_id = t3.mcat_id
				LEFT JOIN tbl_top_category t4 ON t1.tcat_id = t4.tcat_id
				LEFT JOIN tbl_brands t5 ON t1.product_brand=t5.brand_id
                  WHERE t1.seller_id = :seller_id AND t1.p_is_approve = 1  -- Filter by seller_id and approved products
                  ORDER BY t1.id DESC");
							$statement->bindParam(':seller_id', $seller_id, PDO::PARAM_INT); // Bind seller_id parameter
							$statement->execute();
							$result = $statement->fetchAll(PDO::FETCH_ASSOC);
							foreach ($result as $row) {
								$i++;
								?>
								<tr data-date="<?php echo date('Y-m-d', strtotime($row['p_date'])); ?>">
									<td><?php echo $i; ?></td>
									<td style="width:82px;"><img src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>" alt="<?php echo $row['p_name']; ?>" style="width:80px;"></td>
									<td><?php echo $row['brand_name']; ?></td>
									<td><?php echo $row['p_name']; ?></td>
									<td>₹<?php echo $row['p_old_price']; ?></td>
									<td>₹<?php echo $row['p_current_price']; ?></td>
									<td><?php echo $row['p_qty']; ?></td>
									<td>
										<?php if($row['p_is_featured'] == 1) {echo '<span class="badge badge-success" style="background-color:green;">Yes</span>';} else {echo '<span class="badge badge-success" style="background-color:red;">No</span>';} ?>
									</td>
									<!--<td>
										<?php if($row['p_is_active'] == 1) {echo '<span class="badge badge-success" style="background-color:green;">Yes</span>';} else {echo '<span class="badge badge-danger" style="background-color:red;">No</span>';} ?>
									</td>-->
									<td><?php echo $row['tcat_name']; ?><br><?php echo $row['mcat_name']; ?><br><?php echo $row['ecat_name']; ?></td>
									<td><?php echo (!empty($row['p_date']) && $row['p_date'] != '0000-00-00') ? date('d-m-Y', strtotime($row['p_date'])) : 'N/A'; ?></td>
									<td><?php echo $row['p_is_approve'] == 1 ? '<span class="badge badge-success" style="background-color:green;">Approved</span>' : '<span class="badge badge-danger" style="background-color:red;">Rejected</span>'; ?></td>
                  <!-- <td>

                  <button class="btn btn-success btn-xs" disabled>Approved</button>
                      <a href="product-delete.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-xs" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                  </td> -->
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromDateInput = document.getElementById('fromDate');
    const toDateInput = document.getElementById('toDate');
    const clearDatesBtn = document.getElementById('clearDates');
    const hiddenFromDate = document.getElementById('hiddenFromDate');
    const hiddenToDate = document.getElementById('hiddenToDate');
    const tableRows = document.querySelectorAll('#example1 tbody tr');

    function filterTable() {
        const fromDate = fromDateInput.value;
        const toDate = toDateInput.value;

        tableRows.forEach(row => {
            const rowDate = row.getAttribute('data-date');
            let showRow = true;

            if (fromDate && rowDate < fromDate) {
                showRow = false;
            }
            if (toDate && rowDate > toDate) {
                showRow = false;
            }

            row.style.display = showRow ? '' : 'none';
        });

        // Update hidden inputs for export
        hiddenFromDate.value = fromDate;
        hiddenToDate.value = toDate;
    }

    function clearDates() {
        fromDateInput.value = '';
        toDateInput.value = '';
        filterTable();
    }

    fromDateInput.addEventListener('change', filterTable);
    toDateInput.addEventListener('change', filterTable);
    clearDatesBtn.addEventListener('click', clearDates);
});
</script>

<?php require_once('footer.php'); ?>
