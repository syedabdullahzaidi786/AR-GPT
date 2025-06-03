<?php
session_start();
require_once __DIR__ . '/config/database.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set up custom error logging
$logFile = __DIR__ . '/signup_errors.log';
ini_set('log_errors', 1);
ini_set('error_log', $logFile);

// Log the start of the script
error_log("=== New Signup Attempt ===");
error_log("Time: " . date('Y-m-d H:i:s'));

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

// Function to send verification email
function sendVerificationEmail($email, $token) {
    try {
        // Load email configuration
        $config = require 'config/email.php';
        
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = $config['smtp_host'];
        $mail->SMTPAuth = true;
        $mail->Username = $config['smtp_username'];
        $mail->Password = $config['smtp_password'];
        $mail->SMTPSecure = $config['smtp_encryption'];
        $mail->Port = $config['smtp_port'];
        
        // Recipients
        $mail->setFrom($config['from_email'], $config['from_name']);
        $mail->addAddress($email);
        
        // Add logo as embedded image
        $logoPath = __DIR__ . '/images/logo.png';
        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'logo', 'logo.png', 'base64', 'image/png');
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Email - AR GPT';
        
        // Create verification link
        $verificationLink = "http://" . $_SERVER['HTTP_HOST'] . "/AR%20Bot/verify_email.php?token=" . $token;
        
        // Email body
        $mail->Body = "
            <html>
            <head>
                <style>
                    body { 
                        font-family: Arial, sans-serif; 
                        line-height: 1.6; 
                        color: #333;
                        margin: 0;
                        padding: 0;
                    }
                    .container { 
                        max-width: 600px; 
                        margin: 0 auto; 
                        padding: 20px;
                        background-color: #ffffff;
                    }
                    .logo { 
                        text-align: center;
                        margin-bottom: 30px;
                        padding: 20px 0;
                    }
                    .logo img {
                        width: 80px;
                        height: 80px;
                        object-fit: contain;
                    }
                    .content {
                        background-color: #f8f9fa;
                        padding: 30px;
                        border-radius: 10px;
                        margin-bottom: 20px;
                    }
                    h2 {
                        color: #1a73e8;
                        margin-bottom: 20px;
                        text-align: center;
                    }
                    .button { 
                        display: inline-block;
                        padding: 12px 24px;
                        background-color: #1a73e8;
                        color: white !important;
                        text-decoration: none;
                        border-radius: 5px;
                        margin: 20px 0;
                        text-align: center;
                        font-weight: bold;
                    }
                    .button:hover {
                        background-color: #1558b3;
                    }
                    .verification-link {
                        word-break: break-all;
                        background-color: #f1f3f4;
                        padding: 10px;
                        border-radius: 5px;
                        margin: 15px 0;
                    }
                    .footer { 
                        margin-top: 30px; 
                        font-size: 12px; 
                        color: #666;
                        text-align: center;
                        border-top: 1px solid #eee;
                        padding-top: 20px;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='logo'>
                        <img src='cid:logo' alt='AR GPT Logo'>
                    </div>
                    <div class='content'>
                        <h2>Welcome to AR GPT!</h2>
                        <p>Thank you for signing up. Please verify your email address by clicking the button below:</p>
                        <div style='text-align: center;'>
                            <a href='{$verificationLink}' class='button'>Verify Email Address</a>
                        </div>
                        <p>Or copy and paste this link in your browser:</p>
                        <div class='verification-link'>
                            {$verificationLink}
                        </div>
                        <p>This link will expire in 24 hours.</p>
                    </div>
                    <div class='footer'>
                        <p>If you didn't create an account, you can safely ignore this email.</p>
                    </div>
                </div>
            </body>
            </html>
        ";
        
        $mail->AltBody = "Welcome to AR GPT! Please verify your email by visiting: " . $verificationLink;
        
        $mail->send();
        error_log("Verification email sent successfully to: " . $email);
        return true;
    } catch (Exception $e) {
        error_log("Failed to send verification email to: " . $email . ". Error: " . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Log the start of signup process
        error_log("Starting signup process for email: " . $_POST['email']);

        // Validate CSRF token if implemented
        // if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        //     throw new Exception('Invalid request');
        // }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Log received data (excluding password)
        error_log("Received signup data - Name: $name, Email: $email");

        // Enhanced validation
        if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
            throw new Exception('All fields are required');
        }

        if (strlen($name) < 2 || strlen($name) > 50) {
            throw new Exception('Name must be between 2 and 50 characters');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }

        // Enhanced password validation
        if (strlen($password) < 8) {
            throw new Exception('Password must be at least 8 characters long');
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw new Exception('Password must contain at least one uppercase letter');
        }

        if (!preg_match('/[a-z]/', $password)) {
            throw new Exception('Password must contain at least one lowercase letter');
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw new Exception('Password must contain at least one number');
        }

        if (!preg_match('/[^A-Za-z0-9]/', $password)) {
            throw new Exception('Password must contain at least one special character');
        }

        if ($password !== $confirm_password) {
            throw new Exception('Passwords do not match');
        }

        // Get database connection
        try {
            $db = Database::getInstance()->getConnection();
            error_log("Database connection established successfully");
        } catch (Exception $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }

        // Start transaction
        $db->beginTransaction();
        error_log("Transaction started");

        try {
            // Check if email exists
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            if (!$stmt) {
                error_log("Failed to prepare email check statement");
                throw new Exception('Database error occurred');
            }
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                throw new Exception('Email already registered');
            }
            error_log("Email check completed - Email is available");

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            if ($hashedPassword === false) {
                error_log("Password hashing failed");
                throw new Exception('Error creating account. Please try again.');
            }
            error_log("Password hashed successfully");

            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            $tokenExpiry = date('Y-m-d H:i:s', strtotime('+24 hours'));
            error_log("Verification token generated: " . $verificationToken);

            // Insert user with hashed password
            $stmt = $db->prepare("INSERT INTO users (name, email, password, plan_id, verification_token, token_expiry, is_verified) VALUES (?, ?, ?, 1, ?, ?, 0)");
            if (!$stmt) {
                error_log("Failed to prepare insert statement");
                throw new Exception('Database error occurred');
            }
            
            error_log("Attempting to insert user with data: " . json_encode([
                'name' => $name,
                'email' => $email,
                'plan_id' => 1,
                'token_expiry' => $tokenExpiry
            ]));
            
            try {
                $result = $stmt->execute([$name, $email, $hashedPassword, $verificationToken, $tokenExpiry]);
            } catch (PDOException $e) {
                error_log("PDO Exception during user insertion: " . $e->getMessage());
                throw new Exception('Database error during user creation: ' . $e->getMessage());
            }

            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("Failed to execute insert statement. Error: " . json_encode($error));
                throw new Exception('Failed to create account: ' . $error[2]);
            }
            error_log("User inserted successfully");

            $user_id = $db->lastInsertId();
            if (!$user_id) {
                error_log("Failed to get user ID");
                throw new Exception('Failed to get user ID');
            }
            error_log("User ID obtained: $user_id");

            // Send verification email
            if (!sendVerificationEmail($email, $verificationToken)) {
                throw new Exception('Failed to send verification email. Please try again later.');
            }

            // Commit transaction
            $db->commit();
            error_log("Transaction committed successfully");

            // Set success message
            $success = 'Account created successfully! Please check your email to verify your account.';
            
            // Redirect to login page after 3 seconds
            header('Refresh: 3; URL=login.php');
            exit;

        } catch (Exception $e) {
            // Rollback transaction on error
            if ($db->inTransaction()) {
                $db->rollBack();
                error_log("Transaction rolled back due to error: " . $e->getMessage());
            }
            throw $e;
        }

    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        $error = 'Database error: ' . $e->getMessage();
    } catch (Exception $e) {
        error_log("Signup Error: " . $e->getMessage());
        $error = $e->getMessage();
    }
}

