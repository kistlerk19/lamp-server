<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$errors = [];
$title = '';
$description = '';
$priority = '';
$due_date = '';
$status = 'pending'; // Default status

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $priority = sanitize_input($_POST['priority']);
    $due_date = sanitize_input($_POST['due_date']);
    $status = sanitize_input($_POST['status']);
    
    // Validate input
    $errors = validate_task_data($title, $description, $priority, $due_date, $status);
    
    if (empty($errors)) {
        $database = new Database();
        $conn = $database->getConnection();
        
        try {
            $query = "INSERT INTO tasks (title, description, priority, due_date, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
            $stmt = $conn->prepare($query);
            
            // Handle empty due date
            $due_date_value = empty($due_date) ? null : $due_date;
            
            $stmt->bind_param("sssss", $title, $description, $priority, $due_date_value, $status);
            
            if ($stmt->execute()) {
                redirect_with_message('index.php', 'Task created successfully!', 'success');
            } else {
                $errors[] = "Error creating task. Please try again.";
            }
            
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
        
        $database->closeConnection();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task - Task Manager</title>
    
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
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card fade-in-up">
                    <div class="card-header">
                        <h2><i class="bi bi-plus-circle"></i> Create New Task</h2>
                        <p class="mb-0">Add a new task to your task list</p>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger">
                                <h6><i class="bi bi-exclamation-triangle"></i> Please fix the following errors:</h6>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="title" class="form-label">Task Title <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control <?php echo !empty($errors) && empty($title) ? 'is-invalid' : ''; ?>" 
                                           id="title" 
                                           name="title" 
                                           value="<?php echo htmlspecialchars($title); ?>" 
                                           maxlength="255" 
                                           placeholder="Enter task title..."
                                           required>
                                    <div class="invalid-feedback">
                                        Please provide a task title.
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description" 
                                              rows="4" 
                                              maxlength="1000" 
                                              placeholder="Enter task description (optional)..."><?php echo htmlspecialchars($description); ?></textarea>
                                    <div class="form-text">Maximum 1000 characters</div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="priority" class="form-label">Priority <span class="text-danger">*</span></label>
                                    <select class="form-select" id="priority" name="priority" required>
                                        <option value="">Select Priority</option>
                                        <option value="Low" <?php echo $priority === 'Low' ? 'selected' : ''; ?>>Low</option>
                                        <option value="Medium" <?php echo $priority === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                        <option value="High" <?php echo $priority === 'High' ? 'selected' : ''; ?>>High</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a priority level.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="Pending" <?php echo $status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="In Progress" <?php echo $status === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                        <option value="Completed" <?php echo $status === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a status.
                                    </div>
                                </div>

                                <div class="col-md-12 mb-4">
                                    <label for="due_date" class="form-label">Due Date</label>
                                    <input type="date" 
                                           class="form-control" 
                                           id="due_date" 
                                           name="due_date" 
                                           value="<?php echo htmlspecialchars($due_date); ?>"
                                           min="<?php echo date('Y-m-d'); ?>">
                                    <div class="form-text">Leave empty if no specific due date</div>
                                </div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="index.php" class="btn btn-secondary me-md-2">
                                    <i class="bi bi-x-circle"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Create Task
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
</body>
</html>