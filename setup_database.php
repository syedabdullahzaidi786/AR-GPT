<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Setup</h2>";

try {
    // Connect to MySQL without database
    $pdo = new PDO(
        "mysql:host=localhost",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p style='color: green;'>✓ Connected to MySQL successfully</p>";

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS ar_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✓ Database 'ar_bot' created or already exists</p>";

    // Connect to ar_bot database
    $pdo = new PDO(
        "mysql:host=localhost;dbname=ar_bot",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p style='color: green;'>✓ Connected to ar_bot database successfully</p>";

    // Create plans table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `plans` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(50) NOT NULL,
        `price` decimal(10,2) NOT NULL,
        `description` text NOT NULL,
        `features` text NOT NULL,
        `duration` int(11) NOT NULL COMMENT 'Duration in days',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color: green;'>✓ Plans table created successfully</p>";

    // Check if plans exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM plans");
    $planCount = $stmt->fetchColumn();

    if ($planCount == 0) {
        // Insert default plans
        $pdo->exec("INSERT INTO `plans` (`name`, `price`, `description`, `features`, `duration`) VALUES
            ('Free', 0.00, 'Basic access to AR GPT', '[\"Limited messages per day\", \"Basic AI models\", \"Standard response time\"]', 30),
            ('Pro', 9.99, 'Enhanced features and capabilities', '[\"Unlimited messages\", \"All AI models\", \"Priority response time\", \"Advanced features\"]', 30),
            ('Enterprise', 29.99, 'Complete solution for businesses', '[\"Everything in Pro\", \"Custom AI training\", \"API access\", \"Dedicated support\", \"Custom integrations\"]', 30)");
        echo "<p style='color: green;'>✓ Default plans inserted successfully</p>";
    }

    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `users` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL,
        `email` varchar(100) NOT NULL,
        `password` varchar(255) NOT NULL,
        `plan_id` int(11) DEFAULT 1,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `email` (`email`),
        CONSTRAINT `users_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color: green;'>✓ Users table created successfully</p>";

    // Create chat_history table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `chat_history` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `user_id` int(11) NOT NULL,
        `message` text NOT NULL,
        `is_user` tinyint(1) NOT NULL DEFAULT 1,
        `model` varchar(50) DEFAULT 'gemini',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `user_id` (`user_id`),
        CONSTRAINT `chat_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color: green;'>✓ Chat history table created successfully</p>";

    // Create weather_history table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `weather_history` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "<p style='color: green;'>✓ Weather history table created successfully</p>";

    // Update existing users to have Free plan if plan_id is NULL
    $pdo->exec("UPDATE `users` SET `plan_id` = 1 WHERE `plan_id` IS NULL");
    echo "<p style='color: green;'>✓ Updated existing users with Free plan</p>";

    // Test user creation
    $testEmail = "test" . time() . "@example.com";
    $testPassword = password_hash("test123", PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, plan_id) VALUES (?, ?, ?, 1)");
    $result = $stmt->execute(["Test User", $testEmail, $testPassword]);
    
    if ($result) {
        echo "<p style='color: green;'>✓ Test user created successfully</p>";
        echo "<p>Test user email: $testEmail</p>";
        echo "<p>Test user password: test123</p>";
    } else {
        echo "<p style='color: red;'>✗ Failed to create test user</p>";
    }

    echo "<p style='color: green;'>✓ Database setup completed successfully!</p>";

} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Error Code: " . $e->getCode() . "</p>";
}
?> 