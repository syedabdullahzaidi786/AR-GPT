-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(100) NOT NULL,
    `email` varchar(100) NOT NULL,
    `password` varchar(255) NOT NULL,
    `plan_id` int(11) DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `verification_token` VARCHAR(64) NULL,
    `token_expiry` DATETIME NULL,
    `is_verified` TINYINT(1) DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

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

-- Create plans table
CREATE TABLE IF NOT EXISTS `plans` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL,
    `price` decimal(10,2) NOT NULL,
    `description` text NOT NULL,
    `features` text NOT NULL,
    `duration` int(11) NOT NULL COMMENT 'Duration in days',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create chat_history table
CREATE TABLE IF NOT EXISTS `chat_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `message` text NOT NULL,
    `is_user` tinyint(1) NOT NULL DEFAULT 1,
    `model` varchar(50) DEFAULT 'gemini',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `chat_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create weather_history table
CREATE TABLE IF NOT EXISTS `weather_history` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `city` varchar(100) NOT NULL,
    `temperature` decimal(5,2) NOT NULL,
    `condition` varchar(50) NOT NULL,
    `humidity` int(11) NOT NULL,
    `wind_speed` decimal(5,2) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `weather_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
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



