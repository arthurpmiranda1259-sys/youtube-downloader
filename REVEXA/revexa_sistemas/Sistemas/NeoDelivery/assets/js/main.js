// Cart Management
let cart = JSON.parse(localStorage.getItem('cart')) || [];

function updateCartUI() {
    const cartBadge = document.querySelector('.cart-badge');
    const cartItems = document.querySelector('.cart-items');
    const cartTotal = document.querySelector('.cart-total-value');
    
    if (cartBadge) {
        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
        cartBadge.textContent = totalItems;
        cartBadge.style.display = totalItems > 0 ? 'flex' : 'none';
    }
    
    if (cartItems) {
        if (cart.length === 0) {
            cartItems.innerHTML = '<p style="text-align: center; color: #636e72; padding: 40px 0;">Seu carrinho está vazio</p>';
        } else {
            cartItems.innerHTML = cart.map(item => `
                <div class="cart-item" data-id="${item.id}">
                    <img src="${item.image || 'assets/images/placeholder.jpg'}" alt="${item.name}" class="cart-item-image">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${item.name}</div>
                        ${item.options ? `<div class="cart-item-options">${item.options}</div>` : ''}
                        <div class="cart-item-price">${formatMoney(item.price)}</div>
                        <div class="cart-item-quantity">
                            <button class="qty-btn" onclick="updateQuantity(${item.id}, -1)">-</button>
                            <span>${item.quantity}</span>
                            <button class="qty-btn" onclick="updateQuantity(${item.id}, 1)">+</button>
                        </div>
                    </div>
                    <button class="cart-item-remove" onclick="removeFromCart(${item.id})">×</button>
                </div>
            `).join('');
        }
    }
    
    if (cartTotal) {
        const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        cartTotal.textContent = formatMoney(total);
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
}

function addToCart(product) {
    const existingItem = cart.find(item => item.id === product.id);
    
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            image: product.image,
            quantity: 1,
            options: product.options || ''
        });
    }
    
    updateCartUI();
    showNotification('Produto adicionado ao carrinho!', 'success');
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    updateCartUI();
    showNotification('Produto removido do carrinho', 'info');
}

function updateQuantity(productId, change) {
    const item = cart.find(item => item.id === productId);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            removeFromCart(productId);
        } else {
            updateCartUI();
        }
    }
}

function clearCart() {
    cart = [];
    updateCartUI();
}

function toggleCart(event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    const cartSidebar = document.querySelector('.cart-sidebar');
    if (cartSidebar) {
        cartSidebar.classList.toggle('active');
        console.log('Carrinho toggled:', cartSidebar.classList.contains('active'));
    } else {
        console.error('Cart sidebar não encontrado!');
    }
}

// Mobile Menu Toggle
function toggleMobileMenu() {
    const sidebar = document.querySelector('.mobile-menu');
    if (sidebar) {
        sidebar.classList.toggle('active');
    }
}

