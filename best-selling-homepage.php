<style>
/* Container */
.product-section {
    padding: 40px;
    text-align: center;
    font-family: Arial, sans-serif;
}

.section-title-bs {
    font-size: 2em;
    margin-bottom: 70px; /* Increased spacing below the title */
    text-transform: uppercase;
}

/* Product Grid */
.product-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* Display four cards in a row */
    gap: 20px;
    margin: 0 auto;
    max-width: 1100px; /* Optional: limit the overall grid width */
}

/* Product Card */
.product-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border: 1px solid #ddd;
    padding: 10px;
    max-width: 250px; /* Limit the max width of each card */
    background-color: #fff;
    text-align: center;
    transition: transform 0.3s;
    margin: 2 auto; /* Center each card within its grid cell */
    height: 300px; /* Fixed height for each card */
}

.product-card:hover {
    transform: scale(1.05);
}

/* Product Image Container */
.product-img {
    width: 100%;
    height: 200px; /* Set a fixed height for images */
    overflow: hidden; /* Hide any overflow if the image exceeds the container */
    position: relative; /* For absolute positioning of the image */
}

/* Product Image */
.product-img img {
    height: 100%; /* Fill the height of the container */
    object-fit: cover; /* Ensures the image covers the container area */
}

/* Product Description */
.product-desc h3 {
    font-size: 1em;
    margin: 10px 0;
    color: #333;
}

.product-price {
    font-size: 1.1em;
    color: #777;
    margin-top: auto; /* Ensure the price stays at the bottom of the card */
}

/* Shop All Button */
.shop-all {
    margin-top: 80px;
}

.shop-button {
    padding: 10px 20px;
    font-size: 1.1em;
    color: black;
    width: 190px;
    height: 60px;
    text-decoration: none;
    border-radius: 50px;
    transition: background-color 0.3s;
}

.shop-button:hover {
    background-color: #555;
}


</style>



<!-- Best sellers product -->

<div class="product-section">
    <div class="container">
        <h2 class="section-title-bs">Best Sellers</h2>
        <div class="product-grid">
            <div class="product-card">
                <a href="#" class="product-img">
                    <img src="assets/uploads/product-featured-147.png" alt="">
                </a>
                <div class="product-desc">
                    <h3>Impact pneumatic tools</h3>
                    <span class="product-price">₹769</span>
                </div>
            </div>
          
            <div class="product-card">
                <a href="#" class="product-img">
                    <img src="assets/uploads/product-featured-148.png" alt="">
                </a>
                <div class="product-desc">
                    <h3>Cutting Tools</h3>
                    <span class="product-price">₹509</span>
                </div>
            </div>
            <div class="product-card">
                <a href="#" class="product-img">
                    <img src="assets/uploads/product-featured-159.png" alt="">
                </a>
                <div class="product-desc">
                    <h3>Impact pneumatic tools</h3>
                    <span class="product-price">₹769</span>
                </div>
            </div>
          
            <div class="product-card">
                <a href="#" class="product-img">
                    <img src="assets/uploads/product-featured-148.png" alt="">
                </a>
                <div class="product-desc">
                    <h3>Cutting Tools</h3>
                    <span class="product-price">₹509</span>
                </div>
            </div>
            <div class="product-card">
                <a href="#" class="product-img">
                    <img src="assets/uploads/product-featured-147.png" alt="">
                </a>
                <div class="product-desc">
                    <h3>Impact pneumatic tools</h3>
                    <span class="product-price">₹769</span>
                </div>
            </div>
          
            <div class="product-card">
                <a href="#" class="product-img">
                    <img src="assets/uploads/product-featured-159.png" alt="">
                </a>
                <div class="product-desc">
                    <h3>Cutting Tools</h3>
                    <span class="product-price">₹509</span>
                </div>
            </div>
            <div class="product-card">
                <a href="#" class="product-img">
                    <img src="assets/uploads/product-featured-143.png" alt="">
                </a>
                <div class="product-desc">
                    <h3>Impact pneumatic tools</h3>
                    <span class="product-price">₹769</span>
                </div>
            </div>
            <div class="product-card">
                <a href="#" class="product-img">
                    <img src="assets/uploads/product-featured-144.png" alt="">
                </a>
                <div class="product-desc">
                    <h3>Impact pneumatic tools</h3>
                    <span class="product-price">₹769</span>
                </div>
            </div>
          
          
            <div class="product-card">
                <a href="#" class="product-img">
                    <img src="assets/uploads/product-featured-148.png" alt="">
                </a>
                <div class="product-desc">
                    <h3>Cutting Tools</h3>
                    <span class="product-price">₹509</span>
                </div>
            </div>
      
            <div class="product-card">
                <a href="#" class="product-img">
                    <img src="assets/uploads/product-featured-148.png" alt="">
                </a>
                <div class="product-desc">
                    <h3>Cutting Tools</h3>
                    <span class="product-price">₹509</span>
                </div>
            </div>
            <div class="product-card">
                <a href="#" class="product-img">
                    <img src="assets/uploads/product-featured-147.png" alt="">
                </a>
                <div class="product-desc">
                    <h3>Impact pneumatic tools</h3>
                    <span class="product-price">₹769</span>
                </div>
            </div>
          
            <div class="product-card">
                <a href="#" class="product-img">
                    <img src="assets/uploads/product-featured-159.png" alt="">
                </a>
                <div class="product-desc">
                    <h3>Cutting Tools</h3>
                    <span class="product-price">₹509</span>
                </div>
            </div>
        </div>
        <div class="shop-all">
        <button type="button" class="seller-btn btn btn-outline-secondary shop-button">View All Products</button>
        </div>
    </div>
</div>

 <!-- End Best sellers product -->
