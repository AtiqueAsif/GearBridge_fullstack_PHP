-- GearBridge - Complete MySQL database with demo dataset
-- Import this file with phpMyAdmin or the MySQL client.
-- Demo login password for all built-in accounts: Demo@123

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

USE gearbridge_db;

INSERT INTO categories (name) VALUES
('Electronics'),
('Cameras'),
('Lab Tools'),
('Textbooks'),
('Other')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- GearBridge demo dataset

-- 10 student accounts + 10 staff accounts + 31 items + 23 borrow records.

-- All demo accounts use the password: Demo@123



-- Re-import safe: reset only the built-in demo accounts and their related records.

DELETE br FROM borrow_requests br

LEFT JOIN items i ON i.id = br.item_id

LEFT JOIN users item_owner ON item_owner.id = i.owner_id

LEFT JOIN users borrower ON borrower.id = br.borrower_id

WHERE item_owner.email IN ('student01@demo.com', 'student02@demo.com', 'student03@demo.com', 'student04@demo.com', 'student05@demo.com', 'student06@demo.com', 'student07@demo.com', 'student08@demo.com', 'student09@demo.com', 'student10@demo.com', 'staff01@demo.com', 'staff02@demo.com', 'staff03@demo.com', 'staff04@demo.com', 'staff05@demo.com', 'staff06@demo.com', 'staff07@demo.com', 'staff08@demo.com', 'staff09@demo.com', 'staff10@demo.com') OR borrower.email IN ('student01@demo.com', 'student02@demo.com', 'student03@demo.com', 'student04@demo.com', 'student05@demo.com', 'student06@demo.com', 'student07@demo.com', 'student08@demo.com', 'student09@demo.com', 'student10@demo.com', 'staff01@demo.com', 'staff02@demo.com', 'staff03@demo.com', 'staff04@demo.com', 'staff05@demo.com', 'staff06@demo.com', 'staff07@demo.com', 'staff08@demo.com', 'staff09@demo.com', 'staff10@demo.com');



DELETE i FROM items i

INNER JOIN users u ON u.id = i.owner_id

WHERE u.email IN ('student01@demo.com', 'student02@demo.com', 'student03@demo.com', 'student04@demo.com', 'student05@demo.com', 'student06@demo.com', 'student07@demo.com', 'student08@demo.com', 'student09@demo.com', 'student10@demo.com', 'staff01@demo.com', 'staff02@demo.com', 'staff03@demo.com', 'staff04@demo.com', 'staff05@demo.com', 'staff06@demo.com', 'staff07@demo.com', 'staff08@demo.com', 'staff09@demo.com', 'staff10@demo.com');



DELETE FROM users WHERE email IN ('student01@demo.com', 'student02@demo.com', 'student03@demo.com', 'student04@demo.com', 'student05@demo.com', 'student06@demo.com', 'student07@demo.com', 'student08@demo.com', 'student09@demo.com', 'student10@demo.com', 'staff01@demo.com', 'staff02@demo.com', 'staff03@demo.com', 'staff04@demo.com', 'staff05@demo.com', 'staff06@demo.com', 'staff07@demo.com', 'staff08@demo.com', 'staff09@demo.com', 'staff10@demo.com');



-- Demo users

INSERT INTO users (full_name, email, password_hash, user_type, created_at, updated_at) VALUES

