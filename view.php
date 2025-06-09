<?php
session_start();
require_once 'config/database.php';
// require_once 'includes/functions.php';

$task = null;

// Get task ID from URL
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($task_id <= 0) {
    redirect_with_message('index.php', 'Invalid task ID.', 'danger');
}

$database = new Database();
$conn = $database->getConnection();

// Fetch task details
try {
    $query = "SELECT * FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $task = $result->fetch_assoc();
    $stmt->close();
    
    if (!$task) {
        redirect_with_message('index.php', 'Task not found.', 'danger');
    }
} catch (Exception $e) {
    redirect_with_message('index.php', 'Error loading task.', 'danger');
}

$database->closeConnection();

// Helper functions for display
function get_priority_badge($priority) {
    $badges = [
        'Low' => 'badge bg-success',
        'Medium' => 'badge bg-warning text-dark',
        'High' => 'badge bg-danger'
    ];
    return $badges[$priority] ?? 'badge bg-secondary';
}

function get_status_badge($status) {
    $badges = [
        'Pending' => 'badge bg-secondary',
        'In Progress' => 'badge bg-info',
        'Completed' => 'badge bg-success'
    ];
    return $badges[$status] ?? 'badge bg-secondary';
}

function get_status_icon($status) {
    $icons = [
        'Pending' => 'bi-clock',
        'In Progress' => 'bi-arrow-repeat',
        'Completed' => 'bi-check-circle'
    ];
    return $icons[$status] ?? 'bi-question-circle';
}

function get_priority_icon($priority) {
    $icons = [
        'Low' => 'bi-arrow-down',
        'Medium' => 'bi-dash',
        'High' => 'bi-arrow-up'
    ];
    return $icons[$priority] ?? 'bi-dash';
}

