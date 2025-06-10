<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Database connection successful!";

    // Check plans table
    $plans = $db->query("SELECT * FROM plans")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nPlans table contents:\n";
    print_r($plans);

    // Check users table structure
    $users_structure = $db->query("DESCRIBE users")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nUsers table structure:\n";
    print_r($users_structure);

    // Get existing columns
    $columns = array_column($users_structure, 'Field');
    echo "\nExisting columns in users table:\n";
    print_r($columns);

    // Check if verification columns exist
    $required_columns = ['verification_token', 'token_expiry', 'is_verified'];
    $missing_columns = array_diff($required_columns, $columns);

    if (!empty($missing_columns)) {
        echo "\nMissing columns in users table:\n";
        foreach ($missing_columns as $column) {
            echo "- $column\n";
        }

        // Add missing columns
        echo "\nAdding missing columns...\n";
        foreach ($missing_columns as $column) {
            $sql = "ALTER TABLE users ADD COLUMN $column ";
            switch ($column) {
                case 'verification_token':
                    $sql .= "VARCHAR(64) NULL";
                    break;
                case 'token_expiry':
                    $sql .= "DATETIME NULL";
                    break;
                case 'is_verified':
                    $sql .= "TINYINT(1) DEFAULT 0";
                    break;
            }
            $db->exec($sql);
            echo "Added column: $column\n";
        }
    } else {
        echo "\nAll required columns exist in users table\n";
    }

    // Test inserting a user
    echo "\nTesting user insertion...\n";
    $name = "Test User";
    $email = "test" . time() . "@example.com";
    $password = password_hash("Test@123", PASSWORD_DEFAULT);
    $verification_token = bin2hex(random_bytes(32));
    $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));

    $stmt = $db->prepare("INSERT INTO users (name, email, password, plan_id, verification_token, token_expiry, is_verified) VALUES (?, ?, ?, 1, ?, ?, 0)");
    $result = $stmt->execute([$name, $email, $password, $verification_token, $token_expiry]);

    if ($result) {
        echo "Test user inserted successfully\n";
        $user_id = $db->lastInsertId();
        echo "User ID: $user_id\n";

        // Clean up test user
        $db->exec("DELETE FROM users WHERE id = $user_id");
        echo "Test user cleaned up\n";
    } else {
        echo "Failed to insert test user\n";
        print_r($stmt->errorInfo());
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?> 