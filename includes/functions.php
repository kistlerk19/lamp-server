<?php
/**
 * Helper Functions for Task Manager Application
 */

/**
 * Sanitize input data
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate date format
 */
function validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Format date for display
 */
function format_date($date, $format = 'M d, Y') {
    if (empty($date) || $date === '0000-00-00') {
        return 'No due date';
    }
    return date($format, strtotime($date));
}

/**
 * Get priority badge HTML
 */
function get_priority_badge($priority) {
    $badges = [
        'Low' => '<span class="badge bg-success">Low</span>',
        'Medium' => '<span class="badge bg-warning text-dark">Medium</span>',
        'High' => '<span class="badge bg-danger">High</span>'
    ];
    
    return $badges[$priority] ?? '<span class="badge bg-secondary">Unknown</span>';
}

/**
 * Get status badge HTML
 */
function get_status_badge($status) {
    $badges = [
        'Pending' => '<span class="badge bg-secondary">Pending</span>',
        'In Progress' => '<span class="badge bg-primary">In Progress</span>',
        'Completed' => '<span class="badge bg-success">Completed</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-dark">Unknown</span>';
}

/**
 * Validate task data
 */
function validate_task_data($title, $description, $priority, $due_date, $status) {
    $errors = [];
    
    // Validate title
    if (empty($title)) {
        $errors[] = "Title is required.";
    } elseif (strlen($title) > 255) {
        $errors[] = "Title must be less than 255 characters.";
    }
    
    // Validate description
    if (strlen($description) > 1000) {
        $errors[] = "Description must be less than 1000 characters.";
    }
    
    // Validate priority
    $valid_priorities = ['Low', 'Medium', 'High'];
    if (!in_array($priority, $valid_priorities)) {
        $errors[] = "Invalid priority level.";
    }
    
    // Validate status
    $valid_statuses = ['Pending', 'In Progress', 'Completed'];
    if (!in_array($status, $valid_statuses)) {
        $errors[] = "Invalid status.";
    }
    
    // Validate due date
    if (!empty($due_date) && !validate_date($due_date)) {
        $errors[] = "Invalid date format.";
    }
    
    return $errors;
}

/**
 * Display flash messages
 */
function display_flash_message() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        
        echo "<div class='alert alert-{$type} alert-dismissible fade show' role='alert'>
                {$message}
                <button type='button' class='btn-close' data-bs-dismiss='alert'></button>
              </div>";
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

/**
 * Set flash message
 */
function set_flash_message($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Generate CSRF token
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Redirect with message
 */
function redirect_with_message($url, $message, $type = 'success') {
    set_flash_message($message, $type);
    header("Location: $url");
    exit();
}
?>