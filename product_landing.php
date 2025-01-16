<?php include 'header.php';
include 'db_connection.php';
require_once('vendor/autoload.php');
require_once('config.php');
?>

<!-- Style  -->
<link rel="stylesheet" href="css/product_landing.css">

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
  $p_is_active = $row['p_is_active'];
  $ecat_id = $row['ecat_id'];
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

if (!isset($_SESSION['recently_viewed'])) {
  $_SESSION['recently_viewed'] = array();
}

// Add the current product to recently viewed if it's not already there
if (!in_array($product_id, $_SESSION['recently_viewed'])) {
  // Keep only the last 12 viewed products
  if (count($_SESSION['recently_viewed']) >= 12) {
    array_shift($_SESSION['recently_viewed']);
  }
  array_push($_SESSION['recently_viewed'], $product_id);
}



if (isset($_POST['add_to_cart'])) {
  // Check if the user is logged in
  if (isset($_SESSION['user_session']['id'])) {
    $user_id = $_SESSION['user_session']['id']; // Get the user ID from the session
    $product_quantity = intval($_POST['product_quantity']); // Ensure quantity is a valid number

    // Check if the product already exists in the cart
    $select_cart = mysqli_query($conn, "SELECT * FROM `tbl_cart` WHERE id = '$product_id' AND user_id = '$user_id'") or die('Query failed');
    if (mysqli_num_rows($select_cart) > 0) {
      echo "<script>alert('Product already added to cart!');</script>";
    } else {
      // Insert the product into the cart
      $insert_query = "INSERT INTO `tbl_cart` (id, user_id, quantity) 
                           VALUES ('$product_id', '$user_id', '$product_quantity')";
      if (mysqli_query($conn, $insert_query)) {
        echo "<script>alert('Product added to cart!');</script>";
      } else {
        echo "<script>alert('Failed to add product to cart. Please try again!');</script>";
      }
    }
  } else {
    // User is not logged in, show alert
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            var loginModal = new bootstrap.Modal(document.getElementById('staticBackdrop'), {
                backdrop: 'static'
            });
            loginModal.show();
        });
    </script>";
  }
}
?>

<?php
$select_product = mysqli_query($conn, "SELECT * FROM `tbl_product`") or die('query failed');
if (mysqli_num_rows($select_product) > 0) {
  while ($fetch_product = mysqli_fetch_assoc($select_product)) {

  }
}
?>

<?php
if ($error_message1 != '') {
  echo "<script>alert('" . $error_message1 . "')</script>";
}
if ($success_message1 != '') {
  echo "<script>alert('" . $success_message1 . "')</script>";
  header('location: product.php?id=' . $_REQUEST['id']);
}
?>

