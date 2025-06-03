<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Handle plan activation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activation_code'])) {
    try {
        $db = Database::getInstance()->getConnection();
        $activation_code = trim($_POST['activation_code']);
        
        // Validate activation code
        if ($activation_code === 'ARPro') {
            // Update user's plan to Pro (plan_id = 2)
            $stmt = $db->prepare("UPDATE users SET plan_id = 2 WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $success = 'Pro plan activated successfully!';
            $_SESSION['user_plan'] = 2;
        } 
        else if ($activation_code === 'AREnterprise') {
            // Update user's plan to Enterprise (plan_id = 3)
            $stmt = $db->prepare("UPDATE users SET plan_id = 3 WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $success = 'Enterprise plan activated successfully!';
            $_SESSION['user_plan'] = 3;
        }
        else {
            throw new Exception('Invalid activation code');
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get current user's plan
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT p.* FROM plans p JOIN users u ON p.id = u.plan_id WHERE u.id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $current_plan = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $error = 'Error fetching plan information';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plans - AR GPT</title>
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
            color: var(--text-primary);
            padding: 2rem 0;
        }

            .plans-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
            }

            .plan-card {
                background: white;
                border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .plan-card:hover {
                transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            }

            .plan-header {
                text-align: center;
            margin-bottom: 2rem;
            }

        .plan-name {
            font-size: 2rem;
                font-weight: 600;
            color: var(--primary-color);
                margin-bottom: 0.5rem;
            }

            .plan-price {
            font-size: 1.5rem;
                color: var(--text-secondary);
            margin-bottom: 1rem;
            }

            .plan-features {
            list-style: none;
            padding: 0;
            margin: 0 0 2rem 0;
            }

        .plan-features li {
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
                display: flex;
                align-items: center;
                gap: 0.5rem;
        }

        .plan-features li:last-child {
            border-bottom: none;
        }

        .plan-features i {
            color: var(--primary-color);
        }

        .activation-form {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-top: 2rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(26, 115, 232, 0.2);
        }

        .btn-activate {
                background-color: var(--primary-color);
                color: white;
                border: none;
                border-radius: 8px;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
                font-weight: 500;
                transition: background-color 0.3s ease;
            }

        .btn-activate:hover {
                background-color: var(--secondary-color);
            }

            .current-plan-badge {
            background-color: var(--primary-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
                font-weight: 500;
            margin-bottom: 1rem;
            display: inline-block;
            }

            @media (max-width: 768px) {
            .plan-card {
                padding: 1.5rem;
            }

            .plan-name {
                font-size: 1.75rem;
            }

            .plan-price {
                font-size: 1.25rem;
            }
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 8px;
            font-weight: 500;
            font-size: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .back-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            color: white;
        }

        .back-btn i {
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .back-btn {
                padding: 0.6rem 1.2rem;
                font-size: 0.95rem;
            }
        }

        @media (max-width: 480px) {
            .back-btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="plans-container">
        <div class="text-center mb-5">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
            <h1 class="display-4 mb-3">Choose Your Plan</h1>
            <p class="lead text-muted">Select the perfect plan for your needs</p>
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

        <div class="row">
            <!-- Free Plan -->
            <div class="col-md-4">
                <div class="plan-card">
                    <div class="plan-header">
                        <h2 class="plan-name">Free</h2>
                        <div class="plan-price">$0/month</div>
                        <?php if ($current_plan['id'] == 1): ?>
                            <span class="current-plan-badge">Current Plan</span>
                        <?php endif; ?>
                    </div>
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> Limited messages per day</li>
                        <li><i class="fas fa-check"></i> Basic AI models</li>
                        <li><i class="fas fa-check"></i> Standard response time</li>
                    </ul>
                </div>
            </div>

            <!-- Pro Plan -->
            <div class="col-md-4">
                <div class="plan-card">
                    <div class="plan-header">
                        <h2 class="plan-name">Pro</h2>
                        <div class="plan-price">$9.99/month</div>
                        <?php if ($current_plan['id'] == 2): ?>
                            <span class="current-plan-badge">Current Plan</span>
                        <?php endif; ?>
                    </div>
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> Unlimited messages</li>
                        <li><i class="fas fa-check"></i> All AI models</li>
                        <li><i class="fas fa-check"></i> Priority response time</li>
                        <li><i class="fas fa-check"></i> Advanced features</li>
                    </ul>
                </div>
            </div>

            <!-- Enterprise Plan -->
            <div class="col-md-4">
                <div class="plan-card">
                    <div class="plan-header">
                        <h2 class="plan-name">Enterprise</h2>
                        <div class="plan-price">$29.99/month</div>
                        <?php if ($current_plan['id'] == 3): ?>
                            <span class="current-plan-badge">Current Plan</span>
                        <?php endif; ?>
                    </div>
                    <ul class="plan-features">
                        <li><i class="fas fa-check"></i> Everything in Pro</li>
                        <li><i class="fas fa-check"></i> Custom AI training</li>
                        <li><i class="fas fa-check"></i> API access</li>
                        <li><i class="fas fa-check"></i> Dedicated support</li>
                        <li><i class="fas fa-check"></i> Custom integrations</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Activation Form -->
        <div class="activation-form">
            <h3 class="mb-4">Activate Your Plan</h3>
            <form method="POST" class="row g-3">
                <div class="col-md-8">
                    <input type="text" class="form-control" name="activation_code" 
                           placeholder="Enter Your Activation Code" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-activate w-100">
                        <i class="fas fa-key me-2"></i>Activate
                    </button>
                </div>
            </form>
            <div class="mt-3 text-muted">
                <small>For Pro or Enterprise plan inquiries, please reach out to our support team
                <br>📩 ardevelopers622@gmail.com</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 