<?php
// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-header">
        <h3>AR Bot Admin</h3>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'users.php' ? 'active' : ''; ?>" href="users.php">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'plans.php' ? 'active' : ''; ?>" href="plans.php">
                <i class="fas fa-search"></i>
                <span>Plans</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'logs.php' ? 'active' : ''; ?>" href="logs.php">
                <i class="fas fa-cog"></i>
                <span>Logs</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<style>
.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: #2c3e50;
    color: white;
    padding: 20px 0;
    transition: all 0.3s;
}

.sidebar-header {
    padding: 0 20px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    margin-bottom: 20px;
}

.sidebar-header h3 {
    color: white;
    font-size: 1.5rem;
    margin: 0;
}

.sidebar .nav-link {
    color: rgba(255,255,255,0.8);
    padding: 12px 20px;
    display: flex;
    align-items: center;
    transition: all 0.3s;
}

.sidebar .nav-link:hover {
    color: white;
    background: rgba(255,255,255,0.1);
}

.sidebar .nav-link.active {
    color: white;
    background: #3498db;
}

.sidebar .nav-link i {
    width: 20px;
    margin-right: 10px;
    font-size: 1.1rem;
}

.sidebar .nav-link span {
    font-size: 0.95rem;
}

/* Adjust main content when sidebar is present */
.main-content {
    margin-left: 250px;
    padding: 20px;
    min-height: 100vh;
    background: #f8f9fa;
}

/* Responsive sidebar */
@media (max-width: 768px) {
    .sidebar {
        width: 70px;
    }
    .sidebar .nav-link span {
        display: none;
    }
    .sidebar-header h3 {
        display: none;
    }
    .main-content {
        margin-left: 70px;
    }
}
</style> 