<!-- content -->
<section class="py-5">
  <div class="container" style="margin-top: -30px;">
    <!-- Breadcrumb Section -->
    <nav aria-label="breadcrumb" style="margin-left:6px;">
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php" style="text-decoration: none;">Home</a></li>
        <li class="breadcrumb-item"><a href="products_listing.php?id=<?php echo $tcat_id; ?>"
            style="text-decoration: none;"><?php echo htmlspecialchars($tcat_name); ?></a></li>
        <li class="breadcrumb-item"><a href="products_listing.php?id=<?php echo $mcat_id; ?>"
            style="text-decoration: none;"><?php echo htmlspecialchars($mcat_name); ?></a></li>
        <li class="breadcrumb-item active" aria-current="page" style="text-decoration: none;">
          <?php echo htmlspecialchars($ecat_name); ?>
        </li>
      </ol>
    </nav>

    <div class="row gx-5">
      <aside class="col-lg-6">
        <div class="border rounded-4 mb-3 d-flex justify-content-center">
          <a data-bs-toggle="modal" class="rounded-4" data-bs-target="#imageModal" href="#">
            <!-- <img style="max-width: 100%; max-height: 100vh; margin: auto;" class="rounded-4" src="icons\hole.png" /> -->
            <img style="max-width: 100%; max-height: 100vh; margin: auto;" class="rounded-4"
              src="assets/uploads/product-photos/<?php echo $p_featured_photo; ?>">

          </a>
        </div>
        <div class="d-flex justify-content-center mb-3">
          <a class="border mx-1 rounded-2" href="#" data-bs-toggle="modal" data-bs-target="#imageModal">
            <img width="60" height="60" class="rounded-2" src="icons\hole.png" />
          </a>
          <a class="border mx-1 rounded-2" href="#" data-bs-toggle="modal" data-bs-target="#imageModal">
            <img width="60" height="60" class="rounded-2" src="icons\hole.png" />
          </a>
          <a class="border mx-1 rounded-2" href="#" data-bs-toggle="modal" data-bs-target="#imageModal">
            <img width="60" height="60" class="rounded-2" src="icons\hole.png" />
          </a>
          <a class="border mx-1 rounded-2" href="#" data-bs-toggle="modal" data-bs-target="#imageModal">
            <img width="60" height="60" class="rounded-2" src="icons\hole.png" />
          </a>
          <a class="border mx-1 rounded-2" href="#" data-bs-toggle="modal" data-bs-target="#imageModal">
            <img width="60" height="60" class="rounded-2" src="icons\hole.png" />
          </a>
        </div>
      </aside>
      <main class="col-lg-6">
        <class="ps-lg-3">
          <h4 class="title text-dark">
            <?php echo $p_name; ?>
          </h4>
          <div class="d-flex flex-row my-3">
            <div class="text-warning mb-1 me-2">
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-fill"></i>
              <i class="bi bi-star-half"></i>
              <span class="ms-1">
                4.5
              </span>
            </div>
            <span class="text-muted"><i class="bi bi-basket mx-1"></i><?php echo $p_qty; ?></span>
            <span class="text-success ms-2">In stock</span>
          </div>

          <div class="mb-3">
            <div class="d-flex align-items-center gap-2 mb-1">
              <span class="h5 mb-0" style="color: #000;">₹<?php echo $p_current_price; ?></span>
              <span class="h6 mb-0"
                style="color: #9E9E9E; text-decoration: line-through;">₹<?php echo $p_old_price; ?></span>
              <?php
              // Calculate discount if applicable
              if ($p_old_price > 0) {
                $discount = (($p_old_price - $p_current_price) / $p_old_price) * 100;
                // Round the discount to 0 decimal places and display
                echo '<span class="badge bg-success">' . round($discount) . '% OFF</span>';
              }
              ?>
            </div>
          </div>


          <div class="row">
            <dt class="col-3">Insert width</dt>
            <dd class="col-9">1.5 mm</dd>

            <dt class="col-3">Cutting depth</dt>
            <dd class="col-9">10 mm</dd>

            <dt class="col-3">Center height</dt>
            <dd class="col-9">24 mm</dd>

            <dt class="col-3">Module size</dt>
            <dd class="col-9">30 mm</dd>
          </div>

          <hr />
          <!-- <button class="btn btn-warning shadow-0"> Buy now </button> -->
          <div class="product-container">

                <div class="product-box">

                <div class="row mb-4">
                  <div class="col-md-4 col-6 mb-3">
                    <label class="mb-2 d-block">Quantity</label>
                    <div class="input-group mb-3" style="width: 170px;">
                      <input type="number" class="form-control text-center" name="product_quantity" id="quantity-input"
                        value="1" min="1" />
                    </div>
                  </div>
                </div>
                <!-- <input type="submit" value="Add to Cart" name="add_to_cart" class="btn btn-primary shadow-0"> -->
            </div>

          </div>
          <button class="btn btn-warning shadow-0"> Buy now </button>
          <button type="submit" name="add_to_cart" class="btn btn-primary shadow-0"> <i class="bi bi-basket me-1"></i> Add to cart </button>
          </form>

          <!-- Request Price Button -->
          <button id="requestPriceBtn" class="request-price-btn btn btn-danger border" data-product-id="<?php echo htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8'); ?>">
          <i class="fa fa-gavel"></i>  Place a Bid
          </button>
          <button class="btn btn-light border"> <i class="bi bi-heart me-1"></i> Save </button>
    </div>
    </main>
  </div>
  </div>
</section>

<!-- Modal Overlay For Request Price -->
<div class="modal-overlay" id="modalOverlay" style="display: none;">
    <!-- Modal -->
    <div id="priceRequestModal">
        <button class="close-button-rp" id="closeModal">&times;</button>
        <div class="modal-header-rp">
            <h3>Request a Price</h3>
        </div>
        <form id="priceRequestForm" method="POST" action="submit_bid.php">
            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($_REQUEST['id'], ENT_QUOTES, 'UTF-8'); ?>">
            
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    required
                    min="1"
                    placeholder="Enter quantity"
                />
            </div>

            <div class="form-group">
                <label for="proposedPrice">Your Bid Price (₹)</label>
                <input
                    type="number"
                    id="proposedPrice"
                    name="proposed_price"
                    required
                    min="0"
                    step="0.01"
                    placeholder="Enter Price Per Unit"
                />
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-rp btn-cancel-rp" id="cancelBtn">Cancel</button>
                <!-- Submit Request Button -->
                <button type="button" onclick="openRazorpayModal()" class="btn-rp btn-submit-rp">Pay and Place your Bid</button>
            </div>
        </form>
    </div>
</div>
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
    // Function to open Razorpay modal
    function openRazorpayModal() {
    const productId = <?php echo $_REQUEST['id']; ?>;
    
    // First check if user has already bid
    checkExistingBid(productId)
        .then(response => {
            if (response.has_bid) {
                alert('You have already submitted a bid for this product');
                closeModal();
                return;
            }})
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
                handler: function (response) {
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
                            alert('Bid submitted successfully!');
                            closeModal();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while processing your payment');
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
            alert('Error: ' + data.message);
        }
      })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while checking existing bids');
        });
}
</script>


