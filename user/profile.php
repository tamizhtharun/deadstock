<?php
ob_start();
require_once('header.php') ?> 
<?php
ob_start();
include '../db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_session']['id'])) {
    header('Location: ../index.php');
    exit;
}


// Add this near the top of the content area, just before the tab content
if (isset($_GET['success']) || isset($_GET['error'])) {
    $alertType = isset($_GET['success']) ? 'success' : 'error';
    $alertMessage = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : htmlspecialchars($_GET['error']);
    echo "
    <div class='premium-alert {$alertType}'>
        {$alertMessage}
        <button class='close-alert'>&times;</button>
    </div>
    <script>
    document.querySelector('.close-alert').addEventListener('click', function() {
        this.closest('.premium-alert').style.display = 'none';
    });
    </script>
    ";
}
// Assuming user ID is stored in session
$userId = $_SESSION['user_session']['id'];

try {
    // Query to fetch user information and addresses
    $query = "
        SELECT 
            u.username, 
            u.created_at AS user_created_at, 
            u.profile_image,
            a.id AS address_id, 
            a.full_name, 
            a.phone_number, 
            a.address, 
            a.city, 
            a.state, 
            a.pincode, 
            a.address_type, 
            a.is_default, 
            a.created_at AS address_created_at, 
            a.updated_at AS address_updated_at
        FROM 
            users u
        LEFT JOIN 
            users_addresses a ON u.id = a.user_id
        WHERE 
            u.id = :user_id
        ORDER BY 
            a.is_default DESC, 
            a.created_at DESC
    ";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT); // Bind the user_id parameter
    $stmt->execute();
    
    // Fetch results
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Separate user information and addresses
    $userInfo = [];
    $addresses = [];

    foreach ($result as $row) {
        if (empty($userInfo)) {
            $userInfo = [
                'username' => $row['username'],
                'created_at' => $row['user_created_at'],
                'profile_image' => $row['profile_image'],
            ];
        }

        if (!empty($row['address_id'])) {
            $addresses[] = [
                'id' => $row['address_id'],
                'full_name' => $row['full_name'],
                'phone_number' => $row['phone_number'],
                'address' => $row['address'],
                'city' => $row['city'],
                'state' => $row['state'],
                'pincode' => $row['pincode'],
                'address_type' => $row['address_type'],
                'is_default' => $row['is_default'],
                'created_at' => $row['address_created_at'],
                'updated_at' => $row['address_updated_at'],
            ];
        }
    }

    // Orders Query
    $orderQuery = "
        SELECT 
            o.id, 
            o.order_id, 
            o.quantity, 
            o.price, 
            o.order_status, 
            o.created_at,
            p_name AS product_name,
            p_featured_photo AS product_image,
            p.id AS p_id,
            o.order_type,
            o.tracking_id,
            o.payment_id
        FROM 
            tbl_orders o
        JOIN 
            tbl_product p ON o.product_id = p.id
        WHERE 
            o.user_id = :user_id
        ORDER BY 
            o.created_at DESC
    ";
    
    $orderStmt = $pdo->prepare($orderQuery);
    $orderStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $orderStmt->execute();
    
    $orders = $orderStmt->fetchAll(PDO::FETCH_ASSOC);
    

    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

$biddingQuery = "
    SELECT 
        b.bid_id, 
        b.bid_price, 
        b.bid_quantity, 
        b.bid_time, 
        b.bid_status,
        b.payment_id AS bid_payment_id,
        b.refund_id AS bid_refund_id,
        p.p_name AS product_name,
        p.p_featured_photo AS product_image,
        p.id AS p_id,
        p.p_current_price AS current_price
    FROM 
        bidding b
    JOIN 
        tbl_product p ON b.product_id = p.id
    WHERE 
        b.user_id = :user_id
    ORDER BY 
        b.bid_time DESC
";

$biddingStmt = $pdo->prepare($biddingQuery);
$biddingStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
$biddingStmt->execute();

$bids = $biddingStmt->fetchAll(PDO::FETCH_ASSOC);


// print_r($addresses);
// Set default avatar if no profile image is available
$defaultAvatar = "https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y";
$profileImage = !empty($userInfo['profile_image']) 
    ? '../uploads/profile-photos/' . htmlspecialchars($userInfo['profile_image'])
    : $defaultAvatar;


// Simulating user data - in production, this would come from a database
// $user = [
//     'name' => 'John Doe',
//     'email' => 'john@example.com',
//     'phone' => '+1 234 567 8900',
//     'member_since' => 'Jan 2024',
//     'profile_image' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&h=400&fit=crop'
// ];

// $addresses = [
//     [
//         'type' => 'HOME',
//         'name' => 'John Doe',
//         'phone' => '+1 234 567 8900',
//         'street' => '123 Main Street',
//         'city' => 'New York',
//         'state' => 'NY',
//         'zip' => '10001',
//         'is_default' => true
//     ],
//     [
//         'type' => 'WORK',
//         'name' => 'John Doe',
//         'phone' => '+1 234 567 8900',
//         'street' => '456 Office Boulevard',
//         'city' => 'New York',
//         'state' => 'NY',
//         'zip' => '10002',
//         'is_default' => false
//     ]
// ];

// $orders = [
//     [
//         'id' => 'ORD-2024-001',
//         'date' => '2024-03-15',
//         'status' => 'Delivered',
//         'total' => 299.99,
//         'items' => 3
//     ],
//     [
//         'id' => 'ORD-2024-002',
//         'date' => '2024-03-10',
//         'status' => 'In Transit',
//         'total' => 149.50,
//         'items' => 2
//     ]
// ];


$active_tab = $_GET['tab'] ?? 'profile';


?>

    <link rel="stylesheet" href="css/profile.css">

    <div class="container">

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-image-container">
                <?php
                    $defaultAvatar = "https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y"; // Default avatar URL
                    $profileImage = isset($userInfo['profile_image']) && !empty($userInfo['profile_image']) 
        ? 'uploads/profile-photos/' . htmlspecialchars($userInfo['profile_image'])
        : $defaultAvatar;
                ?>
                <!-- Placeholder image if no profile image is available -->
                <img src="<?php echo $profileImage; ?>" alt="Profile" class="profile-image">
            </div>
            <div class="profile-info">
                <h1><?php echo htmlspecialchars($_SESSION['user_session']['username']); ?></h1>
                <p>Member since <?php echo date('F Y', strtotime($_SESSION['user_session']['created_at'])); ?></p>
            </div>
        </div>


        <!-- Navigation Tabs -->
        <div class="tabs">
            <a href="?tab=profile" class="tab <?php echo $active_tab === 'profile' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Personal Information
            </a>
            <a href="?tab=addresses" class="tab <?php echo $active_tab === 'addresses' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                Manage Addresses
            </a>
            <a href="?tab=orders" class="tab <?php echo $active_tab === 'orders' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                </svg>
                My Orders
            </a>
            <a href="?tab=bidding" class="tab <?php echo $active_tab === 'bidding' ? 'active' : ''; ?>">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 20V10"/>
                    <path d="M18 20V4"/>
                    <path d="M6 20v-4"/>
                </svg>
                Bidding
            </a>
        </div>

        <!-- Content Area -->
        <div class="content">
            <?php if ($active_tab === 'profile'): ?>
                <div class="card">
                    <h2>Personal Information</h2>
                    <!-- Personal Information Form -->
                    <form action="update_profile.php" method="POST" class="form" enctype="multipart/form-data">
                    <div class="form-group profile-upload">
    <?php
    $defaultAvatar = "https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y";
    $profileImage = isset($userInfo['profile_image']) && !empty($userInfo['profile_image']) 
        ? 'uploads/profile-photos/' . htmlspecialchars($userInfo['profile_image'])
        : $defaultAvatar;
    ?>
    <div class="profile-image-container">
        <img src="<?php echo $profileImage; ?>" alt="Profile Photo" class="profile-image">
        <input type="file" id="profile_photo" name="profile_photo" accept="image/*" class="file-input">
        <div class="plus-icon" id="trigger-upload">+</div>
    </div>
</div>



                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_SESSION['user_session']['username']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_SESSION['user_session']['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_SESSION['user_session']['phone_number']); ?>" required>
                        </div>
                        <button type="submit" class="button">Save Changes</button>
                    </form>

                </div>
            <?php elseif ($active_tab === 'addresses'): ?>
                <div class="card">
                    <div class="card-header">
                        <h2>Manage Addresses</h2>
                        <button class="button" onclick="showAddressForm()">+ Add New Address</button>
                    </div>
                    <div class="addresses">
                       <?php if (empty($addresses)) {?>
                            <p class="empty">No addresses found. Add a new address to get started.</p>
                        <?php } else{
                        foreach ($addresses as $address): ?>
                            <div class="address-card" data-address-id="<?php echo htmlspecialchars($address['id']); ?>">
                                <div class="address-type">
                                    <span class="badge"><?php echo htmlspecialchars($address['address_type']); ?></span>
                                    <?php if ($address['is_default']): ?>
                                        <span class="badge default">Default</span>
                                    <?php endif; ?>
                                </div>
                                <h3><?php echo htmlspecialchars($address['full_name']); ?></h3>
                                <p class="phone"><?php echo htmlspecialchars($address['phone_number']); ?></p>
                                <p class="address">
                                    <?php echo htmlspecialchars($address['address']); ?><br>
                                    <?php echo htmlspecialchars("{$address['city']}, {$address['state']} {$address['pincode']}"); ?>
                                </p>
                                <div class="address-actions">
                                    <button class="btn-edit" data-id="<?php echo htmlspecialchars($address['id']); ?>">Edit</button>
                                    <?php if (!$address['is_default']): ?>
                                        <button class="btn-delete" data-id="<?php echo htmlspecialchars($address['id']); ?>">Delete</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; }?>
                    </div>

                </div>


                <?php elseif ($active_tab === 'bidding'): ?>
<div class="card">
    <h2>My Bidding History</h2>
    <div class="bidding-list">
        <?php if (empty($bids)): ?>
            <p class="empty-state">No bids found.</p>
        <?php else: ?>
            <?php foreach ($bids as $bid): ?>
                <div class="bidding-card">
                    <div class="bidding-header">
                        <h3><?php echo htmlspecialchars($bid['product_name']); ?></h3>
                        <?php 
    $statusLabels = [
        0 => 'Submitted',
        1 => 'Seen by seller',
        2 => 'Accepted by seller',
        3 => 'Not approved by seller (Please retry with different price)',
        4 => 'Seen by seller'
    ];

    $bidStatusLabel = isset($statusLabels[$bid['bid_status']]) ? $statusLabels[$bid['bid_status']] : 'N/A';
?>
                        <span class="badge <?php echo $bid['bid_status'] == 2 ? 'active' : 'inactive'; ?>">
    <?php echo $bidStatusLabel; ?>
</span>
                    </div>
                    <div class="bidding-details">
                        <p>Your Bid: ₹<?php echo number_format($bid['bid_price'], 2); ?></p>
                        <p>Quantity: <?php echo htmlspecialchars($bid['bid_quantity']); ?></p>
                        <p>Bid Time: <?php echo date('M d, Y H:i', strtotime($bid['bid_time'])); ?></p>
                    </div>
                    <button class="button view-bid-details" data-bid-id="<?php echo $bid['bid_id']; ?>">View Details</button>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
    </div>
    <?php elseif ($active_tab === 'orders'): ?>
                <div class="card">
    <h2>My Orders</h2>
    <div class="orders">
        <?php if (empty($orders)): ?>
            <p class="empty-state">No orders found.</p>
        <?php else: ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <h3>Order ID: <?php echo htmlspecialchars($order['order_id']); ?></h3>
                            <p class="date">Placed on <?php echo date('M d, Y', strtotime($order['created_at'])); ?></p>
                        </div>
                        <!-- <span class="status">
                            <?php echo htmlspecialchars($order['order_status']); ?>
                        </span> -->
                    </div>
                    <div class="order-details" >
                        <a href="../product_landing.php?id=<?php echo $order['p_id']; ?>">
                        <div class="product-info">
                            <img src="../assets/uploads/product-photos/<?php echo htmlspecialchars($order['product_image']); ?>" alt="Product Image" class="product-thumbnail">
                            <div class="product-details">
                                <h3><?php echo htmlspecialchars($order['product_name']); ?></h3>
                                <p>Quantity: <?php echo htmlspecialchars($order['quantity']); ?></p>
                                <p>Order type: <?php if($order['order_type']='bid'){
                                    echo "Bidded Order";
                                }else{
                                    echo "Direct Order";
                                };?>
                            </div>
                        </div></a>
                        <h2 class="total">Total: ₹<?php echo number_format($order['price'] * $order['quantity'], 2); ?></h2>
                    </div>
                    <button class="button secondary">View Details</button>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
            <?php endif; ?>
            
        </div>
    </div>

     <!-- Address Form Modal -->
     <div id="addressModal" class="modal">
            <div class="modal-content">
                <h3>Add New Address</h3>
                <form method="POST" action="add_address.php" class="address-form">
                    <input type="hidden" name="form_type" value="address">
                    <input type="hidden" name="action" value="add">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="addr_name">Full Name</label>
                            <input type="text" id="addr_name" name="addr_name" required>
                        </div>
                        <div class="form-group">
                            <label for="addr_phone">Phone Number</label>
                            <input type="tel" id="addr_phone" name="addr_phone" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" required></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required>
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" id="state" name="state" required>
                        </div>
                        <div class="form-group">
                            <label for="pincode">Pincode</label>
                            <input type="text" id="pincode" name="pincode" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <!-- <div class="form-group">
                            <label>Address Type</label>
                            <div class="radio-group">
                                <input type="radio" id="home" name="type" value="Primary" checked>
                                <label for="home">PRIMARY</label>
                                <input type="radio" id="work" name="type" value="Secondary">
                                <label for="work">SECONDARY</label>
                            </div>
                        </div> -->
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="default">
                                Make this my default address
                            </label>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn-secondary" onclick="closeAddressModal()">Cancel</button>
                        <button type="submit" class="btn-primary">Save Address</button>
                    </div>
                </form>
            </div>
        </div>


        <div class="order-details-modal" id="orderDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalProductName"></h2>
                <button class="modal-close">&times;</button>
            </div>
            <div class="order-grid">
                <!-- <div>
                    <img id="modalProductImage" src="" alt="Product Image" class="product-image">
                </div> -->
                <div>
                <div class="progress-track">
                <ul id="progressbar">
                    <li class="step0 active " id="step1">Ordered</li>
                    <li class="step0 active text-center" id="step2">Packed</li>
                    <li class="step0 active text-right" id="step3">Shipped</li>
                    <li class="step0 text-right" id="step4">Delivered</li>
                </ul>
            </div>
                </div>
                <div class="order-info">
                    <div class="order-info-item">
                        <strong>Order ID</strong>
                        <span id="modalOrderId"></span>
                    </div>
                    <div class="order-info-item">
                        <strong>Order Date</strong>
                        <span id="modalOrderDate"></span>
                    </div>
                    <!-- <div class="order-info-item">
                        <strong>Status</strong>
                        <span id="modalOrderStatus"></span>
                    </div> -->
                    <!-- <div class="order-info-item">
                        <strong>Product</strong>
                        <span id="modalProductName"></span>
                    </div> -->
                    <div class="order-info-item">
                        <strong>Quantity</strong>
                        <span id="modalOrderQuantity"></span>
                    </div>
                    <div class="order-info-item">
                        <strong>Unit Price</strong>
                        <span id="modalUnitPrice"></span>
                    </div>
                    <div class="order-info-item">
                        <strong>Payment Status</strong>
                        <span id="paymentstatus"></span>
                    </div>
                    <div class="order-info-item">
                        <strong>Tracking ID</strong>
                        <span id="modalTrackingID"></span>
                    </div>
                </div>
            </div>
            <div class="total-section">
                <h3 style="color:#2E8B57">Total: <span id="modalTotal"></span></h3>
            </div>
        </div>
    </div>

    <!-- bid details model  -->
     <!-- Bid Details Modal -->
<div class="bid-details-modal" id="bidDetailsModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalBidProductName"></h2>
            <button class="modal-close bid-modal-close">&times;</button>
        </div>
        <div class="bid-details-grid">
            <!-- <div class="bid-image-container">
                <img id="modalBidProductImage" src="" alt="Product Image" class="product-image">
            </div> -->
            <div class="bid-info">
                <div class="bid-info-item">
                    <strong>Bid ID</strong>
                    <span id="modalBidId"></span>
                </div>
                <div class="bid-info-item">
                    <strong>Bid Time</strong>
                    <span id="modalBidTime"></span>
                </div>
                <div class="bid-info-item">
                    <strong>Bid Status</strong>
                    <span id="modalBidStatus"></span>
                </div>
                <div class="bid-info-item">
                    <strong>Current Product Price</strong>
                    <span id="modalCurrentPrice"></span>
                </div>
                <div class="bid-info-item">
                    <strong>Your Bid Price</strong>
                    <span id="modalBidPrice"></span>
                </div>
                <div class="bid-info-item">
                    <strong>Bid Quantity</strong>
                    <span id="modalBidQuantity"></span>
                </div>
                <div class="bid-info-item">
                    <strong>Payment ID</strong>
                    <span id="bidpaymentID"></span>
                </div>
                <div class="bid-info-item">
                    <strong>Refund ID</strong>
                    <span id="bidrefundID"></span>
                </div>
                <div class="bid-info-item">
                    <strong>Total Bid Value</strong>
                    <span id="modalTotalBidValue"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php //require_once '../footer.php'; ?>
    
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const bidDetails = <?php echo json_encode($bids); ?>;
    const bidDetailsModal = document.getElementById('bidDetailsModal');
    const viewBidDetailsButtons = document.querySelectorAll('.view-bid-details');
    const closeBidModalBtn = document.querySelector('.bid-modal-close');


    viewBidDetailsButtons.forEach((button, index) => {
    button.addEventListener('click', function() {
        // Ensure we have bid details for this index
        if (index < bidDetails.length) {
            const bid = bidDetails[index];
            
            document.getElementById('modalBidProductName').textContent = bid.product_name || 'N/A';
            document.getElementById('modalBidId').textContent = bid.bid_id || 'N/A';
            document.getElementById('modalBidTime').textContent = bid.bid_time ? new Date(bid.bid_time).toLocaleString() : 'N/A';
            
            // Add label for bid_status
            const statusLabels = {
                0: 'Submitted',
                1: 'Seen by seller',
                2: 'Accepted by seller',
                3: 'Not approved by seller (Please retry with different price)',
                4: 'Seen by seller'
            };

            const statusElement = document.getElementById('modalBidStatus');
            const bidStatusLabel = statusLabels[bid.bid_status] || 'N/A';
            statusElement.textContent = bidStatusLabel;
            statusElement.className = 'badge ' + (bid.bid_status && bid.bid_status == 2 ? 'active' : 'inactive');
            
            document.getElementById('modalCurrentPrice').textContent = bid.current_price ? '₹' + parseFloat(bid.current_price).toFixed(2) : 'N/A';
            document.getElementById('modalBidPrice').textContent = bid.bid_price ? '₹' + parseFloat(bid.bid_price).toFixed(2) : 'N/A';
            document.getElementById('modalBidQuantity').textContent = bid.bid_quantity || 'N/A';
            document.getElementById('modalTotalBidValue').textContent = bid.bid_price && bid.bid_quantity 
                ? '₹' + (bid.bid_price * bid.bid_quantity).toFixed(2) 
                : 'N/A';
                document.getElementById('bidpaymentID').textContent = bid.bid_payment_id;
                document.getElementById('bidrefundID').textContent = bid.bid_refund_id || 'N/A';
            
            // const productImageElement = document.getElementById('modalBidProductImage');
            // productImageElement.src = bid.product_image 
            //     ? '../assets/uploads/product-photos/' + bid.product_image 
            //     : '../path/to/default/image.jpg';
            
            // Explicitly add 'show' class
            bidDetailsModal.classList.add('show');
            bidDetailsModal.style.display = 'flex';
        } else {
            console.error('No bid details found for index:', index);
        }
    });
});

    // Close modal functionality
    closeBidModalBtn.addEventListener('click', function() {
        bidDetailsModal.classList.remove('show');
        bidDetailsModal.style.display = 'none';
    });

    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        if (event.target === bidDetailsModal) {
            bidDetailsModal.classList.remove('show');
            bidDetailsModal.style.display = 'none';
        }
    });
});