function format_date($datetime, $format = 'M d, Y') {
    try {
        $dt = new DateTime($datetime);
        return $dt->format($format);
    } catch (Exception $e) {
        return htmlspecialchars($datetime); // fallback
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($task['title']); ?> - Task Manager</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.8.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-check2-square"></i> Task Manager
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">
                    <i class="bi bi-arrow-left"></i> Back to Tasks
                </a>
                <a class="nav-link" href="edit.php?id=<?php echo $task_id; ?>">
                    <i class="bi bi-pencil"></i> Edit Task
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Task Header -->
                <div class="card fade-in-up mb-4">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h1 class="h3 mb-2"><?php echo htmlspecialchars($task['title']); ?></h1>
                                <div class="task-meta">
                                    <span class="<?php echo get_status_badge($task['status']); ?> me-2">
                                        <i class="<?php echo get_status_icon($task['status']); ?>"></i>
                                        <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                    </span>
                                    <span class="<?php echo get_priority_badge($task['priority']); ?>">
                                        <i class="<?php echo get_priority_icon($task['priority']); ?>"></i>
                                        <?php echo ucfirst($task['priority']); ?> Priority
                                    </span>
                                </div>
                            </div>
                            <div class="task-actions">
                                <a href="edit.php?id=<?php echo $task_id; ?>" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete(<?php echo $task_id; ?>)">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Task Details -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card fade-in-up">
                            <div class="card-header">
                                <h5><i class="bi bi-file-text"></i> Task Details</h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($task['description'])): ?>
                                    <h6>Description</h6>
                                    <div class="task-description mb-4">
                                        <?php echo nl2br(htmlspecialchars($task['description'])); ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted">
                                        <i class="bi bi-info-circle"></i> No description provided for this task.
                                    </div>
                                <?php endif; ?>

                                <?php if ($task['due_date'] && $task['due_date'] !== '0000-00-00'): ?>
                                    <div class="due-date-section">
                                        <h6>Due Date</h6>
                                        <div class="due-date-info">
                                            <?php 
                                            $due_date = new DateTime($task['due_date']);
                                            $today = new DateTime();
                                            $diff = $today->diff($due_date);
                                            
                                            echo '<p class="mb-2"><strong>' . $due_date->format('F j, Y') . '</strong></p>';
                                            
                                            if ($task['status'] !== 'completed') {
                                                if ($due_date < $today) {
                                                    echo '<div class="alert alert-danger py-2 mb-0">';
                                                    echo '<i class="bi bi-exclamation-triangle"></i> ';
                                                    echo 'Overdue by ' . $diff->days . ' day' . ($diff->days !== 1 ? 's' : '');
                                                    echo '</div>';
                                                } elseif ($diff->days <= 3 && $due_date >= $today) {
                                                    echo '<div class="alert alert-warning py-2 mb-0">';
                                                    echo '<i class="bi bi-clock"></i> ';
                                                    if ($diff->days == 0) {
                                                        echo 'Due today!';
                                                    } else {
                                                        echo 'Due in ' . $diff->days . ' day' . ($diff->days !== 1 ? 's' : '');
                                                    }
                                                    echo '</div>';
                                                } else {
                                                    echo '<div class="alert alert-info py-2 mb-0">';
                                                    echo '<i class="bi bi-calendar"></i> ';
                                                    echo 'Due in ' . $diff->days . ' day' . ($diff->days !== 1 ? 's' : '');
                                                    echo '</div>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Task Information -->
                        <div class="card fade-in-up mb-3">
                            <div class="card-header">
                                <h6><i class="bi bi-info-circle"></i> Task Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="info-item mb-3">
                                    <label class="text-muted small">Status</label>
                                    <div>
                                        <span class="<?php echo get_status_badge($task['status']); ?>">
                                            <i class="<?php echo get_status_icon($task['status']); ?>"></i>
                                            <?php echo ucfirst(str_replace('_', ' ', $task['status'])); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="info-item mb-3">
                                    <label class="text-muted small">Priority</label>
                                    <div>
                                        <span class="<?php echo get_priority_badge($task['priority']); ?>">
                                            <i class="<?php echo get_priority_icon($task['priority']); ?>"></i>
                                            <?php echo ucfirst($task['priority']); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="info-item mb-3">
                                    <label class="text-muted small">Created</label>
                                    <div class="small">
                                        <?php echo format_date($task['created_at'], 'M d, Y \a\t g:i A'); ?>
                                    </div>
                                </div>

                                <?php if ($task['updated_at'] !== $task['created_at']): ?>
                                    <div class="info-item mb-3">
                                        <label class="text-muted small">Last Updated</label>
                                        <div class="small">
                                            <?php echo format_date($task['updated_at'], 'M d, Y \a\t g:i A'); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($task['due_date'] && $task['due_date'] !== '0000-00-00'): ?>
                                    <div class="info-item">
                                        <label class="text-muted small">Due Date</label>
                                        <div class="small">
                                            <?php echo format_date($task['due_date'], 'M d, Y'); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="card fade-in-up">
                            <div class="card-header">
                                <h6><i class="bi bi-lightning"></i> Quick Actions</h6>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="edit.php?id=<?php echo $task_id; ?>" class="btn btn-primary btn-sm">
                                        <i class="bi bi-pencil"></i> Edit Task
                                    </a>
                                    
                                    <?php if ($task['status'] !== 'completed'): ?>
                                        <form method="POST" action="index.php" class="d-inline">
                                            <input type="hidden" name="action" value="quick_complete">
                                            <input type="hidden" name="task_id" value="<?php echo $task_id; ?>">
                                            <button type="submit" class="btn btn-success btn-sm w-100">
                                                <i class="bi bi-check-circle"></i> Mark Complete
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $task_id; ?>)">
                                        <i class="bi bi-trash"></i> Delete Task
                                    </button>
                                    
                                    <a href="index.php" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-arrow-left"></i> Back to Tasks
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-exclamation-triangle text-danger"></i> Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this task?</p>
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> This action cannot be undone.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                        <i class="bi bi-trash"></i> Delete Task
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
    
    <script>
        function confirmDelete(taskId) {
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            confirmBtn.href = 'delete.php?id=' + taskId;
            modal.show();
        }
    </script>
</body>
</html>