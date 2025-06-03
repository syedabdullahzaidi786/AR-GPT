<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Function to read and parse log file
function getLogs() {
    $logFile = __DIR__ . '/../logs/error.log';
    $logs = [];
    
    if (file_exists($logFile)) {
        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines) {
            foreach ($lines as $line) {
                // Parse log line
                if (preg_match('/\[(.*?)\] \[(.*?)\] Login attempt - Username: (.*?), IP: (.*?)(?: - (.*))?$/', $line, $matches)) {
                    $logs[] = [
                        'timestamp' => $matches[1],
                        'status' => $matches[2],
                        'username' => $matches[3],
                        'ip' => $matches[4],
                        'message' => $matches[5] ?? ''
                    ];
                }
            }
        }
    }
    
    // Sort logs by timestamp in descending order (newest first)
    usort($logs, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    return $logs;
}

$logs = getLogs();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Logs - AR Bot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .logs-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .logs-table th {
            background: #f8f9fa;
            font-weight: 600;
        }
        .status-success {
            color: #28a745;
        }
        .status-failed {
            color: #dc3545;
        }
        .refresh-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #4e73df;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        .refresh-btn:hover {
            transform: rotate(180deg);
            background: #2e59d9;
            color: white;
        }
        .content-wrapper {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            background: #f8f9fc;
        }
        @media (max-width: 768px) {
            .content-wrapper {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card logs-table">
                        <div class="card-header bg-white py-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Login Logs</h5>
                                <span class="text-muted">Total Logs: <?php echo count($logs); ?></span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Timestamp</th>
                                            <th>Status</th>
                                            <th>Username</th>
                                            <th>IP Address</th>
                                            <th>Message</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($logs)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">No logs found</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($logs as $log): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                                                <td>
                                                    <span class="status-<?php echo strtolower($log['status']); ?>">
                                                        <i class="fas fa-<?php echo $log['status'] === 'SUCCESS' ? 'check-circle' : 'times-circle'; ?>"></i>
                                                        <?php echo htmlspecialchars($log['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($log['username']); ?></td>
                                                <td><?php echo htmlspecialchars($log['ip']); ?></td>
                                                <td><?php echo htmlspecialchars($log['message']); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href="logs.php" class="refresh-btn" title="Refresh Logs">
        <i class="fas fa-sync-alt"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 