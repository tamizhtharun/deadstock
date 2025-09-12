<?php
// Simple Delhivery API tester (staging/production based on config)
// Provides: Pincode Serviceability, Create Shipment, Track Shipment

require_once(__DIR__ . '/../../services/DelhiveryService.php');
require_once(__DIR__ . '/../../config/delhivery_config.php');

header('Content-Type: text/html; charset=utf-8');

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }

$svc = new DelhiveryService();
$action = $_POST['action'] ?? '';
$result = null;
$error = null;

try {
    if ($action === 'check_pincode') {
        $pincode = trim($_POST['pincode'] ?? '');
        $result = $svc->checkPincodeService($pincode);
    } elseif ($action === 'create_shipment') {
        $shipmentData = [
            'reference_no'   => trim($_POST['reference_no'] ?? ('TEST-' . time())),
            'name'           => trim($_POST['name'] ?? ''),
            'address'        => trim($_POST['address'] ?? ''),
            'city'           => trim($_POST['city'] ?? ''),
            'state'          => trim($_POST['state'] ?? ''),
            'pincode'        => trim($_POST['pincode'] ?? ''),
            'phone'          => trim($_POST['phone'] ?? ''),
            'email'          => trim($_POST['email'] ?? 'customer@example.com'),
            'cod_amount'     => trim($_POST['cod_amount'] ?? '0'),
            'declared_value' => trim($_POST['declared_value'] ?? '0'),
        ];
        $result = $svc->createShipment($shipmentData);
    } elseif ($action === 'track') {
        $awb = trim($_POST['awb'] ?? '');
        $result = $svc->trackShipment($awb);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Delhivery API Tester</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 24px; }
    fieldset { margin-bottom: 20px; }
    label { display: block; margin: 6px 0 4px; font-weight: 600; }
    input, textarea { width: 100%; max-width: 520px; padding: 8px; }
    .row { display: flex; gap: 16px; flex-wrap: wrap; }
    .col { flex: 1 1 240px; min-width: 240px; }
    .env { padding: 8px 12px; background: #f0f4ff; display: inline-block; border-radius: 4px; margin-bottom: 16px; }
    .result { white-space: pre-wrap; background: #0f172a; color: #e2e8f0; padding: 12px; border-radius: 6px; overflow: auto; }
    button { padding: 8px 12px; cursor: pointer; }
  </style>
  <script>
    function fillStagingDefaults() {
      document.getElementById('name').value = 'Test Customer';
      document.getElementById('address').value = '123 Test Street, Test Area';
      document.getElementById('city').value = 'Chennai';
      document.getElementById('state').value = 'Tamil Nadu';
      document.getElementById('pincode').value = '600001';
      document.getElementById('phone').value = '9876543210';
      document.getElementById('email').value = 'customer@example.com';
      document.getElementById('cod_amount').value = '0';
      document.getElementById('declared_value').value = '1000';
      document.getElementById('reference_no').value = 'TEST-' + Date.now();
    }
  </script>
  </head>
<body>
  <div class="env">Environment: <?php echo h(strtoupper(DELHIVERY_ENVIRONMENT)); ?> | Auth: <?php echo h(DELHIVERY_AUTH_TYPE); ?></div>

  <form method="post" style="margin-bottom:24px;">
    <fieldset>
      <legend>Check Pincode Serviceability</legend>
      <div class="row">
        <div class="col">
          <label for="pincode">Pincode</label>
          <input id="pincode" name="pincode" placeholder="e.g. 600001" />
        </div>
      </div>
      <button type="submit" name="action" value="check_pincode">Check Service</button>
    </fieldset>
  </form>

  <form method="post" style="margin-bottom:24px;">
    <fieldset>
      <legend>Create Shipment</legend>
      <div class="row">
        <div class="col">
          <label for="reference_no">Reference No</label>
          <input id="reference_no" name="reference_no" placeholder="TEST-<timestamp>" />
        </div>
        <div class="col">
          <label for="name">Customer Name</label>
          <input id="name" name="name" />
        </div>
      </div>
      <div class="row">
        <div class="col">
          <label for="address">Address</label>
          <input id="address" name="address" />
        </div>
      </div>
      <div class="row">
        <div class="col">
          <label for="city">City</label>
          <input id="city" name="city" />
        </div>
        <div class="col">
          <label for="state">State</label>
          <input id="state" name="state" />
        </div>
        <div class="col">
          <label for="pincode">Pincode</label>
          <input id="pincode" name="pincode" />
        </div>
      </div>
      <div class="row">
        <div class="col">
          <label for="phone">Phone (10 digits)</label>
          <input id="phone" name="phone" />
        </div>
        <div class="col">
          <label for="email">Email</label>
          <input id="email" name="email" />
        </div>
      </div>
      <div class="row">
        <div class="col">
          <label for="cod_amount">COD Amount</label>
          <input id="cod_amount" name="cod_amount" />
        </div>
        <div class="col">
          <label for="declared_value">Declared Value</label>
          <input id="declared_value" name="declared_value" />
        </div>
      </div>
      <div style="margin-top:8px;">
        <button type="button" onclick="fillStagingDefaults()">Fill 600001 Staging Defaults</button>
        <button type="submit" name="action" value="create_shipment">Create Shipment</button>
      </div>
    </fieldset>
  </form>

  <form method="post" style="margin-bottom:24px;">
    <fieldset>
      <legend>Track Shipment</legend>
      <div class="row">
        <div class="col">
          <label for="awb">AWB</label>
          <input id="awb" name="awb" placeholder="e.g. 8466581xxxxxxx" />
        </div>
      </div>
      <button type="submit" name="action" value="track">Track</button>
    </fieldset>
  </form>

  <?php if ($error): ?>
    <h3>Error</h3>
    <div class="result"><?php echo h($error); ?></div>
  <?php endif; ?>

  <?php if ($result !== null): ?>
    <h3>Result</h3>
    <div class="result"><?php echo h(json_encode($result, JSON_PRETTY_PRINT)); ?></div>
  <?php endif; ?>

  <h3>Config Snapshot</h3>
  <div class="result"><?php echo h(json_encode([
    'environment' => DELHIVERY_ENVIRONMENT,
    'auth_type' => DELHIVERY_AUTH_TYPE,
    'base_url' => DELHIVERY_API_BASE_URL,
  ], JSON_PRETTY_PRINT)); ?></div>

</body>
</html>



