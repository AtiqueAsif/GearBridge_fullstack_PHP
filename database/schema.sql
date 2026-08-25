CREATE DATABASE IF NOT EXISTS gearbridge_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE gearbridge_db;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    user_type ENUM('student', 'staff') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL UNIQUE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    condition_status ENUM('excellent', 'good', 'fair') NOT NULL,
    image_path VARCHAR(255) NULL,
    availability_status ENUM('available', 'borrowed') NOT NULL DEFAULT 'available',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_items_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_items_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_items_owner (owner_id),
    INDEX idx_items_category (category_id),
    INDEX idx_items_availability (availability_status),
    INDEX idx_items_deleted (deleted_at),
    INDEX idx_items_browse (deleted_at, availability_status, created_at)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS borrow_requests (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED NOT NULL,
    borrower_id INT UNSIGNED NOT NULL,
    borrow_from DATE NOT NULL,
    borrow_until DATE NOT NULL,
    note VARCHAR(500) NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled', 'returned') NOT NULL DEFAULT 'pending',
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    decision_at TIMESTAMP NULL DEFAULT NULL,
    returned_at TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT fk_borrow_item FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_borrow_borrower FOREIGN KEY (borrower_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_borrow_dates CHECK (borrow_until >= borrow_from),
    INDEX idx_borrow_item (item_id),
    INDEX idx_borrow_borrower (borrower_id),
    INDEX idx_borrow_status (status),
    INDEX idx_borrow_item_status (item_id, status),
    INDEX idx_borrow_borrower_status (borrower_id, status)
) ENGINE=InnoDB;
