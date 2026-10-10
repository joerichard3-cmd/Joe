CREATE DATABASE IF NOT EXISTS joemart
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE joemart;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','seller','customer') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  seller_id INT NULL,
  category_id INT NULL,
  name VARCHAR(150) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  image_url VARCHAR(500) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_product_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE SET NULL,
  CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  address TEXT NOT NULL,
  status ENUM('Pending','Confirmed','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  payment_method ENUM('COD') NOT NULL DEFAULT 'COD',
  payment_status ENUM('Pending','Paid','Failed') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_customer FOREIGN KEY (customer_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_item_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  CONSTRAINT fk_item_product FOREIGN KEY (product_id) REFERENCES products(id)
);

INSERT IGNORE INTO categories (name) VALUES
('Vegetables'),('Fruits'),('Rice & Grains'),('Dairy Products'),('Snacks');

INSERT INTO products (name, description, price, stock, category_id)
SELECT 'Tomato','Fresh tomatoes',30.00,100,id FROM categories WHERE name='Vegetables'
AND NOT EXISTS (SELECT 1 FROM products WHERE name='Tomato');
INSERT INTO products (name, description, price, stock, category_id)
SELECT 'Potato','Fresh potatoes',40.00,100,id FROM categories WHERE name='Vegetables'
AND NOT EXISTS (SELECT 1 FROM products WHERE name='Potato');
INSERT INTO products (name, description, price, stock, category_id)
SELECT 'Apple','Fresh apples',120.00,50,id FROM categories WHERE name='Fruits'
AND NOT EXISTS (SELECT 1 FROM products WHERE name='Apple');
INSERT INTO products (name, description, price, stock, category_id)
SELECT 'Rice 1 kg','Quality rice',65.00,100,id FROM categories WHERE name='Rice & Grains'
AND NOT EXISTS (SELECT 1 FROM products WHERE name='Rice 1 kg');
INSERT INTO products (name, description, price, stock, category_id)
SELECT 'Milk 1 litre','Fresh milk',30.00,50,id FROM categories WHERE name='Dairy Products'
AND NOT EXISTS (SELECT 1 FROM products WHERE name='Milk 1 litre');
