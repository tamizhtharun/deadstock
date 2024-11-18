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
                        <img src="./assets/uploads/top-categories-images/<?php echo $row['photo']; ?>" width="30px" height="30px" alt="<?php echo $row['tcat_name']; ?>">
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
 
<?php require_once( 'live-bidding-card.php');?>

<!-- Live Bidding -->

<!-- Display the featured product -->
<?php
$topCategories = []; // Initialize as an empty array
$statement = $pdo->prepare("SELECT * FROM tbl_top_category");
$statement->execute();
$topCategories = $statement->fetchAll(PDO::FETCH_ASSOC);

if (!empty($topCategories)) {
    foreach ($topCategories as $topCategory) {
        $tcat_id = $topCategory['tcat_id'];

        $statement = $pdo->prepare("
            SELECT p.*, m.mcat_name, e.ecat_name 
            FROM tbl_product p
            JOIN tbl_end_category e ON p.ecat_id = e.ecat_id
            JOIN tbl_mid_category m ON e.mcat_id = m.mcat_id
            WHERE m.tcat_id = ?
        ");

        if ($statement->execute([$tcat_id])) {
            $products = $statement->fetchAll(PDO::FETCH_ASSOC);

            $hasFeaturedProducts = false;
            foreach ($products as $product) {
                if ($product['p_is_featured'] == 1) {
                    $hasFeaturedProducts = true;
                    break;
                }
            }

            if ($hasFeaturedProducts) {
                // Create unique identifier for this category's swiper
                $swiperId = 'featured-swiper-' . $tcat_id;
                ?>
                <section id="featured-products-<?php echo $tcat_id; ?>" class="products-carousel my-10">
                    <div class="container-lg overflow-hidden py-5">
                        <div class="section-header d-flex flex-wrap justify-content-between my-4">
                            <h2 class="section-title"><?php echo htmlspecialchars($topCategory['tcat_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            <div class="d-flex align-items-center">
                            <a href="#" class="btn-link text-decoration-none" style="margin-right:20px">View All <?php echo htmlspecialchars($topCategory['tcat_name']) ?> →</a>
                                <div class="swiper-buttons">
                                    <button class="swiper-prev btn btn-primary" id="<?php echo $swiperId; ?>-prev">❮</button>
                                    <button class="swiper-next btn btn-primary" id="<?php echo $swiperId; ?>-next">❯</button>
                                </div>  
                            </div>
                        </div>
                        
                        <div class="swiper" id="<?php echo $swiperId; ?>">
                            <div class="swiper-wrapper">

                                <?php foreach ($products as $product): ?>
                                    <?php if ($product['p_is_featured'] == 1): ?>
                                    <div class="product-item swiper-slide">
                                        <figure>
                                            <a href="product_landing.php?id=<?php echo htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8'); ?>" title="Product Title">
                                                <img src="assets/uploads/<?php echo htmlspecialchars($product['p_featured_photo'], ENT_QUOTES, 'UTF-8'); ?>" width="130px" height="100px" alt="<?php echo htmlspecialchars($product['p_name'], ENT_QUOTES, 'UTF-8'); ?>" class="tab-image">
                                            </a>
                                        </figure>
                                        <div class="d-flex flex-column text-center">
                                        <a href="product_landing.php?id=<?php echo htmlspecialchars($product['id'], ENT_QUOTES, 'UTF-8'); ?>" style="text-decoration:none !important" title="Product Title">
                                          <h3 class="fs-6 fw-normal"><?php echo htmlspecialchars($product['p_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                        </a>
                                            <div>
                                                <span class="rating">
                                                    <svg width="18" height="18" class="text-warning"><use xlink:href="#star-full"></use></svg>
                                                    <svg width="18" height="18" class="text-warning"><use xlink:href="#star-full"></use></svg>
                                                    <svg width="18" height="18" class="text-warning"><use xlink:href="#star-full"></use></svg>
                                                    <svg width="18" height="18" class="text-warning"><use xlink:href="#star-full"></use></svg>
                                                    <svg width="18" height="18" class="text-warning"><use xlink:href="#star-half"></use></svg>
                                                </span>
                                                <span><?php echo $product['p_current_price']?></span>
                                            </div>
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                <?php if (!empty($product['p_old_price'])): ?>
                                                    <del>₹<?php echo number_format($product['p_old_price'], 2); ?></del>
                                                    <span class="text-dark fw-semibold">₹<?php echo number_format($product['p_current_price'], 2); ?></span>
                                                    <div class="cat-product-discount">
                                                        <?php
                                                        $discount = (($product['p_old_price'] - $product['p_current_price']) / $product['p_old_price']) * 100;
                                                        echo round($discount) . '% OFF';
                                                        ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="button-area p-3 pt-0">
                                                <div class="row g-1 mt-2">
                                                    <div class="col-3"></div>
                                                    <div class="col-7" style="margin-left:-12px"><a href="cart.php" class="btn btn-primary rounded-1 p-2 fs-7 btn-cart" style="text-decoration:none !important"><svg width="18" height="18"><use xlink:href="#cart"></use></svg> Add to Cart</a></div>
                                                    <div class="col-2"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>
                <script>
              // Initialize Swiper for this category
              new Swiper('#<?php echo $swiperId; ?>', {
                  slidesPerView: 1,
                  spaceBetween: 10,
                  navigation: {
                      nextEl: '#<?php echo $swiperId; ?>-next',
                      prevEl: '#<?php echo $swiperId; ?>-prev',
                  },
                  watchOverflow: true, // Automatically disable navigation if not enough slides
                  breakpoints: {
                      640: {
                          slidesPerView: 2,
                      },
                      768: {
                          slidesPerView: 3,
                      },
                      1024: {
                          slidesPerView: 4,
                      },
                  },
              });
            </script>

                <?php
            }
        } else {
            error_log("Database query failed: " . implode(", ", $statement->errorInfo()));
        }
    }
}
?>

<!-- Display the Best selling Product -->
<?php require_once('best-selling-homepage.php')?>
<!-- End Display the Best selling Product -->

<!-- Display All Brands  -->
<?php require_once('brands-homepage.php') ?>
<!-- End Display All Brands -->

 <!-- End Display the featured product -->
  <script src="js/jquery-1.11.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
  <script src="js/plugins.js"></script>
  <script src="js/script.js"></script>


  <?php require_once( 'footer.php');?>






