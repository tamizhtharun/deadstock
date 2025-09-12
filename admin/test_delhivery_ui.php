<?php
require_once('header.php');
require_once('../config/delhivery_config.php');
require_once('../services/DelhiveryService.php');
?>

<section class="content-header">
    <div class="content-header-left">
        <h1>Delhivery Integration Test</h1>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-info">
                <div class="box-header with-border">
                    <h3 class="box-title">Test Delhivery Integration</h3>
                </div>
                <div class="box-body">
                    <div class="alert alert-info">
                        <h4><i class="icon fa fa-info"></i> Current Configuration</h4>
                        <p><strong>Environment:</strong> <?php echo strtoupper(DELHIVERY_ENVIRONMENT); ?></p>
                        <p><strong>Auth Type:</strong> <?php echo DELHIVERY_AUTH_TYPE; ?></p>
                        <p><strong>API Base URL:</strong> <?php echo DELHIVERY_API_BASE_URL; ?></p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Test Shipment Creation</h3>
                                </div>
                                <div class="box-body">
                                    <form id="testShipmentForm">
                                        <div class="form-group">
                                            <label>Customer Name:</label>
                                            <input type="text" class="form-control" id="customerName" value="Test Customer" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Address:</label>
                                            <input type="text" class="form-control" id="address" value="123 Test Street" required>
                                        </div>
                                        <div class="form-group">
                                            <label>City:</label>
                                            <input type="text" class="form-control" id="city" value="Delhi" required>
                                        </div>
                                        <div class="form-group">
                                            <label>State:</label>
                                            <input type="text" class="form-control" id="state" value="Delhi" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Pincode:</label>
                                            <input type="text" class="form-control" id="pincode" value="110001" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Phone:</label>
                                            <input type="text" class="form-control" id="phone" value="9999999999" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Amount:</label>
                                            <input type="number" class="form-control" id="amount" value="100" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa fa-truck"></i> Create Test Shipment
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="box box-success">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Test Results</h3>
                                </div>
                                <div class="box-body">
                                    <div id="testResults">
                                        <p class="text-muted">Click "Create Test Shipment" to test the integration.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="box box-warning">
                                <div class="box-header with-border">
                                    <h3 class="box-title">API Logs</h3>
                                </div>
                                <div class="box-body">
                                    <div id="apiLogs" style="max-height: 300px; overflow-y: auto; background: #f5f5f5; padding: 10px; border-radius: 4px;">
                                        <p class="text-muted">API logs will appear here...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.getElementById('testShipmentForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = {
        reference_no: 'TEST_' + Date.now(),
        name: document.getElementById('customerName').value,
        address: document.getElementById('address').value,
        city: document.getElementById('city').value,
        state: document.getElementById('state').value,
        pincode: document.getElementById('pincode').value,
        phone: document.getElementById('phone').value,
        cod_amount: document.getElementById('amount').value,
        declared_value: document.getElementById('amount').value
    };

    document.getElementById('testResults').innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Creating shipment...</div>';

    fetch('process_direct_order.php?action=create_test_shipment', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('testResults').innerHTML = `
                <div class="alert alert-success">
                    <h4><i class="fa fa-check"></i> Shipment Created Successfully!</h4>
                    <p><strong>AWB Number:</strong> <code>${data.awb_number || 'N/A'}</code></p>
                    <p><strong>Status:</strong> ${data.shipment_status || 'N/A'}</p>
                    <p><strong>Response:</strong></p>
                    <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 12px;">${JSON.stringify(data.data, null, 2)}</pre>
                </div>
            `;
        } else {
            document.getElementById('testResults').innerHTML = `
                <div class="alert alert-danger">
                    <h4><i class="fa fa-times"></i> Shipment Creation Failed</h4>
                    <p><strong>Error:</strong> ${data.message}</p>
                </div>
            `;
        }
        
        // Update API logs
        updateApiLogs();
    })
    .catch(error => {
        document.getElementById('testResults').innerHTML = `
            <div class="alert alert-danger">
                <h4><i class="fa fa-times"></i> Error</h4>
                <p>${error.message}</p>
            </div>
        `;
    });
});

function updateApiLogs() {
    fetch('get_api_logs.php')
    .then(response => response.text())
    .then(data => {
        document.getElementById('apiLogs').innerHTML = data;
    })
    .catch(error => {
        console.error('Error fetching logs:', error);
    });
}

// Update logs on page load
updateApiLogs();
</script>

<?php require_once('footer.php'); ?>

