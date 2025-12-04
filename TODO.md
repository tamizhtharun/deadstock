# TODO: Modify Direct Orders Page and Invoice Generation

## Tasks
- [ ] Modify the SQL query in admin/direct-order.php to group orders by order_id and collect all products for each order
- [ ] Update the table display in admin/direct-order.php to show grouped orders with all product names listed and remove product photos
- [ ] Update the CSV export logic in admin/direct-order.php to handle grouped data
- [ ] Modify admin/generate_invoice.php to fetch and display all products for the given order_id
- [ ] Test the changes to ensure orders are grouped correctly and invoices list all products
