<?php
// Prevent any output before JSON response
ob_start();

// Disable error display but enable logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '../logs/php_errors.log');

session_start();
require_once '../config/database.php';

// Clear any previous output
ob_clean();

// Set proper content type header
header('Content-Type: application/json; charset=utf-8');

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'User already logged in'
    ]);
    exit();
}

try {
    // Get database connection using singleton pattern
    $db = Database::getInstance()->getConnection();
    
    // Get the request body
    $rawInput = file_get_contents('php://input');
    if (empty($rawInput)) {
        throw new Exception('No input received');
    }
    
    $data = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';
    $email = $data['email'] ?? '';

    if (empty($username) || empty($password) || empty($email)) {
        throw new Exception('All fields are required');
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    // Check if username or email already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->rowCount() > 0) {
        throw new Exception('Username or email already exists');
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user
    $stmt = $db->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute([$username, $hashedPassword, $email]);

    // Get the new user's ID
    $userId = $db->lastInsertId();

    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username;

    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Registration successful',
        'user' => [
            'id' => $userId,
            'username' => $username,
            'email' => $email
        ]
    ]);

} catch (Exception $e) {
    error_log("Registration Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// End output buffering and send response
ob_end_flush();
?> 