# LAMP Stack Task Manager Deployment with Ansible

This repository contains Ansible playbooks and configurations to deploy a LAMP stack Task Manager application on AWS EC2 instances with RDS MySQL database.

## Architecture Overview

- **2 EC2 Instances**: Running Apache web servers with PHP
- **RDS MySQL**: Private database in separate subnet
- **Load Balancer**: (Optional) Application Load Balancer for high availability
- **Security Groups**: Properly configured for web tier and database tier

## Prerequisites

### Local Requirements
- Ansible 2.9+
- Python 3.6+
- AWS CLI configured
- SSH key pair for EC2 access

### AWS Infrastructure
- 2 EC2 instances (Ubuntu 20.04 LTS recommended)
- RDS MySQL instance in private subnet
- Security groups configured:
  - Web tier: Allow HTTP (80), HTTPS (443), SSH (22)
  - Database tier: Allow MySQL (3306) from web tier only

## Directory Structure

```
lamp-deployment/
├── ansible.cfg                 # Ansible configuration
├── site.yml                   # Main playbook
├── lamp-deployment.yml         # LAMP deployment playbook
├── deploy.sh                   # Deployment script
├── requirements.yml            # Ansible collections
├── inventory/
│   └── hosts.yml              # Inventory file
├── group_vars/
│   ├── all.yml                # Global variables
│   └── webservers.yml         # Web server variables
├── host_vars/
│   ├── web1.yml               # Web1 specific variables
│   └── web2.yml               # Web2 specific variables
└── templates/
    ├── env.j2                 # Environment file template
    ├── database.php.j2        # Database config template
    ├── apache-vhost.j2        # Apache virtual host
    ├── php.ini.j2             # PHP configuration
    ├── logrotate.j2           # Log rotation config
    └── health-check.sh.j2     # Health check script
```

## Setup Instructions

### 1. Clone and Prepare Repository

```bash
# Clone your application repository
git clone https://github.com/yourusername/task-manager.git
cd task-manager

# Create Ansible deployment directory
mkdir ansible-deployment
cd ansible-deployment

# Copy all the Ansible files provided above
```

### 2. Configure Inventory

Edit `inventory/hosts.yml`:

```yaml
all:
  children:
    webservers:
      hosts:
        web1:
          ansible_host: YOUR_EC2_IP_1
          ansible_user: ubuntu
          ansible_ssh_private_key_file: ~/.ssh/your-key.pem
        web2:
          ansible_host: YOUR_EC2_IP_2
          ansible_user: ubuntu
          ansible_ssh_private_key_file: ~/.ssh/your-key.pem
```

### 3. Configure Variables

Edit `group_vars/webservers.yml`:

```yaml
# Replace with your actual values
rds_endpoint: "your-rds-endpoint.region.rds.amazonaws.com"
rds_db_name: "task-manager"
rds_username: "admin"
github_repo: "https://github.com/yourusername/task-manager.git"
```

### 4. Encrypt Sensitive Data

```bash
# Create vault password file
echo "your-secure-vault-password" > .vault_pass
chmod 600 .vault_pass

# Encrypt RDS password
ansible-vault encrypt_string 'your-rds-password' --name 'rds_password'
```

Copy the encrypted output to `group_vars/webservers.yml`.

### 5. Install Dependencies

```bash
# Install required Ansible collections
ansible-galaxy install -r requirements.yml
```

### 6. Update Application Code

Modify your application's `config/database.php` to use environment variables (template provided above), or let Ansible replace it during deployment.

Add the `health.php` file to your application root for health checks.

### 7. Deploy Application

```bash
# Make deploy script executable
chmod +x deploy.sh

# Run deployment
./deploy.sh
```

Or run manually:

```bash
# Syntax check
ansible-playbook site.yml --syntax-check

# Deploy with verbose output
ansible-playbook site.yml -v

# Deploy to specific host
ansible-playbook site.yml --limit web1
```

## Environment Variables

The following environment variables are automatically configured:

| Variable | Description |
|----------|-------------|
| `DB_HOST` | RDS endpoint |
| `DB_NAME` | Database name |
| `DB_USERNAME` | Database username |
| `DB_PASSWORD` | Database password |
| `DB_PORT` | Database port (default: 3306) |
| `APP_ENV` | Application environment |
| `APP_DEBUG` | Debug mode (false for production) |
| `LOG_PATH` | Application log file path |

## Security Features

- **Firewall Configuration**: UFW configured with minimal open ports
- **Apache Security Headers**: XSS protection, content type sniffing protection
- **File Permissions**: Proper ownership and permissions for application files
- **Environment Variables**: Sensitive data stored in `.env` file with restricted access
- **Log Rotation**: Automated log rotation to prevent disk space issues

## Monitoring and Health Checks

- **Health Check Endpoint**: `/health.php` returns JSON status
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

### Manual Commands

```bash
# Restart services
sudo systemctl restart apache2
sudo systemctl restart mysql  # if running locally

# Check service status
sudo systemctl status apache2
sudo systemctl status mysql

# Test database connection
mysql -h your-rds-endpoint.region.rds.amazonaws.com -u admin -p task-manager

# Check disk space
df -h

# Monitor real-time logs
sudo tail -f /var/log/task-manager/*.log
```

## Load Balancer Configuration (Optional)

If using an Application Load Balancer, configure health checks:

- **Health Check Path**: `/health.php`
- **Health Check Port**: `80`
- **Health Check Protocol**: `HTTP`
- **Healthy Threshold**: `2`
- **Unhealthy Threshold**: `3`
- **Timeout**: `5 seconds`
- **Interval**: `30 seconds`

