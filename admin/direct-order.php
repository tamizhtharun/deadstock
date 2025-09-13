<?php 
//direct-order.php
require_once('header.php');
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Direct Orders</h1>
    </div>
</section>

<style>
/* Delhivery Status Badge Styles */
.status-created {
    background-color: #17a2b8;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.status-manifested {
    background-color: #ffc107;
    color: #212529;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.status-transit {
    background-color: #007bff;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.status-delivered {
    background-color: #28a745;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.status-pending {
    background-color: #6c757d;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.status-non-serviceable {
    background-color: #dc3545;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
}

.awb-number {
    font-family: 'Courier New', monospace;
    background: #e3f2fd;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
    border: 1px solid #bbdefb;
}

/* Delhivery Integration Status Indicator */
.delhivery-status-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    background: #28a745;
    color: white;
    padding: 10px 15px;
    border-radius: 5px;
    font-size: 12px;
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.delhivery-status-indicator.staging {
    background: #ffc107;
    color: #212529;
}

.delhivery-status-indicator.production {
    background: #28a745;
    color: white;
}
</style>

<section class="content">
<div class="row">
<div class="col-md-12">
    <div class="filter-container" style="margin-left:-12px; display: flex; justify-content: space-between; align-items: center;">
        <div class="date-filter-group">
            <label class="date-filter-label">Filter by date range:</label>
            <input type="date" class="form-control input-sm" id="fromDate" style="display: inline-block; width: auto; margin: 0 10px;">
            <label>to</label>
            <input type="date" class="form-control input-sm" id="toDate" style="display: inline-block; width: auto; margin: 0 10px;">
            <button id="clearDates" class="btn btn-default btn-sm">Clear Dates</button>
        </div>
        
        <div class="status-filter-group">
            <label>Filter by status:</label>
            <select id="statusFilter" class="form-control input-sm" style="display: inline-block; width: auto; margin-left: 10px; padding-top:0;">
                <option value="">All Orders</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="canceled">Cancelled</option>
            </select>
        </div>
    </div>

    <!-- Delhivery Integration Status Indicator -->
    <?php
    require_once('../config/delhivery_config.php');
    $envClass = (DELHIVERY_ENVIRONMENT === 'staging') ? 'staging' : 'production';
    $envText = strtoupper(DELHIVERY_ENVIRONMENT);
    ?>
    <div class="delhivery-status-indicator <?php echo $envClass; ?>">
        <i class="fa fa-truck"></i> Delhivery <?php echo $envText; ?>
    </div>

    <div class="box box-info">
        <div class="box-body table-responsive">
            <table id="example1" class="table table-bordered table-hover table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th width="120">Order ID</th>
                        <th width="200">Product</th>
                        <th width="100">Seller Details</th>
                        <th>Customer Details</th>
                        <th width="80">Amount</th>
                        <th>Delivery Address</th>
                        <th>Status</th>
                        <th>Processing Time</th>
                        <th>Delhivery AWB</th>
                        <th>Shipment Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $i = 0;
                    $statement = $pdo->prepare("SELECT 
                        o.id AS order_id,
                        o.order_id AS order_number,
                        o.price,
                        o.quantity,
                        o.order_status,
                        o.processing_time,
                        o.tracking_id,
                        o.delhivery_awb,
                        o.delhivery_shipment_status,
                        o.delhivery_created_at,
                        o.address_id,
                        o.created_at,
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
                        ua.pincode
                    FROM 
                        tbl_orders o
                    JOIN 
                        tbl_product p ON o.product_id = p.id
                    JOIN 
                        sellers s ON p.seller_id = s.seller_id
                    JOIN 
                        users u ON o.user_id = u.id
                    LEFT JOIN
                        users_addresses ua ON o.address_id = ua.id
                    WHERE 
                        o.order_type = 'direct'
                    ORDER BY 
                        o.created_at DESC");
                    $statement->execute();
                    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($result as $row): 
                        $i++;
                ?>
                    <tr class="order-row" 
                        data-order-id="<?php echo $row['order_id']; ?>" 
                        data-date="<?php echo date('Y-m-d', strtotime($row['created_at'])); ?>" 
                        data-status="<?php echo $row['order_status']; ?>">
                        <td><?php echo $i; ?></td>
                        <td>
                            <strong><?php echo $row['order_number']; ?></strong><br>
                            <small class="text-muted"><?php echo date('M d, Y', strtotime($row['created_at'])); ?></small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="../assets/uploads/product-photos/<?php echo $row['p_featured_photo']; ?>" 
                                     alt="Product Photo" 
                                     style="width:70px;"
                                     class="product-image">
                                <div class="ms-3">
                                    <?php echo $row['p_name']; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php echo $row['seller_name']; ?><br>
                            <?php echo $row['seller_cname']; ?>
                            <div>
                                <a href="javascript:void(0);" onclick="openSellerModal(<?php echo $row['seller_id']; ?>)">View Seller Details</a>
                            </div>
                        </td>
                        <td>
                            <?php echo $row['username']; ?><br>
                            <?php echo $row['email']; ?><br>
                            <?php echo $row['phone_number']; ?>
                        </td>
                        <td>
                            Price: ₹<?php echo number_format($row['price'], 2); ?><br>
                            Qty: <?php echo $row['quantity']; ?><br>
                        </td>
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
                                $statusText = ucfirst($row['order_status']);
                                echo "<span class='status-badge status-{$row['order_status']}'>{$statusText}</span>";
                            ?>
                        </td>
                        <td class="processing-time">
                            <?php
                                if (!empty($row['processing_time'])) {
                                    echo date('Y-m-d H:i:s', strtotime($row['processing_time']));
                                } else {
                                    echo '-';
                                }
                            ?>
                        </td>
                        <td class="delhivery-awb">
                            <?php if (!empty($row['delhivery_awb'])): ?>
                                <span class="awb-number" style="font-family: monospace; background: #e3f2fd; padding: 2px 6px; border-radius: 3px; font-size: 12px;">
                                    <?php echo $row['delhivery_awb']; ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="shipment-status">
                            <?php if (!empty($row['delhivery_shipment_status'])): ?>
                                <?php 
                                    $statusClass = '';
                                    $statusText = ucfirst($row['delhivery_shipment_status']);
                                    switch($row['delhivery_shipment_status']) {
                                        case 'created':
                                            $statusClass = 'status-created';
                                            break;
                                        case 'manifested':
                                            $statusClass = 'status-manifested';
                                            break;
                                        case 'in_transit':
                                            $statusClass = 'status-transit';
                                            break;
                                        case 'delivered':
                                            $statusClass = 'status-delivered';
                                            break;
                                        case 'non_serviceable':
                                            $statusClass = 'status-non-serviceable';
                                            $statusText = 'Non-Serviceable';
                                            break;
                                        default:
                                            $statusClass = 'status-pending';
                                    }
                                ?>
                                <span class="status-badge <?php echo $statusClass; ?>" style="font-size: 11px;">
                                    <?php echo $statusText; ?>
                                </span>
                                <?php if (!empty($row['delhivery_created_at'])): ?>
                                    <br><small class="text-muted"><?php echo date('M d, H:i', strtotime($row['delhivery_created_at'])); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="action-column">
                            <?php if($row['order_status'] === 'pending'): ?>
                                <button class="btn-status-update disabled">
                                    <i class="fa fa-clock-o"></i> Waiting for Seller
                                </button>
                            <?php elseif($row['order_status'] === 'processing'): ?>
                                <div class="action-buttons">
                                    <button 
                                        class="btn-status-update" 
                                        onclick="updateOrderStatus(<?php echo $row['order_id']; ?>, 'shipped')">
                                        <i class="fa fa-truck"></i> Shipped
                                    </button>
                                    <button 
                                        class="btn-status-update" 
                                        onclick="updateOrderStatus(<?php echo $row['order_id']; ?>, 'canceled')">
                                        <i class="fa fa-times-circle"></i> Cancelled
                                    </button>
                                </div>
<?php elseif($row['order_status'] === 'shipped'): ?>
    <div class="action-buttons">
        <button 
            class="btn-status-update" 
            onclick="updateOrderStatus(<?php echo $row['order_id']; ?>, 'delivered')">
            <i class="fa fa-check-circle"></i> Delivered
        </button>
        <button 
            class="btn-status-update" 
            onclick="updateOrderStatus(<?php echo $row['order_id']; ?>, 'canceled')">
            <i class="fa fa-times-circle"></i> Cancelled
        </button>
        <?php if (!empty($row['delhivery_awb'])): ?>
            <button 
                class="btn-status-update" 
                onclick="trackShipment(<?php echo $row['order_id']; ?>)"
                style="background: #28a745; color: white; margin: 2px;">
                <i class="fa fa-search"></i> Track Shipment
            </button>
        <?php endif; ?>
        <a href="generate_invoice.php?order_id=<?php echo $row['order_id']; ?>" 
           class="btn btn-sm mt-1" style="color: #007bff; font-weight: 600; border-radius: 4px; padding: 5px 10px; background-color: transparent; border: 1px solid #007bff;">
           <i class="fa fa-file-pdf-o"></i> Generate Invoice
        </a>
    </div>
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
    
    // Add container for table and no orders message
    const tableWrapper = document.createElement('div');
    tableWrapper.id = 'direct-order-table-container';
    const table = document.getElementById('example1');
    table.parentNode.insertBefore(tableWrapper, table);
    tableWrapper.appendChild(table);

    // Create and add no orders message
    const noOrdersMessage = document.createElement('div');
    noOrdersMessage.id = 'no-orders-message';
    noOrdersMessage.className = 'no-orders-container';
    noOrdersMessage.style.display = 'none';
    noOrdersMessage.innerHTML = `
        <div style="text-align: center; padding: 40px 20px;">
            <div class="no-data-icon">
                <i class="fa fa-search" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
            </div>
            <h3 style="color: #666; margin-bottom: 10px;">No Direct Orders Found</h3>
            <p style="color: #888; font-size: 16px;">There are no direct orders available for the selected filters.</p>
        </div>
    `;
    tableWrapper.parentNode.insertBefore(noOrdersMessage, tableWrapper.nextSibling);

    // Set max date to today
    const today = new Date().toISOString().split('T')[0];
    fromDate.max = today;
    toDate.max = today;

    function filterRows() {
        const rows = document.querySelectorAll('.order-row');
        let hasVisibleRows = false;

        const selectedStatus = statusFilter.value.toLowerCase();
        const fDate = fromDate.value ? new Date(fromDate.value) : null;
        const tDate = toDate.value ? new Date(toDate.value) : null;

        rows.forEach(row => {
            const rowDate = new Date(row.getAttribute('data-date'));
            const rowStatus = row.getAttribute('data-status').toLowerCase();

            let showRow = true;

            // Check status filter
            if (selectedStatus && rowStatus !== selectedStatus) {
                showRow = false;
            }

            // Check date filter
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

        // Toggle visibility of table and no orders message
        tableWrapper.style.display = hasVisibleRows ? 'block' : 'none';
        noOrdersMessage.style.display = hasVisibleRows ? 'none' : 'block';
    }

    function showAllOrders() {
        document.querySelectorAll('.order-row').forEach(row => {
            row.style.display = '';
        });
        tableWrapper.style.display = 'block';
        noOrdersMessage.style.display = 'none';
    }

    // Event listeners for filters
    fromDate.addEventListener('change', function() {
        if (this.value) {
            toDate.min = this.value;
        } else {
            toDate.min = '';
        }
        filterRows();
    });

    toDate.addEventListener('change', function() {
        if (this.value) {
            fromDate.max = this.value;
        } else {
            fromDate.max = today;
        }
        filterRows();
    });

    statusFilter.addEventListener('change', filterRows);

    // Clear dates button handler
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
    $.ajax({
        url: 'process_direct_order.php',
        type: 'GET',
        data: {
            action: 'update_status',
            order_id: orderId,
            status: newStatus
        },
        success: function(response) {
            if (response.success) {
                // Update the UI dynamically without reload
                const row = document.querySelector(`.order-row[data-order-id='${orderId}']`);
                if (row) {
                    // Update status badge
                    const statusCell = row.querySelector('.order-status');
                    statusCell.innerHTML = `<span class='status-badge status-${newStatus}'>${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}</span>`;

                    // Update Delhivery AWB if available
                    if (response.awb_number) {
                        const awbCell = row.querySelector('.delhivery-awb');
                        if (awbCell) {
                            awbCell.innerHTML = `<span class="awb-number">${response.awb_number}</span>`;
                        }
                    }

                    // Update shipment status if available
                    if (response.shipment_status) {
                        const shipmentStatusCell = row.querySelector('.shipment-status');
                        if (shipmentStatusCell) {
                            const statusClass = `status-${response.shipment_status}`;
                            shipmentStatusCell.innerHTML = `
                                <span class="status-badge ${statusClass}" style="font-size: 11px;">
                                    ${response.shipment_status.charAt(0).toUpperCase() + response.shipment_status.slice(1)}
                                </span>
                                <br><small class="text-muted">${new Date().toLocaleDateString()}</small>
                            `;
                        }
                    }

                    // Update action column
                    const actionCell = row.querySelector('.action-column');
                    if (newStatus === 'shipped') {
                        const trackButton = response.awb_number ? 
                            `<button onclick="trackShipment(${orderId})" class="btn-status-update" style="background: #28a745; color: white; margin: 2px;">
                                <i class="fa fa-search"></i> Track Shipment
                            </button>` : '';
                        
                        actionCell.innerHTML = `
                            <div class="action-buttons">
                                <button class="btn-status-update" onclick="updateOrderStatus(${orderId}, 'delivered')">
                                    <i class="fa fa-check-circle"></i> Delivered
                                </button>
                                <button class="btn-status-update" onclick="updateOrderStatus(${orderId}, 'canceled')">
                                    <i class="fa fa-times-circle"></i> Cancelled
                                </button>
                                ${trackButton}
                                <a href="generate_invoice.php?order_id=${orderId}" 
                                   class="btn btn-sm mt-1" target="_blank" style="color: #007bff; font-weight: 600; border-radius: 4px; padding: 5px 10px; background-color: transparent; border: 1px solid #007bff;">
                                   <i class="fa fa-file-pdf-o"></i> Generate Invoice
                                </a>
                            </div>`;
                    } else if (newStatus === 'delivered' || newStatus === 'canceled') {
                        actionCell.innerHTML = `<button class="btn-status-update disabled"><i class="fa fa-lock"></i> No Actions Available</button>`;
                    } else {
                        // For other statuses, fallback to reload
                        location.reload();
                    }
                }
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while updating the order status.');
        }
    });
}

function markPacked(orderId) {
    fetch('process_shipments.php?action=mark_packed&order_id=' + orderId)
        .then(r => r.json())
        .then(d => {
            alert(d.message || (d.success ? 'Marked packed' : 'Failed'));
            if (d.success) location.reload();
        })
        .catch(e => alert('Error: ' + e.message));
}

function trackShipment(orderId) {
    $.ajax({
        url: 'process_direct_order.php',
        type: 'GET',
        data: {
            action: 'track_shipment',
            order_id: orderId
        },
        success: function(response) {
            if (response.success) {
                // Display tracking information in a modal or alert
                let trackingInfo = '';
                if (response.data && response.data.ShipmentData && response.data.ShipmentData.length > 0) {
                    const shipment = response.data.ShipmentData[0];
                    trackingInfo = `
                        <div style="text-align: left;">
                            <h4>Shipment Tracking Information</h4>
                            <p><strong>AWB Number:</strong> ${shipment.AWB || 'N/A'}</p>
                            <p><strong>Status:</strong> ${shipment.Status || 'N/A'}</p>
                            <p><strong>Location:</strong> ${shipment.Location || 'N/A'}</p>
                            <p><strong>Last Updated:</strong> ${shipment.StatusDateTime || 'N/A'}</p>
                            <p><strong>Description:</strong> ${shipment.StatusDescription || 'N/A'}</p>
                        </div>
                    `;
                } else {
                    trackingInfo = '<p>No tracking information available yet.</p>';
                }
                
                // Create and show modal
                const modal = document.createElement('div');
                modal.innerHTML = `
                    <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; display: flex; justify-content: center; align-items: center;">
                        <div style="background: white; padding: 20px; border-radius: 8px; max-width: 500px; width: 90%; max-height: 80%; overflow-y: auto;">
                            ${trackingInfo}
                            <div style="text-align: center; margin-top: 20px;">
                                <button onclick="this.parentElement.parentElement.parentElement.remove()" style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer;">Close</button>
                            </div>
                        </div>
                    </div>
                `;
                document.body.appendChild(modal);
            } else {
                alert('Error tracking shipment: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('Error tracking shipment: ' + error);
        }
    });
}

</script>
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
</script>

<?php require_once('footer.php'); ?>