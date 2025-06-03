<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
require_once '../config/database.php';

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'User ID is required']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Remove sensitive data
        unset($user['password']);
        unset($user['verification_token']);
        echo json_encode($user);
    } else {
        echo json_encode(['error' => 'User not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Failed to fetch user: ' . $e->getMessage()]);
}
?> 