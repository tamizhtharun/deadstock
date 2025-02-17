<?php require_once('header.php'); ?>
<section class="content-header">
    <div class="content-header-left">
        <h1>Order History</h1>
    </div>
</section>
<style>
    .filter-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .order-type-filter-group,
    .status-filter-group {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
    }

    .order-type-filter-group label,
    .status-filter-group label {
        white-space: nowrap;
    }

    .dropdown {
        position: relative;
    }

    .dropdown-menu {
        display: none;
        position: absolute;
        left: 0;
        top: 100%;
        z-index: 1000;
        background: white;
        border: 1px solid #ccc;
        box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        min-width: 150px;
        /* Default width */
        max-width: 200px;
        /* Prevents excessive width */
        font-size: 14px;
        /* Improves readability */
    }

    .dropdown:hover .dropdown-menu {
        display: block;
    }

    @media (max-width: 768px) {
        .filter-container {
            display: flex;
            flex-direction: column;
            gap: 10px;
            width: 100%;
            align-items: center;
        }

        .date-filter-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
        }

        .date-filter-group label {
            text-align: center;
            font-weight: bold;
        }

        .date-filter-group input {
            width: 90%;
            max-width: 250px;
            margin: 5px 0;
        }

        /* Reduced width for Clear Dates button */
        .date-filter-group button {
            width: 50%;
            max-width: 150px;
            margin-top: 5px;
        }

        /* Grid layout for Order Type and Status filters */
        .order-status-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            width: 100%;
        }

        .order-type-filter-group,
        .status-filter-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
        }

        .order-type-filter-group label,
        .status-filter-group label {
            font-weight: bold;
        }

        .dropdown {
            width: auto;
            max-width: 200px;
        }

        /* Reduced width for dropdown buttons */
        .dropdown button {
            width: 93%;
            max-width: 180px;
            text-align: center;
            font-size: 14px;
            padding: 8px 12px;
        }

        .dropdown-menu {
            width: auto;
            min-width: 120px;
            font-size: 13px;
        }

        .dropdown-menu li a {
            padding: 8px 10px;
        }
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
                <div class="order-type-filter-group">
                    <label>Filter by order type:</label>
                    <div class="dropdown" style="display: inline-block; margin-left: 10px;">
                        <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="orderTypeDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            All Orders
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="orderTypeDropdown">
                            <li><a href="#" class="order-type-option" data-value="">All Orders</a></li>
                            <li><a href="#" class="order-type-option" data-value="bid">Bid</a></li>
                            <li><a href="#" class="order-type-option" data-value="direct">Direct</a></li>
                        </ul>
                    </div>
                </div>
                <input type="hidden" id="orderTypeFilter">
                <div class="status-filter-group">
                    <label>Filter by status:</label>
                    <div class="dropdown" style="display: inline-block; margin-left: 10px;">
                        <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="statusDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            All Orders
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="statusDropdown">
                            <li><a href="#" class="status-option" data-value="">All Orders</a></li>
                            <li><a href="#" class="status-option" data-value="pending">Pending</a></li>
                            <li><a href="#" class="status-option" data-value="processing">Processing</a></li>
                            <li><a href="#" class="status-option" data-value="shipped">Shipped</a></li>
                            <li><a href="#" class="status-option" data-value="delivered">Delivered</a></li>
                            <li><a href="#" class="status-option" data-value="canceled">Canceled</a></li>
                        </ul>
                    </div>
                </div>
                <input type="hidden" id="statusFilter">
            </div>
            <div class="box box-info">
                <div class="box-body table-responsive" id="bidding-order-table-container">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>S.No.</th>
                                <th>Product</th>
                                <th>Order ID</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Order Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $statement = $pdo->prepare("
                            SELECT o.id, o.order_id, p.p_name, p.p_featured_photo, 
                                   o.quantity, (o.price * o.quantity) AS total_price, 
                                   o.order_status AS status, DATE(o.created_at) AS order_date, 
                                   o.order_type
                            FROM tbl_orders o
                            JOIN tbl_product p ON o.product_id = p.id
                            WHERE o.seller_id = :seller_id
                            ORDER BY o.created_at DESC
                        ");
                            $statement->execute([':seller_id' => $seller_id]);
                            $orders = $statement->fetchAll(PDO::FETCH_ASSOC);
                            $serialNumber = 1;

                            if (count($orders) > 0):
                                foreach ($orders as $order): ?>
                                    <tr class="bid-order-row"
                                        data-date="<?php echo $order['order_date']; ?>"
                                        data-status="<?php echo $order['status']; ?>">
                                        <td><?php echo $serialNumber++; ?></td>
                                        <td>
                                            <img src="assets/uploads/<?php echo htmlspecialchars($order['p_featured_photo']); ?>" width="50" height="50" alt="Product Image">
                                            <span><?php echo htmlspecialchars($order['p_name']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($order['order_id']); ?></td>
                                        <td><?php echo number_format($order['quantity']); ?></td>
                                        <td>₹<?php echo number_format($order['total_price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($order['status']); ?></td>
                                        <td><?php echo $order['order_date']; ?></td>
                                        <td><?php echo $order['order_type']; ?></td>
                                    </tr>
                            <?php endforeach;
                            endif;
                            ?>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get references to various DOM elements
        const fromDate = document.getElementById('fromDate');
        const toDate = document.getElementById('toDate');
        const clearDatesBtn = document.getElementById('clearDates');
        const statusFilter = document.getElementById('statusFilter');
        const orderTypeFilter = document.getElementById('orderTypeFilter');
        const noBidsMessage = document.getElementById('no-bids-message');
        const statusDropdown = document.getElementById('statusDropdown');
        const orderTypeDropdown = document.getElementById('orderTypeDropdown');

        // Set today's date as max for date inputs
        const today = new Date().toISOString().split('T')[0];
        fromDate.max = today;
        toDate.max = today;

        // Initialize DataTable with basic configurations
        let table = $('#example1').DataTable({
            "paging": true,
            "ordering": true,
            "info": true
        });

        // Custom filtering function to filter table based on date range, status, and order type
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            let orderDate = new Date(data[6]); // Extract order date from table row (7th column)
            let rowStatus = data[5].toLowerCase(); // Extract order status (6th column)
            let rowOrderType = data[7].toLowerCase(); // Extract order type (8th column)

            let fDate = fromDate.value ? new Date(fromDate.value) : null;
            let tDate = toDate.value ? new Date(toDate.value) : null;

            // Adjust date range to include full day
            if (fDate) fDate.setHours(0, 0, 0, 0);
            if (tDate) tDate.setHours(23, 59, 59, 999);

            let statusFilterValue = statusFilter.value.toLowerCase();
            let orderTypeFilterValue = orderTypeFilter.value.toLowerCase();
            let showRow = true;

            // Filter by status
            if (statusFilterValue && rowStatus !== statusFilterValue) {
                showRow = false;
            }

            // Filter by order type
            if (orderTypeFilterValue && rowOrderType !== orderTypeFilterValue) {
                showRow = false;
            }

            // Filter by date range
            if (showRow) {
                if (fDate && !tDate) {
                    showRow = orderDate.getTime() >= fDate.getTime();
                } else if (!fDate && tDate) {
                    showRow = orderDate.getTime() <= tDate.getTime();
                } else if (fDate && tDate) {
                    showRow = orderDate.getTime() >= fDate.getTime() && orderDate.getTime() <= tDate.getTime();
                }
            }

            return showRow;
        });

        // Function to update serial numbers in the filtered table
        function updateSerialNumbers() {
            table.rows({
                filter: 'applied'
            }).nodes().each(function(row, index) {
                $(row).find('td:first').text(index + 1);
            });
        }

        // Function to apply filters and refresh the table
        function applyFilters() {
            table.draw(); // Reapply filters
            updateSerialNumbers(); // Update row numbering

            let visibleRows = table.rows({
                filter: 'applied'
            }).count();
            let totalRows = table.data().count(); // Check total data count

            // Show "No Orders Found" message only if there are no visible rows OR no data exists
            if (visibleRows === 0 || totalRows === 0) {
                noBidsMessage.style.display = 'block';
            } else {
                noBidsMessage.style.display = 'none';
            }
        }

        // Event listeners for filtering actions
        fromDate.addEventListener('change', applyFilters);
        toDate.addEventListener('change', applyFilters);
        statusFilter.addEventListener('change', applyFilters);
        orderTypeFilter.addEventListener('change', applyFilters);

        // Reset filters when "Clear Dates" button is clicked
        clearDatesBtn.addEventListener('click', function() {
            fromDate.value = '';
            toDate.value = '';
            statusFilter.value = '';
            orderTypeFilter.value = '';
            statusDropdown.textContent = "All Orders";
            orderTypeDropdown.textContent = "All Types";
            applyFilters();
        });

        // Update status filter when a status option is clicked
        document.querySelectorAll('.status-option').forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                statusDropdown.textContent = this.textContent;
                statusFilter.value = this.getAttribute('data-value');
                applyFilters();
            });
        });

        // Update order type filter when an order type option is clicked
        document.querySelectorAll('.order-type-option').forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                orderTypeDropdown.textContent = this.textContent;
                orderTypeFilter.value = this.getAttribute('data-value');
                applyFilters();
            });
        });

        // Update serial numbers after table is redrawn
        table.on('draw', function() {
            updateSerialNumbers();
        });
    });
</script>

<?php require_once('footer.php'); ?>