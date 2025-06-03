<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to log login attempts
function logLoginAttempt($username, $success, $message = '') {
    $logFile = __DIR__ . '/../logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    $status = $success ? 'SUCCESS' : 'FAILED';
    $logMessage = "[$timestamp] [$status] Login attempt - Username: $username, IP: $ip" . ($message ? " - $message" : "") . "\n";
    
    // Create logs directory if it doesn't exist
    if (!file_exists(dirname($logFile))) {
        mkdir(dirname($logFile), 0777, true);
    }
    
    // Ensure the log file is writable
    if (!is_writable(dirname($logFile))) {
        chmod(dirname($logFile), 0777);
    }
    
    // Append to log file with error handling
    if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false) {
        error_log("Failed to write to log file: $logFile");
    }
}

// Check if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

// Handle login
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
        logLoginAttempt($username, false, 'Empty username or password');
    } else {
        // Hardcoded credentials
        if ($username === 'admin' && $password === 'admin123') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = 1;
            $_SESSION['admin_name'] = 'Administrator';
            
            logLoginAttempt($username, true);
            header("Location: dashboard.php");
            exit();
        } else {
            $error = 'Invalid username or password';
            logLoginAttempt($username, false, 'Invalid credentials');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - AR Bot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            max-width: 400px;
            width: 90%;
            padding: 2rem;
        }
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .login-header {
            text-align: center;
            padding: 2rem 1rem;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        .logo-container {
            width: 120px;
            height: 120px;
            margin: 0 auto 1rem;
            background: white;
            border-radius: 50%;
            padding: 1rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.8rem 1rem;
            border: 2px solid #eee;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78,115,223,0.25);
        }
        .input-group-text {
            border-radius: 10px;
            border: 2px solid #eee;
            background: white;
        }
        .btn-login {
            background: #4e73df;
            border: none;
            border-radius: 10px;
            padding: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        .btn-login:hover {
            background: #2e59d9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78,115,223,0.3);
        }
        .error-message {
            background: #fee;
            color: #c00;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border: 1px solid #fcc;
        }
        .welcome-text {
            color: #5a5c69;
            font-size: 0.9rem;
            margin-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-container">
                    <img src="../images/logo.png" alt="AR Bot Logo">
                </div>
                <h4 class="mb-0">Admin Login</h4>
                <p class="welcome-text">Welcome back! Please login to your account.</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-user"></i>
                            </span>
                            <input type="text" class="form-control" name="username" placeholder="Username" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" name="password" placeholder="Password" required>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 