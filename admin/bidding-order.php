<?php require_once('header.php'); ?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Bidding Orders</h1>
    </div>
    
    <div class="content-header-right">
        <a href="process_bid_order.php?action=sendall" class="btn btn-success" onclick="return confirm('Are you sure you want to send all orders to sellers?')">Send All Orders</a>
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
                <select id="statusFilter" class="form-control input-sm" style="display: inline-block; width: auto; margin-left: 10px;">
                    <option value="">All Orders</option>
                    <option value="not_sent">Not Sent Orders</option>
                    <!-- <option value="sent">Sent Orders</option> -->
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                </select>
            </div>
        </div>

        <div class="box box-info">
            <div class="box-body table-responsive" id="bidding-order-table-container">
                <table id="example1" class="table table-bordered table-hover table-striped">
                    <!-- Table header remains the same -->
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Product Photo</th>
                            <th>Product Name</th>
                            <th>Seller Details</th>
                            <th>Winning User Details</th>
                            <th>Win Bid Price</th>
                            <th>Bid Quantity</th>
                            <th>User Address</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $i = 0;
                        $statement = $pdo->prepare("SELECT 
                        b.bid_id,
                        b.bid_price,
                        b.bid_quantity,
                        b.order_id,
                        b.payment_id,
                        p.id AS product_id,
                        p.p_name,
                        p.p_featured_photo,
                        p.seller_id,
                        s.seller_name,
                        s.seller_cname,
                        u.username,
                        u.id AS user_id,
                        u.email,
                        u.phone_number,
                        ua.full_name,
                        ua.phone_number as delivery_phone,
                        ua.address,
                        ua.city,
                        ua.state,
                        ua.pincode,
                        DATE(b.bid_time) as bid_date,
                        b.bid_status,
                        o.order_status
                    FROM 
                        bidding b
                    JOIN 
                        tbl_product p ON b.product_id = p.id
                    JOIN 
                        sellers s ON p.seller_id = s.seller_id
                    JOIN 
                        users u ON b.user_id = u.id
                    LEFT JOIN 
                        users_addresses ua ON u.id = ua.user_id AND ua.is_default = 1
                    LEFT JOIN 
                        tbl_orders o ON b.product_id = o.product_id 
                        AND b.user_id = o.user_id
                    WHERE 
                        b.bid_status = 2
                    ORDER BY 
                        b.bid_time DESC");
                        $statement->execute();
                        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($result as $row): 
                            $i++;
                    ?>
                        <tr class="bid-order-row" data-date="<?php echo $row['bid_date']; ?>" data-status="<?php echo empty($row['order_status']) ? 'not_sent' : $row['order_status']; ?>">
                            <td><?php echo $i; ?></td>
                            <td>
    <img src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>" 
         alt="Product Photo" 
         style="width:70px;"
         class="product-image"
         onclick="openImageModal('../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>')">
</td>
                            <td><?php echo $row['p_name']; ?></td>
                            <td>
                                <?php echo $row['seller_name']; ?><br>
                                <?php echo $row['seller_cname']; ?>
                            </td>
                            <td>
                                <?php echo $row['username']; ?><br>
                                <?php echo $row['email']; ?><br>
                                <?php echo $row['phone_number']; ?>
                            </td>
                            <td><?php echo number_format($row['bid_price'], 2); ?></td>
                            <td><?php echo $row['bid_quantity']; ?></td>
                            <td>
                                <?php if(!empty($row['address'])): ?>
                                    <?php echo $row['full_name']; ?><br>
                                    <?php echo $row['delivery_phone']; ?><br>
                                    <?php echo $row['address']; ?><br>
                                    <?php echo $row['city']; ?>, <?php echo $row['state']; ?><br>
                                    <?php echo $row['pincode']; ?>
                                <?php else: ?>
                                    <span class="text-muted">Address not available</span>
                                <?php endif; ?>
                            </td>
                            <td class="order-status">
                                <?php 
                                    $status = !empty($row['order_status']) ? $row['order_status'] : 'not_sent';
                                    $statusText = !empty($row['order_status']) ? ucfirst($row['order_status']) : 'Not sent to seller';
                                    echo "<span class='status-badge status-{$status}'>{$statusText}</span>";
                                ?>
                            </td>
                            <td class="action-column">
                                <?php if(empty($row['order_status'])): ?>
                                    <button 
                                        class="btn btn-primary btn-sm send-order-btn" 
                                        data-bid-id="<?php echo $row['bid_id']; ?>"
                                        data-product-id="<?php echo $row['product_id']; ?>"
                                        data-user-id="<?php echo $row['user_id']; ?>"
                                        data-seller-id="<?php echo $row['seller_id']; ?>"
                                        data-quantity="<?php echo $row['bid_quantity']; ?>"
                                        data-price="<?php echo $row['bid_price']; ?>"
                                        onclick="sendOrder(this)">
                                        Send
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-info btn-sm" disabled style="opacity: 0.7; cursor: not-allowed;" >Sent</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div id="no-bids-message" class="no-bids-container" style="display: none;">
                <div style="text-align: center; padding: 40px 20px;">
                    <div class="no-data-icon">
                        <i class="fa fa-search" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
                    </div>
                    <h3 style="color: #666; margin-bottom: 10px;">No Bidding Orders Found</h3>
                    <p style="color: #888; font-size: 16px;">There are no bidding orders available for the selected filters.</p>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
<div id="imageModal" class="modal">
    <span class="close-modal">&times;</span>
    <div class="modal-container">
        <img id="modalImage" class="modal-content">
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromDate = document.getElementById('fromDate');
    const toDate = document.getElementById('toDate');
    const clearDatesBtn = document.getElementById('clearDates');
    const statusFilter = document.getElementById('statusFilter');
    const biddingOrderTableContainer = document.getElementById('bidding-order-table-container');
    const noBidsMessage = document.getElementById('no-bids-message');

    const today = new Date().toISOString().split('T')[0];
    fromDate.max = today;
    toDate.max = today;

    function filterRows() {
        const rows = document.querySelectorAll('.bid-order-row');
        let hasVisibleRows = false;

        const selectedStatus = statusFilter.value;
        const fDate = fromDate.value ? new Date(fromDate.value) : null;
        const tDate = toDate.value ? new Date(toDate.value) : null;

        rows.forEach(row => {
            const rowDate = new Date(row.getAttribute('data-date'));
            const rowStatus = row.getAttribute('data-status');

            let showRow = true;

            // Status filter
            if (selectedStatus && rowStatus !== selectedStatus) {
                showRow = false;
            }

            // Date filter
            if (showRow) {
                if (fDate && !tDate) {
                    // Single date filter
                    showRow = rowDate.toISOString().split('T')[0] === fDate.toISOString().split('T')[0];
                } else if (fDate && tDate) {
                    // Date range filter
                    showRow = rowDate >= fDate && rowDate <= tDate;
                } else if (!fDate && tDate) {
                    // Single end date filter
                    showRow = rowDate <= tDate;
                }
            }

            row.style.display = showRow ? '' : 'none';
            if (showRow) hasVisibleRows = true;
        });

        biddingOrderTableContainer.style.display = hasVisibleRows ? 'block' : 'none';
        noBidsMessage.style.display = hasVisibleRows ? 'none' : 'block';
    }

    function showAllOrders() {
        document.querySelectorAll('.bid-order-row').forEach(row => {
            row.style.display = '';
        });
        biddingOrderTableContainer.style.display = 'block';
        noBidsMessage.style.display = 'none';
    }

    fromDate.addEventListener('change', filterRows);
    toDate.addEventListener('change', filterRows);
    statusFilter.addEventListener('change', filterRows);

    clearDatesBtn.addEventListener('click', function() {
        fromDate.value = '';
        toDate.value = '';
        fromDate.max = today;
        toDate.min = '';
        if (statusFilter.value) {
            filterRows();
        } else {
            showAllOrders();
        }
    });
});

