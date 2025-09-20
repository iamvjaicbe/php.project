// Application State
let products = [];
let categories = [];
let cart = [];
let settings = {};
let isAdminLoggedIn = false;
let currentCategory = 'all';

// Initialize Application
document.addEventListener('DOMContentLoaded', function() {
    initializeData();
    loadProducts();
    loadCategories();
    loadSettings();
    loadCart();
    updateCartDisplay();
    setupEventListeners();
});

// Initialize with sample data
function initializeData() {
    // Sample products
    const sampleProducts = [
        {
            id: 1,
            name: "Wireless Bluetooth Headphones",
            description: "Premium quality wireless headphones with noise cancellation and 20-hour battery life",
            price: 89.99,
            category: "Electronics",
            stock: 25,
            status: 'active'
        },
        {
            id: 2,
            name: "Organic Cotton T-Shirt",
            description: "Comfortable, eco-friendly t-shirt made from 100% organic cotton",
            price: 29.99,
            category: "Clothing",
            stock: 50,
            status: 'active'
        },
        {
            id: 3,
            name: "Stainless Steel Water Bottle",
            description: "Insulated water bottle that keeps drinks cold for 24 hours or hot for 12 hours",
            price: 24.99,
            category: "Home & Garden",
            stock: 30,
            status: 'active'
        },
        {
            id: 4,
            name: "Gaming Mechanical Keyboard",
            description: "RGB backlit mechanical keyboard with customizable keys and tactile feedback",
            price: 129.99,
            category: "Electronics",
            stock: 15,
            status: 'active'
        }
    ];

    const sampleCategories = [
        {id: 1, name: "Electronics", slug: "electronics", display_order: 1},
        {id: 2, name: "Clothing", slug: "clothing", display_order: 2},
        {id: 3, name: "Home & Garden", slug: "home-garden", display_order: 3},
        {id: 4, name: "Sports & Outdoors", slug: "sports-outdoors", display_order: 4}
    ];

    const defaultSettings = {
        shop_name: "MyShop",
        currency: "USD",
        whatsapp_number: "+1234567890",
        banner_text: "🎉 Free shipping on orders over $50! 📦",
        primary_color: "#007bff",
        secondary_color: "#6c757d"
    };

    // Initialize data if not exists
    if (!localStorage.getItem('ecommerce_products')) {
        localStorage.setItem('ecommerce_products', JSON.stringify(sampleProducts));
    }
    if (!localStorage.getItem('ecommerce_categories')) {
        localStorage.setItem('ecommerce_categories', JSON.stringify(sampleCategories));
    }
    if (!localStorage.getItem('ecommerce_settings')) {
        localStorage.setItem('ecommerce_settings', JSON.stringify(defaultSettings));
    }
}

// Load functions
function loadProducts() {
    products = JSON.parse(localStorage.getItem('ecommerce_products') || '[]');
    renderProducts();
}

function loadCategories() {
    categories = JSON.parse(localStorage.getItem('ecommerce_categories') || '[]');
    renderCategoryTabs();
    if (isAdminLoggedIn) {
        renderAdminCategories();
        populateProductCategorySelect();
    }
}

function loadSettings() {
    settings = JSON.parse(localStorage.getItem('ecommerce_settings') || '{}');
    applySettings();
}

function loadCart() {
    cart = JSON.parse(localStorage.getItem('ecommerce_cart') || '[]');
}

function saveProducts() {
    localStorage.setItem('ecommerce_products', JSON.stringify(products));
}

function saveCategories() {
    localStorage.setItem('ecommerce_categories', JSON.stringify(categories));
}

function saveSettingsData() {
    localStorage.setItem('ecommerce_settings', JSON.stringify(settings));
}

function saveCart() {
    localStorage.setItem('ecommerce_cart', JSON.stringify(cart));
}

// Apply settings to UI
function applySettings() {
    document.querySelector('.logo').textContent = settings.shop_name || 'MyShop';
    document.title = (settings.shop_name || 'MyShop') + ' - E-commerce Store';
    
    const promoText = document.querySelector('.promo-text');
    if (promoText) {
        promoText.textContent = settings.banner_text || '🎉 Free shipping on orders over $50! 📦';
    }
}

