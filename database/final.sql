-- =============================================
-- JEWELLERY SHOP - MINIMAL LOGIN SCHEMA
-- =============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS merchants;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS shops;

SET FOREIGN_KEY_CHECKS = 1;

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
-- MERCHANTS
-- Individual or Shop-based merchants (no login)
-- Selected during sales process
-- External parties independent of shop
-- =============================================
CREATE TABLE merchants (
    merchant_id INT AUTO_INCREMENT PRIMARY KEY,
    reference_code CHAR(36) NOT NULL UNIQUE DEFAULT (UUID()),
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
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
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

INSERT INTO users (shop_id, name, email, password_hash, mobileno, user_type, is_active)
VALUES
(1, 'Vijayakumar', 'owner@harinijewellers.com', '$2y$12$rouSEK7XMLcJT/h.dnh3FuWIwYf/aNz43mRxkjfPxAbqRigWFf1hW', '8220466675', 'owner', TRUE),
(1, 'Nadhiya', 'manager@harinijewellers.com', '$2y$12$rouSEK7XMLcJT/h.dnh3FuWIwYf/aNz43mRxkjfPxAbqRigWFf1hW', '8220466001', 'manager', TRUE),
(1, 'Vinoth', 'vinoth@harinijewellers.com', '$2y$12$rouSEK7XMLcJT/h.dnh3FuWIwYf/aNz43mRxkjfPxAbqRigWFf1hW', '8220466002', 'staff', TRUE),
(1, 'Priya', 'priya@harinijewellers.com', '$2y$12$rouSEK7XMLcJT/h.dnh3FuWIwYf/aNz43mRxkjfPxAbqRigWFf1hW', '8220466003', 'staff', TRUE);

-- =============================================
-- SAMPLE MERCHANT DATA
-- =============================================
INSERT INTO merchants (merchant_type, merchant_name, phone, email, profile_logo, personal_address, shop_name, shop_logo, shop_address, gstin, commission_percent, is_active)
VALUES
('individual', 'Ramesh Kumar', '9876543211', 'ramesh.k@email.com', '/uploads/merchant/profile/default-profile-1.png', 'No. 45, East Street, Thirunagar, Madurai - 625006', NULL, NULL, NULL, NULL, 5.00, TRUE),
('individual', 'Lakshmi Retail', '9876543212', 'lakshmi.retail@email.com', '/uploads/merchant/profile/default-profile-2.png', 'No. 102, Main Road, Anna Nagar, Madurai - 625020', NULL, NULL, NULL, NULL, 3.50, TRUE),
('shop', 'Sri Jewelry Store', '9876543213', 'srijewelry@email.com', NULL, NULL, 'Sri Jewelry Store - Branch 1', '/uploads/merchant/shop/default-shop-1.png', '567, Bypass Road, Madurai - 625016', '33AAHFH3055M2BC', 2.50, TRUE),
('shop', 'Anand Jewelry Hub', '9876543214', 'anand.hub@email.com', NULL, NULL, 'Anand Jewelry Hub - Main', '/uploads/merchant/shop/default-shop-2.png', 'No. 123, Lake Street, Madurai - 625001', '33AAHFH3055M3CD', 4.00, TRUE);


-- =============================================
-- PRODUCTS
-- =============================================
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_image VARCHAR(500) NULL,
    category VARCHAR(50) NULL,
    purity VARCHAR(20) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id)
);

-- =============================================
-- SAMPLE PRODUCTS DATA
INSERT INTO products (shop_id, product_name, category, purity, is_active)
VALUES
(1, 'Gold Bar 999', 'gold', '999', TRUE),
(1, 'Gold Coin 999', 'gold', '999', TRUE),
(1, 'Gold 999', 'gold', '999', TRUE),
(1, 'Gold Bar 916', 'gold', '916', TRUE),
(1, 'Gold Coin 916', 'gold', '916', TRUE),
(1, 'Gold 916', 'gold', '916', TRUE);