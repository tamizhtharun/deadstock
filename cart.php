<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shopping Cart</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
 
</head>
<body class="bg-light my-5">
  <!-- Cart + Summary -->
  <section class="bg-light my-5">
    <div class="container">
      <div class="row">
        <!-- Cart -->
        <div class="col-lg-9">
          <div class="card border shadow-sm cart-container">
            <div class="card-body">
              <h4 class="card-title mb-4">Your shopping cart</h4>

              <!-- Cart item -->
              <div class="row gy-3 mb-4">
                <div class="col-lg-5">
                  <div class="d-flex">
                    <img src="http://172.1.25.196/deadstock/icons/index%20milling.png" class="border rounded me-5 product-image" alt="Product" >
                    <div>
                      <a href="#" class="nav-link">Winter jacket for men and lady</a>
                      <p class="text-muted">Yellow, Jeans</p>
                      <div class="quantity-control">
                        <button id="decrease" class="btn btn-outline-secondary quantity-btn">-</button>
                        <input type="number" id="quantity" class="form-control quantity-input" value="1" min="1" readonly>
                        <button id="increase" class="btn btn-outline-secondary quantity-btn">+</button>
                      </div>
                      <div class="delivery-badge">
                        <i class="fas fa-truck me-2"></i>Delivery: Nov 15-18 (Free Shipping)
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-4 col-sm-6 col-6 d-flex flex-row flex-lg-column flex-xl-row text-nowrap">
                  <div style="margin-left:90px">
                    <span class="h6">₹1156.00</span><br>
                    <small class="text-muted">₹460.00 / per item</small><br>
                    <small class="text-danger fw-bold">Limited Time Deal</small> <!-- "Limited Time Deal" line --><br>

                    <small class="text-success">In Stock</small> <!-- New "In Stock" line -->
                  </div>
                </div>
                
                <div class="col-lg-2 text-end">
                  <a href="#" class="btn btn-light border text-danger">Remove</a>
                  <a href="#" class="btn btn-light border text-danger"style="margin-left:px"> Bid</a>
                </div>
              </div>

              <!-- Add more cart items here if needed -->

              
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div class="col-lg-3">
          <div class="summary-fixed">
            <!-- Order summary -->
            <div class="card border shadow-sm " style="width:350px; margin-left: 25px">
              <div class="card-body">
                <h5 class="mb-3">Price Details</h5> <!-- Added header for price details -->
                <div class="d-flex justify-content-between mb-2">
                  <span>Total price:</span>
                  <span>₹329.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span>Discount:</span>
                  <span class="text-success">-₹60.00</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                  <span>Shipping:</span>
                  <span>₹14.00</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between mb-3">
                  <span>Total price:</span>
                  <strong>₹283.00</strong>
                </div>

                <a href="#" class="btn btn-success w-100 mb-2">PROCEED TO CHECKOUT</a>
                <a href="#" class="btn btn-light w-100 border">Back to shop</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Recommended Products -->
  <section>
    <div class="container my-5">
      <header class="mb-4">
        <h3>Related Product</h3>
      </header>

      <div class="row">
        <!-- Product Card -->
        <!-- You can duplicate these cards for additional products -->
        <div class="col-lg-3 col-md-6 col-sm-6">
          <div class="card px-4 border shadow-0 mb-4 mb-lg-0">
            <div class="mask px-2" style="height: 50px;">
              <div class="d-flex justify-content-between">
                <h6><span class="badge bg-danger pt-1 mt-3 ms-2">New</span></h6>
                <a href="#"><i class="fas fa-heart text-primary fa-lg float-end pt-3 m-2"></i></a>
              </div>
            </div>
            <a href="#" class="">
              <img src="https://mdbootstrap.com/img/bootstrap-ecommerce/items/7.webp" class="card-img-top rounded-2" />
            </a>
            <div class="card-body d-flex flex-column pt-3 border-top">
              <a href="#" class="nav-link">Gaming Headset with Mic</a>
              <div class="price-wrap mb-2">
                <strong class="">₹18.95</strong>
                <del class="">₹24.99</del>
              </div>
              <div class="card-footer d-flex align-items-end pt-3 px-0 pb-0 mt-auto">
                <a href="#" class="btn btn-outline-primary w-100">Add to cart</a>
              </div>
            </div>
          </div>
        </div>
        <!-- Add more product cards as needed -->
      </div>
    </div>
  </section>

  <script>
    const quantityInput = document.getElementById('quantity');
    const increaseButton = document.getElementById('increase');
    const decreaseButton = document.getElementById('decrease');

    increaseButton.addEventListener('click', () => {
        quantityInput.value = parseInt(quantityInput.value) + 1;
    });

    decreaseButton.addEventListener('click', () => {
        if (quantityInput.value > 1) {
            quantityInput.value = parseInt(quantityInput.value) - 1;
        }
    });
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>

