<?php
ob_start();
session_start();
require_once 'messages.php';
include 'db_connection.php';
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
    $logo = $row['logo'];
    $favicon = $row['favicon'];
    $quote_text = $row['quote_text'];
    $quote_span_text = $row['quote_span_text'];

}
?>
<?php
// Check if the 'showLoginModal' query parameter exists
if (isset($_GET['showLoginModal']) && $_GET['showLoginModal'] == 'true') {
    echo "<script>window.addEventListener('DOMContentLoaded', function() { $('#staticBackdrop').modal('show'); });</script>";
}
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
unset($_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"> -->
    <!-- <meta name="theme-color" content="#your-brand-color"> -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dead Stock Processing</title>
    <link rel="icon" href="assets\uploads\<?php echo $favicon; ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">     
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="./css/index.css">
    <link rel="stylesheet" href="./css/responsive.css">
    <link rel="stylesheet" href="./css/header.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->

        <!-- Link Disply the featured categories in home page slider  -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="css/vendor.css">
        <link rel="stylesheet" type="text/css" href="css/style.css">
        <link rel="stylesheet" type="text/css" href="css/messages.css">
        <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <defs>
            <symbol xmlns="http://www.w3.org/2000/svg" id="cart" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="M8.5 19a1.5 1.5 0 1 0 1.5 1.5A1.5 1.5 0 0 0 8.5 19ZM19 16H7a1 1 0 0 1 0-2h8.491a3.013 3.013 0 0 0 2.885-2.176l1.585-5.55A1 1 0 0 0 19 5H6.74a3.007 3.007 0 0 0-2.82-2H3a1 1 0 0 0 0 2h.921a1.005 1.005 0 0 1 .962.725l.155.545v.005l1.641 5.742A3 3 0 0 0 7 18h12a1 1 0 0 0 0-2Zm-1.326-9l-1.22 4.274a1.005 1.005 0 0 1-.963.726H8.754l-.255-.892L7.326 7ZM16.5 19a1.5 1.5 0 1 0 1.5 1.5a1.5 1.5 0 0 0-1.5-1.5Z" />
            </symbol>
            <symbol xmlns="http://www.w3.org/2000/svg" id="star-full" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="m3.1 11.3l3.6 3.3l-1 4.6c-.1.6.1 1.2.6 1.5c.2.2.5.3.8.3c.2 0 .4 0 .6-.1c0 0 .1 0 .1-.1l4.1-2.3l4.1 2.3s.1 0 .1.1c.5.2 1.1.2 1.5-.1c.5-.3.7-.9.6-1.5l-1-4.6c.4-.3 1-.9 1.6-1.5l1.9-1.7l.1-.1c.4-.4.5-1 .3-1.5s-.6-.9-1.2-1h-.1l-4.7-.5l-1.9-4.3s0-.1-.1-.1c-.1-.7-.6-1-1.1-1c-.5 0-1 .3-1.3.8c0 0 0 .1-.1.1L8.7 8.2L4 8.7h-.1c-.5.1-1 .5-1.2 1c-.1.6 0 1.2.4 1.6" />
            </symbol>
            <symbol xmlns="http://www.w3.org/2000/svg" id="star-half" viewBox="0 0 24 24">
                <path fill="currentColor"
                    d="m3.1 11.3l3.6 3.3l-1 4.6c-.1.6.1 1.2.6 1.5c.2.2.5.3.8.3c.2 0 .4 0 .6-.1c0 0 .1 0 .1-.1l4.1-2.3l4.1 2.3s.1 0 .1.1c.5.2 1.1.2 1.5-.1c.5-.3.7-.9.6-1.5l-1-4.6c.4-.3 1-.9 1.6-1.5l1.9-1.7l.1-.1c.4-.4.5-1 .3-1.5s-.6-.9-1.2-1h-.1l-4.7-.5l-1.9-4.3s0-.1-.1-.1c-.1-.7-.6-1-1.1-1c-.5 0-1 .3-1.3.8c0 0 0 .1-.1.1L8.7 8.2L4 8.7h-.1c-.5.1-1 .5-1.2 1c-.1.6 0 1.2.4 1.6m8.9 5V5.8l1.7 3.8c.1.3.5.5.8.6l4.2.5l-3.1 2.8c-.3.2-.4.6-.3 1c0 .2.5 2.2.8 4.1l-3.6-2.1c-.2-.2-.3-.2-.5-.2" />
            </symbol>
        </defs>
        </svg>
        <!-- Message Container (Fixed Position) -->
        <div class="message-wrapper ">
            <div id="message-container"></div>
        </div>
        <style>
        #message-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
        .alert {
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        
    </style>
</head>

<body>
    <div class="header">


        <?php
        $current_page = basename($_SERVER['PHP_SELF']);
        ?>

        <nav class="ds-nav-container">
            <div class="ds-logo-section">
                <a href="index.php" class="ds-logo">
                    <img src="./assets/uploads/<?php echo $logo ?>" alt="Logo" width="30" height="30">
                    <span>Dead Stock</span>
                </a>
            </div>

            <div class="ds-search-section">
                <div class="ds-search-wrapper">
                    <form action="search-result.php" method="GET" id="search-form">
                        <i class="fas fa-search"></i>
                        <input type="text" id="search-bar" name="search_text" placeholder="Search products..."
                            class="ds-search-input" autocomplete="off" required aria-label="Search products">
                        <button type="submit" style="display: none;">Search</button>
                    </form>
                    <ul id="suggestions-list" class="suggestions-dropdown"></ul>
                </div>
            </div>


            <div class="ds-actions-section">
                <?php if (isset($_SESSION['user_session'])): ?>
                    <button class="ds-btn-secondary" onclick="window.location.href='seller_registration.php';">Sell
                        here</button>

                    <div class="ds-user-controls">
                        <!-- Cart Icon -->
                        <button class="ds-icon-button cart-button" title="Shopping Cart"
                            onclick="window.location.href='cart.php';">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="ds-cart-badge" id="cart-count">
                                <?php
                                $user_id = $_SESSION['user_session']['id'] ?? null;
                                $cart_count = 0;

                                if ($user_id) {
                                    $cart_query = mysqli_query($conn, "SELECT * FROM tbl_cart WHERE user_id = '$user_id'");
                                    $cart_count = mysqli_num_rows($cart_query);
                                }
                                echo $cart_count;
                                ?>
                            </span>
                        </button>


                        <!-- Notification Icon (Not shown in notification.php) -->
                        <?php if ($current_page !== 'notification.php'): ?>

                            <button class=" notification-trigger ds-icon-button" title="Notifications"
                                onclick="window.location.href='notification.php';">
                                <i class="fas fa-bell"></i>
                                <span class="ds-cart-badge" id="cart-count">5</span>
                            </button>

                            </a>

                        <?php endif; ?>

                        <div class="ds-profile-menu">
                            <button class="ds-profile-trigger">
                                <div class="ds-avatar">
                                <?php
                                $userInfo = $_SESSION['user_session'];
                            $defaultAvatar = "https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y"; // Default avatar URL
                            $profileImage = isset($userInfo['profile_image']) && !empty($userInfo['profile_image']) 
                            ? 'user/uploads/profile-photos/' . htmlspecialchars($userInfo['profile_image'])
                            : $defaultAvatar;
                                ?>
                                    <img src="<?= $profileImage ?>" alt="Profile">
                                </div>
                            </button>
                            <div class="ds-menu-dropdown">
                                <div class="ds-menu-header">
                                    <img src="<?= $profileImage ?>" alt="Profile" class="ds-menu-avatar">
                                    <div class="ds-user-info">
                                        <span
                                            class="ds-user-name"><?php echo $_SESSION['user_session']['username'] ?></span>
                                        <span class="ds-user-email"><?php echo $_SESSION['user_session']['email'] ?></span>
                                    </div>
                                </div>
                                <div class="ds-menu-items">
                                    <a href="user/profile.php" class="ds-menu-item"
                                        style="text-decoration: none !important;">
                                        <i class="fas fa-user"></i>
                                        <span>Account</span>
                                    </a>
                                    <a href="user/profile.php?tab=orders" class="ds-menu-item"
                                        style="text-decoration: none !important;">
                                        <i class="fas fa-shopping-bag"></i>
                                        <span>Orders</span>
                                    </a>
                                    <a href="user/profile.php?tab=bidding" class="ds-menu-item"
                                        style="text-decoration: none !important;">
                                        <i class="fas fa-gavel"></i>
                                        <span>Bidding</span>
                                    </a>
                                    <div class="ds-menu-divider"></div>
                                    <a href="logout.php" class="ds-menu-item ds-logout"
                                        style="text-decoration: none !important;">
                                        <i class="fas fa-sign-out-alt"></i>
                                        <span>Logout</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="ds-auth-buttons">
                        <button class="ds-btn-secondary" onclick="window.location.href='seller_registration.php';">Sell
                            here</button>
                        <button class="ds-btn-primary" type="button" id="login-btn" data-bs-toggle="modal"
                            data-bs-target="#staticBackdrop">Login</button>
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
</div>
    
  <script src="js/messages.js"></script>
  <?php MessageSystem::display(); ?>

    <!-- Login Modal -->
    <div class="modal fade" id="staticBackdrop" data-bs-backdrop="true" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel">Dead Stock</h1>
                    <button type="button" id="btn-close" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div id="modal-body" class="modal-body">
                    <!-- Message container for displaying errors and success messages -->
                    <div id="modal-message-container"></div>
                    <!-- Error Message HTML -->
<?php if (!empty($error_message) || !empty($success_message)): ?>
    <div class="premium-alert" id="premium-alert">
        <div class="alert-content">
            <div class="alert-icon">
                <?php if (!empty($success_message)): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="#28a745" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                <?php else: ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="12"></line>
                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                    </svg>
                <?php endif; ?>
            </div>
            <span class="alert-message"><?php echo htmlspecialchars(!empty($success_message) ? $success_message : $error_message); ?></span>
            <button class="alert-close" onclick="closeAlert()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
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
                            <input type="email" id="mail" class="input-field" placeholder="Email" name="email"
                                autocomplete="off" required>
                        </div>
                        <div class="input-box">
                            <input type="password" class="input-field" placeholder="Password" name="password"
                                autocomplete="off" required>
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
                            <input type="text" class="input-field" placeholder="Username" name="username"
                                autocomplete="off" required>
                        </div>
                        <div class="input-box">
                            <input type="tel" id="phone-number" class="input-field" placeholder="Phone"
                                name="phone_number" autocomplete="off" required pattern="[0-9]{10}">
                        </div>
                        <div class="input-box">
                            <input type="email" id="email" class="input-field" placeholder="Email" name="email"
                                autocomplete="off" required>
                        </div>
                        <div class="input-box">
                            <input id="password" type="password" class="input-field" placeholder="Password"
                                name="password" autocomplete="off" required>
                        </div>
                        <div class="input-box">
                            <input type="text" class="input-field" placeholder="GST (Optional)" name="user_gst"
                                autocomplete="off">
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

                    <!-- Forgot Password Form -->
                    <form id="forgot-password-form" method="POST" action="forgot_password.php" style="display: none;">
                        <h1 class="modal-title fs-5" id="box-header">Forgot Password</h1>
                        <div class="input-box">
                            <input type="email" class="input-field" placeholder="Email" name="email" autocomplete="off" required>
                        </div>
                        <div class="input-submit">
                            <button type="submit" class="submit-btn" id="forgot-password-btn" name="forgot_password">
                                <label for="submit">Reset Password</label>
                            </button>
                        </div>
                        <div class="back-to-login-link">
                            <p><a href="#" id="back-to-login-link">Back to Login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <script>
        $(document).ready(function () {
            $("#search-bar").on("keyup", function () {
                let query = $(this).val();
                if (query.length > 0) {
                    $.ajax({
                        url: "search_suggestions.php",
                        method: "GET",
                        data: { search_text: query },
                        success: function (data) {
                            $("#suggestions-list").html(data).fadeIn();
                        }
                    });
                } else {
                    $("#suggestions-list").fadeOut();
                }
            });

            $(document).on("click", ".suggestion-item", function () {
                $("#search-bar").val($(this).text());
                $("#suggestions-list").fadeOut();
            });

            $(document).on("click", function (e) {
                if (!$(e.target).closest(".ds-search-wrapper").length) {
                    $("#suggestions-list").fadeOut();
                }
            });
        });

        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && window.performance.navigation.type === 2)) {
                document.getElementById('search-bar').value = '';
                document.getElementById('suggestions-list').style.display = 'none'; // Hide suggestions
            }
        });
        function updateBadgeCount() {
            const unreadCount = notifications.filter(n => !n.isRead).length;
            const badge = document.getElementById('notificationBadge');
            badge.textContent = unreadCount;
            badge.style.display = unreadCount > 0 ? 'block' : 'none';
        }

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

            const signinForm = document.getElementById('signin-form');
            const signupForm = document.getElementById('signup-form');
            const forgotPasswordForm = document.getElementById('forgot-password-form');
            const forgotPasswordLink = document.getElementById('forgot-password-link');
            const backToLoginLink = document.getElementById('back-to-login-link');

            forgotPasswordLink.addEventListener('click', (e) => {
                e.preventDefault();
                signinForm.style.display = 'none';
                forgotPasswordForm.style.display = 'block';
            });

            backToLoginLink.addEventListener('click', (e) => {
                e.preventDefault();
                forgotPasswordForm.style.display = 'none';
                signinForm.style.display = 'block';
            });

            document.getElementById('signup-link').addEventListener('click', (e) => {
                e.preventDefault();
                signinForm.style.display = 'none';
                forgotPasswordForm.style.display = 'none';
                signupForm.style.display = 'block';
            });

            document.getElementById('signin-link').addEventListener('click', (e) => {
                e.preventDefault();
                signupForm.style.display = 'none';
                forgotPasswordForm.style.display = 'none';
                signinForm.style.display = 'block';
            });
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

        document.getElementById('search-bar').addEventListener('focus', function () {
            fetchSuggestions('');  // Empty string to show top 5 most viewed products immediately
        });

        // Function to fetch suggestions from the server
        function fetchSuggestions(searchText) {
            const suggestionsList = document.getElementById('suggestions-list');
            suggestionsList.style.display = 'block';  // Ensure the suggestions dropdown is visible
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'search_automatic.php?search_text=' + searchText, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    suggestionsList.innerHTML = xhr.responseText;  // Populate the suggestion list with response
                }
            };
            xhr.send();
        }
    </script>
    <script src="forgot-password.js"></script>
    <!-- <script>
        document.getElementById('forgot-password-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[name="email"]').value;
            fetch('forgot_password.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                const messageContainer = document.getElementById('modal-message-container');
                messageContainer.innerHTML = `<div class="alert alert-${data.success ? 'success' : 'danger'}">${data.message}</div>`;
                messageContainer.style.display = 'block';
                if (data.success) {
                    document.getElementById('forgot-password-form').reset();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const messageContainer = document.getElementById('modal-message-container');
                messageContainer.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
                messageContainer.style.display = 'block';
            });
        });

        function showMessage(message, type) {
            const messageContainer = document.getElementById('message-container');
            messageContainer.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            messageContainer.style.display = 'block';
            setTimeout(() => {
                messageContainer.style.display = 'none';
            }, 5000);
        }
    </script>

    <style>
        #modal-message-container {
            margin-bottom: 15px;
        }
        #modal-message-container .alert {
            margin-bottom: 0;
        }
    </style> -->

 
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script> 
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  <script src="js/index.js"></script>
  <script src="js/validation.js"></script>
  
</body>

</html>