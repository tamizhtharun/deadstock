<?php
include 'db_connection.php';
$statement = $pdo->prepare("SELECT * FROM tbl_settings WHERE id=1");
$statement->execute();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row)
{
  $logo = $row['logo'];
  $favicon = $row['favicon'];
  $quote_text = $row['quote_text'];
  $quote_span_text = $row['quote_span_text'];

}
?>
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dead Stock Processing</title>
    <link rel="icon" href="assets\uploads\<?php echo $favicon?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/index.css">

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
    <nav id="nav-bar-head" class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
        <div class="container-fluid">
          <a class="navbar-brand" href="index.php">
            <img src="./assets/uploads/<?php echo $logo?>" alt="Logo" width="30" height="30">
            Dead Stock
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse page-head" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <li class="nav-item search-form" id="navbarSupportedContent">
              <form class="d-flex" role="search" action="search-result.php" method="get">
								<div class="search-container">
								<form action="search-result.php" method="GET" class="d-flex">
										<ion-icon class="search-outline" name="search-outline" type="submit" size="small" style="padding: 5px;"></ion-icon>
										<input id="form-control-me-2" class="form-control me-2" type="search" name="search_text" placeholder="Search" aria-label="Search" required>
										<button type="submit" style="display: none;">Search</button>
								</form>
              </li>
            
              <li class="nav-item btns">
              
              <button id="seller-btn" type="button" class="seller-btn btn btn-outline-secondary" onclick="window.location.href='seller_registration.php';">
                  Sell here!
                </button>

               
                <?php if(!isset($_SESSION['user_session'])):?>

                <button type="button" id="login-btn" class="login-btn btn btn-outline-secondary"
                  data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                  Login
                </button>

                <?php else: ?>
                    <button type="button" id="login-btn" class="login-btn btn btn-outline-secondary" onclick="window.location.href='cart.php';">
                    Cart
                  </button>
                  <div class="dropdown">
                    <button class="dropbtn btn btn-outline-secondary"><?php echo $_SESSION['user_session']['name']?> ▾</button>
                    <div class="dropdown-content">
                      <a href="" >Account</a>
                      <a href="" >Settings</a>
                      <a href="" >orders</a>
                      <a href="logout.php" >logout</a>
                    </div>
                  </div>
                  <?php endif; ?>
              </li>
            </ul>
          </div>
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


  <script src="js/index.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script> 
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

</body>
</html>