// Enhanced Search Results Scripts
class ProductFilter {
    constructor() {
      this.products = Array.from(document.querySelectorAll('.product-card'));
      this.filters = {
        sort: document.getElementById('sort'),
        price: document.getElementById('price-range'),
        category: document.getElementById('category'),
        availability: document.getElementById('availability')
      };
      
      this.initializeFilters();
      this.bindEvents();
    }
  
    initializeFilters() {
      // Store original product order
      this.originalOrder = this.products.map(p => p.cloneNode(true));
      
      // Initialize price range values
      const prices = this.products.map(p => 
        this.extractPrice(p.querySelector('.product-price').textContent)
      );
      const minPrice = Math.min(...prices);
      const maxPrice = Math.max(...prices);
      
      if (this.filters.price) {
        this.filters.price.min = minPrice;
        this.filters.price.max = maxPrice;
        this.filters.price.value = maxPrice;
      }
    }
  
    bindEvents() {
      // Bind filter change events
      Object.entries(this.filters).forEach(([key, element]) => {
        if (element) {
          element.addEventListener('change', () => this.applyFilters());
        }
      });
  
      // Add debounced price range input handler
      if (this.filters.price) {
        this.filters.price.addEventListener('input', this.debounce(() => {
          this.applyFilters();
        }, 300));
      }
    }
  
    applyFilters() {
      let filteredProducts = [...this.products];
  
      // Apply category filter
      if (this.filters.category?.value) {
        filteredProducts = filteredProducts.filter(product => {
          const category = product.querySelector('.product-category').textContent;
          return category.includes(this.filters.category.value);
        });
      }
  
      // Apply price filter
      if (this.filters.price?.value) {
        const maxPrice = parseFloat(this.filters.price.value);
        filteredProducts = filteredProducts.filter(product => {
          const price = this.extractPrice(product.querySelector('.product-price').textContent);
          return price <= maxPrice;
        });
      }
  
      // Apply availability filter
      if (this.filters.availability?.value) {
        filteredProducts = filteredProducts.filter(product => {
          const isAvailable = product.dataset.available === 'true';
          return this.filters.availability.value === 'all' || 
                 (this.filters.availability.value === 'in-stock' && isAvailable) ||
                 (this.filters.availability.value === 'out-of-stock' && !isAvailable);
        });
      }
  
      // Apply sorting
      if (this.filters.sort?.value) {
        filteredProducts.sort((a, b) => {
          const aValue = this.getSortValue(a, this.filters.sort.value);
          const bValue = this.getSortValue(b, this.filters.sort.value);
          
          return this.filters.sort.value.includes('desc') ? 
            bValue.localeCompare(aValue, undefined, {numeric: true}) :
            aValue.localeCompare(bValue, undefined, {numeric: true});
        });
      }
  
      this.updateDOM(filteredProducts);
    }
  
    getSortValue(product, sortType) {
      switch(sortType) {
        case 'name_asc':
        case 'name_desc':
          return product.querySelector('.product-name').textContent;
        case 'price_asc':
        case 'price_desc':
          return this.extractPrice(product.querySelector('.product-price').textContent).toString();
        default:
          return '';
      }
    }
  
    extractPrice(priceString) {
      return parseFloat(priceString.replace(/[^0-9.-]+/g, ''));
    }
  
    updateDOM(filteredProducts) {
      const container = document.querySelector('.search-grid');
      const noResults = document.querySelector('.no-results');
      
      container.innerHTML = '';
      
      if (filteredProducts.length) {
        filteredProducts.forEach(product => container.appendChild(product));
        noResults?.classList.add('hidden');
      } else {
        noResults?.classList.remove('hidden');
      }
  
      // Update results count
      const summary = document.querySelector('.search-summary');
      if (summary) {
        summary.textContent = `Found ${filteredProducts.length} results`;
      }
    }
  
    debounce(func, wait) {
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
        setTimeout(() => notification.remove(), 300);
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
  
  // Initialize filters when DOM is loaded
  document.addEventListener('DOMContentLoaded', () => {
    new ProductFilter();
  });