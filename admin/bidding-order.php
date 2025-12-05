<<<<<<< HEAD
<?php
//bidding-order.php
require_once('header.php');
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Bidding Orders</h1>
    </div>
    <!-- <div class="content-header-right">
      <a href="process_bid_order.php?action=sendall" class="btn btn-success" onclick="return confirm('Are you sure you want to send all orders to sellers?')">Send All Orders</a>
  </div> -->
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
                        <option value="">All Orders</option>
                        <option value="not_sent">Not Sent Orders</option>
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
                        <thead>
                            <tr>
                                <th>#</th>
                                <th width="120">Order ID</th>
                                <th width="200">Product</th>
                                <th width="100">Seller Details</th>
                                <th>Winning User Details</th>
                                <th width="80">Amount</th>
                                <th>User Address</th>
                                <th>Status</th>
                                <th>Processing Time</th>
                                <th>Tracking ID</th>
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
                    o.order_status,
                    o.id AS order_table_id,
                    o.updated_at,
                    o.processing_time,
                    o.tracking_id
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
                    tbl_orders o ON b.bid_id = o.bid_id
                WHERE 
                    b.bid_status = 2
                  OR
                    o.order_type ='bid'
                ORDER BY 
                    b.bid_time DESC");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($result as $row):
                                $i++;
                            ?>
                                <tr class="bid-order-row" data-order-id="<?php echo $row['order_table_id']; ?>" data-date="<?php echo $row['bid_date']; ?>" data-status="<?php echo empty($row['order_status']) ? 'not_sent' : $row['order_status']; ?>">
                                    <td><?php echo $i; ?></td>
                                    <td>
                                        <strong><?php echo $row['order_id']; ?></strong><br>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($row['bid_date'])); ?></small>
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
                                        Price: ₹<?php echo number_format($row['bid_price'], 2); ?><br>
                                        Qty: <?php echo $row['bid_quantity']; ?><br>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['address'])): ?>
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
                                    <td class="processing-time">
                                        <?php
                                        if (!empty($row['processing_time'])) {
                                            echo date('Y-m-d H:i:s', strtotime($row['processing_time']));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="tracking-id">
                                        <?php echo !empty($row['tracking_id']) ? $row['tracking_id'] : '-'; ?>
                                    </td>
                                    <td class="action-column">
<?php if (!empty($row['order_status']) && $row['order_status'] === 'shipped'): ?>
<a href="generate_invoice.php?order_id=<?php echo $row['order_table_id']; ?>"
   class="btn btn-sm btn-primary mt-1" style="color: #007bff; font-weight: 600; border-radius: 4px; padding: 5px 10px; background-color: transparent; border: 1px solid #007bff;">
   <i class="fa fa-file-pdf-o"></i> Generate Invoice
</a>
<?php endif; ?>
                                        <?php if (empty($row['order_status'])): ?>
                                            <button
                                                class="btn btn-primary btn-sm send-order-btn"
                                                data-bid-id="<?php echo $row['bid_id']; ?>"
                                                data-product-id="<?php echo $row['product_id']; ?>"
                                                data-user-id="<?php echo $row['user_id']; ?>"
                                                data-seller-id="<?php echo $row['seller_id']; ?>"
                                                data-quantity="<?php echo $row['bid_quantity']; ?>"
                                                data-price="<?php echo $row['bid_price']; ?>"
                                                data-order-id="<?php echo $row['order_table_id']; ?>"
                                                onclick="sendOrder(this)">
                                                Send
                                            </button>
                                        <?php elseif ($row['order_status'] === 'pending'): ?>
                                            <button class="btn-status-update disabled">
                                                <i class="fa fa-clock-o"></i> Waiting for Seller
                                            </button>
                                        <?php elseif ($row['order_status'] !== 'delivered' && $row['order_status'] !== 'canceled'): ?>
                                            <div class="action-buttons">
                                                <?php
                                                $next_statuses = [];
                                                switch ($row['order_status']) {
                                                    case 'processing':
                                                        $next_statuses = ['shipped', 'canceled'];
                                                        break;
                                                    case 'shipped':
                                                        $next_statuses = ['delivered', 'canceled'];
                                                        break;
                                                }
                                                foreach ($next_statuses as $next_status):
                                                ?>
                                                    <button
                                                        class="btn-status-update"
                                                        onclick="updateOrderStatus(<?php echo $row['order_table_id']; ?>, '<?php echo $next_status; ?>')">
                                                        <?php
                                                        $icon = '';
                                                        switch ($next_status) {
                                                            case 'shipped':
                                                                $icon = 'fa-truck';
                                                                break;
                                                            case 'delivered':
                                                                $icon = 'fa-check-circle';
                                                                break;
                                                            case 'canceled':
                                                                $icon = 'fa-times-circle';
                                                                break;
                                                        }
                                                        ?>
                                                        <i class="fa <?php echo $icon; ?>"></i>
                                                        <?php echo ucfirst($next_status); ?>
                                                    </button>
                                                <?php endforeach; ?>
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

    // Update order status function for bidding orders
    function updateOrderStatus(orderId, newStatus) {
        $.ajax({
            url: 'process_bid_order.php',
            type: 'GET',
            data: {
                action: 'update_status',
                order_id: orderId,
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    const row = document.querySelector(`.bid-order-row[data-order-id='${orderId}']`);
                    if (row) {
                        // Update status badge
                        const statusCell = row.querySelector('.order-status');
                        statusCell.innerHTML = `<span class='status-badge status-${newStatus}'>${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}</span>`;

                        // Update action column
                        const actionCell = row.querySelector('.action-column');
if (newStatus === 'shipped') {
    actionCell.innerHTML = `
        <div class="action-buttons" style="display: flex; flex-direction: column; gap: 8px;">
            <button 
                class="btn-status-update"
                onclick="updateOrderStatus(${orderId}, 'delivered')">
                <i class="fa fa-check-circle"></i> Delivered
            </button>
            <button 
                class="btn-status-update"
                onclick="updateOrderStatus(${orderId}, 'canceled')">
                <i class="fa fa-times-circle"></i> Cancelled
            </button>
        </div>
        <a href="generate_invoice.php?order_id=${orderId}"
            class="btn btn-sm mt-1" style="color: #007bff; font-weight: 600; border-radius: 4px; padding: 5px 10px; background-color: transparent; border: 1px solid #007bff; display: block; margin-top: 8px;">
            <i class="fa fa-file-pdf-o"></i> Generate Invoice
        </a>
    `;
                        } else if (newStatus === 'delivered' || newStatus === 'canceled') {
                            actionCell.innerHTML = `<button class="btn-status-update disabled"><i class="fa fa-lock"></i> No Actions Available</button>`;
                        } else {
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
</script>
=======
<?php
//bidding-order.php
require_once('../db_connection.php');

// Check if export_csv button is clicked - must be before any output
if (isset($_POST['export_csv'])) {
    // Build query with filters
    $query = "SELECT
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
        ua.id AS address_id,
        ua.full_name,
        ua.phone_number as delivery_phone,
        ua.address,
        ua.city,
        ua.state,
        ua.pincode,
        DATE(b.bid_time) as bid_date,
        b.bid_status,
        o.order_status,
        o.id AS order_table_id,
        o.updated_at,
        o.processing_time,
        o.tracking_id,
        o.delhivery_awb,
        o.delhivery_shipment_status
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
        tbl_orders o ON b.bid_id = o.bid_id
    WHERE
        (b.bid_status = 2 OR o.order_type ='bid')";

    $params = array();

    // Apply status filter
    if (!empty($_POST['status_filter'])) {
        if ($_POST['status_filter'] == 'not_sent') {
            $query .= " AND o.order_status IS NULL";
        } else {
            $query .= " AND o.order_status = ?";
            $params[] = $_POST['status_filter'];
        }
    }

    // Apply date filter
    if (!empty($_POST['from_date']) && !empty($_POST['to_date'])) {
        $query .= " AND DATE(b.bid_time) BETWEEN ? AND ?";
        $params[] = $_POST['from_date'];
        $params[] = $_POST['to_date'];
    } elseif (!empty($_POST['from_date'])) {
        $query .= " AND DATE(b.bid_time) >= ?";
        $params[] = $_POST['from_date'];
    } elseif (!empty($_POST['to_date'])) {
        $query .= " AND DATE(b.bid_time) <= ?";
        $params[] = $_POST['to_date'];
    }

    $query .= " ORDER BY b.bid_time DESC";

    try {
        $statement = $pdo->prepare($query);
        $statement->execute($params);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Set headers for CSV download after successful query
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bidding-orders.csv"');

        // Output CSV data
        $output = fopen('php://output', 'w');

        // Write headers
        fputcsv($output, array('#', 'Order ID', 'Product', 'Seller Details', 'Customer Details', 'Amount', 'Quantity', 'Delivery Address', 'Status', 'Delhivery AWB', 'Shipment Status'));

        // Write data
        $i = 0;
        foreach ($result as $row) {
            $i++;
            $total = $row['bid_price'] * $row['bid_quantity'];
            $amount = number_format($total, 0);
            $delivery_address = !empty($row['address']) ?
                $row['full_name'] . ', ' . $row['delivery_phone'] . ', ' . $row['address'] . ', ' . $row['city'] . ', ' . $row['state'] . ', ' . $row['pincode'] :
                'Address not available';
            $status = !empty($row['order_status']) ? ucfirst($row['order_status']) : 'Not sent to seller';
            $awb = !empty($row['delhivery_awb']) ? "'" . $row['delhivery_awb'] : '-';
            $shipment_status = !empty($row['delhivery_shipment_status']) ? ucfirst($row['delhivery_shipment_status']) : '-';

            fputcsv($output, array(
                $i,
                $row['order_id'],
                $row['p_name'],
                $row['seller_name'], // Only seller name
                $row['full_name'], // Only customer name
                $amount,
                $row['bid_quantity'],
                $delivery_address,
                $status,
                $awb,
                $shipment_status
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
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Bidding Orders</h1>
    </div>
    <!-- <div class="content-header-right">
      <a href="process_bid_order.php?action=sendall" class="btn btn-success" onclick="return confirm('Are you sure you want to send all orders to sellers?')">Send All Orders</a>
  </div> -->
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
                        <option value="">All Orders</option>
                        <option value="not_sent">Not Sent Orders</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                    </select>
                </div>
                <div class="export-group">
                    <form method="POST" action="" id="exportForm">
                        <input type="hidden" name="status_filter" id="hiddenStatusFilter">
                        <input type="hidden" name="from_date" id="hiddenFromDate">
                        <input type="hidden" name="to_date" id="hiddenToDate">
                        <button type="submit" name="export_csv" class="btn btn-primary btn-xs">Export to CSV</button>
                    </form>
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
                <div class="box-body table-responsive" id="bidding-order-table-container">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th width="120">Order ID</th>
                                <th width="200">Product</th>
                                <th width="100">Seller Details</th>
                                <th>Winning User Details</th>
                                <th width="80">Amount</th>
                                <th>User Address</th>
                                <th>Status</th>
                                <th>Processing Time</th>
                                <th>AWB</th>
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
                    ua.id AS address_id,
                    ua.full_name,
                    ua.phone_number as delivery_phone,
                    ua.address,
                    ua.city,
                    ua.state,
                    ua.pincode,
                    DATE(b.bid_time) as bid_date,
                    b.bid_status,
                    o.order_status,
                    o.id AS order_table_id,
                    o.updated_at,
                    o.processing_time,
                    o.tracking_id,
                    o.delhivery_awb,
                    o.delhivery_shipment_status
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
                    tbl_orders o ON b.bid_id = o.bid_id
                WHERE
                    b.bid_status = 2
                  OR
                    o.order_type ='bid'
                ORDER BY
                    b.bid_time DESC");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($result as $row):
                                $i++;
                            ?>
                                <tr class="bid-order-row" data-order-id="<?php echo $row['order_table_id']; ?>" data-date="<?php echo $row['bid_date']; ?>" data-status="<?php echo empty($row['order_status']) ? 'not_sent' : $row['order_status']; ?>">
                                    <td><?php echo $i; ?></td>
                                    <td>
                                        <strong><?php echo $row['order_id']; ?></strong><br>
                                        <small class="text-muted"><?php echo date('M d, Y', strtotime($row['bid_date'])); ?></small>
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
                                        Price: ₹<?php echo number_format($row['bid_price'], 2); ?><br>
                                        Qty: <?php echo $row['bid_quantity']; ?><br>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['address'])): ?>
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
                                    <td class="processing-time">
                                        <?php
                                        if (!empty($row['processing_time'])) {
                                            echo date('Y-m-d H:i:s', strtotime($row['processing_time']));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="awb-number">
                                        <?php
                                        // Prefer Delhivery AWB from orders table if available
                                        if (!empty($row['delhivery_awb'])) {
                                            echo htmlspecialchars($row['delhivery_awb']);
                                        } elseif (!empty($row['tracking_id'])) {
                                            echo htmlspecialchars($row['tracking_id']);
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="action-column">
<?php if (!empty($row['order_status']) && $row['order_status'] === 'shipped'): ?>
<a href="generate_invoice.php?order_id=<?php echo $row['order_table_id']; ?>"
   class="btn btn-sm btn-primary mt-1" style="color: #007bff; font-weight: 600; border-radius: 4px; padding: 5px 10px; background-color: transparent; border: 1px solid #007bff;">
   <i class="fa fa-file-pdf-o"></i> Generate Invoice
</a>
<?php endif; ?>
                                        <?php if (empty($row['order_status'])): ?>
                                            <button
                                                class="btn btn-primary btn-sm send-order-btn"
                                                data-bid-id="<?php echo $row['bid_id']; ?>"
                                                data-product-id="<?php echo $row['product_id']; ?>"
                                                data-user-id="<?php echo $row['user_id']; ?>"
                                                data-seller-id="<?php echo $row['seller_id']; ?>"
                                                data-quantity="<?php echo $row['bid_quantity']; ?>"
                                                data-price="<?php echo $row['bid_price']; ?>"
                                                data-address-id="<?php echo $row['address_id']; ?>"
                                                data-order-id="<?php echo $row['order_table_id']; ?>"
                                                onclick="sendOrder(this)">
                                                Send
                                            </button>
                                        <?php elseif ($row['order_status'] === 'pending'): ?>
                                            <button class="btn-status-update disabled">
                                                <i class="fa fa-clock-o"></i> Waiting for Seller
                                            </button>
                                        <?php elseif ($row['order_status'] !== 'delivered' && $row['order_status'] !== 'canceled'): ?>
                                            <div class="action-buttons">
                                                <?php
                                                $next_statuses = [];
                                                switch ($row['order_status']) {
                                                    case 'processing':
                                                        $next_statuses = ['shipped', 'canceled'];
                                                        break;
                                                    case 'shipped':
                                                        $next_statuses = ['delivered', 'canceled'];
                                                        break;
                                                }
                                                foreach ($next_statuses as $next_status):
                                                ?>
                                                    <button
                                                        class="btn-status-update"
                                                        onclick="updateOrderStatus(<?php echo $row['order_table_id']; ?>, '<?php echo $next_status; ?>')">
                                                        <?php
                                                        $icon = '';
                                                        switch ($next_status) {
                                                            case 'shipped':
                                                                $icon = 'fa-truck';
                                                                break;
                                                            case 'delivered':
                                                                $icon = 'fa-check-circle';
                                                                break;
                                                            case 'canceled':
                                                                $icon = 'fa-times-circle';
                                                                break;
                                                        }
                                                        ?>
                                                        <i class="fa <?php echo $icon; ?>"></i>
                                                        <?php echo ucfirst($next_status); ?>
                                                    </button>
                                                <?php endforeach; ?>
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

    // Function to open seller modal
    function openSellerModal(sellerId) {
        const modal = document.getElementById('sellerModal');
        const profileTab = document.getElementById('profile');

        // Reset tabs to profile
        sellerTabButtons.forEach(b => b.classList.remove('active'));
        sellerTabPanes.forEach(p => p.classList.remove('active'));
        document.querySelector('.seller-tab-button[data-tab="profile"]').classList.add('active');
        profileTab.classList.add('active');

        // Fetch seller data
        fetch(`get_seller_details.php?seller_id=${sellerId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Populate profile tab
                    profileTab.innerHTML = `
                        <div class="seller-info-grid">
                            <div class="seller-info-item">
                                <strong>Name:</strong> ${data.seller.seller_name}
                            </div>
                            <div class="seller-info-item">
                                <strong>Company:</strong> ${data.seller.seller_cname}
                            </div>
                            <div class="seller-info-item">
                                <strong>Email:</strong> ${data.seller.seller_email}
                            </div>
                            <div class="seller-info-item">
                                <strong>Phone:</strong> ${data.seller.seller_phone}
                            </div>
                            <div class="seller-info-item">
                                <strong>Address:</strong> ${data.seller.seller_address}
                            </div>
                            <div class="seller-info-item">
                                <strong>Status:</strong> ${data.seller.seller_status}
                            </div>
                        </div>
                    `;

                    // Populate other tabs similarly if needed
                    // For brevity, assuming profile is main

                    modal.classList.add('show');
                    document.body.style.overflow = 'hidden';
                } else {
                    alert('Failed to load seller details: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to load seller details.');
            });
    }

    // Function to close seller modal
    function closeSellerModal() {
        const modal = document.getElementById('sellerModal');
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
    }

    // Event listeners for modal close
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('sellerModal');
        const closeBtn = document.querySelector('.seller-close');
        const closeFooterBtn = document.getElementById('closeSellerModal');

        modal.addEventListener('click', function(e) {
            if (e.target === modal || e.target.classList.contains('seller-modal')) {
                closeSellerModal();
            }
        });

        closeBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeSellerModal();
        });

        closeFooterBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            closeSellerModal();
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeSellerModal();
            }
        });
    });

    // Function to open image modal
    function openImageModal(imgSrc) {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');

        modal.classList.add('show');
        modalImg.src = imgSrc;
        document.body.style.overflow = 'hidden';
    }

    // Function to close image modal
    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        const modalImg = document.getElementById('modalImage');

        modal.classList.remove('show');
        setTimeout(() => {
            modalImg.src = '';
        }, 300);
        document.body.style.overflow = 'auto';
    }

    // Event listeners for image modal
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

    // sendOrder function to handle sending orders
    window.sendOrder = function(button) {
        if (!confirm("Are you sure you want to send this order to seller?")) {
            return;
        }

        const data = button.dataset;
        const url = `process_bid_order.php?action=send&bid_id=${data.bidId}&product_id=${data.productId}&user_id=${data.userId}&seller_id=${data.sellerId}&quantity=${data.quantity}&price=${data.price}&address_id=${data.addressId}`;

        fetch(url)
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    const row = button.closest("tr");
                    const statusCell = row.querySelector(".order-status");
                    const actionCell = row.querySelector(".action-column");

                    statusCell.innerHTML = '<span class="status-badge status-pending">Pending</span>';
                    row.setAttribute("data-status", "pending");
                    row.setAttribute("data-order-id", data.order_id);

                    alert("Order sent to seller successfully.");
                    location.reload(); // Reload the page
                } else {
                    alert("Failed to send order: " + data.message);
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("Failed to send order. Please try again.");
            });
    };

    // Update order status function for bidding orders
    function updateOrderStatus(orderId, newStatus) {
        $.ajax({
            url: 'process_bid_order.php',
            type: 'GET',
            data: {
                action: 'update_status',
                order_id: orderId,
                status: newStatus
            },
            success: function(response) {
                if (response.success) {
                    const row = document.querySelector(`.bid-order-row[data-order-id='${orderId}']`);
                    if (row) {
                        // Update status badge
                        const statusCell = row.querySelector('.order-status');
                        statusCell.innerHTML = `<span class='status-badge status-${newStatus}'>${newStatus.charAt(0).toUpperCase() + newStatus.slice(1)}</span>`;

                        // Update action column
                        const actionCell = row.querySelector('.action-column');
if (newStatus === 'shipped') {
    actionCell.innerHTML = `
        <div class="action-buttons" style="display: flex; flex-direction: column; gap: 8px;">
            <button 
                class="btn-status-update"
                onclick="updateOrderStatus(${orderId}, 'delivered')">
                <i class="fa fa-check-circle"></i> Delivered
            </button>
            <button 
                class="btn-status-update"
                onclick="updateOrderStatus(${orderId}, 'canceled')">
                <i class="fa fa-times-circle"></i> Cancelled
            </button>
        </div>
        <a href="generate_invoice.php?order_id=${orderId}"
            class="btn btn-sm mt-1" style="color: #007bff; font-weight: 600; border-radius: 4px; padding: 5px 10px; background-color: transparent; border: 1px solid #007bff; display: block; margin-top: 8px;">
            <i class="fa fa-file-pdf-o"></i> Generate Invoice
        </a>
    `;
                        } else if (newStatus === 'delivered' || newStatus === 'canceled') {
                            actionCell.innerHTML = `<button class="btn-status-update disabled"><i class="fa fa-lock"></i> No Actions Available</button>`;
                        } else {
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

    // Handle CSV export form submission
    document.getElementById('exportForm').addEventListener('submit', function(e) {
        // Populate hidden fields with current filter values
        document.getElementById('hiddenStatusFilter').value = document.getElementById('statusFilter').value;
        document.getElementById('hiddenFromDate').value = document.getElementById('fromDate').value;
        document.getElementById('hiddenToDate').value = document.getElementById('toDate').value;
    });

</script>
>>>>>>> main
<?php require_once('footer.php'); ?>