<?php
?>
<link rel="stylesheet" href="/deadstock/css/footer.css">

<footer class="text-center text-lg-start bg-light text-muted">
    <section class="d-flex justify-content-center justify-content-lg-between p-4 border-bottom"></section>

    <section>
        <div class="container text-center text-md-start mt-5">
            <div class="row mt-3">
                <div class="col-md-3 col-lg-4 col-xl-3 mx-auto mb-4">
                    <h6 class="text-uppercase fw-bold mb-4">Dead Stock</h6>
                    <p>Sell and buy unused industrial products easily.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="text-center p-4" style="background-color: rgba(0, 0, 0, 0.05);">
        © 2025 Dead Stock.
    </div>
</footer>

<!-- Fixed: Removed infinite AJAX polling loop -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    $.ajax({
        url: "/deadstock/product/cart_count.php",
        type: "GET",
        success: function (data) {
            $("#cartCount").text(data);
        }
    });
});
</script>

</div>
</body>
</html>
