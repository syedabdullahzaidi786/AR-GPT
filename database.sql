-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    plan_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create plans table
CREATE TABLE IF NOT EXISTS plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    price DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create chat_history table
CREATE TABLE IF NOT EXISTS chat_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    response TEXT NOT NULL,
    model VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create weather_history table
CREATE TABLE IF NOT EXISTS weather_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    city VARCHAR(100) NOT NULL,
    temperature DECIMAL(5,2) NOT NULL,
    `condition` VARCHAR(100) NOT NULL,
    humidity INT NOT NULL,
    wind_speed DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create password_reset_otps table
CREATE TABLE IF NOT EXISTS `password_reset_otps` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(100) NOT NULL,
    `otp` varchar(6) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `expires_at` timestamp NULL DEFAULT NULL,
    `is_used` tinyint(1) NOT NULL DEFAULT 0,
    `verification_token` VARCHAR(64) NULL,
    PRIMARY KEY (`id`),
    KEY `email` (`email`),
    KEY `verification_token` (`verification_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add foreign key constraint for plan_id in users table
ALTER TABLE `users` 
ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`);

-- Insert default plans
INSERT INTO `plans` (`name`, `price`, `description`, `features`, `duration`) VALUES
('Free', 0.00, 'Basic access to AR GPT', '["Limited messages per day", "Basic AI models", "Standard response time"]', 30),
('Pro', 9.99, 'Enhanced features and capabilities', '["Unlimited messages", "All AI models", "Priority response time", "Advanced features"]', 30),
('Enterprise', 29.99, 'Complete solution for businesses', '["Everything in Pro", "Custom AI training", "API access", "Dedicated support", "Custom integrations"]', 30);

-- Update existing users to have Free plan if plan_id is NULL
UPDATE `users` SET `plan_id` = 1 WHERE `plan_id` IS NULL;