('Arafat Hossain', 'student01@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'student', '2026-06-05 09:00:00', '2026-06-05 09:00:00'),
('Nusrat Jahan', 'student02@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'student', '2026-06-07 10:15:00', '2026-06-07 10:15:00'),
('Samiul Islam', 'student03@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'student', '2026-06-10 11:30:00', '2026-06-10 11:30:00'),
('Tanjim Ahmed', 'student04@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'student', '2026-06-14 14:20:00', '2026-06-14 14:20:00'),
('Farzana Rahman', 'student05@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'student', '2026-06-18 08:45:00', '2026-06-18 08:45:00'),
('Mehedi Hasan', 'student06@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'student', '2026-06-22 13:10:00', '2026-06-22 13:10:00'),
('Sabila Noor', 'student07@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'student', '2026-06-25 16:05:00', '2026-06-25 16:05:00'),
('Rafi Khan', 'student08@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'student', '2026-07-01 09:40:00', '2026-07-01 09:40:00'),
('Tasnim Akter', 'student09@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'student', '2026-07-05 12:25:00', '2026-07-05 12:25:00'),
('Nayeem Chowdhury', 'student10@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'student', '2026-07-10 15:35:00', '2026-07-10 15:35:00'),
('Dr. Farhan Kabir', 'staff01@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'staff', '2026-06-02 08:30:00', '2026-06-02 08:30:00'),
('Sharmeen Akter', 'staff02@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'staff', '2026-06-06 11:20:00', '2026-06-06 11:20:00'),
('Imran Hossain', 'staff03@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'staff', '2026-06-12 10:00:00', '2026-06-12 10:00:00'),
('Mahmudul Karim', 'staff04@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'staff', '2026-06-17 14:50:00', '2026-06-17 14:50:00'),
('Sabrina Rahman', 'staff05@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'staff', '2026-06-21 09:10:00', '2026-06-21 09:10:00'),
('Asif Mahmud', 'staff06@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'staff', '2026-06-27 13:45:00', '2026-06-27 13:45:00'),
('Nabila Sultana', 'staff07@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'staff', '2026-07-02 08:55:00', '2026-07-02 08:55:00'),
('Tariq Hasan', 'staff08@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'staff', '2026-07-07 12:15:00', '2026-07-07 12:15:00'),
('Rezaul Islam', 'staff09@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'staff', '2026-07-12 10:40:00', '2026-07-12 10:40:00'),
('Morsheda Begum', 'staff10@demo.com', '$2y$12$4C93dg0Q/kzJi/qFG4NWGeOviVMOFhy5SsT0Yvx8FbTda3jxCNDy.', 'staff', '2026-07-18 15:00:00', '2026-07-18 15:00:00');



-- Demo equipment

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Arduino Uno Starter Kit', 'Arduino Uno board with USB cable, breadboard, jumper wires, LEDs, resistors and basic sensors. Ideal for introductory electronics and embedded-system lab work.', 'excellent', 'assets/images/items/arduino-uno-starter-kit.jpg', 'available', '2026-08-15 07:55:00', '2026-08-15 07:55:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student01@demo.com' AND c.name = 'Electronics';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Raspberry Pi 4 Kit', 'Raspberry Pi 4 with power adapter, micro-HDMI cable, case and 32GB microSD card. Suitable for Linux, IoT and networking practice.', 'good', 'assets/images/items/raspberry-pi-4-kit.jpg', 'available', '2026-08-14 18:20:00', '2026-08-14 18:20:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student03@demo.com' AND c.name = 'Electronics';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Digital Multimeter', 'Auto-ranging digital multimeter with probes for voltage, current, resistance and continuity testing.', 'excellent', 'assets/images/items/digital-multimeter.jpg', 'borrowed', '2026-08-13 10:10:00', '2026-08-13 10:10:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff01@demo.com' AND c.name = 'Electronics';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Soldering Station', 'Temperature-controlled soldering iron with stand, sponge, solder wire and basic desoldering pump.', 'good', 'assets/images/items/soldering-station.jpg', 'available', '2026-08-12 14:30:00', '2026-08-12 14:30:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff04@demo.com' AND c.name = 'Electronics';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Portable Projector', 'Compact 1080p-compatible projector with HDMI cable and remote. Useful for presentations and small group demos.', 'good', 'assets/images/items/portable-projector.jpg', 'borrowed', '2026-08-11 09:20:00', '2026-08-11 09:20:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff06@demo.com' AND c.name = 'Electronics';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Noise-Cancelling Headphones', 'Over-ear headphones with active noise cancellation and wired/Bluetooth modes for focused study or media work.', 'excellent', 'assets/images/items/noise-cancelling-headphones.jpg', 'available', '2026-08-10 16:40:00', '2026-08-10 16:40:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student07@demo.com' AND c.name = 'Electronics';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Power Bank 20000mAh', 'High-capacity USB-C power bank with multiple charging ports. Good for long campus days and field projects.', 'good', 'assets/images/items/power-bank-20000mah.jpg', 'available', '2026-08-09 12:00:00', '2026-08-09 12:00:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student09@demo.com' AND c.name = 'Electronics';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Scientific Calculator', 'Scientific calculator suitable for engineering mathematics, statistics and laboratory calculations.', 'good', 'assets/images/items/scientific-calculator.jpg', 'available', '2026-08-08 11:15:00', '2026-08-08 11:15:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff02@demo.com' AND c.name = 'Electronics';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'USB-C Multiport Hub', 'USB-C hub with HDMI, USB-A, SD card and power-delivery ports for laptops and tablets.', 'excellent', 'assets/images/items/usb-c-multiport-hub.jpg', 'available', '2026-08-07 13:45:00', '2026-08-07 13:45:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff08@demo.com' AND c.name = 'Electronics';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Canon EOS 200D DSLR', 'Entry-level DSLR camera with 18-55mm kit lens, battery, charger and camera strap. Suitable for class projects and events.', 'good', 'assets/images/items/canon-eos-200d-dslr.jpg', 'borrowed', '2026-08-15 07:20:00', '2026-08-15 07:20:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student02@demo.com' AND c.name = 'Cameras';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Sony Alpha Mirrorless Camera', 'Mirrorless camera with 16-50mm lens, battery and charger. Compact option for photography and video assignments.', 'excellent', 'assets/images/items/sony-alpha-mirrorless-camera.jpg', 'available', '2026-08-14 15:35:00', '2026-08-14 15:35:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff03@demo.com' AND c.name = 'Cameras';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Tripod with Ball Head', 'Lightweight aluminum tripod with quick-release plate and adjustable ball head for cameras and phones.', 'good', 'assets/images/items/tripod-with-ball-head.jpg', 'available', '2026-08-13 17:10:00', '2026-08-13 17:10:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student06@demo.com' AND c.name = 'Cameras';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'GoPro Action Camera', 'Compact action camera with protective case, mounting accessories and USB charging cable.', 'good', 'assets/images/items/gopro-action-camera.jpg', 'available', '2026-08-12 08:25:00', '2026-08-12 08:25:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff05@demo.com' AND c.name = 'Cameras';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Camera Lighting Kit', 'Two compact LED video lights with mini stands and adjustable brightness for interview or presentation recording.', 'excellent', 'assets/images/items/camera-lighting-kit.jpg', 'available', '2026-08-11 18:05:00', '2026-08-11 18:05:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student04@demo.com' AND c.name = 'Cameras';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Digital Vernier Caliper', '150mm digital vernier caliper with metric/inch modes. Useful for mechanical measurement and prototype work.', 'excellent', 'assets/images/items/digital-vernier-caliper.jpg', 'available', '2026-08-15 06:50:00', '2026-08-15 06:50:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student05@demo.com' AND c.name = 'Lab Tools';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Electronics Tool Kit', 'Precision screwdriver set, wire stripper, pliers, cutter, tweezers and small hand tools for electronics projects.', 'good', 'assets/images/items/electronics-tool-kit.jpg', 'available', '2026-08-14 09:15:00', '2026-08-14 09:15:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff07@demo.com' AND c.name = 'Lab Tools';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Breadboard and Sensor Kit', 'Full-size breadboard with jumper wires and a collection of common sensors for Arduino and embedded-system prototyping.', 'excellent', 'assets/images/items/breadboard-and-sensor-kit.jpg', 'available', '2026-08-13 12:45:00', '2026-08-13 12:45:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student08@demo.com' AND c.name = 'Lab Tools';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Oscilloscope Probe Set', 'Pair of switchable 1x/10x oscilloscope probes with grounding clips and accessories.', 'good', 'assets/images/items/oscilloscope-probe-set.jpg', 'available', '2026-08-12 11:10:00', '2026-08-12 11:10:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff10@demo.com' AND c.name = 'Lab Tools';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Mini Hot Glue Gun', 'Compact hot glue gun with glue sticks for quick prototype assembly, poster work and light fabrication.', 'good', 'assets/images/items/mini-hot-glue-gun.jpg', 'available', '2026-08-11 13:05:00', '2026-08-11 13:05:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student10@demo.com' AND c.name = 'Lab Tools';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Basic Chemistry Glassware Set', 'Small lab set with beakers, measuring cylinders and droppers for supervised academic demonstration use.', 'good', 'assets/images/items/basic-chemistry-glassware-set.jpg', 'available', '2026-08-10 10:30:00', '2026-08-10 10:30:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff02@demo.com' AND c.name = 'Lab Tools';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Data Structures and Algorithms', 'Reference textbook covering arrays, linked lists, trees, graphs, sorting, searching and algorithm analysis.', 'good', 'assets/images/items/data-structures-and-algorithms.jpg', 'available', '2026-08-15 07:05:00', '2026-08-15 07:05:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff09@demo.com' AND c.name = 'Textbooks';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Engineering Mathematics', 'Engineering mathematics reference covering calculus, differential equations, matrices and transforms.', 'good', 'assets/images/items/engineering-mathematics.jpg', 'available', '2026-08-14 08:40:00', '2026-08-14 08:40:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff01@demo.com' AND c.name = 'Textbooks';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Circuit Analysis Fundamentals', 'Introductory circuit-analysis textbook covering DC/AC circuits, network theorems and basic electronics calculations.', 'fair', 'assets/images/items/circuit-analysis-fundamentals.jpg', 'available', '2026-08-13 08:00:00', '2026-08-13 08:00:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student03@demo.com' AND c.name = 'Textbooks';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Web Programming with PHP', 'Web programming reference focused on HTML, CSS, JavaScript, PHP, forms, sessions and MySQL integration.', 'excellent', 'assets/images/items/web-programming-with-php.jpg', 'borrowed', '2026-08-12 16:00:00', '2026-08-12 16:00:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff07@demo.com' AND c.name = 'Textbooks';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Physics for Engineers', 'Engineering physics textbook covering mechanics, waves, electricity, magnetism and modern physics.', 'good', 'assets/images/items/physics-for-engineers.jpg', 'available', '2026-08-11 10:10:00', '2026-08-11 10:10:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student06@demo.com' AND c.name = 'Textbooks';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Database System Concepts', 'Database reference covering relational models, SQL, normalization, transactions and indexing.', 'excellent', 'assets/images/items/database-system-concepts.jpg', 'available', '2026-08-10 15:25:00', '2026-08-10 15:25:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff04@demo.com' AND c.name = 'Textbooks';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Chemistry Lab Manual', 'Practical chemistry lab manual with common experiment procedures, observation tables and safety notes.', 'good', 'assets/images/items/chemistry-lab-manual.jpg', 'available', '2026-08-09 09:55:00', '2026-08-09 09:55:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student05@demo.com' AND c.name = 'Textbooks';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Adjustable Laptop Stand', 'Foldable adjustable laptop stand suitable for study desks, presentations and ergonomic laptop use.', 'excellent', 'assets/images/items/adjustable-laptop-stand.jpg', 'available', '2026-08-08 18:15:00', '2026-08-08 18:15:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student01@demo.com' AND c.name = 'Other';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Portable Whiteboard', 'Lightweight tabletop whiteboard with markers and eraser for tutoring, team discussion and quick diagrams.', 'good', 'assets/images/items/portable-whiteboard.jpg', 'available', '2026-08-07 14:20:00', '2026-08-07 14:20:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff06@demo.com' AND c.name = 'Other';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Wireless Presentation Clicker', 'USB wireless presentation remote with slide navigation and laser pointer for classroom presentations.', 'excellent', 'assets/images/items/wireless-presentation-clicker.jpg', 'available', '2026-08-06 12:30:00', '2026-08-06 12:30:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'staff08@demo.com' AND c.name = 'Other';

INSERT INTO items (owner_id, category_id, title, description, condition_status, image_path, availability_status, created_at, updated_at)
SELECT u.id, c.id, 'Graphing Notebook Pack', 'Pack of reusable graph and engineering notebooks for sketches, circuits, plots and lab calculations.', 'good', 'assets/images/items/graphing-notebook-pack.jpg', 'available', '2026-08-05 10:45:00', '2026-08-05 10:45:00'
FROM users u CROSS JOIN categories c
WHERE u.email = 'student10@demo.com' AND c.name = 'Other';



-- Demo borrowing history and active/pending requests

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-18', '2026-08-20', 'Needed for a microcontroller lab prototype and sensor testing.', 'pending', '2026-08-15 08:00:00', NULL, NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Arduino Uno Starter Kit' AND owner.email = 'student01@demo.com' AND b.email = 'student02@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-19', '2026-08-21', 'For recording a short course presentation and campus interview.', 'pending', '2026-08-15 07:50:00', NULL, NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Sony Alpha Mirrorless Camera' AND owner.email = 'staff03@demo.com' AND b.email = 'student04@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-17', '2026-08-19', 'Needed for an IoT networking demonstration.', 'pending', '2026-08-15 07:35:00', NULL, NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Raspberry Pi 4 Kit' AND owner.email = 'student03@demo.com' AND b.email = 'staff02@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-20', '2026-08-22', 'For measuring components in a design assignment.', 'pending', '2026-08-15 07:15:00', NULL, NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Digital Vernier Caliper' AND owner.email = 'student05@demo.com' AND b.email = 'student09@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-18', '2026-08-20', 'For a recorded seminar session in a low-light room.', 'pending', '2026-08-15 06:55:00', NULL, NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Camera Lighting Kit' AND owner.email = 'student04@demo.com' AND b.email = 'staff08@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-22', '2026-08-25', 'Need the book while preparing tutorial problems for students.', 'pending', '2026-08-14 19:30:00', NULL, NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Engineering Mathematics' AND owner.email = 'staff01@demo.com' AND b.email = 'staff04@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-14', '2026-08-17', 'For documenting a departmental project showcase.', 'approved', '2026-08-12 17:40:00', '2026-08-13 09:15:00', NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Canon EOS 200D DSLR' AND owner.email = 'student02@demo.com' AND b.email = 'student06@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-13', '2026-08-16', 'For checking voltages during an electronics lab session.', 'approved', '2026-08-11 13:25:00', '2026-08-12 10:05:00', NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Digital Multimeter' AND owner.email = 'staff01@demo.com' AND b.email = 'staff05@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-15', '2026-08-18', 'For a group presentation and prototype demonstration.', 'approved', '2026-08-13 16:30:00', '2026-08-14 08:45:00', NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Portable Projector' AND owner.email = 'staff06@demo.com' AND b.email = 'student08@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-12', '2026-08-14', 'Using it to review PHP/MySQL examples before a lab session.', 'approved', '2026-08-10 11:10:00', '2026-08-11 09:00:00', NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Web Programming with PHP' AND owner.email = 'staff07@demo.com' AND b.email = 'staff09@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-01', '2026-08-04', 'Needed for differential-equation practice.', 'returned', '2026-07-30 14:20:00', '2026-07-31 10:05:00', '2026-08-04 16:15:00'
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Engineering Mathematics' AND owner.email = 'staff01@demo.com' AND b.email = 'student01@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-07-28', '2026-07-30', 'For a short embedded systems demonstration.', 'returned', '2026-07-26 09:30:00', '2026-07-27 11:10:00', '2026-07-30 15:40:00'
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Arduino Uno Starter Kit' AND owner.email = 'student01@demo.com' AND b.email = 'staff03@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-03', '2026-08-06', 'For graph and tree revision before a quiz.', 'returned', '2026-08-01 18:10:00', '2026-08-02 09:45:00', '2026-08-06 13:20:00'
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Data Structures and Algorithms' AND owner.email = 'staff09@demo.com' AND b.email = 'student07@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-07', '2026-08-09', 'For recording a short outdoor field demonstration.', 'returned', '2026-08-05 12:15:00', '2026-08-06 10:30:00', '2026-08-09 17:05:00'
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'GoPro Action Camera' AND owner.email = 'staff05@demo.com' AND b.email = 'staff02@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-07-25', '2026-07-27', 'Needed for assembling a small prototype enclosure.', 'returned', '2026-07-23 16:25:00', '2026-07-24 08:55:00', '2026-07-27 14:35:00'
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Electronics Tool Kit' AND owner.email = 'staff07@demo.com' AND b.email = 'student10@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-05', '2026-08-08', 'Borrowed for tutorial preparation.', 'returned', '2026-08-03 10:05:00', '2026-08-04 09:15:00', '2026-08-08 12:10:00'
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Physics for Engineers' AND owner.email = 'student06@demo.com' AND b.email = 'staff10@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-07-30', '2026-08-02', 'For soldering headers on a sensor board.', 'returned', '2026-07-28 15:10:00', '2026-07-29 10:45:00', '2026-08-02 17:20:00'
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Soldering Station' AND owner.email = 'staff04@demo.com' AND b.email = 'student04@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-14', '2026-08-16', 'Wanted the camera for another project during the same period.', 'rejected', '2026-08-11 10:40:00', '2026-08-13 09:16:00', NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Canon EOS 200D DSLR' AND owner.email = 'student02@demo.com' AND b.email = 'student03@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-10', '2026-08-12', 'Requested for a networking workshop.', 'rejected', '2026-08-08 13:30:00', '2026-08-09 08:50:00', NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Raspberry Pi 4 Kit' AND owner.email = 'student03@demo.com' AND b.email = 'staff06@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-12', '2026-08-14', 'For an informal project discussion.', 'rejected', '2026-08-10 17:25:00', '2026-08-11 09:05:00', NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Portable Projector' AND owner.email = 'staff06@demo.com' AND b.email = 'student05@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-16', '2026-08-17', 'Needed for a class presentation.', 'rejected', '2026-08-13 10:20:00', '2026-08-14 12:00:00', NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Wireless Presentation Clicker' AND owner.email = 'staff08@demo.com' AND b.email = 'student08@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-16', '2026-08-18', 'Originally planned for a video shoot that was postponed.', 'cancelled', '2026-08-12 15:45:00', '2026-08-13 18:00:00', NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Camera Lighting Kit' AND owner.email = 'student04@demo.com' AND b.email = 'student09@demo.com'
LIMIT 1;

INSERT INTO borrow_requests (item_id, borrower_id, borrow_from, borrow_until, note, status, requested_at, decision_at, returned_at)
SELECT i.id, b.id, '2026-08-19', '2026-08-20', 'Requested for a supervised demonstration, later no longer needed.', 'cancelled', '2026-08-13 12:10:00', '2026-08-14 09:20:00', NULL
FROM items i
INNER JOIN users owner ON owner.id = i.owner_id
CROSS JOIN users b
WHERE i.title = 'Basic Chemistry Glassware Set' AND owner.email = 'staff02@demo.com' AND b.email = 'student01@demo.com'
LIMIT 1;
