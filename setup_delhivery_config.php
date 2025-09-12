<?php
/**
 * Delhivery Configuration Setup Script
 * This script helps you configure your Delhivery API credentials
 */

echo "<h2>Delhivery Configuration Setup</h2>\n";

// Check if config file exists
if (!file_exists('config/delhivery_config.php')) {
    echo "❌ Configuration file not found. Please ensure config/delhivery_config.php exists.<br>\n";
    exit;
}

echo "<h3>Current Configuration</h3>\n";
echo "<p>Please update the following values in <strong>config/delhivery_config.php</strong>:</p>\n";

echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>\n";
echo "<h4>For Staging Environment:</h4>\n";
echo "<ul>\n";
echo "<li><strong>DELHIVERY_ENVIRONMENT</strong>: Set to 'staging'</li>\n";
echo "<li><strong>DELHIVERY_JWT_TOKEN</strong>: Your staging JWT Bearer token</li>\n";
echo "<li><strong>DELHIVERY_CLIENT_ID</strong>: Your staging client ID</li>\n";
echo "</ul>\n";

echo "<h4>For Production Environment:</h4>\n";
echo "<ul>\n";
echo "<li><strong>DELHIVERY_ENVIRONMENT</strong>: Set to 'production'</li>\n";
echo "<li><strong>DELHIVERY_API_TOKEN</strong>: Your production API token</li>\n";
echo "<li><strong>DELHIVERY_CLIENT_ID</strong>: Your production client ID</li>\n";
echo "</ul>\n";
echo "</div>\n";

echo "<h3>Configuration Steps:</h3>\n";
echo "<ol>\n";
echo "<li>Open <code>config/delhivery_config.php</code> in your text editor</li>\n";
echo "<li>Choose your environment (staging or production)</li>\n";
echo "<li>Update the credentials with your actual values</li>\n";
echo "<li>Save the file</li>\n";
echo "<li>Run the test script to verify the configuration</li>\n";
echo "</ol>\n";

echo "<h3>Example Configuration:</h3>\n";
echo "<pre style='background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto;'>\n";
echo "// For Staging\n";
echo "define('DELHIVERY_ENVIRONMENT', 'staging');\n";
echo "define('DELHIVERY_JWT_TOKEN', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...');\n";
echo "define('DELHIVERY_CLIENT_ID', 'your_staging_client_id');\n\n";
echo "// For Production\n";
echo "define('DELHIVERY_ENVIRONMENT', 'production');\n";
echo "define('DELHIVERY_API_TOKEN', 'your_production_api_token');\n";
echo "define('DELHIVERY_CLIENT_ID', 'your_production_client_id');\n";
echo "</pre>\n";

echo "<h3>Getting Your Credentials:</h3>\n";
echo "<div style='background: #e7f3ff; padding: 15px; border-radius: 8px; margin: 20px 0;'>\n";
echo "<h4>For Staging:</h4>\n";
echo "<ol>\n";
echo "<li>Log in to your Delhivery staging account</li>\n";
echo "<li>Go to Developer Portal or API Settings</li>\n";
echo "<li>Generate or copy your JWT Bearer token</li>\n";
echo "<li>Note down your Client ID</li>\n";
echo "</ol>\n";

echo "<h4>For Production:</h4>\n";
echo "<ol>\n";
echo "<li>Log in to your Delhivery production account</li>\n";
echo "<li>Go to Settings > API Setup</li>\n";
echo "<li>View or generate your API Token</li>\n";
echo "<li>Note down your Client ID</li>\n";
echo "</ol>\n";
echo "</div>\n";

echo "<h3>Security Notes:</h3>\n";
echo "<ul>\n";
echo "<li>Never commit your actual credentials to version control</li>\n";
echo "<li>Keep your API tokens secure and rotate them regularly</li>\n";
echo "<li>Use staging environment for testing before going to production</li>\n";
echo "<li>Monitor your API usage and logs</li>\n";
echo "</ul>\n";

echo "<h3>Next Steps:</h3>\n";
echo "<ol>\n";
echo "<li>Configure your credentials in the config file</li>\n";
echo "<li>Run <code>php test_delhivery_integration.php</code> to test the setup</li>\n";
echo "<li>Test with a real order by updating its status to 'shipped'</li>\n";
echo "<li>Check the tracking functionality</li>\n";
echo "</ol>\n";

echo "<p><strong>Important:</strong> This setup script is for configuration only. Remove it from production after setup.</p>\n";
?>
