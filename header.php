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
            
            <button class="ds-icon-button notification-button" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="ds-notification-badge">5</span>
            </button>
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

  </script>
</body>
</html>