-- =============================================
-- JEWELLERY SHOP - MINIMAL LOGIN SCHEMA
-- =============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS shop_onboarding;
DROP TABLE IF EXISTS saas_users;
DROP TABLE IF EXISTS product_stock_history;
DROP TABLE IF EXISTS merchant_ledger;
DROP TABLE IF EXISTS merchants;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS shops;
DROP TABLE IF EXISTS products;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================
-- SAAS USERS (Control-plane users)
-- =============================================
CREATE TABLE saas_users (
    saas_user_id INT AUTO_INCREMENT PRIMARY KEY,
    reference_code CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin','onboarding_admin','support') NOT NULL DEFAULT 'onboarding_admin',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO saas_users (name, email, password_hash, role, is_active)
VALUES
('SaaS Admin', 'admin@saas.local', '$2y$12$rouSEK7XMLcJT/h.dnh3FuWIwYf/aNz43mRxkjfPxAbqRigWFf1hW', 'super_admin', TRUE);

-- =============================================
-- SHOP
-- =============================================
CREATE TABLE shops (
    shop_id INT AUTO_INCREMENT PRIMARY KEY,
    reference_code CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    shop_name VARCHAR(255) NOT NULL,
    owner_name VARCHAR(255) NULL,
    mobile_no VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    logo VARCHAR(500) NULL,
    banner VARCHAR(500) NULL,
    street VARCHAR(255) NULL,
    city VARCHAR(100) NULL,
    state_code VARCHAR(10) NULL,
    state_name VARCHAR(100) NULL,
    country VARCHAR(100) NULL,
    pincode VARCHAR(20) NULL,
    address TEXT NULL,
    gstin VARCHAR(30) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

-- =============================================
-- SHOP ONBOARDING (Lead -> Approved tenant)
-- =============================================
CREATE TABLE shop_onboarding (
    onboarding_id INT AUTO_INCREMENT PRIMARY KEY,
    reference_code CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    proposed_shop_name VARCHAR(255) NOT NULL,
    owner_name VARCHAR(255) NOT NULL,
    owner_email VARCHAR(255) NOT NULL,
    owner_mobile VARCHAR(20) NULL,
    city VARCHAR(100) NULL,
    state_name VARCHAR(100) NULL,
    country VARCHAR(100) NULL,
    gstin VARCHAR(30) NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    onboarding_notes TEXT NULL,
    created_shop_id INT NULL,
    created_by_saas_user INT NULL,
    approved_by_saas_user INT NULL,
    approved_at DATETIME NULL,
    rejected_at DATETIME NULL,
    rejection_reason VARCHAR(500) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_onboarding_shop FOREIGN KEY (created_shop_id) REFERENCES shops(shop_id),
    CONSTRAINT fk_onboarding_created_by FOREIGN KEY (created_by_saas_user) REFERENCES saas_users(saas_user_id),
    CONSTRAINT fk_onboarding_approved_by FOREIGN KEY (approved_by_saas_user) REFERENCES saas_users(saas_user_id),
    INDEX idx_onboarding_status_created (status, created_at),
    INDEX idx_onboarding_owner_email (owner_email)
);

-- =============================================
-- BASIC SEED DATA - SHOP & USERS ONLY
-- =============================================
INSERT INTO shops (shop_name, owner_name, mobile_no, email, logo, banner, street, city, state_code, state_name, country, pincode, address, gstin)
VALUES (
    'HARINI JEWELLERS',
    'Vijayakumar',
    '9876543210',
    'harinijewelers@gmail.com',
    '/assets/images/harini-logo.png',
    '/assets/images/harini-banner.png',
    '1/2E, SRI Raa Complex 1st Floor, Mettukamala Street, South Avani Moola Street',
    'Madurai',
    '33',
    'Tamil Nadu',
    'India',
    '625001',
    '1/2E, SRI Raa Complex 1st Floor, Mettukamala Street, South Avani Moola Street, Madurai - 625001, Tamil Nadu',
    '33AAHFH3055M1ZB'
);
-- =============================================
-- LOGIN USERS (compatible with current Auth.php)
-- required columns: reference_code, name, email, password_hash, user_type, is_active
-- =============================================
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    reference_code CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    shop_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    mobileno VARCHAR(20) NULL UNIQUE,
    profile_image VARCHAR(500) NULL,
    id_proof_type ENUM('aadhaar','pan','voter_id','driving_license','passport','other') NULL,
    id_proof_number VARCHAR(100) NULL,
    id_proof_front_image VARCHAR(500) NULL,
    id_proof_back_image VARCHAR(500) NULL,
    user_type ENUM('owner','manager','staff') NOT NULL DEFAULT 'staff',
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    last_login_at DATETIME NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id)
);

