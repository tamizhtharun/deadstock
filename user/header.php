<?php
include '../db_connection.php';
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<meta name="theme-color" content="#your-brand-color">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dead Stock Processing</title>
    <link rel="icon" href="../assets\uploads\<?php echo $favicon?>">
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"> -->
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"> -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
    <!-- <link rel="stylesheet" href="./css/responsive.css"> -->
    <link rel="stylesheet" href="css/header.css">

        
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
                <a href="../index.php" class="ds-logo">
                <img src="../assets/uploads/<?php echo $logo?>" alt="Logo" width="30" height="30">
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
      <button class="ds-btn-secondary" onclick="window.location.href='../seller_registration.php';" >Sell here</button>
      
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
                        <?php
                        $currentPage = basename($_SERVER['PHP_SELF']); // Get the current page name
                        ?>
                        <a href="/deadstockcurrent/user/profile.php" 
                          <?php if ($currentPage === 'profile.php') echo '"class="ds-menu-item " style="text-decoration: none !important""'; ?> 
                          <?php if ($currentPage === 'profile.php') echo ' class="ds-menu-item " style="text-decoration: none !important" style="pointer-events: none; color: grey;"'; ?>>
                            <i class="fas fa-user"></i>
                            <span>Account</span>
                        </a>

                        <a href="profile.php?tab=orders" class="ds-menu-item"style="text-decoration: none !important">
                            <i class="fas fa-shopping-bag"></i>
                            <span>Orders</span>
                        </a>
                        <a href="/bidding" class="ds-menu-item"style="text-decoration: none !important">
                            <i class="fas fa-gavel"></i>
                            <span>Bidding</span>
                        </a>
                        <div class="ds-menu-divider"></div>
                        <a href="../logout.php" class="ds-menu-item ds-logout"style="text-decoration: none !important">
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

<!-- login modal -->

<!-- <div id="error-message" style="color: red; display: none;"></div> -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">          
          <h1 class="modal-title fs-5" id="staticBackdropLabel">Dead Stock</h1>
          <button type="button" id="btn-close" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div id="modal-body" class="modal-body">
          <!-- Sign In Form -->
          <form id="signin-form" method="POST" action="login.php">
            <h1 class="modal-title fs-5" id="box-header">Login</h1>
            <div class="input-box">
              <input type="email" id="mail" class="input-field" placeholder="Email" name="email" autocomplete="off"
                required>
              <!-- <p id="email-error"></p> -->
            </div>
            <div class="input-box">
              <input type="password" class="input-field" placeholder="Password" name="password" autocomplete="off"
                required>

            </div>
            <div class="forgot" id="forgot-password-link">
              <!-- <section>
                <input type="checkbox" id="check">
                <label for="check">Remember me</label>
              </section> -->
              <section>
                <a href="#">Forgot password ?</a>
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
              <input type="tel" id="phone-number" class="input-field" placeholder="Phone" name="phone_number"
                autocomplete="off" required pattern="[0-9]{10}">
              <!-- <p id="error-message"></p> -->
            </div>
            <div class="input-box">
              <input type="email" id="email" class="input-field" placeholder="Email" name="email" autocomplete="off"
                required>
              <!-- <p id="email-error-message"></p> -->
            </div>
            <div class="input-box">
              <input id="password" type="password" class="input-field" placeholder="Password" name="password" autocomplete="off"
                required >
                <!-- <p id="password-error"></p> -->
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


  <script src="../js/index.js"></script>
  

</body>
</html>