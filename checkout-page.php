<?php
//checkout-page.php
require_once('header.php');
require_once('db_connection.php');

if (!isset($_SESSION['user_session']['id'])) {
    header('Location: index.php');
    exit;
}

$userId = $_SESSION['user_session']['id'];

// Handle Buy Now submission
// if (isset($_POST['buy_now'])) {
//     $product_id = $_POST['product_id'];
//     $quantity = $_POST['product_quantity'];

//     // Fetch the product details for buy now
//     $stmt = $pdo->prepare("
//         SELECT id, p_name, p_current_price, p_old_price, p_featured_photo 
//         FROM tbl_product 
//         WHERE id = ? AND p_is_approve = 1
//     ");
//     $stmt->execute([$product_id]);
//     $product = $stmt->fetch(PDO::FETCH_ASSOC);

//     if ($product) {
//         // Store buy now data in session
//         $_SESSION['buy_now'] = [
//             'product_id' => $product_id,
//             'product' => $product,
//             'quantity' => $quantity,
//             'user_id' => $userId
//         ];
//     }
// }

function calculateOrderSummary($items)
{
    $summary = [
        'subtotal' => 0,
        'total_items' => 0,
        'savings' => 0
    ];

    foreach ($items as $item) {
        $current_price = $item['p_current_price'] * $item['quantity'];
        $old_price = $item['p_old_price'] * $item['quantity'];
        $summary['subtotal'] += $current_price;
        $summary['savings'] += max(0, $old_price - $current_price);
        $summary['total_items'] += $item['quantity'];
    }

    $summary['total'] = $summary['subtotal'];
    return $summary;
}

