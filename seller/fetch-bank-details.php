<?php
// Prevent PHP errors from being displayed
ini_set('display_errors', 0);
error_reporting(0);

// Set JSON headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

try {
    if (!isset($_GET['ifsc']) || empty($_GET['ifsc'])) {
        throw new Exception('IFSC code is required');
    }

    $ifsc = strtoupper(trim($_GET['ifsc']));

    // Validate IFSC format
    if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $ifsc)) {
        throw new Exception('Invalid IFSC code format');
    }

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options with improved configuration
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://bank-apis.justinclicks.com/API/V1/IFSC/" . $ifsc,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true, // Follow redirects
        CURLOPT_MAXREDIRS => 5, // Maximum number of redirects to follow
        CURLOPT_TIMEOUT => 30, // Increased timeout
        CURLOPT_SSL_VERIFYPEER => true, // Enable SSL verification
        CURLOPT_SSL_VERIFYHOST => 2, // Verify the hostname
        CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; BankDetailsAPI/1.0)', // Add user agent
        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'Content-Type: application/json'
        ]
    ]);

    // Execute cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if(curl_errno($ch)) {
        throw new Exception('Failed to fetch bank details: ' . curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check HTTP response code
    if ($httpCode !== 200) {
        // Add more detailed error reporting
        throw new Exception('Bank API returned error code: ' . $httpCode . 
                          '. Please verify the IFSC code and try again.');
    }

    // Try to decode the response
    $decodedResponse = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid response format from bank API');
    }

    // Send the response back
    echo json_encode([
        'status' => 'SUCCESS',
        'data' => $decodedResponse
    ]);

} catch (Exception $e) {
    // Return error in JSON format
    http_response_code(400);
    echo json_encode([
        'status' => 'ERROR',
        'error' => $e->getMessage()
    ]);
}