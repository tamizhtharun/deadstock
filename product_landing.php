<?php include 'header.php';
include 'db_connection.php';
require_once 'messages.php';
require_once('vendor/autoload.php');
require_once('config.php');
//product_landing.php
?>


<?php
if (isset($_SESSION['user_session']['id'])) {
  $user_id = $_SESSION['user_session']['id'];
}
if (!isset($_REQUEST['id'])) {
  header('location: index.php');
  exit;
} else {
  // Check the id is valid or not
  $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE id=?");
  $statement->execute(array($_REQUEST['id']));
  $total = $statement->rowCount();
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
  if ($total == 0) {
    header('location: index.php');
    exit;
  }
}

foreach ($result as $row) {
  $p_name = $row['p_name'];
  $p_old_price = $row['p_old_price'];
  $p_current_price = $row['p_current_price'];
  $p_qty = $row['p_qty'];
  $p_featured_photo = $row['p_featured_photo'];
  $p_description = $row['p_description'];
  $p_feature = $row['p_feature'];
  $p_condition = $row['p_condition'];
  $p_return_policy = $row['p_return_policy'];
  $p_total_view = $row['p_total_view'];
  $p_is_featured = $row['p_is_featured'];
  // $p_is_active = $row['p_is_active'];
  $ecat_id = $row['ecat_id'];
  $gst_percentage = $row['gst_percentage'];
}

// Getting all categories name for breadcrumb
$statement = $pdo->prepare("SELECT
                        t1.ecat_id,
                        t1.ecat_name,
                        t1.mcat_id,

                        t2.mcat_id,
                        t2.mcat_name,
                        t2.tcat_id,

                        t3.tcat_id,
                        t3.tcat_name

                        FROM tbl_end_category t1
                        JOIN tbl_mid_category t2
                        ON t1.mcat_id = t2.mcat_id
                        JOIN tbl_top_category t3
                        ON t2.tcat_id = t3.tcat_id
                        WHERE t1.ecat_id=?");
$statement->execute(array($ecat_id));
$total = $statement->rowCount();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $row) {
  $ecat_name = $row['ecat_name'];
  $mcat_id = $row['mcat_id'];
  $mcat_name = $row['mcat_name'];
  $tcat_id = $row['tcat_id'];
  $tcat_name = $row['tcat_name'];
}


$p_total_view = $p_total_view + 1;

$statement = $pdo->prepare("UPDATE tbl_product SET p_total_view=? WHERE id=?");
$statement->execute(array($p_total_view, $_REQUEST['id']));
?>
<?php
$error_message1 = '';
$success_message1 = '';
$product_id = isset($_GET['id']) ? $_GET['id'] : '';


