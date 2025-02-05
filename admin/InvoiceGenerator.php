<?php
class InvoiceGenerator {
    private $db;
    private $company_settings;
    
    public function __construct($pdo) {
        $this->db = $pdo;
        $this->loadCompanySettings();
    }
    
    private function loadCompanySettings() {
        try {
            $query = "SELECT * FROM tbl_company_settings WHERE id = 1";
            $statement = $this->db->prepare($query);
            $statement->execute();
            $this->company_settings = $statement->fetch(PDO::FETCH_ASSOC);
            
            if (!$this->company_settings) {
                $this->company_settings = [
                    'company_name' => 'Company Name Not Set',
                    'address' => 'Address Not Set',
                    'gstin' => 'GSTIN Not Set'
                ];
            }
        } catch (PDOException $e) {
            error_log("Database Error in loadCompanySettings: " . $e->getMessage());
            throw new Exception("Failed to load company settings");
        }
    }
    
    public function generateBiddingInvoice($order_id) {
        $order = $this->getBiddingOrderDetails($order_id);
        if (!$order) {
            throw new Exception("Order not found");
        }
        
        return $this->generateInvoiceHTML($order);
    }
    
    private function getBiddingOrderDetails($order_id) {
        try {
            $query = "SELECT 
                o.*,
                p.p_name,
                p.p_featured_photo,
                p.hsn_code,
                u.username,
                u.email,
                u.phone_number,
                ua.full_name,
                ua.phone_number as delivery_phone,
                ua.address,
                ua.city,
                ua.state,
                ua.pincode
            FROM 
                tbl_orders o
                JOIN tbl_product p ON o.product_id = p.id
                JOIN users u ON o.user_id = u.id
                LEFT JOIN users_addresses ua ON u.id = ua.user_id AND ua.is_default = 1
            WHERE 
                o.id = :order_id";
                
            $statement = $this->db->prepare($query);
            $statement->execute(['order_id' => $order_id]);
            return $statement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database Error in getBiddingOrderDetails: " . $e->getMessage());
            throw new Exception("Failed to fetch order details");
        }
    }
    
    private function generateInvoiceHTML($order) {
        // Calculate tax amounts
        $subtotal = $order['price'] * $order['quantity'];
        $cgst_rate = 9;
        $sgst_rate = 9;
        $cgst_amount = ($subtotal * $cgst_rate) / 100;
        $sgst_amount = ($subtotal * $sgst_rate) / 100;
        $total_amount = $subtotal + $cgst_amount + $sgst_amount;
        
        // Format numbers beforehand
        $price_formatted = number_format($order['price'], 2);
        $subtotal_formatted = number_format($subtotal, 2);
        $cgst_amount_formatted = number_format($cgst_amount, 2);
        $sgst_amount_formatted = number_format($sgst_amount, 2);
        $total_amount_formatted = number_format($total_amount, 2);
        
        // Escape all data for safety
        $safe_data = array_map('htmlspecialchars', [
            'company_name' => $this->company_settings['company_name'],
            'company_address' => $this->company_settings['address'],
            'company_gstin' => $this->company_settings['gstin'],
            'order_id' => $order['id'],
            'created_at' => date('d-M-Y', strtotime($order['created_at'])),
            'tracking_id' => $order['tracking_id'] ?? 'Not Available',
            'processing_time' => $order['processing_time'] ?? 'Not Available',
            'full_name' => $order['full_name'],
            'delivery_phone' => $order['delivery_phone'],
            'address' => $order['address'],
            'city' => $order['city'],
            'state' => $order['state'],
            'pincode' => $order['pincode'],
            'p_name' => $order['p_name'],
            'hsn_code' => $order['hsn_code']
        ]);
    
        $html = <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invoice #{$safe_data['order_id']}</title>
        <style>
            body { 
                font-family: DejaVu Sans, sans-serif; 
                line-height: 1.6; 
                margin: 0;
                padding: 20px;
            }
            .invoice-container { 
                max-width: 800px; 
                margin: auto; 
            }
            .header { 
                text-align: center; 
                margin-bottom: 30px; 
                border-bottom: 2px solid #eee;
                padding-bottom: 20px;
            }
            .company-details, .buyer-details { 
                margin-bottom: 20px; 
                padding: 15px;
                background: #f9f9f9;
            }
            .items-table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 20px 0; 
            }
            .items-table th, .items-table td { 
                border: 1px solid #ddd; 
                padding: 12px; 
                text-align: left;
            }
            .items-table th {
                background: #f5f5f5;
            }
            .amount-summary { 
                margin-top: 20px; 
                text-align: right;
                padding: 15px;
                background: #f9f9f9;
            }
        </style>
    </head>
    <body>
        <div class="invoice-container">
            <div class="header">
                <h1>TAX INVOICE</h1>
                <p>(Original for Recipient)</p>
            </div>
            
            <div class="company-details">
                <h3>{$safe_data['company_name']}</h3>
                <p>{$safe_data['company_address']}</p>
                <p>GSTIN: {$safe_data['company_gstin']}</p>
            </div>
            
            <div class="invoice-info">
                <p><strong>Invoice No:</strong> {$safe_data['order_id']}</p>
                <p><strong>Date:</strong> {$safe_data['created_at']}</p>
            </div>
            
            <div class="buyer-details">
                <h4>Bill To:</h4>
                <p>{$safe_data['full_name']}</p>
                <p>{$safe_data['delivery_phone']}</p>
                <p>{$safe_data['address']}</p>
                <p>{$safe_data['city']}, {$safe_data['state']} - {$safe_data['pincode']}</p>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>HSN/SAC</th>
                        <th>Quantity</th>
                        <th>Rate</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{$safe_data['p_name']}</td>
                        <td>{$safe_data['hsn_code']}</td>
                        <td>{$order['quantity']}</td>
                        <td>₹{$price_formatted}</td>
                        <td>₹{$subtotal_formatted}</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="amount-summary">
                <p>Subtotal: ₹{$subtotal_formatted}</p>
                <p>CGST ({$cgst_rate}%): ₹{$cgst_amount_formatted}</p>
                <p>SGST ({$sgst_rate}%): ₹{$sgst_amount_formatted}</p>
                <p><strong>Total Amount: ₹{$total_amount_formatted}</strong></p>
            </div>
        </div>
    </body>
    </html>
    HTML;
        
        return $html;
    }}