<?php require_once( 'header.php');?>
<link rel="stylesheet" href="./css/index.css">

<div class="category-pad">
<div class="category-box">
    <ul class="categories">
        <?php
            $statement = $pdo->prepare("SELECT * FROM tbl_top_category WHERE show_on_menu=1");
            $statement->execute();
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            foreach ($result as $row) {
        ?>
            <li class="category">
                <a class="category-link" href="product-category.php?id=<?php echo $row['tcat_id']; ?>&type=top-category">
                    <img src="./icons/hole.png" width="30px" height="30px">
                    <span><?php echo $row['tcat_name']; ?></span>
                </a>
                <ul class="subcategories">
                    <?php
                        $statement1 = $pdo->prepare("SELECT * FROM tbl_mid_category WHERE tcat_id=?");
                        $statement1->execute(array($row['tcat_id']));
                        $result1 = $statement1->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($result1 as $row1) {
                    ?>
                        <li class="subcategory">
                            <a class="subcategory-link" href="product-category.php?id=<?php echo $row1['mcat_id']; ?>&type=mid-category">
                                <?php echo $row1['mcat_name']; ?>
                            </a>
                            <ul class="sub-subcategories">
                                <?php
                                    $statement2 = $pdo->prepare("SELECT * FROM tbl_end_category WHERE mcat_id=?");
                                    $statement2->execute(array($row1['mcat_id']));
                                    $result2 = $statement2->fetchAll(PDO::FETCH_ASSOC);
                                    foreach ($result2 as $row2) {
                                ?>
                                    <li class="sub-subcategory">
                                        <a href="product-category.php?id=<?php echo $row2['ecat_id']; ?>&type=end-category">
                                            <?php echo $row2['ecat_name']; ?>
                                        </a>
                                    </li>
                                <?php
                                    }
                                ?>
                            </ul>
                        </li>
                    <?php
                        }
                    ?>
                </ul>
            </li>
        <?php
            }
        ?>
    </ul>
</div>


  <div class="right-category-pad">
    <div class="quote-container">
      <p class="quote"><span class="quote-bold">Buy</span> at your Desired bidding price</p>
      <img src="assets/uploads/<?php echo $logo?>" alt="Logo" class="logo">
    </div>
    <div class="brands">
      <div class="ind-brand">
        <a href="#" class="link-body-emphasis link-underline-opacity-0">
          <div class="img-category">
        <img src="./icons/index milling.png">
      </div>
      <p class="brand-name">Indexable Milling Tools</p>
      </a>
      </div>
      <div class="ind-brand">
        <a href="#" class="link-body-emphasis link-underline-opacity-0">
          <div class="img-category">
        <img src="./icons/endmill.png">
      </div>
      <p class="brand-name">Solid Carbide Endmills</p>
        </a>
      </div>
      <div class="ind-brand">
        <a href="#" class="link-body-emphasis link-underline-opacity-0">
          <div class="img-category">
        <img src="./icons/turning.png">
      </div>
      <p class="brand-name ">Turning Tools</p>
        </a>
      </div>
      <div class="ind-brand">
        <a href="#" class="link-body-emphasis link-underline-opacity-0">
          <div class="img-category">
            <img src="./icons/hole.png">
          </div>
          <p class="brand-name">Holemaking Tools</p>
        </a>
      </div>
      <div class="ind-brand">
        <a href="#" class="link-body-emphasis link-underline-opacity-0">
          <div class="img-category">
        <img src="./icons/Threading tools.png">
      </div>
      <p class="brand-name">Threading Tools</p>
        </a>
      </div>
      <div class="ind-brand">
        <a href="./products.html" class="link-body-emphasis link-underline-opacity-0">
          <div class="img-category">
            <img src="./icons/others.png">
      </div>
      <p class="brand-name">Others</p>
        </a>
      </div>
  </div>
  </div>
</div>








<!-- banner -->
<div class="banner"> 
      <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        
      <?php
          $i = 0;
          $statement = $pdo->prepare("SELECT * FROM tbl_slider");
          $statement->execute();
          $result = $statement->fetchAll(PDO::FETCH_ASSOC);
          foreach ($result as $row) {            
              $activeClass = ($i === 0) ? 'active' : ''; // Only first item is active
          ?>
              <div class="carousel-item <?php echo $activeClass; ?>">
                  <img class="img" src="assets/uploads/<?php echo $row['photo']; ?>" class="d-block w-100" alt="..." style="width:100%" >
              </div>
          <?php
              $i++;
          }
          ?>

                  
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev">
                  <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next">
                  <span class="carousel-control-next-icon" aria-hidden="true"></span>
                  <span class="visually-hidden">Next</span>
                </button>
              </div>
            </div>
            
