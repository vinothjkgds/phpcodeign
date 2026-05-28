-- =============================================
-- JEWELLERY SHOP - MINIMAL SALES & PURCHASE SCHEMA
-- =============================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS stock_transactions;
DROP TABLE IF EXISTS sale_payments;
DROP TABLE IF EXISTS sale_items;
DROP TABLE IF EXISTS sales;
DROP TABLE IF EXISTS purchase_payments;
DROP TABLE IF EXISTS purchase_items;
DROP TABLE IF EXISTS purchases;
DROP TABLE IF EXISTS merchants;
DROP TABLE IF EXISTS parties;
DROP TABLE IF EXISTS items;
DROP TABLE IF EXISTS role_permissions;
DROP TABLE IF EXISTS permissions;
DROP TABLE IF EXISTS user_roles;
DROP TABLE IF EXISTS roles;
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
-- PRIVILEGES (Role-based)
-- =============================================
CREATE TABLE roles (
    role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_code VARCHAR(50) NOT NULL UNIQUE,
    role_name VARCHAR(100) NOT NULL,
    description TEXT NULL
);

CREATE TABLE user_roles (
    user_role_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    UNIQUE KEY uq_user_role (user_id, role_id),
    CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(user_id),
    CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(role_id)
);

CREATE TABLE permissions (
    permission_id INT AUTO_INCREMENT PRIMARY KEY,
    permission_code VARCHAR(100) NOT NULL UNIQUE,
    module_name VARCHAR(100) NOT NULL,
    action_name VARCHAR(50) NOT NULL,
    description TEXT NULL
);

CREATE TABLE role_permissions (
    role_permission_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    is_allowed BOOLEAN NOT NULL DEFAULT TRUE,
    UNIQUE KEY uq_role_permission (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(role_id),
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(permission_id)
);

-- =============================================
-- MASTER DATA
-- =============================================
CREATE TABLE items (
    item_id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    category VARCHAR(50) NULL,
    purity VARCHAR(20) NULL,
    hsn_code VARCHAR(20) NULL,
    unit ENUM('Gram','Kilogram','Milligram','Piece','Ounce','Tola','Carat') NOT NULL DEFAULT 'Gram',
    opening_stock DECIMAL(12,3) NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_items_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id)
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

CREATE TABLE parties (
    party_id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    party_name VARCHAR(255) NOT NULL,
    party_type ENUM('SUPPLIER','CUSTOMER') NOT NULL,
    phone VARCHAR(20) NULL,
    email VARCHAR(255) NULL,
    address TEXT NULL,
    gstin VARCHAR(30) NULL,
    opening_balance DECIMAL(14,2) NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_parties_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id)
);

-- =============================================
-- PURCHASE
-- =============================================
CREATE TABLE purchases (
    purchase_id INT AUTO_INCREMENT PRIMARY KEY,
    reference_no VARCHAR(50) NOT NULL,
    shop_id INT NOT NULL,
    supplier_id INT NOT NULL,
    purchase_date DATE NOT NULL,
    created_by_user_id INT NOT NULL,
    subtotal_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    due_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    status ENUM('draft','confirmed','cancelled') NOT NULL DEFAULT 'confirmed',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_purchase_ref_per_shop (shop_id, reference_no),
    CONSTRAINT fk_purchases_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id),
    CONSTRAINT fk_purchases_supplier FOREIGN KEY (supplier_id) REFERENCES parties(party_id),
    CONSTRAINT fk_purchases_user FOREIGN KEY (created_by_user_id) REFERENCES users(user_id)
);

CREATE TABLE purchase_items (
    purchase_item_id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity DECIMAL(12,3) NOT NULL,
    rate DECIMAL(15,2) NOT NULL,
    line_discount DECIMAL(14,2) NOT NULL DEFAULT 0,
    tax_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
    line_total DECIMAL(18,2) NOT NULL,
    CONSTRAINT fk_purchase_items_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(purchase_id),
    CONSTRAINT fk_purchase_items_item FOREIGN KEY (item_id) REFERENCES items(item_id)
);

CREATE TABLE purchase_payments (
    purchase_payment_id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    payment_mode ENUM('cash','upi','bank_transfer','card','cheque') NOT NULL,
    reference_no VARCHAR(100) NULL,
    remarks VARCHAR(255) NULL,
    created_by_user_id INT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_purchase_payments_purchase FOREIGN KEY (purchase_id) REFERENCES purchases(purchase_id),
    CONSTRAINT fk_purchase_payments_user FOREIGN KEY (created_by_user_id) REFERENCES users(user_id)
);