// Generate CSRF token
// $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - AR GPT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        .signup-container {
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
        }

        .logo-container img {
            width: 60px;
            height: 60px;
            margin-bottom: 1rem;
        }

        .signup-title {
            text-align: center;
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
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

        .btn-signup {
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

        .btn-signup:hover {
            background-color: var(--secondary-color);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
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
            <div class="signup-container">
                <div class="logo-container">
                    <img src="./images/logo.png" alt="AR GPT Logo">
                    <h1 class="signup-title">Create your account</h1>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <div class="form-group">
                        <label for="name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="name" required 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                               minlength="2" maxlength="50">
                        <div class="invalid-feedback">Please enter your full name (2-50 characters)</div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        <div class="invalid-feedback">Please enter a valid email address</div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required
                               minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9]).{8,}">
                        <div class="invalid-feedback">
                            Password must be at least 8 characters long and contain uppercase, lowercase, number and special character
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <div class="invalid-feedback">Passwords must match</div>
                    </div>

                    <button type="submit" class="btn-signup">Create Account</button>
                </form>

                <div class="login-link">
                    Already have an account? <a href="login.php">Log in</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Form validation
    (function () {
        'use strict'
        var forms = document.querySelectorAll('.needs-validation')
        Array.prototype.slice.call(forms).forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
    })()

    // Password match validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        if (this.value !== document.getElementById('password').value) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
    </script>
</body>
</html> 