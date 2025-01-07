<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <style>
            body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            /* background-color: #f5f5f5; */
            }
            .featured{
                margin-top:90px

            }
            .container {
            width: 90%;
            margin: 0 auto;
            }





            .section-title-fp {
                margin-bottom: 60px;
	            text-align: center;
            }

            .section-title-fp h2 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 10px;
           
            color: #1c1c1c;
            font-weight: 700;
            position: relative;
            }

            .featured__control ul {
                display: flex;
                justify-content: center;
                list-style: none;
                padding: 0;
                margin: 20px 0;
            }

            .featured__control li {
                margin: 0 10px;
                padding: 10px 20px;
                cursor: pointer;
                font-size: 1rem;
                border: 2px solid #ddd;
                border-radius: 30px;
                transition: all 0.4s ease;
            }

            .featured__control li.active,
            .featured__control li:hover {
            background-color: #333;
            color: #fff;
            border-color: #333;
            }

            .featured__items {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 80px;
            margin-bottom: 50px;



            }


            .item {
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                border: 1px solid #ddd;
                padding: 10px;
                max-width: 250px;
                background-color: #fff;
                text-align: center;
                transition: transform 0.3s;
                margin: 2 auto;
                height: 300px;

            }
            .item:hover {
                transform: scale(1.05);
            }
            .fp-image {
                width: 100%;
                height: 200px;
                overflow: hidden;
                position: relative;
            }

            .item img {
                height: 100%;
                object-fit: cover;  
            }
            .product-price{
                font-size: 1.1em;
                color: #777;
                margin-top: auto;
            }
            .product-desc h6 {
            
            font-size: 1em;
            margin: 10px 0;
            color: #333;
            }
            .item h6 {
            font-size: 1.2rem;
            color: #333;
            margin: 10px 0 5px;
            }

            .item p {
            font-size: 1rem;
            color: #777;
            }

            .item.hidden {
            opacity: 0;
            transform: scale(0.8);
            pointer-events: none;
            position: absolute;
            }

            .item:not(.hidden) {
            opacity: 1;
            transform: scale(1);
            }

    </style>

<?php

$topCategories = [];
$statement = $pdo->prepare("SELECT * FROM tbl_top_category");
$statement->execute();
$topCategories = $statement->fetchAll(PDO::FETCH_ASSOC);

if (!empty($topCategories)) {
    // Fetch all featured products across all top categories
    $allProducts = [];
    $statementAll = $pdo->prepare("
        SELECT p.*, m.mcat_name, e.ecat_name, t.tcat_name, t.tcat_id
        FROM tbl_product p
        JOIN tbl_end_category e ON p.ecat_id = e.ecat_id
        JOIN tbl_mid_category m ON e.mcat_id = m.mcat_id
        JOIN tbl_top_category t ON m.tcat_id = t.tcat_id
        WHERE p.p_is_featured = 1
    ");
    $statementAll->execute();
    $allProducts = $statementAll->fetchAll(PDO::FETCH_ASSOC);

    // Filter categories that have featured products
    $categoriesWithProducts = [];
    foreach ($topCategories as $topCategory) {
        $tcat_id = $topCategory['tcat_id'];
        $statementCategory = $pdo->prepare("
            SELECT p.id
            FROM tbl_product p
            JOIN tbl_end_category e ON p.ecat_id = e.ecat_id
            JOIN tbl_mid_category m ON e.mcat_id = m.mcat_id
            WHERE m.tcat_id = ? AND p.p_is_featured = 1
        ");
        $statementCategory->execute([$tcat_id]);
        if ($statementCategory->fetch(PDO::FETCH_ASSOC)) {
            $categoriesWithProducts[] = $topCategory; // Add category only if it has products
        }
    }

    if (!empty($allProducts)) {
        ?>
        <section class="featured">
            <div class="container">
                <div class="section-title-fp">
                    <h2>Featured Products</h2>
                </div>
                <div class="featured__control">
                    <ul>
                        <li class="active" data-filter="all">All</li>
                        <?php foreach ($categoriesWithProducts as $topCategory): ?>
                            <li data-filter="<?php echo htmlspecialchars($topCategory['tcat_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <?php echo htmlspecialchars($topCategory['tcat_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="row featured__items">
                    <!-- Display all products initially -->
                    <?php foreach ($allProducts as $product): ?>
                        <div class="item <?php echo htmlspecialchars($product['tcat_id'], ENT_QUOTES, 'UTF-8'); ?>" data-filter="<?php echo htmlspecialchars($product['tcat_id'], ENT_QUOTES, 'UTF-8'); ?>">
                            <a href="#" class="fp-image">
                            <img 
                                src="<?php 
                                    $imagePath = 'assets/uploads/' . htmlspecialchars($product['p_featured_photo'], ENT_QUOTES, 'UTF-8');
                                    echo (file_exists($imagePath) && !empty($product['p_featured_photo'])) 
                                        ? $imagePath 
                                        : './assets/uploads/logo.png'; 
                                ?>" 
                                width="130px" 
                                height="100px" 
                                alt="<?php echo htmlspecialchars($product['p_name'], ENT_QUOTES, 'UTF-8'); ?>" 
                                class="tab-image">                            </a>
                            <div class="product-desc">
                                <h6><?php echo htmlspecialchars($product['p_name'], ENT_QUOTES, 'UTF-8'); ?></h6>
                                <p class="product-price">₹<?php echo number_format($product['p_current_price']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <?php
    }
}
?>
    <script src="https://cdn.jsdelivr.net/npm/mixitup/dist/mixitup.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
    const controls = document.querySelectorAll('.featured__control li');
    const items = document.querySelectorAll('.item');

// Animation settings
const animationConfig = {
    duration: 300,
    nudge: true,
    reverseOut: true,
    effects: 'fade scale(0.01)'
};

// Event listener for controls
controls.forEach(control => {
    control.addEventListener('click', () => {
        // Remove active class from all controls
        controls.forEach(ctrl => ctrl.classList.remove('active'));
        control.classList.add('active');

        // Determine filter or sort action
        const filter = control.dataset.filter;
        const sort = control.dataset.sort;

        // Apply filter or sort based on button type
        if (filter) {
            applyFilter(filter);
        }

        if (sort) {
            applySort(sort);
        }
    });
});

// Function to filter items
function applyFilter(filter) {
    items.forEach(item => {
        if (filter === 'all' || item.classList.contains(filter)) {
            // Animate item in
            item.style.transition = `opacity ${animationConfig.duration}ms ease, transform ${animationConfig.duration}ms ease`;
            item.style.opacity = '1';
            item.style.transform = 'scale(1)';
            setTimeout(() => {
                item.classList.remove('hidden');
                item.style.position = 'relative';
            }, animationConfig.duration);
        } else {
            // Animate item out
            item.style.transition = `opacity ${animationConfig.duration}ms ease, transform ${animationConfig.duration}ms ease`;
            item.style.opacity = '0';
            item.style.transform = animationConfig.reverseOut ? 'scale(0.01)' : 'scale(1)';
            setTimeout(() => {
                item.classList.add('hidden');
                item.style.position = 'absolute';
            }, animationConfig.duration);
        }
    });
}




});

    </script>
</body>
</html>
