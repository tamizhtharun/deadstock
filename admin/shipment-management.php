<?php
// shipment-management.php
require_once('header.php');
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Shipment Management</h1>
    </div>
</section>

<style>
.status-badge { display:inline-block; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:500; }
.status-created { background:#17a2b8; color:#fff; }
.status-pending { background:#6c757d; color:#fff; }
.status-non-serviceable { background:#dc3545; color:#fff; }
.status-processing { background:#0d6efd; color:#fff; }
.awb-number { font-family: 'Courier New', monospace; background:#e3f2fd; padding:2px 6px; border-radius:3px; font-size:12px; border:1px solid #bbdefb; }
</style>

<section class="content">
    <div class="box box-info">
        <div class="box-body table-responsive">
            <table id="example1" class="table table-bordered table-hover table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>AWB</th>
                        <th>Shipment</th>
                        <th>Seller Packed</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
<?php
$i = 0;
$stmt = $pdo->prepare("SELECT 
    o.id,
    o.order_id AS order_number,
    o.order_type,
    o.order_status,
    o.processing_time,
    o.delhivery_awb,
    o.delhivery_shipment_status,
    o.seller_packed,
    u.username, u.phone_number, u.email,
    a.address, a.city, a.state, a.pincode
FROM tbl_orders o
LEFT JOIN users u ON o.user_id = u.id
LEFT JOIN users_addresses a ON o.address_id = a.id
ORDER BY o.created_at DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($orders as $row): $i++; ?>
                    <tr data-order-id="<?php echo $row['id']; ?>">
                        <td><?php echo $i; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['order_number']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['order_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?><br><?php echo htmlspecialchars($row['phone_number']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($row['address']); ?><br>
                            <?php echo htmlspecialchars($row['city']); ?>, <?php echo htmlspecialchars($row['state']); ?><br>
                            <?php echo htmlspecialchars($row['pincode']); ?>
                        </td>
                        <td class="order-status">
                            <span class="status-badge status-<?php echo htmlspecialchars($row['order_status'] ?: 'pending'); ?>">
                                <?php echo ucfirst($row['order_status']); ?>
                            </span>
                        </td>
                        <td class="delhivery-awb">
                            <?php if (!empty($row['delhivery_awb'])): ?>
                                <span class="awb-number"><?php echo htmlspecialchars($row['delhivery_awb']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="shipment-status">
                            <?php if (!empty($row['delhivery_shipment_status'])): ?>
                                <span class="status-badge status-<?php echo htmlspecialchars($row['delhivery_shipment_status']); ?>">
                                    <?php echo ucfirst(str_replace('_',' ', $row['delhivery_shipment_status'])); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo !empty($row['seller_packed']) ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>'; ?>
                        </td>
                        <td class="action-column">
                            <div class="action-buttons">
<?php if (empty($row['delhivery_awb'])): ?>
                                <button class="btn-status-update" onclick="createShipment(<?php echo $row['id']; ?>)"><i class="fa fa-truck"></i> Create Shipment</button>
<?php else: ?>
                                <button class="btn-status-update" onclick="requestPickup(<?php echo $row['id']; ?>)"><i class="fa fa-clipboard-check"></i> Request Pickup</button>
                                <button class="btn-status-update" onclick="trackShipmentAdmin(<?php echo $row['id']; ?>)"><i class="fa fa-search"></i> Track</button>
<?php endif; ?>
                            </div>
                        </td>
                    </tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<script>
function createShipment(orderId){
  fetch('process_shipments.php?action=create_shipment&order_id='+orderId)
    .then(r=>r.json())
    .then(d=>{
      alert(d.message || (d.success?'Shipment created':'Failed'))
      if(d.success){ location.reload() }
    }).catch(e=>alert('Error: '+e.message))
}
function requestPickup(orderId){
  fetch('process_shipments.php?action=request_pickup&order_id='+orderId)
    .then(r=>r.json())
    .then(d=>{
      alert(d.message || (d.success?'Pickup requested':'Failed'))
      if(d.success){ location.reload() }
    }).catch(e=>alert('Error: '+e.message))
}
function trackShipmentAdmin(orderId){
  fetch('process_shipments.php?action=track&order_id='+orderId)
    .then(r=>r.json())
    .then(d=>{
      alert(d.success? JSON.stringify(d.data) : d.message)
    }).catch(e=>alert('Error: '+e.message))
}
</script>

<?php require_once('footer.php'); ?>


