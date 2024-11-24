<?php require_once('header.php') ?>
<?php
include '../db_connection.php';

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
    

    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}



// Set default avatar if no profile image is available
$defaultAvatar = "https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y";
$profileImage = !empty($user['profile_image']) 
    ? 'uploads/profile_photos/' . htmlspecialchars($user['profile_image'])  : $defaultAvatar;


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

$orders = [
    [
        'id' => 'ORD-2024-001',
        'date' => '2024-03-15',
        'status' => 'Delivered',
        'total' => 299.99,
        'items' => 3
    ],
    [
        'id' => 'ORD-2024-002',
        'date' => '2024-03-10',
        'status' => 'In Transit',
        'total' => 149.50,
        'items' => 2
    ]
];


$active_tab = $_GET['tab'] ?? 'profile';


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile | E-commerce</title>
    <link rel="stylesheet" href="css/profile.css">
</head>
<body>
    <div class="container">

        <!-- Profile Header -->
        <div class="profile-header">
            <div class="profile-image-container">
                <?php
                    $defaultAvatar = "https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y"; // Default avatar URL
                    $profileImage = isset($user['profile_image']) && !empty($user['profile_image']) 
                        ? htmlspecialchars($user['profile_image']) 
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
                        $defaultAvatar = "https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y"; // Default avatar URL
                        $profileImage = isset($user['profile_image']) && !empty($user['profile_image']) 
                            ? htmlspecialchars($user['profile_image']) 
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
                        <?php foreach ($addresses as $address): ?>
                            <div class="address-card">
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
                                    <button class="btn-edit">Edit</button>
                                    <?php if (!$address['is_default']): ?>
                                        <button class="btn-delete">Delete</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>


                <?php elseif ($active_tab === 'bidding'): ?>
                <div class="card">
                    <h2>My Bidding History</h2>
                    <div class="bidding-list">
                        <div class="bidding-card">
                            <div class="bidding-header">
                                <h3>Product Name</h3>
                                <span class="badge active">Active</span>
                            </div>
                            <div class="bidding-details">
                                <p>Current Bid: $150.00</p>
                                <p>Your Bid: $145.00</p>
                                <p>Ends in: 2d 5h 30m</p>
                            </div>
                            <button class="button">Place New Bid</button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <h2>My Orders</h2>
                    <div class="orders">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div>
                                        <h3>Order <?php echo htmlspecialchars($order['id']); ?></h3>
                                        <p class="date">Placed on <?php echo htmlspecialchars($order['date']); ?></p>
                                    </div>
                                    <span class="status <?php echo strtolower($order['status']); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </div>
                                <div class="order-details">
                                    <p><?php echo $order['items']; ?> items</p>
                                    <p class="total">Total: $<?php echo number_format($order['total'], 2); ?></p>
                                </div>
                                <button class="button secondary">View Details</button>
                            </div>
                        <?php endforeach; ?>
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
                        <div class="form-group">
                            <label>Address Type</label>
                            <div class="radio-group">
                                <input type="radio" id="home" name="type" value="Home" checked>
                                <label for="home">Home</label>
                                <input type="radio" id="work" name="type" value="Work">
                                <label for="work">Work</label>
                            </div>
                        </div>
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
  <script>
    function showAddressForm() {
    const modal = document.getElementById('addressModal');
    modal.style.display = 'flex'; // Ensure it uses flex for centering
      }

      function closeAddressModal() {
          const modal = document.getElementById('addressModal');
          modal.style.display = 'none';
      }

      // Ensure clicking outside the modal closes it
      window.onclick = function(event) {
          const modal = document.getElementById('addressModal');
          if (event.target === modal) {
              closeAddressModal();
          }
    };


    // Trigger script for profile photo update

    document.getElementById('trigger-upload').addEventListener('click', function () {
    document.getElementById('profile_photo').click();
    
});

</script>
</body>
</html>