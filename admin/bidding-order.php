<?php 
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
            <select id="statusFilter" class="form-control input-sm" style="display: inline-block; width: auto; margin-left: 10px;">
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
                            <?php if(empty($row['order_status'])): ?>
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
                            <?php elseif($row['order_status'] === 'pending'): ?>
                                <button class="btn-status-update disabled">
                                    <i class="fa fa-clock-o"></i> Waiting for Seller
                                </button>
                            <?php elseif($row['order_status'] !== 'delivered' && $row['order_status'] !== 'canceled'): ?>
                                <div class="action-buttons">
                                    <?php
                                        $next_statuses = [];
                                        switch($row['order_status']) {
                                            case 'processing':
                                                $next_statuses = ['shipped', 'canceled'];
                                                break;
                                            case 'shipped':
                                                $next_statuses = ['delivered', 'canceled'];
                                                break;
                                        }
                                        foreach($next_statuses as $next_status):
                                    ?>
                                        <button 
                                            class="btn-status-update" 
                                            onclick="updateOrderStatus(<?php echo $row['order_table_id']; ?>, '<?php echo $next_status; ?>')"
                                        >
                                            <?php
                                                $icon = '';
                                                switch($next_status) {
                                                    case 'shipped': $icon = 'fa-truck'; break;
                                                    case 'delivered': $icon = 'fa-check-circle'; break;
                                                    case 'canceled': $icon = 'fa-times-circle'; break;
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

<div id="imageModal" class="modal">
    <span class="close-modal">&times;</span>
    <div class="modal-container">
        <img id="modalImage" class="modal-content">
    </div>
</div>


<script src="./js/bidding-order.js"></script>
<?php require_once('footer.php'); ?>

