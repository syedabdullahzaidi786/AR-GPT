<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once '../config/database.php';

// Get total users count
$stmt = $conn->query("SELECT COUNT(*) as total_users FROM users");
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

// Get total verified users count
$stmt = $conn->query("SELECT COUNT(*) as verified_users FROM users WHERE is_verified = 1");
$verified_users = $stmt->fetch(PDO::FETCH_ASSOC)['verified_users'];

// Get today's new users count
$stmt = $conn->query("SELECT COUNT(*) as today_users FROM users WHERE DATE(created_at) = CURDATE()");
$today_users = $stmt->fetch(PDO::FETCH_ASSOC)['today_users'];

// Get active users count (users with plans)
$stmt = $conn->query("SELECT COUNT(*) as active_users FROM users WHERE plan_id IS NOT NULL");
$active_users = $stmt->fetch(PDO::FETCH_ASSOC)['active_users'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-container {
            padding: 20px;
        }
        .welcome-header {
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        .stat-title {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-container">
            <div class="welcome-header">
                <h1>Welcome to Admin Dashboard</h1>
                <p>You are successfully logged in as Administrator.</p>
            </div>
            
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-title">Total Users</div>
                        <div class="stat-value"><?php echo number_format($total_users); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-title">Verified Users</div>
                        <div class="stat-value"><?php echo number_format($verified_users); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="stat-title">Today's New Users</div>
                        <div class="stat-value"><?php echo number_format($today_users); ?></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-title">Active Users</div>
                        <div class="stat-value"><?php echo number_format($active_users); ?></div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Recent Users</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Plan</th>
                                            <th>Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // Get recent users
                                        $stmt = $conn->query("
                                            SELECT u.*, p.name as plan_name 
                                            FROM users u 
                                            LEFT JOIN plans p ON u.plan_id = p.id 
                                            ORDER BY u.created_at DESC 
                                            LIMIT 5
                                        ");
                                        $recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        foreach ($recent_users as $user):
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['plan_name'] ?? 'No Plan'); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="users.php" class="btn btn-primary">
                                    <i class="fas fa-users me-2"></i>Manage Users
                                </a>
                                <a href="plans.php" class="btn btn-success">
                                    <i class="fas fa-tags me-2"></i>Manage Plans
                                </a>
                                <a href="settings.php" class="btn btn-info">
                                    <i class="fas fa-cog me-2"></i>Settings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 