-- =============================================
-- SALES
-- =============================================
CREATE TABLE sales (
    sale_id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_no VARCHAR(50) NOT NULL,
    shop_id INT NOT NULL,
    customer_id INT NULL,
    merchant_id INT NULL,
    sale_date DATE NOT NULL,
    created_by_user_id INT NOT NULL,
    subtotal_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    discount_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    tax_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    paid_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    due_amount DECIMAL(14,2) NOT NULL DEFAULT 0,
    notes TEXT NULL,
    status ENUM('draft','completed','cancelled','returned') NOT NULL DEFAULT 'completed',
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_sale_invoice_per_shop (shop_id, invoice_no),
    CONSTRAINT fk_sales_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id),
    CONSTRAINT fk_sales_customer FOREIGN KEY (customer_id) REFERENCES parties(party_id),
    CONSTRAINT fk_sales_merchant FOREIGN KEY (merchant_id) REFERENCES merchants(merchant_id),
    CONSTRAINT fk_sales_user FOREIGN KEY (created_by_user_id) REFERENCES users(user_id)
);

CREATE TABLE sale_items (
    sale_item_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    item_id INT NOT NULL,
    quantity DECIMAL(12,3) NOT NULL,
    rate DECIMAL(15,2) NOT NULL,
    line_discount DECIMAL(14,2) NOT NULL DEFAULT 0,
    tax_percent DECIMAL(5,2) NOT NULL DEFAULT 0,
    line_total DECIMAL(18,2) NOT NULL,
    CONSTRAINT fk_sale_items_sale FOREIGN KEY (sale_id) REFERENCES sales(sale_id),
    CONSTRAINT fk_sale_items_item FOREIGN KEY (item_id) REFERENCES items(item_id)
);

CREATE TABLE sale_payments (
    sale_payment_id INT AUTO_INCREMENT PRIMARY KEY,
    sale_id INT NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(14,2) NOT NULL,
    payment_mode ENUM('cash','upi','bank_transfer','card','cheque') NOT NULL,
    reference_no VARCHAR(100) NULL,
    remarks VARCHAR(255) NULL,
    created_by_user_id INT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_sale_payments_sale FOREIGN KEY (sale_id) REFERENCES sales(sale_id),
    CONSTRAINT fk_sale_payments_user FOREIGN KEY (created_by_user_id) REFERENCES users(user_id)
);

-- =============================================
-- STOCK LEDGER
-- +qty for purchase/opening/adjustments, -qty for sales/returns out
-- =============================================
CREATE TABLE stock_transactions (
    txn_id INT AUTO_INCREMENT PRIMARY KEY,
    shop_id INT NOT NULL,
    txn_type ENUM('OPENING','PURCHASE','SALE','PURCHASE_RETURN','SALE_RETURN','ADJUSTMENT') NOT NULL,
    ref_table ENUM('purchases','sales','manual') NULL,
    ref_id INT NULL,
    item_id INT NOT NULL,
    quantity DECIMAL(12,3) NOT NULL,
    rate DECIMAL(15,2) NULL,
    value DECIMAL(18,2) NULL,
    txn_date DATE NOT NULL,
    remarks VARCHAR(255) NULL,
    created_by_user_id INT NOT NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_stock_txn_shop FOREIGN KEY (shop_id) REFERENCES shops(shop_id),
    CONSTRAINT fk_stock_txn_item FOREIGN KEY (item_id) REFERENCES items(item_id),
    CONSTRAINT fk_stock_txn_user FOREIGN KEY (created_by_user_id) REFERENCES users(user_id)
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
INSERT INTO merchants (merchant_type, merchant_name, phone, email, personal_address, shop_name, shop_address, gstin, commission_percent, is_active)
VALUES
('individual', 'Ramesh Kumar', '9876543211', 'ramesh.k@email.com', 'No. 45, East Street, Thirunagar, Madurai - 625006', NULL, NULL, NULL, 5.00, TRUE),
('individual', 'Lakshmi Retail', '9876543212', 'lakshmi.retail@email.com', 'No. 102, Main Road, Anna Nagar, Madurai - 625020', NULL, NULL, NULL, 3.50, TRUE),
('shop', 'Sri Jewelry Store', '9876543213', 'srijewelry@email.com', NULL, 'Sri Jewelry Store - Branch 1', '567, Bypass Road, Madurai - 625016', '33AAHFH3055M2BC', 2.50, TRUE),
('shop', 'Anand Jewelry Hub', '9876543214', 'anand.hub@email.com', NULL, 'Anand Jewelry Hub - Main', 'No. 123, Lake Street, Madurai - 625001', '33AAHFH3055M3CD', 4.00, TRUE);
