<?php require_once('header.php'); ?>
<?php
$seller_id = $_SESSION['seller_session']['seller_id'];

// Get seller status
$statement = $pdo->prepare("SELECT seller_status FROM sellers WHERE seller_id = ?");
$statement->execute([$seller_id]);
$seller_status = $statement->fetchColumn();

if ($seller_status == 0) {
    header('Location: profile-edit.php');
    exit;
}

// Handle bulk upload
if (isset($_POST['form_bulk_upload'])) {
    $valid = 1;
    $errors = [];
    $success_count = 0;
    $error_count = 0;

    // Check if file is uploaded
    if (!isset($_FILES['bulk_file']) || $_FILES['bulk_file']['error'] != 0) {
        $valid = 0;
    } else {
        $file = $_FILES['bulk_file'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Check file type - only CSV for now
        if ($file_ext != 'csv') {
            $valid = 0;
        }
    }

    if ($valid == 1) {
        try {
            // Process CSV file
            $handle = fopen($file['tmp_name'], 'r');
            if ($handle === false) {
                throw new Exception("Unable to open file");
            }

            // Skip header row
            fgetcsv($handle);

            $row_num = 2; // Start from row 2 (after header)
            while (($row = fgetcsv($handle)) !== false) {
                // Validate required fields - only basic fields
                $product_name = trim($row[0] ?? '');
                $old_price = trim($row[1] ?? '');
                $current_price = trim($row[2] ?? '');
                $quantity = trim($row[3] ?? '');

                $row_errors = [];

                // Validate product name
                if (empty($product_name)) {
                    $row_errors[] = "Product name is required";
                }

                // Validate prices
                if (!empty($current_price) && (!is_numeric($current_price) || $current_price <= 0)) {
                    $row_errors[] = "Current price must be a positive number";
                }

                if (!empty($old_price) && (!is_numeric($old_price) || $old_price <= 0)) {
                    $row_errors[] = "Old price must be a positive number";
                }

                // Validate quantity
                if (!empty($quantity) && (!is_numeric($quantity) || $quantity <= 0)) {
                    $row_errors[] = "Quantity must be a positive number";
                }

                if (empty($row_errors)) {
                    // Get next product ID
                    $stmt = $pdo->prepare("SHOW TABLE STATUS LIKE 'tbl_product'");
                    $stmt->execute();
                    $result = $stmt->fetchAll();
                    foreach ($result as $row) {
                        $ai_id = $row[10];
                    }

                    // Insert basic product data only
                    $stmt = $pdo->prepare("INSERT INTO tbl_product (
                        seller_id, p_name, p_old_price, p_current_price, p_qty,
                        p_total_view, p_date, p_is_approve
                    ) VALUES (?, ?, ?, ?, ?, 0, ?, 0)");

                    $stmt->execute([
                        $seller_id,
                        $product_name,
                        $old_price ?: NULL,
                        $current_price ?: NULL,
                        $quantity ?: NULL,
                        date('Y-m-d H:i:s')
                    ]);

                    $success_count++;
                } else {
                    $errors[] = "Row $row_num: " . implode(", ", $row_errors);
                    $error_count++;
                }

                $row_num++;
            }

            fclose($handle);

        } catch (Exception $e) {
            // Error handling without message display
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Product Upload</title>
    <style>
        .upload-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .template-download {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .instructions {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .instructions h4 {
            margin-top: 0;
            color: #0c5460;
        }
    </style>
</head>
<body>
<section class="content-header">
    <div class="content-header-left">
        <h1>Bulk Product Upload</h1>
    </div>
    <div class="content-header-right">
        <a href="product.php" class="btn btn-primary btn-sm">View All Products</a>
    </div>
</section>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <?php if (isset($_POST['form_bulk_upload'])): ?>
                <div class="alert alert-success">
                    <h4>Upload Results</h4>
                    <p>Successfully uploaded: <?php echo $success_count; ?> products</p>
                    <?php if ($error_count > 0): ?>
                        <p>Errors: <?php echo $error_count; ?> rows failed</p>
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="upload-section">
                <div class="template-download">
                    <h4><i class="fa fa-download"></i> Download Template</h4>
                    <p>Download the CSV template file and fill in basic product information:</p>
                    <a href="download-template.php" class="btn btn-info btn-sm">
                        <i class="fa fa-download"></i> Download CSV Template
                    </a>
                </div>

                <div class="instructions">
                    <h4><i class="fa fa-info-circle"></i> Instructions</h4>
                    <ol>
                        <li>Download the CSV template file</li>
                        <li>Fill in only the basic information: Product Name, Old Price, Current Price, Quantity</li>
                        <li>Save the file and upload it below</li>
                        <li>Review the results and fix any errors</li>
                        <li><strong>After upload, edit each product individually to add categories, brand, HSN code, GST percentage, description, photos, and catalogue</strong></li>
                        <li><strong>Go to Product List and click "Edit" on each product to complete the details</strong></li>
                    </ol>
                    <strong>Current Fields:</strong> Product Name, Old Price, Current Price, Quantity<br>
                    <strong>Note:</strong> All other product details (categories, brand, HSN, GST, description, photos, catalogue) must be added by editing each product after bulk upload.
                </div>

                <form action="" method="post" enctype="multipart/form-data">
                    <div class="box box-info">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="bulk_file" class="col-sm-3 control-label">Upload File <span>*</span></label>
                                <div class="col-sm-4">
                                    <input type="file" name="bulk_file" id="bulk_file" accept=".csv" required>
                                    <p class="help-block">Supported format: CSV (.csv)</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-3 control-label"></label>
                                <div class="col-sm-6">
                                    <button type="submit" class="btn btn-success pull-left" name="form_bulk_upload">
                                        <i class="fa fa-upload"></i> Upload Basic Product Data
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
// File validation
var bulkFileInput = document.getElementById('bulk_file');
if (bulkFileInput) {
    bulkFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const maxSize = 5 * 1024 * 1024; // 5MB

            if (!file.name.match(/\.csv$/)) {
                alert('Please select a valid CSV file.');
                e.target.value = '';
                return;
            }

            if (file.size > maxSize) {
                alert('File size must be less than 5MB.');
                e.target.value = '';
                return;
            }
        }
    });
}
</script>
</body>
</html>
<?php require_once('footer.php'); ?>
