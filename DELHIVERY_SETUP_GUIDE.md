# Delhivery Integration Setup Guide

This guide will help you set up the Delhivery API integration for your Deadstock e-commerce platform.

## Prerequisites

1. **Delhivery Account**: You need an active Delhivery account with API access
2. **API Credentials**: Obtain your API token and client ID from Delhivery
3. **Database Access**: Ensure you have access to modify the database schema

## Step 1: Configure Delhivery API Credentials

1. Open `config/delhivery_config.php`
2. Replace the placeholder values with your actual Delhivery credentials:

```php
define('DELHIVERY_API_TOKEN', 'YOUR_ACTUAL_API_TOKEN_HERE');
define('DELHIVERY_CLIENT_ID', 'YOUR_ACTUAL_CLIENT_ID_HERE');
```

3. Update the default settings with your store information:

```php
define('DELHIVERY_DEFAULT_SETTINGS', [
    'payment_mode' => 'Prepaid',
    'product_type' => 'Non-Document',
    'product_category' => 'Non-Document',
    'package_type' => 'Non-Document',
    'reference_no' => '',
    'cod_amount' => 0,
    'declared_value' => 0,
    'name' => 'Your Store Name',
    'company_name' => 'Your Company Name',
    'address' => 'Your Store Address',
    'city' => 'Your City',
    'state' => 'Your State',
    'pincode' => '110001',
    'phone' => '9999999999',
    'email' => 'store@yourdomain.com'
]);
```

## Step 2: Update Database Schema

Run the SQL commands in `database/update_delhivery_schema.sql` to add the necessary tables and columns:

```sql
-- Add Delhivery-related columns to tbl_orders table
ALTER TABLE tbl_orders 
ADD COLUMN delhivery_awb VARCHAR(50) NULL AFTER tracking_id,
ADD COLUMN delhivery_pickup_token VARCHAR(100) NULL AFTER delhivery_awb,
ADD COLUMN delhivery_shipment_status VARCHAR(50) NULL AFTER delhivery_pickup_token,
ADD COLUMN delhivery_created_at TIMESTAMP NULL AFTER delhivery_shipment_status,
ADD COLUMN delhivery_updated_at TIMESTAMP NULL AFTER delhivery_created_at;

-- Create additional tables for tracking history and pickup requests
-- (See the full SQL file for complete commands)
```

## Step 3: Test the Integration

1. **Test API Connection**: Create a test order and try to update its status to "shipped"
2. **Check Logs**: Monitor the log file at `logs/delhivery_api.log` for any errors
3. **Verify Database**: Check that AWB numbers are being stored in the `delhivery_awb` column

## Step 4: Configure Logging (Optional)

If you want to enable API logging:

1. Set `DELHIVERY_LOG_ENABLED` to `true` in `config/delhivery_config.php`
2. Ensure the `logs/` directory exists and is writable
3. Monitor `logs/delhivery_api.log` for API requests and responses

## Features Implemented

### 1. Automatic Shipment Creation
- When an order status is changed to "shipped", a shipment is automatically created with Delhivery
- Customer details are automatically populated from the order
- AWB number is generated and stored in the database

### 2. Real-time Tracking
- Customers can track their shipments using the new `track_shipment.php` page
- Admin and sellers can track shipments from their respective dashboards
- Tracking history is stored in the database for future reference

### 3. Updated User Interfaces
- Removed manual tracking ID entry prompts
- Added "Track Shipment" buttons for shipped orders
- Real-time tracking information display in modals

### 4. Database Integration
- New tables for tracking history and pickup requests
- Additional columns in orders table for Delhivery data
- Proper indexing for performance

## API Endpoints Used

1. **Create Shipment**: `https://track.delhivery.com/api/cmu/create.json`
2. **Track Shipment**: `https://track.delhivery.com/api/v1/packages/json`
3. **Create Pickup**: `https://track.delhivery.com/api/p/pickup`
4. **Pincode Serviceability**: `https://track.delhivery.com/api/p/pincode/json`

## Error Handling

The integration includes comprehensive error handling:

- API connection failures
- Invalid credentials
- Missing customer data
- Network timeouts
- Invalid responses

All errors are logged and user-friendly messages are displayed.

## Security Considerations

1. **API Token Security**: Keep your API token secure and never commit it to version control
2. **Input Validation**: All user inputs are validated before API calls
3. **Error Messages**: Sensitive information is not exposed in error messages
4. **Database Security**: Prepared statements are used to prevent SQL injection

## Troubleshooting

### Common Issues

1. **"Invalid API token" Error**
   - Verify your API token is correct
   - Check if the token has expired
   - Ensure the token has the required permissions

2. **"Service not available for this pincode" Error**
   - Check if Delhivery services the destination pincode
   - Use the pincode serviceability API to verify coverage

3. **"Failed to create shipment" Error**
   - Check customer address details
   - Verify all required fields are populated
   - Check Delhivery API status

### Debug Mode

To enable debug mode, set `error_reporting(E_ALL)` and `ini_set('display_errors', 1)` in the relevant PHP files.

## Support

For technical support:
1. Check the log files for detailed error messages
2. Verify your Delhivery account status
3. Test API connectivity using Delhivery's developer portal
4. Contact Delhivery support for API-related issues

## Future Enhancements

Potential future improvements:
1. Automated pickup scheduling
2. Bulk shipment creation
3. Advanced tracking notifications
4. Integration with other courier services
5. Real-time status updates via webhooks

---

**Note**: Make sure to test the integration thoroughly in a staging environment before deploying to production.
