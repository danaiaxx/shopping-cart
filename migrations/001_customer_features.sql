-- LUMIÈRE BEAUTY customer features migration
-- Run once in phpMyAdmin on the `shopping` database

USE `shopping`;

-- Users
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `address_line1` VARCHAR(200) DEFAULT NULL,
  `address_line2` VARCHAR(200) DEFAULT NULL,
  `city` VARCHAR(100) DEFAULT NULL,
  `province` VARCHAR(100) DEFAULT NULL,
  `postal_code` VARCHAR(20) DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Products: category and featured flag (ignore errors if columns already exist)
ALTER TABLE `products` ADD COLUMN `category` VARCHAR(50) NOT NULL DEFAULT 'Makeup';
ALTER TABLE `products` ADD COLUMN `is_featured` TINYINT(1) NOT NULL DEFAULT 0;

-- Clear legacy anonymous cart rows before user scoping
DELETE FROM `cart`;

ALTER TABLE `cart` ADD COLUMN `user_id` INT NOT NULL AFTER `cart_id`;
ALTER TABLE `cart` ADD UNIQUE KEY `unique_user_product` (`user_id`, `product_id`);
ALTER TABLE `cart` ADD CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

-- Orders
CREATE TABLE IF NOT EXISTS `orders` (
  `order_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `order_number` VARCHAR(30) NOT NULL,
  `status` ENUM('processing', 'shipped', 'delivered') NOT NULL DEFAULT 'processing',
  `tracking_number` VARCHAR(40) NOT NULL,
  `payment_method` ENUM('cod', 'gcash', 'card') NOT NULL,
  `subtotal` INT NOT NULL,
  `shipping` INT NOT NULL DEFAULT 0,
  `total` INT NOT NULL,
  `ship_name` VARCHAR(100) NOT NULL,
  `ship_email` VARCHAR(150) NOT NULL,
  `ship_phone` VARCHAR(30) NOT NULL,
  `ship_address_line1` VARCHAR(200) NOT NULL,
  `ship_address_line2` VARCHAR(200) DEFAULT NULL,
  `ship_city` VARCHAR(100) NOT NULL,
  `ship_province` VARCHAR(100) NOT NULL,
  `ship_postal_code` VARCHAR(20) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`order_id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `order_items` (
  `order_item_id` INT NOT NULL AUTO_INCREMENT,
  `order_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `product_name` VARCHAR(100) NOT NULL,
  `product_price` INT NOT NULL,
  `quantity` INT NOT NULL,
  PRIMARY KEY (`order_item_id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Auto-categorize products by name
UPDATE `products` SET `category` = 'Skincare' WHERE `product_name` LIKE '%Setting Spray%' OR `product_name` LIKE '%Primer%';
UPDATE `products` SET `category` = 'Tools & Accessories' WHERE `product_name` LIKE '%Brush%' OR `product_name` LIKE '%Curler%' OR `product_name` LIKE '%Blender%' OR `product_name` LIKE '%Eyelashes%';
UPDATE `products` SET `category` = 'Makeup' WHERE `category` = 'Makeup' OR `category` IS NULL OR `category` = '';

-- Featured products for homepage carousel
UPDATE `products` SET `is_featured` = 1 WHERE `product_id` IN (1, 6, 12, 13, 18, 23);
