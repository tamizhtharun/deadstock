<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Bidding</h1>
	</div>
	
</section>

<section class="content">
<div class="row">
        <div class="col-md-12">
        <div class="date-filter-group" style="margin-bottom: 20px;">
            <label class="date-filter-label">Filter by date range:</label>
            <input type="date" class="form-control input-sm" id="fromDate" style="display: inline-block; width: auto; margin: 0 10px;">
            <label>to</label>
            <input type="date" class="form-control input-sm" id="toDate" style="display: inline-block; width: auto; margin: 0 10px;">
            <button id="clearDates" class="btn btn-default btn-sm">Clear Dates</button>
        </div>

        <div class="box box-info">
        
        <div class="box-body table-responsive" id="bidding-table-container">
          <table id="example1" class="table table-bordered table-hover table-striped">
			<thead>
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
                        MAX(b.bid_status) AS bid_status,
                        DATE(b.bid_time) as bid_date

                    FROM 
                        tbl_product p
                    JOIN 
                        sellers s ON p.seller_id = s.seller_id
                    LEFT JOIN 
                        bidding b ON p.id = b.product_id
                    GROUP BY 
                        p.id
                    HAVING
                        no_of_bids > 0  
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

                foreach ($result as $row): ?>
                    <tr class="bid-row" data-date="<?php echo date('Y-m-d', strtotime($row['bid_date'])); ?>">
                        <td><?php echo ++$i; ?></td>
                        <td><img src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>" alt="Product Photo" style="width:70px;"></td>
                        <td><?php echo $row['p_name']; ?></td>
                        <td>
                            <?php echo $row['seller_name'];?> 
                            ,<br><?php echo$row['seller_cname']; ?>
                            <div>
                                <a href="javascript:void(0);" onclick="openSellerModal(<?php echo $row['seller_id']; ?>)">View Seller Details</a>
                            </div>
                        </td>
                        <td><?php echo $row['no_of_bids']; ?></td>
                        <td><a href="view_bid.php?id=<?php echo $row['product_id']; ?>">View all Bids</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <!-- Updated No bids message styling -->
        <div id="no-bids-message" class="no-bids-container" style="display: none;">
          <div style="text-align: center; padding: 40px 20px;">
          <div class="no-data-icon">
               <i class="fa fa-search" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
                </div>            
            <h3 style="color: #666; margin-bottom: 10px;">No Bids Found</h3>
            <p style="color: #888; font-size: 16px;">There are no bids available for the selected date range.</p>
          </div>
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
        </div>

        <div class="seller-modal-footer">
            <button class="seller-btn seller-secondary" id="closeSellerModal">Close</button>
            <!-- <button class="seller-btn seller-primary">View Full Details</button> -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromDate = document.getElementById('fromDate');
    const toDate = document.getElementById('toDate');
    const clearDatesBtn = document.getElementById('clearDates');
    const biddingTableContainer = document.getElementById('bidding-table-container');
    const noBidsMessage = document.getElementById('no-bids-message');

    // Set max date to today for both inputs
    const today = new Date().toISOString().split('T')[0];
    fromDate.max = today;
    toDate.max = today;

    // Show all products initially
    showAllProducts();

    fromDate.addEventListener('change', function() {
        if (this.value) {
            toDate.min = this.value;
            filterDates();
        } else {
            toDate.min = '';
            showAllProducts();
        }
    });

    toDate.addEventListener('change', function() {
        if (this.value) {
            fromDate.max = this.value;
            filterDates();
        } else {
            fromDate.max = today;
            if (fromDate.value) {
                filterDates();
            } else {
                showAllProducts();
            }
        }
    });

    clearDatesBtn.addEventListener('click', function() {
        fromDate.value = '';
        toDate.value = '';
        fromDate.max = today;
        toDate.min = '';
        showAllProducts();
    });

    function showAllProducts() {
        const rows = document.querySelectorAll('.bid-row');
        rows.forEach(row => {
            row.style.display = '';
        });
        biddingTableContainer.style.display = 'block';
        noBidsMessage.style.display = 'none';
    }

    function isSameDate(date1, date2) {
        const d1 = new Date(date1);
        const d2 = new Date(date2);
        return d1.getFullYear() === d2.getFullYear() &&
               d1.getMonth() === d2.getMonth() &&
               d1.getDate() === d2.getDate();
    }

    function filterDates() {
        const rows = document.querySelectorAll('.bid-row');
        let hasVisibleRows = false;
        
        rows.forEach(row => {
            const rowDate = row.getAttribute('data-date');
            let showRow = true;

            // Handle single date and date range filtering
            if (fromDate.value && !toDate.value) {
                // Single date filter - from date only
                showRow = isSameDate(rowDate, fromDate.value);
            } else if (!fromDate.value && toDate.value) {
                // Single date filter - to date only
                showRow = isSameDate(rowDate, toDate.value);
            } else if (fromDate.value && toDate.value) {
                // Date range filter
                const rDate = new Date(rowDate);
                const fDate = new Date(fromDate.value);
                const tDate = new Date(toDate.value);
                showRow = rDate >= fDate && rDate <= tDate;
            }

            if (showRow) {
                row.style.display = '';
                hasVisibleRows = true;
            } else {
                row.style.display = 'none';
            }
        });

        // Toggle visibility of table and no-bids message
        if (!hasVisibleRows) {
            biddingTableContainer.style.display = 'none';
            noBidsMessage.style.display = 'block';
        } else {
            biddingTableContainer.style.display = 'block';
            noBidsMessage.style.display = 'none';
        }
    }
});


</script>



<script src="./js/bidding-order.js"></script>

<?php require_once('footer.php'); ?>