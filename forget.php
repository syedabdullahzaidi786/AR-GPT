<?php
session_start();
require_once 'config/database.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get database connection
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to generate verification token
function generateVerificationToken() {
    return bin2hex(random_bytes(32));
}

// Function to send verification email
function sendVerificationEmail($email, $token, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ardevelopers622@gmail.com';
        $mail->Password = 'illa fyhw pwkg ouex';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPDebug = 3;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: $str");
        };

        // Additional settings for better reliability
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom('ardevelopers622@gmail.com', 'AR GPT');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - AR GPT';
        
        // Get the current domain
        $verificationLink = 'https://argpt.great-site.net/verify_email_reset.php?token=' . $token;
        
        // Create a beautiful HTML email template
        $emailBody = '
        <!DOCTYPE html>
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
                }
                .header {
                    text-align: center;
                    padding: 20px 0;
                    background-color: #f8f9fa;
                    border-radius: 10px 10px 0 0;
                }
                .logo {
                    max-width: 150px;
                    height: auto;
                }
                .content {
                    background-color: #ffffff;
                    padding: 30px;
                    border-radius: 0 0 10px 10px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }
                .button {
                    display: inline-block;
                    padding: 12px 24px;
                    background-color: #1a73e8;
                    color: #ffffff;
                    text-decoration: none;
                    border-radius: 5px;
                    margin: 20px 0;
                }
                .otp-box {
                    background-color: #f8f9fa;
                    border: 2px dashed #1a73e8;
                    padding: 20px;
                    text-align: center;
                    margin: 20px 0;
                    border-radius: 5px;
                }
                .otp-code {
                    font-size: 32px;
                    font-weight: bold;
                    color: #1a73e8;
                    letter-spacing: 5px;
                }
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    font-size: 12px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="cid:logo" alt="AR GPT Logo" class="logo">
                </div>
                <div class="content">
                    <h2>Password Reset</h2>
                    <p>Hello,</p>
                    <p>Please use the following OTP to reset your password:</p>
                    
                    <div class="otp-box">
                        <div class="otp-code">' . $otp . '</div>
                    </div>
                    
                    <p>Or click the button below to verify your email and proceed with password reset:</p>
                    
                    <div style="text-align: center;">
                        <a href="' . $verificationLink . '" class="button">Verify Email</a>
                    </div>
                    
                    <p>This OTP will expire in 10 minutes.</p>
                    
                    <p>If you did not request this password reset, please ignore this email or contact support if you have concerns.</p>
                    
                    <p>Best regards,<br>AR GPT Team</p>
                </div>
                <div class="footer">
                    <p>This is an automated message, please do not reply to this email.</p>
                    <p>&copy; ' . date('Y') . ' AR GPT. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->Body = $emailBody;
        
        // Add logo as embedded image
        $mail->addEmbeddedImage('./images/logo.png', 'logo');

        $mail->send();
        error_log("Verification email sent successfully to: $email");
        return true;
    } catch (Exception $e) {
        error_log("Failed to send verification email to $email. Error: " . $e->getMessage());
        error_log("SMTP Debug Info: " . print_r($mail->ErrorInfo, true));
        return false;
    }
}

