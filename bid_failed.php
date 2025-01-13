<?php
// bid_failed.php
session_start();
$error = isset($_GET['error']) ? $_GET['error'] : 'Payment failed';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Bid Failed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">Payment Failed</h4>
            <p><?php echo htmlspecialchars($error); ?></p>
            <hr>
            <p class="mb-0">
                <a href="javascript:history.back()" class="btn btn-primary">Try Again</a>
                <a href="index.php" class="btn btn-secondary">Back to Home</a>
            </p>
        </div>
    </div>
</body>
</html>