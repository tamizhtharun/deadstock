<?php
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
                                        <td><?php echo date('Y-m-d', strtotime($bid['bid_time'])); ?></td>
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
});
</script>

<?php require_once('footer.php'); ?>
