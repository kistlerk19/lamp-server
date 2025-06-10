# Task Manager - Ansible Deployment Guide

This guide provides comprehensive instructions for deploying the Task Manager application on AWS infrastructure using Ansible automation.

## Architecture Overview

![Architecture Diagram](https://raw.githubusercontent.com/kistlerk19/lamp-server/main/screenshots/aws-architecture.png)

- **Web Tier**: 2 EC2 instances running Apache and PHP
- **Database Tier**: RDS MySQL instance in a private subnet
- **Load Balancing**: Optional Application Load Balancer for high availability
- **Security**: Properly configured security groups and firewall rules

## Prerequisites

### Local Requirements

- Ansible 2.9+
- Python 3.6+
- AWS CLI configured with appropriate permissions
- SSH key pair for EC2 access

### AWS Infrastructure

- 2 EC2 instances (Ubuntu 20.04 LTS recommended)
- RDS MySQL instance in a private subnet
- Security groups configured:
  - Web tier: Allow HTTP (80), HTTPS (443), SSH (22)
  - Database tier: Allow MySQL (3306) from web tier only
- VPC with public and private subnets

## Deployment Files Structure

```
task-manager/
├── ansible.cfg                 # Ansible configuration
├── site.yml                    # Main playbook
├── lamp-deployment.yml         # LAMP deployment playbook
├── deploy.sh                   # Deployment script
├── requirements.yml            # Ansible collections
├── inventory/
│   └── hosts.yml               # Inventory file
├── group_vars/
│   ├── all.yml                 # Global variables
│   └── webservers.yml          # Web server variables
├── host_vars/
│   ├── web1.yml                # Web1 specific variables
│   └── web2.yml                # Web2 specific variables
└── templates/
    ├── env.j2                  # Environment file template
    ├── database.php.j2         # Database config template
    ├── apache-vhost.j2         # Apache virtual host
    ├── php.ini.j2              # PHP configuration
    ├── logrotate.j2            # Log rotation config
    └── health-check.sh.j2      # Health check script
```

## Deployment Steps

### 1. Configure Inventory

Edit `inventory/hosts.yml` to specify your EC2 instances:

```yaml
all:
  children:
    webservers:
      hosts:
        web1:
          ansible_host: YOUR_EC2_IP_1
          ansible_user: ubuntu
          ansible_ssh_private_key_file: ~/path/to/your-key.pem
        web2:
          ansible_host: YOUR_EC2_IP_2
          ansible_user: ubuntu
          ansible_ssh_private_key_file: ~/path/to/your-key.pem
      vars:
        ansible_python_interpreter: /usr/bin/python3
```

### 2. Configure Variables

#### Global Variables (group_vars/all.yml)

```yaml
# Global variables
timezone: "UTC"
ntp_enabled: true

# SSL Configuration (optional)
ssl_enabled: false
ssl_cert_path: "/etc/ssl/certs/{{ app_name }}.crt"
ssl_key_path: "/etc/ssl/private/{{ app_name }}.key"

# Monitoring
enable_monitoring: true
log_retention_days: 30
```

#### Web Server Variables (group_vars/webservers.yml)

```yaml
# Database configuration for RDS
rds_endpoint: "your-rds-endpoint.region.rds.amazonaws.com"
rds_db_name: "task-manager"
rds_username: "admin"

# Application settings
github_repo: "https://github.com/yourusername/task-manager.git"
github_branch: "main"
```

### 3. Secure Sensitive Data

Create a vault password file and encrypt sensitive information:

```bash
# Create vault password file
echo "your-secure-vault-password" > .vault_pass
chmod 600 .vault_pass

# Encrypt RDS password
ansible-vault encrypt_string 'your-rds-password' --name 'rds_password'
```

Add the encrypted output to `group_vars/webservers.yml`.

### 4. Install Dependencies

```bash
# Install required Ansible collections
ansible-galaxy install -r requirements.yml
```

### 5. Run Deployment

```bash
# Make deploy script executable
chmod +x deploy.sh

# Run deployment
./deploy.sh
```

Alternatively, run the deployment manually:

```bash
# Syntax check
ansible-playbook site.yml --syntax-check

# Deploy with verbose output
ansible-playbook site.yml -v

# Deploy to specific host
ansible-playbook site.yml --limit web1
```

## Deployment Process Details

The deployment process performs the following steps:

1. **System Updates**: Updates all system packages
2. **PHP Detection**: Detects and installs the appropriate PHP version
3. **Package Installation**: Installs Apache, PHP, and required extensions
4. **Application Deployment**: Clones the application from GitHub
5. **Configuration**: Sets up environment variables and database connection
6. **Apache Configuration**: Creates and enables virtual host
7. **Database Initialization**: Sets up the database schema
8. **Monitoring Setup**: Configures health checks and log rotation
9. **Security Hardening**: Configures firewall rules and security settings
10. **Verification**: Tests the application health and connectivity

## Environment Variables

The following environment variables are configured during deployment:

| Variable | Description |
|----------|-------------|
| `DB_HOST` | RDS endpoint |
| `DB_NAME` | Database name |
| `DB_USERNAME` | Database username |
| `DB_PASSWORD` | Database password |
| `DB_PORT` | Database port (default: 3306) |
| `APP_ENV` | Application environment |
| `APP_DEBUG` | Debug mode (false for production) |
| `APP_NAME` | Application name |
| `APP_URL` | Application URL |
| `LOG_PATH` | Application log file path |

## Security Features

- **Firewall Configuration**: UFW configured with minimal open ports
- **Apache Security Headers**: XSS protection, content type sniffing protection
- **File Permissions**: Proper ownership and permissions for application files
- **Environment Variables**: Sensitive data stored in `.env` file with restricted access
- **Log Rotation**: Automated log rotation to prevent disk space issues

## Monitoring and Health Checks

- **Health Check Endpoint**: `/health.php` returns JSON status of application components
- **Automated Health Monitoring**: Cron job runs every 5 minutes
- **Log Aggregation**: Centralized logging in `/var/log/task-manager/`
- **Apache Monitoring**: Service monitoring and auto-restart

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   ```bash
   # Check RDS connectivity
   mysql -h your-rds-endpoint.region.rds.amazonaws.com -u admin -p
   
   # Check security groups
   # Ensure web servers can reach RDS on port 3306
   ```

2. **Apache Not Starting**
   ```bash
   # Check Apache error logs
   sudo tail -f /var/log/task-manager/apache_error.log
   
   # Check PHP configuration
   sudo php -m | grep mysql
   ```

3. **Application Not Loading**
   ```bash
   # Check application logs
   sudo tail -f /var/log/task-manager/app.log
   
   # Verify file permissions
   sudo ls -la /var/www/task-manager/
   ```

### Log Locations

- Apache Error Log: `/var/log/task-manager/apache_error.log`
- Apache Access Log: `/var/log/task-manager/apache_access.log`
- PHP Error Log: `/var/log/task-manager/php_errors.log`
- Application Log: `/var/log/task-manager/app.log`
- Health Check Log: `/var/log/task-manager/health-check.log`

## Performance Tuning

### PHP Configuration

Adjust PHP settings in `templates/php.ini.j2`:

```ini
memory_limit = 512M
max_execution_time = 60
opcache.enable = 1
opcache.memory_consumption = 128
```

### Apache Configuration

Enable compression and caching in `templates/apache-vhost.j2`:

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
</IfModule>

<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
</IfModule>
```

### Database Optimization

Add indexes for better performance:

```sql
-- Add indexes for better performance
ALTER TABLE tasks ADD INDEX idx_status (status);
ALTER TABLE tasks ADD INDEX idx_priority (priority);
ALTER TABLE tasks ADD INDEX idx_due_date (due_date);

-- Optimize table
OPTIMIZE TABLE tasks;
```

## Backup and Recovery

### Database Backup

Create a backup script:

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/mysql"
DB_HOST="your-rds-endpoint.region.rds.amazonaws.com"
DB_NAME="task-manager"
DB_USER="admin"
DB_PASS="your-password"

mkdir -p $BACKUP_DIR

mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/task-manager_$DATE.sql

# Keep only last 7 days of backups
find $BACKUP_DIR -name "task-manager_*.sql" -mtime +7 -delete

# Compress older backups
find $BACKUP_DIR -name "task-manager_*.sql" -mtime +1 -exec gzip {} \;
```

Schedule daily backups:

```bash
echo "0 2 * * * root /usr/local/bin/backup-database.sh" >> /etc/crontab
```

### Application Backup

```bash
# Backup application files and configuration
tar -czf /var/backups/task-manager-$(date +%Y%m%d).tar.gz \
  /var/www/task-manager \
  /etc/apache2/sites-available/task-manager.conf \
  /var/log/task-manager
```

## SSL/TLS Configuration (Recommended)

1. **Obtain SSL Certificate** (Let's Encrypt example):
   ```bash
   # Install certbot
   sudo apt install certbot python3-certbot-apache
   
   # Obtain certificate
   sudo certbot --apache -d yourdomain.com
   ```

2. **Update Apache Configuration**:
   ```apache
   <VirtualHost *:443>
       ServerName yourdomain.com
       DocumentRoot /var/www/task-manager
       
       SSLEngine on
       SSLCertificateFile /etc/letsencrypt/live/yourdomain.com/cert.pem
       SSLCertificateKeyFile /etc/letsencrypt/live/yourdomain.com/privkey.pem
       SSLCertificateChainFile /etc/letsencrypt/live/yourdomain.com/chain.pem
       
       # Include all other configurations from HTTP vhost
   </VirtualHost>
   ```

## Maintenance Procedures

### Regular Maintenance Tasks

1. **Weekly Tasks**:
   - Review application logs
   - Check disk space usage
   - Verify backup integrity
   - Update security patches

2. **Monthly Tasks**:
   - Review performance metrics
   - Optimize database queries
   - Update dependencies
   - Security audit

3. **Quarterly Tasks**:
   - Full disaster recovery testing
   - Capacity planning review
   - Security assessment
   - Documentation updates

### Upgrade Procedures

1. **Application Updates**:
   ```bash
   cd ansible-deployment
   ansible-playbook site.yml --tags deployment --limit web1
   # Test web1, then deploy to web2
   ansible-playbook site.yml --tags deployment --limit web2
   ```

2. **System Updates**:
   ```bash
   ansible-playbook -i inventory/hosts.yml -m apt -a "update_cache=yes upgrade=dist" webservers
   ```

## Conclusion

This deployment guide provides a comprehensive approach to deploying and maintaining the Task Manager application on AWS infrastructure using Ansible automation. By following these instructions, you can ensure a secure, reliable, and scalable deployment of your application.

For additional support or questions, please contact the development team or refer to the project documentation.

## Author

Ishmael Gyamfi (June 2025)