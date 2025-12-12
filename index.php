<?php require_once('header.php');
require_once('track_view.php');
trackPageView('HP', 'Home page');
?>
<link rel="stylesheet" href="/css/index.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
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
                    <a class="category-link" href="search-result.php?type=top-category&slug=<?php echo urlencode($row['tcat_slug']); ?>">
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
                                <a class="subcategory-link" href="search-result.php?type=mid-category&slug=<?php echo urlencode($row1['mcat_slug']); ?>">
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
                                            <a href="search-result.php?type=end-category&slug=<?php echo urlencode($row2['ecat_slug']); ?>">
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

    <!-- quote container -->
    <div class="right-category-pad">
        <div class="quote-container">
            <p class="quote">
                <span class="quote-bold"><?php echo htmlspecialchars($quote_span_text); ?></span>
                <?php echo nl2br(htmlspecialchars($quote_text)); ?>
            </p>
            <img src="assets/uploads/<?php echo $favicon ?>" alt="Logo" class="logo" loading="lazy">
        </div>

<!-- advertisements section -->
<div class="advertisements">
    <?php
    $statement = $pdo->prepare("SELECT a.*, t.tcat_name, t.tcat_slug FROM tbl_advertisements a LEFT JOIN tbl_top_category t ON a.tcat_id = t.tcat_id WHERE a.status=1 ORDER BY a.id DESC LIMIT 3");
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    foreach ($result as $row) {
        $link = 'search-result.php?type=top-category&slug=' . urlencode($row['tcat_slug']);
        ?>
        <div class="ad-column">
            <a href="<?php echo $link; ?>" class="ad-link">
                <div class="ad-container">
                    <img src="assets/uploads/advertisements/<?php echo $row['photo']; ?>" alt="<?php echo $row['tcat_name']; ?>" class="ad-image" loading="lazy">
                    <div class="ad-overlay">
                        <span class="ad-text"><?php echo $row['tcat_name']; ?></span>
                    </div>
                </div>
            </a>
        </div>
        <?php
    }
    ?>
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
                    <img class="img" src="assets/uploads/sliders/<?php echo $row['photo']; ?>" class="d-block w-100" alt="..." style="width:100%">
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

<?php //require_once( 'live-bidding-card.php');
?>

<!-- Live Bidding -->

<!-- Display the product -->
 <?php
// Replace your existing product carousel section with this code

$topCategories = [];
$statement = $pdo->prepare("SELECT * FROM tbl_mid_category");
$statement->execute();
$topCategories = $statement->fetchAll(PDO::FETCH_ASSOC);

