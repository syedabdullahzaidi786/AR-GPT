<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Log the login attempt
        error_log("Login attempt for email: " . $_POST['email']);

        $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        // Basic validation
        if (empty($email) || empty($password)) {
            throw new Exception('Email and password are required');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Get database connection
        try {
            $db = Database::getInstance()->getConnection();
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception('Database connection failed. Please try again later.');
        }

        // Check for failed login attempts
        if (isset($_SESSION['login_attempts']) && $_SESSION['login_attempts'] >= 5) {
            if (time() - $_SESSION['last_attempt'] < 300) { // 5 minutes lockout
                throw new Exception('Too many failed attempts. Please try again in 5 minutes.');
            } else {
                $_SESSION['login_attempts'] = 0;
            }
        }

        $stmt = $db->prepare("SELECT id, name, password, plan_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            error_log("Password verified successfully for user: " . $email);
            // Reset login attempts on successful login
            $_SESSION['login_attempts'] = 0;
            
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_plan'] = $user['plan_id'];
            
            // Regenerate session ID for security
            session_regenerate_id(true);
            
            header('Location: dashboard.php');
            exit();
        } else {
            error_log("Password verification failed for user: " . $email);
            // Increment failed login attempts
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            $_SESSION['last_attempt'] = time();
            
            throw new Exception('Invalid email or password');
        }
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AI Chat Assistant</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a73e8;
            --secondary-color: #1558b3;
            --background-color: #f8f9fa;
            --text-primary: #202124;
            --text-secondary: #5f6368;
            --border-color: #e0e0e0;
        }

        body {
            background-color: var(--background-color);
            font-family: 'Google Sans', 'Roboto', sans-serif;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        .main-container {
            display: flex;
            min-height: 100vh;
        }

        .left-section {
            flex: 1;
            background: linear-gradient(135deg, #1a73e8 0%, #1558b3 100%);
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .left-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('./images/cover.png') center/cover no-repeat;
            opacity: 1;
        }

        .left-content {
            display: none;
        }

        .right-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }

        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            width: 100%;
            max-width: 400px;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .logo-container img {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
            object-fit: contain;
        }

        .login-title {
            text-align: center;
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            width: 100%;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
        }

        .btn-login {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 1rem;
            font-weight: 500;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .btn-login:hover {
            background-color: var(--secondary-color);
        }

        .signup-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .signup-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

        .forgot-password-link {
            text-align: center;
            margin-top: 1rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .forgot-password-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .forgot-password-link a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 768px) {
            .main-container {
                flex-direction: column;
            }
            .left-section {
                padding: 20px;
            }
            .right-section {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="left-section">
            <div class="left-content"></div>
        </div>
        <div class="right-section">
            <div class="login-container">
                <div class="logo-container">
                    <img src="./images/logo.png" alt="AR GPT Logo">
                    <h1 class="login-title">Welcome back</h1>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <div class="invalid-feedback">Please enter your email address</div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="invalid-feedback">Please enter your password</div>
                    </div>

                    <button type="submit" class="btn-login">Log In</button>
                </form>

                <div class="signup-link">
                    Don't have an account? <a href="signup.php">Sign up</a>
                </div>
                <div class="forgot-password-link">
                    <a href="forget.php">Forgot Password?</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 