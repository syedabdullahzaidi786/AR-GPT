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
    echo json_encode(['error' => 'Plan ID is required']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT * FROM plans WHERE id = :id");
    $stmt->execute(['id' => $_GET['id']]);
    $plan = $stmt->fetch();
    
    if ($plan) {
        echo json_encode($plan);
    } else {
        echo json_encode(['error' => 'Plan not found']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Failed to fetch plan: ' . $e->getMessage()]);
}
?> 