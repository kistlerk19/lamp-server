-- Task Manager Database Setup
-- Run this script to create the database and table structure

-- Create database
CREATE DATABASE IF NOT EXISTS `task-manager`;
USE `task-manager`;

-- Create `tasks` table
CREATE TABLE IF NOT EXISTS `tasks` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    priority ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    due_date DATE,
    status ENUM('Pending', 'In Progress', 'Completed') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_status (status),
    INDEX idx_priority (priority),
    INDEX idx_due_date (due_date),
    INDEX idx_created_at (created_at)
);

-- Insert sample data for testing
INSERT INTO `tasks` (title, description, priority, due_date, status) VALUES
('Setup LAMP Stack', 'Configure Apache, MySQL, and PHP on AWS EC2', 'High', '2025-06-10', 'In Progress'),
('Database Optimization', 'Optimize MySQL queries and add proper indexing', 'Medium', '2025-06-15', 'Pending'),
('Security Review', 'Conduct security audit of the application', 'High', '2025-06-12', 'Pending'),
('Load Testing', 'Perform load testing to ensure scalability', 'Medium', '2025-06-20', 'Pending'),
('Documentation', 'Complete project documentation and deployment guide', 'Low', '2025-06-25', 'Pending');

-- Create a user for the application (optional, for production)
-- CREATE USER 'taskapp'@'%' IDENTIFIED BY 'secure_password_here';
-- GRANT SELECT, INSERT, UPDATE, DELETE ON task-manager.* TO 'taskapp'@'%';
-- FLUSH PRIVILEGES;