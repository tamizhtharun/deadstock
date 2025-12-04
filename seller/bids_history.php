<?php
require_once('../db_connection.php');
session_start();

// Check if export_csv button is clicked - must be before any output
if (isset($_POST['export_csv'])) {

    $seller_id = $_SESSION['seller_session']['seller_id'] ?? null;

    // Build query with filters
    $query = "
        SELECT
            p.p_name,
            b.bid_quantity,
            b.bid_price,
            b.bid_status,
            b.bid_time
        FROM
            bidding b
        JOIN
            tbl_product p ON b.product_id = p.id
        WHERE
            p.seller_id = :seller_id
            AND (b.bid_status = 2 OR b.bid_status = 3)
    ";

    $bindings = ['seller_id' => $seller_id];

    // Apply date filter
    if (!empty($_POST['from_date']) && !empty($_POST['to_date'])) {
        $query .= " AND DATE(b.bid_time) BETWEEN :fromDate AND :toDate";
        $bindings['fromDate'] = $_POST['from_date'];
        $bindings['toDate'] = $_POST['to_date'];
    } elseif (!empty($_POST['from_date'])) {
        $query .= " AND DATE(b.bid_time) >= :fromDate";
        $bindings['fromDate'] = $_POST['from_date'];
    } elseif (!empty($_POST['to_date'])) {
        $query .= " AND DATE(b.bid_time) <= :toDate";
        $bindings['toDate'] = $_POST['to_date'];
    }

    // Apply status filter
    if (!empty($_POST['status_filter'])) {
        if ($_POST['status_filter'] == 'approved') {
            $query .= " AND b.bid_status = 2";
        } elseif ($_POST['status_filter'] == 'refunded') {
            $query .= " AND b.bid_status = 3";
        }
    }

    $query .= " ORDER BY b.bid_time DESC";

    try {
        $statement = $pdo->prepare($query);
        $statement->execute($bindings);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Set headers for CSV download after successful query
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bids-history.csv"');

        // Output CSV data
        $output = fopen('php://output', 'w');

        // Write headers
        fputcsv($output, array('#', 'Product Name', 'Quantity', 'Price', 'Status', 'Bid Date'));

        // Write data
        $i = 0;
        foreach ($result as $row) {
            $i++;
            $bid_date = !empty($row['bid_time']) && $row['bid_time'] != '0000-00-00 00:00:00' ? '="' . date('d/m/Y', strtotime($row['bid_time'])) . '"' : 'N/A';
            $status = $row['bid_status'] == 2 ? 'Approved' : 'Refunded';
            fputcsv($output, array(
                $i,
                $row['p_name'],
                $row['bid_quantity'],
                number_format($row['bid_price'], 2),
                $status,
                $bid_date
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

require_once('header.php');

$seller_id = $_SESSION['seller_session']['seller_id'];

$fromDate = $_POST['fromDate'] ?? null;
$toDate = $_POST['toDate'] ?? null;
$filterStatus = $_POST['filterStatus'] ?? '';

$query = "
    SELECT 
        p.p_featured_photo,
        p.p_name,
        b.bid_quantity,
        b.bid_price,
        b.bid_status,
        b.bid_time
    FROM 
        bidding b
    JOIN
        tbl_product p ON b.product_id = p.id
    WHERE 
        p.seller_id = :seller_id
        AND (b.bid_status = 2 OR b.bid_status = 3)
";

$bindings = ['seller_id' => $seller_id];

if ($fromDate && $toDate) {
    $query .= " AND DATE(b.bid_time) BETWEEN :fromDate AND :toDate";
    $bindings['fromDate'] = $fromDate;
    $bindings['toDate'] = $toDate;
}

if ($filterStatus !== '') {
    if ($filterStatus == 'approved') {
        $query .= " AND b.bid_status = 2";
    } elseif ($filterStatus == 'refunded') {
        $query .= " AND b.bid_status = 3";
    }
}

$query .= " ORDER BY b.bid_time DESC";
$statement = $pdo->prepare($query);
$statement->execute($bindings);
$bids = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Bidding History</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
        <div class="filter-container" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0;">
                <div class="date-filter-group">
                    <label><strong>Filter by date range:</strong></label>
                    <input 
                        type="date" 
                        id="fromDate" 
                        name="fromDate"
                        class="form-control input-sm" 
                        style="display: inline-block; width: auto; margin: 0 10px;"
                        value="<?php echo htmlspecialchars($fromDate); ?>"
                    >
                    <label>to</label>
                    <input 
                        type="date" 
                        id="toDate" 
                        name="toDate"
                        class="form-control input-sm"  
                        style="display: inline-block; width: auto; margin: 0 10px;"
                        value="<?php echo htmlspecialchars($toDate); ?>"
                    >
                    <button type="button" id="clearDates" class="btn btn-default btn-sm">Clear Dates</button>
                </div>
                <div class="status-filter-group">
                    <label><strong>Filter by status:</strong></label>
                    <select
                        id="statusFilter"
                        name="filterStatus"
                        class="form-control input-sm"
                        style="display: inline-block; width: auto; margin-left: 10px;"
                    >
                        <option value="">All Status</option>
                        <option value="approved" <?php echo $filterStatus === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="refunded" <?php echo $filterStatus === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                    </select>
                </div>
                <div class="export-group">
                    <form method="POST" action="" id="exportForm">
                        <input type="hidden" name="seller_id" value="<?php echo $seller_id; ?>">
                        <input type="hidden" name="status_filter" id="hiddenStatusFilter">
                        <input type="hidden" name="from_date" id="hiddenFromDate">
                        <input type="hidden" name="to_date" id="hiddenToDate">
                        <button type="submit" name="export_csv" class="btn btn-primary btn-xs">Export to CSV</button>
                    </form>
                </div>
            </div>

            <div class="box box-info">
                <div class="box-body table-responsive">
                    <?php if (empty($bids)): ?>
                        <div id="no-bids-message" class="no-bids-container" style="display: block;">
                            <div style="text-align: center; padding: 40px 20px;">
                                <i class="fa fa-search" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
                                <h3 style="color: #666;">No Orders Found</h3>
                                <p style="color: #888; font-size: 16px;">There are no orders available for the selected filters.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <table id="example1" class="table table-bordered table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product Photo</th>
                                    <th>Product Name</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Bid Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($bids as $index => $bid): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td>
                                            <img src="../assets/uploads/product-photos/<?php echo $bid['p_featured_photo']; ?>" 
                                                 alt="Product Photo" 
                                                 style="width:70px;">
                                        </td>
                                        <td><?php echo $bid['p_name']; ?></td>
                                        <td><?php echo $bid['bid_quantity']; ?></td>
                                        <td>₹<?php echo number_format($bid['bid_price'], 2); ?></td>
                                        <td><?php echo $bid['bid_status'] == 2 ? 'Approved' : 'Refunded'; ?></td>
                                        <td><?php echo (!empty($bid['bid_time']) && $bid['bid_time'] != '0000-00-00 00:00:00') ? date('d-m-Y', strtotime($bid['bid_time'])) : 'N/A'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Add event listeners for filter changes and clear button
document.addEventListener('DOMContentLoaded', function() {
    const fromDateInput = document.getElementById('fromDate');
    const toDateInput = document.getElementById('toDate');
    const statusFilter = document.getElementById('statusFilter');
    const clearDatesBtn = document.getElementById('clearDates');

    // Function to submit the form
    function submitFilter() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.location.href;

        // Add date inputs
        const fromDate = createHiddenInput('fromDate', fromDateInput.value);
        const toDate = createHiddenInput('toDate', toDateInput.value);
        const status = createHiddenInput('filterStatus', statusFilter.value);

        form.appendChild(fromDate);
        form.appendChild(toDate);
        form.appendChild(status);

        document.body.appendChild(form);
        form.submit();
    }

    // Function to create hidden input
    function createHiddenInput(name, value) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value || '';
        return input;
    }

    // Event listener for date inputs
    fromDateInput.addEventListener('change', submitFilter);
    toDateInput.addEventListener('change', submitFilter);

    // Event listener for status filter
    statusFilter.addEventListener('change', submitFilter);

    // Event listener for clear dates button
    clearDatesBtn.addEventListener('click', function() {
        fromDateInput.value = '';
        toDateInput.value = '';
        submitFilter();
    });

    // Handle CSV export form submission
    document.getElementById('exportForm').addEventListener('submit', function(e) {
        // Populate hidden fields with current filter values
        document.getElementById('hiddenStatusFilter').value = statusFilter.value;
        document.getElementById('hiddenFromDate').value = fromDateInput.value;
        document.getElementById('hiddenToDate').value = toDateInput.value;
    });
});
</script>

<?php require_once('footer.php'); ?>
