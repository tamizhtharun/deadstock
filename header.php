<?php
// header.php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dead Stock Processing</title>
    <link rel="icon" href="./icons/dead stock.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="./css/index.css">
</head>
<body>
<div class="header">   
    <nav id="nav-bar-head" class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
        <div class="container-fluid">
          <a class="navbar-brand" href="index.php">
            <img src="./icons/dead stock.png" alt="Logo" width="30" height="30">
            Dead Stock
          </a>
          <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse page-head" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <li class="nav-item search-form" id="navbarSupportedContent">
                <form class="d-flex" role="search">
                  <div class="search-container">
                    <ion-icon class="search-outline" name="search-outline" type="submit" size="small"
                      style="padding: 5px;"></ion-icon>
                    <input id="form-control-me-2" class="form-control me-2" type="search" placeholder="Search"
                      aria-label="Search">
                  </div>
                </form>
              </li>
              <li class="nav-item btns">
                <button id="seller-btn" type="button" class="seller-btn btn btn-outline-secondary" onclick="window.location.href='seller_registration.php';">
                  Sell here!
                </button>
                <button type="button" id="login-btn" class="login-btn btn btn-outline-secondary"
                  data-bs-toggle="modal" data-bs-target="#staticBackdrop">
                  Login
                </button>
              </li>
            </ul>
          </div>
        </div>
      </nav>

  <!-- runningtxt -->
  <div class="runningtxt">
      <marquee id="marquee" onmouseover="this.stop();" onmouseout="this.start();">
      </marquee>
  </div>
</div>

<!-- login modal -->

<div id="error-message" style="color: red; display: none;"></div>
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
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script> 
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  <script src="js/index.js"></script>

