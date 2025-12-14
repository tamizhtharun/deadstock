# TODO: Add View Invoice button with print/download options on the invoice page

## Tasks
- [x] Modify admin/download_invoice.php to accept invoice_number instead of order_id
- [x] Add single "View Invoice" button to actions column in admin/settlement_invoices.php
- [x] Add print and download buttons to the generate_invoice.php page
- [ ] Test the new buttons functionality

## Details
- Single "View Invoice" button links to generate_invoice.php?invoice_number=...
- Print and Download buttons are now on the invoice viewing page itself
- Print button uses window.print() JavaScript
- Download button links to download_invoice.php?invoice_number=...
- Buttons are positioned fixed in top-right corner of invoice page
