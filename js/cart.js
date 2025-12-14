document.addEventListener('DOMContentLoaded', function () {
    // Function to update all cart totals dynamically
    function updateCartTotals() {
        let grandTotal = 0;
        let totalSavings = 0;
        let totalGst = 0;

        // Calculate totals from all cart items
        document.querySelectorAll('.cart-item').forEach(item => {
            const quantityInput = item.querySelector('.quantity-input');
            const quantity = parseInt(quantityInput.value) || 1;
            const form = item.querySelector('.quantity-form');
            const price = parseFloat(form.dataset.price);

            // Get GST percentage and old price from the item data
            const gstPercentage = parseFloat(item.dataset.gst) || 0;
            const oldPrice = parseFloat(item.dataset.oldPrice) || price;

            // Calculate item totals
            const subtotal = price * quantity;
            const itemGst = subtotal * (gstPercentage / 100);
            const itemTotal = subtotal + itemGst;
            const savings = (oldPrice - price) * quantity;

            // Update item display
            const totalPriceElement = item.querySelector('.total-price');
            if (totalPriceElement) {
                totalPriceElement.innerHTML = `
                    Subtotal: ₹${subtotal.toFixed(2)}<br>
                    <small class="text-muted">GST (${gstPercentage}%): ₹${itemGst.toFixed(2)}</small><br>
                    <strong>Total: ₹${itemTotal.toFixed(2)}</strong>
                `;
            }

            // Update item savings display
            const savingsElement = item.querySelector('.item-savings');
            if (savingsElement) {
                if (savings > 0) {
                    savingsElement.textContent = `You save: ₹${savings.toFixed(2)}`;
                    savingsElement.style.display = 'block';
                } else {
                    savingsElement.style.display = 'none';
                }
            }

            // Add to grand totals
            grandTotal += subtotal;
            totalGst += itemGst;
            totalSavings += savings;
        });

        // Update order summary
        const summaryItems = document.querySelectorAll('.summary-item');
        if (summaryItems.length > 0) {
            // Update Subtotal
            summaryItems[0].querySelector('.amount').textContent = `₹${grandTotal.toFixed(2)}`;

            // Update Total Savings (if exists)
            const savingsSummary = document.querySelector('.summary-item.text-success');
            if (savingsSummary) {
                if (totalSavings > 0) {
                    savingsSummary.querySelector('.amount').textContent = `-₹${totalSavings.toFixed(2)}`;
                    savingsSummary.style.display = 'flex';
                } else {
                    savingsSummary.style.display = 'none';
                }
            }

            // Update GST
            const gstIndex = totalSavings > 0 ? 1 : 0;
            if (summaryItems[gstIndex + 1]) {
                summaryItems[gstIndex + 1].querySelector('.amount').textContent = `₹${totalGst.toFixed(2)}`;
            }
        }

        // Update Total Amount
        const totalAmountElement = document.querySelector('.total-amount .amount');
        if (totalAmountElement) {
            totalAmountElement.textContent = `₹${(grandTotal + totalGst).toFixed(2)}`;
        }
    }

    // Handle quantity input changes
    const quantityInputs = document.querySelectorAll('.quantity-input');

    quantityInputs.forEach(input => {
        input.addEventListener('change', function () {
            let value = parseInt(this.value);

            // Validate input
            if (isNaN(value) || value < 1) {
                value = 1;
            } else if (value > 99) {
                value = 99;
            }

            this.value = value;

            // Update display immediately
            updateCartTotals();

            // Update database via AJAX
            const form = this.closest('.quantity-form');
            const formData = new FormData(form);

            fetch('cart.php', {
                method: 'POST',
                body: formData
            }).catch(error => console.error('Error updating cart:', error));
        });

        // Also handle real-time input (as user types)
        input.addEventListener('input', function () {
            const value = parseInt(this.value);
            if (!isNaN(value) && value >= 1 && value <= 99) {
                updateCartTotals();
            }
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

    // Initialize totals on page load
    updateCartTotals();
});