if (isset($_POST['add_to_cart'])) {

  if (isset($_SESSION['user_session']['id'])) {
    $user_id = $_SESSION['user_session']['id'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $product_quantity = isset($_POST['product_quantity']) ? intval($_POST['product_quantity']) : 1; // Default to 1

    // Check if product already exists in the cart
    $stmt = $conn->prepare("SELECT * FROM tbl_cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      MessageSystem::set('Product already added to cart!', 'error');
    } else {
      $stmt = $conn->prepare("INSERT INTO tbl_cart (id, user_id, quantity) VALUES (?, ?, ?)");
      $stmt->bind_param("iii", $product_id, $user_id, $product_quantity);
      if ($stmt->execute()) {
        // MessageSystem::set('Product added to cart!', 'success');
      } else {
        // MessageSystem::set('Failed to add product to cart.', 'error');
      }
    }
  } else {
    // User is not logged in, show login modal
    echo "<script>
          document.addEventListener('DOMContentLoaded', function() {
              var loginModal = new bootstrap.Modal(document.getElementById('staticBackdrop'), {
                  backdrop: 'static'
              });
              loginModal.show();
              showMessage('Please login to add items to your cart', 'error');
          });
      </script>";
  }
}

if (!isset($_SESSION['recently_viewed'])) {
  $_SESSION['recently_viewed'] = [];
}
$_SESSION['recently_viewed'] = array_diff($_SESSION['recently_viewed'], [$product_id]);
array_unshift($_SESSION['recently_viewed'], $product_id);
$_SESSION['recently_viewed'] = array_slice($_SESSION['recently_viewed'], 0, 5);

?>

<?php
$select_product = mysqli_query($conn, "SELECT * FROM tbl_product") or die('query failed');
if (mysqli_num_rows($select_product) > 0) {
  while ($fetch_product = mysqli_fetch_assoc($select_product)) {
  }
}
?>

<?php
if ($error_message1 != '') {
  MessageSystem::set($error_message1, 'error');
}
if ($success_message1 != '') {
  MessageSystem::set($success_message1, 'success');
  header('location: product.php?id=' . $_REQUEST['id']);
  exit;
}

// Get product details first
$statement = $pdo->prepare("SELECT * FROM tbl_product WHERE id=?");
$statement->execute(array($_REQUEST['id']));
$total = $statement->rowCount();
$result = $statement->fetchAll(PDO::FETCH_ASSOC);

if ($total == 0) {
  header('location: index.php');
  exit;
}

// Initialize category IDs and names
$tcat_id = $mcat_id = $ecat_id = 0;
$tcat_name = $mcat_name = $ecat_name = 'Uncategorized';

foreach ($result as $row) {
  $tcat_id = $row['tcat_id'];
  $mcat_id = $row['mcat_id'];
  $ecat_id = $row['ecat_id'];
}

// Get top category name
if ($tcat_id) {
  $statement = $pdo->prepare("SELECT tcat_name FROM tbl_top_category WHERE tcat_id=?");
  $statement->execute(array($tcat_id));
  $result = $statement->fetch(PDO::FETCH_ASSOC);
  if ($result) {
    $tcat_name = $result['tcat_name'];
  }
}

// Get mid category name
if ($mcat_id) {
  $statement = $pdo->prepare("SELECT mcat_name FROM tbl_mid_category WHERE mcat_id=?");
  $statement->execute(array($mcat_id));
  $result = $statement->fetch(PDO::FETCH_ASSOC);
  if ($result) {
    $mcat_name = $result['mcat_name'];
  }
}

// Get end category name
if ($ecat_id) {
  $statement = $pdo->prepare("SELECT ecat_name FROM tbl_end_category WHERE ecat_id=?");
  $statement->execute(array($ecat_id));
  $result = $statement->fetch(PDO::FETCH_ASSOC);
  if ($result) {
    $ecat_name = $result['ecat_name'];
  }
}

$stmt = $pdo->prepare("SELECT min_bid_pct FROM bid_settings ORDER BY created_at DESC LIMIT 1");
$stmt->execute();
$bid_settings = $stmt->fetch(PDO::FETCH_ASSOC);
$min_bid_pct = $bid_settings ? $bid_settings['min_bid_pct'] : 0;

// Calculate minimum allowed bid price
$min_allowed_price = $p_current_price * (1 - ($min_bid_pct / 100));
//gst and discount calculation
$gst_amount = ($p_current_price * $gst_percentage) / 100;
$final_price = $p_current_price + $gst_amount;
$discount = ($p_old_price > 0) ? round((($p_old_price - $p_current_price) / $p_old_price) * 100) : 0;

?>
<!DOCTYPE html>
<html lang="en">


<link rel="stylesheet" href="css/product_landing.css">


<!-- content -->
<section class="py-5">
  <div class="container" style="margin-top: -30px;">
    <!-- breadcrumb -->
    <nav aria-label="breadcrumb" style="margin-left:6px;">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php" style="text-decoration: none;">Home</a></li>
        <?php if ($tcat_id && $tcat_name !== 'Uncategorized'): ?>
          <li class="breadcrumb-item"><a href="search-result.php?type=top-category&id=<?php echo $tcat_id; ?>"
              style="text-decoration: none;"><?php echo htmlspecialchars($tcat_name); ?></a></li>
        <?php endif; ?>

        <?php if ($mcat_id && $mcat_name !== 'Uncategorized'): ?>
          <li class="breadcrumb-item"><a href="search-result.php?type=mid-category&id=<?php echo $mcat_id; ?>"
              style="text-decoration: none;"><?php echo htmlspecialchars($mcat_name); ?></a></li>
        <?php endif; ?>

        <?php if ($ecat_id && $ecat_name !== 'Uncategorized'): ?>
          <li class="breadcrumb-item active" aria-current="page" style="text-decoration: none;">
            <?php echo htmlspecialchars($ecat_name); ?>
          </li>
        <?php endif; ?>
      </ol>
    </nav>
    <div class="row gx-5 justify-content-center">
      <aside class="col-lg-6 justify-content-center">
        <div class="d-flex justify-content-center mb-3">
          <!-- Main Image -->
          <div class="border rounded-4 mb-3 d-flex justify-content-center"
            style="width: 450px; height: 300px; overflow: hidden; position: relative;">
            <a data-bs-toggle="modal" id="mainImageLink" class="rounded-4" data-bs-target="#imageModal" href="#">
              <!-- Default Big Photo -->
              <img id="mainImage" class="rounded-4 zoom-effect" class="rounded-4"
                src="assets/uploads/product-photos/<?php echo $p_featured_photo; ?>">
            </a>
          </div>
        </div>
        <!-- Thumbnail Images -->
        <div class="d-flex justify-content-center mb-3">
          <div class="d-flex flex-wrap">
            <?php
            echo '
            <a class="border mx-1 rounded-2 thumbnail-link" href="javascript:void(0);">
                <img width="60" height="60" class="rounded-2" 
                     src="assets/uploads/product-photos/' . $p_featured_photo . '" 
                     data-full-image="assets/uploads/product-photos/' . $p_featured_photo . '">
            </a>';
            $stmt = $pdo->prepare("SELECT photo FROM tbl_product_photo WHERE p_id = :product_id");
            $stmt->execute([':product_id' => $product_id]);
            $product_photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($product_photos)) {
              foreach ($product_photos as $photo) {
                $photo_url = htmlspecialchars($photo['photo'], ENT_QUOTES, 'UTF-8'); // Secure the URL
                echo '
                    <a class="border mx-1 rounded-2 thumbnail-link" href="javascript:void(0);">
                        <img width="60" height="60" class="rounded-2" 
                             src="assets/uploads/product-photos/' . $photo_url . '" 
                             data-full-image="assets/uploads/product-photos/' . $photo_url . '">
                    </a>';
              }
            }
            ?>
          </div>
        </div>
      </aside>

      <main class="col-lg-6">
        <dclass="ps-lg-3">
          <h4 class="title text-dark">
            <?php echo $p_name; ?>
          </h4>

          <div class="d-flex flex-row my-2">
            <span class="text-muted"><i class="bi bi-basket mx-1"></i>
              <?php if ($p_qty > 10): ?>
                <span class="text-success ms-2">In stock</span>
              <?php elseif ($p_qty >= 1 && $p_qty <= 10): ?>
                <span class="text-warning ms-2">Only Few left</span>
              <?php
              elseif ($p_qty == 0):
                // Ensure $product_id is set
                if (!isset($product_id) || empty($product_id)) {
                  echo "<script>console.log('Error: product_id is not set');</script>";
                } else {
                  // Prepare SQL Update Query
                  $updateQuery = "UPDATE tbl_product SET  p_is_featured = 0,p_is_approve= 0 WHERE id = ?";
                  $stmt = $conn->prepare($updateQuery);

                  if ($stmt) {
                    $stmt->bind_param("i", $product_id);
                    if ($stmt->execute()) {
                      echo "<script>console.log('Product status updated for ID: $product_id');</script>";
                    } else {
                      echo "<script>console.log('Error executing query: " . $stmt->error . "');</script>";
                    }
                    $stmt->close();
                  } else {
                    echo "<script>console.log('Error preparing statement: " . $conn->error . "');</script>";
                  }
                }
              ?>
              <?php endif; ?>
            </span>
          </div>

          <div class="mb-3">
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="h7" style="color:rgb(137, 134, 134);">₹<?php echo number_format($final_price, 0); ?> (Incl. of all taxes)</span>
            </div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="h3 mb-0" style="color: #000; font-weight: bold;">₹<?php echo number_format($p_current_price, 0); ?></span>
              <span class="h6 mb-0">+ ₹<?php echo number_format($gst_amount, 0); ?> GST</span>
            </div>
            <div class="d-flex align-items-center gap-2">
              <span class="h7 mb-0" style="color:rgb(77, 74, 74);">MRP</span><span class="h6 mb-0" style="color:rgb(77, 74, 74); text-decoration: line-through;">₹<?php echo number_format($p_old_price, 0); ?></span>
              <?php if ($discount > 0) { ?>
                <span class="discount-text"><?php echo $discount; ?>% OFF</span>
              <?php } ?>
            </div>
          </div>

          <div class="product-grid">
            <!-- Headers -->
            <div class="letter-icon p">P</div>
            <div class="letter-icon m">M</div>
            <div class="letter-icon k">K</div>
            <div class="letter-icon n">N</div>
            <div class="letter-icon s">S</div>
            <div class="letter-icon h">H</div>
            <div class="letter-icon o">O</div>

            <?php

            $product_id = isset($_GET['id']) ? $_GET['id'] : '';
            $query = "SELECT P, M, K, N, S, H, O FROM tbl_key WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$product_id]);

            // Fetch the result
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $dot_values = [
              'P' => isset($row['P']) ? $row['P'] : 0,
              'M' => isset($row['M']) ? $row['M'] : 0,
              'K' => isset($row['K']) ? $row['K'] : 0,
              'N' => isset($row['N']) ? $row['N'] : 0,
              'S' => isset($row['S']) ? $row['S'] : 0,
              'H' => isset($row['H']) ? $row['H'] : 0,
              'O' => isset($row['O']) ? $row['O'] : 0
            ];
            ?>

            <!-- Indicator dots -->

            <!-- For each alphabet, we check its value and assign the correct class -->
            <div
              class="letter-icon Rec p <?php echo ($dot_values['P'] == 0 ? 'no-dot' : 'icon-rank-' . $dot_values['P']); ?>">
            </div>
            <div
              class="letter-icon Rec m <?php echo ($dot_values['M'] == 0 ? 'no-dot' : 'icon-rank-' . $dot_values['M']); ?>">
            </div>
            <div
              class="letter-icon Rec k <?php echo ($dot_values['K'] == 0 ? 'no-dot' : 'icon-rank-' . $dot_values['K']); ?>">
            </div>
            <div
              class="letter-icon Rec n <?php echo ($dot_values['N'] == 0 ? 'no-dot' : 'icon-rank-' . $dot_values['N']); ?>">
            </div>
            <div
              class="letter-icon Rec s <?php echo ($dot_values['S'] == 0 ? 'no-dot' : 'icon-rank-' . $dot_values['S']); ?>">
            </div>
            <div
              class="letter-icon Rec h <?php echo ($dot_values['H'] == 0 ? 'no-dot' : 'icon-rank-' . $dot_values['H']); ?>">
            </div>
            <div
              class="letter-icon Rec o <?php echo ($dot_values['O'] == 0 ? 'no-dot' : 'icon-rank-' . $dot_values['O']); ?>">
            </div>
          </div>
          <!-- Key Explanation -->
          <div class="key-button-container">
            <button class="material-info-btn">
              Key (explanation of symbols)
              <span class="info-icon">ⓘ</span>
            </button>

            <!-- The dropdown container that will show/hide -->
            <div class="l-info-icons-container">
              <div class="info-row">
                <div class="letter-icon Rec icon-rank-2"> </div>
                <div class="description">Main application</div>
              </div>
              <div class="info-row">
                <div class="letter-icon Rec icon-rank-1"> </div>
                <div class="description">Additional application</div>
              </div>
            </div>


            <hr />
            <!-- <button class="btn btn-warning shadow-0"> Buy now </button> -->

            <div class="product-container">
              <div class="product-box">
                <form method="POST" action="">
                  <div class="row mb-4">
                    <div class="col-md-4 col-6 mb-3">
                      <label class="mb-2 d-block">Quantity</label>
                      <div class="input-group mb-3" style="width: 170px;">
                        <input type="number" class="form-control text-center" name="product_quantity" id="quantity-input"
                          value="1" min="1" />
                      </div>
                    </div>
                  </div>

                  <!-- Flexbox for buttons to align them horizontally -->
                  <div class="d-flex align-items-center">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">

                    <!-- Add to Cart Button -->
                    <button type="submit" name="add_to_cart" class="btn btn-primary shadow-0 me-2">
                      <i class="bi bi-basket me-1"></i> Add to cart
                    </button>

                    <!-- Place a Bid Button -->
                    <div class="d-inline-block"
                      <?php if (!isset($_SESSION['user_session']['id'])) { ?>
                      data-bs-toggle="tooltip" data-bs-placement="top" title="Please login through your account"
                      <?php } ?>>

                      <button id="requestPriceBtn" class="request-price-btn btn btn-danger border"
                        <?php if (!isset($_SESSION['user_session']['id'])) {
                          echo 'disabled';
                        } ?>>
                        <i class="fa fa-gavel"></i> Place a Bid
                      </button>
                    </div>
                  </div>
                </form>

                <script>
                  document.addEventListener("DOMContentLoaded", function() {
                    var tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                    tooltipTriggerList.forEach(function(tooltipTriggerEl) {
                      new bootstrap.Tooltip(tooltipTriggerEl);
                    });
                  });
                </script>

                <style>
                  .tooltip-inner {
                    font-size: 12px !important;
                    padding: 4px 8px !important;
                  }
                </style>
              </div>
            </div>
          </div>
    </div>
  </div>
