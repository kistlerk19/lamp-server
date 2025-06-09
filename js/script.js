/**
 * Task Manager Application JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Form validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Delete confirmation
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const taskTitle = this.getAttribute('data-task-title');
            const deleteUrl = this.getAttribute('href');
            
            if (confirm(`Are you sure you want to delete the task "${taskTitle}"?\n\nThis action cannot be undone.`)) {
                window.location.href = deleteUrl;
            }
        });
    });

    // Auto-hide alerts
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);

    // Date validation
    const dueDateInput = document.getElementById('due_date');
    if (dueDateInput) {
        dueDateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            if (selectedDate < today) {
                this.setCustomValidity('Due date cannot be in the past');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Character counter for textarea
    const descriptionTextarea = document.getElementById('description');
    if (descriptionTextarea) {
        const maxLength = 1000;
        const counter = document.createElement('small');
        counter.className = 'form-text text-muted float-end';
        counter.id = 'char-counter';
        descriptionTextarea.parentNode.appendChild(counter);
        
        function updateCounter() {
            const remaining = maxLength - descriptionTextarea.value.length;
            counter.textContent = `${remaining} characters remaining`;
            
            if (remaining < 100) {
                counter.className = 'form-text text-warning float-end';
            } else if (remaining < 50) {
                counter.className = 'form-text text-danger float-end';
            } else {
                counter.className = 'form-text text-muted float-end';
            }
        }
        
        descriptionTextarea.addEventListener('input', updateCounter);
        updateCounter(); // Initial call
    }

    // Loading states for buttons
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading"></span> Processing...';
            }
        });
    });

    // Table sorting (simple client-side sorting)
    const sortableHeaders = document.querySelectorAll('th[data-sort]');
    sortableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            const table = this.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const column = this.getAttribute('data-sort');
            const isAscending = this.getAttribute('data-order') === 'asc';
            
            rows.sort((a, b) => {
                const aVal = a.querySelector(`td[data-${column}]`).textContent.trim();
                const bVal = b.querySelector(`td[data-${column}]`).textContent.trim();
                
                if (column === 'due_date') {
                    const aDate = new Date(aVal === 'No due date' ? '9999-12-31' : aVal);
                    const bDate = new Date(bVal === 'No due date' ? '9999-12-31' : bVal);
                    return isAscending ? aDate - bDate : bDate - aDate;
                }
                
                if (column === 'priority') {
                    const priorities = { 'High': 3, 'Medium': 2, 'Low': 1 };
                    const aPriority = priorities[aVal] || 0;
                    const bPriority = priorities[bVal] || 0;
                    return isAscending ? aPriority - bPriority : bPriority - aPriority;
                }
                
                return isAscending ? 
                    aVal.localeCompare(bVal) : 
                    bVal.localeCompare(aVal);
            });
            
            // Update table
            rows.forEach(row => tbody.appendChild(row));
            
            // Update sort indicator
            this.setAttribute('data-order', isAscending ? 'desc' : 'asc');
            
            // Update all headers
            sortableHeaders.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));
            this.classList.add(isAscending ? 'sort-desc' : 'sort-asc');
        });
    });

    // Search functionality
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const title = row.querySelector('.task-title').textContent.toLowerCase();
                const description = row.querySelector('.task-description').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Add fade-in animation to new elements
    const newElements = document.querySelectorAll('.fade-in-up');
    newElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            element.style.transition = 'all 0.5s ease-out';
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }, 100);
    });
});

// Utility functions
function showLoading(button) {
    button.disabled = true;
    button.innerHTML = '<span class="loading"></span> Processing...';
}

function hideLoading(button, originalText) {
    button.disabled = false;
    button.innerHTML = originalText;
}

function showNotification(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        const bsAlert = new bootstrap.Alert(alertDiv);
        bsAlert.close();
    }, 5000);
}