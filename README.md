# Task Manager - LAMP Stack Application

A comprehensive web-based task management application built with PHP, MySQL, and Bootstrap. This application provides a complete CRUD (Create, Read, Update, Delete) interface for managing tasks with features like priority levels, due dates, and status tracking.

![Task Manager Screenshot](https://via.placeholder.com/800x400?text=Task+Manager+Screenshot)

## Features

- ✅ **CRUD Operations**: Create, read, update, and delete tasks
- 🎯 **Priority Levels**: Low, Medium, and High priority settings
- 📅 **Due Date Tracking**: Set and track task due dates with visual indicators
- 📊 **Status Management**: Pending, In Progress, and Completed statuses
- 🔍 **Advanced Filtering**: Filter tasks by status, priority, and search terms
- 📱 **Responsive Design**: Mobile-friendly interface using Bootstrap 5
- ⚡ **Real-time Feedback**: Success/error messages and form validation
- 🎨 **Modern UI**: Clean and intuitive user interface with animations
- 🔒 **Security Features**: CSRF protection, input sanitization, and secure database access
- 🚀 **Ansible Deployment**: Automated deployment to AWS infrastructure

## Technology Stack

- **Backend**: PHP 7.4+ with MySQLi
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework**: Bootstrap 5.1.3
- **Icons**: Bootstrap Icons
- **Server**: Apache with PHP support
- **Deployment**: Ansible automation for AWS EC2 and RDS

## Project Structure

```
task-manager/
├── config/
│   └── database.php          # Database configuration
├── css/
│   └── style.css            # Custom styles and animations
├── includes/
│   └── functions.php        # PHP helper functions
├── js/
│   └── script.js            # Client-side JavaScript functionality
├── sql/
│   └── database_setup.sql   # Database schema and initial data
├── templates/               # Ansible deployment templates
│   ├── apache-vhost.j2      # Apache virtual host configuration
│   ├── database.php.j2      # Database config template
│   ├── env.j2               # Environment variables template
│   ├── health-check.sh.j2   # Health check script
│   ├── logrotate.j2         # Log rotation configuration
│   └── php.ini.j2           # PHP configuration
├── group_vars/              # Ansible group variables
│   ├── all.yml              # Global variables
│   └── webservers.yml       # Web server variables
├── host_vars/               # Ansible host-specific variables
│   ├── web1.yml             # Web1 specific variables
│   └── web2.yml             # Web2 specific variables
├── inventory/               # Ansible inventory
│   └── hosts.yml            # Host definitions
├── index.php                # Main dashboard page
├── create.php               # Add new task page
├── edit.php                 # Edit existing task page
├── view.php                 # Task detail view page
├── delete.php               # Task deletion handler
├── health.php               # Health check endpoint
├── env_loader.php           # Environment variables loader
├── site.yml                 # Main Ansible playbook
├── lamp-deployment.yml      # LAMP deployment playbook
├── deploy.sh                # Deployment script
└── README.md                # Project documentation
```

## Database Schema

### Tasks Table

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT PRIMARY KEY AUTO_INCREMENT | Unique task identifier |
| `title` | VARCHAR(255) NOT NULL | Task title |
| `description` | TEXT | Detailed task description |
| `priority` | ENUM('Low','Medium','High') | Task priority level |
| `status` | ENUM('Pending','In Progress','Completed') | Task status |
| `due_date` | DATE | Task due date (nullable) |
| `created_at` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| `updated_at` | TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP | Last update timestamp |

## Local Development Setup

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache web server
- Git

### Installation Steps

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/task-manager.git
   cd task-manager
   ```

2. **Database Setup**
   - Create a new MySQL database
   - Import the database schema:
   ```bash
   mysql -u your_username -p your_database_name < sql/database_setup.sql
   ```

3. **Configuration**
   - Create a `.env` file in the project root:
   ```
   DB_HOST=localhost
   DB_NAME=task-manager
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   DB_PORT=3306
   APP_ENV=development
   APP_DEBUG=true
   ```

4. **Web Server Configuration**
   - Configure your web server to point to the project directory
   - Ensure the web server has read/write permissions for the project files

5. **Access the Application**
   - Open your browser and navigate to: `http://localhost/task-manager/`

## Usage Guide

### Managing Tasks

1. **View Tasks**: The main dashboard displays all tasks with filtering options
2. **Create Task**: Click "Add New Task" and fill in the required information
3. **Edit Task**: Click the edit icon next to any task to modify its details
4. **Delete Task**: Click the delete icon to remove a task
5. **View Details**: Click the view icon to see complete task information

### Filtering and Searching

1. Use the search box to find tasks by title or description
2. Filter tasks by status (Pending, In Progress, Completed)
3. Filter tasks by priority (Low, Medium, High)
4. Combine filters for more specific results

## Security Features

- **Input Sanitization**: All user inputs are sanitized to prevent XSS attacks
- **CSRF Protection**: Form submissions are protected against cross-site request forgery
- **Prepared Statements**: SQL injection prevention through parameterized queries
- **Environment Variables**: Sensitive configuration stored in environment variables
- **Error Handling**: Custom error handling to prevent information disclosure

## Performance Optimizations

- **Database Indexing**: Optimized database schema with proper indexes
- **Query Optimization**: Efficient SQL queries with proper filtering
- **Content Compression**: Apache configured with mod_deflate for compression
- **Browser Caching**: Static assets configured for optimal caching
- **Minimal Dependencies**: Lightweight implementation with few external dependencies

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature-name`
3. Commit your changes: `git commit -am 'Add new feature'`
4. Push to the branch: `git push origin feature-name`
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgments

- Bootstrap team for the excellent UI framework
- PHP and MySQL communities for documentation and support
- Ansible team for the deployment automation tools