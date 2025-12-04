<?php
// Create CSV content
$csv_content = "Product Name,Old Price,New Price,Quantity\n";

// Add example rows


// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=product_upload_template.csv');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

// Output CSV content
echo $csv_content;
exit;
?>