function sendOrder(button) {
    if (!confirm('Are you sure you want to send this order to seller?')) {
        return;
    }

    const data = button.dataset;
    const url = `process_bid_order.php?action=send&bid_id=${data.bidId}&product_id=${data.productId}&user_id=${data.userId}&seller_id=${data.sellerId}&quantity=${data.quantity}&price=${data.price}`;

    fetch(url)
        .then(response => response.text())
        .then(() => {
            // Update button
            button.className = 'btn btn-info btn-sm';
            button.disabled = true;
            button.style.opacity = '0.7';
            button.style.cursor = 'not-allowed';
            button.textContent = 'Sent';

            // Update status cell
            const row = button.closest('tr');
            const statusCell = row.querySelector('.order-status');
            statusCell.textContent = 'Sent';

            // Update row status attribute
            row.setAttribute('data-status', 'sent');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send order. Please try again.');
        });
}

function openImageModal(imgSrc) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    
    modal.classList.add('show');
    modalImg.src = imgSrc;
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    
    modal.classList.remove('show');
    setTimeout(() => {
        modalImg.src = '';
    }, 300);
    document.body.style.overflow = 'auto';
}

// Event listeners for modal closing
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    const closeBtn = document.querySelector('.close-modal');

    // Close on clicking outside the image
    modal.addEventListener('click', function(e) {
        if (e.target === modal || e.target.classList.contains('modal-container')) {
            closeImageModal();
        }
    });

    // Close on clicking close button
    closeBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        closeImageModal();
    });

    // Close on ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });

    // Prevent image click from closing modal
    document.getElementById('modalImage').addEventListener('click', function(e) {
        e.stopPropagation();
    });
});

</script>

<?php require_once('footer.php'); ?>