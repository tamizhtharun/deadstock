<?php
// OPTIMIZED VERSION - Performance optimizations applied

// Single query to fetch all featured products (avoids N+1 problem)
$statement = $pdo->prepare("
    SELECT p.*, m.mcat_name, m.mcat_slug, m.mcat_id, e.ecat_name 
    FROM tbl_product p
    LEFT JOIN tbl_end_category e ON p.ecat_id = e.ecat_id
    LEFT JOIN tbl_mid_category m ON p.mcat_id = m.mcat_id
    WHERE p.p_is_featured = 1 AND m.mcat_id IS NOT NULL
    ORDER BY m.mcat_id, p.p_id
");
$statement->execute();
$allFeaturedProducts = $statement->fetchAll(PDO::FETCH_ASSOC);

// Group products by category in PHP
$productsByCategory = [];
$categoryInfo = [];
foreach ($allFeaturedProducts as $product) {
    $mcat_id = $product['mcat_id'];
    if (!isset($productsByCategory[$mcat_id])) {
        $productsByCategory[$mcat_id] = [];
        $categoryInfo[$mcat_id] = [
            'mcat_name' => $product['mcat_name'],
            'mcat_slug' => $product['mcat_slug']
        ];
    }
    $productsByCategory[$mcat_id][] = $product;
}

// Display products by category
foreach ($productsByCategory as $mcat_id => $products) {
    $swiperId = 'featured-swiper-' . $mcat_id;
    $topCategory = $categoryInfo[$mcat_id];
?>
                <section id="featured-products-<?php echo $mcat_id; ?>" class="products-section">
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
                                                             class="product-img"
                                                             loading="lazy"
                                                             width="300"
                                                             height="225"
                                                             decoding="async">
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
                        padding: 0 20px;
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

                    /* Product Card */
                    .product-card {
                        background: #ffffff;
                        border-radius: 12px;
                        overflow: hidden;
                        transition: all 0.3s;
                        border: 1px solid #e5e5e5;
                        height: 100%;
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
?>