// Utility Functions
function formatMoney(value) {
    return 'R$ ' + parseFloat(value).toFixed(2).replace('.', ',');
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type}`;
    notification.style.position = 'fixed';
    notification.style.top = '20px';
    notification.style.right = '20px';
    notification.style.zIndex = '9999';
    notification.style.minWidth = '300px';
    notification.style.animation = 'slideIn 0.3s ease';
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Modal Functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
    }
}

// Form Validation
function validateCheckoutForm() {
    const form = document.getElementById('checkoutForm');
    if (!form) return false;
    
    const name = form.querySelector('[name="customer_name"]').value.trim();
    const phone = form.querySelector('[name="customer_phone"]').value.trim();
    const deliveryType = form.querySelector('[name="delivery_type"]:checked');
    
    if (!name) {
        showNotification('Por favor, informe seu nome', 'danger');
        return false;
    }
    
    if (!phone) {
        showNotification('Por favor, informe seu telefone', 'danger');
        return false;
    }
    
    if (!deliveryType) {
        showNotification('Por favor, selecione o tipo de entrega', 'danger');
        return false;
    }
    
    if (deliveryType.value === 'delivery') {
        const address = form.querySelector('[name="customer_address"]').value.trim();
        const neighborhood = form.querySelector('[name="customer_neighborhood"]').value.trim();
        
        if (!address || !neighborhood) {
            showNotification('Por favor, preencha o endereço completo', 'danger');
            return false;
        }
    }
    
    return true;
}

// Delivery Type Change
function handleDeliveryTypeChange() {
    const deliveryType = document.querySelector('[name="delivery_type"]:checked');
    const addressFields = document.getElementById('addressFields');
    
    if (deliveryType && addressFields) {
        if (deliveryType.value === 'delivery') {
            addressFields.style.display = 'block';
        } else {
            addressFields.style.display = 'none';
        }
    }
}

// Neighborhood Change - Calculate Delivery Fee
function handleNeighborhoodChange() {
    const neighborhood = document.querySelector('[name="customer_neighborhood"]').value;
    if (!neighborhood) return;
    
    fetch('api/get_delivery_fee.php?neighborhood=' + encodeURIComponent(neighborhood))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('deliveryFee').textContent = formatMoney(data.fee);
                updateOrderTotal();
            }
        });
}

function updateOrderTotal() {
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const deliveryFeeText = document.getElementById('deliveryFee')?.textContent || 'R$ 0,00';
    const deliveryFee = parseFloat(deliveryFeeText.replace('R$ ', '').replace(',', '.')) || 0;
    const total = subtotal + deliveryFee;
    
    if (document.getElementById('orderTotal')) {
        document.getElementById('orderTotal').textContent = formatMoney(total);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM carregado!');
    updateCartUI();
    
    // Cart toggle
    const cartIcon = document.querySelector('.cart-icon');
    if (cartIcon) {
        console.log('Cart icon encontrado!');
        cartIcon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Cart icon clicado!');
            toggleCart(e);
        });
    } else {
        console.error('Cart icon não encontrado!');
    }
    
    const cartClose = document.querySelector('.cart-close');
    if (cartClose) {
        cartClose.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            toggleCart(e);
        });
    }
    
    // Menu hamburger toggle
    const menuToggle = document.querySelector('.menu-toggle');
    if (menuToggle) {
        console.log('Menu toggle encontrado!');
        menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Menu toggle clicado!');
            toggleMobileMenu();
        });
    } else {
        console.error('Menu toggle não encontrado!');
    }
    
    // Delivery type listeners
    const deliveryTypeInputs = document.querySelectorAll('[name="delivery_type"]');
    deliveryTypeInputs.forEach(input => {
        input.addEventListener('change', handleDeliveryTypeChange);
    });
    
    // Neighborhood listener
    const neighborhoodInput = document.querySelector('[name="customer_neighborhood"]');
    if (neighborhoodInput) {
        neighborhoodInput.addEventListener('change', handleNeighborhoodChange);
    }
    
    // Close modal on outside click
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });
});

// Close menus when clicking outside
document.addEventListener('click', function(e) {
    const cartSidebar = document.querySelector('.cart-sidebar');
    const cartIcon = document.querySelector('.cart-icon');
    const mobileMenu = document.querySelector('.mobile-menu');
    const menuToggle = document.querySelector('.menu-toggle');
    
    // Close cart if clicking outside
    if (cartSidebar && cartSidebar.classList.contains('active')) {
        if (!cartSidebar.contains(e.target) && !cartIcon.contains(e.target)) {
            cartSidebar.classList.remove('active');
        }
    }
    
    // Close mobile menu if clicking outside
    if (mobileMenu && mobileMenu.classList.contains('active')) {
        if (!mobileMenu.contains(e.target) && !menuToggle.contains(e.target)) {
            mobileMenu.classList.remove('active');
        }
    }
});

// Carousel Functions
let currentSlideIndex = 0;

function changeSlide(direction) {
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.dot');
    
    if (slides.length === 0) return;
    
    slides[currentSlideIndex].classList.remove('active');
    if (dots[currentSlideIndex]) {
        dots[currentSlideIndex].classList.remove('active');
    }
    
    currentSlideIndex += direction;
    
    if (currentSlideIndex >= slides.length) {
        currentSlideIndex = 0;
    } else if (currentSlideIndex < 0) {
        currentSlideIndex = slides.length - 1;
    }
    
    slides[currentSlideIndex].classList.add('active');
    if (dots[currentSlideIndex]) {
        dots[currentSlideIndex].classList.add('active');
    }
}

function currentSlide(index) {
    const slides = document.querySelectorAll('.carousel-slide');
    const dots = document.querySelectorAll('.dot');
    
    if (slides.length === 0) return;
    
    slides[currentSlideIndex].classList.remove('active');
    if (dots[currentSlideIndex]) {
        dots[currentSlideIndex].classList.remove('active');
    }
    
    currentSlideIndex = index;
    
    slides[currentSlideIndex].classList.add('active');
    if (dots[currentSlideIndex]) {
        dots[currentSlideIndex].classList.add('active');
    }
}

// Auto-advance carousel
function startCarousel() {
    const slides = document.querySelectorAll('.carousel-slide');
    if (slides.length > 1) {
        setInterval(() => {
            changeSlide(1);
        }, 5000); // Mudar slide a cada 5 segundos
    }
}

// CSS Animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Start carousel when page loads
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startCarousel);
} else {
    startCarousel();
}