if (!empty($topCategories)) {
    foreach ($topCategories as $topCategory) {
        $tcat_id = $topCategory['mcat_id'];

        $statement = $pdo->prepare("
            SELECT p.*, m.mcat_name, e.ecat_name 
            FROM tbl_product p
            LEFT JOIN tbl_end_category e ON p.ecat_id = e.ecat_id
            LEFT JOIN tbl_mid_category m ON p.mcat_id = m.mcat_id
            WHERE p.mcat_id = ?
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
                $swiperId = 'featured-swiper-' . $tcat_id;
?>
                <section id="featured-products-<?php echo $tcat_id; ?>" class="products-section">
                    <div class="products-container">
                        <div class="section-header">
                            <h2 class="section-title"><?php echo htmlspecialchars($topCategory['mcat_name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            <div class="header-actions">
                                <a href="search-result.php?type=mid-category&slug=<?php echo urlencode($topCategory['mcat_slug']) ?>" class="view-all-link">
                                    View All
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                                        <path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                                <div class="nav-controls">
                                    <button class="nav-button" id="<?php echo $swiperId; ?>-prev">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                            <path d="M12 16L6 10L12 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                    <button class="nav-button" id="<?php echo $swiperId; ?>-next">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                                            <path d="M8 4L14 10L8 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="swiper" id="<?php echo $swiperId; ?>">
                            <div class="swiper-wrapper">
                                <?php foreach ($products as $product): ?>
                                    <?php if ($product['p_is_featured'] == 1): ?>
                                        <div class="swiper-slide">
                                            <div class="product-card">
                                                <a href="product/<?php echo urlencode($product['p_slug']); ?>" class="product-link">
                                                    <div class="product-image-box">
                                                        <?php if (!empty($product['p_old_price'])): ?>
                                                            <span class="discount-tag">
                                                                <?php
                                                                $discount = (($product['p_old_price'] - $product['p_current_price']) / $product['p_old_price']) * 100;
                                                                echo round($discount) . '%';
                                                                ?>
                                                            </span>
                                                        <?php endif; ?>
                                                        <img src="assets/uploads/product-photos/<?php echo htmlspecialchars($product['p_featured_photo'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                             alt="<?php echo htmlspecialchars($product['p_name'], ENT_QUOTES, 'UTF-8'); ?>" 
                                                             class="product-img">
                                                    </div>
                                                    <div class="product-info">
                                                        <h3 class="product-name"><?php echo htmlspecialchars($product['p_name'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                                        <div class="product-price">
                                                            <span class="price-current">₹<?php echo number_format($product['p_current_price'], 0); ?></span>
                                                            <?php if (!empty($product['p_old_price'])): ?>
                                                                <span class="price-old">₹<?php echo number_format($product['p_old_price'], 0); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </section>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        new Swiper('#<?php echo $swiperId; ?>', {
                            slidesPerView: 2,
                            spaceBetween: 16,
                            navigation: {
                                nextEl: '#<?php echo $swiperId; ?>-next',
                                prevEl: '#<?php echo $swiperId; ?>-prev',
                            },
                            breakpoints: {
                                640: {
                                    slidesPerView: 3,
                                    spaceBetween: 20,
                                },
                                768: {
                                    slidesPerView: 4,
                                    spaceBetween: 20,
                                },
                                1024: {
                                    slidesPerView: 5,
                                    spaceBetween: 24,
                                },
                                1280: {
                                    slidesPerView: 6,
                                    spaceBetween: 24,
                                },
                            },
                            loop: false,
                            autoplay: {
                                delay: 5000,
                                disableOnInteraction: false,
                                pauseOnMouseEnter: true,
                            },
                        });
                    });
                </script>

                <style>
                    /* Clean Professional Product Carousel */
                    .products-section {
                        padding-bottom: 50px;
                        background: #faf8f3;
                    }

                    .products-container {
                        max-width: 100%;
                        margin: 0 auto;
                        /* padding: 0 20px; */
                    }

                    /* Header */
                    .section-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: 32px;
                        padding-bottom: 16px;
                        border-bottom: 1px solid #e5e5e5;
                    }

                    .section-title {
                        font-size: 28px;
                        font-weight: 600;
                        color: #1a1a1a;
                        margin: 0;
                    }

                    .header-actions {
                        display: flex;
                        align-items: center;
                        gap: 45px;
                    }

                    .view-all-link {
                        display: flex;
                        align-items: center;
                        gap: 6px;
                        color: #b87333;
                        text-decoration: none;
                        font-weight: 500;
                        font-size: 15px;
                        transition: color 0.2s;
                    }

                    .view-all-link:hover {
                        color: #8b5a2b;
                    }

                    .nav-controls {
                        display: flex;
                        gap: 8px;
                    }

                    .nav-button {
                        width: 40px;
                        height: 40px;
                        border-radius: 8px;
                        background: #ffffff;
                        border: 1px solid #d4a574;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        cursor: pointer;
                        transition: all 0.2s;
                        color: #b87333;
                    }

                    .nav-button:hover:not(.swiper-button-disabled) {
                        background: #b87333;
                        border-color: #b87333;
                        color: #ffffff;
                    }

                    .nav-button.swiper-button-disabled {
                        opacity: 0.3;
                        cursor: not-allowed;
                    }

                    .swiper-slide{
                        /* display: flex !important; */
                        /* justify-content: center; */
                        width: 200px !important;
                    }
                    /* Product Card */
                    .product-card {
                        background: #ffffff;
                        border-radius: 12px;
                        overflow: hidden;
                        transition: all 0.3s;
                        border: 1px solid #e5e5e5;
                        height: 100%;
                        width: 200px !important;
                        /* margin-right:5px !important; */
                    }

                    .product-card:hover {
                        box-shadow: 0 4px 12px rgba(184, 115, 51, 0.12);
                        border-color: #d4a574;
                        transform: translateY(-4px);
                    }

                    .product-link {
                        text-decoration: none;
                        color: inherit;
                        display: block;
                    }

                    .product-image-box {
                        position: relative;
                        width: 100%;
                        padding-top: 75%;
                        background: #ffffffff;
                        overflow: hidden;
                    }

                    .product-img {
                        position: absolute;
                        top: 10%;
                        left: 10%;
                        width: 80%;
                        height: 80%;
                        object-fit: contain;
                        transition: transform 0.3s;
                    }

                    .product-card:hover .product-img {
                        transform: scale(1.05);
                    }

                    .discount-tag {
                        position: absolute;
                        top: 8px;
                        left: 8px;
                        background: #8b5a2b;
                        color: #ffffff;
                        padding: 3px 8px;
                        border-radius: 4px;
                        font-size: 11px;
                        font-weight: 600;
                        z-index: 1;
                    }

                    .product-info {
                        padding: 12px;
                    }

                    .product-name {
                        font-size: 14px;
                        font-weight: 500;
                        color: #1a1a1a;
                        margin: 0 0 8px 0;
                        line-height: 1.3;
                        display: -webkit-box;
                        -webkit-line-clamp: 2;
                        -webkit-box-orient: vertical;
                        overflow: hidden;
                        min-height: 36px;
                    }

                    .product-price {
                        display: flex;
                        align-items: center;
                        gap: 6px;
                    }

                    .price-current {
                        font-size: 18px;
                        font-weight: 600;
                        color: #b87333;
                    }

                    .price-old {
                        font-size: 13px;
                        color: #999;
                        text-decoration: line-through;
                    }

                    /* Responsive */
                    @media (max-width: 768px) {
                        .products-section {
                            padding: 40px 0;
                        }

                        .swiper-slide{
                            /* display: flex !important; */
                            /* justify-content: center; */
                            width: 180px !important;
                        }
                        .section-header {
                            flex-wrap: wrap;
                            gap: 16px;
                        }

                        .section-title {
                            font-size: 24px;
                            width: 100%;
                        }

                        .nav-controls {
                            display: none;
                        }

                        .product-name {
                            font-size: 14px;
                            min-height: 40px;
                        }

                        .price-current {
                            font-size: 18px;
                        }

                        .price-old {
                            font-size: 14px;
                        }
                    }

                    @media (max-width: 480px) {
                        .products-container {
                            padding: 0 16px;
                        }

                        .section-title {
                            font-size: 20px;
                        }

                        .product-info {
                            padding: 12px;
                        }

                        .discount-tag {
                            font-size: 11px;
                            padding: 3px 8px;
                        }
                    }
                </style>
<?php
            }
        }
    }
}
?>



<!-- End Display the product -->

<!-- Display the Best selling Product -->
<?php //require_once('best-selling-homepage.php')
?>
<!-- End Display the Best selling Product -->

<!-- Display All Brands  -->
<?php //require_once('brands-homepage.php') 
?>
<!-- End Display All Brands -->

<!-- Display the Featured Products -->
<?php //require_once('featured-product.php') 
?>
<!-- End Display the Featured Products -->

<script src="js/jquery-1.11.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>
<script src="/js/plugins.js"></script>


<script src="/js/script.js"></script>

<style>
    .quote-container .quote {
        font-size: 42px;
        /* Slightly smaller quote size */
        line-height: 1.4;
        /* Adjust line height for better spacing */
    }

    .quote-container .quote .quote-bold {
        font-size: 45px;
        /* Bold "Buy" text slightly larger */
    }
</style>


<?php require_once('footer.php'); ?>