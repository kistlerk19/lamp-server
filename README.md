# Task Manager

A simple yet powerful web-based task management application built with PHP, MySQL, and Bootstrap. This application allows users to create, read, update, and delete tasks with features like priority levels, due dates, and status tracking.

## Features

- ✅ **CRUD Operations**: Create, Read, Update, and Delete tasks
- 🎯 **Priority Levels**: Low, Medium, and High priority settings
- 📅 **Due Date Tracking**: Set and track task due dates with visual indicators
- 📊 **Status Management**: Pending, In Progress, and Completed statuses
- 🔍 **Advanced Filtering**: Filter tasks by status, priority, and search terms
- 📱 **Responsive Design**: Mobile-friendly interface using Bootstrap 5
- ⚡ **Real-time Feedback**: Success/error messages and form validation
- 🎨 **Modern UI**: Clean and intuitive user interface with animations

## Technology Stack

- **Backend**: PHP 7.4+ with MySQLi
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5.1.3
- **Icons**: Bootstrap Icons
- **Server**: Apache/Nginx with PHP support

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (optional, for dependencies)

### Setup Instructions

1. **Clone or Download the Project**
   ```bash
   git clone <repository-url>
   cd task-manager
   ```

2. **Database Setup**
   - Create a new MySQL database
   - Import the database schema:
   ```sql
   mysql -u your_username -p your_database_name < sql/database_setup.sql
   ```

3. **Configuration**
   - Copy and edit the database configuration:
   ```php
   // config/database.php
   private $host = "localhost";
   private $db_name = "your_database_name";
   private $username = "your_username";
   private $password = "your_password";
   ```

4. **File Permissions**
   - Ensure proper permissions for web server access
   - Make sure the web server can read all project files

5. **Access the Application**
   - Place the project in your web server's document root
   - Access via: `http://localhost/task-manager/` or your domain

## Project Structure

```
task-manager/
├── config/
│   └── database.php          # Database configuration
├── css/
│   └── style.css            # Custom styles and animations
├── js/
│   └── script.js            # Client-side JavaScript functionality
├── includes/
│   └── functions.php        # PHP helper functions
├── sql/
│   └── database_setup.sql   # Database schema and initial data
├── index.php               # Main dashboard page
├── create.php              # Add new task page
├── edit.php                # Edit existing task page
├── view.php                # Task detail view page
├── delete.php              # Task deletion handler
└── README.md               # Project documentation
```

## Database Schema

### Tasks Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT PRIMARY KEY AUTO_INCREMENT | Unique task identifier |
| `title` | VARCHAR(255) NOT NULL | Task title |
| `description` | TEXT | Detailed task description |
| `priority` | ENUM('low','medium','high') | Task priority level |
| `status` | ENUM('pending','in_progress','completed') | Task status |
| `due_date` | DATE | Task due date (nullable) |
| `created_at` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last update timestamp |

## Usage Guide

### Creating Tasks

1. Click "Add New Task" button on the dashboard
2. Fill in the required fields:
   - **Title**: Brief description of the task (required)
   - **Description**: Detailed explanation (optional)
   - **Priority**: Low, Medium, or High (required)
   - **Status**: Pending, In Progress,