// Function to generate OTP
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Function to send OTP email
function sendOTPEmail($email, $otp) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'ardevelopers622@gmail.com';
        $mail->Password = 'illa fyhw pwkg ouex';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPDebug = 3;
        $mail->Debugoutput = function($str, $level) {
            error_log("PHPMailer Debug: $str");
        };

        // Additional settings for better reliability
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom('ardevelopers622@gmail.com', 'AR GPT');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset OTP - AR GPT';
        
        // Create a beautiful HTML email template
        $emailBody = '
        <!DOCTYPE html>
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
                }
                .header {
                    text-align: center;
                    padding: 20px 0;
                    background-color: #f8f9fa;
                    border-radius: 10px 10px 0 0;
                }
                .logo {
                    max-width: 150px;
                    height: auto;
                }
                .content {
                    background-color: #ffffff;
                    padding: 30px;
                    border-radius: 0 0 10px 10px;
                    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
                }
                .otp-box {
                    background-color: #f8f9fa;
                    border: 2px dashed #1a73e8;
                    padding: 20px;
                    text-align: center;
                    margin: 20px 0;
                    border-radius: 5px;
                }
                .otp-code {
                    font-size: 32px;
                    font-weight: bold;
                    color: #1a73e8;
                    letter-spacing: 5px;
                }
                .footer {
                    text-align: center;
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #eee;
                    font-size: 12px;
                    color: #666;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="cid:logo" alt="AR GPT Logo" class="logo">
                </div>
                <div class="content">
                    <h2>Password Reset OTP</h2>
                    <p>Hello,</p>
                    <p>Please use the following OTP to reset your password:</p>
                    
                    <div class="otp-box">
                        <div class="otp-code">' . $otp . '</div>
                    </div>
                    
                    <p>This OTP will expire in 10 minutes.</p>
                    
                    <p>If you did not request this password reset, please ignore this email or contact support if you have concerns.</p>
                    
                    <p>Best regards,<br>AR GPT Team</p>
                </div>
                <div class="footer">
                    <p>This is an automated message, please do not reply to this email.</p>
                    <p>&copy; ' . date('Y') . ' AR GPT. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>';

        $mail->Body = $emailBody;
        
        // Add logo as embedded image
        $mail->addEmbeddedImage('./images/logo.png', 'logo');

        $mail->send();
        error_log("OTP email sent successfully to: $email");
        return true;
    } catch (Exception $e) {
        error_log("Failed to send OTP email to $email. Error: " . $e->getMessage());
        error_log("SMTP Debug Info: " . print_r($mail->ErrorInfo, true));
        return false;
    }
}

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        // Step 1: Email verification
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // Generate verification token and OTP
            $token = generateVerificationToken();
            $otp = generateOTP();
            
            // Store verification token and OTP in database
            $stmt = $conn->prepare("INSERT INTO password_reset_otps (email, verification_token, otp, expires_at) VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE))");
            $stmt->execute([$email, $token, $otp]);
            
            if (sendVerificationEmail($email, $token, $otp)) {
                // Clear any existing session variables
                unset($_SESSION['reset_email']);
                unset($_SESSION['otp_verified']);
                $_SESSION['reset_email'] = $email;
                $success = "Verification email has been sent. Please check your email for the OTP and verification link.";
            } else {
                $error = "Failed to send verification email. Please try again.";
            }
        } else {
            $error = "Email not found in our records.";
        }
    } 
    elseif (isset($_POST['otp'])) {
        // Step 2: OTP verification
        $otp = $_POST['otp'];
        $email = $_SESSION['reset_email'];
        
        $stmt = $conn->prepare("SELECT * FROM password_reset_otps WHERE email = ? AND otp = ? AND is_used = 0 AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$email, $otp]);
        
        if ($stmt->rowCount() > 0) {
            // Mark OTP as used
            $stmt = $conn->prepare("UPDATE password_reset_otps SET is_used = 1 WHERE email = ? AND otp = ?");
            $stmt->execute([$email, $otp]);
            
            $_SESSION['otp_verified'] = true;
            $success = "OTP verified successfully. Please set your new password.";
        } else {
            $error = "Invalid or expired OTP. Please try again.";
        }
    }
    elseif (isset($_POST['new_password'])) {
        // Step 3: Password update
        if (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true) {
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 8) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $email = $_SESSION['reset_email'];
                    
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                    if ($stmt->execute([$hashed_password, $email])) {
                        $success = "Password updated successfully. You can now login with your new password.";
                        // Clear all session variables
                        session_unset();
                        session_destroy();
                        session_start();
                    } else {
                        $error = "Failed to update password. Please try again.";
                    }
                } else {
                    $error = "Password must be at least 8 characters long.";
                }
            } else {
                $error = "Passwords do not match.";
            }
        } else {
            $error = "Please verify your OTP first.";
        }
    }
}

// Reset session if user is starting fresh
if (!isset($_POST['email']) && !isset($_POST['otp']) && !isset($_POST['new_password'])) {
    unset($_SESSION['reset_email']);
    unset($_SESSION['otp_verified']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - AR GPT</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .forgot-password-container {
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

        .forgot-password-title {
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

        .btn-submit {
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

        .btn-submit:hover {
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
            <div class="forgot-password-container">
                <div class="logo-container">
                    <img src="./images/logo.png" alt="AR GPT Logo">
                    <h1 class="forgot-password-title">Reset Password</h1>
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

                <?php if (!isset($_SESSION['reset_email'])): ?>
                    <!-- Step 1: Email Form -->
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">Please enter your email address</div>
                        </div>
                        <button type="submit" class="btn-submit">Send Verification Link</button>
                    </form>
                <?php elseif (isset($_SESSION['reset_email']) && !isset($_SESSION['otp_verified'])): ?>
                    <!-- Step 2: OTP Form -->
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label for="otp" class="form-label">Enter OTP</label>
                            <input type="text" class="form-control" id="otp" name="otp" required>
                            <div class="invalid-feedback">Please enter the OTP sent to your email</div>
                        </div>
                        <button type="submit" class="btn-submit">Verify OTP</button>
                    </form>
                <?php elseif (isset($_SESSION['otp_verified']) && $_SESSION['otp_verified'] === true): ?>
                    <!-- Step 3: New Password Form -->
                    <form method="POST" action="" class="needs-validation" novalidate>
                        <div class="form-group">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="invalid-feedback">Please enter your new password</div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            <div class="invalid-feedback">Please confirm your new password</div>
                        </div>
                        <button type="submit" class="btn-submit">Update Password</button>
                    </form>
                <?php endif; ?>

                <div class="login-link">
                    <a href="login.php">Back to Login</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 