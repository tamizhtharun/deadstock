<?php
// Include database connection
require_once 'db_connection.php';
include 'header.php';

$query = "SELECT user_tc, seller_tc FROM tbl_settings";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$user_tc = $row['user_tc'] ?? "No terms available.";
$seller_tc = $row['seller_tc'] ?? "No terms available.";
$source = isset($_GET['source']) ? $_GET['source'] : '';

$show_user_tc = ($source === 'user' || empty($source));
$show_seller_tc = ($source === 'seller');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms & Conditions</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            color: #333;
        }

        .container {
            max-width: 850px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .nav-tabs .nav-link {
            color: #555;
            background-color: #f1f1f1;
            border: none;
            font-weight: 500;
            padding: 10px 20px;
        }

        .nav-tabs .nav-link.active {
            color: #007bff;
            background-color: #fff;
            border-bottom: 3px solid #007bff;
        }

        .nav-tabs {
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
        }

        .content-box {
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 6px;
            color: #666;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        h2 {
            text-align: center;
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 30px;
            font-weight: 600;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Terms & Conditions</h2>

        <!-- Tabs for User & Seller -->
        <ul class="nav nav-tabs" id="tcTabs">
            <?php if ($show_user_tc): ?>
                <li class="nav-item">
                    <a data-bs-toggle="tab" href="#userTC"></a>
                </li>
            <?php endif; ?>
            <?php if ($show_seller_tc): ?>
                <li class="nav-item">
                    <a data-bs-toggle="tab" href="#sellerTC"></a>
                </li>
            <?php endif; ?>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content">
            <?php if ($show_user_tc): ?>
                <div class="tab-pane fade show active" id="userTC">
                    <div class="content-box"><?php echo nl2br(htmlspecialchars($user_tc)); ?></div>
                </div>
            <?php endif; ?>
            <?php if ($show_seller_tc): ?>
                <div class="tab-pane fade show active" id="sellerTC">
                    <div class="content-box"><?php echo nl2br(htmlspecialchars($seller_tc)); ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>