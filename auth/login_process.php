<?php
session_start();
require_once '../config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get and sanitize input
$email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';
$remember = isset($_POST['remember']);

// Validate input
if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Please fill in all fields']);
    exit;
}

try {
    // Get database connection
    $db = Database::getInstance()->getConnection();

    // Log the attempt
    error_log("Login attempt for email: " . $email);

    // Prepare and execute query
    $stmt = $db->prepare("SELECT id, name, email, password, plan FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if user exists
    if (!$user) {
        error_log("Login failed: User not found - " . $email);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }

    // Check if password is correct
    if (!password_verify($password, $user['password'])) {
        error_log("Login failed: Invalid password for user - " . $email);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email or password'
        ]);
        exit;
    }

    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_plan'] = $user['plan'];

    // Set remember me cookie if requested
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expires = time() + (30 * 24 * 60 * 60); // 30 days

        // Store token in database
        $stmt = $db->prepare("UPDATE users SET remember_token = ?, token_expires = ? WHERE id = ?");
        $stmt->execute([$token, date('Y-m-d H:i:s', $expires), $user['id']]);

        // Set cookie
        setcookie('remember_token', $token, $expires, '/', '', true, true);
    }

    error_log("Login successful for user: " . $email);
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => 'dashboard.php'
    ]);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again later.',
        'debug' => $e->getMessage() // Remove this in production
    ]);
} 