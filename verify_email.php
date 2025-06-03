<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

if (isset($_GET['token'])) {
    try {
        $token = $_GET['token'];
        
        // Get database connection
        $db = Database::getInstance()->getConnection();
        
        // Start transaction
        $db->beginTransaction();
        
        try {
            // Check if token exists and is valid
            $stmt = $db->prepare("SELECT id, email FROM users WHERE verification_token = ? AND token_expiry > NOW() AND is_verified = 0");
            $stmt->execute([$token]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                throw new Exception('Invalid or expired verification token');
            }
            
            // Update user as verified
            $stmt = $db->prepare("UPDATE users SET is_verified = 1, verification_token = NULL, token_expiry = NULL WHERE id = ?");
            $result = $stmt->execute([$user['id']]);
            
            if (!$result) {
                throw new Exception('Failed to verify email');
            }
            
            // Commit transaction
            $db->commit();
            
            $success = 'Email verified successfully! You can now login.';
            
            // Redirect to login page after 3 seconds
            header('Refresh: 3; URL=login.php');
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
        
    } catch (Exception $e) {
        error_log("Email Verification Error: " . $e->getMessage());
        $error = $e->getMessage();
    }
} else {
    $error = 'No verification token provided';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - AR GPT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Google Sans', 'Roboto', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .verification-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .verification-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #1a73e8;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <p class="mt-3">
                <a href="login.php" class="btn btn-primary">Go to Login</a>
            </p>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="verification-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="alert alert-success" role="alert">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html> 