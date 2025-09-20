CREATE DATABASE IF NOT EXISTS `ecommerce_shop` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ecommerce_shop`;

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `short_description` varchar(500) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_quantity` int(11) NOT NULL DEFAULT 0,
  `image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive','draft') NOT NULL DEFAULT 'active',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) NOT NULL UNIQUE,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `customer_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB;

INSERT INTO `admin_users` (`username`, `password_hash`, `email`) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@myshop.com');

INSERT INTO `categories` (`name`, `slug`, `description`, `display_order`) VALUES
('Electronics', 'electronics', 'Latest gadgets and electronic devices', 1),
('Clothing', 'clothing', 'Fashion and apparel for all ages', 2),
('Home & Garden', 'home-garden', 'Home improvement and gardening supplies', 3),
('Sports & Outdoors', 'sports-outdoors', 'Sports equipment and outdoor gear', 4);

INSERT INTO `products` (`category_id`, `name`, `slug`, `description`, `short_description`, `price`, `stock_quantity`) VALUES
(1, 'Wireless Bluetooth Headphones', 'wireless-bluetooth-headphones', 'Premium quality wireless headphones with noise cancellation', 'Premium wireless headphones with noise cancellation', 89.99, 25),
(2, 'Organic Cotton T-Shirt', 'organic-cotton-t-shirt', 'Comfortable eco-friendly t-shirt made from 100% organic cotton', 'Eco-friendly organic cotton t-shirt', 29.99, 50),
(3, 'Stainless Steel Water Bottle', 'stainless-steel-water-bottle', 'Insulated water bottle that keeps drinks cold for 24 hours', 'Insulated stainless steel water bottle', 24.99, 30),
(1, 'Gaming Mechanical Keyboard', 'gaming-mechanical-keyboard', 'RGB backlit mechanical keyboard with customizable keys', 'RGB mechanical keyboard for gaming', 129.99, 15);

INSERT INTO `settings` (`key`, `value`) VALUES
('shop_name', 'MyShop'),
('currency', 'USD'),
('currency_symbol', '$'),
('whatsapp_number', '+1234567890'),
('banner_text', '🎉 Free shipping on orders over $50! 📦'),
('primary_color', '#007bff'),
('secondary_color', '#6c757d');