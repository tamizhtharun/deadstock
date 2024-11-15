<?php include 'header.php';
include 'db_connection.php';

$error_message1 = '';
$success_message1 = '';
?>

<?php
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
  $p_short_description = $row['p_short_description'];
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





if (isset($_POST['form_review'])) {

  $statement = $pdo->prepare("SELECT * FROM tbl_rating WHERE id=? AND cust_id=?");
  $statement->execute(array($_REQUEST['id'], $_SESSION['customer']['cust_id']));
  $total = $statement->rowCount();

  if ($total) {
    $error_message = LANG_VALUE_68;
  } else {
    $statement = $pdo->prepare("INSERT INTO tbl_rating (p_id,cust_id,comment,rating) VALUES (?,?,?,?)");
    $statement->execute(array($_REQUEST['id'], $_SESSION['customer']['cust_id'], $_POST['comment'], $_POST['rating']));
    $success_message = LANG_VALUE_163;
  }

}

// Getting the average rating for this product
$t_rating = 0;
$statement = $pdo->prepare("SELECT * FROM tbl_rating WHERE p_id=?");
$statement->execute(array($_REQUEST['id']));
$tot_rating = $statement->rowCount();
if ($tot_rating == 0) {
  $avg_rating = 0;
} else {
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
  foreach ($result as $row) {
    $t_rating = $t_rating + $row['rating'];
  }
  $avg_rating = $t_rating / $tot_rating;
}

if (isset($_POST['form_add_to_cart'])) {

  // getting the currect stock of this product
  $statement = $pdo->prepare("SELECT * FROM tbl_product WHERE id=?");
  $statement->execute(array($_REQUEST['id']));
  $result = $statement->fetchAll(PDO::FETCH_ASSOC);
  foreach ($result as $row) {
    $current_p_qty = $row['p_qty'];
  }
  if ($_POST['p_qty'] > $current_p_qty):
    $temp_msg = 'Sorry! There are only ' . $current_p_qty . ' item(s) in stock';
    ?>
    <script type="text/javascript">alert('<?php echo $temp_msg; ?>');</script>
    <?php
  else:
    if (isset($_SESSION['cart_p_id'])) {
      $arr_cart_p_id = array();
      $arr_cart_size_id = array();
      $arr_cart_color_id = array();
      $arr_cart_p_qty = array();
      $arr_cart_p_current_price = array();

      $i = 0;
      foreach ($_SESSION['cart_p_id'] as $key => $value) {
        $i++;
        $arr_cart_p_id[$i] = $value;
      }

      $i = 0;
      foreach ($_SESSION['cart_size_id'] as $key => $value) {
        $i++;
        $arr_cart_size_id[$i] = $value;
      }

      $i = 0;
      foreach ($_SESSION['cart_color_id'] as $key => $value) {
        $i++;
        $arr_cart_color_id[$i] = $value;
      }


      $added = 0;
      if (!isset($_POST['size_id'])) {
        $size_id = 0;
      } else {
        $size_id = $_POST['size_id'];
      }
      if (!isset($_POST['color_id'])) {
        $color_id = 0;
      } else {
        $color_id = $_POST['color_id'];
      }
      for ($i = 1; $i <= count($arr_cart_p_id); $i++) {
        if (($arr_cart_p_id[$i] == $_REQUEST['id']) && ($arr_cart_size_id[$i] == $size_id) && ($arr_cart_color_id[$i] == $color_id)) {
          $added = 1;
          break;
        }
      }
      if ($added == 1) {
        $error_message1 = 'This product is already added to the shopping cart.';
      } else {

        $i = 0;
        foreach ($_SESSION['cart_p_id'] as $key => $res) {
          $i++;
        }
        $new_key = $i + 1;

        if (isset($_POST['size_id'])) {

          $size_id = $_POST['size_id'];

          $statement = $pdo->prepare("SELECT * FROM tbl_size WHERE size_id=?");
          $statement->execute(array($size_id));
          $result = $statement->fetchAll(PDO::FETCH_ASSOC);
          foreach ($result as $row) {
            $size_name = $row['size_name'];
          }
        } else {
          $size_id = 0;
          $size_name = '';
        }

        if (isset($_POST['color_id'])) {
          $color_id = $_POST['color_id'];
          $statement = $pdo->prepare("SELECT * FROM tbl_color WHERE color_id=?");
          $statement->execute(array($color_id));
          $result = $statement->fetchAll(PDO::FETCH_ASSOC);
          foreach ($result as $row) {
            $color_name = $row['color_name'];
          }
        } else {
          $color_id = 0;
          $color_name = '';
        }


        $_SESSION['cart_p_id'][$new_key] = $_REQUEST['id'];
        $_SESSION['cart_size_id'][$new_key] = $size_id;
        $_SESSION['cart_size_name'][$new_key] = $size_name;
        $_SESSION['cart_color_id'][$new_key] = $color_id;
        $_SESSION['cart_color_name'][$new_key] = $color_name;
        $_SESSION['cart_p_qty'][$new_key] = $_POST['p_qty'];
        $_SESSION['cart_p_current_price'][$new_key] = $_POST['p_current_price'];
        $_SESSION['cart_p_name'][$new_key] = $_POST['p_name'];
        $_SESSION['cart_p_featured_photo'][$new_key] = $_POST['p_featured_photo'];

        $success_message1 = 'Product is added to the cart successfully!';
      }

    } else {

      if (isset($_POST['size_id'])) {

        $size_id = $_POST['size_id'];

        $statement = $pdo->prepare("SELECT * FROM tbl_size WHERE size_id=?");
        $statement->execute(array($size_id));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
          $size_name = $row['size_name'];
        }
      } else {
        $size_id = 0;
        $size_name = '';
      }

      if (isset($_POST['color_id'])) {
        $color_id = $_POST['color_id'];
        $statement = $pdo->prepare("SELECT * FROM tbl_color WHERE color_id=?");
        $statement->execute(array($color_id));
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $row) {
          $color_name = $row['color_name'];
        }
      } else {
        $color_id = 0;
        $color_name = '';
      }


      $_SESSION['cart_p_id'][1] = $_REQUEST['id'];
      $_SESSION['cart_size_id'][1] = $size_id;
      $_SESSION['cart_size_name'][1] = $size_name;
      $_SESSION['cart_color_id'][1] = $color_id;
      $_SESSION['cart_color_name'][1] = $color_name;
      $_SESSION['cart_p_qty'][1] = $_POST['p_qty'];
      $_SESSION['cart_p_current_price'][1] = $_POST['p_current_price'];
      $_SESSION['cart_p_name'][1] = $_POST['p_name'];
      $_SESSION['cart_p_featured_photo'][1] = $_POST['p_featured_photo'];

      $success_message1 = 'Product is added to the cart successfully!';
    }
  endif;
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
              src="assets/uploads/<?php echo $p_featured_photo; ?>">

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
        <div class="ps-lg-3">
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
            <span class="text-muted"><i class="bi bi-basket mx-1"></i>154 orders</span>
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

          <p>
            <?php echo $p_short_description; ?>
          </p>

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

          <div class="row mb-4">
            <div class="col-md-4 col-6 mb-3">
              <label class="mb-2 d-block">Quantity</label>
              <div class="input-group mb-3" style="width: 170px;">
                <!-- <button class="btn btn-outline-secondary" type="button" id="decrease-btn"> -->
                <!-- <i class="bi bi-dash"></i> -->
                <!-- </button> -->
                <input type="number" class="form-control text-center" id="quantity-input" value="1" min="1" />
                <!-- <button class="btn btn-outline-secondary" type="button" id="increase-btn">
                  <i class="bi bi-plus"></i>
                </button> -->
              </div>
            </div>
          </div>
          <button class="btn btn-warning shadow-0"> Buy now </button>
          <button class="btn btn-primary shadow-0" onclick="addToCart()"> <i class="bi bi-basket me-1"></i> Add to cart </button>
          <?php if(!isset($_SESSION['user_session'])): ?>
              <script>
                function addToCart() {
                  // console.log('User  is not logged in');
                  alert("You must be logged in to continue");              
                }
              </script> 
            <?php else: ?>
              <script>
                function addToCart() {
                  // console.log('User  is logged in');
                  alert("Item added to cart successfully!");
                }
              </script>
            <?php endif; ?>
          <button class="btn btn-light border"> <i class="bi bi-heart me-1"></i> Save </button>
        </div>
      </main>
    </div>
  </div>
