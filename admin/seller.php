<?php require_once('header.php'); ?>

<section class="content-header">
	<div class="content-header-left">
		<h1>Sellers - Completed Profiles</h1>
	</div>
</section>

<section class="content">
	<div class="row">
		<div class="col-md-12">
			<div class="box box-info">
				<div class="box-body table-responsive">
					<table id="example1" class="table table-bordered table-hover table-striped">
						<thead>
							<tr>
								<th width="5px">#</th>
								<th width="180">Name</th>
								<th width="150">Email Address</th>
								<th width="180">Address</th>
								<th>Status</th>
								<th width="100">Change Status</th>
								<th width="100">Action</th>
							</tr>
						</thead>
						<tbody>
							<?php
							$i=0;
							$statement = $pdo->prepare("SELECT * 
														FROM sellers t1 WHERE seller_name IS NOT NULL
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
								<tr class="<?php if($row['seller_status']==1) {echo 'bg-g';}else {echo 'bg-r';} ?>">
									<td><?php echo $i; ?></td>
									<td><?php echo $row['seller_name']; ?>
									<div>
											<a href="javascript:void(0);" onclick="openSellerModal(<?php echo $row['seller_id']; ?>)">View Seller Details</a>
									</div>
								  </td>
									<td><?php echo $row['seller_email']; ?></td>
									<td>
										<?php echo $row['seller_address']; ?><br>
										<!-- <?php echo $row['cust_city']; ?><br>
										<?php echo $row['cust_state']; ?> -->
									</td>
									<td><?php if($row['seller_status']==1) {echo 'Active';} else {echo 'Inactive';} ?></td>
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

<?php require_once('footer.php'); ?>