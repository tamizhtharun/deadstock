<?php
include 'db_connection.php';
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row)
{
  $logo = $row['logo'];
  $favicon = $row['favicon'];
}
?>
<?php
session_start();
// Retrieve the error message from the session
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;

// Clear the error message after retrieving it
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="theme-color" content="#your-brand-color">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dead Stock Processing</title>
    <link rel="icon" href="assets\uploads\<?php echo $favicon?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="./css/responsive.css">
    <link rel="stylesheet" href="./css/header.css">

        <!-- Link Disply the featured categories in home page slider  -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="css/vendor.css">
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <defs>
        <symbol xmlns="http://www.w3.org/2000/svg" id="cart" viewBox="0 0 24 24"><path fill="currentColor" d="M8.5 19a1.5 1.5 0 1 0 1.5 1.5A1.5 1.5 0 0 0 8.5 19ZM19 16H7a1 1 0 0 1 0-2h8.491a3.013 3.013 0 0 0 2.885-2.176l1.585-5.55A1 1 0 0 0 19 5H6.74a3.007 3.007 0 0 0-2.82-2H3a1 1 0 0 0 0 2h.921a1.005 1.005 0 0 1 .962.725l.155.545v.005l1.641 5.742A3 3 0 0 0 7 18h12a1 1 0 0 0 0-2Zm-1.326-9l-1.22 4.274a1.005 1.005 0 0 1-.963.726H8.754l-.255-.892L7.326 7ZM16.5 19a1.5 1.5 0 1 0 1.5 1.5a1.5 1.5 0 0 0-1.5-1.5Z"/></symbol>
        <symbol xmlns="http://www.w3.org/2000/svg" id="star-full" viewBox="0 0 24 24"><path fill="currentColor" d="m3.1 11.3l3.6 3.3l-1 4.6c-.1.6.1 1.2.6 1.5c.2.2.5.3.8.3c.2 0 .4 0 .6-.1c0 0 .1 0 .1-.1l4.1-2.3l4.1 2.3s.1 0 .1.1c.5.2 1.1.2 1.5-.1c.5-.3.7-.9.6-1.5l-1-4.6c.4-.3 1-.9 1.6-1.5l1.9-1.7l.1-.1c.4-.4.5-1 .3-1.5s-.6-.9-1.2-1h-.1l-4.7-.5l-1.9-4.3s0-.1-.1-.1c-.1-.7-.6-1-1.1-1c-.5 0-1 .3-1.3.8c0 0 0 .1-.1.1L8.7 8.2L4 8.7h-.1c-.5.1-1 .5-1.2 1c-.1.6 0 1.2.4 1.6"/></symbol>
            <symbol xmlns="http://www.w3.org/2000/svg" id="star-half" viewBox="0 0 24 24"><path fill="currentColor" d="m3.1 11.3l3.6 3.3l-1 4.6c-.1.6.1 1.2.6 1.5c.2.2.5.3.8.3c.2 0 .4 0 .6-.1c0 0 .1 0 .1-.1l4.1-2.3l4.1 2.3s.1 0 .1.1c.5.2 1.1.2 1.5-.1c.5-.3.7-.9.6-1.5l-1-4.6c.4-.3 1-.9 1.6-1.5l1.9-1.7l.1-.1c.4-.4.5-1 .3-1.5s-.6-.9-1.2-1h-.1l-4.7-.5l-1.9-4.3s0-.1-.1-.1c-.1-.7-.6-1-1.1-1c-.5 0-1 .3-1.3.8c0 0 0 .1-.1.1L8.7 8.2L4 8.7h-.1c-.5.1-1 .5-1.2 1c-.1.6 0 1.2.4 1.6m8.9 5V5.8l1.7 3.8c.1.3.5.5.8.6l4.2.5l-3.1 2.8c-.3.2-.4.6-.3 1c0 .2.5 2.2.8 4.1l-3.6-2.1c-.2-.2-.3-.2-.5-.2"/></symbol>
        </defs>
        </svg>
        