// Product rendering
function renderProducts() {
    const grid = document.getElementById('productsGrid');
    const activeProducts = products.filter(p => p.status === 'active');
    const filteredProducts = currentCategory === 'all' 
        ? activeProducts 
        : activeProducts.filter(p => p.category.toLowerCase().replace(/\s+/g, '-').replace('&', '') === currentCategory);

    if (filteredProducts.length === 0) {
        grid.innerHTML = '<div class="empty-state">No products found in this category.</div>';
        return;
    }

    grid.innerHTML = filteredProducts.map(product => `
        <div class="product-card">
            <div class="product-image">📦</div>
            <h3 class="product-name">${product.name}</h3>
            <p class="product-description">${product.description}</p>
            <div class="product-footer">
                <div class="product-price">$${product.price.toFixed(2)}</div>
                <div class="quantity-controls">
                    <button class="qty-btn" onclick="updateProductQuantity(${product.id}, -1)">-</button>
                    <span class="qty-display" id="qty-${product.id}">${getProductQuantity(product.id)}</span>
                    <button class="qty-btn" onclick="updateProductQuantity(${product.id}, 1)">+</button>
                </div>
            </div>
        </div>
    `).join('');
}

function renderCategoryTabs() {
    const tabsContainer = document.getElementById('categoryTabs');
    const allTab = '<button class="tab-btn active" data-category="all">All Products</button>';
    const categoryTabs = categories
        .sort((a, b) => a.display_order - b.display_order)
        .map(cat => `<button class="tab-btn" data-category="${cat.slug}">${cat.name}</button>`)
        .join('');
    
    tabsContainer.innerHTML = allTab + categoryTabs;
}

// Cart functionality
function getProductQuantity(productId) {
    const cartItem = cart.find(item => item.id === productId);
    return cartItem ? cartItem.quantity : 0;
}

function updateProductQuantity(productId, change) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    const existingItem = cart.find(item => item.id === productId);
    
    if (existingItem) {
        existingItem.quantity += change;
        if (existingItem.quantity <= 0) {
            cart = cart.filter(item => item.id !== productId);
        }
    } else if (change > 0) {
        cart.push({
            id: product.id,
            name: product.name,
            price: product.price,
            quantity: change
        });
    }

    // Update quantity display
    const qtyDisplay = document.getElementById(`qty-${productId}`);
    if (qtyDisplay) {
        qtyDisplay.textContent = getProductQuantity(productId);
    }

    saveCart();
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartCount = document.getElementById('cartCount');
    const cartTotal = document.getElementById('cartTotal');
    
    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
    const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

    cartCount.textContent = totalItems;
    cartTotal.textContent = `$${totalPrice.toFixed(2)}`;
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    
    // Update product quantity display
    const qtyDisplay = document.getElementById(`qty-${productId}`);
    if (qtyDisplay) {
        qtyDisplay.textContent = '0';
    }

    saveCart();
    updateCartDisplay();
    renderCartModal();
}

function updateCartItemQuantity(productId, newQuantity) {
    const cartItem = cart.find(item => item.id === productId);
    if (cartItem) {
        if (newQuantity <= 0) {
            removeFromCart(productId);
        } else {
            cartItem.quantity = newQuantity;
            
            // Update product quantity display
            const qtyDisplay = document.getElementById(`qty-${productId}`);
            if (qtyDisplay) {
                qtyDisplay.textContent = newQuantity;
            }
            
            saveCart();
            updateCartDisplay();
            renderCartModal();
        }
    }
}

function renderCartModal() {
    const cartItems = document.getElementById('cartItems');
    const modalCartTotal = document.getElementById('modalCartTotal');
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<div class="empty-cart">Your cart is empty</div>';
        modalCartTotal.textContent = '$0.00';
        return;
    }

    const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    modalCartTotal.textContent = `$${totalPrice.toFixed(2)}`;

    cartItems.innerHTML = cart.map(item => `
        <div class="cart-item">
            <div class="cart-item-image">📦</div>
            <div class="cart-item-details">
                <div class="cart-item-name">${item.name}</div>
                <div class="cart-item-price">$${item.price.toFixed(2)} each</div>
            </div>
            <div class="cart-item-actions">
                <div class="quantity-controls">
                    <button class="qty-btn" onclick="updateCartItemQuantity(${item.id}, ${item.quantity - 1})">-</button>
                    <span class="qty-display">${item.quantity}</span>
                    <button class="qty-btn" onclick="updateCartItemQuantity(${item.id}, ${item.quantity + 1})">+</button>
                </div>
                <button class="cart-remove-btn" onclick="removeFromCart(${item.id})">×</button>
            </div>
        </div>
    `).join('');
}