</section>

<!-- Modal Overlay For Request Price -->
<div class="modal-overlay" id="modalOverlay" style="display: none;">
  <!-- Modal -->
  <div id="priceRequestModal">
    <button class="close-button-rp" id="closeModal">&times;</button>
    <div class="terms-modal-header-rp">
      <h3>Request a Price</h3>
    </div>
    <!-- Form -->
    <form id="priceRequestForm" method="POST" action="submit_bid.php">
      <input type="hidden" name="product_id"
        value="<?php echo htmlspecialchars($_REQUEST['id'], ENT_QUOTES, 'UTF-8'); ?>">

      <div class="form-group">
        <label for="quantity">Quantity</label>
        <input type="number" id="quantity" name="quantity" required min="1" placeholder="Enter quantity" />
      </div>

      <div class="form-group">
        <label for="proposedPrice">Your Bid Price (₹)</label>
        <input type="number" id="proposedPrice" name="proposed_price" required min="0" step="0.01"
          placeholder="Enter Price Per Unit" />
      </div>

      <label>
        <input type="checkbox" id="terms-checkbox" name="terms_accepted" required>
        <span>
          I agree to the <span class="terms-link" id="terms-btn"
            style="text-decoration:none; cursor: pointer; color:rgb(59, 98, 139);" onclick="openTermsPage(event)">Terms
            and Conditions</span>
        </span>
      </label>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" id="cancelBtn"
          style="--bs-btn-padding-y: .30rem; --bs-btn-padding-x: 1rem; --bs-btn-font-size: .85rem;">
          Cancel
        </button>
        <button type="button" class="btn btn-primary" onclick="validateCheckboxAndPay()"
          style="--bs-btn-padding-y: .30rem; --bs-btn-padding-x: 1rem; --bs-btn-font-size: .85rem;">
          Pay and Place your Bid
        </button>
      </div>
    </form>
  </div>
