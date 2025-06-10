<?php
require_once 'config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Test users table
    $stmt = $db->query("DESCRIBE users");
    echo "Users table structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "\n";
    
    // Test plans table
    $stmt = $db->query("DESCRIBE plans");
    echo "Plans table structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "\n";
    
    // Test chat_history table
    $stmt = $db->query("DESCRIBE chat_history");
    echo "Chat history table structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "\n";
    
    // Test weather_history table
    $stmt = $db->query("DESCRIBE weather_history");
    echo "Weather history table structure:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?> 