// WhatsApp sharing - Fixed function
function shareOrderViaWhatsApp() {
    if (cart.length === 0) {
        alert('Your cart is empty!');
        return;
    }

    try {
        const orderSummary = cart.map(item => 
            `${item.name} x ${item.quantity} - $${(item.price * item.quantity).toFixed(2)}`
        ).join('%0A');
        
        const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        const shopName = settings.shop_name || 'MyShop';
        const whatsappNumber = (settings.whatsapp_number || '+1234567890').replace(/\D/g, '');
        
        const message = `Order Summary:%0A${orderSummary}%0A%0ATotal: $${totalPrice.toFixed(2)}%0AFrom: ${shopName}`;
        const whatsappUrl = `https://wa.me/${whatsappNumber}?text=${message}`;
        
        // Show confirmation and open WhatsApp
        console.log('Opening WhatsApp with URL:', whatsappUrl);
        
        // Try to open WhatsApp - this will work in most browsers
        const newWindow = window.open(whatsappUrl, '_blank');
        
        // If popup was blocked, show the URL to the user
        if (!newWindow || newWindow.closed || typeof newWindow.closed == 'undefined') {
            const fallbackMessage = `WhatsApp link: ${whatsappUrl}`;
            if (confirm('Unable to open WhatsApp automatically. Would you like to copy the link?')) {
                navigator.clipboard.writeText(whatsappUrl).then(() => {
                    alert('WhatsApp link copied to clipboard!');
                }).catch(() => {
                    alert(fallbackMessage);
                });
            }
        } else {
            // Success feedback
            setTimeout(() => {
                if (!newWindow.closed) {
                    console.log('WhatsApp opened successfully');
                }
            }, 1000);
        }
    } catch (error) {
        console.error('Error sharing order:', error);
        alert('Error sharing order. Please try again.');
    }
}

// Modal functions
function showCartModal() {
    renderCartModal();
    document.getElementById('cartModal').classList.remove('hidden');
}

function hideCartModal() {
    document.getElementById('cartModal').classList.add('hidden');
}

function showAdminLogin() {
    document.getElementById('adminLoginModal').classList.remove('hidden');
}

function hideAdminLogin() {
    document.getElementById('adminLoginModal').classList.add('hidden');
}

// Admin functions
function handleAdminLogin(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const username = formData.get('username');
    const password = formData.get('password');

    // Simple authentication (in real app, this would be server-side)
    if (username === 'admin' && password === 'admin123') {
        isAdminLoggedIn = true;
        hideAdminLogin();
        showAdminPanel();
    } else {
        alert('Invalid credentials. Use admin/admin123');
    }
}

function logoutAdmin() {
    isAdminLoggedIn = false;
    document.getElementById('adminPanel').classList.add('hidden');
}

function showAdminPanel() {
    document.getElementById('adminPanel').classList.remove('hidden');
    renderAdminProducts();
    renderAdminCategories();
    loadAdminSettings();
    updateDashboardStats();
}

