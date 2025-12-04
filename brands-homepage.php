<style>
    .brand-logo {
        width: 161px;
        height: 160px;
        object-fit: contain;
        /* Changed from cover to contain for clarity */
        padding: 5px;
        /* Optional: Adds padding inside the border for spacing */
        background-color: #fff;
        /* Optional: Ensures a white background if images have transparency */
    }

    .section-title {
        margin-top: -10px
    }
</style>

<section class="py-5 overflow-hidden">
    <div class="container-lg">
        <div class="row">
            <div class="col-md-12">
                <div class="section-header d-flex flex-wrap justify-content-between mb-5">
                    <h2 class="section-title">Brands</h2>
                    <div class="d-flex align-items-center">
                        <a href="#" class="btn-link text-decoration-none" style="margin-right:20px">View All Brands →</a>
                        <div class="swiper-buttons">
                            <button class="swiper-prev btn btn-primary" id="brand-swiper-prev">❮</button>
                            <button class="swiper-next btn btn-primary" id="brand-swiper-next">❯</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="category-carousel swiper" id="brand-swiper">
                    <div class="swiper-wrapper">
                        <?php
                        $i = 0;
                        // Fetch data from tbl_brands
                        $statement = $pdo->prepare("SELECT brand_id, brand_name, brand_logo FROM tbl_brands");
                        $statement->execute();
                        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
                        foreach ($result as $row) {
                            $i++;
                        ?>
                            <a href="category.html" class="nav-link swiper-slide text-center" style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                <img src="assets/uploads/brand-logos/<?php echo $row['brand_logo']; ?>"
                                    class=" brand-logo"
                                    alt="<?php echo $row['brand_name']; ?>">
                                <h4 class="fs-6 mt-3 fw-normal category-title"><?php echo $row['brand_name'] ?></h4>
                            </a>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    // Initialize Swiper
    const brandSwiper = new Swiper('#brand-swiper', {
        slidesPerView: 1,
        spaceBetween: 10,
        loop: false, // Set to false if you want to handle end navigation
        navigation: {
            nextEl: '#brand-swiper-next',
            prevEl: '#brand-swiper-prev',
        },
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

    // Get navigation buttons
    const prevButton = document.querySelector('#brand-swiper-prev');
    const nextButton = document.querySelector('#brand-swiper-next');

    // Initially disable the "Prev" button
    prevButton.disabled = true;

    // Add event listener for slide change
    brandSwiper.on('slideChange', () => {
        // Enable/disable Prev button
        prevButton.disabled = brandSwiper.isBeginning;

        // Enable/disable Next button
        nextButton.disabled = brandSwiper.isEnd;
    });
</script>




<!-- Without using loop -->
<!-- <script>
    // Swiper configuration
    const brandSwiper = new Swiper('#brand-swiper', {
        slidesPerView: 1,
        spaceBetween: 10,
        navigation: {
            nextEl: '#brand-swiper-next',
            prevEl: '#brand-swiper-prev',
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

    // Function to update button states
    function updateNavigationButtons(swiper) {
        const { isBeginning, isEnd } = swiper;
        const prevButton = document.querySelector('#brand-swiper-prev');
        const nextButton = document.querySelector('#brand-swiper-next');

        // Disable or enable buttons based on swiper state
        prevButton.disabled = isBeginning;
        nextButton.disabled = isEnd;
    }

    // Initial button state update
    updateNavigationButtons(brandSwiper);

    // Attach event listener to update buttons on slide change
    brandSwiper.on('slideChange', () => {
        updateNavigationButtons(brandSwiper);
    });
</script> -->