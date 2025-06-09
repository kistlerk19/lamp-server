<?php
/**
 * Health check endpoint for load balancer
 */
header('Content-Type: application/json');

// Load environment configuration
require_once 'config/database.php';

$health = [
    'status' => 'healthy',
    'timestamp' => date('c'),
    'server' => gethostname(),
    'services' => []
];

try {
    // Test database connection
    $database = new Database();
    $conn = $database->getConnection();
    
    if ($conn) {
        $health['services']['database'] = 'connected';
        $database->closeConnection();
    } else {
        $health['services']['database'] = 'failed';
        $health['status'] = 'unhealthy';
    }
} catch (Exception $e) {
    $health['services']['database'] = 'failed';
    $health['status'] = 'unhealthy';
    $health['error'] = $e->getMessage();
}

// Check disk space
$disk_free = disk_free_space('/');
$disk_total = disk_total_space('/');
$disk_usage = round((($disk_total - $disk_free) / $disk_total) * 100, 2);

$health['services']['disk'] = [
    'usage_percent' => $disk_usage,
    'status' => $disk_usage > 90 ? 'critical' : ($disk_usage > 80 ? 'warning' : 'ok')
];

if ($disk_usage > 90) {
    $health['status'] = 'unhealthy';
}

// Set appropriate HTTP status code
http_response_code($health['status'] === 'healthy' ? 200 : 503);

echo json_encode($health, JSON_PRETTY_PRINT);
?>