function switchAdminPanel(panelName) {
    // Hide all panels
    document.querySelectorAll('.admin-panel-content').forEach(panel => {
        panel.classList.add('hidden');
    });
    
    // Show selected panel
    document.getElementById(panelName + 'Panel').classList.remove('hidden');
    
    // Update navigation
    document.querySelectorAll('.admin-nav-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    document.querySelector(`[data-panel="${panelName}"]`).classList.add('active');
}

function updateDashboardStats() {
    document.getElementById('totalProducts').textContent = products.length;
    document.getElementById('totalCategories').textContent = categories.length;
    document.getElementById('activeProducts').textContent = products.filter(p => p.status === 'active').length;
}

// Admin product management
function renderAdminProducts() {
    const tbody = document.querySelector('#adminProductsTable tbody');
    tbody.innerHTML = products.map(product => `
        <tr>
            <td>${product.name}</td>
            <td>${product.category}</td>
            <td>$${product.price.toFixed(2)}</td>
            <td>${product.stock}</td>
            <td><span class="status-badge status-${product.status}">${product.status}</span></td>
            <td>
                <div class="table-actions">
                    <button class="btn-sm btn-edit" onclick="editProduct(${product.id})">Edit</button>
                    <button class="btn-sm btn-delete" onclick="deleteProduct(${product.id})">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function showAddProduct() {
    document.getElementById('productFormTitle').textContent = 'Add New Product';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    populateProductCategorySelect();
    document.getElementById('productFormModal').classList.remove('hidden');
}

function editProduct(id) {
    const product = products.find(p => p.id === id);
    if (!product) return;

    document.getElementById('productFormTitle').textContent = 'Edit Product';
    document.getElementById('productId').value = product.id;
    document.getElementById('productName').value = product.name;
    populateProductCategorySelect();
    document.getElementById('productCategory').value = product.category;
    document.getElementById('productPrice').value = product.price;
    document.getElementById('productStock').value = product.stock;
    document.getElementById('productDescription').value = product.description;
    document.getElementById('productFormModal').classList.remove('hidden');
}

function saveProduct(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const productId = formData.get('product_id');
    
    const productData = {
        name: formData.get('name'),
        category: formData.get('category'),
        price: parseFloat(formData.get('price')),
        stock: parseInt(formData.get('stock')),
        description: formData.get('description'),
        status: 'active'
    };

    if (productId) {
        // Edit existing product
        const product = products.find(p => p.id === parseInt(productId));
        if (product) {
            Object.assign(product, productData);
        }
    } else {
        // Add new product
        productData.id = Math.max(0, ...products.map(p => p.id)) + 1;
        products.push(productData);
    }

    saveProducts();
    renderProducts();
    renderAdminProducts();
    updateDashboardStats();
    hideProductForm();
}

function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        const product = products.find(p => p.id === id);
        if (product) {
            product.status = 'inactive'; // Soft delete
        }
        saveProducts();
        renderProducts();
        renderAdminProducts();
        updateDashboardStats();
    }
}

function hideProductForm() {
    document.getElementById('productFormModal').classList.add('hidden');
}

// Admin category management
function renderAdminCategories() {
    const tbody = document.querySelector('#adminCategoriesTable tbody');
    tbody.innerHTML = categories.map(category => `
        <tr>
            <td>${category.name}</td>
            <td>${category.slug}</td>
            <td>${category.display_order}</td>
            <td>
                <div class="table-actions">
                    <button class="btn-sm btn-edit" onclick="editCategory(${category.id})">Edit</button>
                    <button class="btn-sm btn-delete" onclick="deleteCategory(${category.id})">Delete</button>
                </div>
            </td>
        </tr>
    `).join('');
}

function showAddCategory() {
    document.getElementById('categoryFormTitle').textContent = 'Add New Category';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryFormModal').classList.remove('hidden');
}

function editCategory(id) {
    const category = categories.find(c => c.id === id);
    if (!category) return;

    document.getElementById('categoryFormTitle').textContent = 'Edit Category';
    document.getElementById('categoryId').value = category.id;
    document.getElementById('categoryName').value = category.name;
    document.getElementById('categorySlug').value = category.slug;
    document.getElementById('categoryDisplayOrder').value = category.display_order;
    document.getElementById('categoryFormModal').classList.remove('hidden');
}

function saveCategory(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    const categoryId = formData.get('category_id');
    
    const categoryData = {
        name: formData.get('name'),
        slug: formData.get('slug'),
        display_order: parseInt(formData.get('display_order'))
    };

    if (categoryId) {
        // Edit existing category
        const category = categories.find(c => c.id === parseInt(categoryId));
        if (category) {
            Object.assign(category, categoryData);
        }
    } else {
        // Add new category
        categoryData.id = Math.max(0, ...categories.map(c => c.id)) + 1;
        categories.push(categoryData);
    }

    saveCategories();
    loadCategories();
    renderAdminCategories();
    updateDashboardStats();
    hideCategoryForm();
}

function deleteCategory(id) {
    if (confirm('Are you sure you want to delete this category?')) {
        categories = categories.filter(c => c.id !== id);
        saveCategories();
        loadCategories();
        renderAdminCategories();
        updateDashboardStats();
    }
}

function hideCategoryForm() {
    document.getElementById('categoryFormModal').classList.add('hidden');
}

function populateProductCategorySelect() {
    const select = document.getElementById('productCategory');
    select.innerHTML = '<option value="">Select Category</option>' +
        categories.map(cat => `<option value="${cat.name}">${cat.name}</option>`).join('');
}

// Admin settings
function loadAdminSettings() {
    document.getElementById('shopName').value = settings.shop_name || '';
    document.getElementById('currency').value = settings.currency || 'USD';
    document.getElementById('whatsappNumber').value = settings.whatsapp_number || '';
    document.getElementById('bannerText').value = settings.banner_text || '';
}

function saveSettings(event) {
    event.preventDefault();
    const formData = new FormData(event.target);
    
    settings = {
        shop_name: formData.get('shop_name'),
        currency: formData.get('currency'),
        whatsapp_number: formData.get('whatsapp_number'),
        banner_text: formData.get('banner_text')
    };

    saveSettingsData();
    applySettings();
    alert('Settings saved successfully!');
}

// Event listeners
function setupEventListeners() {
    // Cart button
    document.getElementById('cartBtn').addEventListener('click', showCartModal);

    // Category tabs
    document.getElementById('categoryTabs').addEventListener('click', function(e) {
        if (e.target.classList.contains('tab-btn')) {
            // Update active tab
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            e.target.classList.add('active');
            
            // Update current category and render products
            currentCategory = e.target.dataset.category;
            renderProducts();
        }
    });

    // Admin navigation
    const adminNav = document.querySelector('.admin-nav');
    if (adminNav) {
        adminNav.addEventListener('click', function(e) {
            if (e.target.classList.contains('admin-nav-btn')) {
                const panel = e.target.dataset.panel;
                switchAdminPanel(panel);
            }
        });
    }

    // Auto-generate category slug
    const categoryNameInput = document.getElementById('categoryName');
    if (categoryNameInput) {
        categoryNameInput.addEventListener('input', function() {
            const slug = this.value.toLowerCase()
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
            document.getElementById('categorySlug').value = slug;
        });
    }
}