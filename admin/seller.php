<?php require_once('header.php'); ?>
<section class="content-header">
    <div class="content-header-left">
        <h1>Seller - Completed Profile</h1>
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
                    <label>Filter by Status:</label>
                    <div class="dropdown" style="display: inline-block; margin-left: 10px;">
                        <button class="btn btn-default btn-sm dropdown-toggle" type="button" id="statusTypeDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            All Status
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="statusTypeDropdown">
                            <li><a href="#" class="status-type-option" data-value="">All Status</a></li>
                            <li><a href="#" class="status-type-option" data-value="Active">Active</a></li>
                            <li><a href="#" class="status-type-option" data-value="Inactive">Inactive</a></li>
                        </ul>
                    </div>
                </div>
                <input type="hidden" id="orderTypeFilter">
            </div>
            <div class="box box-info">
                <div class="box-body table-responsive">
                    <table id="example1" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th width="5px">#</th>
                                <th width="180">Name</th>
                                <th width="150">Email Address</th>
                                <th width="180">Address</th>
                                <th width="80">Status</th>
                                <th width="150">Registration Date</th> <!-- New Column -->
                                <th width="100">Change Status</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $statement = $pdo->prepare("SELECT *, DATE(created_at) as registration_date 
                                            FROM sellers 
                                            WHERE seller_name IS NOT NULL
                                                AND seller_cname IS NOT NULL
                                                AND seller_email IS NOT NULL
                                                AND seller_phone IS NOT NULL
                                                AND seller_gst IS NOT NULL
                                                AND seller_address IS NOT NULL
                                                AND seller_state IS NOT NULL
                                                AND seller_city IS NOT NULL
                                                AND seller_zipcode IS NOT NULL
                                                AND seller_password IS NOT NULL
                                                AND account_number IS NOT NULL
                                                AND ifsc_code IS NOT NULL
                                                AND bank_name IS NOT NULL
                                                AND bank_branch IS NOT NULL
                                                AND bank_address IS NOT NULL
                                                AND bank_city IS NOT NULL
                                                AND bank_state IS NOT NULL
                                                AND account_holder IS NOT NULL
                ");
                            $statement->execute();
                            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($result as $row) {
                                $i++;
                            ?>
                                <tr class="<?php echo ($row['seller_status'] == 1) ? 'bg-g' : 'bg-r'; ?>">
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo htmlspecialchars($row['seller_name']); ?>
                                        <div>
                                            <a href="javascript:void(0);" onclick="openSellerModal(<?php echo $row['seller_id']; ?>)">View Seller Details</a>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['seller_email']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($row['seller_address']); ?>
                                    </td>
                                    <td><?php echo ($row['seller_status'] == 1) ? 'Active' : 'Inactive'; ?></td>
                                    <td><?php echo $row['registration_date']; ?></td> <!-- Display Registration Date -->
                                    <td>
                                        <?php if ($row['seller_status'] == 0) { ?>
                                            <a href="seller-change-status.php?id=<?php echo $row['seller_id']; ?>" class="btn btn-warning btn-xs">Rejected</a>
                                        <?php } else { ?>
                                            <a href="seller-change-status.php?id=<?php echo $row['seller_id']; ?>" class="btn btn-success btn-xs">Approved</a>
                                        <?php } ?>
                                    </td>

                                    <td>
                                        <a href="#" class="btn btn-danger btn-xs" data-href="seller-delete.php?id=<?php echo $row['seller_id']; ?>" data-toggle="modal" data-target="#confirm-delete">Delete</a>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div id="no-bids-message" class="no-bids-container" style="display: none;">
                    <div style="text-align: center; padding: 40px 20px;">
                        <div class="no-data-icon">
                            <i class="fa fa-search" style="font-size: 64px; color: #ccc; margin-bottom: 20px;"></i>
                        </div>
                        <h3 style="color: #666; margin-bottom: 10px;">No Data Found</h3>
                        <p style="color: #888; font-size: 16px;">There are no other data available for the selected filters.</p>
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

<!-- <button class="seller-btn seller-primary">View Full Details</button> -->

<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="myModalLabel">Delete Confirmation</h4>
            </div>
            <div class="modal-body">
                <p>Are you sure want to delete this item?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <a class="btn btn-danger btn-ok">Delete</a>
            </div>
        </div>
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
</script>

<!-- Required Scripts -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fromDate = document.getElementById('fromDate');
        const toDate = document.getElementById('toDate');
        const clearDatesBtn = document.getElementById('clearDates');
        const orderTypeFilter = document.getElementById('orderTypeFilter');
        const noBidsMessage = document.getElementById('no-bids-message');
        const statusTypeDropdown = document.getElementById('statusTypeDropdown');

        // Set today's date as max for date inputs
        const today = new Date().toISOString().split('T')[0];
        fromDate.max = today;
        toDate.max = today;

        let table = $('#example1').DataTable({
            "paging": true,
            "ordering": true,
            "info": true
        });

        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            let orderDate = new Date(data[5]); // Registration date column
            let rowOrderType = data[4].toLowerCase(); // Status column

            let fDate = fromDate.value ? new Date(fromDate.value) : null;
            let tDate = toDate.value ? new Date(toDate.value) : null;
            let orderTypeFilterValue = orderTypeFilter.value.toLowerCase();
            let showRow = true;

            if (orderTypeFilterValue && rowOrderType !== orderTypeFilterValue) {
                showRow = false;
            }

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

        function updateSerialNumbers() {
            table.rows({
                filter: 'applied'
            }).nodes().each(function(row, index) {
                $(row).find('td:first').text(index + 1);
            });
        }

        function applyFilters() {
            table.draw();
            updateSerialNumbers();

            let visibleRows = table.rows({
                filter: 'applied'
            }).count();
            let totalRows = table.data().count();

            noBidsMessage.style.display = (visibleRows === 0 || totalRows === 0) ? 'block' : 'none';
        }

        fromDate.addEventListener('change', applyFilters);
        toDate.addEventListener('change', applyFilters);

        clearDatesBtn.addEventListener('click', function() {
            fromDate.value = '';
            toDate.value = '';
            orderTypeFilter.value = '';
            statusTypeDropdown.textContent = "All status";
            applyFilters();
        });

        document.querySelectorAll('.status-type-option').forEach(option => {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                statusTypeDropdown.textContent = this.textContent;
                orderTypeFilter.value = this.getAttribute('data-value');
                applyFilters();
            });
        });

        table.on('draw', function() {
            updateSerialNumbers();
        });
    });
</script>
<?php require_once('footer.php'); ?>