</head>
<style>
    

:root {
  --apple-bg: #ffffff;
  --apple-text: #1d1d1f;
  --apple-secondary: #86868b;
  --apple-blue: #0071e3;
  --apple-gray: #f5f5f7;
  --apple-border: #d2d2d7;
  --apple-shadow: rgba(0, 0, 0, 0.1);
  --apple-radius: 12px;
}

* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: -apple-system, BlinkMacSystemFont, 'SF Pro Text', sans-serif;
}

body {
  background-color: var(--apple-gray);
  color: var(--apple-text);
  -webkit-font-smoothing: antialiased;
}

.navbar {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: fixed;
  top: 0;
  width: 100%;
  z-index: 100;
  border-bottom: 1px solid var(--apple-border);
}

.nav-brand {
  font-size: 1.5rem;
  font-weight: 600;
  letter-spacing: -0.02em;
}

.notification-trigger {
  position: relative;
  cursor: pointer;
  padding: 0.5rem;
  z-index: 20;
}

.notification-badge {
  position: absolute;
  top: 0;
  right: 0;
  background: var(--apple-blue);
  color: white;
  border-radius: 12px;
  padding: 0.15rem 0.4rem;
  font-size: 0.75rem;
  font-weight: 500;
}

.notification-panel {
  position: absolute;
  top: calc(100% + 0.8rem);
  right: -1rem;
  width: 380px;
  background: var(--apple-bg);
  border-radius: var(--apple-radius);
  box-shadow: 0 4px 24px var(--apple-shadow);
  opacity: 0;
  visibility: hidden;
  transform: translateY(-10px);
  transition: all 0.2s ease;
}

.notification-trigger:hover .notification-panel {
  opacity: 1;
  visibility: visible;
  transform: translateY(0);
}

.notification-header {
  padding: 1rem 1.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-bottom: 1px solid var(--apple-border);
}

.notification-header h3 {
  font-size: 1.1rem;
  font-weight: 600;
}

.mark-all-read {
  color: var(--apple-blue);
  background: none;
  border: none;
  font-size: 0.9rem;
  cursor: pointer;
  padding: 0.5rem;
}

.notification-tabs {
  display: flex;
  padding: 0.5rem;
  gap: 0.5rem;
  border-bottom: 1px solid var(--apple-border);
}

.tab {
  padding: 0.5rem 1rem;
  border: none;
  background: none;
  border-radius: 8px;
  font-size: 0.9rem;
  color: var(--apple-secondary);
  cursor: pointer;
  transition: all 0.2s ease;
}

.tab:hover {
  background: var(--apple-gray);
}

.tab.active {
  background: var(--apple-gray);
  color: var(--apple-text);
  font-weight: 500;
}

.notification-list {
  max-height: 400px;
  overflow-y: auto;
}

.notification-item {
  padding: 1rem 1.5rem;
  display: flex;
  gap: 1rem;
  border-bottom: 1px solid var(--apple-border);
  transition: background 0.2s ease;
}

.notification-item:hover {
  background: var(--apple-gray);
}

.notification-item.unread {
  background: rgba(0, 113, 227, 0.05);
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 50%;
  background: var(--apple-gray);
  display: flex;
  align-items: center;
  justify-content: center;
}

.notification-content {
  flex: 1;
}

.notification-title {
  font-weight: 500;
  margin-bottom: 0.25rem;
}

.notification-message {
  color: var(--apple-secondary);
  font-size: 0.9rem;
  line-height: 1.4;
}

.notification-time {
  font-size: 0.8rem;
  color: var(--apple-secondary);
  margin-top: 0.25rem;
}

.notification-image {
  width: 60px;
  height: 60px;
  border-radius: 8px;
  object-fit: cover;
}

