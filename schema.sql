-- schema.sql
CREATE DATABASE IF NOT EXISTS forbidden_codex CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE forbidden_codex;


-- users table
CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(100) NOT NULL UNIQUE,
email VARCHAR(255) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
role ENUM('buyer','seller','admin') NOT NULL DEFAULT 'buyer',
fullname VARCHAR(255) DEFAULT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- categories table
CREATE TABLE categories (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL UNIQUE
);


-- products table
CREATE TABLE products (
id INT AUTO_INCREMENT PRIMARY KEY,
seller_id INT NOT NULL,
category_id INT NULL,
title VARCHAR(255) NOT NULL,
description TEXT,
price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
stock INT DEFAULT 0,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);


-- orders table
CREATE TABLE orders (
id INT AUTO_INCREMENT PRIMARY KEY,
buyer_id INT NOT NULL,
total_amount DECIMAL(10,2) NOT NULL,
payment_status ENUM('pending','paid','failed') DEFAULT 'pending',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE
);


-- order_items table
CREATE TABLE order_items (
id INT AUTO_INCREMENT PRIMARY KEY,
order_id INT NOT NULL,
product_id INT NOT NULL,
quantity INT NOT NULL DEFAULT 1,
price DECIMAL(10,2) NOT NULL,
FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);


-- sample data
INSERT INTO categories (name) VALUES ('Electronics'),('Books'),('Clothing');

-- create admin account (password: adminpass123, stored as plain text)
INSERT INTO users (username, email, password, role, fullname)
VALUES ('admin','admin@example.com', 'adminpass123', 'admin','Site Admin');

-- create sample seller accounts (passwords stored as plain text)
INSERT INTO users (username, email, password, role, fullname) VALUES 
('mystic_merchant','merchant@codex.com', 'merchantpass', 'seller','Mystic Merchant'),
('ancient_keeper','keeper@codex.com', 'keeperpass', 'seller','Ancient Keeper'),
('code_wizard','wizard@codex.com', 'wizardpass', 'seller','Code Wizard');

-- hardcoded accounts from README (plaintext passwords)
-- john.smith / p@ssW0rd1234 (Admin)
INSERT INTO users (username, email, password, role, fullname)
VALUES ('john.smith','john.smith@example.com','p@ssW0rd1234','admin','John Smith');

-- jane.doe / securePass123 (mapped to buyer)
INSERT INTO users (username, email, password, role, fullname)
VALUES ('jane.doe','jane.doe@example.com','securePass123','buyer','Jane Doe');

-- bob.wilson / myPassword456 (mapped to buyer)
INSERT INTO users (username, email, password, role, fullname)
VALUES ('bob.wilson','bob.wilson@example.com','myPassword456','buyer','Bob Wilson');

-- alice.brown / strongPass789 (mapped to buyer)
INSERT INTO users (username, email, password, role, fullname)
VALUES ('alice.brown','alice.brown@example.com','strongPass789','buyer','Alice Brown');

-- charlie.davis / testPass321 (mapped to buyer)
INSERT INTO users (username, email, password, role, fullname)
VALUES ('charlie.davis','charlie.davis@example.com','testPass321','buyer','Charlie Davis');

-- sample products
INSERT INTO products (seller_id, category_id, title, description, price, stock) VALUES
(2, 1, 'Quantum Processing Crystal', 'A mystical crystal that enhances computational power through ancient quantum entanglement. Harness the power of parallel dimensions for your digital workings.', 299.99, 5),
(2, 1, 'Neural Interface Amulet', 'An enchanted amulet that creates a direct connection between mind and machine. Experience true digital telepathy with this forbidden artifact.', 599.99, 3),
(3, 2, 'The Codex of Digital Mysteries', 'Ancient tome containing the lost programming languages of forgotten civilizations. Unlock secrets that modern developers dare not speak.', 149.99, 10),
(3, 2, 'Scrolls of Algorithmic Wisdom', 'Sacred scrolls revealing the mathematical foundations of reality itself. Master the algorithms that govern both digital and physical realms.', 89.99, 15),
(4, 2, 'The Forbidden Git Grimoire', 'A comprehensive guide to version control magic, including rituals for merging parallel timelines and resolving conflicts across dimensions.', 79.99, 8),
(2, 3, 'Cloak of Digital Invisibility', 'Woven from encrypted fibers, this cloak renders the wearer undetectable to surveillance systems and tracking algorithms.', 399.99, 4),
(4, 3, 'Robes of the Code Master', 'Ceremonial robes that enhance programming abilities and protect against syntax errors. Blessed by the ancient developers.', 199.99, 7),
(3, 1, 'Scrying Mirror of System Monitoring', 'A mystical mirror that reveals the true state of any digital system. See through illusions and detect hidden processes.', 449.99, 6),
(4, 2, 'The Necronomicon of Debugging', 'Forbidden knowledge for summoning and banishing the most elusive bugs. Use with extreme caution - some bugs should remain sleeping.', 199.99, 5),
(2, 1, 'Ethereal Storage Vessel', 'A crystalline container that exists partially in digital space, providing unlimited storage through dimensional folding techniques.', 799.99, 2);