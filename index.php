<?php
require_once 'config.php';

try {
    $pdo = getDatabase();
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE status = 'active' ORDER BY display_order ASC");
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.status = 'active' AND c.status = 'active' ORDER BY p.featured DESC, p.created_at DESC");
    $stmt->execute();
    $products = $stmt->fetchAll();
    
    $stmt = $pdo->prepare("SELECT `key`, `value` FROM settings");
    $stmt->execute();
    $settings_data = $stmt->fetchAll();
    
    $settings = [];
    foreach ($settings_data as $setting) {
        $settings[$setting['key']] = $setting['value'];
    }
} catch (PDOException $e) {
    $categories = [];
    $products = [];
    $settings = ['shop_name' => 'MyShop', 'currency_symbol' => '$', 'banner_text' => 'Welcome to our store!'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitizeInput($settings['shop_name'] ?? 'MyShop') ?> - Modern E-commerce</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #1a202c;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 16px;
        }
        
        /* Header */
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 0;
        }
        
        .logo {
            font-size: 28px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }
        
        .modern-cart-btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 50px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .modern-cart-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.4);
        }
        
        .modern-cart-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .modern-cart-btn:hover::before {
            left: 100%;
        }
        
        .cart-badge {
            background: rgba(255, 255, 255, 0.9);
            color: #667eea;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Promo Banner */
        .promo-banner {
            background: linear-gradient(90deg, #1a202c, #2d3748);
            color: white;
            padding: 12px 0;
            overflow: hidden;
            position: relative;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .promo-text {
            text-align: center;
            font-weight: 500;
            font-size: 15px;
            animation: slideText 20s linear infinite;
            white-space: nowrap;
        }
        
        @keyframes slideText {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        
        /* Main Content */
        .main-content {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            margin: 20px 0;
            border-radius: 24px;
            padding: 32px 0;
        }
        
        /* Category Tabs */
        .category-tabs {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
            padding: 0 16px;
            overflow-x: auto;
            scrollbar-width: none;
        }
        
        .category-tabs::-webkit-scrollbar {
            display: none;
        }
        
        .tab-btn {
            background: rgba(255, 255, 255, 0.9);
            border: none;
            color: #4a5568;
            padding: 14px 24px;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s ease;
            white-space: nowrap;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .tab-btn:hover,
        .tab-btn.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 24px;
            padding: 0 16px;
        }
        
        .product-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(20px);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.37);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        .product-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 20px 40px rgba(31, 38, 135, 0.5);
        }
        
        .product-image {
            width: 100%;
            height: 240px;
            background: linear-gradient(135deg, #f7fafc, #edf2f7);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }
        
        .product-card:hover .product-image img {
            transform: scale(1.1);
        }
        
        .product-image .no-image {
            font-size: 64px;
            color: #cbd5e0;
        }
        
        .product-info {
            padding: 24px;
        }
        
        .product-name {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #2d3748;
            line-height: 1.4;
        }
        
        .product-description {
            font-size: 14px;
            color: #718096;
            margin-bottom: 16px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .product-price {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 20px;
        }
        
        .product-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 12px;
            background: rgba(102, 126, 234, 0.1);
            padding: 8px 16px;
            border-radius: 50px;
            backdrop-filter: blur(10px);
        }
        
        .qty-btn {
            width: 40px;
            height: 40px;
            border: none;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: 700;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .qty-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        .qty-btn:active {
            transform: scale(0.95);
        }
        
        .quantity {
            font-weight: 700;
            font-size: 18px;
            color: #2d3748;
            min-width: 30px;
            text-align: center;
        }
        
        /* Cart Modal */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(10px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            padding: 20px;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .cart-modal {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            max-width: 600px;
            width: 100%;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.18);
            overflow: hidden;
        }
        
        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 24px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.8);
        }
        
        .cart-header h2 {
            font-size: 24px;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .close-btn {
            background: rgba(220, 53, 69, 0.1);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #dc3545;
            font-size: 18px;
            transition: all 0.3s ease;
        }
        
        .close-btn:hover {
            background: #dc3545;
            color: white;
            transform: scale(1.1);
        }
        
        .cart-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
        }
        
        .cart-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .cart-item-image {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            overflow: hidden;
            background: #f7fafc;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .cart-item-info {
            flex: 1;
        }
        
        .cart-item-name {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }
        
        .cart-item-price {
            color: #667eea;
            font-weight: 700;
            font-size: 16px;
        }
        
        .cart-item-controls {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .cart-qty-btn {
            width: 32px;
            height: 32px;
            border: none;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .cart-qty-btn:hover {
            transform: scale(1.1);
        }
        
        .cart-quantity {
            font-weight: 600;
            min-width: 24px;
            text-align: center;
        }
        
        .remove-btn {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: none;
            border-radius: 8px;
            width: 32px;
            height: 32px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .remove-btn:hover {
            background: #dc3545;
            color: white;
            transform: scale(1.1);
        }
        
        .empty-cart {
            text-align: center;
            padding: 40px 20px;
            color: #718096;
        }
        
        .empty-cart i {
            font-size: 64px;
            margin-bottom: 16px;
            color: #e2e8f0;
        }
        
        .cart-footer {
            padding: 24px;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.8);
        }
        
        .cart-total-display {
            font-size: 24px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .cart-actions {
            display: flex;
            gap: 12px;
        }
        
        .btn-primary,
        .btn-secondary {
            flex: 1;
            padding: 16px 20px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            box-shadow: 0 4px 15px rgba(72, 187, 120, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(72, 187, 120, 0.6);
        }
        
        .btn-secondary {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            border: 2px solid rgba(108, 117, 125, 0.2);
        }
        
        .btn-secondary:hover {
            background: rgba(108, 117, 125, 0.2);
            transform: translateY(-2px);
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 0 12px;
            }
            
            .header-content {
                padding: 12px 0;
            }
            
            .logo {
                font-size: 24px;
            }
            
            .modern-cart-btn {
                padding: 10px 16px;
                font-size: 13px;
            }
            
            .main-content {
                margin: 12px 0;
                padding: 20px 0;
            }
            
            .category-tabs {
                padding: 0 12px;
                gap: 8px;
            }
            
            .tab-btn {
                padding: 12px 20px;
                font-size: 13px;
            }
            
            .products-grid {
                grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
                gap: 16px;
                padding: 0 12px;
            }
            
            .product-card {
                border-radius: 16px;
            }
            
            .product-image {
                height: 200px;
            }
            
            .product-info {
                padding: 20px;
            }
            
            .product-name {
                font-size: 16px;
            }
            
            .product-price {
                font-size: 20px;
            }
            
            .cart-modal {
                margin: 10px;
                max-width: calc(100vw - 20px);
                border-radius: 20px;
            }
            
            .cart-header {
                padding: 20px;
            }
            
            .cart-header h2 {
                font-size: 20px;
            }
            
            .cart-actions {
                flex-direction: column;
            }
            
            .qty-btn {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }
        }
        
        @media (max-width: 480px) {
            .products-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .product-actions {
                gap: 12px;
            }
            
            .quantity-controls {
                gap: 10px;
                padding: 6px 12px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <h1 class="logo"><?= sanitizeInput($settings['shop_name'] ?? 'MyShop') ?></h1>
                <button class="modern-cart-btn" id="cartBtn" onclick="toggleCartModal()">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-badge" id="cartCount">0</span>
                    <span id="cartTotal"><?= $settings['currency_symbol'] ?? '$' ?>0.00</span>
                </button>
            </div>
        </div>
    </header>

    <div class="promo-banner">
        <div class="promo-text"><?= sanitizeInput($settings['banner_text'] ?? '🎉 Free shipping on orders over $50! 📦') ?></div>
    </div>

    <div class="main-content">
        <div class="container">
            <?php if (!empty($categories)): ?>
            <div class="category-tabs" id="categoryTabs">
                <button class="tab-btn active" data-category="all">All Products</button>
                <?php foreach ($categories as $category): ?>
                <button class="tab-btn" data-category="<?= $category['slug'] ?>">
                    <?= sanitizeInput($category['name']) ?>
                </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="products-grid" id="productsGrid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                    <div class="product-card" data-category="<?= $product['category_slug'] ?>" data-product-id="<?= $product['id'] ?>">
                        <div class="product-image">
                            <?php if ($product['image']): ?>
                                <img src="uploads/<?= sanitizeInput($product['image']) ?>" alt="<?= sanitizeInput($product['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-name"><?= sanitizeInput($product['name']) ?></h3>
                            <p class="product-description"><?= sanitizeInput($product['short_description'] ?? substr($product['description'], 0, 100)) ?></p>
                            <div class="product-price"><?= formatPrice($product['price'], $settings['currency_symbol'] ?? '$') ?></div>
                            <div class="product-actions">
                                <div class="quantity-controls">
                                    <button class="qty-btn" onclick="updateQuantity(<?= $product['id'] ?>, -1)">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <span class="quantity" id="qty-<?= $product['id'] ?>">0</span>
                                    <button class="qty-btn" onclick="updateQuantity(<?= $product['id'] ?>, 1)">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-cart">
                        <i class="fas fa-box-open"></i>
                        <h3>No products available</h3>
                        <p>Check back soon for new arrivals!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="cartModal">
        <div class="cart-modal" onclick="event.stopPropagation()">
            <div class="cart-header">
                <h2><i class="fas fa-shopping-bag"></i> Shopping Cart</h2>
                <button class="close-btn" onclick="closeCartModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="cart-content" id="cartContent">
                <div class="empty-cart">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Your cart is empty</h3>
                    <p>Add some products to get started!</p>
                </div>
            </div>
            <div class="cart-footer">
                <div class="cart-total-display">Total: <span id="cartModalTotal"><?= $settings['currency_symbol'] ?? '$' ?>0.00</span></div>
                <div class="cart-actions">
                    <button class="btn-secondary" onclick="clearCart()">
                        <i class="fas fa-trash"></i> Clear Cart
                    </button>
                    <button class="btn-primary" onclick="shareOrderWhatsApp()" id="whatsappBtn">
                        <i class="fab fa-whatsapp"></i> Order via WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Shopping Cart System
        class ShoppingCart {
            constructor() {
                this.cart = [];
                this.products = <?= json_encode($products) ?>;
                this.settings = <?= json_encode($settings) ?>;
                this.currencySymbol = this.settings.currency_symbol || '$';
                this.whatsappNumber = this.settings.whatsapp_number || '+1234567890';
                
                this.loadCart();
                this.updateDisplay();
                this.initCategoryTabs();
            }
            
            findProduct(id) {
                return this.products.find(p => p.id == id);
            }
            
            addToCart(productId, quantity = 1) {
                const product = this.findProduct(productId);
                if (!product) return;
                
                const existingItem = this.cart.find(item => item.id == productId);
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
                this.updateDisplay();
                this.showNotification(`${product.name} added to cart!`);
            }
            
            updateQuantity(productId, change) {
                const qtyElement = document.getElementById(`qty-${productId}`);
                let currentQty = parseInt(qtyElement.textContent) || 0;
                let newQty = Math.max(0, currentQty + change);
                
                qtyElement.textContent = newQty;
                
                if (newQty === 0) {
                    this.removeFromCart(productId);
                } else if (newQty > currentQty) {
                    this.addToCart(productId, change);
                } else {
                    const cartItem = this.cart.find(item => item.id == productId);
                    if (cartItem) {
                        cartItem.quantity += change;
                        if (cartItem.quantity <= 0) {
                            this.removeFromCart(productId);
                        }
                    }
                }
                
                this.saveCart();
                this.updateDisplay();
                this.updateCartModal();
            }
            
            removeFromCart(productId) {
                this.cart = this.cart.filter(item => item.id != productId);
                
                const qtyElement = document.getElementById(`qty-${productId}`);
                if (qtyElement) {
                    qtyElement.textContent = '0';
                }
                
                this.saveCart();
                this.updateDisplay();
                this.updateCartModal();
            }
            
            clearCart() {
                if (confirm('Clear all items from cart?')) {
                    this.cart = [];
                    document.querySelectorAll('.quantity').forEach(el => {
                        el.textContent = '0';
                    });
                    this.saveCart();
                    this.updateDisplay();
                    this.updateCartModal();
                }
            }
            
            updateDisplay() {
                const cartCount = document.getElementById('cartCount');
                const cartTotal = document.getElementById('cartTotal');
                
                const totalItems = this.cart.reduce((sum, item) => sum + item.quantity, 0);
                const totalPrice = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                
                if (cartCount) cartCount.textContent = totalItems;
                if (cartTotal) cartTotal.textContent = this.formatPrice(totalPrice);
                
                // Update cart quantities on page
                this.cart.forEach(item => {
                    const qtyElement = document.getElementById(`qty-${item.id}`);
                    if (qtyElement) {
                        qtyElement.textContent = item.quantity;
                    }
                });
            }
            
            updateCartModal() {
                const cartContent = document.getElementById('cartContent');
                const cartModalTotal = document.getElementById('cartModalTotal');
                
                if (this.cart.length === 0) {
                    cartContent.innerHTML = `
                        <div class="empty-cart">
                            <i class="fas fa-shopping-cart"></i>
                            <h3>Your cart is empty</h3>
                            <p>Add some products to get started!</p>
                        </div>
                    `;
                } else {
                    const cartHTML = this.cart.map(item => `
                        <div class="cart-item">
                            <div class="cart-item-image">
                                ${item.image ? 
                                    `<img src="uploads/${item.image}" alt="${item.name}">` : 
                                    `<i class="fas fa-image"></i>`
                                }
                            </div>
                            <div class="cart-item-info">
                                <div class="cart-item-name">${item.name}</div>
                                <div class="cart-item-price">${this.formatPrice(item.price)}</div>
                            </div>
                            <div class="cart-item-controls">
                                <button class="cart-qty-btn" onclick="cart.updateCartItemQuantity(${item.id}, ${item.quantity - 1})">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <span class="cart-quantity">${item.quantity}</span>
                                <button class="cart-qty-btn" onclick="cart.updateCartItemQuantity(${item.id}, ${item.quantity + 1})">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button class="remove-btn" onclick="cart.removeFromCart(${item.id})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    `).join('');
                    
                    cartContent.innerHTML = cartHTML;
                }
                
                const totalPrice = this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                if (cartModalTotal) {
                    cartModalTotal.textContent = this.formatPrice(totalPrice);
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
                    this.updateDisplay();
                    this.updateCartModal();
                }
            }
            
            shareOrderWhatsApp() {
                if (this.cart.length === 0) {
                    alert('Your cart is empty!');
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
            
            formatPrice(price) {
                return this.currencySymbol + parseFloat(price || 0).toFixed(2);
            }
            
            showNotification(message) {
                const notification = document.createElement('div');
                notification.style.cssText = `
                    position: fixed; top: 20px; right: 20px; 
                    background: linear-gradient(135deg, #48bb78, #38a169); 
                    color: white; padding: 12px 20px; border-radius: 12px; 
                    z-index: 10001; font-weight: 600; box-shadow: 0 8px 25px rgba(72, 187, 120, 0.4);
                `;
                notification.textContent = message;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 3000);
            }
            
            saveCart() {
                try {
                    localStorage.setItem('ecommerce_cart', JSON.stringify(this.cart));
                } catch (e) {
                    console.warn('Unable to save cart to localStorage');
                }
            }
            
            loadCart() {
                try {
                    const savedCart = localStorage.getItem('ecommerce_cart');
                    if (savedCart) {
                        this.cart = JSON.parse(savedCart);
                    }
                } catch (e) {
                    this.cart = [];
                }
            }
            
            initCategoryTabs() {
                document.getElementById('categoryTabs').addEventListener('click', (e) => {
                    if (!e.target.classList.contains('tab-btn')) return;
                    
                    const category = e.target.dataset.category;
                    
                    // Update active tab
                    document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                    e.target.classList.add('active');
                    
                    // Filter products
                    document.querySelectorAll('.product-card').forEach(card => {
                        if (category === 'all' || card.dataset.category === category) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }
        }
        
        // Global functions
        function toggleCartModal() {
            const modal = document.getElementById('cartModal');
            const isActive = modal.classList.contains('active');
            
            if (isActive) {
                modal.classList.remove('active');
                document.body.style.overflow = '';
            } else {
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
                cart.updateCartModal();
            }
        }
        
        function closeCartModal() {
            const modal = document.getElementById('cartModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }
        
        function updateQuantity(productId, change) {
            cart.updateQuantity(productId, change);
        }
        
        function clearCart() {
            cart.clearCart();
        }
        
        function shareOrderWhatsApp() {
            cart.shareOrderWhatsApp();
        }
        
        // Close modal when clicking outside
        document.getElementById('cartModal').addEventListener('click', (e) => {
            if (e.target.id === 'cartModal') {
                closeCartModal();
            }
        });
        
        // Initialize cart system
        const cart = new ShoppingCart();
    </script>
</body>
</html>
