-- =============================================
-- JEWELLERY SHOP - MINIMAL LOGIN SCHEMA
-- =============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS merchant_ledger;
DROP TABLE IF EXISTS merchants;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS shops;
DROP TABLE IF EXISTS products;

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
INSERT INTO merchants (shop_id, merchant_type, merchant_name, phone, email, profile_logo, personal_address, shop_name, shop_logo, shop_address, gstin, commission_percent, is_active)
VALUES
(1, 'individual', 'Ramesh Kumar', '9876543211', 'ramesh.k@email.com', '/uploads/merchant/profile/default-profile-1.png', 'No. 45, East Street, Thirunagar, Madurai - 625006', NULL, NULL, NULL, NULL, 5.00, TRUE),
(1, 'individual', 'Lakshmi Retail', '9876543212', 'lakshmi.retail@email.com', '/uploads/merchant/profile/default-profile-2.png', 'No. 102, Main Road, Anna Nagar, Madurai - 625020', NULL, NULL, NULL, NULL, 3.50, TRUE),
(1, 'shop', 'Sri Jewelry Store', '9876543213', 'srijewelry@email.com', NULL, NULL, 'Sri Jewelry Store - Branch 1', '/uploads/merchant/shop/default-shop-1.png', '567, Bypass Road, Madurai - 625016', '33AAHFH3055M2BC', 2.50, TRUE),
(1, 'shop', 'Anand Jewelry Hub', '9876543214', 'anand.hub@email.com', NULL, NULL, 'Anand Jewelry Hub - Main', '/uploads/merchant/shop/default-shop-2.png', 'No. 123, Lake Street, Madurai - 625001', '33AAHFH3055M3CD', 4.00, TRUE);


-- =============================================
-- PRODUCTS
-- =============================================
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_image VARCHAR(500) NULL,
    category VARCHAR(50) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id)
);

-- =============================================
-- SAMPLE PRODUCTS DATA
INSERT INTO products (shop_id, product_name, category, is_active)
VALUES
(1, 'Gold Bar', 'gold', TRUE),
(1, 'Gold Coin', 'gold', TRUE),
(1, 'Gold', 'gold', TRUE),
(1, 'Silver Bar', 'silver', TRUE),
(1, 'Silver Coin', 'silver', TRUE),
(1, 'Silver', 'silver', TRUE)
;

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
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_ledger_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id),
    CONSTRAINT fk_ledger_merchant FOREIGN KEY (merchant_id) REFERENCES merchants(merchant_id),
    CONSTRAINT fk_ledger_product FOREIGN KEY (product_id) REFERENCES products(product_id),
    CONSTRAINT chk_ledger_weight_unit_consistency CHECK (
        (weight IS NULL AND weight_unit IS NULL)
        OR (weight IS NOT NULL AND weight_unit IS NOT NULL)
    ),
    INDEX idx_ledger_shop_merchant_date (shop_id, merchant_id, entry_date)
);

-- =============================================
-- LEDGER SAMPLE DATA
-- CASE 1: Sale to merchant + payment received
-- CASE 2: Purchase from merchant (shop payable increases)
-- CASE 3: Same merchant both sale and purchase
-- =============================================
INSERT INTO merchant_ledger
(shop_id, merchant_id, entry_date, entry_type, txn_ref, product_id, description, weight, weight_unit, purity, amount, receivable_delta, payable_delta)
VALUES
-- CASE 1 (merchant_id = 1: Ramesh Kumar)
-- old receivable balance = 1,50,000
(1, 1, '2026-05-01 09:00:00', 'opening', NULL, NULL, 'Opening receivable balance', NULL, NULL, NULL, 150000.00, 150000.00, 0.00),
-- sold 40g 999 gold for 6,00,000 => receivable +6,00,000
(1, 1, '2026-05-28 10:00:00', 'sale', 'SAL-0001', 1, 'Sold Gold 999 to merchant', 40.000, 'gram', '999', 600000.00, 600000.00, 0.00),
-- received 5,00,000 => receivable -5,00,000
(1, 1, '2026-05-28 18:00:00', 'payment_received', 'RCPT-0001', NULL, 'Payment received from merchant', NULL, NULL, NULL, 500000.00, -500000.00, 0.00),

-- CASE 2 (merchant_id = 2: Lakshmi Retail)
-- old payable balance = 5,00,000
(1, 2, '2026-05-01 09:00:00', 'opening', NULL, NULL, 'Opening payable balance', NULL, NULL, NULL, 500000.00, 0.00, 500000.00),
-- purchased 20g for 3,00,000 => payable +3,00,000 (total payable 8,00,000)
(1, 2, '2026-05-28 11:00:00', 'purchase', 'PUR-0001', 1, 'Purchased Gold 999 from merchant', 20.000, 'gram', '999', 300000.00, 0.00, 300000.00),

-- CASE 3 (merchant_id = 3: Sri Jewelry Store)
-- both receivable and payable can coexist
(1, 3, '2026-05-01 09:00:00', 'opening', NULL, NULL, 'Opening mixed balance', NULL, NULL, NULL, 0.00, 100000.00, 50000.00),
(1, 3, '2026-05-10 10:00:00', 'sale', 'SAL-0002', 4, 'Sold Gold 916', 15.000, 'gram', '916', 200000.00, 200000.00, 0.00),
(1, 3, '2026-05-12 11:00:00', 'purchase', 'PUR-0002', 1, 'Purchased Gold 999', 8.000, 'gram', '999', 120000.00, 0.00, 120000.00),
(1, 3, '2026-05-15 16:00:00', 'payment_received', 'RCPT-0002', NULL, 'Received against sales', NULL, NULL, NULL, 150000.00, -150000.00, 0.00),
(1, 3, '2026-05-18 17:00:00', 'payment_paid', 'PAY-0001', NULL, 'Paid against purchases', NULL, NULL, NULL, 70000.00, 0.00, -70000.00);

-- =============================================
-- BALANCE VIEW QUERY (run when needed)
-- =============================================
-- SELECT
--   m.merchant_id,
--   m.merchant_name,
--   SUM(l.receivable_delta) AS receivable_balance,
--   SUM(l.payable_delta) AS payable_balance
-- FROM merchant_ledger l
-- JOIN merchants m ON m.merchant_id = l.merchant_id
-- WHERE l.shop_id = 1
-- GROUP BY m.merchant_id, m.merchant_name
-- ORDER BY m.merchant_name;