function getOrderItems($pdo)
{
    $user_id = $_SESSION['user_session']['id'];
    $stmt = $pdo->prepare("
        SELECT 
            p.id,
            p.p_name,
            p.p_current_price,
            p.p_old_price,
            p.p_featured_photo,
            c.quantity
        FROM tbl_cart c
        JOIN tbl_product p ON p.id = c.id
        WHERE c.user_id = ?
        AND p.p_is_approve = 1
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch addresses
$stmt = $pdo->prepare("SELECT * FROM users_addresses WHERE user_id = ?");
$stmt->execute([$userId]);
$addresses = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\checkout.css">

</head>

<body>

    <div class="container">
        <div class="cart-header">
            <h1 class="cart-title">Checkout</h1>
            <div class="cart-steps">
                <div class="step1">
                    <span class="step-number1">1</span>
                    <span class="step-text1">Cart</span>
                </div>
                <div class="step-divider2"></div>
                <div class="step2">
                    <span class="step-number2">2</span>
                    <span class="step-text2">Shipping</span>
                </div>
                <div class="step-divider"></div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-text">Payment</span>
                </div>
            </div>
        </div>
        <div class="checkout-container">
            <div class="forms-container">
                <section class="shipping-section">
                    <h2>Delivery Address</h2>
                    <div class="saved-addresses">
                        <?php if (count($addresses) > 0): ?>
                            <?php foreach ($addresses as $row): ?>
                                <label class="address-option">
                                    <input type="radio" name="address" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <div class="address-details">
                                        <span class="name"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                        <span class="phone">📞 <?php echo htmlspecialchars($row['phone_number']); ?></span>
                                        <span class="address">
                                            <?php echo htmlspecialchars($row['address']) . ', ' .
                                                htmlspecialchars($row['city']) . ', ' .
                                                htmlspecialchars($row['state']) . ' - ' .
                                                htmlspecialchars($row['pincode']) .
                                                ' (' . htmlspecialchars($row['address_type']) . ')'; ?>
                                        </span>
                                    </div>
                                    <div class="address-actions">
                                        <button type="button" class="link-btn view-address"
                                            data-address-id="<?php echo htmlspecialchars($row['id']); ?>">
                                            View Details
                                        </button>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-address">No addresses found. Please add a new address.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <aside class="order-summary">
                <h2>Order Summary</h2>
                <div class="products-list" id="productsList">
                    <?php
                    $items = getOrderItems($pdo);
                    foreach ($items as $item):
                        ?>
                        <div class="product-item">
                            <div class="product-image">
                                <img src="assets/uploads/product-photos/<?php echo htmlspecialchars($item['p_featured_photo']); ?>"
                                    alt="<?php echo htmlspecialchars($item['p_name']); ?>">
                                <!-- <span class="product-quantity"><?php echo $item['quantity']; ?></span> -->
                            </div>
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($item['p_name']); ?></div>
                                <?php if ($item['p_old_price'] > $item['p_current_price']): ?>
                                    <div class="product-savings">
                                        Save ₹<?php echo number_format($item['p_old_price'] - $item['p_current_price'], 2); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="product-price">₹<?php echo number_format($item['p_current_price'], 2); ?></div>
                        </div>
                    <?php
                    endforeach;
                    $summary = calculateOrderSummary($items);
                    ?>
                </div>
                <div class="summary-details">
                    <div class="summary-row">
                        <span>Items (<?php echo $summary['total_items']; ?>):</span>
                        <span>₹<?php echo number_format($summary['subtotal'], 2); ?></span>
                    </div>
                    <?php if ($summary['savings'] > 0): ?>
                        <div class="summary-row text-success">
                            <span>Total Savings:</span>
                            <span>-₹<?php echo number_format($summary['savings'], 2); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="summary-total">
                        <span>Order Total</span>
                        <span>₹<?php echo number_format($summary['total'], 2); ?></span>
                    </div>
                </div>
                <button class="btn-primary btn-large">Place Order</button>
            </aside>
        </div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <script>
        // Add this script to your checkout page
        document.addEventListener('DOMContentLoaded', function () {
            const orderButton = document.querySelector('.btn-primary.btn-large');

            async function initializePayment(e) {
                e.preventDefault();

                // Validate address
                const selectedAddress = document.querySelector('input[name="address"]:checked');
                if (!selectedAddress) {
                    alert('Please select a delivery address');
                    return;
                }

                try {
                    // Disable button and show loading state
                    orderButton.disabled = true;
                    orderButton.textContent = 'Processing...';

                    // Create order
                    const response = await fetch('process-payment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    });

                    // Check if response is OK
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    // Get response text first for debugging
                    const responseText = await response.text();

                    // Try to parse JSON
                    let data;
                    try {
                        data = JSON.parse(responseText);
                    } catch (e) {
                        console.error('Raw response:', responseText);
                        throw new Error('Failed to parse server response');
                    }

                    // Check for success
                    if (!data.success) {
                        throw new Error(data.error || 'Failed to create order');
                    }

                    // Configure Razorpay
                    const options = {
                        key: data.key,
                        amount: data.amount,
                        currency: data.currency,
                        name: 'Deadstock',
                        description: `Order Payment (${data.total_items} items)`,
                        order_id: data.order_id,
                        handler: function (response) {
                            handlePaymentSuccess(response, selectedAddress.value);
                        },
                        prefill: {
                            name: document.querySelector('.name')?.textContent.trim() || '',
                            contact: document.querySelector('.phone')?.textContent.replace('📞 ', '').trim() || ''
                        },
                        modal: {
                            ondismiss: function () {
                                orderButton.disabled = false;
                                orderButton.textContent = 'Place Order';
                            }
                        }
                    };

                    // Initialize Razorpay
                    const rzp = new Razorpay(options);
                    rzp.open();

                } catch (error) {
                    console.error('Payment Error:', error);
                    alert('Payment initialization failed: ' + error.message);

                    // Reset button state
                    orderButton.disabled = false;
                    orderButton.textContent = 'Place Order';
                }
            }

            async function handlePaymentSuccess(response, addressId) {
                try {
                    const verifyData = new FormData();
                    verifyData.append('razorpay_payment_id', response.razorpay_payment_id);
                    verifyData.append('razorpay_order_id', response.razorpay_order_id);
                    verifyData.append('razorpay_signature', response.razorpay_signature);
                    verifyData.append('address_id', addressId);

                    const verifyResponse = await fetch('verify-payment.php', {
                        method: 'POST',
                        body: verifyData
                    });

                    const result = await verifyResponse.json();

                    if (result.success) {
                        window.location.href = 'order-confirmation.php';
                    } else {
                        throw new Error(result.error || 'Payment verification failed');
                    }

                } catch (error) {
                    console.error('Verification Error:', error);
                    alert('Payment verification failed: ' + error.message);
                    orderButton.disabled = false;
                    orderButton.textContent = 'Place Order';
                }
            }

            // Attach click handler
            orderButton.addEventListener('click', initializePayment);
        });
    </script>

    <script>

        document.addEventListener('DOMContentLoaded', function () {
            // Handle View button clicks
            document.querySelectorAll('.view-address').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.stopPropagation(); // Prevent radio button selection when clicking view
                    const addressId = this.getAttribute('data-address-id');
                    const addressOption = this.closest('.address-option');
                    const name = addressOption.querySelector('.name').textContent;
                    const phone = addressOption.querySelector('.phone').textContent;
                    const address = addressOption.querySelector('.address').textContent;

                    showAddressModal(name, phone, address);
                });
            });
        });

        function showAddressModal(name, phone, address) {
            // Create modal if it doesn't exist
            let modal = document.querySelector('.address-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.className = 'address-modal';
                modal.innerHTML = `
            <div class="address-modal-content">
                <button class="modal-close">×</button>
                <h3>Address Details</h3>
                <dl class="address-details-list">
                    <dt>Full Name</dt>
                    <dd class="modal-name"></dd>
                    <dt>Phone Number</dt>
                    <dd class="modal-phone phone-number"></dd>
                    <dt>Complete Address</dt>
                    <dd class="modal-address"></dd>
                </dl>
            </div>
        `;
                document.body.appendChild(modal);

                // Close modal when clicking outside or on close button
                modal.addEventListener('click', function (e) {
                    if (e.target === modal || e.target.className === 'modal-close') {
                        modal.style.display = 'none';
                    }
                });
            }

            // Update modal content
            modal.querySelector('.modal-name').textContent = name;
            modal.querySelector('.modal-phone').textContent = phone;
            modal.querySelector('.modal-address').textContent = address;

            // Display modal
            modal.style.display = 'flex';
        }
    </script>

</body>