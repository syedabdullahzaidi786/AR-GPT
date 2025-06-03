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

// Handle different actions
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        addPlan();
        break;
    case 'edit':
        editPlan();
        break;
    case 'delete':
        deletePlan();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function addPlan() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            INSERT INTO plans (name, price, description, features, duration)
            VALUES (:name, :price, :description, :features, :duration)
        ");
        
        $features = json_encode(explode("\n", $_POST['features']));
        
        $stmt->execute([
            'name' => $_POST['name'],
            'price' => $_POST['price'],
            'description' => $_POST['description'],
            'features' => $features,
            'duration' => $_POST['duration']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Plan added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to add plan: ' . $e->getMessage()]);
    }
}

function editPlan() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("
            UPDATE plans 
            SET name = :name,
                price = :price,
                description = :description,
                features = :features,
                duration = :duration
            WHERE id = :id
        ");
        
        $features = json_encode(explode("\n", $_POST['features']));
        
        $stmt->execute([
            'id' => $_POST['id'],
            'name' => $_POST['name'],
            'price' => $_POST['price'],
            'description' => $_POST['description'],
            'features' => $features,
            'duration' => $_POST['duration']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Plan updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to update plan: ' . $e->getMessage()]);
    }
}

function deletePlan() {
    global $conn;
    
    try {
        // First check if any users are using this plan
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE plan_id = :id");
        $stmt->execute(['id' => $_POST['id']]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            echo json_encode(['error' => 'Cannot delete plan: Users are currently subscribed to this plan']);
            return;
        }
        
        // If no users are using this plan, proceed with deletion
        $stmt = $conn->prepare("DELETE FROM plans WHERE id = :id");
        $stmt->execute(['id' => $_POST['id']]);
        
        echo json_encode(['success' => true, 'message' => 'Plan deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to delete plan: ' . $e->getMessage()]);
    }
}
?> 