document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileMenuButton = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (mobileMenuButton) {
        mobileMenuButton.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }

    // Cart quantity adjustments
    const quantityInputs = document.querySelectorAll('.cart-quantity');
    quantityInputs.forEach(input => {
        input.addEventListener('change', updateCartItem);
    });

    // Product grid/list view toggle
    const viewToggle = document.querySelector('.view-toggle');
    const productGrid = document.querySelector('.products-grid');
    
    if (viewToggle && productGrid) {
        viewToggle.addEventListener('click', (e) => {
            if (e.target.classList.contains('grid-view')) {
                productGrid.classList.remove('list-view');
            } else if (e.target.classList.contains('list-view')) {
                productGrid.classList.add('list-view');
            }
        });
    }
});

// Cart update function
async function updateCartItem(e) {
    const quantity = e.target.value;
    const productId = e.target.dataset.productId;

    try {
        const response = await fetch('/api/cart-update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity
            })
        });

        if (!response.ok) throw new Error('Cart update failed');

        const data = await response.json();
        updateCartTotal(data.total);
        
    } catch (error) {
        console.error('Error updating cart:', error);
    }
}

// Update cart total display
function updateCartTotal(total) {
    const cartTotal = document.querySelector('.cart-total');
    if (cartTotal) {
        cartTotal.textContent = `$${total.toFixed(2)}`;
    }
}

// Mobile menu functionality
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileSidebar = document.querySelector('.mobile-sidebar');
    const mobileOverlay = document.querySelector('.mobile-overlay');
    const mobileSidebarClose = document.querySelector('.mobile-sidebar-close');

    function openMobileMenu() {
        mobileSidebar.classList.add('active');
        mobileOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        mobileSidebar.classList.remove('active');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    mobileMenuBtn.addEventListener('click', openMobileMenu);
    mobileSidebarClose.addEventListener('click', closeMobileMenu);
    mobileOverlay.addEventListener('click', closeMobileMenu);
}); 