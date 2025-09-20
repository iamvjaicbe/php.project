class ECommerceApp {
    constructor() {
        this.cart = [];
        this.products = window.shopData?.products || [];
        this.settings = window.shopData?.settings || {};
        this.whatsappNumber = window.shopData?.whatsappNumber || '+1234567890';
        this.currencySymbol = window.shopData?.currencySymbol || '$';

        this.init();
    }

    init() {
        this.loadCart();
        this.setupEventListeners();
        this.updateCartDisplay();
        this.initCategoryTabs();
    }

    setupEventListeners() {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => this.filterProducts(e.target.dataset.category));
        });

        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('modal-overlay')) {
                this.closeCartModal();
            }
        });
    }

    initCategoryTabs() {
        const tabs = document.querySelectorAll('.tab-btn');
        if (tabs.length > 0) {
            tabs[0].click();
        }
    }

    filterProducts(category) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-category="${category}"]`).classList.add('active');

        document.querySelectorAll('.product-card').forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    updateQuantity(productId, change) {
        const qtyElement = document.getElementById(`qty-${productId}`);
        let currentQty = parseInt(qtyElement.textContent) || 0;
        let newQty = Math.max(0, currentQty + change);

        qtyElement.textContent = newQty;

        if (newQty === 0) {
            this.removeFromCart(productId);
        } else if (currentQty > 0) {
            const cartItem = this.cart.find(item => item.id == productId);
            if (cartItem) {
                cartItem.quantity = newQty;
                this.saveCart();
                this.updateCartDisplay();
            }
        }
    }

    addToCart(productId) {
        const product = this.products.find(p => p.id == productId);
        if (!product) return;

        const qtyElement = document.getElementById(`qty-${productId}`);
        const quantity = parseInt(qtyElement.textContent) || 1;

        if (quantity === 0) {
            qtyElement.textContent = '1';
            this.addProductToCart(product, 1);
        } else {
            this.addProductToCart(product, quantity);
        }
    }

    addProductToCart(product, quantity) {
        const existingItem = this.cart.find(item => item.id == product.id);

        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.cart.push({
                id: product.id,
                name: product.name,
                price: parseFloat(product.price),
                quantity: quantity,
                image: product.image
            });
        }

        this.saveCart();
        this.updateCartDisplay();
        this.showNotification(product.name + ' added to cart!');
    }

    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id != productId);

        const qtyElement = document.getElementById(`qty-${productId}`);
        if (qtyElement) {
            qtyElement.textContent = '0';
        }

        this.saveCart();
        this.updateCartDisplay();
        this.updateCartModal();
    }

    clearCart() {
        if (confirm('Clear cart?')) {
            this.cart = [];
            document.querySelectorAll('.quantity').forEach(el => {
                el.textContent = '0';
            });
            this.saveCart();
            this.updateCartDisplay();
            this.updateCartModal();
        }
    }

    updateCartDisplay() {
        const cartCount = document.getElementById('cartCount');
        const cartTotal = document.getElementById('cartTotal');

        const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
        const totalPrice = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

        if (cartCount) cartCount.textContent = totalItems;
        if (cartTotal) cartTotal.textContent = this.formatPrice(totalPrice);
    }

    toggleCartModal() {
        const modal = document.getElementById('cartModal');
        if (modal.classList.contains('active')) {
            this.closeCartModal();
        } else {
            this.openCartModal();
        }
    }

    openCartModal() {
        const modal = document.getElementById('cartModal');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        this.updateCartModal();
    }

    closeCartModal() {
        const modal = document.getElementById('cartModal');
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }

    updateCartModal() {
        const cartContent = document.getElementById('cartContent');
        const cartModalTotal = document.getElementById('cartModalTotal');

        if (this.cart.length === 0) {
            cartContent.innerHTML = '<div class="empty-cart"><p>Your cart is empty</p></div>';
        } else {
            const cartHTML = this.cart.map(item => `
                <div class="cart-item">
                    <div class="cart-item-info">
                        <div class="cart-item-name">${this.escapeHtml(item.name)}</div>
                        <div class="cart-item-price">${this.formatPrice(item.price)}</div>
                    </div>
                    <div class="cart-item-controls">
                        <button class="qty-btn" onclick="app.updateCartItemQuantity(${item.id}, ${item.quantity - 1})">-</button>
                        <span class="quantity">${item.quantity}</span>
                        <button class="qty-btn" onclick="app.updateCartItemQuantity(${item.id}, ${item.quantity + 1})">+</button>
                        <button class="remove-btn" onclick="app.removeFromCart(${item.id})">×</button>
                    </div>
                </div>
            `).join('');

            cartContent.innerHTML = cartHTML;
        }

        const totalPrice = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        cartModalTotal.textContent = this.formatPrice(totalPrice);

        const whatsappBtn = document.getElementById('whatsappBtn');
        if (whatsappBtn) {
            whatsappBtn.style.display = this.cart.length > 0 ? 'block' : 'none';
        }
    }

    updateCartItemQuantity(productId, newQuantity) {
        const cartItem = this.cart.find(item => item.id == productId);
        if (!cartItem) return;

        if (newQuantity <= 0) {
            this.removeFromCart(productId);
        } else {
            cartItem.quantity = newQuantity;

            const qtyElement = document.getElementById(`qty-${productId}`);
            if (qtyElement) {
                qtyElement.textContent = newQuantity;
            }

            this.saveCart();
            this.updateCartDisplay();
            this.updateCartModal();
        }
    }

    shareOrderWhatsApp() {
        if (this.cart.length === 0) {
            alert('Cart is empty!');
            return;
        }

        let orderSummary = `🛒 *Order Summary*\n\n`;
        let totalPrice = 0;

        this.cart.forEach(item => {
            const itemTotal = item.price * item.quantity;
            totalPrice += itemTotal;
            orderSummary += `• ${item.name}\n`;
            orderSummary += `  Qty: ${item.quantity} × ${this.formatPrice(item.price)} = ${this.formatPrice(itemTotal)}\n\n`;
        });

        orderSummary += `💰 *Total: ${this.formatPrice(totalPrice)}*\n\n`;
        orderSummary += `From: ${this.settings.shop_name || 'MyShop'}`;

        const whatsappUrl = `https://wa.me/${this.whatsappNumber.replace(/[^\d]/g, '')}?text=${encodeURIComponent(orderSummary)}`;
        window.open(whatsappUrl, '_blank');
    }

    showNotification(message) {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed; top: 20px; right: 20px; background: #28a745; color: white;
            padding: 12px 20px; border-radius: 8px; z-index: 1001;
        `;
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 2000);
    }

    formatPrice(price) {
        return this.currencySymbol + parseFloat(price || 0).toFixed(2);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    saveCart() {
        try {
            localStorage.setItem('ecommerce_cart', JSON.stringify(this.cart));
        } catch (e) {
            console.warn('Unable to save cart');
        }
    }

    loadCart() {
        try {
            const savedCart = localStorage.getItem('ecommerce_cart');
            if (savedCart) {
                this.cart = JSON.parse(savedCart);
                this.cart.forEach(item => {
                    const qtyElement = document.getElementById(`qty-${item.id}`);
                    if (qtyElement) {
                        qtyElement.textContent = item.quantity;
                    }
                });
            }
        } catch (e) {
            this.cart = [];
        }
    }
}

function updateQuantity(productId, change) { app.updateQuantity(productId, change); }
function addToCart(productId) { app.addToCart(productId); }
function toggleCartModal() { app.toggleCartModal(); }
function closeCartModal() { app.closeCartModal(); }
function clearCart() { app.clearCart(); }
function shareOrderWhatsApp() { app.shareOrderWhatsApp(); }

document.addEventListener('DOMContentLoaded', () => {
    window.app = new ECommerceApp();
});