</section>

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
              <!-- <p>
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
              </p> -->
              <div class="row mb-2">
                <!-- <div class="col-12 col-md-6">
                  <ul class="list-unstyled mb-0">
                    <li><i class="bi bi-check-lg text-success me-2"></i>Some great feature name here</li>
                    <li><i class="bi bi-check-lg text-success me-2"></i>Lorem ipsum dolor sit amet, consectetur</li>
                    <li><i class="bi bi-check-lg text-success me-2"></i>Duis aute irure dolor in reprehenderit</li>
                    <li><i class="bi bi-check-lg text-success me-2"></i>Optical heart sensor</li>
                  </ul>
                </div> -->

               
                <div class="col-12 col-md-6 mb-0">
                  <!-- <ul class="list-unstyled">
                    <li><i class="bi bi-check-lg text-success me-2"></i>Easy fast and ver good</li>
                    <li><i class="bi bi-check-lg text-success me-2"></i>Some great feature name here</li>
                    <li><i class="bi bi-check-lg text-success me-2"></i>Modern style and design</li>
                  </ul> -->
                </div>
              </div>
              <!-- <table class="table border mt-3 mb-2">
                <tr>
                  <th class="py-2">Display:</th>
                  <td class="py-2">13.3-inch LED-backlit display with IPS</td>
                </tr>
                <tr>
                  <th class="py-2">Processor capacity:</th>
                  <td class="py-2">2.3GHz dual-core Intel Core i5</td>
                </tr>
                <tr>
                  <th class="py-2">Camera quality:</th>
                  <td class="py-2">720p FaceTime HD camera</td>
                </tr>
                <tr>
                  <th class="py-2">Memory</th>
                  <td class="py-2">8 GB RAM or 16 GB RAM</td>
                </tr>
                <tr>
                  <th class="py-2">Graphics</th>
                  <td class="py-2">Intel Iris Plus Graphics 640</td>
                </tr>
              </table> -->
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
    $file_path = 'assets/uploads/'. $pdf_name;
    $view_url = "pdf_download.php?action=view&id=$product_id";
    $download_url = "pdf_download.php?action=download&id=$product_id";
    echo '<a href="'. $view_url . '" class="btn btn-warning" target="_blank"><i class="fa fa-file-pdf-o"></i> View Catalogue</a>';
    echo '&nbsp;&nbsp;';
    echo '<a href="'. $download_url . '" class="btn btn-success"><i class="fa fa-download"></i> Download Catalogue</a>';
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
                    <img src="payment/assets/uploads/<?php echo $related_product['p_featured_photo']; ?>"
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

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'footer.php'; ?>