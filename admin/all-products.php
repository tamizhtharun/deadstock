<?php
include("../db_connection.php");
// Check if export_csv button is clicked - must be before any output
if (isset($_POST['export_csv'])) {
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
            WHERE 1=1";

    $params = array();

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

    // Apply status filter
    if (!empty($_POST['status_filter'])) {
        if ($_POST['status_filter'] == 'approved') {
            $query .= " AND t1.p_is_approve = 1";
        } elseif ($_POST['status_filter'] == 'rejected') {
            $query .= " AND t1.p_is_approve = 0";
        }
    }

    $query .= " ORDER BY t1.id DESC";

    try {
        $statement = $pdo->prepare($query);
        $statement->execute($params);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Set headers for CSV download after successful query
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="products.csv"');

        // Output CSV data
        $output = fopen('php://output', 'w');

        // Write headers
        fputcsv($output, array('#', 'Product Brand', 'Product Name', 'Old Price', 'Current Price', 'Quantity', 'Approval Status', 'Upload Date'));

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
                $row['p_is_approve'] ? 'Approved' : 'Rejected',
                $upload_date
            ));
        }

        fclose($output);
        exit();
    } catch (Exception $e) {
        // Log the error
        error_log("CSV Export Error: " . $e->getMessage());
        // Redirect back with error message
        header('Location: ' . $_SERVER['PHP_SELF'] . '?error=export_failed');
        exit();
    }
}
?>

<?php require_once('header.php'); ?>

