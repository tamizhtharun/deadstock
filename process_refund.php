<?php
// Start by enabling all error reporting at the very top
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
use Razorpay\Api\Api;

// Function to log debug messages
function debug_log($message)
{
    echo $message . "<br>";
    error_log($message);
}

class RefundHandler
{
    private $conn;
    private $api;

    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->api = new Razorpay\Api\Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);
    }

    public function processRefunds()
    {
        // Get pending refunds from database with bid_price and quantity
        $query = "SELECT order_id, bid_price, bid_quantity, payment_id FROM bidding 
                 WHERE bid_status = '3' 
                 AND (refund_status IS NULL OR refund_status = 'pending')";
        $result = $this->conn->query($query);

        if (!$result) {
            throw new Exception("Failed to fetch refunds: " . $this->conn->error);
        }

        $processedCount = 0;
        while ($row = $result->fetch_assoc()) {
            // Calculate total amount from bid_price and quantity
            $amount = $row['bid_price'] * $row['bid_quantity'];
            $this->processRefund($row['order_id'], $amount, $row['payment_id']);
            $processedCount++;
        }

        return $processedCount;
    }

    private function processRefund($orderId, $amount, $paymentId)
    {
        debug_log("Starting refund process for Order ID: $orderId with amount: $amount");

        // Validate amount
        if (!$amount || $amount <= 0) {
            throw new Exception("Invalid refund amount for Order ID $orderId: $amount");
        }

        try {
            // Update refund status to processing
            $updateQuery = "UPDATE bidding SET refund_status = 'processing' WHERE order_id = ?";
            $stmt = $this->conn->prepare($updateQuery);
            $stmt->bind_param('s', $orderId);
            $stmt->execute();

            // Process refund through Razorpay
            try {
                $refund = $this->api->payment->fetch($paymentId)->refund([
                    'amount' => $amount * 100, // Convert to paisa
                    'notes' => [
                        'order_id' => $orderId,
                        'reason' => 'Bid unsuccessful'
                    ]
                ]);

                // Update refund status to completed
                $updateQuery = "UPDATE bidding SET 
                    refund_status = 'completed',
                    refund_id = ?,
                    refund_date = NOW(),
                    refund_amount = ?
                    WHERE order_id = ?";
                $stmt = $this->conn->prepare($updateQuery);
                $stmt->bind_param('sds', $refund['id'], $amount, $orderId);
                $stmt->execute();

                debug_log("Refund processed successfully for Order ID: $orderId, Amount: $amount");

            } catch (Exception $e) {
                // Update refund status to failed
                $updateQuery = "UPDATE bidding SET 
                    refund_status = 'failed',
                    refund_error = ?,
                    refund_amount = ?
                    WHERE order_id = ?";
                $stmt = $this->conn->prepare($updateQuery);
                $errorMessage = $e->getMessage();
                $stmt->bind_param('sds', $errorMessage, $amount, $orderId);
                $stmt->execute();

                throw new Exception("Razorpay refund failed for Order ID $orderId: " . $e->getMessage());
            }

        } catch (Exception $e) {
            debug_log("Error processing refund for Order ID $orderId: " . $e->getMessage());
            throw $e;
        }
    }
}

debug_log("Script started");

// Check session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
debug_log("Session checked");

// Check required files
$requiredFiles = [
    'vendor/autoload.php' => 'Composer autoload file',
    'config.php' => 'Configuration file',
    'db_connection.php' => 'Database connection file'
];

foreach ($requiredFiles as $file => $description) {
    if (!file_exists($file)) {
        debug_log("ERROR: Required $description not found: $file");
        die("Required file missing: $file");
    }
    debug_log("Found file: $file");
}

try {
    debug_log("Loading required files...");
    require 'vendor/autoload.php';
    require 'config.php';
    require 'db_connection.php';
    debug_log("Required files loaded successfully");

    // Check Razorpay credentials
    debug_log("Checking Razorpay credentials...");
    if (!defined('RAZORPAY_KEY_ID') || !defined('RAZORPAY_KEY_SECRET')) {
        throw new Exception("Razorpay credentials not properly configured in config.php");
    }
    debug_log("Razorpay credentials found");

    // Test database connection
    debug_log("Testing database connection...");
    if (!isset($conn) || !$conn) {
        throw new Exception("Database connection failed or not established");
    }
    debug_log("Database connection successful");

    // Test database table
    debug_log("Checking bidding table...");
    $tableCheck = $conn->query("SHOW TABLES LIKE 'bidding'");
    if ($tableCheck->num_rows === 0) {
        throw new Exception("Bidding table does not exist");
    }
    debug_log("Bidding table exists");

    // Test query to verify bid_price and quantity columns
    debug_log("Testing refund query...");
    $testQuery = "SELECT COUNT(*) as count FROM bidding 
                 WHERE bid_status = '3' 
                 AND (refund_status IS NULL OR refund_status = 'pending')
                 AND bid_price > 0 
                 AND bid_quantity > 0";
    $result = $conn->query($testQuery);
    if (!$result) {
        throw new Exception("Failed to execute test query: " . $conn->error);
    }
    $row = $result->fetch_assoc();
    debug_log("Found {$row['count']} valid records to process");

    debug_log("Starting RefundHandler initialization...");
    $refundHandler = new RefundHandler($conn);
    debug_log("RefundHandler initialized successfully");

    debug_log("Starting refund processing...");
    $processedCount = $refundHandler->processRefunds();
    debug_log("Refund processing completed. Processed $processedCount refunds.");

    echo "Refund processing completed successfully";

} catch (Exception $e) {
    $errorMessage = "Fatal error: " . $e->getMessage();
    debug_log($errorMessage);
    echo $errorMessage;
}
?>