## Scaling and Performance

### Performance Tuning

1. **PHP Configuration**:
   ```ini
   # Adjust in templates/php.ini.j2
   memory_limit = 512M
   max_execution_time = 60
   opcache.enable = 1
   opcache.memory_consumption = 128
   ```

2. **Apache Configuration**:
   ```apache
   # Add to templates/apache-vhost.j2
   <IfModule mod_deflate.c>
       AddOutputFilterByType DEFLATE text/plain
       AddOutputFilterByType DEFLATE text/html
       AddOutputFilterByType DEFLATE text/xml
       AddOutputFilterByType DEFLATE text/css
       AddOutputFilterByType DEFLATE application/xml
       AddOutputFilterByType DEFLATE application/xhtml+xml
       AddOutputFilterByType DEFLATE application/rss+xml
       AddOutputFilterByType DEFLATE application/javascript
       AddOutputFilterByType DEFLATE application/x-javascript
   </IfModule>
   
   <IfModule mod_expires.c>
       ExpiresActive on
       ExpiresByType text/css "access plus 1 year"
       ExpiresByType application/javascript "access plus 1 year"
       ExpiresByType image/png "access plus 1 year"
       ExpiresByType image/jpg "access plus 1 year"
       ExpiresByType image/jpeg "access plus 1 year"
   </IfModule>
   ```

3. **Database Optimization**:
   ```sql
   -- Add indexes for better performance
   ALTER TABLE tasks ADD INDEX idx_user_status (user_id, status);
   ALTER TABLE tasks ADD INDEX idx_due_date_status (due_date, status);
   
   -- Optimize table
   OPTIMIZE TABLE tasks;
   ```

### Auto Scaling Setup

For auto-scaling with EC2, create a launch template and auto-scaling group:

```bash
# Create launch template
aws ec2 create-launch-template \
  --launch-template-name task-manager-template \
  --launch-template-data '{
    "ImageId": "ami-0c55b159cbfafe1d0",
    "InstanceType": "t3.micro",
    "KeyName": "your-key-pair",
    "SecurityGroupIds": ["sg-xxxxxxxxx"],
    "UserData": "'$(base64 -w 0 user-data.sh)'"
  }'

# Create auto-scaling group
aws autoscaling create-auto-scaling-group \
  --auto-scaling-group-name task-manager-asg \
  --launch-template LaunchTemplateName=task-manager-template \
  --min-size 2 \
  --max-size 4 \
  --desired-capacity 2 \
  --vpc-zone-identifier "subnet-xxxxxxxxx,subnet-yyyyyyyyy"
```

## Backup and Recovery

### Database Backup

```bash
# Create backup script
cat > /usr/local/bin/backup-database.sh << 'EOF'
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
EOF

chmod +x /usr/local/bin/backup-database.sh

# Schedule daily backups
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

## CI/CD Integration

### GitHub Actions Example

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v2
    
    - name: Setup Python
      uses: actions/setup-python@v2
      with:
        python-version: '3.8'
    
    - name: Install Ansible
      run: |
        pip install ansible
        ansible-galaxy install -r ansible-deployment/requirements.yml
    
    - name: Deploy with Ansible
      run: |
        cd ansible-deployment
        echo "${{ secrets.VAULT_PASSWORD }}" > .vault_pass
        echo "${{ secrets.SSH_PRIVATE_KEY }}" > ~/.ssh/deploy_key
        chmod 600 ~/.ssh/deploy_key
        ansible-playbook site.yml --private-key ~/.ssh/deploy_key
      env:
        ANSIBLE_HOST_KEY_CHECKING: False
```

## Security Considerations

### SSL/TLS Configuration (Recommended)

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

### Additional Security Measures

1. **Fail2Ban Installation**:
   ```bash
   sudo apt install fail2ban
   sudo systemctl enable fail2ban
   ```

2. **Regular Security Updates**:
   ```bash
   # Add to crontab
   0 3 * * 0 apt update && apt upgrade -y && systemctl reboot
   ```

3. **Database Security**:
   - Use RDS encryption at rest
   - Enable RDS backup encryption
   - Implement database parameter groups with security settings
   - Regular security patches via RDS maintenance windows

## Monitoring and Alerting

### CloudWatch Integration

```bash
# Install CloudWatch agent
wget https://s3.amazonaws.com/amazoncloudwatch-agent/amazon_linux/amd64/latest/amazon-cloudwatch-agent.rpm
sudo rpm -U ./amazon-cloudwatch-agent.rpm

# Configure CloudWatch agent
sudo /opt/aws/amazon-cloudwatch-agent/bin/amazon-cloudwatch-agent-config-wizard
```

### Custom Metrics

Add custom metrics to your application:

```php
<?php
// In your application code
function sendCloudWatchMetric($metricName, $value, $unit = 'Count') {
    $client = new Aws\CloudWatch\CloudWatchClient([
        'region' => 'us-west-2',
        'version' => 'latest'
    ]);
    
    $client->putMetricData([
        'Namespace' => 'TaskManager/Application',
        'MetricData' => [
            [
                'MetricName' => $metricName,
                'Value' => $value,
                'Unit' => $unit,
                'Timestamp' => time()
            ]
        ]
    ]);
}

// Usage examples
sendCloudWatchMetric('TasksCreated', 1);
sendCloudWatchMetric('DatabaseConnections', $connection_count);
sendCloudWatchMetric('ResponseTime', $response_time, 'Milliseconds');
?>
```

## Maintenance

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

This comprehensive deployment guide provides everything needed to deploy, monitor, and maintain your LAMP stack Task Manager application on AWS infrastructure using Ansible automation.