-- =============================================
-- BASIC SEED DATA - USERS ONLY
-- =============================================

INSERT INTO users (shop_id, name, email, password_hash, mobileno, user_type, is_active)
VALUES
(1, 'Vijayakumar', 'owner@harinijewellers.com', '$2y$12$rouSEK7XMLcJT/h.dnh3FuWIwYf/aNz43mRxkjfPxAbqRigWFf1hW', '8220466675', 'owner', TRUE),
(1, 'Nadhiya', 'manager@harinijewellers.com', '$2y$12$rouSEK7XMLcJT/h.dnh3FuWIwYf/aNz43mRxkjfPxAbqRigWFf1hW', '8220466001', 'manager', TRUE),
(1, 'Vinoth', 'vinoth@harinijewellers.com', '$2y$12$rouSEK7XMLcJT/h.dnh3FuWIwYf/aNz43mRxkjfPxAbqRigWFf1hW', '8220466002', 'staff', TRUE),
(1, 'Priya', 'priya@harinijewellers.com', '$2y$12$rouSEK7XMLcJT/h.dnh3FuWIwYf/aNz43mRxkjfPxAbqRigWFf1hW', '8220466003', 'staff', TRUE);

-- =============================================
-- PRODUCTS (with stock management)
-- =============================================
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_image VARCHAR(500) NULL,
    category VARCHAR(50) NULL,
    current_stock DECIMAL(12,3) NOT NULL DEFAULT 0.000,
    stock_unit ENUM('gram','kilogram','milligram','tola','ounce','piece','liter','other') NOT NULL DEFAULT 'gram',
    reorder_level DECIMAL(12,3) NOT NULL DEFAULT 100.000,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id)
);

-- =============================================
-- CATEGORIES (Shop-scoped product categories)
-- =============================================
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    reference_code CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    shop_id INT NOT NULL,
    category_name VARCHAR(100) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_categories_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id),
    UNIQUE KEY uq_categories_shop_name (shop_id, category_name)
);

INSERT INTO categories (shop_id, category_name, is_active)
VALUES
(1, 'Gold', TRUE),
(1, 'Silver', TRUE),
(1, 'Diamond', TRUE),
(1, 'Platinum', TRUE);

-- =============================================
-- MERCHANTS
-- Individual or Shop-based merchants (no login)
-- Selected during sales process
-- Scoped to a shop
-- =============================================
CREATE TABLE merchants (
    merchant_id INT AUTO_INCREMENT PRIMARY KEY,
    reference_code CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
    shop_id INT NOT NULL,
    merchant_type ENUM('individual','shop') NOT NULL,
    merchant_name VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    profile_logo VARCHAR(500) NULL,
    personal_address TEXT NULL,
    shop_name VARCHAR(255) NULL,
    shop_logo VARCHAR(500) NULL,
    shop_address TEXT NULL,
    gstin VARCHAR(30) NULL,
    commission_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_merchants_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id)
);