.notification-badge {
  position: absolute;
  top: -2px;
  right: -2px;
  background-color: #ff3b30;
  color: white;
  border-radius: 12px;
  font-size: 0.75rem;
  font-weight: 600;
  border: 2px solid var(--apple-bg); /* Matches background color */
  min-width: 20px;
  height: 20px; /* Explicit height for the circle */
  display: flex;
  align-items: center; /* Centers text vertically */
  justify-content: center; /* Centers text horizontally */
  line-height: 1; /* Prevents text from shifting */
  transform: translate(50%, -50%); /* Centers above the bell icon */
  animation: badge-pulse 2s infinite;
}

.notification-list {
  max-height: 350px;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: var(--apple-border) transparent;
}

.mark-all-read {
  color: var(--apple-blue);
  background: none;
  border: none;
  font-size: 1.2rem; /* Icon size */
  cursor: pointer;
  padding: 0.3rem 0.8rem;
  display: flex;
  align-items: center;
  gap: 0.4rem; /* Space between icon and text */
  border-radius: 20px; /* Rounded style */
  transition: background 0.3s ease, transform 0.2s ease;
  height: auto; /* Allow dynamic height */
}

.read-all-text {
  font-size: 0.9rem; /* Slightly smaller than the icon */
  font-weight: 500; /* Medium weight for a premium feel */
  color: var(--apple-blue);
  transition: color 0.2s ease;
}

.mark-all-read:hover {
  background: rgba(0, 113, 227, 0.1); /* Subtle hover background */
  transform: scale(1.05); /* Slight zoom-in effect */
}

.mark-all-read:hover .read-all-text {
  color: var(--apple-text); /* Change text color on hover */
}


</style>
<body>
<div class="header">   
<!-- <?php

echo "<pre>";
print_r($_SESSION); // Display all session variables
echo "</pre>";






?> -->
<nav class="ds-nav-container">
            <div class="ds-logo-section">
                <a href="index.php" class="ds-logo">
                <img src="./assets/uploads/<?php echo $logo?>" alt="Logo" width="30" height="30">
                    <span>Dead Stock</span>
                </a>
            </div>

            <div class="ds-search-section">
                <div class="ds-search-wrapper">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search products..." class="ds-search-input">
                </div>
            </div>
            <div class="ds-actions-section">
    <?php if(isset($_SESSION['user_session'])): ?>
      <button class="ds-btn-secondary" onclick="window.location.href='seller_registration.php';" >Sell here</button>

        <div class="ds-user-controls">
            <button class="ds-icon-button cart-button" title="Shopping Cart"  onclick="window.location.href='cart.php';">
                <i class="fas fa-shopping-cart"></i>
                <span class="ds-cart-badge">3</span>
            </button>
            
            <!-- Notification -->

            <div class="notification-trigger ds-icon-button" id="notificationTrigger">
             <i class="fas fa-bell"></i>
                <span class="notification-badge" id="notificationBadge">3</span>
                <div class="notification-panel" id="notificationPanel">
                    <div class="notification-header">
                        <h3>Notifications</h3>
                        <!-- <button class="mark-all-read">Mark all as read</button> -->
                        <button class="mark-all-read">
                            <i class="fas fa-check-double" title="Mark all as read"></i>
                            <span class="read-all-text">Read All</span>
                        </button>


                    </div>
                    <div class="notification-tabs">
                        <button class="tab active" data-tab="all">All</button>
                        <button class="tab" data-tab="orders">Orders</button>
                        <button class="tab" data-tab="bids">Bids</button>
                    </div>
                    <div class="notification-list" id="notificationList"></div>
                </div>
            </div>

            <div class="ds-profile-menu">
                <button class="ds-profile-trigger">
                 <div class="ds-avatar">
                      <?php
                      // Path to user's avatar
                      $userAvatar = isset($_SESSION['user_session']['avatar']) ? $_SESSION['user_session']['avatar'] : '';
                      $defaultAvatar = "https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y"; // Default avatar from the internet

                      // Check if the file exists
                      $avatarToDisplay = (file_exists($userAvatar) && !empty($userAvatar)) ? $userAvatar : $defaultAvatar;
                      ?>
                      <img src="<?= $avatarToDisplay ?>" alt="Profile">
                  </div>

                </button>
                <div class="ds-menu-dropdown">
                    <div class="ds-menu-header">
                        <img src="<?= $avatarToDisplay ?>" alt="Profile" class="ds-menu-avatar">
                        <div class="ds-user-info">
                            <span class="ds-user-name"><?php echo $_SESSION['user_session']['username']?></span>
                            <span class="ds-user-email"><?php echo $_SESSION['user_session']['email']?></span>
                        </div>
                    </div>
                    <div class="ds-menu-items">
                        <a href="user/profile.php" class="ds-menu-item "style="text-decoration: none !important";>
                            <i class="fas fa-user"></i>
                            <span>Account</span>
                        </a>
                        <a href="/orders" class="ds-menu-item"style="text-decoration: none !important">
                            <i class="fas fa-shopping-bag"></i>
                            <span>Orders</span>
                        </a>
                        <a href="/bidding" class="ds-menu-item"style="text-decoration: none !important">
                            <i class="fas fa-gavel"></i>
                            <span>Bidding</span>
                        </a>
                        <div class="ds-menu-divider"></div>
                        <a href="logout.php" class="ds-menu-item ds-logout"style="text-decoration: none !important">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Logout</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="ds-auth-buttons">
            <button class="ds-btn-secondary" onclick="window.location.href='seller_registration.php';" >Sell here</button>
            <button class="ds-btn-primary" type="button" id="login-btn" data-bs-toggle="modal" data-bs-target="#staticBackdrop">Login</button>
        </div>
    <?php endif; ?>
