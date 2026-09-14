-- ============================================================
--  DETECH E-Commerce Database
--  MySQL 5.7+ / MariaDB 10.3+
--  Usage: mysql -u root -p detech < database.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS detech CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE detech;

-- ============================================================
-- USERS
-- ============================================================
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120) NOT NULL,
    email       VARCHAR(180) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    phone       VARCHAR(30)  DEFAULT '',
    avatar      VARCHAR(500) DEFAULT '',
    role        ENUM('user','admin') DEFAULT 'user',
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin account  (password: admin123)
INSERT INTO users (name, email, password, role) VALUES
('Admin', 'admin@detech.pk', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- ============================================================
-- PRODUCTS
-- ============================================================
DROP TABLE IF EXISTS products;
CREATE TABLE products (
    id               VARCHAR(20)  PRIMARY KEY,
    name             VARCHAR(250) NOT NULL,
    brand            VARCHAR(80)  NOT NULL,
    category         VARCHAR(80)  NOT NULL,
    price            INT          NOT NULL,
    original_price   INT          NOT NULL,
    discount         TINYINT      DEFAULT 0,
    image            VARCHAR(500) NOT NULL,
    rating           DECIMAL(3,1) DEFAULT 0,
    review_count     INT          DEFAULT 0,
    stock            INT          DEFAULT 0,
    sold             INT          DEFAULT 0,
    description      TEXT,
    is_flash_sale    TINYINT(1)   DEFAULT 0,
    flash_sale_price INT          DEFAULT NULL,
    flash_sale_stock INT          DEFAULT NULL,
    flash_sale_sold  INT          DEFAULT 0,
    is_cod           TINYINT(1)   DEFAULT 0,
    is_best_seller   TINYINT(1)   DEFAULT 0,
    is_featured      TINYINT(1)   DEFAULT 0,
    is_trending      TINYINT(1)   DEFAULT 0,
    delivery_days    TINYINT      DEFAULT 3,
    created_at       DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- PRODUCT IMAGES  (multiple images per product)
-- ============================================================
DROP TABLE IF EXISTS product_images;
CREATE TABLE product_images (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  VARCHAR(20) NOT NULL,
    image_url   VARCHAR(500) NOT NULL,
    sort_order  TINYINT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- PRODUCT SPECS
-- ============================================================
DROP TABLE IF EXISTS product_specs;
CREATE TABLE product_specs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  VARCHAR(20)  NOT NULL,
    spec_key    VARCHAR(80)  NOT NULL,
    spec_value  VARCHAR(250) NOT NULL,
    sort_order  TINYINT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- PRODUCT TAGS
-- ============================================================
DROP TABLE IF EXISTS product_tags;
CREATE TABLE product_tags (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  VARCHAR(20) NOT NULL,
    tag         VARCHAR(80) NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- CATEGORIES
-- ============================================================
DROP TABLE IF EXISTS categories;
CREATE TABLE categories (
    id          VARCHAR(40) PRIMARY KEY,
    name        VARCHAR(80) NOT NULL,
    icon        VARCHAR(10) NOT NULL,
    sort_order  TINYINT DEFAULT 0
) ENGINE=InnoDB;

INSERT INTO categories (id, name, icon, sort_order) VALUES
('smartphones',  'Smartphones',   '📱', 1),
('laptops',      'Laptops',       '💻', 2),
('headphones',   'Headphones',    '🎧', 3),
('tablets',      'Tablets',       '📟', 4),
('gaming',       'Gaming',        '🎮', 5),
('accessories',  'Accessories',   '🔌', 6),
('smartwatches', 'Smart Watches', '⌚', 7),
('cameras',      'Cameras',       '📷', 8);

-- ============================================================
-- ORDERS
-- ============================================================
DROP TABLE IF EXISTS orders;
CREATE TABLE orders (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_key       VARCHAR(30)  NOT NULL UNIQUE,
    user_id         INT UNSIGNED DEFAULT NULL,
    guest_email     VARCHAR(180) DEFAULT NULL,
    total           INT          NOT NULL,
    delivery_fee    INT          DEFAULT 0,
    coupon_code     VARCHAR(30)  DEFAULT NULL,
    coupon_discount INT          DEFAULT 0,
    address         TEXT         NOT NULL,
    payment_method  ENUM('cod','card','easypaisa') DEFAULT 'cod',
    status          ENUM('confirmed','processing','shipped','out_for_delivery','delivered','cancelled') DEFAULT 'confirmed',
    tracking_id     VARCHAR(30)  DEFAULT NULL,
    notes           TEXT         DEFAULT NULL,
    created_at      DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- ORDER ITEMS
-- ============================================================
DROP TABLE IF EXISTS order_items;
CREATE TABLE order_items (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id        INT UNSIGNED NOT NULL,
    product_id      VARCHAR(20)  NOT NULL,
    product_name    VARCHAR(250) NOT NULL,
    product_image   VARCHAR(500) NOT NULL,
    product_brand   VARCHAR(80)  NOT NULL,
    price           INT          NOT NULL,
    quantity        INT          NOT NULL DEFAULT 1,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- CART  (server-side cart linked to session or user)
-- ============================================================
DROP TABLE IF EXISTS cart;
CREATE TABLE cart (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED DEFAULT NULL,
    session_id  VARCHAR(120) DEFAULT NULL,
    product_id  VARCHAR(20)  NOT NULL,
    quantity    INT          NOT NULL DEFAULT 1,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cart_item (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- WISHLIST
-- ============================================================
DROP TABLE IF EXISTS wishlist;
CREATE TABLE wishlist (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id     INT UNSIGNED NOT NULL,
    product_id  VARCHAR(20)  NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_wishlist (user_id, product_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- REVIEWS
-- ============================================================
DROP TABLE IF EXISTS reviews;
CREATE TABLE reviews (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id  VARCHAR(20)  NOT NULL,
    user_id     INT UNSIGNED DEFAULT NULL,
    reviewer    VARCHAR(100) NOT NULL,
    rating      TINYINT      NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE SET NULL
) ENGINE=InnoDB;

-- ============================================================
-- COUPONS
-- ============================================================
DROP TABLE IF EXISTS coupons;
CREATE TABLE coupons (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code        VARCHAR(30)  NOT NULL UNIQUE,
    discount    TINYINT      NOT NULL,
    type        ENUM('percent','fixed') DEFAULT 'percent',
    min_order   INT          DEFAULT 0,
    max_uses    INT          DEFAULT NULL,
    used_count  INT          DEFAULT 0,
    expires_at  DATE         DEFAULT NULL,
    is_active   TINYINT(1)   DEFAULT 1,
    created_at  DATETIME     DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO coupons (code, discount, type) VALUES
('DETECH10', 10, 'percent'),
('SAVE20',   20, 'percent'),
('FLASH15',  15, 'percent'),
('WELCOME5',  5, 'percent');

-- ============================================================
-- NEWSLETTER SUBSCRIBERS
-- ============================================================
DROP TABLE IF EXISTS newsletter;
CREATE TABLE newsletter (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(180) NOT NULL UNIQUE,
    subscribed TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- SEED: PRODUCTS
-- ============================================================
INSERT INTO products (id, name, brand, category, price, original_price, discount, image, rating, review_count, stock, sold, description, is_flash_sale, flash_sale_price, flash_sale_stock, flash_sale_sold, is_cod, is_best_seller, is_featured, is_trending, delivery_days) VALUES
('p1',  'iPhone 15 Pro Max 256GB',      'Apple',   'smartphones', 189999, 219999, 14, 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format', 4.9, 2847, 23,  1204, 'The most powerful iPhone ever. A17 Pro chip, titanium design, and pro camera system that captures stunning detail.', 1, 169999, 50, 34, 1, 1, 1, 1, 2),
('p2',  'Samsung Galaxy S24 Ultra',     'Samsung', 'smartphones', 179999, 209999, 14, 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=600&auto=format', 4.8, 1923, 45,   876, 'Galaxy AI is here. 200MP camera, embedded S Pen, and Snapdragon 8 Gen 3.',                                           1, 159999, 30, 18, 1, 1, 1, 1, 2),
('p3',  'OnePlus 12 5G 256GB',          'OnePlus', 'smartphones',  64999,  74999, 13, 'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=600&auto=format', 4.7, 1102, 67,   543, 'Flagship performance. Snapdragon 8 Gen 3 with Hasselblad camera system.',                                              0,   NULL, NULL, 0, 1, 0, 1, 1, 3),
('p4',  'Xiaomi 14 Ultra 5G',           'Xiaomi',  'smartphones',  89999,  99999, 10, 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format', 4.6,  834, 30,   290, 'Leica professional camera system meets flagship performance.',                                                           0,   NULL, NULL, 0, 0, 0, 0, 1, 4),
('p5',  'MacBook Pro 14" M3 Pro',       'Apple',   'laptops',     249999, 279999, 11, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&auto=format', 4.9, 1547, 12,   432, 'Supercharged by M3 Pro with 18-hour battery life and Liquid Retina XDR display.',                                      1, 229999, 15,  9, 0, 1, 1, 1, 3),
('p6',  'Dell XPS 15 Core i9',          'Dell',    'laptops',     189999, 219999, 14, 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=600&auto=format', 4.7,  892, 18,   267, '15.6" 3.5K OLED Touch display with Intel Core i9 and RTX 4060.',                                                       0,   NULL, NULL, 0, 0, 1, 1, 0, 4),
('p7',  'ASUS ROG Zephyrus G16',        'Asus',    'laptops',     219999, 249999, 12, 'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=600&auto=format', 4.8,  673,  8,   198, 'RTX 4090 in ultra-slim chassis with 240Hz QHD+ OLED display.',                                                          1, 199999, 10,  6, 0, 0, 1, 1, 5),
('p8',  'Sony WH-1000XM5 ANC',          'Sony',    'headphones',   29999,  34999, 14, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format', 4.9, 3421, 89,  2134, 'Industry-leading noise canceling. 30-hour battery with USB-C fast charge.',                                            1,  24999,100, 73, 1, 1, 1, 1, 2),
('p9',  'AirPods Pro 2nd Gen',          'Apple',   'headphones',   24999,  27999, 11, 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=600&auto=format', 4.8, 2198,120,  1876, 'Personalized Spatial Audio and Adaptive Transparency with H2 chip.',                                                   0,   NULL, NULL, 0, 1, 1, 0, 1, 2),
('p10', 'Bose QuietComfort 45',         'Bose',    'headphones',   27999,  32999, 15, 'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=600&auto=format', 4.7, 1456, 54,   876, 'Legendary Bose noise canceling. 24-hour battery, all-day comfort.',                                                     0,   NULL, NULL, 0, 1, 0, 0, 0, 3),
('p11', 'iPad Pro 12.9" M2',            'Apple',   'tablets',     129999, 149999, 13, 'https://images.unsplash.com/photo-1561154464-82e9adf32764?w=600&auto=format', 4.9,  987, 28,   432, 'Apple M2 chip with Liquid Retina XDR display and ProMotion technology.',                                                0,   NULL, NULL, 0, 0, 1, 1, 0, 3),
('p12', 'Samsung Galaxy Tab S9 Ultra',  'Samsung', 'tablets',      99999, 119999, 17, 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format', 4.7,  654, 35,   212, '14.6" Dynamic AMOLED display with S Pen included.',                                                                      0,   NULL, NULL, 0, 0, 0, 1, 0, 4),
('p13', 'Razer DeathAdder V3 Pro',      'Razer',   'gaming',       12999,  15999, 19, 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=600&auto=format', 4.8, 2341,150,   987, 'Ultra-lightweight wireless gaming mouse with Focus Pro 30K sensor.',                                                    0,   NULL, NULL, 0, 1, 1, 1, 1, 2),
('p14', 'Sony WF-1000XM5 Earbuds',      'Sony',    'headphones',   19999,  24999, 20, 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=600&auto=format', 4.8, 1876, 78,  1234, 'Best-in-class noise canceling earbuds with Integrated Processor V2.',                                                   1,  16999, 80, 52, 1, 1, 1, 1, 2),
('p15', 'Apple Watch Series 9 GPS',     'Apple',   'smartwatches', 49999,  57999, 14, 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format', 4.8, 3241, 67,  1876, 'Double Tap gesture, S9 chip, Always-On Retina display, 18-hour battery.',                                              0,   NULL, NULL, 0, 1, 1, 1, 1, 2),
('p16', 'Canon EOS R50 Mirrorless',     'Canon',   'cameras',      79999,  94999, 16, 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&auto=format', 4.7,  543, 22,   134, '24.2MP APS-C, 4K video, Dual Pixel CMOS AF II — perfect for vloggers.',                                               0,   NULL, NULL, 0, 0, 0, 1, 0, 5),
('p17', 'Logitech MX Master 3S',        'Logitech','accessories',   8999,  10999, 18, 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=600&auto=format', 4.8, 4521,200,  3421, 'Silent clicks, MagSpeed scroll, 8000 DPI Darkfield sensor. Works on glass.',                                           0,   NULL, NULL, 0, 1, 1, 0, 1, 2),
('p18', 'Samsung Galaxy Watch 6 Classic','Samsung','smartwatches', 39999,  47999, 17, 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=600&auto=format', 4.6, 1234, 45,   678, 'Classic rotating bezel with ECG, Blood Pressure, and BIA sensor.',                                                     0,   NULL, NULL, 0, 1, 0, 1, 0, 3);

-- ============================================================
-- SEED: PRODUCT IMAGES
-- ============================================================
INSERT INTO product_images (product_id, image_url, sort_order) VALUES
('p1',  'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format', 0),
('p1',  'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=600&auto=format', 1),
('p1',  'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=600&auto=format', 2),
('p2',  'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=600&auto=format', 0),
('p2',  'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format', 1),
('p2',  'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=600&auto=format', 2),
('p3',  'https://images.unsplash.com/photo-1565849904461-04a58ad377e0?w=600&auto=format', 0),
('p3',  'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format', 1),
('p4',  'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format', 0),
('p4',  'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?w=600&auto=format', 1),
('p5',  'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&auto=format', 0),
('p5',  'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=600&auto=format', 1),
('p5',  'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=600&auto=format', 2),
('p6',  'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?w=600&auto=format', 0),
('p6',  'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&auto=format', 1),
('p7',  'https://images.unsplash.com/photo-1593642632559-0c6d3fc62b89?w=600&auto=format', 0),
('p7',  'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&auto=format', 1),
('p8',  'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format', 0),
('p8',  'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=600&auto=format', 1),
('p8',  'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=600&auto=format', 2),
('p9',  'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=600&auto=format', 0),
('p9',  'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format', 1),
('p10', 'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=600&auto=format', 0),
('p10', 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format', 1),
('p11', 'https://images.unsplash.com/photo-1561154464-82e9adf32764?w=600&auto=format', 0),
('p11', 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format', 1),
('p12', 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?w=600&auto=format', 0),
('p12', 'https://images.unsplash.com/photo-1561154464-82e9adf32764?w=600&auto=format', 1),
('p13', 'https://images.unsplash.com/photo-1542751371-adc38448a05e?w=600&auto=format', 0),
('p13', 'https://images.unsplash.com/photo-1586182987320-4f376d39d787?w=600&auto=format', 1),
('p14', 'https://images.unsplash.com/photo-1546435770-a3e426bf472b?w=600&auto=format', 0),
('p14', 'https://images.unsplash.com/photo-1484704849700-f032a568e944?w=600&auto=format', 1),
('p15', 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format', 0),
('p15', 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=600&auto=format', 1),
('p16', 'https://images.unsplash.com/photo-1516035069371-29a1b244cc32?w=600&auto=format', 0),
('p16', 'https://images.unsplash.com/photo-1502920917128-1aa500764cbd?w=600&auto=format', 1),
('p17', 'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=600&auto=format', 0),
('p17', 'https://images.unsplash.com/photo-1585386959984-a4155224a1ad?w=600&auto=format', 1),
('p18', 'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=600&auto=format', 0),
('p18', 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&auto=format', 1);

-- ============================================================
-- SEED: PRODUCT SPECS
-- ============================================================
INSERT INTO product_specs (product_id, spec_key, spec_value, sort_order) VALUES
('p1','Display','6.7" Super Retina XDR OLED',0),('p1','Processor','Apple A17 Pro',1),('p1','RAM','8GB',2),('p1','Storage','256GB',3),('p1','Camera','48MP + 12MP + 12MP Triple',4),('p1','Battery','4422 mAh',5),('p1','OS','iOS 17',6),('p1','5G','Yes',7),
('p2','Display','6.8" Dynamic AMOLED 2X 120Hz',0),('p2','Processor','Snapdragon 8 Gen 3',1),('p2','RAM','12GB',2),('p2','Storage','256GB',3),('p2','Camera','200MP + 12MP + 50MP + 10MP',4),('p2','Battery','5000 mAh',5),('p2','OS','Android 14',6),('p2','5G','Yes',7),
('p3','Display','6.82" LTPO AMOLED 120Hz',0),('p3','Processor','Snapdragon 8 Gen 3',1),('p3','RAM','12GB',2),('p3','Storage','256GB',3),('p3','Camera','50MP + 48MP + 64MP Hasselblad',4),('p3','Battery','5400 mAh',5),('p3','OS','OxygenOS 14',6),('p3','5G','Yes',7),
('p4','Display','6.73" LTPO AMOLED 2K 120Hz',0),('p4','Processor','Snapdragon 8 Gen 3',1),('p4','RAM','16GB',2),('p4','Storage','512GB',3),('p4','Camera','50MP Leica + 50MP + 50MP',4),('p4','Battery','5000 mAh 90W',5),('p4','OS','MIUI 14',6),('p4','5G','Yes',7),
('p5','Display','14.2" Liquid Retina XDR',0),('p5','Processor','Apple M3 Pro 11-core',1),('p5','RAM','18GB Unified Memory',2),('p5','Storage','512GB SSD',3),('p5','Graphics','M3 Pro 14-core GPU',4),('p5','Battery','18 hours',5),('p5','OS','macOS Sonoma',6),('p5','Weight','1.61 kg',7),
('p6','Display','15.6" 3.5K OLED Touch',0),('p6','Processor','Intel Core i9-13900H',1),('p6','RAM','32GB DDR5',2),('p6','Storage','1TB NVMe SSD',3),('p6','Graphics','NVIDIA RTX 4060 8GB',4),('p6','Battery','86Wh',5),('p6','OS','Windows 11 Pro',6),('p6','Weight','1.86 kg',7),
('p7','Display','16" QHD+ OLED 240Hz',0),('p7','Processor','AMD Ryzen 9 8945HS',1),('p7','RAM','32GB DDR5',2),('p7','Storage','2TB SSD',3),('p7','Graphics','NVIDIA RTX 4090 16GB',4),('p7','Battery','90Wh',5),('p7','OS','Windows 11 Home',6),('p7','Weight','1.85 kg',7),
('p8','Type','Over-ear Wireless',0),('p8','Driver','30mm Dynamic',1),('p8','ANC','Industry Leading',2),('p8','Battery','30 hours',3),('p8','Charging','USB-C Fast Charge',4),('p8','Connectivity','Bluetooth 5.2',5),('p8','Weight','250g',6),
('p9','Type','In-ear True Wireless',0),('p9','ANC','Adaptive Transparency',1),('p9','Battery','6hr + 24hr case',2),('p9','Charging','Lightning / MagSafe',3),('p9','Chip','Apple H2',4),('p9','Water Resistant','IPX4',5),('p9','Audio','Spatial Audio',6),
('p10','Type','Over-ear Wireless',0),('p10','ANC','QuietComfort Technology',1),('p10','Battery','24 hours',2),('p10','Charging','USB-C',3),('p10','Connectivity','Bluetooth 5.1',4),('p10','Weight','238g',5),
('p11','Display','12.9" Liquid Retina XDR',0),('p11','Processor','Apple M2',1),('p11','RAM','8GB',2),('p11','Storage','256GB',3),('p11','Camera','12MP Wide + 10MP Ultra Wide',4),('p11','Battery','10 hours',5),('p11','Connectivity','WiFi 6E + 5G',6),('p11','OS','iPadOS 17',7),
('p12','Display','14.6" Dynamic AMOLED 120Hz',0),('p12','Processor','Snapdragon 8 Gen 2',1),('p12','RAM','12GB',2),('p12','Storage','256GB',3),('p12','Camera','13MP + 8MP',4),('p12','Battery','11200 mAh',5),('p12','OS','Android 13',6),('p12','S Pen','Included',7),
('p13','Sensor','Focus Pro 30K Optical',0),('p13','DPI','100 - 30,000',1),('p13','Buttons','6 Programmable',2),('p13','Battery','90 hours',3),('p13','Weight','64g',4),('p13','Connectivity','HyperSpeed Wireless',5),('p13','Polling Rate','8000Hz',6),
('p14','Type','In-ear True Wireless',0),('p14','ANC','Auto NC Optimizer',1),('p14','Battery','8hr + 24hr case',2),('p14','Driver','8.4mm Dynamic',3),('p14','Chip','Integrated Processor V2',4),('p14','Water Resistant','IPX4',5),
('p15','Display','45mm Always-On Retina',0),('p15','Chip','Apple S9',1),('p15','Storage','64GB',2),('p15','Health','Blood Oxygen, ECG, Temperature',3),('p15','Battery','18 hours',4),('p15','Water Resistant','50m',5),('p15','Connectivity','GPS + Cellular',6),
('p16','Sensor','24.2MP APS-C CMOS',0),('p16','Video','4K 30fps / 1080p 120fps',1),('p16','AF','Dual Pixel CMOS AF II',2),('p16','ISO','100-32000',3),('p16','Display','3" Vari-Angle LCD Touch',4),('p16','Connectivity','WiFi + Bluetooth',5),('p16','Weight','375g',6),
('p17','Sensor','8000 DPI Darkfield',0),('p17','Connectivity','Bluetooth + USB Receiver',1),('p17','Battery','70 days',2),('p17','Buttons','7 buttons',3),('p17','Scroll','MagSpeed Electromagnetic',4),('p17','Compatibility','Windows, Mac, Linux',5),
('p18','Display','47mm Super AMOLED',0),('p18','Processor','Exynos W930',1),('p18','Storage','16GB',2),('p18','Health','BIA Sensor, ECG, Blood Pressure',3),('p18','Battery','40 hours',4),('p18','Water Resistant','5ATM',5),('p18','OS','Wear OS + One UI Watch 5',6);

-- ============================================================
-- SEED: PRODUCT TAGS
-- ============================================================
INSERT INTO product_tags (product_id, tag) VALUES
('p1','5G'),('p1','Pro Camera'),('p1','Titanium'),
('p2','S Pen'),('p2','AI Camera'),('p2','200MP'),
('p3','Hasselblad'),('p3','Fast Charging'),('p3','5G'),
('p4','Leica'),('p4','Pro Camera'),('p4','90W Charging'),
('p5','M3 Pro'),('p5','Retina XDR'),('p5','18hr Battery'),
('p6','OLED'),('p6','Core i9'),('p6','RTX 4060'),
('p7','RTX 4090'),('p7','240Hz OLED'),('p7','Gaming'),
('p8','ANC'),('p8','Hi-Res'),('p8','LDAC'),
('p9','Spatial Audio'),('p9','ANC'),('p9','H2 Chip'),
('p10','QuietComfort'),('p10','ANC'),('p10','24hr Battery'),
('p11','M2 Chip'),('p11','ProMotion'),('p11','Apple Pencil'),
('p12','S Pen Included'),('p12','DeX Mode'),('p12','AMOLED'),
('p13','Wireless'),('p13','30K DPI'),('p13','Ultra-Light'),
('p14','Best ANC Earbuds'),('p14','Hi-Res'),('p14','LDAC'),
('p15','Double Tap'),('p15','Health Monitor'),('p15','Always-On'),
('p16','4K Video'),('p16','Mirrorless'),('p16','Vlog Camera'),
('p17','Silent Clicks'),('p17','MagSpeed Scroll'),('p17','Multi-Device'),
('p18','Rotating Bezel'),('p18','Health Monitor'),('p18','Classic Design');

-- ============================================================
-- SEED: SAMPLE REVIEWS
-- ============================================================
INSERT INTO reviews (product_id, reviewer, rating, comment) VALUES
('p1','Ahmad R.',5,'Absolutely love this product! Exceeded my expectations. A17 Pro is lightning fast.'),
('p1','Sara M.',5,'Genuine product, fast delivery. The camera system is incredible.'),
('p1','Bilal K.',4,'Great value for money. Packaging was excellent. Titanium finish looks premium.'),
('p2','Usman T.',5,'Galaxy AI features are amazing. S Pen is super useful. Best Android phone!'),
('p5','Ayesha N.',5,'MacBook M3 Pro is a beast. 18-hour battery life is real. Absolutely love it.'),
('p8','Zain A.',5,'Best noise canceling headphones I have ever used. Sony delivered perfection.');

-- ============================================================
-- SEED: SAMPLE ORDERS (demo data)
-- ============================================================
INSERT INTO orders (order_key, total, address, payment_method, status, tracking_id, created_at) VALUES
('ORD-2024-100001', 194999, 'Demo User, +92 300 0000001, 123 Main Street, Karachi', 'cod',  'delivered',   'TRK9A1B2C3D4', '2024-01-10 10:30:00'),
('ORD-2024-100002', 229999, 'Demo User, +92 300 0000002, 456 Garden Town, Lahore',  'card', 'shipped',     'TRK5E6F7G8H9', '2024-01-15 14:20:00'),
('ORD-2024-100003',  29999, 'Demo User, +92 300 0000003, 789 F-10 Sector, Islamabad','cod', 'confirmed',   'TRKABC123DEF', '2024-01-18 09:15:00');

INSERT INTO order_items (order_id, product_id, product_name, product_image, product_brand, price, quantity) VALUES
(1, 'p1',  'iPhone 15 Pro Max 256GB',  'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?w=600&auto=format', 'Apple',  169999, 1),
(1, 'p17', 'Logitech MX Master 3S',    'https://images.unsplash.com/photo-1491553895911-0055eca6402d?w=600&auto=format', 'Logitech', 8999, 1),
(2, 'p5',  'MacBook Pro 14" M3 Pro',   'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=600&auto=format', 'Apple',  229999, 1),
(3, 'p8',  'Sony WH-1000XM5 ANC',      'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&auto=format', 'Sony',    24999, 1);

-- ============================================================
-- USEFUL VIEWS
-- ============================================================
CREATE OR REPLACE VIEW v_flash_sale_products AS
SELECT p.*, COUNT(pi.id) AS image_count
FROM products p
LEFT JOIN product_images pi ON p.id = pi.product_id
WHERE p.is_flash_sale = 1
GROUP BY p.id;

CREATE OR REPLACE VIEW v_order_summary AS
SELECT
    o.id, o.order_key, o.total, o.status, o.payment_method,
    o.tracking_id, o.created_at,
    u.name AS customer_name, u.email AS customer_email,
    COUNT(oi.id) AS item_count
FROM orders o
LEFT JOIN users u ON o.user_id = u.id
LEFT JOIN order_items oi ON o.id = oi.order_id
GROUP BY o.id;

-- ============================================================
-- INDEXES for performance
-- ============================================================
CREATE INDEX idx_products_category    ON products(category);
CREATE INDEX idx_products_brand       ON products(brand);
CREATE INDEX idx_products_flash_sale  ON products(is_flash_sale);
CREATE INDEX idx_products_trending    ON products(is_trending);
CREATE INDEX idx_orders_user          ON orders(user_id);
CREATE INDEX idx_orders_status        ON orders(status);
CREATE INDEX idx_order_items_order    ON order_items(order_id);
CREATE INDEX idx_cart_user            ON cart(user_id);
CREATE INDEX idx_wishlist_user        ON wishlist(user_id);

-- ============================================================
-- Done!
-- ============================================================
SELECT 'DETECH database installed successfully!' AS message;
SELECT COUNT(*) AS total_products FROM products;
SELECT COUNT(*) AS total_categories FROM categories;
SELECT COUNT(*) AS total_coupons FROM coupons;
