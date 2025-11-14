-- Single-file export for ecommerce_db
-- Drop and create the database, then create schema in dependency order.
DROP DATABASE IF EXISTS ecommerce_db;
CREATE DATABASE ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ecommerce_db;

-- USERS
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('buyer','seller','admin') NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- SHOPS (optional store table used by migrations)
CREATE TABLE IF NOT EXISTS shops (
    shop_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    logo VARCHAR(255),
    banner VARCHAR(255),
    description TEXT,
    shipping_policy TEXT,
    return_policy TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_shops_seller FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- SELLER PROFILES (app expects this table in some places)
CREATE TABLE IF NOT EXISTS seller_profiles (
    profile_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    shop_name VARCHAR(150) NOT NULL,
    logo VARCHAR(255),
    banner VARCHAR(255),
    description TEXT,
    shipping_policy TEXT,
    return_policy TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_seller (seller_id),
    CONSTRAINT fk_profile_seller FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- CATEGORIES
CREATE TABLE IF NOT EXISTS categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT
) ENGINE=InnoDB;

-- PRODUCTS
CREATE TABLE IF NOT EXISTS products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT,
    category_id INT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (seller_id),
    INDEX (category_id),
    CONSTRAINT fk_products_seller FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ORDERS
CREATE TABLE IF NOT EXISTS orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    payment_method VARCHAR(50),
    status ENUM('Pending','Confirmed','Shipped','Delivered','Cancelled') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (buyer_id),
    CONSTRAINT fk_orders_buyer FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ORDER ITEMS
CREATE TABLE IF NOT EXISTS order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    INDEX (order_id),
    INDEX (product_id),
    CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- REVIEWS
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    buyer_id INT,
    rating INT,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5),
    INDEX (product_id),
    INDEX (buyer_id),
    CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_reviews_buyer FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- CHAT MESSAGES
CREATE TABLE IF NOT EXISTS chat_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message TEXT NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (sender_id),
    INDEX (receiver_id),
    CONSTRAINT fk_chat_sender FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_chat_receiver FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- NOTIFICATIONS
CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- VOUCHERS (basic)
CREATE TABLE IF NOT EXISTS vouchers (
    voucher_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    description VARCHAR(255),
    discount_percent INT DEFAULT 0,
    active BOOLEAN DEFAULT TRUE,
    expires_at DATETIME NULL,
    created_by INT NULL,
    shop_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (created_by),
    INDEX (shop_id),
    CONSTRAINT fk_vouchers_user FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    CONSTRAINT fk_vouchers_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- COUPONS (also support linking to user/shop)
CREATE TABLE IF NOT EXISTS coupons (
    coupon_id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) UNIQUE NOT NULL,
    type ENUM('fixed','percent') NOT NULL DEFAULT 'fixed',
    value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    expires_at DATETIME NULL,
    max_uses INT DEFAULT 0,
    used_count INT DEFAULT 0,
    created_by INT NULL,
    shop_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (created_by),
    INDEX (shop_id),
    CONSTRAINT fk_coupons_user FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
    CONSTRAINT fk_coupons_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Add coupon tracking fields on orders (if not present will be ignored by MySQL import if already present)
ALTER TABLE orders
    ADD COLUMN coupon_id INT NULL,
    ADD COLUMN coupon_code VARCHAR(50) NULL,
    ADD COLUMN discount_amount DECIMAL(10,2) DEFAULT 0.00;

-- Add foreign key from orders.coupon_id -> coupons.coupon_id (safe attempt)
-- Note: MySQL/MariaDB do not support `IF NOT EXISTS` for ADD CONSTRAINT.
-- Run the ALTER below only if the constraint `fk_orders_coupon` does not already exist.
ALTER TABLE orders
    ADD CONSTRAINT fk_orders_coupon FOREIGN KEY (coupon_id) REFERENCES coupons(coupon_id) ON DELETE SET NULL;

-- Simple activity log (optional)
CREATE TABLE IF NOT EXISTS activity_logs (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(100),
    details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    CONSTRAINT fk_logs_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Default categories
INSERT INTO categories (name, description) VALUES
('Electronics','Phones, laptops, and gadgets'),
('Fashion','Clothes and accessories'),
('Home','Home and living essentials')
ON DUPLICATE KEY UPDATE description=VALUES(description);

-- Seed users (password is MD5('123456789'); app will upgrade to password_hash on first successful login)
INSERT INTO users (role, name, email, password, phone, address, status) VALUES
('admin','Westlie Casuncad','westragma@gmail.com', '25f9e794323b453885f5181f1b624d0b', NULL, NULL, 'approved'),
('seller','West Ragma','west@gmail.com', '25f9e794323b453885f5181f1b624d0b', NULL, NULL, 'pending'),
('buyer','Danhil Baluyot','dbaluyot@gmail.com', '25f9e794323b453885f5181f1b624d0b', NULL, NULL, 'approved')
ON DUPLICATE KEY UPDATE name=VALUES(name);

-- Minimal products for demo (assign to seller_id=2; can be inactive until approval)
INSERT INTO products (seller_id, category_id, name, description, price, stock, status)
VALUES
(2, 1, 'Wireless Earbuds', 'Comfortable earbuds with great sound', 29.99, 50, 'active'),
(2, 2, 'Pastel Hoodie', 'Cozy pastel hoodie with soft fabric', 39.90, 25, 'active')
ON DUPLICATE KEY UPDATE price=VALUES(price), stock=VALUES(stock);

-- Helpful note: This single-file export contains shops, seller_profiles, coupons and vouchers to
-- remain compatible with both older migration logic and current app code. Import this file
-- via phpMyAdmin or the MySQL client. If you encounter DELIMITER/procedure issues, run the
-- `tools/migrate.php` script which handles DELIMITER blocks safely.