</div>
 </nav>

  <!-- runningtxt -->
  <!-- runningtxt -->
<div class="scrolling-text">
    <?php
    // Add this query to your existing database connection
    $stmt = $pdo->prepare("SELECT running_text FROM tbl_settings");
    $stmt->execute();
    $running_text = $stmt->fetchColumn();
     $display_text = str_repeat(htmlspecialchars($running_text) . " &nbsp;&nbsp;&nbsp;&nbsp; ", 3);
    ?>
    <div class="scrolling-text-content">
        <?php echo htmlspecialchars($running_text); ?>
    </div>
</div>
</div>

<!-- Login Modal -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="true" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="staticBackdropLabel">Dead Stock</h1>
                <button type="button" id="btn-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="modal-body" class="modal-body">
                <!-- Error Message HTML -->
                <?php if (!empty($error_message)): ?>
                <div class="premium-alert" id="premium-alert">
                    <div class="alert-content">
                        <div class="alert-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                        </div>
                        <span class="alert-message"><?php echo htmlspecialchars($error_message); ?></span>
                        <button class="alert-close" onclick="closeAlert()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                <!-- Login Form -->
                <form id="signin-form" method="POST" action="login.php">
                    <h1 class="modal-title fs-5" id="box-header">Login</h1>
                    <div class="input-box">
                        <input type="email" id="mail" class="input-field" placeholder="Email" name="email" autocomplete="off" required>
                    </div>
                    <div class="input-box">
                        <input type="password" class="input-field" placeholder="Password" name="password" autocomplete="off" required>
                    </div>
                    <div class="forgot" id="forgot-password-link">
                        <section>
                            <a href="#">Forgot password?</a>
                        </section>
                    </div>
                    <div class="input-submit">
                        <button class="submit-btn" id="signin-btn" name="login">
                            <label for="submit">Sign In</label>
                        </button>
                    </div>
                    <div class="sign-up-link">
                        <p>Don't have an account? <a href="#" id="signup-link">Sign Up</a></p>
                    </div>
                </form>

                <!-- Sign Up Form -->
                <form id="signup-form" method="POST" action="register.php" style="display: none;">
                    <h1 class="modal-title fs-5" id="box-header">SignUp</h1>
                    <div class="input-box">
                        <input type="text" class="input-field" placeholder="Username" name="username" autocomplete="off" required>
                    </div>
                    <div class="input-box">
                        <input type="tel" id="phone-number" class="input-field" placeholder="Phone" name="phone_number" autocomplete="off" required pattern="[0-9]{10}">
                    </div>
                    <div class="input-box">
                        <input type="email" id="email" class="input-field" placeholder="Email" name="email" autocomplete="off" required>
                    </div>
                    <div class="input-box">
                        <input id="password" type="password" class="input-field" placeholder="Password" name="password" autocomplete="off" required>
                    </div>
                    <div class="input-box">
                        <input type="text" class="input-field" placeholder="GST (Optional)" name="user_gst" autocomplete="off">
                    </div>
                    <div class="input-submit">
                        <button class="submit-btn" id="signup-btn" name="register">
                            <label for="submit">Sign Up</label>
                        </button>
                    </div>
                    <div class="sign-in-link">
                        <p>Already have an account? <a href="#" id="signin-link">Sign In</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

  <script src="js/index.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script> 
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>


  <!-- Login Modal script -->
  <script>
    document.addEventListener('DOMContentLoaded', () => {
    const modalElement = document.getElementById('staticBackdrop');
    let modal = new bootstrap.Modal(modalElement, {
        backdrop: 'true',  // Enable closing when clicking outside (set to 'true')
        keyboard: false    // Prevent closing when pressing ESC
    });

    // Show the modal if there's an error message
    const errorMessage = "<?php echo addslashes($error_message ?? ''); ?>";
    if (errorMessage) {
        modal.show(); // Show the modal with error message
    }

    // Handle cleanup when the modal is hidden
    modalElement.addEventListener('hidden.bs.modal', () => {
        // Remove the modal backdrop manually
        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();  // Remove the backdrop when modal is closed
        }

        // Reset the modal-open class and body styles
        document.body.classList.remove('modal-open');
        document.body.style.overflow = "";
        document.body.style.paddingRight = "";
    });

    // Handle opening the modal from the login button
    const loginBtn = document.getElementById('login-btn');
    if (loginBtn) {
        loginBtn.addEventListener('click', () => {
            modal.show(); // Show the modal
        });
    }

    // Handle closing the modal using the close button
    const closeButton = document.querySelector('#staticBackdrop .btn-close');
    if (closeButton) {
        closeButton.addEventListener('click', () => {
            modal.hide(); // Hide the modal when the close button is clicked
        });
    }
});