-- =============================================
-- MERCHANT LEDGER (SALE / PURCHASE / PAYMENT)
-- receivable_delta: + = merchant owes shop, - = reduced by payment received
-- payable_delta:    + = shop owes merchant, - = reduced by payment paid
-- =============================================
CREATE TABLE merchant_ledger (
    ledger_id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    merchant_id INT NOT NULL,
    entry_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    entry_type ENUM('opening','sale','purchase','payment_received','payment_paid','adjustment') NOT NULL,
    txn_ref VARCHAR(50) NULL,
    product_id INT NULL,
    description VARCHAR(255) NULL,
    weight DECIMAL(12,3) NULL,
    weight_unit ENUM('gram','kilogram','milligram','tola','ounce','other') NULL,
    purity VARCHAR(20) NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    receivable_delta DECIMAL(12,2) NOT NULL DEFAULT 0,
    payable_delta DECIMAL(12,2) NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ledger_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id),
    CONSTRAINT fk_ledger_merchant FOREIGN KEY (merchant_id) REFERENCES merchants(merchant_id),
    CONSTRAINT fk_ledger_product FOREIGN KEY (product_id) REFERENCES products(product_id),
    CONSTRAINT chk_ledger_weight_unit_consistency CHECK (
        (weight IS NULL AND weight_unit IS NULL)
        OR (weight IS NOT NULL AND weight_unit IS NOT NULL)
    ),
    INDEX idx_ledger_shop_merchant_date (shop_id, merchant_id, entry_date),
    INDEX idx_ledger_is_active (is_active)
);

-- =============================================
-- PRODUCT STOCK HISTORY (Audit Trail)
-- Tracks all stock movements (sales, purchases, adjustments)
-- =============================================
CREATE TABLE product_stock_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    product_id INT NOT NULL,
    movement_type ENUM('purchase','sale','adjustment','opening') NOT NULL,
    quantity DECIMAL(12,3) NOT NULL,
    stock_unit ENUM('gram','kilogram','milligram','tola','ounce','piece','liter','other') NOT NULL,
    stock_before DECIMAL(12,3) NOT NULL,
    stock_after DECIMAL(12,3) NOT NULL,
    reference_type ENUM('merchant_ledger','manual','system') NULL,
    reference_id INT NULL,
    txn_ref VARCHAR(50) NULL,
    notes VARCHAR(500) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_by INT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_history_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id),
    CONSTRAINT fk_stock_history_product FOREIGN KEY (product_id) REFERENCES products(product_id),
    CONSTRAINT fk_stock_history_user FOREIGN KEY (created_by) REFERENCES users(user_id),
    INDEX idx_stock_history_product_date (product_id, created_at),
    INDEX idx_stock_history_shop_date (shop_id, created_at),
    INDEX idx_stock_history_is_active (is_active)
);

-- =============================================
-- IS_ACTIVE HISTORY (Audit Trail)
-- Tracks all is_active status changes across entities
-- =============================================
CREATE TABLE is_active_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    entity_type ENUM('user','product','category','merchant','saas_user','shop') NOT NULL,
    entity_id INT NOT NULL,
    entity_name VARCHAR(255) NULL,
    old_status BOOLEAN NOT NULL,
    new_status BOOLEAN NOT NULL,
    changed_by INT NULL,
    change_reason TEXT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_is_active_history_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id),
    CONSTRAINT fk_is_active_history_user FOREIGN KEY (changed_by) REFERENCES users(user_id),
    INDEX idx_is_active_history_entity (entity_type, entity_id),
    INDEX idx_is_active_history_shop_date (shop_id, created_at),
    INDEX idx_is_active_history_changed_by (changed_by)
);

-- =============================================
-- CURRENT STOCK SUMMARY
-- =============================================
-- SELECT
--   p.product_id,
--   p.product_name,
--   p.category,
--   p.current_stock,
--   p.stock_unit,
--   p.reorder_level,
--   CASE WHEN p.current_stock <= p.reorder_level THEN 'LOW' ELSE 'OK' END AS stock_status
-- FROM products p
-- WHERE p.shop_id = 1
-- ORDER BY p.category, p.product_name;