CREATE DATABASE IF NOT EXISTS `ashesi_lost_found`;
USE `ashesi_lost_found`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20),
    `role` ENUM('student', 'admin') DEFAULT 'student',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
    `category_id` INT AUTO_INCREMENT PRIMARY KEY,
    `category_name` VARCHAR(100) NOT NULL UNIQUE
);

-- Insert default categories
INSERT IGNORE INTO `categories` (`category_name`) VALUES 
('Electronics'), ('Keys'), ('IDs & Cards'), ('Clothing'), ('Books & Stationery'), ('Other');

-- 3. Locations Table
CREATE TABLE IF NOT EXISTS `locations` (
    `location_id` INT AUTO_INCREMENT PRIMARY KEY,
    `location_name` VARCHAR(100) NOT NULL UNIQUE
);

-- Insert default locations
INSERT IGNORE INTO `locations` (`location_name`) VALUES 
('Norton Motulsky Hall'), ('Radcliffe Hall'), ('Wangari Maathai Hall'), 
('Cafeteria'), ('Library'), ('Engineering Building'), ('Research Building'), ('Sports Center');

-- 4. Items Table
CREATE TABLE IF NOT EXISTS `items` (
    `item_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` ENUM('lost', 'found') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `category_id` INT NOT NULL,
    `location_id` INT NOT NULL,
    `item_date` DATE NOT NULL,
    `item_time` TIME,
    `status` ENUM('open', 'claimed', 'resolved') DEFAULT 'open',
    `image_url` VARCHAR(255),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`category_id`) ON DELETE RESTRICT,
    FOREIGN KEY (`location_id`) REFERENCES `locations`(`location_id`) ON DELETE RESTRICT
);

-- 5. Claims Table
CREATE TABLE IF NOT EXISTS `claims` (
    `claim_id` INT AUTO_INCREMENT PRIMARY KEY,
    `item_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `proof_description` TEXT NOT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`item_id`) REFERENCES `items`(`item_id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
);

-- 6. Notifications Table
CREATE TABLE IF NOT EXISTS `notifications` (
    `notification_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `message` TEXT NOT NULL,
    `link` VARCHAR(255),
    `is_read` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE
);
