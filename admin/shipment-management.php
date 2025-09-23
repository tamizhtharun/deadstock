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

/* Warehouse Input Styles */
.warehouse-input {
    border: 1px solid #ddd;
    border-radius: 4px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.warehouse-input:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
    outline: 0;
}

.warehouse-input.error-input {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    background-color: #fff5f5;
}

.warehouse-column {
    min-width: 150px;
    vertical-align: middle;
}

/* Ensure buttons are clickable */
.btn-create-shipment {
    cursor: pointer !important;
    pointer-events: auto !important;
    z-index: 10 !important;
    position: relative !important;
    display: inline-block !important;
    background-color: #007bff !important;
    color: white !important;
    border: 1px solid #007bff !important;
    padding: 6px 12px !important;
    border-radius: 4px !important;
    text-decoration: none !important;
    font-size: 12px !important;
    line-height: 1.5 !important;
    margin: 2px !important;
}

.btn-create-shipment:hover {
    background-color: #0056b3 !important;
    border-color: #0056b3 !important;
}

.btn-create-shipment:active {
    background-color: #004085 !important;
    border-color: #004085 !important;
}

.action-buttons {
    position: relative;
    z-index: 10;
    display: block !important;
}

.action-column {
    position: relative;
    z-index: 10;
}

/* Track Modal Styles */
#trackModal .modal-dialog {
    max-width: 800px;
}

#trackModal .modal-content {
    background-color: #ffffff;
    border-radius: 0.5rem;
}

#trackModal .modal-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    border-radius: 0.5rem 0.5rem 0 0;
}

#trackModal .modal-title {
    color: #333;
    font-weight: 600;
}

#trackModal .tracking-details {
    padding: 1rem 0;
}

#trackModal .tracking-item {
    border-left: 3px solid #007bff;
    padding-left: 1rem;
    margin-bottom: 1rem;
    background-color: #f8f9fa;
    padding: 0.75rem 1rem;
    border-radius: 0.25rem;
}

#trackModal .tracking-item.completed {
    border-left-color: #28a745;
    background-color: #d4edda;
}

#trackModal .tracking-item.current {
    border-left-color: #ffc107;
    background-color: #fff3cd;
}

#trackModal .tracking-item.pending {
    border-left-color: #6c757d;
    background-color: #e2e3e5;
}

#trackModal .tracking-item.error {
    border-left-color: #dc3545;
    background-color: #f8d7da;
}

#trackModal .tracking-status {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.25rem;
}

#trackModal .tracking-time {
    font-size: 0.875rem;
    color: #6c757d;
}

#trackModal .tracking-location {
    font-size: 0.875rem;
    color: #495057;
    margin-top: 0.25rem;
}

#trackModal .error-message {
    color: #dc3545;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    padding: 0.75rem;
    border-radius: 0.25rem;
    text-align: center;
}

/* Ensure modal stays visible */
#trackModal {
    display: block !important;
    z-index: 9999 !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
}

#trackModal .modal-dialog {
    z-index: 10000 !important;
    position: relative !important;
    margin: 1.75rem auto !important;
    max-width: 800px !important;
}

#trackModal .modal-content {
    z-index: 10001 !important;
    position: relative !important;
}

/* Spinner animation */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Ensure modal is visible */
#trackModal {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    z-index: 9999 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background-color: rgba(0, 0, 0, 0.5) !important;
}





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
                        <!-- <th>Seller Packed</th> -->
                        <th>Warehouse</th>
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
                        <!-- <td>
                            <?php echo !empty($row['seller_packed']) ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>'; ?>
                        </td> -->
                        <td class="warehouse-column">
                            <input type="text" 
                                   class="form-control warehouse-input" 
                                   data-order-id="<?php echo $row['id']; ?>" 
                                   placeholder="Enter warehouse name" 
                                   style="width: 150px; font-size: 12px; padding: 4px 8px;">
                        </td>
                        <td class="action-column">
                            <div class="action-buttons">
<?php if (empty($row['delhivery_awb'])): ?>
                                <button type="button" class="btn-status-update btn-create-shipment" data-order-id="<?php echo $row['id']; ?>" onclick="handleCreateShipment(<?php echo $row['id']; ?>)"><i class="fa fa-truck"></i> Create Shipment</button>
<?php else: ?>
                                <button class="btn-status-update" onclick="trackShipmentAdmin(<?php echo $row['id']; ?>)" onmousedown="event.preventDefault();"><i class="fa fa-search"></i> Track</button>
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

