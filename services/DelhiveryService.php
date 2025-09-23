<?php
require_once(__DIR__ . '/../config/delhivery_config.php');

/**
 * Delhivery API Service Class
 * 
 * This class handles all interactions with Delhivery APIs including:
 * - Shipment creation
 * - Shipment tracking
 * - Pickup request creation
 * - Pincode serviceability check
 */
class DelhiveryService {
    
    private $apiToken;
    private $jwtToken;
    private $baseUrl;
    private $endpoints;
    private $defaultSettings;
    private $authType;
    private $environment;
    
    public function __construct() {
        $this->environment = DELHIVERY_ENVIRONMENT;
        $this->authType = DELHIVERY_AUTH_TYPE;
        $this->baseUrl = DELHIVERY_API_BASE_URL;
        $this->endpoints = DELHIVERY_ENDPOINTS;
        $this->defaultSettings = DELHIVERY_DEFAULT_SETTINGS;
        
        if ($this->authType === 'bearer') {
            $this->jwtToken = DELHIVERY_JWT_TOKEN;
        } else {
            $this->apiToken = DELHIVERY_API_TOKEN;
        }
    }
    
    /**
     * Check if pincode is serviceable
     *
     * @param string $pincode Pincode to check
     * @return array Serviceability response
     */
    public function checkPincodeService($pincode) {
        try {
            $this->log('Checking service for pincode: ' . $pincode);

            // Prepare data for pincode serviceability check
            $data = [];
            if ($this->authType === 'bearer') {
                $url = $this->endpoints['get_pincode_service'];
                $data['pin_codes'] = $pincode;
            } else {
                $url = $this->endpoints['get_pincode_service'];
                $data['token'] = $this->apiToken;
                $data['pin_codes'] = $pincode;
            }

            $response = $this->makeApiCall($url, $data, 'POST');

            if ($response['success']) {
                $this->log('Pincode service check successful: ' . json_encode($response['data']));

                // Parse the response to determine serviceability
                $isServiceable = $this->parsePincodeServiceability($response['data'], $pincode);

                return [
                    'success' => true,
                    'serviceable' => $isServiceable,
                    'data' => $response['data']
                ];
            } else {
                $this->log('Pincode service check failed: ' . $response['message']);
                return [
                    'success' => false,
                    'serviceable' => false,
                    'message' => $response['message']
                ];
            }

        } catch (Exception $e) {
            $this->log('Error checking pincode service: ' . $e->getMessage());
            return [
                'success' => false,
                'serviceable' => false,
                'message' => 'Error checking pincode service: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create a new warehouse in Delhivery
     *
     * @param array $warehouseData Warehouse data
     * @return array API response
     */
    public function createWarehouse($warehouseData) {
        try {
            $this->log('Creating new warehouse: ' . json_encode($warehouseData));
            
 
            $endpoint = $this->endpoints['create_warehouse'] ?? $this->baseUrl . 'api/backend/clientwarehouse/create/';
            
            // Validate required fields based on the working API format
            $requiredFields = [
                'name' => 'Warehouse Name',
                'email' => 'Email',
                'phone' => 'Phone',
                'address' => 'Address',
                'city' => 'City',
                'state' => 'State',
                'country' => 'Country',
                'pin' => 'Pincode',
                'registered_name' => 'Registered Business Name'
            ];
            
            $missingFields = [];
            foreach ($requiredFields as $field => $label) {
                if (!isset($warehouseData[$field]) || trim($warehouseData[$field]) === '') {
                    $missingFields[] = $label;
                }
            }
            
            if (!empty($missingFields)) {
                throw new Exception('The following required fields are missing or empty: ' . implode(', ', $missingFields));
            }
            
            // Prepare the payload in JSON format as required by Delhivery API
            $payload = [
                'name' => $warehouseData['name'],
                'email' => $warehouseData['email'],
                'phone' => $warehouseData['phone'],
                'address' => $warehouseData['address'],
                'city' => $warehouseData['city'],
                'state' => $warehouseData['state'],
                'country' => $warehouseData['country'],
                'pin' => $warehouseData['pin'],
                'registered_name' => $warehouseData['registered_name'] ?? $warehouseData['name'],
                'contact_person' => $warehouseData['contact_person'] ?? $warehouseData['name']
            ];
            
            // Add return address if provided
            if (isset($warehouseData['return_address'])) {
                $payload['return_address'] = $warehouseData['return_address'];
                $payload['return_pin'] = $warehouseData['return_pin'] ?? $warehouseData['pin'];
                $payload['return_city'] = $warehouseData['return_city'] ?? $warehouseData['city'];
                $payload['return_state'] = $warehouseData['return_state'] ?? $warehouseData['state'];
                $payload['return_country'] = $warehouseData['return_country'] ?? $warehouseData['country'];
            }
            
            // Add optional fields if provided
            $optionalFields = [
                'address_2', 'landmark', 'gstin', 'gst_company_name', 'gst_company_address',
                'gst_state_code', 'gst_city', 'gst_pin_code', 'gst_email', 'gst_phone'
            ];
            
            foreach ($optionalFields as $field) {
                if (isset($warehouseData[$field]) && $warehouseData[$field] !== '') {
                    $payload[$field] = $warehouseData[$field];
                }
            }
            
            // Set headers for JSON content
            $headers = [
                'Content-Type: application/json',
                'Accept: application/json'
            ];
            
            // Add authorization header based on configured auth type
            if ($this->authType === 'bearer' && !empty($this->jwtToken)) {
                $headers[] = 'Authorization: Bearer ' . $this->jwtToken;
                $this->log('Using Bearer token for authentication');
            } elseif (!empty($this->apiToken)) {
                $headers[] = 'Authorization: Token ' . $this->apiToken;
                $this->log('Using API token for authentication');
            } else {
                throw new Exception('No API credentials available. Please check your API configuration.');
            }
            
            // Log the full request details
            $this->log('Making API call to: ' . $endpoint);
            $this->log('Request Method: POST');
            $this->log('Request Headers: ' . json_encode($headers));
            $this->log('Request Payload: ' . json_encode($payload, JSON_PRETTY_PRINT));
            
            // Log the JSON payload for debugging
            $jsonPayload = json_encode($payload, JSON_PRETTY_PRINT);
            $this->log('JSON Request Payload: ' . $jsonPayload);
            
            // Make the API call with JSON content
            $response = $this->makeApiCall($endpoint, $payload, 'POST', 3, true, $headers);
            
            // Log the full response for debugging
            $this->log('API Response: ' . print_r($response, true));
            
            // Log the response
            $this->log('API Response: ' . print_r($response, true));
            
            // Process the response
            if (isset($response['success']) && $response['success']) {
                $this->log('Warehouse created successfully');
                
                // Standardize the response format
                $responseData = [
                    'success' => true,
                    'message' => is_array($response['message'] ?? null) ? implode(', ', $response['message']) : ($response['message'] ?? 'Warehouse created successfully'),
                    'data' => [
                        // Delhivery often uses warehouse name as identifier; fallback to request name when API omits an explicit id
                        'wh_id' => $response['data']['id'] ?? $response['data']['wh_id'] ?? $response['data']['name'] ?? $response['data']['data']['name'] ?? $warehouseData['name'],
                        'delhivery_warehouse_id' => $response['data']['id'] ?? $response['data']['delhivery_warehouse_id'] ?? $response['data']['name'] ?? $response['data']['data']['name'] ?? $warehouseData['name'],
                        'name' => $response['data']['name'] ?? $response['data']['data']['name'] ?? $warehouseData['name'],
                        'address' => $warehouseData['address'],
                        'city' => $warehouseData['city'],
                        'state' => $warehouseData['state'],
                        'country' => $warehouseData['country'],
                        'pin' => $warehouseData['pin']
                    ]
                ];
                
                return $responseData;
            } else {
                $errorMessage = 'Failed to create warehouse';
                if (isset($response['message'])) {
                    if (is_array($response['message'])) {
                        $errorMessage = implode(', ', $response['message']);
                    } else {
                        $errorMessage = $response['message'];
                    }
                } elseif (isset($response['error'])) {
                    if (is_array($response['error'])) {
                        $errorMessage = implode(', ', $response['error']);
                    } else {
                        $errorMessage = $response['error'];
                    }
                }
                // Normalize common error into a concise, user-friendly message
                if (stripos($errorMessage, 'already exists') !== false) {
                    $errorMessage = 'A warehouse with this name already exists in Delhivery. Please use a different name or manage the existing warehouse.';
                }
                
                $this->log('Failed to create warehouse: ' . $errorMessage);
                
                return [
                    'success' => false,
                    'message' => $errorMessage,
                    'data' => $response['data'] ?? null
                ];
            }
            
        } catch (Exception $e) {
            $errorMsg = 'Error creating warehouse: ' . $e->getMessage();
            $this->log($errorMsg);
            return [
                'success' => false,
                'message' => $errorMsg,
                'data' => null
            ];
        }
    }
    
    /**
     * Update an existing warehouse in Delhivery
     *
     * @param string $warehouseId Delhivery warehouse ID
     * @param array $warehouseData Updated warehouse data
     * @return array API response
     */
    public function updateWarehouse($warehouseId, $warehouseData) {
        try {
            $this->log('Updating warehouse ' . $warehouseId . ': ' . json_encode($warehouseData));
            
            if (empty($warehouseId)) {
                throw new Exception('Warehouse ID is required for update');
            }
            
            $endpoint = str_replace('{delhivery_warehouse_id}', $warehouseId, $this->endpoints['update_warehouse']);
            
            // Prepare the payload with only updated fields
            $payload = [];
            $updatableFields = [
                'name', 'email', 'phone', 'address', 'city', 'state', 'country', 'pin',
                'contact_person', 'address_2', 'landmark', 'gstin', 'gst_company_name',
                'gst_company_address', 'gst_state_code', 'gst_city', 'gst_pin_code',
                'gst_email', 'gst_phone', 'is_active'
            ];
            
            foreach ($updatableFields as $field) {
                if (isset($warehouseData[$field])) {
                    $payload[$field] = $warehouseData[$field];
                }
            }
            
            // Handle boolean flags
            if (isset($warehouseData['is_return'])) {
                $payload['return_address'] = $warehouseData['is_return'] ? 1 : 0;
            }
            // if (isset($warehouseData['is_fulfillment'])) {
            //     $payload['fulfillment'] = $warehouseData['is_fulfillment'] ? 1 : 0;
            // }
            // if (isset($warehouseData['is_rto_address'])) {
            //     $payload['rto_address'] = $warehouseData['is_rto_address'] ? 1 : 0;
            // }
            
            // Add authorization headers
            $headers = [];
            if ($this->authType === 'bearer') {
                $headers[] = 'Authorization: Bearer ' . $this->jwtToken;
                $headers[] = 'Content-Type: application/json';
            } else {
                $headers[] = 'Authorization: Token '.$this->apiToken;
                $headers[] = 'Content-Type: application/json';
            }
            
            // Make the API call with JSON content type and proper headers
            $response = $this->makeApiCall($endpoint, $payload, 'PUT', 3, true, $headers);
            
            return $response;
            
        } catch (Exception $e) {
            $errorMsg = 'Error updating warehouse: ' . $e->getMessage();
            $this->log($errorMsg);
            return [
                'success' => false,
                'message' => $errorMsg,
                'data' => null
            ];
        }
    }
    
    /**
     * Get warehouse details from Delhivery
     *
     * @param string $warehouseId Delhivery warehouse ID
     * @return array API response
     */
    public function getWarehouse($warehouseId) {
        try {
            if (empty($warehouseId)) {
                throw new Exception('Warehouse ID is required');
            }
            
            $endpoint = $this->endpoints['get_warehouse'] . $warehouseId . '/';
            
            // Add authorization headers
            $headers = [];
            if ($this->authType === 'bearer') {
                $headers[] = 'Authorization: Bearer ' . $this->jwtToken;
                $headers[] = 'Accept: application/json';
            } else {
                $endpoint .= (strpos($endpoint, '?') === false ? '?' : '&') . 'token=' . urlencode($this->apiToken);
            }
            
            // Make the API call with proper headers
            $response = $this->makeApiCall($endpoint, [], 'GET', 3, false, $headers);
            
            if ($response['success']) {
                $this->log('Warehouse details retrieved successfully');
                // Standardize the response format
                if (!isset($response['data']['wh_id']) && isset($response['data']['delhivery_warehouse_id'])) {
                    $response['data']['wh_id'] = $response['data']['delhivery_warehouse_id'];
                }
            } else {
                $this->log('Failed to get warehouse details: ' . ($response['message'] ?? 'Unknown error'));
            }
            
            return $response;
            
        } catch (Exception $e) {
            $errorMsg = 'Error getting warehouse details: ' . $e->getMessage();
            $this->log($errorMsg);
            return [
                'success' => false,
                'message' => $errorMsg,
                'data' => null
            ];
        }
    }
    
    /**
     * Parse pincode serviceability response from Delhivery API
     *
     * @param array $responseData API response data
     * @param string $pincode Pincode being checked
     * @return bool Whether pincode is serviceable
     */
    private function parsePincodeServiceability($responseData, $pincode) {
        try {
            $this->log('Parsing pincode serviceability for: ' . $pincode);

            // Check if response has delivery_codes array
            if (isset($responseData['delivery_codes']) && is_array($responseData['delivery_codes'])) {
                foreach ($responseData['delivery_codes'] as $codeData) {
                    if (isset($codeData['postal_code']) && $codeData['postal_code'] == $pincode) {
                        // Check if serviceable based on various indicators
                        $isServiceable = isset($codeData['serviceable']) ? $codeData['serviceable'] : true;

                        // Also check for other indicators like cod, prepaid, etc.
                        if ($isServiceable && isset($codeData['prepaid'])) {
                            $isServiceable = $codeData['prepaid'];
                        }

                        $this->log('Pincode ' . $pincode . ' serviceability: ' . ($isServiceable ? 'YES' : 'NO'));
                        return $isServiceable;
                    }
                }
            }

            // Check if response has direct pincode data
            if (isset($responseData[$pincode])) {
                $pinData = $responseData[$pincode];
                $isServiceable = isset($pinData['serviceable']) ? $pinData['serviceable'] : true;
                $this->log('Pincode ' . $pincode . ' serviceability: ' . ($isServiceable ? 'YES' : 'NO'));
                return $isServiceable;
            }

            // If no specific data found, check for general success indicators
            if (isset($responseData['success']) && $responseData['success'] === true) {
                $this->log('Pincode ' . $pincode . ' assumed serviceable based on success response');
                return true;
            }

            // Default to serviceable if we can't determine otherwise
            $this->log('Pincode ' . $pincode . ' serviceability undetermined, defaulting to serviceable');
            return true;

        } catch (Exception $e) {
            $this->log('Error parsing pincode serviceability: ' . $e->getMessage());
            return true; // Default to serviceable on parsing errors
        }
    }

    /**
     * Create a new shipment
     *
     * @param array $shipmentData Shipment details
     * @return array Response from Delhivery API
     */
    public function createShipment($shipmentData) {
        try {
            $this->log('Creating shipment for order: ' . $shipmentData['reference_no']);

            // Validate required fields
            $this->validateShipmentData($shipmentData);

            // Prepare shipment data
            $data = $this->prepareShipmentData($shipmentData);
            $this->log('Prepared shipment data: ' . json_encode($data));

            // Make API call
            $response = $this->makeApiCall($this->endpoints['create_shipment'], $data, 'POST');

            if ($response['success']) {
                $this->log('Shipment created successfully: ' . json_encode($response['data']));

                // Extract AWB number from response
                $awbNumber = null;
                if (isset($response['data']['packages']) && is_array($response['data']['packages']) && count($response['data']['packages']) > 0) {
                    $awbNumber = $response['data']['packages'][0]['waybill'] ?? null;
                }

                return [
                    'success' => true,
                    'data' => $response['data'],
                    'awb_number' => $awbNumber,
                    'message' => 'Shipment created successfully'
                ];
            } else {
                // Try to extract detailed error info from Delhivery response
                $detailedMessage = $response['message'] ?? 'API call failed';
                $serviceable = null;
                $remarksMessage = '';

                if (isset($response['data']) && is_array($response['data'])) {
                    // Use 'rmk' if present
                    if (!empty($response['data']['rmk'])) {
                        $detailedMessage = $response['data']['rmk'];
                    }

                    // Check packages array for serviceability and remarks
                    if (isset($response['data']['packages']) && is_array($response['data']['packages']) && count($response['data']['packages']) > 0) {
                        $pkg = $response['data']['packages'][0];
                        if (isset($pkg['serviceable'])) {
                            $serviceable = (bool)$pkg['serviceable'];
                        }
                        if (!empty($pkg['remarks'])) {
                            if (is_array($pkg['remarks'])) {
                                $remarksMessage = implode('; ', array_filter($pkg['remarks']));
                            } else if (is_string($pkg['remarks'])) {
                                $remarksMessage = $pkg['remarks'];
                            }
                        }
                    }
                }

                $finalMessage = 'Shipment creation failed';
                if (!empty($remarksMessage)) {
                    $finalMessage .= ': ' . $remarksMessage;
                } else if (!empty($detailedMessage)) {
                    $finalMessage .= ': ' . $detailedMessage;
                }

                $this->log('Shipment creation failed: ' . $finalMessage);

                $result = [
                    'success' => false,
                    'message' => $finalMessage
                ];
                // Bubble up serviceability info when available so callers can branch
                if ($serviceable !== null) {
                    $result['serviceable'] = $serviceable;
                }
                // Also bubble raw response for any downstream needs
                if (isset($response['data'])) {
                    $result['data'] = $response['data'];
                }

                return $result;
            }

        } catch (Exception $e) {
            $this->log('Error creating shipment: ' . $e->getMessage());
            $this->log('Stack trace: ' . $e->getTraceAsString());
            return [
                'success' => false,
                'message' => 'Failed to create shipment: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Track a shipment using AWB number
     * 
     * @param string $awbNumber AWB number to track
     * @return array Tracking information
     */
    public function trackShipment($awbNumber) {
        try {
            $this->log('Tracking shipment: ' . $awbNumber);
            
            if ($this->authType === 'bearer') {
                $url = $this->endpoints['track_shipment'] . '?waybill=' . $awbNumber;
            } else {
                $url = $this->endpoints['track_shipment'] . '?token=' . $this->apiToken . '&waybill=' . $awbNumber;
            }
            
            $response = $this->makeApiCall($url, null, 'GET');
            
            if ($response['success']) {
                $this->log('Shipment tracking successful: ' . json_encode($response['data']));
                return [
                    'success' => true,
                    'data' => $response['data'],
                    'message' => 'Shipment tracking successful'
                ];
            } else {
                $this->log('Shipment tracking failed: ' . $response['message']);
                return [
                    'success' => false,
                    'message' => $response['message']
                ];
            }
            
        } catch (Exception $e) {
            $this->log('Error tracking shipment: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to track shipment: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generate shipping label PDF for one or more waybills
     * @param array|string $waybills Single waybill string or array of waybills
     * @param array $options Optional params like size, format
     * @return array { success: bool, data|content, message }
     */
    public function generateShippingLabel($waybills, $options = []) {
        try {
            $this->log('Generating shipping label');

            if (is_string($waybills)) { $waybills = [$waybills]; }
            if (!is_array($waybills) || count($waybills) === 0) {
                throw new Exception('No waybills provided');
            }

            // Typical inputs: waybill(s) and output format; API expects form params
            $payload = [
                'waybill' => implode(',', $waybills),
                'format' => $options['format'] ?? 'pdf'
            ];
            if (!empty($options['size'])) { $payload['size'] = $options['size']; }
            if (!empty($options['orientation'])) { $payload['orientation'] = $options['orientation']; }

            // For label we often need to fetch raw PDF bytes. Use cURL directly for binary
            $ch = curl_init();
            $headers = ['Accept: application/pdf'];
            if ($this->authType === 'bearer') {
                $headers[] = 'Authorization: Bearer ' . $this->jwtToken;
            } else {
                // Some token auths accept token as header or param; leave as-is if needed
            }
            curl_setopt_array($ch, [
                CURLOPT_URL => $this->endpoints['generate_label'],
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => DELHIVERY_API_TIMEOUT,
                CURLOPT_CONNECTTIMEOUT => DELHIVERY_CURL_TIMEOUT,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => $headers
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) { throw new Exception('CURL Error: ' . $err); }
            if ($httpCode !== 200) { throw new Exception('HTTP Error: ' . $httpCode); }

            // Return raw PDF content
            return [ 'success' => true, 'content' => $response, 'message' => 'Label generated' ];
        } catch (Exception $e) {
            $this->log('Error generating label: ' . $e->getMessage());
            return [ 'success' => false, 'message' => $e->getMessage() ];
        }
    }
    
    /**
     * Create a pickup request
     * 
     * @param array $pickupData Pickup request details
     * @return array Response from Delhivery API
     */
    public function createPickup($pickupData) {
        try {
            $this->log('Creating pickup request for: ' . $pickupData['pickup_location']);
            
            // Validate pickup data
            $this->validatePickupData($pickupData);
            
            // Prepare pickup data
            $data = $this->preparePickupData($pickupData);
            
            // Make API call
            $response = $this->makeApiCall($this->endpoints['create_pickup'], $data, 'POST');
            
            if ($response['success']) {
                $this->log('Pickup request created successfully: ' . json_encode($response['data']));
                return [
                    'success' => true,
                    'data' => $response['data'],
                    'message' => 'Pickup request created successfully'
                ];
            } else {
                $this->log('Pickup request creation failed: ' . $response['message']);
                return [
                    'success' => false,
                    'message' => $response['message']
                ];
            }
            
        } catch (Exception $e) {
            $this->log('Error creating pickup request: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create pickup request: ' . $e->getMessage()
            ];
        }
    }
    
    
    /**
     * Validate shipment data
     * 
     * @param array $data Shipment data to validate
     * @throws Exception If validation fails
     */
    private function validateShipmentData($data) {
        $requiredFields = ['reference_no', 'name', 'address', 'city', 'state', 'pincode', 'phone'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Required field '{$field}' is missing");
            }
        }
        
        // Validate phone number
        if (!preg_match('/^[0-9]{10}$/', $data['phone'])) {
            throw new Exception('Invalid phone number format');
        }
        
        // Validate pincode
        if (!preg_match('/^[0-9]{6}$/', $data['pincode'])) {
            throw new Exception('Invalid pincode format');
        }
    }
    
    /**
     * Validate pickup data
     * 
     * @param array $data Pickup data to validate
     * @throws Exception If validation fails
     */
    private function validatePickupData($data) {
        $requiredFields = ['pickup_location', 'pickup_date', 'pickup_time'];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("Required field '{$field}' is missing");
            }
        }
    }
    
    /**
     * Prepare shipment data for API call
     * 
     * @param array $shipmentData Raw shipment data
     * @return array Formatted shipment data
     */
    private function prepareShipmentData($shipmentData) {
        // Use provided warehouse name or fallback to environment-based default
        $warehouseName = '';
        if (!empty($shipmentData['warehouse_name'])) {
            $warehouseName = trim($shipmentData['warehouse_name']);
        } else {
            // Fallback to environment-based default
            $warehouseName = ($this->environment === 'staging') ? 'TAMIL WAREHOUSE' : 'IMET WAREHOUSE';
        }
        
        // Format shipment data according to Delhivery API specification
        $shipment = [
            'name' => $shipmentData['name'],
            'add' => $shipmentData['address'],
            'pin' => $shipmentData['pincode'],
            'city' => $shipmentData['city'],
            'state' => $shipmentData['state'],
            'country' => 'India',
            'phone' => $shipmentData['phone'],
            'order' => $shipmentData['reference_no'],
            'payment_mode' => 'Prepaid',
            'return_pin' => '',
            'return_city' => '',
            'return_phone' => '',
            'return_add' => '',
            'return_state' => '',
            'return_country' => '',
            'products_desc' => '',
            'hsn_code' => '',
            'cod_amount' => $shipmentData['cod_amount'] ?? '',
            'order_date' => null,
            'total_amount' => $shipmentData['declared_value'] ?? '',
            'seller_add' => '',
            'seller_name' => '',
            'seller_inv' => '',
            'quantity' => '',
            'waybill' => '',
            'shipment_width' => '100',
            'shipment_height' => '100',
            'weight' => '',
            'shipping_mode' => 'Surface',
            'address_type' => ''
        ];
        
        $apiData = [
            'shipments' => [$shipment],
            'pickup_location' => [
                'name' => $warehouseName
            ]
        ];
        
        if ($this->authType === 'bearer') {
            return [
                'format' => 'json',
                'data' => json_encode($apiData)
            ];
        } else {
            return [
                'token' => $this->apiToken,
                'format' => 'json',
                'data' => json_encode($apiData)
            ];
        }
    }
    
    /**
     * Prepare pickup data for API call
     * 
     * @param array $pickupData Raw pickup data
     * @return array Formatted pickup data
     */
    private function preparePickupData($pickupData) {
        if ($this->authType === 'bearer') {
            return array_merge([
                'format' => 'json'
            ], $pickupData);
        } else {
            return array_merge([
                'token' => $this->apiToken,
                'format' => 'json'
            ], $pickupData);
        }
    }
    
    /**
     * Make API call to Delhivery with retry logic
     *
     * @param string $url API endpoint URL
     * @param array $data Data to send
     * @param string $method HTTP method
     * @param int $maxRetries Maximum number of retries
     * @return array API response
     */
    /**
     * Make API call to Delhivery with retry logic
     * 
     * @param string $url API endpoint URL
     * @param array $data Data to send
     * @param bool $isJson Whether to send data as JSON
     * @param array $customHeaders Custom headers to include
     * @return array API response
     */
    private function makeApiCall($url, $data = null, $method = 'GET', $maxRetries = 3, $isJson = false, $customHeaders = []) {
        // Check if we're sending XML data
        $isXml = false;
        if (is_string($data) && strpos(trim($data), '<?xml') === 0) {
            $isXml = true;
        }
        // Log the full request details
        $this->log("\n=== Making API Request ===");
        $this->log("URL: $url");
        $this->log("Method: $method");
        $this->log("Is JSON: " . ($isJson ? 'Yes' : 'No'));
        $this->log("Request Data: " . print_r($data, true));
        $this->log("Custom Headers: " . print_r($customHeaders, true));

        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            try {
                $this->log('Making API call to: ' . $url . ' (Method: ' . $method . ', Attempt: ' . ($attempt + 1) . ')');
                $ch = curl_init();

                // Prepare headers based on authentication type
                $headers = [
                    'Accept: application/json',
                    'Content-Type: ' . ($isJson ? 'application/json' : 'application/x-www-form-urlencoded')
                ];

                // Add appropriate authentication header
                if ($this->authType === 'bearer' && !empty($this->jwtToken)) {
                    $headers[] = 'Authorization: Bearer ' . $this->jwtToken;
                } elseif (!empty($this->apiToken)) {
                    $headers[] = 'Authorization: Token ' . $this->apiToken;
                    // For some endpoints, we might need to add the token as a query parameter
                    if (strpos($url, '?') !== false) {
                        $url .= '&token=' . urlencode($this->apiToken);
                    } else {
                        $url .= '?token=' . urlencode($this->apiToken);
                    }
                }

                curl_setopt_array($ch, [
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => DELHIVERY_API_TIMEOUT,
                    CURLOPT_CONNECTTIMEOUT => DELHIVERY_CURL_TIMEOUT,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 5,
                    CURLOPT_HTTPHEADER => array_merge($headers, $customHeaders)
                ]);

                // Handle different HTTP methods and content types
                if (in_array($method, ['POST', 'PUT', 'PATCH']) && $data) {
                    if ($method !== 'POST') {
                        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                    } else {
                        curl_setopt($ch, CURLOPT_POST, true);
                    }

                    if ($isJson) {
                        // Send as JSON
                        $postData = json_encode($data);
                        $headers[] = 'Content-Type: application/json';
                        $headers[] = 'Content-Length: ' . strlen($postData);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                        $this->log('Request Data (JSON): ' . $postData);
                    } elseif ($isXml) {
                        // Send as XML
                        $postData = $data;
                        $headers[] = 'Content-Length: ' . strlen($postData);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                        $this->log('Request Data (XML): ' . $postData);
                    } else {
                        // Default to form-encoded
                        $postData = http_build_query($data);
                        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
                        $this->log('Request Data (Form): ' . $postData);
                    }
                } else if ($method === 'GET' && $data) {
                    // For GET requests, append data as query parameters
                    $url = $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($data);
                    curl_setopt($ch, CURLOPT_URL, $url);
                    $this->log('GET URL: ' . $url);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                } else {
                    // For other methods without data or non-POST requests
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                }

                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $error = curl_error($ch);

                curl_close($ch);

                if ($error) {
                    $errorMsg = 'CURL Error: ' . $error;
                    $this->log($errorMsg);

                    // Check if this is a retryable error
                    if ($this->isRetryableError($error)) {
                        $attempt++;
                        if ($attempt < $maxRetries) {
                            $this->log('Retrying API call in ' . (2 * $attempt) . ' seconds...');
                            sleep(2 * $attempt); // Exponential backoff
                            continue;
                        }
                    }

                    throw new Exception($errorMsg);
                }

                // Treat any non-2xx status as an error
                if ($httpCode < 200 || $httpCode >= 300) {
                    $errorMsg = 'HTTP Error: ' . $httpCode;
                    $this->log($errorMsg);

                    // Log response headers and body for debugging
                    $responseHeaders = curl_getinfo($ch);
                    $this->log("=== Raw Response Details ===");
                    $this->log("HTTP Status: " . $httpCode);
                    $this->log("Response Headers: " . print_r($responseHeaders, true));
                    $this->log("Raw Response Body: " . $response);

                    // Try to decode the response for more detailed error information
                    $decodedError = json_decode($response, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decodedError)) {
                        $this->log("Decoded Response: " . print_r($decodedError, true));

                        // Extract a helpful message if present
                        $errorFields = ['message', 'error', 'detail', 'errors', 'error_description'];
                        $details = [];
                        foreach ($errorFields as $field) {
                            if (isset($decodedError[$field])) {
                                if (is_array($decodedError[$field])) {
                                    $flat = array_filter($decodedError[$field], 'is_scalar');
                                    $details = array_merge($details, $flat);
                                } else if (is_scalar($decodedError[$field])) {
                                    $details[] = (string)$decodedError[$field];
                                }
                            }
                        }
                        if (!empty($details)) {
                            $errorMsg .= ' - ' . implode(', ', array_unique($details));
                        }
                    } elseif ($httpCode === 400) {
                        $errorMsg .= ' - Bad Request';
                    } elseif ($httpCode === 401) {
                        $errorMsg .= ' - Unauthorized (check API token/permissions)';
                    }

                    // Retry on server errors (5xx)
                    if ($httpCode >= 500 && $attempt < $maxRetries - 1) {
                        $attempt++;
                        $this->log('Retrying API call due to HTTP ' . $httpCode . ' in ' . (2 * $attempt) . ' seconds...');
                        continue;
                    }

                    // For any non-200 response, return a failure immediately
                    // Normalize duplicate warehouse message
                    $normalizedMsg = $errorMsg;
                    if (stripos($normalizedMsg, 'already exists') !== false && stripos($normalizedMsg, 'client-warehouse') !== false) {
                        $normalizedMsg = 'A warehouse with this name already exists in Delhivery. Please use a different name or manage the existing warehouse.';
                    }

                    return [
                        'success' => false,
                        'message' => $normalizedMsg,
                        'data' => null
                    ];
                }

            // Process the response
            $responseData = [];
            $decodedResponse = null;

            // Try to parse as XML first
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($response);
            if ($xml !== false) {
                // Convert XML to array
                $json = json_encode($xml);
                $responseData = json_decode($json, true);
                $responseData['_raw_xml'] = $response; // Keep original XML
                $decodedResponse = $responseData;
            } else {
                // Try to parse as JSON
                $decodedResponse = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $responseData = $decodedResponse;
                } else {
                    // If not JSON, return as text
                    $responseData = ['_raw_response' => $response];
                }
            }

            // Log the full response for debugging
            $this->log('API Response: ' . json_encode($responseData));

            // Check if response indicates success=false in body
            if ($decodedResponse && isset($decodedResponse['success']) && $decodedResponse['success'] === false) {
                $errorMessage = $decodedResponse['message'] ?? 'API call failed';
                $this->log('API Error: ' . $errorMessage);
                throw new Exception($errorMessage);
            }

            return [
                'success' => true,
                'data' => $decodedResponse ?: $responseData,
                'message' => 'API call successful'
            ];
        } catch (Exception $e) {
            $lastException = $e;
            $this->log('API call attempt ' . ($attempt + 1) . ' failed: ' . $e->getMessage());

            $attempt++;
            if ($attempt < $maxRetries) {
                $this->log('Retrying API call in ' . (2 * $attempt) . ' seconds...');
                sleep(2 * $attempt);
                continue;
            }
            throw $e;
        }
    }

        // All retries exhausted
        throw $lastException ?? new Exception('API call failed after ' . $maxRetries . ' attempts');
    }

    /**
     * Check if an error is retryable
     *
     * @param string $error CURL error message
     * @return bool Whether the error is retryable
     */
    private function isRetryableError($error) {
        $retryableErrors = [
            'Operation timed out',
            'Connection timed out',
            'Connection reset',
            'Connection refused',
            'Network is unreachable',
            'Temporary failure in name resolution',
            'Could not resolve host'
        ];

        foreach ($retryableErrors as $retryableError) {
            if (strpos($error, $retryableError) !== false) {
                return true;
            }
        }

        return false;
    }
    
    /**
     * Log messages to file
     * 
     * @param string $message Message to log
     */
    private function log($message) {
        if (DELHIVERY_LOG_ENABLED) {
            $logFile = __DIR__ . '/../' . DELHIVERY_LOG_FILE;
            $logDir = dirname($logFile);
            
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }
            
            $timestamp = date('Y-m-d H:i:s');
            $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
            
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        }
    }
}
?>
