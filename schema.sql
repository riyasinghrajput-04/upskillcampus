-- QuickBite Food Delivery App - Database Schema
-- Run this file in phpMyAdmin or MySQL CLI: source schema.sql;

CREATE DATABASE IF NOT EXISTS quickbite;
USE quickbite;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Restaurants Table
CREATE TABLE restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    cuisine VARCHAR(100),
    address TEXT,
    phone VARCHAR(15),
    image VARCHAR(255),
    rating DECIMAL(2,1) DEFAULT 4.0,
    delivery_time VARCHAR(30),
    min_order DECIMAL(8,2) DEFAULT 0.00,
    is_open TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Menu Items Table
CREATE TABLE menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(8,2) NOT NULL,
    category VARCHAR(100),
    image VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    delivery_address TEXT,
    status ENUM('pending','confirmed','preparing','out_for_delivery','delivered','cancelled') DEFAULT 'pending',
    payment_method ENUM('cash','card','upi') DEFAULT 'cash',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
);

-- Order Items Table
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    price DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id)
);

-- ========== SAMPLE DATA ==========

INSERT INTO restaurants (name, cuisine, address, phone, image, rating, delivery_time, min_order) VALUES
('Spice Garden',   'Indian',    '12 MG Road, Patna',       '9801234567', 'spice.jpg',   4.5, '30-40 min', 99.00),
('Dragon Wok',     'Chinese',   '45 Station Rd, Muzaffarpur','9812345678', 'dragon.jpg',  4.2, '25-35 min', 79.00),
('Pizza Planet',   'Italian',   '8 Civil Lines, Patna',    '9823456789', 'pizza.jpg',   4.7, '20-30 min', 149.00),
('Burger Barn',    'Fast Food', '22 Kankar Bagh, Patna',   '9834567890', 'burger.jpg',  4.0, '15-25 min', 59.00),
('South Tadka',    'South Indian','7 Boring Rd, Patna',    '9845678901', 'south.jpg',   4.3, '35-45 min', 89.00);

INSERT INTO menu_items (restaurant_id, name, description, price, category) VALUES
-- Spice Garden
(1, 'Butter Chicken',    'Creamy tomato based chicken curry',           220.00, 'Main Course'),
(1, 'Paneer Tikka',      'Grilled cottage cheese with spices',          180.00, 'Starters'),
(1, 'Dal Makhani',       'Slow cooked black lentils',                   150.00, 'Main Course'),
(1, 'Garlic Naan',       'Soft bread with garlic butter',                40.00, 'Bread'),
(1, 'Mango Lassi',       'Sweet yogurt mango drink',                     60.00, 'Drinks'),
-- Dragon Wok
(2, 'Chicken Fried Rice','Wok-tossed rice with egg and chicken',        160.00, 'Rice'),
(2, 'Veg Hakka Noodles', 'Stir-fried noodles with vegetables',          140.00, 'Noodles'),
(2, 'Manchurian Gravy',  'Fried balls in spicy sauce',                  170.00, 'Starters'),
(2, 'Spring Rolls',      'Crispy rolls with veg filling',                80.00, 'Starters'),
-- Pizza Planet
(3, 'Margherita Pizza',  'Classic tomato sauce and mozzarella',         249.00, 'Pizza'),
(3, 'Pepperoni Pizza',   'Loaded pepperoni and cheese',                 299.00, 'Pizza'),
(3, 'Pasta Arrabbiata',  'Penne in spicy tomato sauce',                 199.00, 'Pasta'),
(3, 'Garlic Bread',      'Toasted bread with herb butter',               89.00, 'Sides'),
-- Burger Barn
(4, 'Classic Burger',    'Beef patty with lettuce and cheese',          129.00, 'Burgers'),
(4, 'Veg Burger',        'Crispy veggie patty with sauces',              99.00, 'Burgers'),
(4, 'Loaded Fries',      'Fries topped with cheese and jalapeños',       79.00, 'Sides'),
(4, 'Milkshake',         'Thick chocolate or vanilla shake',             89.00, 'Drinks'),
-- South Tadka
(5, 'Masala Dosa',       'Crispy crepe with spiced potato filling',     120.00, 'Breakfast'),
(5, 'Idli Sambar',       'Steamed rice cakes with lentil soup',          90.00, 'Breakfast'),
(5, 'Vada',              'Crispy lentil fritters',                        70.00, 'Starters'),
(5, 'Filter Coffee',     'Traditional South Indian coffee',               50.00, 'Drinks');

-- Sample user (password: password123 - bcrypt hashed)
INSERT INTO users (name, email, password, phone, address) VALUES
('Rahul Kumar', 'rahul@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543210', 'Flat 3B, Shanti Nagar, Muzaffarpur');