</div>

<script>
  function checkLogin() {
    <?php if (!isset($_SESSION['user_session']['id'])) { ?>
      var loginModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
      loginModal.show();
      showMessage('Please login', 'error');
      return false;
    <?php } ?>
    return true;
  }

  function showTermsModal(event) {
    event.preventDefault();
  }
</script>
<script>
  function checkExistingBid(productId) {
    return fetch('check_bid_status.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          product_id: productId
        })
      })
      .then(response => response.json());
  }
</script>

<!-- Razorpay Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
  // Terms and Conditions new page
  function openTermsPage(event) {
    event.preventDefault();
    event.stopPropagation();
    window.open('terms.php?source=user', '_blank');
  }

  function showNotification(message) {
    const notification = document.getElementById('errorNotification');
    const messageElement = document.getElementById('errorMessage');

    messageElement.textContent = message;
    notification.style.display = 'block';
    notification.classList.add('show');

    // Auto-dismiss after 3 seconds
    setTimeout(() => {
      closeNotification();
    }, 3000);
  }

  function closeNotification() {
    const notification = document.getElementById('errorNotification');
    notification.classList.remove('show');

    setTimeout(() => {
      notification.style.display = 'none';
    }, 400);
  }

  function validateCheckboxAndPay() {
    const termsCheckbox = document.getElementById('terms-checkbox');
    const quantityField = document.getElementById('quantity');
    const proposedPrice = document.getElementById('proposedPrice');

    const minAllowedPrice = <?php echo $min_allowed_price; ?>;

    if (quantityField.value <= 0) {
      showMessage('Please enter a valid quantity', 'error');
    } else if (proposedPrice.value <= 0) {
      showMessage('Please enter a valid bid price', 'error');
    } else if (parseFloat(proposedPrice.value) < minAllowedPrice) {
      showMessage('Bidding too low? Try offering a fair price.', 'error');
    } else if (!termsCheckbox.checked) {
      showMessage('You must agree to the Terms and Conditions', 'error');
    } else {
      openRazorpayModal();
    }
  }

  // Function to open Razorpay modal
  function openRazorpayModal() {

    //testing start
    // showMessage('biting button working, check the razorpay modal');
    // for testing end

    const productId = <?php echo $_REQUEST['id']; ?>;

    // First check if user has already bid
    checkExistingBid(productId)
      .then(response => {
        if (response.has_bid) {
          showMessage('You have already submitted a bid for this product', 'error');
          closeModal();
          return;
        }
      })
    const quantity = document.getElementById('quantity').value;
    const proposedPrice = document.getElementById('proposedPrice').value;

    // First, create the order
    fetch('submit_bid.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          product_id: <?php echo $_REQUEST['id']; ?>,
          quantity: quantity,
          proposed_price: proposedPrice
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === 'success') {
          var options = {
            key: "<?php echo RAZORPAY_KEY_ID; ?>",
            amount: data.amount,
            currency: "INR",
            name: "Deadstock",
            description: "Bid Payment",
            order_id: data.order_id,
            handler: function(response) {
              // Handle successful payment
              fetch('submit_bid.php', {
                  method: 'POST',
                  headers: {
                    'Content-Type': 'application/json',
                  },
                  body: JSON.stringify(response)
                })
                .then(res => res.json())
                .then(result => {
                  if (result.status === 'success') {
                    showMessage('Bid submitted successfully!', 'success');
                    closeModal();
                  } else {
                    showMessage('Error: ' + result.message, 'error');
                  }
                })
                .catch(error => {
                  console.error('Error:', error);
                  showMessage('An error occurred while processing your payment');
                });
            },
            prefill: {
              name: "<?php echo $_SESSION['user_session']['name'] ?? ''; ?>",
              email: "<?php echo $_SESSION['user_session']['email'] ?? ''; ?>"
            },
            theme: {
              color: "#3399cc"
            }
          };
          var rzp = new Razorpay(options);
          rzp.open();
        } else {
          showMessage('Error: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showMessage('An error occurred while checking existing bids');
      });
  }
