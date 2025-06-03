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
        addUser();
        break;
    case 'edit':
        editUser();
        break;
    case 'delete':
        deleteUser();
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function addUser() {
    global $conn;
    
    try {
        // Generate verification token
        $verification_token = bin2hex(random_bytes(32));
        $token_expiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
        
        $stmt = $conn->prepare("
            INSERT INTO users (
                name, email, password, plan_id, 
                created_at, updated_at, 
                verification_token, token_expiry, is_verified
            ) VALUES (
                :name, :email, :password, :plan_id,
                NOW(), NOW(),
                :verification_token, :token_expiry, :is_verified
            )
        ");
        
        $stmt->execute([
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'plan_id' => $_POST['plan_id'],
            'verification_token' => $verification_token,
            'token_expiry' => $token_expiry,
            'is_verified' => $_POST['is_verified']
        ]);
        
        echo json_encode(['success' => true, 'message' => 'User added successfully']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to add user: ' . $e->getMessage()]);
    }
}

function editUser() {
    global $conn;
    
    try {
        $sql = "UPDATE users SET 
                name = :name,
                email = :email,
                plan_id = :plan_id,
                is_verified = :is_verified,
                updated_at = NOW()";
        
        // Only update password if a new one is provided
        if (!empty($_POST['password'])) {
            $sql .= ", password = :password";
        }
        
        $sql .= " WHERE id = :id";
        
        $stmt = $conn->prepare($sql);
        
        $params = [
            'id' => $_POST['id'],
            'name' => $_POST['name'],
            'email' => $_POST['email'],
            'plan_id' => $_POST['plan_id'],
            'is_verified' => $_POST['is_verified']
        ];
        
        if (!empty($_POST['password'])) {
            $params['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }
        
        $stmt->execute($params);
        
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to update user: ' . $e->getMessage()]);
    }
}

function deleteUser() {
    global $conn;
    
    try {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->execute(['id' => $_POST['id']]);
        
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Failed to delete user: ' . $e->getMessage()]);
    }
}
?> 