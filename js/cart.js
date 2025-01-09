document.addEventListener('DOMContentLoaded', function () {
    // Auto-update quantity and price
    const quantityForms = document.querySelectorAll('.quantity-form');

    quantityForms.forEach(form => {
        const minusBtn = form.querySelector('.minus');
        const plusBtn = form.querySelector('.plus');
        const input = form.querySelector('.quantity-input');
        const itemId = input.dataset.itemId;
        const price = parseFloat(form.dataset.price);
        const totalElement = form.closest('.cart-item').querySelector('.item-total');

        function updateQuantity(newValue) {
            const formData = new FormData();
            formData.append('cart_id', itemId);
            formData.append('cart_quantity', newValue);
            formData.append('update_cart', '1');

            // Update total price immediately
            const newTotal = (price * newValue).toFixed(2);
            totalElement.textContent = newTotal;

            // Send AJAX request to update database
            fetch('cart.php', {
                method: 'POST',
                body: formData
            }).catch(error => console.error('Error:', error));
        }

        minusBtn.addEventListener('click', () => {
            const currentValue = parseInt(input.value);
            if (currentValue > 1) {
                input.value = currentValue;
                updateQuantity(input.value);
            }
        });

        plusBtn.addEventListener('click', () => {
            const currentValue = parseInt(input.value);
            if (currentValue < 99) {
                input.value = currentValue;
                updateQuantity(input.value);
            }
        });

        input.addEventListener('change', () => {
            let value = parseInt(input.value);
            if (isNaN(value) || value < 1) {
                value = 1;
            } else if (value > 99) {
                value = 99;
            }
            input.value = value;
            updateQuantity(value);
        });
    });

    // Bid button functionality
    const bidButtons = document.querySelectorAll('.bid-btn');
    bidButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            // Add your bid logic here
            alert('Bidding functionality will be implemented here');
        });
    });

    const promoInput = document.querySelector('.promo-code input');
    const promoButton = document.querySelector('.promo-code button');

    promoButton.addEventListener('click', () => {
        const code = promoInput.value.trim();
        if (code) {
            // Add your promo code validation logic here
            alert('Promo code applied: ' + code);
        }
    });

    // Smooth Scroll for Checkout
    const checkoutBtn = document.querySelector('.checkout-btn');

    checkoutBtn.addEventListener('click', (e) => {
        e.preventDefault();
        // Add your checkout logic here
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // Cart Item Hover Effect
    const cartItems = document.querySelectorAll('.cart-item');

    cartItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            item.style.transform = 'translateX(5px)';
        });

        item.addEventListener('mouseleave', () => {
            item.style.transform = 'translateX(0)';
        });
    });

    // Remove Item Confirmation
    const removeButtons = document.querySelectorAll('.remove-item');

    removeButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (!confirm('Are you sure you want to remove this item?')) {
                e.preventDefault();
            }
        });
    });

    // Sticky Order Summary
    const orderSummary = document.querySelector('.order-summary');
    if (orderSummary) {
        const originalTop = orderSummary.offsetTop;

        window.addEventListener('scroll', () => {
            if (window.pageYOffset > originalTop) {
                orderSummary.classList.add('sticky-top');
            } else {
                orderSummary.classList.remove('sticky-top');
            }
        });
    }
});
// cart.js
$(document).ready(function () {
    // Helper function to format currency
    function formatCurrency(amount) {
        return '₹' + parseFloat(amount).toFixed(2);
    }

    // Function to update item total
    function updateItemTotal($item) {
        const quantity = parseInt($item.find('.quantity-input').val());
        const price = parseFloat($item.find('.quantity-form').data('price'));
        const oldPrice = parseFloat($item.find('.old-price').text().replace('₹', '')) || price;

        // Calculate totals
        const total = quantity * price;
        const savings = quantity * (oldPrice - price);

        // Update item total
        $item.find('.item-total').text(total);

        // Update item savings if applicable
        if (savings > 0) {
            $item.find('.item-savings').html('You save: ' + formatCurrency(savings));
        }

        // Update cart totals
        updateCartTotals();
    }

    // Function to update all cart totals
    function updateCartTotals() {
        let grandTotal = 0;
        let totalSavings = 0;
        const shippingCost = 14; // Your fixed shipping cost

        // Calculate totals from all items
        $('.cart-item').each(function () {
            const $item = $(this);
            const quantity = parseInt($item.find('.quantity-input').val());
            const price = parseFloat($item.find('.quantity-form').data('price'));
            const oldPrice = parseFloat($item.find('.old-price').text().replace('₹', '')) || price;

            grandTotal += quantity * price;
            totalSavings += quantity * (oldPrice - price);
        });

        // Update summary sections
        $('.summary-item .amount:eq(0)').text(formatCurrency(grandTotal)); // Subtotal

        // Update total savings if exists
        if (totalSavings > 0) {
            $('.summary-item.text-success .amount').text('-' + formatCurrency(totalSavings));
            $('.summary-item.text-success').show();
        } else {
            $('.summary-item.text-success').hide();
        }

        // Update final total
        $('.total-amount .amount').text(formatCurrency(grandTotal + shippingCost));
    }

    // Handle quantity button clicks
    $('.quantity-btn').on('click', function () {
        const $input = $(this).closest('.input-group').find('.quantity-input');
        const currentVal = parseInt($input.val());

        if ($(this).hasClass('minus')) {
            if (currentVal > 1) {
                $input.val(currentVal - 1);
            }
        } else {
            if (currentVal < 99) {
                $input.val(currentVal + 1);
            }
        }

        // Trigger update for this item
        updateItemTotal($(this).closest('.cart-item'));

        // Auto-submit the form after a short delay
        clearTimeout($(this).data('timeout'));
        const $form = $(this).closest('form');
        const timeout = setTimeout(() => {
            $form.submit();
        }, 500);
        $(this).data('timeout', timeout);
    });

    // Handle direct input changes
    $('.quantity-input').on('change', function () {
        const $input = $(this);
        let value = parseInt($input.val());

        // Validate input
        if (isNaN(value) || value < 1) {
            value = 1;
        } else if (value > 99) {
            value = 99;
        }

        $input.val(value);

        // Update totals
        updateItemTotal($(this).closest('.cart-item'));

        // Auto-submit the form
        $(this).closest('form').submit();
    });

    // Handle form submission via AJAX
    $('.quantity-form').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                // You can handle any server response here if needed
            }
        });
    });

    // Initialize totals on page load
    updateCartTotals();
});
$(document).ready(function () {
    // Update quantity
    $(".quantity-btn").on("click", function () {
        const input = $(this).siblings(".quantity-input");
        const currentQuantity = parseInt(input.val());
        const price = parseFloat(input.data("price"));
        const oldPrice = parseFloat(input.data("old-price")); // Add old price for savings calculation
        const itemId = input.data("id");

        let newQuantity = currentQuantity;

        if ($(this).hasClass("plus")) {
            newQuantity += 1;
        } else if ($(this).hasClass("minus") && currentQuantity > 1) {
            newQuantity -= 1;
        }

        input.val(newQuantity);

        // Update item total
        const newTotal = (price * newQuantity).toFixed(2);
        input.closest(".cart-item").find(".item-total").text(`₹${newTotal}`);

        // Update item savings
        const savings = ((oldPrice - price) * newQuantity).toFixed(2);
        const savingsElement = input.closest(".cart-item").find(".item-savings");
        if (savings > 0) {
            savingsElement.text(`You save: ₹${savings}`).show();
        } else {
            savingsElement.hide();
        }

        // Update grand total and total savings
        updateSummary();
    });

    // Function to update grand total and total savings
    function updateSummary() {
        let grandTotal = 0;
        let totalSavings = 0;

        $(".cart-item").each(function () {
            const quantity = parseInt($(this).find(".quantity-input").val());
            const price = parseFloat($(this).find(".quantity-input").data("price"));
            const oldPrice = parseFloat($(this).find(".quantity-input").data("old-price"));

            grandTotal += quantity * price;
            totalSavings += (oldPrice - price) * quantity;
        });

        $(".grand-total").text(`₹${grandTotal.toFixed(2)}`);
        $(".total-savings").text(`₹${totalSavings.toFixed(2)}`).toggle(totalSavings > 0);
    }
});