<section class="bg-light border-top py-4">
  <div class="container">
    <div class="row gx-4">
      <div class="col-lg-8 mb-4">
        <div class="border rounded-2 px-3 py-2 bg-white">
          <!-- Pills navs -->
          <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="pills-spec-tab" data-bs-toggle="pill" data-bs-target="#pills-spec"
                type="button" role="tab">Specification</button>
            </li>
            <li class="nav-item" role="presentation">
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
            </li>
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
              echo '<a href="' . $view_url . '" class="btn btn-warning" target="_blank"><i class="fa fa-file-pdf-o"></i> View Catalogue</a>';
              echo '&nbsp;&nbsp;';
              echo '<a href="' . $download_url . '" class="btn btn-success"><i class="fa fa-download"></i> Download Catalogue</a>';
            }
            ?>
            <div class="tab-pane fade" id="pills-warranty" role="tabpanel">
              Tab content or sample information now <br />
              Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et
              dolore magna aliqua.
            </div>
            <div class="tab-pane fade" id="pills-shipping" role="tabpanel">
              <?php
              $product_id = intval($_GET['id']);
              $sql = "SELECT p_return_policy 
                          FROM tbl_product 
                         WHERE tbl_product.id = $product_id";
              $result = $conn->query($sql);
              if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo '<td class="py-2">' . htmlspecialchars($row['p_return_policy']) . '</td>';
              }
              ?>
            </div>
            <div class="tab-pane fade" id="pills-seller" role="tabpanel">
              <table class="table border mt-3 mb-2">
                <tr>
                  <th class="py-2">Seller Name:</th>
                  <?php
                  $product_id = intval($_GET['id']);
                  $sql = "SELECT sellers.seller_name 
                     FROM tbl_product 
                      INNER JOIN sellers ON tbl_product.seller_id = sellers.seller_id 
                      WHERE tbl_product.id = $product_id";
                  $result = $conn->query($sql);
                  if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo '<td class="py-2">' . htmlspecialchars($row['seller_name']) . '</td>';
                  }
                  ?>
                </tr>
                <tr>
                  <th class="py-2">Seller Email:</th>
                  <?php
                  $product_id = intval($_GET['id']);
                  $sql = "SELECT sellers.seller_email 
                          FROM tbl_product 
                          INNER JOIN sellers ON tbl_product.seller_id = sellers.seller_id 
                          WHERE tbl_product.id = $product_id";
                  $result = $conn->query($sql);
                  if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo '<td class="py-2">' . htmlspecialchars($row['seller_email']) . '</td>';
                  }
                  ?>
                </tr>
                <tr>
                  <th class="py-2">Certification No:</th>
                  <td class="py-2">4e5r6t7yu8</td>
                </tr>
                <!-- <tr>
                  <th class="py-2">Memory</th>
                  <td class="py-2">8 GB RAM or 16 GB RAM</td>
                </tr>
                <tr>
                  <th class="py-2">Graphics</th>
                  <td class="py-2">Intel Iris Plus Graphics 640</td>
                </tr> -->
              </table>
            </div>
          </div>
        </div>
      </div>


      <!-- Display Similar Products -->
      <?php
      // Fetch similar products from the same end category
      $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE ecat_id=? AND id != ? AND p_is_featured=? ORDER BY p_total_view DESC LIMIT 4");
      $statement->execute(array($ecat_id, $_REQUEST['id'], 1));
      $related_products = $statement->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <!-- Then replace the similar items card with this code -->
      <div class="col-lg-4">
        <div class="border rounded-2 shadow-0">
          <div class="card">
            <div class="card-body">
              <h5 class="card-title">Similar items</h5>
              <?php foreach ($related_products as $related_product): ?>
                <div class="d-flex mb-3">
                  <a href="product_landing.php?id=<?php echo $related_product['id']; ?>" class="me-3">
                    <img src="payment/assets/uploads/product-photos/<?php echo $related_product['p_featured_photo']; ?>"
                      style="min-width: 96px; height: 96px;" class="img-thumbnail"
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


<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Script For Request Price Process -->
<script>
    const modalOverlay = document.getElementById("modalOverlay");
    const requestBtn = document.getElementById("requestPriceBtn");
    const closeBtn = document.getElementById("closeModal");
    const cancelBtn = document.getElementById("cancelBtn");
    const form = document.getElementById("priceRequestForm");

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

    // Attach event listeners for opening and closing modal
    requestBtn.addEventListener("click", openModal);
    closeBtn.addEventListener("click", closeModal);
    cancelBtn.addEventListener("click", closeModal);
    modalOverlay.addEventListener("click", (e) => {
        if (e.target === modalOverlay) {
            closeModal();
        }
    });
</script>

<?php include 'footer.php'; ?>