// Initialize when document is ready
$(document).ready(function() {
    console.log('Shipment management page loaded');
    console.log('Create shipment buttons found:', $('.btn-create-shipment').length);

    // Add real-time validation for warehouse inputs
    $(document).on('input', '.warehouse-input', function() {
        const input = $(this);
        const value = input.val().trim();
        
        // Remove error styling when user starts typing
        input.removeClass('error-input');
        
        // Basic validation feedback
        if (value.length > 0 && value.length < 2) {
            input.css('border-color', '#ffc107'); // Warning color
        } else if (value.length >= 2) {
            input.css('border-color', '#28a745'); // Success color
        } else {
            input.css('border-color', '#ddd'); // Default color
        }
    });

    // Add validation on blur (when user leaves the input)
    $(document).on('blur', '.warehouse-input', function() {
        const input = $(this);
        const value = input.val().trim();
        
        // Reset to default styling
        input.css('border-color', '#ddd');
        
        // Show warning if input is too short but not empty
        if (value.length > 0 && value.length < 2) {
            input.css('border-color', '#ffc107');
        }
    });


    console.log('Event handlers bound successfully');
    
    // Add event listeners for modal
    const trackModal = document.getElementById('trackModal');
    if (trackModal) {
      // Prevent modal from closing when clicking outside
      trackModal.addEventListener('click', function(e) {
        if (e.target === trackModal) {
          e.stopPropagation();
        }
      });
      
      // Prevent modal from closing with escape key
      trackModal.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          e.preventDefault();
          e.stopPropagation();
        }
      });
    }
});