document.addEventListener('DOMContentLoaded', function() {
    // Status label and color mapping
    const statusMap = {
        'pending': { label: 'Pending', color: '#FFA500' },     // Orange
        'processing': { label: 'Processing', color: '#1E90FF' }, // Dodger Blue
        'shipped': { label: 'Shipped', color: '#4682B4' },     // Steel Blue
        'delivered': { label: 'Delivered', color: '#2E8B57' }, // Sea Green
        'canceled': { label: 'Cancelled', color: '#DC143C' }    // Crimson
    };

    const orderDetailsModal = document.getElementById('orderDetailsModal');
    const closeModalBtn = document.querySelector('.modal-close');
    const viewDetailsButtons = document.querySelectorAll('.button.secondary');

    const orderDetails = <?php echo json_encode($orders); ?>;

    viewDetailsButtons.forEach((button, index) => {
        button.addEventListener('click', function() {
            const order = orderDetails[index];
            
            document.getElementById('modalOrderId').textContent = order.order_id;
            document.getElementById('modalOrderDate').textContent = new Date(order.created_at).toLocaleDateString();
            
            // Apply status label and color
            // const status = order.order_status.toLowerCase();
            // const statusInfo = statusMap[status] || { label: status, color: '#888' };
            // const statusElement = document.getElementById('modalOrderStatus');
            // statusElement.textContent = statusInfo.label;
            // statusElement.style.color = statusInfo.color;
            // statusElement.style.fontWeight = 'bold';
            
            // document.getElementById('modalProductImage').src = '../assets/uploads/product-photos/' + order.product_image;
            document.getElementById('modalProductName').textContent = order.product_name;
            document.getElementById('modalOrderQuantity').textContent = order.quantity;
            document.getElementById('modalUnitPrice').textContent = '₹' + parseFloat(order.price).toFixed(2);
            document.getElementById('paymentstatus').textContent = order.payment_id !== null ? 'Paid: ' + order.payment_id : 'Not Paid';
            document.getElementById('modalTrackingID').textContent = order.tracking_id || 'Not Generated';
            const subtotal = order.price * order.quantity;
            document.getElementById('modalTotal').textContent = '₹' + subtotal.toFixed(2);

            orderDetailsModal.classList.add('show');
        });
    });

    closeModalBtn.addEventListener('click', function() {
        orderDetailsModal.classList.remove('show');
    });

    window.addEventListener('click', function(event) {
        if (event.target === orderDetailsModal) {
            orderDetailsModal.classList.remove('show');
        }
    });
});

