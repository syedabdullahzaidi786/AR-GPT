<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once '../config/database.php';

// Fetch all users with their plan information
$stmt = $conn->query("
    SELECT u.*, p.name as plan_name 
    FROM users u 
    LEFT JOIN plans p ON u.plan_id = p.id 
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all plans for the dropdown
$plans = $conn->query("SELECT id, name FROM plans")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .user-card {
            transition: transform 0.2s;
        }
        .user-card:hover {
            transform: translateY(-5px);
        }
        .verification-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
        }
        .verified {
            background: #d4edda;
            color: #155724;
        }
        .unverified {
            background: #f8d7da;
            color: #721c24;
        }
        .plan-badge {
            background: #e9ecef;
            color: #495057;
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Users Management</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-plus"></i> Add New User
                </button>
            </div>

            <div class="row">
                <?php foreach ($users as $user): ?>
                <div class="col-md-4 mb-4">
                    <div class="card user-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($user['name'] ?? 'N/A'); ?></h5>
                                <span class="verification-badge <?php echo $user['is_verified'] ? 'verified' : 'unverified'; ?>">
                                    <?php echo $user['is_verified'] ? 'Verified' : 'Unverified'; ?>
                                </span>
                            </div>
                            
                            <p class="card-text text-muted mb-2">
                                <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email'] ?? 'N/A'); ?>
                            </p>
                            
                            <p class="card-text text-muted mb-2">
                                <i class="fas fa-tag me-2"></i>
                                <span class="plan-badge">
                                    <?php echo htmlspecialchars($user['plan_name'] ?? 'No Plan'); ?>
                                </span>
                            </p>
                            
                            <p class="card-text text-muted mb-2">
                                <i class="fas fa-calendar me-2"></i>Joined: <?php echo isset($user['created_at']) ? date('M d, Y', strtotime($user['created_at'])) : 'N/A'; ?>
                            </p>
                            
                            <p class="card-text text-muted mb-3">
                                <i class="fas fa-clock me-2"></i>Last Updated: <?php echo isset($user['updated_at']) ? date('M d, Y', strtotime($user['updated_at'])) : 'N/A'; ?>
                            </p>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-warning btn-sm" onclick="editUser(<?php echo $user['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Plan</label>
                            <select class="form-select" name="plan_id" required>
                                <option value="">Select a Plan</option>
                                <?php foreach ($plans as $plan): ?>
                                <option value="<?php echo $plan['id']; ?>">
                                    <?php echo htmlspecialchars($plan['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Verification Status</label>
                            <select class="form-select" name="is_verified" required>
                                <option value="0">Unverified</option>
                                <option value="1">Verified</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="saveUser()">Save User</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editUser(userId) {
            // Fetch user details and populate form
            fetch('get_user.php?id=' + userId)
                .then(response => response.json())
                .then(user => {
                    const form = document.getElementById('addUserForm');
                    form.name.value = user.name;
                    form.email.value = user.email;
                    form.plan_id.value = user.plan_id;
                    form.is_verified.value = user.is_verified;
                    
                    // Add user ID to form
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id';
                    idInput.value = userId;
                    form.appendChild(idInput);
                    
                    // Change modal title and button
                    document.querySelector('#addUserModal .modal-title').textContent = 'Edit User';
                    document.querySelector('#addUserModal .btn-primary').textContent = 'Update User';
                    document.querySelector('#addUserModal .btn-primary').onclick = () => updateUser();
                    
                    // Show modal
                    new bootstrap.Modal(document.getElementById('addUserModal')).show();
                })
                .catch(error => {
                    alert('Error fetching user details: ' + error);
                });
        }

        function updateUser() {
            const form = document.getElementById('addUserForm');
            const formData = new FormData(form);
            formData.append('action', 'edit');

            fetch('user_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.error);
                }
            })
            .catch(error => {
                alert('Error updating user: ' + error);
            });
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', userId);

                fetch('user_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.error);
                    }
                })
                .catch(error => {
                    alert('Error deleting user: ' + error);
                });
            }
        }

        function saveUser() {
            const form = document.getElementById('addUserForm');
            const formData = new FormData(form);
            formData.append('action', 'add');

            fetch('user_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.error);
                }
            })
            .catch(error => {
                alert('Error saving user: ' + error);
            });
        }

        // Reset form when modal is closed
        document.getElementById('addUserModal').addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('addUserForm');
            form.reset();
            form.removeAttribute('data-user-id');
            document.querySelector('#addUserModal .modal-title').textContent = 'Add New User';
            document.querySelector('#addUserModal .btn-primary').textContent = 'Save User';
            document.querySelector('#addUserModal .btn-primary').onclick = saveUser;
        });
    </script>
</body>
</html> 