<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Check</h2>";

try {
    // Test direct PDO connection
    $pdo = new PDO(
        "mysql:host=localhost",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p style='color: green;'>✓ Connected to MySQL successfully</p>";

    // Check if database exists
    $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'ar_bot'");
    if ($stmt->fetch()) {
        echo "<p style='color: green;'>✓ Database 'ar_bot' exists</p>";
    } else {
        echo "<p style='color: red;'>✗ Database 'ar_bot' does not exist</p>";
        // Create database
        $pdo->exec("CREATE DATABASE ar_bot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo "<p style='color: green;'>✓ Created database 'ar_bot'</p>";
    }

    // Connect to ar_bot database
    $pdo = new PDO(
        "mysql:host=localhost;dbname=ar_bot",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p style='color: green;'>✓ Connected to ar_bot database successfully</p>";

    // Check tables
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<h3>Existing Tables:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>$table</li>";
    }
    echo "</ul>";

    // Check plans table
    if (in_array('plans', $tables)) {
        $stmt = $pdo->query("SELECT * FROM plans");
        $plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Plans:</h3>";
        echo "<ul>";
        foreach ($plans as $plan) {
            echo "<li>{$plan['name']} - \${$plan['price']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>✗ Plans table does not exist</p>";
    }

    // Check users table
    if (in_array('users', $tables)) {
        $stmt = $pdo->query("DESCRIBE users");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo "<h3>Users Table Columns:</h3>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li>$column</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: red;'>✗ Users table does not exist</p>";
    }

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

} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
    echo "<p>Error Code: " . $e->getCode() . "</p>";
}
?> 