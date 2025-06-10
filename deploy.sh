#!/usr/bin/env bash
# Deployment script

set -e

echo "Starting LAMP Stack Deployment..."

# Check if Ansible is installed
if ! command -v ansible &> /dev/null; then
    echo "Ansible is not installed. Please install Ansible first."
    exit 1
fi

# Install required collections
echo "Installing Ansible collections..."
ansible-galaxy install -r requirements.yml

# Encrypt sensitive variables (run this once)
if [ ! -f ".vault_pass" ]; then
    echo "Creating vault password file..."
    read -s -p "Enter vault password: " vault_password
    echo "$vault_password" > .vault_pass
    chmod 600 .vault_pass
fi

# Run syntax check
echo "Running syntax check..."
ansible-playbook site.yml --syntax-check

# Run the deployment
echo "Starting deployment..."
ansible-playbook site.yml -v

echo "Deployment completed!"
echo ""
echo "Your application should now be accessible at:"
echo "Web Server 1: http://$(ansible-inventory --host web1 | jq -r .ansible_host)"
echo "Web Server 2: http://$(ansible-inventory --host web2 | jq -r .ansible_host)"