<?php
// Check if approve_all button is clicked
if (isset($_POST['approve_all'])) {
    try {
        $stmt = $pdo->prepare("UPDATE tbl_product SET p_is_approve = 1");
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
        $stmt = $pdo->prepare("UPDATE tbl_product SET p_is_approve = 0, p_is_featured = 0");
        $stmt->execute();
        echo '<script>
            document.getElementById("message").style.backgroundColor = "red";
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
            <div class="filter-container" style="margin-left :-12px; display: flex; justify-content: space-between; align-items: center;">
                <div class="date-filter-group">
                    <label class="date-filter-label">Filter by date range:</label>
                    <input type="date" class="form-control input-sm" id="fromDate" style="display: inline-block; width: auto; margin: 0 10px;">
                    <label>to</label>
                    <input type="date" class="form-control input-sm" id="toDate" style="display: inline-block; width: auto; margin: 0 10px;">
                    <button id="clearDates" class="btn btn-default btn-sm">Clear Dates</button>
                </div>

                <div class="status-filter-group">
                    <label>Filter by status:</label>
                    <select id="statusFilter" class="form-control input-sm" style="display: inline-block; width: auto; margin-left: 10px;padding-top:0;">
                        <option value="">All Products</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header">
                    <h3 class="box-title"></h3>
                    <div class="box-tools pull-right">
                        <form method="POST" action="" id="actionForm">
                            <input type="hidden" name="seller_id" value="<?php echo $seller_id; ?>">
                            <input type="hidden" name="from_date" id="hiddenFromDate">
                            <input type="hidden" name="to_date" id="hiddenToDate">
                            <input type="hidden" name="status_filter" id="hiddenStatusFilter">
                            <button type="submit" name="approve_all" class="btn btn-success btn-xs">Approve All</button>
                            <button type="submit" name="reject_all" class="btn btn-danger btn-xs">Reject All</button>
                            <button type="submit" name="export_csv" class="btn btn-primary btn-xs" id="exportCsvBtn">Export to CSV</button>
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
                                <th>Product Name</th>
                                <th>Old Price</th>
                                <th>(C) Price</th>
                                <th>Quantity</th>
                                <th>Featured?</th>
                                <th>Category</th>
                                <th>Product Catalogue</th>
                                <th>Seller ID</th>
                                <th>Approval Status</th>
                                <th width="100">Upload Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;

                            // Updated query to fetch all products, no seller filter
                            $statement = $pdo->prepare("SELECT
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
                                                    ORDER BY t1.id DESC");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                $i++;
                            ?>
                                <tr data-date="<?php echo date('Y-m-d', strtotime($row['p_date'])); ?>" data-status="<?php echo $row['p_is_approve'] == 1 ? 'approved' : 'rejected'; ?>">
                                    <td><?php echo $i; ?></td>
                                    <td style="width:82px;"><img src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>" alt="<?php echo $row['p_name']; ?>" style="width:80px;"></td>
                                    <td><?php echo $row['brand_name']; ?></td>
                                    <td><?php echo $row['p_name']; ?></td>
                                    <td>₹<?php echo $row['p_old_price']; ?></td>
                                    <td>₹<?php echo $row['p_current_price']; ?></td>
                                    <td><?php echo $row['p_qty']; ?></td>
                                    <!-- Update the Featured column -->
                                    <td>
                                        <select class="form-control" id="featured-select-<?php echo $row['id']; ?>" style="width:auto;" onchange="updateFeatured(<?php echo $row['id']; ?>, this.value)" <?php echo $row['p_is_approve'] == 0 ? 'disabled' : ''; ?>>
                                            <option value="0" <?php echo $row['p_is_featured'] == 0 ? 'selected' : ''; ?>>No</option>
                                            <option value="1" <?php echo $row['p_is_featured'] == 1 ? 'selected' : ''; ?>>Yes</option>
                                        </select>
                                    </td>
                                    <td><?php echo $row['tcat_name']; ?><br><?php echo $row['mcat_name']; ?><br><?php echo $row['ecat_name']; ?></td>
                                    <td><a href="../assets/uploads/product-catalogues/<?php echo $row['product_catalogue'] ?>">View catalogue</a> </td>
                                    <td><?php echo $row['seller_id']; ?>
                                        <div>
                                            <a href="javascript:void(0);" onclick="openSellerModal(<?php echo $row['seller_id']; ?>)">View Seller Details</a>
                                        </div>
                                    </td>
                                    <td><span id="status-badge-<?php echo $row['id']; ?>" class="badge <?php echo $row['p_is_approve'] == 1 ? 'badge-success' : 'badge-danger'; ?>" style="background-color:<?php echo $row['p_is_approve'] == 1 ? 'green' : 'red'; ?>;"><?php echo $row['p_is_approve'] == 1 ? 'Approved' : 'Rejected'; ?></span></td>
                                    <td><?php echo (!empty($row['p_date']) && $row['p_date'] != '0000-00-00') ? date('d-m-Y', strtotime($row['p_date'])) : 'N/A'; ?></td>
                                    <td>
                                        <?php if ($row['p_is_approve'] == 1) { ?>
                                            <button onclick="toggleApproval(<?php echo $row['id']; ?>, 1, this)" class="btn btn-warning btn-xs">Reject</button>
                                        <?php } else { ?>
                                            <button onclick="toggleApproval(<?php echo $row['id']; ?>, 0, this)" class="btn btn-success btn-xs">Approve</button>
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

<div id="sellerModal" class="seller-modal">
    <div class="seller-modal-content">
        <div class="seller-modal-header">
            <h2>Seller Information Dashboard</h2>
            <span class="seller-close">&times;</span>
        </div>

        <div class="seller-tabs">
            <button class="seller-tab-button active" data-tab="profile">Profile</button>
            <button class="seller-tab-button" data-tab="products">Products</button>
            <button class="seller-tab-button" data-tab="bidding">Bidding</button>
            <button class="seller-tab-button" data-tab="orders">Orders</button>
            <button class="seller-tab-button" data-tab="certification">Certification</button>
        </div>

        <div class="seller-tab-content">
            <div id="profile" class="seller-tab-pane active">
                <div class="seller-info-grid">
                    <!-- Profile content will be dynamically inserted here -->
                </div>
            </div>

            <div id="products" class="seller-tab-pane">
                <div class="seller-stats-grid">
                    <!-- Product stats will be dynamically inserted here -->
                </div>
                <div class="seller-chart-container">
                    <canvas id="productsChart"></canvas>
                </div>
            </div>

            <div id="bidding" class="seller-tab-pane">
                <div class="seller-stats-grid">
                    <!-- Bidding stats will be dynamically inserted here -->
                </div>
                <div class="seller-chart-container">
                    <canvas id="biddingChart"></canvas>
                </div>
            </div>

            <div id="orders" class="seller-tab-pane">
                <div class="seller-stats-grid">
                    <!-- Order stats will be dynamically inserted here -->
                </div>
                <div class="seller-charts-grid">
                    <div class="seller-chart-container-orders">
                        <canvas id="ordersChart"></canvas>
                    </div>
                    <div class="seller-chart-container-orders">
                        <canvas id="orderStatusChart"></canvas>
                    </div>
                </div>
            </div>

            <div id="certification" class="seller-tab-pane">
                <div class="seller-certification-container">
                    <div class="seller-certification-grid" id="certificationGrid">
                        <!-- Certification cards or no certification message will be dynamically inserted here -->
                    </div>
                </div>
            </div>
        </div>

        <div class="seller-modal-footer">
            <button class="seller-btn seller-secondary" id="closeSellerModal">Close</button>
        </div>
    </div>
</div>

<script src="./js/bidding-order.js"></script>

<script>
    const sellerTabButtons = document.querySelectorAll('.seller-tab-button');
    const sellerTabPanes = document.querySelectorAll('.seller-tab-pane');

    sellerTabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tab = button.dataset.tab;
            sellerTabButtons.forEach(b => b.classList.remove('active'));
            sellerTabPanes.forEach(pane => pane.classList.remove('active'));
            button.classList.add('active');
            document.getElementById(tab).classList.add('active');
        });
    });

    function toggleApproval(id, currentStatus, btn) {
        var newStatus = currentStatus == 1 ? 0 : 1;
        var originalText = btn.innerHTML;
        
        btn.innerHTML = 'Loading...';
        btn.disabled = true;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update_product_status.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4) {
                btn.disabled = false;
                if (xhr.status == 200) {
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            var statusBadge = document.getElementById('status-badge-' + id);
                            var featuredSelect = document.getElementById('featured-select-' + id);
                            
                            if (newStatus == 1) {
                                btn.innerHTML = 'Reject';
                                btn.className = 'btn btn-warning btn-xs';
                                btn.setAttribute('onclick', 'toggleApproval(' + id + ', 1, this)');
                                
                                statusBadge.innerHTML = 'Approved';
                                statusBadge.className = 'badge badge-success';
                                statusBadge.style.backgroundColor = 'green';
                                
                                if (featuredSelect) featuredSelect.disabled = false;
                            } else {
                                btn.innerHTML = 'Approve';
                                btn.className = 'btn btn-success btn-xs';
                                btn.setAttribute('onclick', 'toggleApproval(' + id + ', 0, this)');
                                
                                statusBadge.innerHTML = 'Rejected';
                                statusBadge.className = 'badge badge-danger';
                                statusBadge.style.backgroundColor = 'red';
                                
                                if (featuredSelect) {
                                    featuredSelect.value = '0';
                                    featuredSelect.disabled = true;
                                }
                            }
                        } else {
                            alert('Error: ' + response.message);
                            btn.innerHTML = originalText;
                        }
                    } catch (e) {
                        console.error('Invalid JSON response', xhr.responseText);
                        alert('Error updating status');
                        btn.innerHTML = originalText;
                    }
                } else {
                    alert('Request failed');
                    btn.innerHTML = originalText;
                }
            }
        };
        xhr.send("id=" + id + "&p_is_approve=" + newStatus);
    }

    function updateFeatured(productId, value) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update_product_status.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                console.log(xhr.responseText); // Optional: handle response
            }
        };
        xhr.send("id=" + productId + "&p_is_featured=" + value);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const fromDateInput = document.getElementById('fromDate');
        const toDateInput = document.getElementById('toDate');
        const clearDatesButton = document.getElementById('clearDates');
        const statusFilter = document.getElementById('statusFilter');
        const exportCsvBtn = document.getElementById('exportCsvBtn');
        const tableRows = document.querySelectorAll('#example1 tbody tr');

        function filterRows() {
            const fromDate = fromDateInput.value;
            const toDate = toDateInput.value;
            const selectedStatus = statusFilter.value;

            tableRows.forEach(row => {
                const rowDate = row.getAttribute('data-date');
                const rowStatus = row.getAttribute('data-status');

                let showRow = true;

                // Date filter
                if (fromDate && rowDate < fromDate) {
                    showRow = false;
                }
                if (toDate && rowDate > toDate) {
                    showRow = false;
                }

                // Status filter
                if (selectedStatus && rowStatus !== selectedStatus) {
                    showRow = false;
                }

                row.style.display = showRow ? '' : 'none';
            });
        }

        fromDateInput.addEventListener('change', filterRows);
        toDateInput.addEventListener('change', filterRows);
        statusFilter.addEventListener('change', filterRows);

        clearDatesButton.addEventListener('click', function() {
            fromDateInput.value = '';
            toDateInput.value = '';
            filterRows();
        });

        exportCsvBtn.addEventListener('click', function() {
            document.getElementById('hiddenFromDate').value = fromDateInput.value;
            document.getElementById('hiddenToDate').value = toDateInput.value;
            document.getElementById('hiddenStatusFilter').value = statusFilter.value;
        });
    });
</script>

<?php require_once('footer.php'); ?>