<?php require_once('header.php'); ?>

<section class="content-header">
    <div class="content-header-left">
        <h1>My Orders</h1>
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
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="canceled">Canceled</option>
                </select>
            </div>
        </div>

        <div class="box box-info">
            <div class="box-body table-responsive" id="bidding-order-table-container">
                <table id="example1" class="table table-bordered table-hover table-striped">
                    <thead>
                        <tr>
                            <th width="50">#</th>
                            <th width="120">Order ID</th>
                            <th width="200">Product</th>
                            <!-- <th width="200">Customer</th> -->
                            <th width="150">Price</th>
                            <th width="150">Quantity</th>

                            <!-- <th width="250">Delivery Address</th> -->
                            <th width="120">Status</th>
                            <th width="150">Processing Time</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $seller_id = $_SESSION['seller_session']['seller_id']; // Get logged in seller's ID
                        $i = 0;
                        $statement = $pdo->prepare("SELECT 
                            o.id,
                            o.order_id,
                            o.price,
                            o.quantity,
                            o.order_status,
                            o.created_at,
                            o.processing_time,
                            p.p_name,
                            p.p_featured_photo,
                            u.username,
                            u.email,
                            u.phone_number,
                            ua.full_name,
                            ua.phone_number as delivery_phone,
                            ua.address,
                            ua.city,
                            ua.state,
                            ua.pincode
                        FROM 
                            tbl_orders o
                        JOIN 
                            tbl_product p ON o.product_id = p.id
                        JOIN 
                            users u ON o.user_id = u.id
                        LEFT JOIN 
                            users_addresses ua ON u.id = ua.user_id AND ua.is_default = 1
                        WHERE 
                            o.seller_id = :seller_id
                        AND
                            o.order_type = 'bid'
                        ORDER BY 
                            o.created_at DESC");
                        
                        $statement->execute(array(':seller_id' => $seller_id));
                        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                        
                        foreach ($result as $row): 
                            $i++;
                    ?>
                    <tr class="bid-order-row" data-date="<?php echo date('Y-m-d', strtotime($row['created_at'])); ?>" data-status="<?php echo $row['order_status']; ?>" data-order-id="<?php echo $row['id']; ?>">
                            <td><?php echo $i; ?></td>
                            <td>
                                <strong><?php echo $row['order_id']; ?></strong><br>
                                <small class="text-muted"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></small>
                            </td>                           
                                 <td>
                                    <div class="d-flex align-items-center">
                                    <img src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>" 
                                     alt="Product Photo" 
                                     style="width:70px;"
                                     class="product-image"
                                     onclick="openImageModal('../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>')">
                                        <div class="ms-3">
                                            <?php echo $row['p_name']; ?>
                                        </div>
                                    </div>
                                </td>
                                <!-- <td>
                                    <strong><?php echo $row['username']; ?></strong><br>
                                    <?php echo $row['email']; ?><br>
                                    <?php echo $row['phone_number']; ?>
                                </td> -->
                                <td>
                                    Price: ₹<?php echo number_format($row['price'], 2); ?><br>
                                </td>
                                <td>
                                    Qty: <?php echo $row['quantity']; ?><br>
                                </td>

                                <!-- <td>
                                    <?php if(!empty($row['address'])): ?>
                                        <strong><?php echo $row['full_name']; ?></strong><br>
                                        <?php echo $row['delivery_phone']; ?><br>
                                        <?php echo $row['address']; ?><br>
                                        <?php echo $row['city']; ?>, <?php echo $row['state']; ?> - <?php echo $row['pincode']; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Address not available</span>
                                    <?php endif; ?>
                                </td> -->
                                <td class="text-center">
                                    <span class="status-badge status-<?php echo $row['order_status']; ?>">
                                        <?php echo ucfirst($row['order_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    if (!empty($row['processing_time'])) {
                                        echo date('Y-m-d H:i:s', strtotime($row['processing_time']));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if($row['order_status'] === 'pending'): ?>
                                        <div class="action-buttons">
                                            <button 
                                                class="btn-status-update" 
                                                onclick="updateOrderStatus(<?php echo $row['id']; ?>, 'processing')"
                                            >
                                                <i class="fa fa-cog"></i>
                                                Processing
                                            </button>
                                        </div>
                                    <?php elseif($row['order_status'] !== 'delivered' && $row['order_status'] !== 'canceled'): ?>
                                        <button class="btn-status-update disabled">
                                            <i class="fa fa-lock"></i> Status Updated
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-status-update disabled">
                                            <i class="fa fa-lock"></i> No Actions Available
                                        </button>
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
                    <h3 style="color: #666; margin-bottom: 10px;">No Orders Found</h3>
                    <p style="color: #888; font-size: 16px;">There are no orders available for the selected filters.</p>
                </div>
            </div>
        </div>
    </div>
</div>
</section>

<!-- Image Modal -->
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

            if (selectedStatus && rowStatus !== selectedStatus) {
                showRow = false;
            }

            if (showRow) {
                if (fDate && !tDate) {
                    showRow = rowDate.toISOString().split('T')[0] === fDate.toISOString().split('T')[0];
                } else if (fDate && tDate) {
                    showRow = rowDate >= fDate && rowDate <= tDate;
                } else if (!fDate && tDate) {
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

function updateOrderStatus(orderId, newStatus) {
    if (!confirm(`Are you sure you want to update the order status to ${newStatus}?`)) {
        return;
    }

    fetch('update_order_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}&status=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`tr[data-order-id="${orderId}"]`);
            
            if (row) {
                // Update status badge
                const statusCell = row.querySelector('.text-center');
                if (statusCell) {
                    statusCell.innerHTML = `<span class="status-badge status-${newStatus}">${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}</span>`;
                }
                
                // Update row attribute
                row.setAttribute('data-status', newStatus);
                
                // Update action buttons
                const actionCell = row.querySelector('.action-buttons');
                if (actionCell) {
                    actionCell.innerHTML = `
                        <button class="btn-status-update disabled">
                            <i class="fa fa-lock"></i> Status Updated
                        </button>
                    `;
                }

                // Update processing time
                const processingTimeCell = row.querySelector('td:nth-child(8)');
                if (processingTimeCell && newStatus === 'processing') {
                    const now = new Date();
                    processingTimeCell.textContent = now.toISOString().slice(0, 19).replace('T', ' ');
                }
            }
            
            alert('Order status updated successfully!');
            location.reload();
        } else {
            throw new Error(data.message || 'Failed to update order status');
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        alert(`Failed to update order status: ${error.message}`);
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

// Modal event listeners
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    const closeBtn = document.querySelector('.close-modal');

    modal.addEventListener('click', function(e) {
        if (e.target === modal || e.target.classList.contains('modal-container')) {
            closeImageModal();
        }
    });

    closeBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        closeImageModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });

    document.getElementById('modalImage').addEventListener('click', function(e) {
        e.stopPropagation();
    });
});

function sendOrder(button) {
    if (!confirm('Are you sure you want to send this order to seller?')) {
        return;
    }

    const data = button.dataset;
    
    // Construct URL without order_id if it's empty
    const url = `process_bid_order.php?action=send&bid_id=${data.bidId}&product_id=${data.productId}&user_id=${data.userId}&seller_id=${data.sellerId}&quantity=${data.quantity}&price=${data.price}` + 
               (data.orderId ? `&order_id=${data.orderId}` : '');

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = button.closest('tr');
                const statusCell = row.querySelector('.order-status');
                const actionCell = row.querySelector('.action-column');

                statusCell.innerHTML = '<span class="status-badge status-pending">Pending</span>';
                row.setAttribute('data-status', 'pending');

                actionCell.innerHTML = `
                    <div class="action-buttons">
                        <button 
                            class="btn-status-update" 
                            onclick="updateOrderStatus(${data.order_id}, 'processing')"
                        >
                            <i class="fa fa-cog"></i>
                            Processing
                        </button>
                        <button 
                            class="btn-status-update" 
                            onclick="updateOrderStatus(${data.order_id}, 'canceled')"
                        >
                            <i class="fa fa-times-circle"></i>
                            Canceled
                        </button>
                    </div>
                `;

                alert('Order sent to seller successfully.');
            } else {
                alert('Failed to send order: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send order. Please try again.');
        });
}


</script>

<?php require_once('footer.php'); ?>