</script>


<section class="bg border-top py-4">
  <div class="container">
    <div class="row gx-4">
      <div class="col-lg-8 mb-4">
        <div class="border rounded-2 px-3 py-2 bg-white">
          <!-- Pills navs -->
          <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="pills-spec-tab" data-bs-toggle="pill" data-bs-target="#pills-spec"
                type="button" role="tab">Description</button>
            </li>
            <!-- <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-warranty-tab" data-bs-toggle="pill" data-bs-target="#pills-warranty"
                  type="button" role="tab">Warranty info</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-shipping-tab" data-bs-toggle="pill" data-bs-target="#pills-shipping"
                  type="button" role="tab">Return Policy</button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-seller-tab" data-bs-toggle="pill" data-bs-target="#pills-seller"
                  type="button" role="tab">Seller profile</button>
              </li> -->
          </ul>

          <!-- Pills content -->
          <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-spec" role="tabpanel">
              <p>
                <?php
                $product_id = intval($_GET['id']);
                $sql = "SELECT p_description
                     FROM tbl_product 
                      WHERE tbl_product.id = $product_id";
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                  $row = $result->fetch_assoc();
                  echo '<td class="py-2">' . htmlspecialchars($row['p_description']) . '</td>';
                }
                ?>
              </p>
              <div class="row mb-2">



                <div class="col-12 col-md-6 mb-0">

                </div>
              </div>

            </div>
            <?php
            $product_id = intval($_GET['id']);
            $sql = "SELECT product_catalogue
        FROM tbl_product
        WHERE tbl_product.id = $product_id";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $pdf_name = $row['product_catalogue'];

              // files are stored in the 'assets/uploads/' directory
              $file_path = 'assets/uploads/' . $pdf_name;
              $view_url = "pdf_download.php?action=view&id=$product_id";
              $download_url = "pdf_download.php?action=download&id=$product_id";
              echo '<a href="' . $view_url . '" class="btn btn-warning" target="_blank" style="text-decoration:none;"><i class="fa fa-file-pdf-o"></i> View Catalogue</a>';
              echo '&nbsp;&nbsp;';
              echo '<a href="' . $download_url . '" class="btn btn-success" style="text-decoration:none;"><i class="fa fa-download"></i> Download Catalogue</a>';
            }
            ?>
          </div>
        </div>
      </div>


      <?php
      $search_term = substr($p_name, 0, 4); // Get the first 4 letters of the current product name
      $search_term = htmlspecialchars($search_term, ENT_QUOTES, 'UTF-8'); // Prevent any special character issues

      $sql = "SELECT id, p_name, p_current_price, p_old_price, p_featured_photo 
          FROM tbl_product 
          WHERE p_name LIKE :search_term 
          AND id != :current_id
          AND p_is_approve = 1
          LIMIT 3";

      $stmt = $pdo->prepare($sql);
      $stmt->execute([
        ':search_term' => "%$search_term%",
        ':current_id' => $product_id
      ]);

      $related_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
      ?>

      <div class="col-lg-4">
        <div class="border rounded-2 shadow-0">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Similar items</h5>
              <?php foreach ($related_products as $related_product): ?>
                <div class="d-flex mb-3">
                  <a href="product_landing.php?id=<?php echo $related_product['id']; ?>" class="me-3">
                    <img src="assets/uploads/product-photos/<?php echo $related_product['p_featured_photo']; ?>"
                      style="width: 100px; height: 96px;" class="img-thumbnail"
                      alt="<?php echo htmlspecialchars($related_product['p_name']); ?>" />
                  </a>
                  <div class="info">
                    <a href="product_landing.php?id=<?php echo $related_product['id']; ?>"
                      class="text-decoration-none mb-1">
                      <?php echo htmlspecialchars($related_product['p_name']); ?>
                    </a>
                    <div class="d-flex align-items-center gap-2 mt-1">
                      <p class="text-dark mb-0">
                        <strong>₹<?php echo $related_product['p_current_price']; ?></strong>
                      </p>
                      <?php if ($related_product['p_old_price'] > 0): ?>
                        <span class="text-muted text-decoration-line-through">
                          ₹<?php echo $related_product['p_old_price']; ?>
                        </span>
                        <?php
                        $discount = (($related_product['p_old_price'] - $related_product['p_current_price']) / $related_product['p_old_price']) * 100;
                        echo '<span class="badge bg-success">' . round($discount) . '% OFF</span>';
                        ?>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
              <?php if (count($related_products) == 0): ?>
                <div class="text-center py-4">
                  <p class="text-muted">No similar products found</p>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>


