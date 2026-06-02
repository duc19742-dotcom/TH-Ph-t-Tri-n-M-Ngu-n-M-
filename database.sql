CREATE DATABASE IF NOT EXISTS my_store
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE my_store;

CREATE TABLE IF NOT EXISTS category (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS product (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(12, 2) NOT NULL DEFAULT 0,
    category_id INT UNSIGNED NULL,
    image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_product_category
        FOREIGN KEY (category_id) REFERENCES category(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    total_amount DECIMAL(12, 2) NOT NULL DEFAULT 0,
    order_status VARCHAR(50) NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50) NOT NULL DEFAULT 'cod',
    payment_status VARCHAR(50) NOT NULL DEFAULT 'pending',
    momo_order_id VARCHAR(100) NULL,
    momo_request_id VARCHAR(100) NULL,
    momo_trans_id VARCHAR(100) NULL,
    momo_result_code VARCHAR(20) NULL,
    vnp_txn_ref VARCHAR(100) NULL,
    vnp_transaction_no VARCHAR(100) NULL,
    vnp_response_code VARCHAR(20) NULL,
    vnp_bank_code VARCHAR(50) NULL,
    vnp_pay_date VARCHAR(20) NULL,
    paid_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_orders_momo_order_id (momo_order_id),
    INDEX idx_orders_vnp_txn_ref (vnp_txn_ref)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS order_details (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    price DECIMAL(12, 2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_details_order
        FOREIGN KEY (order_id) REFERENCES orders(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_order_details_product
        FOREIGN KEY (product_id) REFERENCES product(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS account (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) UNIQUE,
    github_id VARCHAR(100) UNIQUE,
    google_id VARCHAR(100) UNIQUE,
    fullname VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    avatar VARCHAR(255) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO category (id, name, description) VALUES
    (1, 'Dien thoai', 'San pham dien thoai'),
    (2, 'Laptop', 'May tinh xach tay'),
    (3, 'Phu kien', 'Phu kien cong nghe')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description);

INSERT INTO product (id, name, description, price, category_id, image) VALUES
    (1, 'Dien thoai mau 1', 'San pham mau de kiem tra giao dien.', 3500000, 1, 'uploads/products/c4c14a7d98cc1b6ed1dda02386da1ae5.webp'),
    (2, 'Laptop mau 1', 'San pham mau de kiem tra gio hang.', 12500000, 2, 'uploads/products/b22978650149ffd29d49a22a0bf79057.webp'),
    (3, 'Tai nghe mau', 'Phu kien mau trong cua hang.', 450000, 3, 'uploads/products/9cd4a1a56d5070b9a81b3fa90cd5832d.webp'),
    (4, 'Ban phim mau', 'Phu kien mau co san anh.', 690000, 3, 'uploads/products/330aef0a75b509a92366deaf56992ebf.webp')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    description = VALUES(description),
    price = VALUES(price),
    category_id = VALUES(category_id),
    image = VALUES(image);
