// Enhanced Search Results with Modern Price Slider
class ProductFilter {
  constructor() {
    this.products = Array.from(document.querySelectorAll('.product-card'));
    this.filters = {
      sort: document.getElementById('sort'),
      price: document.getElementById('price-range'),
      category: document.getElementById('category')
    };
    
    this.initializePriceSlider();
    this.bindEvents();
  }

  initializePriceSlider() {
    const priceRange = this.filters.price;
    const priceDisplay = document.getElementById('price-display');
    
    if (!priceRange || !priceDisplay) return;

    // Update price display on input
    const updatePriceDisplay = () => {
      const value = parseFloat(priceRange.value);
      priceDisplay.textContent = '₹' + value.toLocaleString('en-IN', {
        maximumFractionDigits: 2,
        minimumFractionDigits: 2
      });
      
      // Update slider fill for webkit browsers
      const min = parseFloat(priceRange.min);
      const max = parseFloat(priceRange.max);
      const percentage = ((value - min) / (max - min)) * 100;
      
      priceRange.style.background = `linear-gradient(to right, 
        var(--light-copper) 0%, 
        var(--primary-copper) ${percentage}%, 
        var(--bg-tertiary) ${percentage}%, 
        var(--bg-tertiary) 100%)`;
    };

    // Initialize display
    updatePriceDisplay();

    // Update on input
    priceRange.addEventListener('input', updatePriceDisplay);
  }

  bindEvents() {
    // Submit form when filters change
    Object.entries(this.filters).forEach(([key, element]) => {
      if (element) {
        element.addEventListener('change', () => {
          this.submitFilters();
        });
      }
    });

    // Also submit on price range change (after user releases)
    if (this.filters.price) {
      this.filters.price.addEventListener('change', () => {
        this.submitFilters();
      });
    }
  }

  submitFilters() {
    const form = document.getElementById('filter-form');
    if (form) {
      form.submit();
    }
  }

  extractPrice(priceString) {
    return parseFloat(priceString.replace(/[^0-9.-]+/g, ''));
  }
}

// Initialize cart functionality
const cart = {
  add: async function(productId) {
    try {
      const response = await fetch('add-to-cart.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `product_id=${productId}`
      });
      
      const data = await response.json();
      
      if (data.success) {
        this.showNotification('Product added to cart successfully!', 'success');
        this.updateCartBadge();
      } else {
        if (data.message === 'login_required') {
          this.showLoginModal();
        } else {
          this.showNotification(data.message, 'error');
        }
      }
    } catch (error) {
      console.error('Error:', error);
      this.showNotification('An error occurred while adding the product to cart.', 'error');
    }
  },

  showNotification: function(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
      notification.classList.add('fade-out');
      setTimeout(() => notification.remove(), 400);
    }, 3000);
  },

  showLoginModal: function() {
    const modal = document.getElementById('staticBackdrop');
    if (modal) {
      new bootstrap.Modal(modal).show();
    }
  },

  updateCartBadge: function() {
    const badge = document.querySelector('.cart-badge');
    if (badge) {
      const currentCount = parseInt(badge.textContent || '0');
      badge.textContent = currentCount + 1;
    }
  }
};

// Enhanced price slider with visual feedback
function enhancePriceSlider() {
  const priceRange = document.getElementById('price-range');
  const priceDisplay = document.getElementById('price-display');
  
  if (!priceRange || !priceDisplay) return;

  const updateSlider = () => {
    const value = parseFloat(priceRange.value);
    const min = parseFloat(priceRange.min);
    const max = parseFloat(priceRange.max);
    const percentage = ((value - min) / (max - min)) * 100;
    
    // Update display
    priceDisplay.textContent = '₹' + value.toLocaleString('en-IN', {
      maximumFractionDigits: 2,
      minimumFractionDigits: 2
    });
    
    // Update slider background for webkit browsers
    priceRange.style.background = `linear-gradient(to right, 
      #d4a574 0%, 
      #b87333 ${percentage}%, 
      #f5f5f5 ${percentage}%, 
      #f5f5f5 100%)`;
  };

  // Initialize
  updateSlider();

  // Update on input
  priceRange.addEventListener('input', updateSlider);
  priceRange.addEventListener('change', updateSlider);
}

// Auto-submit form helper
function setupAutoSubmit() {
  const form = document.getElementById('filter-form');
  if (!form) return;

  const selects = form.querySelectorAll('select');
  selects.forEach(select => {
    select.addEventListener('change', () => {
      form.submit();
    });
  });
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
  // Initialize product filter
  new ProductFilter();
  
  // Enhance price slider
  enhancePriceSlider();
  
  // Setup auto-submit for filters
  setupAutoSubmit();
  
  console.log('Search filters initialized successfully');
});

// Debounce utility for performance
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}