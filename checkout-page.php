
<?php
// Include the database connection file
include 'db_connection.php';


// Fetch addresses from the database
$sql = "SELECT * FROM users_addresses";
$result = $conn->query($sql);

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Page</title>
</head>
<style>
    /* Reset and base styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    line-height: 1.5;
    background-color: #f9fafb;
    color: #111827;
}

/* Header styles */
header {
    background: white;
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #e5e7eb;
}

.logo {
    font-size: 1.5rem;
    font-weight: bold;
    color: #10b981;
}

nav {
    display: flex;
    gap: 2rem;
}

nav a {
    text-decoration: none;
    color: #374151;
    transition: color 0.2s;
}

nav a:hover {
    color: #10b981;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.search-container input {
    padding: 0.5rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    background-color: #f9fafb;
}

.icons {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.icon-btn {
    position: relative;
    background: none;
    border: none;
    cursor: pointer;
    padding: 0.25rem;
}

.badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #ef4444;
    color: white;
    font-size: 0.75rem;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Main content styles */
.container {
    max-width: 1280px;
    margin: 0 auto;
    padding: 2rem;
}

.checkout-container {
    display: flex;
    gap: 2rem;
}

.forms-container {
    flex: 1;
}

/* Shipping and Payment sections */
.shipping-section,
.payment-section {
    margin-bottom: 2rem;
}

h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 1rem;
}

.add-new-btn {
    width: 100%;
    padding: 1rem;
    border: 2px dashed #e5e7eb;
    border-radius: 0.5rem;
    background: none;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s;
}

.add-new-btn:hover {
    border-color: #10b981;
    color: #10b981;
}

/* Forms */
.address-form,
.payment-form {
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin-top: 1rem;
}

.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-size: 0.875rem;
    color: #374151;
    margin-bottom: 0.25rem;
}

.form-group input {
    width: 100%;
    padding: 0.5rem 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    transition: all 0.2s;
}

.form-group input:focus {
    outline: none;
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 1.5rem;
}

/* Buttons */
.btn-primary {
    background: #10b981;
    color: white;
    border: none;
    padding: 0.5rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
}

.btn-primary:hover {
    background: #059669;
}

.btn-secondary {
    background: white;
    color: #374151;
    border: 1px solid #e5e7eb;
    padding: 0.5rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background: #f9fafb;
}

.btn-large {
    width: 100%;
    padding: 0.75rem;
    font-size: 1rem;
}

