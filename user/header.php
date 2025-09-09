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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="../css/messages.css">

    <style>
    
    
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

</style>
        
</head>
<div class="message-wrapper">
    <div id="message-container"></div>
</div>
<body>

<div class="header">   
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
        <button class="ds-icon-button cart-button" title="Shopping Cart"  onclick="window.location.href='../cart.php';">
                <i class="fas fa-shopping-cart"></i>
                <span class="ds-cart-badge"><?php              
                $user_id = $_SESSION['user_session']['id'] ?? null;
                $cart_count = 0;
                
                if ($user_id) {
                    // Counting the number of items in the cart for the logged-in user
                    $cart_query = mysqli_query($conn, "SELECT * FROM tbl_cart WHERE user_id = '$user_id'");
                    $cart_count = mysqli_num_rows($cart_query);  // Get number of items
                }               
                echo $cart_count; ?></span>
               
            </button>

            <button class="ds-icon-button cart-button" title="Shopping Cart"  onclick="window.location.href='../notification.php';">
                <i class="fas fa-bell"></i>
                <span class="ds-cart-badge">5</span>
               
            </button>
  <!-- <a href="../notification.php" style="text-decoration: none; color: inherit;">
    <div class="notification-trigger ds-icon-button" id="notificationTrigger">
        <i class="fas fa-bell"></i>
        <span class="ds-cart-badge" id="cart-count">5</span>
    </div>
</a> -->
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

  <script>
  

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
  <script src="../js/index.js"></script>
  <script src="../js/messages.js"></script>
  <script>
function showMessage(text, type = 'success') {
    const container = document.getElementById('message-container');
    if (!container) return;

    const message = document.createElement('div');
    message.className = `message-box ${type}`;
    message.innerHTML = text;

    container.prepend(message);

    setTimeout(() => {
        message.remove();
    }, 4500);
}

document.addEventListener('click', (e) => {
    if (e.target.closest('.message-box')) {
        e.target.closest('.message-box').remove();
    }
});
</script>

  </body>
</html>