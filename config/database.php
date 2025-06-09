<?php
/**
 * Database Configuration
 * Handles database connection and configuration settings
 */

// Load environment variables (choose one method)
// Method 1: If using vlucas/phpdotenv
// require_once 'vendor/autoload.php';
// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
// $dotenv->load();

// Method 2: If using manual loader
require_once 'env_loader.php';

class Database {
    // Database configuration - now loaded from environment variables
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;
    public $conn;

    public function __construct() {
        // Load configuration from environment variables with fallbacks
        $this->host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?? 'task_manager';
        $this->username = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?? 'root';
        $this->password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?? '';
        $this->port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? 3306;
    }

    /**
     * Establish database connection
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new mysqli(
                $this->host,
                $this->username,
                $this->password,
                $this->db_name,
                $this->port
            );
            
            // Set charset to utf8mb4 for better Unicode support
            $this->conn->set_charset("utf8mb4");
            
            if ($this->conn->connect_error) {
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
        } catch(Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Database connection failed. Please try again later.");
        }
        
        return $this->conn;
    }

    /**
     * Close database connection
     */
    public function closeConnection() {
        if ($this->conn) {
            $this->conn->close();
        }
    }
}
?>