<!-- end banner  -->

<!-- Live Bidding -->
<!-- <div class="container swiper">
  <p class="swiper-title">Live Bidding</p>
  <div class="slider-wrapper">
    <div class="card-list swiper-wrapper">
      <div class="card-item swiper-slide">
        <div class="card-img">
        <img alt="Card-img" src="./icons/index milling.png" >
        </div>
        <div class="live-bidding-details">
        <div class="live-bidding-product-title">
          <h2>Product title</h2>
        </div>
          <div class="price-section">
            <p class="actual-price">₹1,000.00</p>
            <p class="price-strikethrough"> ₹2,500.00</p>
          </div>
          <div class="live-bidding-price">
            <p>Live Bidding Price: <span class="live-bidding-price-text">₹ 100.00</span></p>
          </div>
          <div class="available-stock">
            <p>Available Stock: <span class="available-stock-text">10</span> units</p>
          </div>
          <div class="bid-ends-in">
            <p>Bid Ends In: <span class="bid-ends-in-text" id="bid-ends-in-time"></span></p>
          </div>
        </div>
      </div>

      <div class="card-item swiper-slide">
        <div class="card-img">
          <img alt="Card-img" src="./icons/index milling.png" >
          </div>
          <div class="live-bidding-details">
          <div class="live-bidding-product-title">
            <h2>Product title</h2>
          </div>
            <div class="price-section">
              <p class="actual-price">₹1,000.00</p>
              <p class="price-strikethrough"> ₹2,500.00</p>
            </div>
            <div class="live-bidding-price">
              <p>Live Bidding Price: <span class="live-bidding-price-text">₹ 100.00</span></p>
            </div>
            <div class="available-stock">
              <p>Available Stock: <span class="available-stock-text">20</span> units</p>
            </div>
            <div class="bid-ends-in">
              <p>Bid Ends In: <span class="bid-ends-in-text" id="bid-ends-in-time"></span></p>
            </div>
          </div>
      </div>

      <div class="card-item swiper-slide">
        <div class="card-img">
          <img alt="Card-img" src="./icons/index milling.png" >
          </div>
          <div class="live-bidding-details">
          <div class="live-bidding-product-title">
            <h2>Product title</h2>
          </div>
            <div class="price-section">
              <p class="actual-price">₹1,000.00</p>
              <p class="price-strikethrough"> ₹2,500.00</p>
            </div>
            <div class="live-bidding-price">
              <p>Live Bidding Price: <span class="live-bidding-price-text">₹ 100.00</span></p>
            </div>
            <div class="available-stock">
              <p>Available Stock: <span class="available-stock-text">30</span> units</p>
            </div>
            <div class="bid-ends-in">
              <p>Bid Ends In: <span class="bid-ends-in-text" id="bid-ends-in-time"></span></p>
            </div>
          </div>
      </div>

      <div class="card-item swiper-slide">
        <div class="card-img">
          <img alt="Card-img" src="./icons/index milling.png" >
          </div>
          <div class="live-bidding-details">
          <div class="live-bidding-product-title">
            <h2>Product title</h2>
          </div>
            <div class="price-section">
              <p class="actual-price">₹1,000.00</p>
              <p class="price-strikethrough"> ₹2,500.00</p>
            </div>
            <div class="live-bidding-price">
              <p>Live Bidding Price: <span class="live-bidding-price-text">₹ 100.00</span></p>
            </div>
            <div class="available-stock">
              <p>Available Stock: <span class="available-stock-text">40</span> units</p>
            </div>
            <div class="bid-ends-in">
              <p>Bid Ends In: <span class="bid-ends-in-text" id="bid-ends-in-time"></span></p>
            </div>
          </div>
      </div>

      <div class="card-item swiper-slide">
        <div class="card-img">
          <img alt="Card-img" src="./icons/index milling.png" >
          </div>
          <div class="live-bidding-details">
          <div class="live-bidding-product-title">
            <h2>Product title</h2>
          </div>
            <div class="price-section">
              <p class="actual-price">₹1,000.00</p>
              <p class="price-strikethrough"> ₹2,500.00</p>
            </div>
            <div class="live-bidding-price">
              <p>Live Bidding Price: <span class="live-bidding-price-text">₹ 100.00</span></p>
            </div>
            <div class="available-stock">
              <p>Available Stock: <span class="available-stock-text">50</span> units</p>
            </div>
            <div class="bid-ends-in">
              <p>Bid Ends In: <span class="bid-ends-in-text" id="bid-ends-in-time"></span></p>
            </div>
          </div>
      </div>

      <div class="card-item swiper-slide">
        <div class="card-img">
          <img alt="Card-img" src="./icons/index milling.png" >
          </div>
          <div class="live-bidding-details">
          <div class="live-bidding-product-title">
            <h2>Product title</h2>
          </div>
            <div class="price-section">
              <p class="actual-price">₹1,000.00</p>
              <p class="price-strikethrough"> ₹2,500.00</p>
            </div>
            <div class="live-bidding-price">
              <p>Live Bidding Price: <span class="live-bidding-price-text">₹ 100.00</span></p>
            </div>
            <div class="available-stock">
              <p>Available Stock: <span class="available-stock-text">60</span> units</p>
            </div>
            <div class="bid-ends-in">
              <p>Bid Ends In: <span class="bid-ends-in-text" id="bid-ends-in-time"></span></p>
            </div>
          </div>
      </div>
    </div>

    <div class="swiper-pagination"></div>
    <div class="swiper-slide-button swiper-button-prev"></div>
    <div class="swiper-slide-button swiper-button-next"></div>
  </div>
