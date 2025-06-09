<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();

// Handle search and filtering
$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
$priority_filter = isset($_GET['priority']) ? sanitize_input($_GET['priority']) : '';

// Build query with filters
$query = "SELECT * FROM tasks WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';
}

if (!empty($status_filter)) {
    $query .= " AND status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($priority_filter)) {
    $query .= " AND priority = ?";
    $params[] = $priority_filter;
    $types .= 's';
}

$query .= " ORDER BY created_at DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$tasks = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$database->closeConnection();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Manager - LAMP Stack CRUD Application</title>
    
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
                <a class="nav-link" href="create.php">
                    <i class="bi bi-plus-circle"></i> Add Task
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php display_flash_message(); ?>
        
        <div class="card fade-in-up">
            <div class="card-header">
                <h1><i class="bi bi-list-task"></i> Task Manager</h1>
                <p class="mb-0">Manage your tasks efficiently with this LAMP stack application</p>
            </div>
            <div class="card-body">
                <!-- Search and Filter Form -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search Tasks</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       placeholder="Search by title or description..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">All Status</option>
                                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="In Progress" <?php echo $status_filter === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="priority" class="form-label">Priority</label>
                                <select class="form-select" id="priority" name="priority">
                                    <option value="">All Priority</option>
                                    <option value="High" <?php echo $priority_filter === 'High' ? 'selected' : ''; ?>>High</option>
                                    <option value="Medium" <?php echo $priority_filter === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="Low" <?php echo $priority_filter === 'Low' ? 'selected' : ''; ?>>Low</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-outline-primary me-2">
                                    <i class="bi bi-search"></i> Filter
                                </button>
                                <a href="index.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Reset
                                </a>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="create.php" class="btn btn-success w-100">
                                    <i class="bi bi-plus-circle"></i> Add New Task
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tasks Table -->
                <?php if (empty($tasks)): ?>
                    <div class="no-tasks">
                        <i class="bi bi-inbox"></i>
                        <h3>No tasks found</h3>
                        <p>
                            <?php if (!empty($search) || !empty($status_filter) || !empty($priority_filter)): ?>
                                No tasks match your current filters. Try adjusting your search criteria.
                            <?php else: ?>
                                You haven't created any tasks yet. Click the "Add New Task" button to get started.
                            <?php endif; ?>
                        </p>
                        <a href="create.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Create Your First Task
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th data-sort="title">Title <i class="bi bi-arrow-down-up"></i></th>
                                    <th>Description</th>
                                    <th data-sort="priority">Priority <i class="bi bi-arrow-down-up"></i></th>
                                    <th data-sort="due_date">Due Date <i class="bi bi-arrow-down-up"></i></th>
                                    <th data-sort="status">Status <i class="bi bi-arrow-down-up"></i></th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td data-title="<?php echo htmlspecialchars($task['title']); ?>">
                                            <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                                        </td>
                                        <td>
                                            <div class="task-description" title="<?php echo htmlspecialchars($task['description']); ?>">
                                                <?php echo htmlspecialchars($task['description'] ?: 'No description'); ?>
                                            </div>
                                        </td>
                                        <td data-priority="<?php echo $task['priority']; ?>">
                                            <?php echo get_priority_badge($task['priority']); ?>
                                        </td>
                                        <td data-due_date="<?php echo $task['due_date']; ?>">
                                            <?php 
                                            if (empty($task['due_date']) || $task['due_date'] === '0000-00-00') {
                                                echo '<span class="text-muted">No due date</span>';
                                            } else {
                                                $due_date = new DateTime($task['due_date']);
                                                $today = new DateTime();
                                                $diff = $today->diff($due_date);
                                                
                                                if ($due_date < $today) {
                                                    echo '<span class="text-danger">' . format_date($task['due_date']) . '</span>';
                                                    echo '<br><small class="text-danger">Overdue</small>';
                                                } elseif ($diff->days <= 3) {
                                                    echo '<span class="text-warning">' . format_date($task['due_date']) . '</span>';
                                                    echo '<br><small class="text-warning">Due soon</small>';
                                                } else {
                                                    echo format_date($task['due_date']);
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td data-status="<?php echo $task['status']; ?>">
                                            <?php echo get_status_badge($task['status']); ?>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo date('M d, Y', strtotime($task['created_at'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="view.php?id=<?php echo $task['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary"
                                                   data-bs-toggle="tooltip" title="View Details">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="edit.php?id=<?php echo $task['id']; ?>" 
                                                   class="btn btn-sm btn-outline-warning"
                                                   data-bs-toggle="tooltip" title="Edit Task">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="delete.php?id=<?php echo $task['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger delete-btn"
                                                   data-task-title="<?php echo htmlspecialchars($task['title']); ?>"
                                                   data-bs-toggle="tooltip" title="Delete Task">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Task Statistics -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="row">
                                <?php
                                $total_tasks = count($tasks);
                                $pending_tasks = count(array_filter($tasks, fn($task) => $task['status'] === 'Pending'));
                                $in_progress_tasks = count(array_filter($tasks, fn($task) => $task['status'] === 'In Progress'));
                                $completed_tasks = count(array_filter($tasks, fn($task) => $task['status'] === 'Completed'));
                                ?>
                                <div class="col-md-3">
                                    <div class="card bg-primary text-white">
                                        <div class="card-body text-center">
                                            <h3><?php echo $total_tasks; ?></h3>
                                            <p class="mb-0">Total Tasks</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-secondary text-white">
                                        <div class="card-body text-center">
                                            <h3><?php echo $pending_tasks; ?></h3>
                                            <p class="mb-0">Pending</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-info text-white">
                                        <div class="card-body text-center">
                                            <h3><?php echo $in_progress_tasks; ?></h3>
                                            <p class="mb-0">In Progress</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card bg-success text-white">
                                        <div class="card-body text-center">
                                            <h3><?php echo $completed_tasks; ?></h3>
                                            <p class="mb-0">Completed</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
</body>
</html>