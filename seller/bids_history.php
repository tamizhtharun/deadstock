<?php
require_once('header.php');

$seller_id = $_SESSION['seller_session']['seller_id'];

$fromDate = null;
$toDate = null;
$filterStatus = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fromDate = $_POST['fromDate'] ? $_POST['fromDate'] : null;
    $toDate = $_POST['toDate'] ? $_POST['toDate'] : null;
    $filterStatus = $_POST['filterStatus'] ?? '';
}

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

if ($fromDate && $toDate) {
    $query .= " AND DATE(b.bid_time) BETWEEN :from_date AND :to_date";
} elseif ($fromDate) {
    $query .= " AND DATE(b.bid_time) >= :from_date";
} elseif ($toDate) {
    $query .= " AND DATE(b.bid_time) <= :to_date";
}

if ($filterStatus) {
    $query .= " AND b.bid_status = :bid_status";
}

$query .= " ORDER BY b.bid_time DESC";

$statement = $pdo->prepare($query);
$bindings = ['seller_id' => $seller_id];

if ($fromDate && $toDate) {
    $bindings['from_date'] = $fromDate;
    $bindings['to_date'] = $toDate;
} elseif ($fromDate) {
    $bindings['from_date'] = $fromDate;
} elseif ($toDate) {
    $bindings['to_date'] = $toDate;
}

if ($filterStatus) {
    $bindings['bid_status'] = $filterStatus === 'approved' ? 2 : 3;
}

$statement->execute($bindings);
$bids = $statement->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="content-header">
    <div class="content-header-left" style="display: flex; justify-content: space-between; align-items: center; padding: 10px 0;">
        <h1 style="margin: 0;">Bidding Orders</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="filter-container" style="margin: 20px 0; padding: 15px 0;">
                <form method="POST" style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="date-filter-group" style="display: flex; align-items: center;">
                        <label style="margin-right: 15px; font-weight: 500;">Filter by date range:</label>
                        <div style="display: flex; align-items: center;">
                            <input 
                                type="date" 
                                class="form-control input-sm" 
                                id="fromDate" 
                                name="fromDate" 
                                value="<?php echo $fromDate; ?>" 
                                style="width: 150px; margin-right: 10px; border: 1px solid #ddd; padding: 6px 12px; border-radius: 4px;"
                                placeholder="dd-mm-yyyy"
                            >
                            <span style="margin: 0 10px;">to</span>
                            <input 
                                type="date" 
                                class="form-control input-sm" 
                                id="toDate" 
                                name="toDate" 
                                value="<?php echo $toDate; ?>" 
                                style="width: 150px; margin-right: 10px; border: 1px solid #ddd; padding: 6px 12px; border-radius: 4px;"
                                placeholder="dd-mm-yyyy"
                            >
                            <button 
                                type="button" 
                                id="clearDates" 
                                class="btn btn-default btn-sm"
                                style="border: 1px solid #ddd; padding: 6px 12px; border-radius: 4px;"
                            >
                                Clear Dates
                            </button>
                        </div>
                    </div>
                    
                    <div class="status-filter-group">
                        <label style="margin-left :-12px; display: flex; justify-content: space-between; align-items: center;">Filter by status:</label>
                        <select 
                            id="filterStatus" 
                            name="filterStatus" 
                            class="form-control input-sm"
                           style="display: inline-block; width: auto; margin-left: 10px;"
                        >
                            <option value="">All Status</option>
                            <option value="approved" <?php echo $filterStatus === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="refunded" <?php echo $filterStatus === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="box box-info">
    <div class="box-body table-responsive">
        <?php if (empty($bids)): ?>
            <div id="no-bids-message" class="no-bids-container" style="display: block;">
                <div style="text-align: center; padding: 40px 20px;">
                    <div class="no-data-icon">
                        <i class="fa fa-search" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
                    </div>
                    <h3 style="color: #666; margin-bottom: 10px;">No Bids Found</h3>
                    <p style="color: #888; font-size: 16px;">There are no bids matching the selected filters.</p>
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
                            <td>
                                <?php
                                if ($bid['bid_status'] == 2) {
                                    echo 'Approved';
                                } else {
                                    echo 'Refunded';
                                }
                                ?>
                            </td>
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
document.addEventListener('DOMContentLoaded', function() {
    const fromDate = document.getElementById('fromDate');
    const toDate = document.getElementById('toDate');
    const filterStatus = document.getElementById('filterStatus');
    const clearDatesBtn = document.getElementById('clearDates');

    fromDate.max = new Date().toISOString().split('T')[0];
    toDate.max = fromDate.max;

    fromDate.addEventListener('change', filterBids);
    toDate.addEventListener('change', filterBids);
    filterStatus.addEventListener('change', filterBids);

    clearDatesBtn.addEventListener('click', function() {
        fromDate.value = '';
        toDate.value = '';
        filterStatus.value = '';
        filterBids();
    });

    function filterBids() {
        document.querySelector('form').submit();
    }
});
</script>

<?php require_once('footer.php'); ?>