</div> -->

<!-- Display the product depends od category -->

<?php
$topCategories = []; // Initialize as an empty array
$statement = $pdo->prepare("SELECT * FROM tbl_top_category");
$statement->execute();
$topCategories = $statement->fetchAll(PDO::FETCH_ASSOC);

// Loop through each top category
if (!empty($topCategories)) {
    foreach ($topCategories as $topCategory) {
        $tcat_id = $topCategory['tcat_id']; // Get the current category ID

        // Fetch all products related to the specified tcat_id, including p_is_featured
        $statement = $pdo->prepare("
            SELECT p.*, m.mcat_name, e.ecat_name 
            FROM tbl_product p
            JOIN tbl_end_category e ON p.ecat_id = e.ecat_id
            JOIN tbl_mid_category m ON e.mcat_id = m.mcat_id
            WHERE m.tcat_id = ?
        ");

        if ($statement->execute([$tcat_id])) {
            $products = $statement->fetchAll(PDO::FETCH_ASSOC);

            // Check if there are featured products
            $hasFeaturedProducts = false;
            foreach ($products as $product) {
                if ($product['p_is_featured'] == 1) {
                    $hasFeaturedProducts = true;
                    break; // No need to check further, we found a featured product
                }
            }

            // Only display the wrapper if there are featured products
            if ($hasFeaturedProducts) {
                ?>
                <div class="wrapper">
                    <span class="cat-title"><?php echo htmlspecialchars($topCategory['tcat_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                    <ul class="carousel">
                        <?php foreach ($products as $product): ?>
                            <?php if ($product['p_is_featured'] == 1): // Check if the product is featured ?>
                                <li class="cat-product-list-card swiper-slide">
                                    <a href="product_landing.php?id=<?php echo htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8'); ?>" style="text-decoration: none; color: black;">
                                        <div class="cat-product-img">
                                            <img src="payment/assets/uploads/<?php echo htmlspecialchars($product['p_featured_photo'], ENT_QUOTES, 'UTF-8'); ?>" width="130px" height="100px" alt="<?php echo htmlspecialchars($product['p_name'], ENT_QUOTES, 'UTF-8'); ?>">
                                        </div>
                                        <div class="product-card-lower">
                                            <div class="cat-product-title">
                                                <span><?php echo htmlspecialchars($product['p_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                            </div>
                                            <div class="cat-product-price">
                                                ₹<?php echo number_format($product['p_current_price'], 2); ?>
                                            </div>
                                            <div class="cat-product-original-price">
                                                <?php if (!empty($product['p_old_price'])): ?>
                                                    <div class="price-strike">₹<?php echo number_format($product['p_old_price'], 2); ?></div>
                                                    <div class="cat-product-discount">
                                                        <?php
                                                        // Calculate the discount percentage
                                                        $discount = (($product['p_old_price'] - $product['p_current_price']) / $product['p_old_price']) * 100;
                                                        echo round($discount) . '% OFF';
                                                        ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            <?php endif; // End of featured product check ?>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php
            }
        } else {
            // Handle the error if the statement failed
            error_log("Database query failed: " . implode(", ", $statement->errorInfo()));
        }
    }
} // End of top categories loop
?>
<!-- End Display the product depends od category -->


<?php include 'footer.php'; ?>





