<?php
session_start();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Bid Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Payment Successful!</h4>
            <p>Your bid has been successfully placed and payment has been processed.</p>
            <hr>
            <p class="mb-0">
                <a href="index.php" class="btn btn-primary">Back to Home</a>
            </p>
        </div>
    </div>
</body>

</html>