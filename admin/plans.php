<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once '../config/database.php';

// Fetch all plans
$stmt = $conn->query("SELECT * FROM plans ORDER BY price ASC");
$plans = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plans Management - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .plan-card {
            transition: transform 0.2s;
            height: 100%;
        }
        .plan-card:hover {
            transform: translateY(-5px);
        }
        .feature-list {
            list-style: none;
            padding-left: 0;
        }
        .feature-list li {
            padding: 5px 0;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        .feature-list li:last-child {
            border-bottom: none;
        }
        .feature-list li i {
            color: #28a745;
            margin-right: 8px;
        }
        .price-tag {
            font-size: 2rem;
            font-weight: bold;
            color: #2c3e50;
        }
        .duration-badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            background: #e9ecef;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Subscription Plans</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                    <i class="fas fa-plus"></i> Add New Plan
                </button>
            </div>

            <div class="row">
                <?php foreach ($plans as $plan): ?>
                <div class="col-md-4 mb-4">
                    <div class="card plan-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h3 class="card-title mb-0"><?php echo htmlspecialchars($plan['name']); ?></h3>
                                <span class="duration-badge">
                                    <?php echo $plan['duration']; ?> days
                                </span>
                            </div>
                            
                            <div class="price-tag mb-3">
                                $<?php echo number_format($plan['price'], 2); ?>
                            </div>
                            
                            <p class="card-text text-muted mb-3">
                                <?php echo htmlspecialchars($plan['description']); ?>
                            </p>
                            
                            <h6 class="mb-2">Features:</h6>
                            <ul class="feature-list mb-4">
                                <?php 
                                $features = json_decode($plan['features'], true);
                                foreach ($features as $feature): 
                                ?>
                                <li>
                                    <i class="fas fa-check-circle"></i>
                                    <?php echo htmlspecialchars($feature); ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <div class="d-flex justify-content-end gap-2">
                                <button class="btn btn-warning btn-sm" onclick="editPlan(<?php echo $plan['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="deletePlan(<?php echo $plan['id']; ?>)">
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

    <!-- Add Plan Modal -->
    <div class="modal fade" id="addPlanModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addPlanForm">
                        <div class="mb-3">
                            <label class="form-label">Plan Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Price ($)</label>
                            <input type="number" class="form-control" name="price" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Duration (days)</label>
                            <input type="number" class="form-control" name="duration" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Features (one per line)</label>
                            <textarea class="form-control" name="features" rows="5" required></textarea>
                            <small class="text-muted">Enter each feature on a new line</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="savePlan()">Save Plan</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editPlan(planId) {
            // Fetch plan details
            fetch('get_plan.php?id=' + planId)
                .then(response => response.json())
                .then(plan => {
                    // Populate the form
                    const form = document.getElementById('addPlanForm');
                    form.name.value = plan.name;
                    form.price.value = plan.price;
                    form.duration.value = plan.duration;
                    form.description.value = plan.description;
                    form.features.value = JSON.parse(plan.features).join('\n');
                    
                    // Add plan ID to form
                    const idInput = document.createElement('input');
                    idInput.type = 'hidden';
                    idInput.name = 'id';
                    idInput.value = planId;
                    form.appendChild(idInput);
                    
                    // Change modal title and button
                    document.querySelector('#addPlanModal .modal-title').textContent = 'Edit Plan';
                    document.querySelector('#addPlanModal .btn-primary').textContent = 'Update Plan';
                    document.querySelector('#addPlanModal .btn-primary').onclick = () => updatePlan();
                    
                    // Show modal
                    new bootstrap.Modal(document.getElementById('addPlanModal')).show();
                })
                .catch(error => {
                    alert('Error fetching plan details: ' + error);
                });
        }

        function updatePlan() {
            const form = document.getElementById('addPlanForm');
            const formData = new FormData(form);
            formData.append('action', 'edit');
            
            // Convert features textarea to JSON array
            const features = formData.get('features')
                .split('\n')
                .filter(feature => feature.trim() !== '');
            formData.set('features', JSON.stringify(features));

            fetch('plan_actions.php', {
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
                alert('Error updating plan: ' + error);
            });
        }

        function deletePlan(planId) {
            if (confirm('Are you sure you want to delete this plan?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', planId);

                fetch('plan_actions.php', {
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
                    alert('Error deleting plan: ' + error);
                });
            }
        }

        function savePlan() {
            const form = document.getElementById('addPlanForm');
            const formData = new FormData(form);
            formData.append('action', 'add');
            
            // Convert features textarea to JSON array
            const features = formData.get('features')
                .split('\n')
                .filter(feature => feature.trim() !== '');
            formData.set('features', JSON.stringify(features));

            fetch('plan_actions.php', {
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
                alert('Error saving plan: ' + error);
            });
        }

        // Reset form when modal is closed
        document.getElementById('addPlanModal').addEventListener('hidden.bs.modal', function () {
            const form = document.getElementById('addPlanForm');
            form.reset();
            form.removeAttribute('data-plan-id');
            document.querySelector('#addPlanModal .modal-title').textContent = 'Add New Plan';
            document.querySelector('#addPlanModal .btn-primary').textContent = 'Save Plan';
            document.querySelector('#addPlanModal .btn-primary').onclick = savePlan;
        });
    </script>
</body>
</html> 