// JavaScript for alert functionality
function closeAlert() {
    const alert = document.getElementById('premium-alert');
    alert.style.animation = 'fadeOut 0.3s ease forwards';
    setTimeout(() => {
        alert.style.display = 'none';
    }, 300);
}

// Add fade out animation
const style = document.createElement('style');
style.textContent = `
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateY(0);
        }
        to {
            opacity: 0;
            transform: translateY(-20px);
        }
    }
`;
document.head.appendChild(style);

//     document.addEventListener('DOMContentLoaded', () => {
//     const errorMessage = "<?php echo addslashes($error_message); ?>"; // Get error message from PHP

//     // If there's an error message, open the modal
//     if (errorMessage) {
//         const loginModal = new bootstrap.Modal(document.getElementById('staticBackdrop'), {
//             backdrop: 'static',
//             keyboard: false
//         });
//         loginModal.show(); // Show the modal
//     }
// });

        // Script Notification Dropdown

        const notifications = [
                    // All Notifications
                    {
                        type: 'all',
                        subtype: 'bid',
                        title: 'New Bid Received',
                        message: 'Someone placed a bid of $250 on Vintage Watch',
                        time: '2 min ago',
                        isRead: false,
                        // image: 'https://images.unsplash.com/photo-1524592094714-0f0654e20314?w=200&q=80'
                        image: 'assets/uploads/logo.png'
                    },
                    {
                        type: 'all',
                        subtype: 'order',
                        title: 'Order Confirmed',
                        message: 'Your order #12345 has been confirmed',
                        time: '15 min ago',
                        isRead: false
                    },
                    // Orders Notifications
                    {
                        type: 'orders',
                        title: 'Shipping Update',
                        message: 'Your package is out for delivery',
                        time: '30 min ago',
                        isRead: false
                    },
                    {
                        type: 'orders',
                        title: 'Order Delivered',
                        message: 'Your recent order has been delivered',
                        time: '2 hours ago',
                        isRead: true
                    },
                    // Bids Notifications
                    {
                        type: 'bids',
                        title: 'Outbid Alert',
                        message: 'Welcome To Deadstock',
                        time: '1 hour ago',
                        isRead: true,
                        // image: 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=200&q=80'
                        image: 'assets/uploads/logo.png'

                    },
                    {
                        type: 'bids',
                        title: 'Bid Accepted',
                        message: 'Your bid on Luxury Watch was accepted',
                        time: '3 hours ago',
                        isRead: false
                    }
                ];

        function createNotificationElement(notification) {
            const div = document.createElement('div');
            div.className = `notification-item ${notification.isRead ? '' : 'unread'}`;
            
            const icon = getNotificationIcon(notification.type);
            
            div.innerHTML = `
                <div class="notification-icon">
                    <i class="${icon}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-time">${notification.time}</div>
                </div>
                ${notification.image ? `<img src="${notification.image}" alt="" class="notification-image">` : ''}
            `;
            
            return div;
        }

        function getNotificationIcon(type) {
            switch(type) {
                case 'bid': return 'fas fa-gavel';
                case 'order': return 'fas fa-shopping-bag';
                default: return 'fas fa-bell';
            }
        }

        function updateNotifications(filter = 'all') {
            const notificationList = document.getElementById('notificationList');
            notificationList.innerHTML = '';
            
            let filteredNotifications = notifications;
            if (filter !== 'all') {
                filteredNotifications = notifications.filter(n => n.type === filter.toLowerCase());
            }
            
            filteredNotifications.forEach(notification => {
                notificationList.appendChild(createNotificationElement(notification));
            });
            
            updateBadgeCount();
        }

        function updateBadgeCount() {
            const unreadCount = notifications.filter(n => !n.isRead).length;
            const badge = document.getElementById('notificationBadge');
            badge.textContent = unreadCount;
            badge.style.display = unreadCount > 0 ? 'block' : 'none';
        }

        // Event Listeners
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', (e) => {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                e.target.classList.add('active');
                updateNotifications(e.target.dataset.tab);
            });
        });

        document.querySelector('.mark-all-read').addEventListener('click', () => {
            notifications.forEach(n => n.isRead = true);
            updateNotifications();
        });

        // Initial load
        updateNotifications();

        const notificationTrigger = document.getElementById('notificationTrigger');
        const notificationPanel = document.getElementById('notificationPanel');

        // Toggle panel display
        notificationTrigger.addEventListener('click', () => {
            notificationPanel.classList.toggle('show');
        });

        // Close the panel when clicking outside
        document.addEventListener('click', (event) => {
            if (!notificationPanel.contains(event.target) && !notificationTrigger.contains(event.target)) {
                notificationPanel.classList.remove('show');
            }
        });

        const notificationList = document.getElementById('notificationList');

notificationList.addEventListener('wheel', (event) => {
  const isScrollingUp = event.deltaY < 0; // Negative value means scrolling up
  const isScrollingDown = event.deltaY > 0; // Positive value means scrolling down

  const atTop = notificationList.scrollTop === 0;
  const atBottom =
    notificationList.scrollHeight - notificationList.scrollTop === notificationList.clientHeight;

  // Prevent page scroll when at top or bottom of the notification list
  if ((isScrollingUp && atTop) || (isScrollingDown && atBottom)) {
    event.preventDefault();
  }
});

  </script>
</body>
</html>