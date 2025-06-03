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

// Verify the token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Check if token exists and is not expired
    $stmt = $conn->prepare("SELECT email FROM password_reset_otps WHERE verification_token = ? AND expires_at > NOW() AND is_used = 0");
    $stmt->execute([$token]);
    
    if ($stmt->rowCount() > 0) {
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = $result['email'];
        
        // Generate OTP
        $otp = generateOTP();
        
        // Store OTP in database and update verification status
        $stmt = $conn->prepare("UPDATE password_reset_otps SET otp = ?, verification_token = NULL, is_verified = 1 WHERE verification_token = ?");
        if ($stmt->execute([$otp, $token])) {
            if (sendOTPEmail($email, $otp)) {
                $_SESSION['reset_email'] = $email;
                $success = "Email verified successfully. OTP has been sent to your email.";
                // Redirect to forget.php after 3 seconds
                header("refresh:3;url=forget.php");
            } else {
                $error = "Failed to send OTP. Please try again.";
            }
        } else {
            $error = "Failed to update verification status. Please try again.";
        }
    } else {
        $error = "Invalid or expired verification link. Please request a new password reset.";
    }
} else {
    $error = "Invalid verification link.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification - AR GPT</title>
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

        .verification-container {
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

        .verification-title {
            text-align: center;
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            width: 100%;
        }

        .alert {
            border-radius: 8px;
            margin-bottom: 1.5rem;
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
            <div class="verification-container">
                <div class="logo-container">
                    <img src="./images/logo.png" alt="AR GPT Logo">
                    <h1 class="verification-title">Email Verification</h1>
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

                <div class="login-link">
                    <a href="login.php">Back to Login</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 