// Add this to your existing JavaScript in profile.php
document.addEventListener('DOMContentLoaded', function() {
    // Edit address functionality
    const editButtons = document.querySelectorAll('.btn-edit');
    editButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const addressId = this.getAttribute('data-id');
            
            // Fetch address details
            fetch(`edit_address.php?id=${addressId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    
                    // Populate the modal form
                    document.getElementById('addr_name').value = data.full_name;
                    document.getElementById('addr_phone').value = data.phone_number;
                    document.getElementById('address').value = data.address;
                    document.getElementById('city').value = data.city;
                    document.getElementById('state').value = data.state;
                    document.getElementById('pincode').value = data.pincode;
                    document.querySelector('input[name="default"]').checked = data.is_default == 1;
                    
                    // Update form action and method
                    const form = document.querySelector('.address-form');
                    form.action = 'edit_address.php';
                    
                    // Add address ID to form
                    let addressIdInput = document.querySelector('input[name="address_id"]');
                    if (!addressIdInput) {
                        addressIdInput = document.createElement('input');
                        addressIdInput.type = 'hidden';
                        addressIdInput.name = 'address_id';
                        form.appendChild(addressIdInput);
                    }
                    addressIdInput.value = addressId;
                    
                    // Show modal
                    showAddressForm();
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to fetch address details');
                });
        });
    });
    
    // Delete address functionality
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this address?')) {
                const addressId = this.getAttribute('data-id');
                
                // Create and submit form
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'delete_address.php';
                
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'address_id';
                input.value = addressId;
                
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
    
    // Reset form when adding new address
    const addAddressButton = document.querySelector('button[onclick="showAddressForm()"]');
    if (addAddressButton) {
        addAddressButton.addEventListener('click', function() {
            const form = document.querySelector('.address-form');
            form.reset();
            form.action = 'add_address.php';
            
            // Remove address_id input if it exists
            const addressIdInput = form.querySelector('input[name="address_id"]');
            if (addressIdInput) {
                addressIdInput.remove();
            }
        });
    }
});


</script>

<!-- tracking css -->
 <style>
#progressbar {
    margin-bottom: 3vh;
    overflow: hidden;
    color: rgb(252, 103, 49);
    padding-left: 0px;
    margin-top: 1vh;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
}

#progressbar li {
    list-style-type: none;
    font-size: x-small;
    width: 25%;
    text-align: center;
    position: relative;
    font-weight: 400;
    color: rgb(160, 159, 159);
    z-index: 2;
}

#progressbar li:before {
    content: "";
    width: 15px;
    height: 15px;
    background: #ddd;
    border-radius: 50%;
    display: block;
    margin: 0 auto 10px;
    position: relative;
    z-index: 2;
}

#progressbar li:after {
    content: '';
    height: 2px;
    background: #ddd;
    position: absolute;
    left: -50%;
    right: 50%;
    top: 7px;
    z-index: 1;
}

#progressbar li:first-child:after {
    left: 50%;
}

#progressbar li:last-child:after {
    /* right: -50%; */
}

#progressbar li.active:before,
#progressbar li.active:after {
    background: rgb(0, 170, 20);
}

#progressbar li.active {
    color: black;
}
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const progressSteps = {
        'pending': ['step1'],
        'processing': ['step1', 'step2'],
        'shipped': ['step1', 'step2', 'step3'],
        'delivered': ['step1', 'step2', 'step3', 'step4'],
        'canceled': ['step4'], // Separate style for canceled
        'returned': ['step4']  // Separate style for returned
    };

    function updateProgressBar(status) {
        const progressbar = document.getElementById('progressbar');
        const steps = progressSteps[status.toLowerCase()] || [];

        // Reset all steps
        ['step1', 'step2', 'step3', 'step4'].forEach(stepId => {
            const step = document.getElementById(stepId);
            step.classList.remove('active');
            step.classList.remove('canceled');
            step.classList.remove('returned');
        });

        // Activate steps
        steps.forEach(stepId => {
            const step = document.getElementById(stepId);
            step.classList.add('active');
            
            // Special styling for canceled or returned
            if (status.toLowerCase() === 'canceled') {
                step.classList.add('canceled');
            } else if (status.toLowerCase() === 'returned') {
                step.classList.add('returned');
            }
        });

        // Update last step text based on status
        const lastStep = document.getElementById('step4');
        switch(status.toLowerCase()) {
            case 'delivered':
                lastStep.textContent = 'Delivered';
                break;
            case 'canceled':
                lastStep.textContent = 'Cancelled';
                break;
            case 'returned':
                lastStep.textContent = 'Returned';
                break;
            default:
                lastStep.textContent = 'Delivered';
        }
    }

    // Modify existing order details modal script to use the new updateProgressBar function
    const viewDetailsButtons = document.querySelectorAll('.button.secondary');
    const orderDetails = <?php echo json_encode($orders); ?>;

    viewDetailsButtons.forEach((button, index) => {
        button.addEventListener('click', function() {
            const order = orderDetails[index];
            
            // Existing code...
            
            // Update progress bar based on order status
            updateProgressBar(order.order_status || 'processing');
        });
    });
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle profile photo upload
    const fileInput = document.getElementById('profile_photo');
    const triggerUpload = document.getElementById('trigger-upload');
    const profileImage = document.querySelector('.profile-image');

    if (triggerUpload && fileInput) {
        triggerUpload.addEventListener('click', function() {
            fileInput.click();
        });

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    profileImage.src = e.target.result;
                };

                reader.readAsDataURL(this.files[0]);
                
                // Automatically submit the form when a file is selected
                const form = this.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    }
});
</script>

<!-- Order Details Modal CSS -->
<style>

       

        .bid-details-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    
}
.container{
    position: relative;
    margin-top: 0px !important;
}

.view-bid-details{
    background-color:#edf2f7;
    color:#4a5568;
}
.view-bid-details:hover{
    background-color:rgb(231, 231, 239);
    /* color:white; */
}

.bid-details-modal.show {
    opacity: 1;
    visibility: visible;
}

.bid-details-modal .modal-content {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    width: 90%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
    transform: scale(0.7);
    transition: all 0.3s ease;
    padding: 30px;
}

.bid-details-modal.show .modal-content {
    transform: scale(1);
}

.bid-details-modal .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solidrgb(224, 224, 224);
    /* padding-bottom: 15px; */
    /* margin-bottom: 20px; */
}

.bid-details-modal .modal-header h2 {
    margin: 0;
    font-size: 1rem;
    color: #333;
}

.bid-details-modal .modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: #888;
    cursor: pointer;
    transition: color 0.3s ease;
}

.bid-details-modal .modal-close:hover {
    color: #333;
}

.bid-details-grid {
    display: grid;
    /* grid-template-columns: 1fr 1fr; */
    gap: 20px;
}

.bid-image-container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.bid-image-container .product-image {
    width: 100%;
    max-height: 300px;
    object-fit: cover;
    border-radius: 8px;
}

.bid-info {
    display: grid;
    gap: 10px;
}

.bid-info-item {
    display: flex;
    justify-content: space-between;
    padding: 10px;
    background-color: #f9f9f9;
    border-radius: 6px;
}

.bid-info-item strong {
    color: #555;
}

.badge.active {
    background-color: #28a745;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
}

.badge.inactive {
    background-color: #6c757d;
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
}

@media (max-width: 600px) {
    .bid-details-grid {
        grid-template-columns: 1fr;
    }
}
    </style>


<style>
.premium-alert {
    position: fixed;
    top: -100px;
    left: 50%;
    transform: translateX(-50%) scale(0.7);
    opacity: 0;
    z-index: 1000;
    padding: 15px 40px 15px 15px;
    border-radius: 5px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    max-width: 400px;
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    animation: slideIn 0.5s forwards;
}

@keyframes slideIn {
    0% {
        top: -100px;
        transform: translateX(-50%) scale(0.7);
        opacity: 0;
    }
    100% {
        top: 20px;
        transform: translateX(-50%) scale(1);
        opacity: 1;
    }
}

@keyframes slideOut {
    0% {
        top: 20px;
        transform: translateX(-50%) scale(1);
        opacity: 1;
    }
    100% {
        top: -100px;
        transform: translateX(-50%) scale(0.7);
        opacity: 0;
    }
}

.premium-alert.success {
    background-color: #dff0d8;
    color: #3c763d;
    border: 1px solid #d6e9c6;
}

.premium-alert.error {
    background-color: #f2dede;
    color: #a94442;
    border: 1px solid #ebccd1;
}

.premium-alert .close-alert {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.7;
    transition: opacity 0.3s;
}

.premium-alert .close-alert:hover {
    opacity: 1;
}
.order-details .product-info {
    display: flex;
    align-items: center;
    gap: 15px;
}
.order-details a{
    text-decoration:none !important;
    color: rgb(113, 128, 150);
}

.product-thumbnail {
    width: 120px;
    height: relative;
    object-fit: cover;
    border-radius: 8px;
}

.empty-state {
    text-align: center;
    color: #888;
    padding: 20px;
}
</style>



  <script>
    // JavaScript for address modal
function showAddressForm() {
    const modal = document.getElementById('addressModal');
    modal.style.display = 'flex';
    // Add show class after a brief timeout to trigger transition
    setTimeout(() => {
        modal.classList.add('show');
    }, 10);
}

function closeAddressModal() {
    const modal = document.getElementById('addressModal');
    modal.classList.remove('show');
    // Wait for transition to complete before hiding
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300); // Match this with your transition duration
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('addressModal');
    if (event.target === modal) {
        closeAddressModal();
    }
};

</script>