// Main function for button clicks
function handleCreateShipment(orderId) {
    
    // Get warehouse input
    const row = document.querySelector(`button[data-order-id="${orderId}"]`).closest('tr');
    const warehouseInput = row.querySelector('.warehouse-input');
    const warehouseName = warehouseInput ? warehouseInput.value.trim() : '';
    
    
    if (!warehouseName) {
        alert('❌ Please enter a warehouse name in the warehouse column before creating shipment.');
        if (warehouseInput) {
            warehouseInput.focus();
            warehouseInput.style.borderColor = '#dc3545';
            warehouseInput.style.backgroundColor = '#fff5f5';
        }
        return;
    }

    if (warehouseName.length < 2) {
        alert('❌ Warehouse name must be at least 2 characters long.');
        if (warehouseInput) {
            warehouseInput.focus();
            warehouseInput.style.borderColor = '#dc3545';
            warehouseInput.style.backgroundColor = '#fff5f5';
        }
        return;
    }

    // Show loading state
    const button = document.querySelector(`button[data-order-id="${orderId}"]`);
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Creating...';
    button.disabled = true;
    
    // Create shipment
    fetch(`process_shipments.php?action=create_shipment&order_id=${orderId}&warehouse_name=${encodeURIComponent(warehouseName)}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(d => {
            // Reset button state
            button.innerHTML = originalText;
            button.disabled = false;

            if (d.success) {
                alert('✅ ' + (d.message || 'Shipment created successfully'));
                location.reload();
            } else {
                let errorMessage = d.message || 'Failed to create shipment';
                if (errorMessage.toLowerCase().includes('warehouse') || 
                    errorMessage.toLowerCase().includes('pickup') ||
                    errorMessage.toLowerCase().includes('location')) {
                    errorMessage = '❌ Warehouse Error: ' + errorMessage + '\n\nPlease check if the warehouse name is correct and exists in your Delhivery dashboard.';
                } else {
                    errorMessage = '❌ ' + errorMessage;
                }
                alert(errorMessage);
            }
        })
        .catch(e => {
            // Reset button state
            button.innerHTML = originalText;
            button.disabled = false;
            
            let errorMessage = 'Error creating shipment: ' + e.message;
            if (e.message.includes('Failed to fetch') || e.message.includes('NetworkError')) {
                errorMessage = '❌ Network Error: Unable to connect to the server. Please check your internet connection and try again.';
            } else if (e.message.includes('HTTP error')) {
                errorMessage = '❌ Server Error: ' + e.message + '\n\nPlease try again later or contact support if the problem persists.';
            } else {
                errorMessage = '❌ ' + errorMessage;
            }
            
            alert(errorMessage);
        });
}

// Function to show enhanced error messages
function showError(message, inputElement) {
    // Remove any existing error styling
    inputElement.removeClass('error-input');
    
    // Add error styling
    inputElement.addClass('error-input');
    
    // Show error message
    alert('❌ ' + message);
    
    // Focus on the input
    inputElement.focus();
    
    // Remove error styling after 3 seconds
    setTimeout(() => {
        inputElement.removeClass('error-input');
    }, 3000);
}






function trackShipmentAdmin(orderId){
  console.log('Track button clicked for order:', orderId);
  
  // Prevent default behavior
  if (event) {
    event.preventDefault();
    event.stopPropagation();
  }
  
  // Test: Show alert first to confirm function is called
  alert('Track button clicked! Order ID: ' + orderId);
  
  // Test: Also log to console
  console.log('Creating modal for order:', orderId);
  
  // Create a completely new modal element to avoid conflicts
  createAndShowModal(orderId);
}

function createAndShowModal(orderId) {
  // Remove any existing modal
  const existingModal = document.getElementById('trackModal');
  if (existingModal) {
    existingModal.remove();
  }
  
  // Remove any existing backdrop
  const existingBackdrop = document.getElementById('trackModalBackdrop');
  if (existingBackdrop) {
    existingBackdrop.remove();
  }
  
  // Create new modal HTML with simpler structure
  const modalHTML = `
    <div id="trackModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 9999; display: flex; align-items: center; justify-content: center; background-color: rgba(0,0,0,0.5);">
      <div style="background: white; border-radius: 8px; max-width: 800px; width: 90%; max-height: 80%; overflow-y: auto; position: relative; z-index: 10000;">
        <div style="padding: 20px; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center;">
          <h5 style="margin: 0; font-weight: 600;">Shipment Tracking Details</h5>
          <button type="button" onclick="closeTrackModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <div style="padding: 20px;">
          <div id="trackingContent">
            <div style="text-align: center;">
              <div style="display: inline-block; width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #007bff; border-radius: 50%; animation: spin 1s linear infinite;"></div>
              <p style="margin-top: 10px;">Loading tracking information...</p>
            </div>
          </div>
        </div>
        <div style="padding: 20px; border-top: 1px solid #dee2e6; text-align: right;">
          <button type="button" onclick="closeTrackModal()" style="background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">Close</button>
        </div>
      </div>
    </div>
  `;
  
  // Add modal to body
  document.body.insertAdjacentHTML('beforeend', modalHTML);
  
  // Prevent body scrolling
  document.body.style.overflow = 'hidden';
  
  // Add click handler to modal background to close modal
  const modalElement = document.getElementById('trackModal');
  modalElement.addEventListener('click', function(e) {
    if (e.target === modalElement) {
      closeTrackModal();
    }
  });
  
  // Add escape key handler
  document.addEventListener('keydown', handleEscapeKey);
  
  // Fetch tracking data after a short delay
  setTimeout(() => {
    fetchTrackingData(orderId);
  }, 500);
}

function fetchTrackingData(orderId) {
  console.log('Fetching tracking data for order:', orderId);
  
  // Fetch tracking data
  fetch('process_shipments.php?action=track&order_id='+orderId)
    .then(r=>r.json())
    .then(d=>{
      console.log('Raw API response:', d);
      console.log('Response type:', typeof d);
      console.log('Success:', d.success);
      console.log('Data:', d.data);
      console.log('Message:', d.message);
      
      if(d.success) {
        // Handle different response formats
        if(d.data) {
          displayTrackingData(d.data);
        } else if(d.message) {
          // Sometimes the message contains the tracking info
          displayTrackingMessage(d.message);
        } else {
          displayTrackingError('No tracking data available');
        }
      } else {
        displayTrackingError(d.message || 'Failed to fetch tracking information');
      }
    }).catch(e=>{
      console.error('Tracking fetch error:', e);
      displayTrackingError('Error: ' + e.message);
    });
}

function displayTrackingData(data) {
  console.log('Displaying tracking data:', data);
  let html = '<div class="tracking-details">';
  
  if (Array.isArray(data) && data.length > 0) {
    data.forEach((item, index) => {
      console.log('Processing tracking item:', item);
      const statusClass = getStatusClass(item.status || item.Status || item.state);
      const time = item.time || item.timestamp || item.Time || item.date || 'N/A';
      const location = item.location || item.city || item.Location || item.City || item.origin || item.destination || 'N/A';
      const status = item.status || item.Status || item.state || item.State || 'Unknown Status';
      
      html += `
        <div class="tracking-item ${statusClass}">
          <div class="tracking-status">${status}</div>
          <div class="tracking-time">${time}</div>
          <div class="tracking-location">${location}</div>
        </div>
      `;
    });
  } else if (typeof data === 'object' && data !== null) {
    console.log('Processing single tracking object:', data);
    // Handle single tracking object
    const statusClass = getStatusClass(data.status || data.Status || data.state);
    const time = data.time || data.timestamp || data.Time || data.date || 'N/A';
    const location = data.location || data.city || data.Location || data.City || data.origin || data.destination || 'N/A';
    const status = data.status || data.Status || data.state || data.State || 'Unknown Status';
    
    html += `
      <div class="tracking-item ${statusClass}">
        <div class="tracking-status">${status}</div>
        <div class="tracking-time">${time}</div>
        <div class="tracking-location">${location}</div>
      </div>
    `;
  } else if (typeof data === 'string') {
    // Handle string response
    html += `
      <div class="tracking-item">
        <div class="tracking-status">${data}</div>
        <div class="tracking-time">N/A</div>
        <div class="tracking-location">N/A</div>
      </div>
    `;
  } else {
    // Show raw data as fallback
    html += `
      <div class="tracking-item">
        <div class="tracking-status">Raw Data</div>
        <div class="tracking-time">${JSON.stringify(data)}</div>
        <div class="tracking-location">N/A</div>
      </div>
    `;
  }
  
  html += '</div>';
  document.getElementById('trackingContent').innerHTML = html;
}

function displayTrackingMessage(message) {
  console.log('Displaying tracking message:', message);
  let html = '<div class="tracking-details">';
  
  // Try to parse JSON if it's a string
  if (typeof message === 'string') {
    try {
      const parsed = JSON.parse(message);
      if (Array.isArray(parsed)) {
        displayTrackingData(parsed);
        return;
      } else if (typeof parsed === 'object') {
        displayTrackingData(parsed);
        return;
      }
    } catch (e) {
      // Not JSON, treat as plain text
    }
  }
  
  // Display as plain text
  html += `
    <div class="tracking-item">
      <div class="tracking-status">${message}</div>
      <div class="tracking-time">N/A</div>
      <div class="tracking-location">N/A</div>
    </div>
  `;
  
  html += '</div>';
  document.getElementById('trackingContent').innerHTML = html;
}

function displayTrackingError(message) {
  document.getElementById('trackingContent').innerHTML = `
    <div class="error-message">
      <i class="fa fa-exclamation-triangle"></i>
      ${message}
    </div>
  `;
}

function getStatusClass(status) {
  if (!status) return 'pending';
  
  const statusLower = status.toLowerCase();
  
  // Completed/Delivered statuses
  if (statusLower.includes('delivered') || 
      statusLower.includes('completed') || 
      statusLower.includes('delivery completed') ||
      statusLower.includes('successfully delivered') ||
      statusLower.includes('delivered to recipient')) {
    return 'completed';
  } 
  // In transit/Processing statuses
  else if (statusLower.includes('in transit') || 
           statusLower.includes('out for delivery') || 
           statusLower.includes('processing') ||
           statusLower.includes('picked up') ||
           statusLower.includes('in route') ||
           statusLower.includes('on the way') ||
           statusLower.includes('dispatched') ||
           statusLower.includes('shipped')) {
    return 'current';
  } 
  // Pending/Initial statuses
  else if (statusLower.includes('pending') ||
           statusLower.includes('created') ||
           statusLower.includes('initiated') ||
           statusLower.includes('booked') ||
           statusLower.includes('accepted')) {
    return 'pending';
  } 
  // Error/Failed statuses
  else if (statusLower.includes('failed') ||
           statusLower.includes('error') ||
           statusLower.includes('cancelled') ||
           statusLower.includes('rejected') ||
           statusLower.includes('returned')) {
    return 'error';
  }
  // Default to current for unknown statuses
  else {
    return 'current';
  }
}

// Function to close tracking modal
function closeTrackModal() {
  console.log('Closing tracking modal');
  
  try {
    // Remove modal
    const modalElement = document.getElementById('trackModal');
    if (modalElement) {
      modalElement.remove();
    }
    
    // Restore body scrolling
    document.body.style.overflow = 'auto';
    
    // Remove escape key listener
    document.removeEventListener('keydown', handleEscapeKey);
    
    console.log('Modal closed successfully');
  } catch (error) {
    console.error('Error closing modal:', error);
  }
}

// Handle escape key
function handleEscapeKey(e) {
  if (e.key === 'Escape') {
    e.preventDefault();
    closeTrackModal();
  }
}


</script>

<?php require_once('footer.php'); ?>




