<?php
/**
 * Delhivery API Configuration
 * 
 * This file contains all the configuration settings for Delhivery API integration.
 * Make sure to update these values with your actual Delhivery credentials.
 */

// Environment Configuration
define('DELHIVERY_ENVIRONMENT', 'staging'); // Change to 'production' for live environment

// Delhivery API Configuration
if (DELHIVERY_ENVIRONMENT === 'staging') {
    // Staging Environment
    define('DELHIVERY_API_BASE_URL', 'https://staging-express.delhivery.com/');
    define('DELHIVERY_JWT_TOKEN', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VybmFtZSI6ImJkLUlNRVRUT09MSU5HSU5ESUFQTFRELWRvLWNkcCIsInBob25lX251bWJlciI6Ijk1MDA2MTIyNzciLCJsYXN0X25hbWUiOiJIIiwidXNlcl90eXBlIjoiQ0wiLCJpYXQiOjE3NTc2ODI0NzgsImlzX2NsaWVudF9hZG1pbiI6dHJ1ZSwidGVuYW50IjoiRGVsaGl2ZXJ5IiwiYXVkIjoiR3ZES3pvZDZhT0lNM0xjeWE5QmpmQmI4YnZGa1lUWHkiLCJmaXJzdF9uYW1lIjoiQXJ1biIsInN1YiI6InVtczo6dXNlcjo6YWI2MWI3NWEtODdiYy0xMWYwLWE2MmUtMGUxNWY3NDdhMzEzIiwiY2xpZW50X3V1aWQiOiJjbXM6OmNsaWVudDo6YWI2MWI3NWItODdiYy0xMWYwLWE2MmUtMGUxNWY3NDdhMzEzIiwiaWRsZSI6MTc1ODI4NzI3OCwiY2xpZW50X2VtYWlsIjoiYXJ1bmltZXRAZ21haWwuY29tIiwiZXhwIjoxNzU3NzY4ODc4LCJjbGllbnRfbmFtZSI6ImJkLUlNRVRUT09MSU5HSU5ESUFQTFRELWRvLWNkcCIsInRva2VuX2lkIjoiZGQwN2VmNTQtMzViZC00NjY4LWIyMmItZDQ1Y2M5YThjOWUwIiwiZW1haWwiOiJhcnVuaW1ldEBnbWFpbC5jb20iLCJhcGlfdmVyc2lvbiI6InYyIiwidG9lIjoxNzU3NjgyNDc4fQ.ulwabywiCrsrWLu-6cZccWQ3Ccs1zVgER7tSfDb0WIQ'); // Replace with your staging JWT Bearer token
    define('DELHIVERY_AUTH_TYPE', 'bearer'); // Use Bearer token for staging
} else {
    // Production Environment
    define('DELHIVERY_API_BASE_URL', 'https://track.delhivery.com/');
    define('DELHIVERY_API_TOKEN', 'YOUR_PRODUCTION_API_TOKEN_HERE'); // Replace with your production API token
    define('DELHIVERY_AUTH_TYPE', 'token'); // Use API token for production
}

// Delhivery API Endpoints
if (DELHIVERY_ENVIRONMENT === 'staging') {
    define('DELHIVERY_ENDPOINTS', [
        'create_shipment' => 'https://staging-express.delhivery.com/api/cmu/create.json',
        'track_shipment' => 'https://staging-express.delhivery.com/api/v1/packages/json',
        'create_pickup' => 'https://staging-express.delhivery.com/api/p/pickup',
        'cancel_shipment' => 'https://staging-express.delhivery.com/api/p/edit',
        'get_pincode_service' => 'https://staging-express.delhivery.com/api/p/pincode/',
        'fetch_waybill' => 'https://staging-express.delhivery.com/api/v1/packages/json',
        'generate_label' => 'https://staging-express.delhivery.com/api/labels/shipping'
    ]);
} else {
    define('DELHIVERY_ENDPOINTS', [
        'create_shipment' => 'https://track.delhivery.com/api/cmu/create.json',
        'track_shipment' => 'https://track.delhivery.com/api/v1/packages/json',
        'create_pickup' => 'https://track.delhivery.com/api/p/pickup',
        'cancel_shipment' => 'https://track.delhivery.com/api/p/edit',
        'get_pincode_service' => 'https://track.delhivery.com/api/p/pincode/',
        'fetch_waybill' => 'https://track.delhivery.com/api/v1/packages/json',
        'generate_label' => 'https://track.delhivery.com/api/labels/shipping'
    ]);
}

define('DELHIVERY_DEFAULT_SETTINGS', [
    'payment_mode' => 'Prepaid',
    'product_type' => 'Non-Document',
    'product_category' => 'Non-Document',
    'package_type' => 'Non-Document',
    'reference_no' => '',
    'cod_amount' => 0,
    'declared_value' => 0,
    'name' => 'Deadstock Store',
    'company_name' => 'Deadstock Store',
    'address' => 'Your Store Address',
    'city' => 'Your City',
    'state' => 'Your State',
    'pincode' => '110001',
    'phone' => '9999999999',
    'email' => 'store@deadstock.com'
]);

// Error messages
define('DELHIVERY_ERROR_MESSAGES', [
    'INVALID_TOKEN' => 'Invalid API token provided',
    'INVALID_PINCODE' => 'Service not available for this pincode',
    'SHIPMENT_CREATION_FAILED' => 'Failed to create shipment',
    'TRACKING_FAILED' => 'Failed to track shipment',
    'PICKUP_CREATION_FAILED' => 'Failed to create pickup request',
    'NETWORK_ERROR' => 'Network error occurred while communicating with Delhivery API'
]);

// Logging configuration
define('DELHIVERY_LOG_ENABLED', true);
define('DELHIVERY_LOG_FILE', 'logs/delhivery_api.log');

// Timeout settings (in seconds)
define('DELHIVERY_API_TIMEOUT', 60);
define('DELHIVERY_CURL_TIMEOUT', 45);
?>
