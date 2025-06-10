<?php
require_once 'config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Drop the existing chat_history table
    $db->exec("DROP TABLE IF EXISTS chat_history");
    
    // Create the new chat_history table with the correct structure
    $db->exec("CREATE TABLE chat_history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        message TEXT NOT NULL,
        response TEXT NOT NULL,
        model VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    echo "Chat history table has been fixed successfully!\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 