<!-- Link Bootstrap CSS and Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script For Request Price Process -->
<script>
  document.querySelector('.material-info-btn').addEventListener('click', function() {
    var container = document.querySelector('.key-button-container');
    container.classList.toggle('show-info'); // Toggle the visibility of the container
  });


  const modalOverlay = document.getElementById("modalOverlay");
  const requestBtn = document.getElementById("requestPriceBtn");
  const closeBtn = document.getElementById("closeModal");
  const cancelBtn = document.getElementById("cancelBtn");
  const form = document.getElementById("priceRequestForm");
  const termsModalOverlay = document.getElementById("termsModalOverlay");
  const closeTermsModal = document.getElementById("closeTermsModal");


  // Open the modal
  function openModal() {
    modalOverlay.style.display = "flex";
    document.body.style.overflow = "hidden";
  }

  // Close the modal
  function closeModal() {
    modalOverlay.style.display = "none";
    document.body.style.overflow = "auto";
    form.reset();
  }

  function closeTerms() {
    termsModalOverlay.style.display = "none";
    document.body.style.overflow = "auto";
  }


  // Attach event listeners for opening and closing modal
  requestBtn.addEventListener("click", openModal);
  closeBtn.addEventListener("click", closeModal);
  cancelBtn.addEventListener("click", closeModal);


  modalOverlay.addEventListener("click", (e) => {
    if (e.target === modalOverlay) {
      closeModal();
    }
  });

  document.addEventListener('DOMContentLoaded', function() {
    const infoBtn = document.querySelector('.material-info-btn');
    const infoContainer = document.querySelector('.l-info-icons-container');
    let isVisible = false;

    // Toggle info container
    infoBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      isVisible = !isVisible;
      infoContainer.classList.toggle('show', isVisible);
      // Update arrow direction
      this.innerHTML = `Key (explanation of symbols) ${isVisible ? '▲' : '▼'}`;
    });

    // Close info container when clicking outside
    document.addEventListener('click', function(e) {
      if (!infoContainer.contains(e.target) && !infoBtn.contains(e.target)) {
        isVisible = false;
        infoContainer.classList.remove('show');
        infoBtn.innerHTML = 'Key (explanation of symbols) ▼';
      }
    });
  });

  document.addEventListener("DOMContentLoaded", function() {
    const thumbnailLinks = document.querySelectorAll(".thumbnail-link img");
    const mainImage = document.getElementById("mainImage");

    thumbnailLinks.forEach((thumbnail) => {
      thumbnail.addEventListener("click", () => {
        const fullImageSrc = thumbnail.getAttribute("data-full-image");
        mainImage.src = fullImageSrc; // Update the main image dynamically
      });
    });

    // Ensure the main image defaults to p_featured_photo
    mainImage.src = "<?php echo 'assets/uploads/product-photos/' . $p_featured_photo; ?>";
  });
  const img = document.querySelector('.zoom-effect');

  img.addEventListener('mousemove', function(e) {
    const mouseX = e.offsetX;
    const mouseY = e.offsetY;

    const width = img.width;
    const height = img.height;
    const percentX = (mouseX / width) * 100;
    const percentY = (mouseY / height) * 100;

    img.style.transformOrigin = `${percentX}% ${percentY}%`;
  });

  img.addEventListener('mouseleave', function() {
    img.style.transformOrigin = 'center center'; // Defaults back to center zoom
  });
</script>

<div>
  <?php include 'footer.php'; ?>
</div>