/* Order Summary */
.order-summary {
    width: 384px;
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.products-list {
    margin-bottom: 1.5rem;
}

.product-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.product-image {
    position: relative;
    width: 4rem;
    height: 4rem;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 0.5rem;
}

.product-quantity {
    position: absolute;
    top: -0.5rem;
    right: -0.5rem;
    background: #6b7280;
    color: white;
    font-size: 0.75rem;
    width: 1.25rem;
    height: 1.25rem;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.product-info {
    flex: 1;
}

.product-name {
    font-weight: 500;
}

.product-color {
    font-size: 0.875rem;
    color: #6b7280;
}

.product-price {
    font-weight: 500;
}

.summary-details {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
    margin-bottom: 1.5rem;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
    color: #6b7280;
    margin-bottom: 0.5rem;
}

.summary-total {
    display: flex;
    justify-content: space-between;
    font-weight: 600;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #e5e7eb;
}

.hidden {
    display: none;
}

/* Saved addresses */
.saved-addresses {
    margin-bottom: 1rem;
}

.address-option {
    display: flex;
    align-items: flex-start;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
    cursor: pointer;
}

.address-option input[type="radio"] {
    margin-top: 0.25rem;
    margin-right: 1rem;
}

.address-details {
    flex: 1;
}

.address-details .name {
    display: block;
    font-weight: 500;
}

.address-details .address {
    color: #6b7280;
    font-size: 0.875rem;
}

.address-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.link-btn {
    background: none;
    border: none;
    color: #2563eb;
    font-size: 0.875rem;
    cursor: pointer;
}

.separator {
    color: #e5e7eb;
}

/* Icons */
.heart-icon::before {
    content: "♥";
    font-size: 1.25rem;
}

.cart-icon::before {
    content: "🛒";
    font-size: 1.25rem;
}

.user-icon::before {
    content: "👤";
    font-size: 1.25rem;
}

.card-icon::before {
    content: "💳";
    font-size: 1.25rem;
    margin-right: 0.5rem;
}
.edit-form {
    margin-top: 10px;
    padding: 10px;
    border: 1px solid #ddd;
    background-color: #f9f9f9;
}
.edit-form input {
    display: block;
    margin-bottom: 10px;
    width: 100%;
    padding: 5px;
}
.edit-form button {
    margin-right: 10px;
}

</style>
<body>
  

   <main>
   <div class="container">
    <div class="checkout-container">
        <div class="forms-container">
            <section class="shipping-section">
                <h2>Shipping Address</h2>
                <div class="saved-addresses">
    <?php
    if ($result->num_rows > 0) {
        // Output data for each row
        while ($row = $result->fetch_assoc()) {
            // Fetch the address ID
            $addressId = htmlspecialchars($row["id"]); // Correctly assign $addressId here
            
            echo '<label class="address-option">';
            echo '<input type="radio" name="address">';
            echo '<div class="address-details">';
            echo '<span class="name">' . htmlspecialchars($row["full_name"]) . '</span>';
            echo '<span class="address">' . htmlspecialchars($row["address"]) . ', ' . htmlspecialchars($row["city"]) . ', ' . htmlspecialchars($row["state"]) . ' - ' . htmlspecialchars($row["pincode"]) . ' (' . htmlspecialchars($row["address_type"]) . ')</span>';
            echo '</div>';
            echo '<div class="address-actions">';
            echo '<button type="button" class="link-btn" onclick="showEditForm(' . $addressId . ')">Edit</button>'; // Use $addressId here
            echo '</div>';
            echo '</label>';

            // Edit Form (Initially Hidden)
            echo '<div class="edit-form" id="edit-form-' . $addressId . '" style="display: none;">';
            echo '<form method="POST" action="update_address.php">';
            echo '<input type="hidden" name="id" value="' . $addressId . '">'; // Use $addressId here
            echo '<label>Full Name:</label>';
            echo '<input type="text" name="full_name" value="' . htmlspecialchars($row["full_name"]) . '">';
            echo '<label>Address:</label>';
            echo '<input type="text" name="address" value="' . htmlspecialchars($row["address"]) . '">';
            echo '<label>City:</label>';
            echo '<input type="text" name="city" value="' . htmlspecialchars($row["city"]) . '">';
            echo '<label>State:</label>';
            echo '<input type="text" name="state" value="' . htmlspecialchars($row["state"]) . '">';
            echo '<label>Pincode:</label>';
            echo '<input type="text" name="pincode" value="' . htmlspecialchars($row["pincode"]) . '">';
            echo '<label>Address Type:</label>';
            echo '<input type="text" name="address_type" value="' . htmlspecialchars($row["address_type"]) . '">';
            echo '<button type="submit">Save</button>';
            echo '<button type="button" onclick="hideEditForm(' . $addressId . ')">Cancel</button>'; // Use $addressId here
            echo '</form>';
            echo '</div>';
        }
    } else {
        echo '<p>No addresses found.</p>';
    }
    $conn->close();
    ?>
</div>

                <script>
                    function showEditForm(id) {
                        const editForm = document.getElementById(`edit-form-${id}`);
                        editForm.style.display = 'block'; // Show the edit form
                    }

                    function hideEditForm(id) {
                        const editForm = document.getElementById(`edit-form-${id}`);
                        editForm.style.display = 'none'; // Hide the edit form
                    }

                </script>
                        
                        <button id="addAddressBtn" class="add-new-btn">+ Add New Address</button>
                        <!-- Add Address Form -->
                        <form id="addressForm" class="address-form hidden">
                            <h3>Add New Address</h3>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" name="firstName">
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" name="lastName">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Street Address</label>
                                <input type="text" name="street">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>District</label>
                                    <input type="text" name="District">
                                </div>
                                <div class="form-group">
                                    <label>State</label>
                                    <input type="text" name="state">
                                </div>
                                <div class="form-group">
                                    <label>Zip Code</label>
                                    <input type="text" name="zipCode">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn-secondary">Cancel</button>
                                <button type="submit" class="btn-primary">Use This Address</button>
                            </div>
                        </form>
                    </section>

                    <section class="payment-section">
                        <h2>Payment Method</h2>
                        <button id="addPaymentBtn" class="add-new-btn">+ Add Payment Method</button>
                        <form id="paymentForm" class="payment-form hidden">
                            <div class="form-header">
                                <span class="card-icon"></span>
                                <h3>Credit or Debit Card</h3>
                            </div>
                            <div class="form-group">
                                <label>Name on Card</label>
                                <input type="text" name="cardName">
                            </div>
                            <div class="form-group">
                                <label>Card Number</label>
                                <input type="text" name="cardNumber" placeholder="0000 0000 0000 0000">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Expiry Date (MM/YY)</label>
                                    <input type="text" name="expiry" placeholder="MM/YY">
                                </div>
                                <div class="form-group">
                                    <label>CVC/CVV</label>
                                    <input type="text" name="cvc" placeholder="123">
                                </div>
                            </div>
                            <!-- <div class="form-group">
                                <label class="checkbox">
                                    <input type="checkbox" name="saveCard">
                                    <span>Save this card</span>
                                </label>
                            </div> -->
                            <div class="form-actions">
                                <button type="button" class="btn-secondary">Cancel</button>
                                <button type="submit" class="btn-primary">Use This Card</button>
                            </div>
                        </form>
                    </section>
                    <div class="payment-method">
                        <label>
                            <input type="radio" name="payment-method" value="google-pay">
                            Google Pay
                        </label>
                        <div id="google-pay-button"></div>
                    </div>
                </div>

                <aside class="order-summary">
                    <h2>Order Summary</h2>
                    <div class="products-list" id="productsList"></div>
                    <div class="summary-details">
                        <div class="summary-row">
                            <span>Items (5):</span>
                            <span>₹365.24</span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping & Handling:</span>
                            <span>₹2.50</span>
                        </div>
                        <div class="summary-row">
                            <span>Before Tax:</span>
                            <span>₹367.74</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax Collected (20%):</span>
                            <span>₹73.54</span>
                        </div>
                        <div class="summary-total">
                            <span>Order Total</span>
                            <span>₹504.77</span>
                        </div>
                    </div>
                    <button class="btn-primary btn-large">Place Order</button>
                </aside>
            </div>
        </div>
    </main>

    <script src="https://pay.google.com/gp/p/js/pay.js" async></script>

    <script>
        // Sample products data
const products = [
    {
        id: 1,
        name: 'Cutting Tool',
        color: 'Glacial Grey',
        price: 75.00,
        quantity: 1,
        image: ''
    },
    {
        id: 2,
        name: 'Cutting Tool',
        color: 'Space Grey',
        price: 98.86,
        quantity: 1,
        image: ''
    },
    {
        id: 3,
        name: 'Cutting Tool',
        color: 'Off White',
        price: 267.50,
        quantity: 1,
        image: ''
    },
    {
        id: 4,
        name: 'Cutting Tool',
        color: 'Red Velvet',
        price: 291.07,
        quantity: 1,
        image: ''
    },
    {
        id: 5,
        name: 'Cutting Tool',
        color: 'Glacial Green',
        price: 226.20,
        quantity: 1,
        image: ''
    }
];

// Initialize the page
document.addEventListener('DOMContentLoaded', () => {
    renderProducts();
    setupFormHandlers();
});

// Render products in the order summary
function renderProducts() {
    const productsList = document.getElementById('productsList');
    productsList.innerHTML = products.map(product => `
        <div class="product-item">
            <div class="product-image">
                <img src="${product.image}" alt="${product.name}">
                <span class="product-quantity">${product.quantity}</span>
            </div>
            <div class="product-info">
                <div class="product-name">${product.name}</div>
                <div class="product-color">${product.color}</div>
            </div>
            <div class="product-price">₹${product.price.toFixed(2)}</div>
        </div>
    `).join('');
}

// Setup form handlers
function setupFormHandlers() {
    // Address form
    const addAddressBtn = document.getElementById('addAddressBtn');
    const addressForm = document.getElementById('addressForm');
    
    addAddressBtn.addEventListener('click', () => {
        addAddressBtn.classList.add('hidden');
        addressForm.classList.remove('hidden');
    });

    addressForm.querySelector('.btn-secondary').addEventListener('click', () => {
        addressForm.classList.add('hidden');
        addAddressBtn.classList.remove('hidden');
    });

    addressForm.addEventListener('submit', (e) => {
        e.preventDefault();
        addressForm.classList.add('hidden');
        addAddressBtn.classList.remove('hidden');
    });

    // Payment form
    const addPaymentBtn = document.getElementById('addPaymentBtn');
    const paymentForm = document.getElementById('paymentForm');
    
    addPaymentBtn.addEventListener('click', () => {
        addPaymentBtn.classList.add('hidden');
        paymentForm.classList.remove('hidden');
    });

    paymentForm.querySelector('.btn-secondary').addEventListener('click', () => {
        paymentForm.classList.add('hidden');
        addPaymentBtn.classList.remove('hidden');
    });

    paymentForm.addEventListener('submit', (e) => {
        e.preventDefault();
        paymentForm.classList.add('hidden');
        addPaymentBtn.classList.remove('hidden');
    });
}
// Initialize the Google Pay API
const paymentsClient = new google.payments.api.PaymentsClient({ environment: 'TEST' });

// Create and display the Google Pay button
const googlePayButton = paymentsClient.createButton({
    onClick: onGooglePayButtonClicked,
});

// Add Google Pay button to the page
document.getElementById('google-pay-button').appendChild(googlePayButton);

// Define payment request
const paymentDataRequest = {
    apiVersion: 2,
    apiVersionMinor: 0,
    allowedPaymentMethods: [
        {
            type: 'CARD',
            parameters: {
                allowedAuthMethods: ['PAN_ONLY', 'CRYPTOGRAM_3DS'],
                allowedCardNetworks: ['VISA', 'MASTERCARD'],
            },
            tokenizationSpecification: {
                type: 'PAYMENT_GATEWAY',
                parameters: {
                    gateway: 'example',
                    gatewayMerchantId: 'exampleMerchantId', // Replace with your merchant ID
                },
            },
        },
    ],
    merchantInfo: {
        merchantId: 'BCR2DN4T5TTGLF6P', // Replace with your Merchant ID
        merchantName: 'Your Merchant Name',
    },
    transactionInfo: {
        totalPriceStatus: 'FINAL',
        totalPrice: '10.00', // Replace with your product's price
        currencyCode: 'USD',
        countryCode: 'US',
    },
};

// Handle button click
function onGooglePayButtonClicked() {
    paymentsClient.loadPaymentData(paymentDataRequest)
        .then(function (paymentData) {
            // Handle successful payment
            console.log('Payment successful:', paymentData);
        })
        .catch(function (error) {
            // Handle errors
            console.error('Payment failed:', error);
        });
}
    </script>
</body>
</html>