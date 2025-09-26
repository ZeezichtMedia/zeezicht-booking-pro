/**
 * ZeeZicht Booking Pro - Admin JavaScript
 * Simple admin functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('ZZBP Admin JS loaded');
    
    // Initialize admin functionality
    initApiTesting();
    initFormValidation();
});

/**
 * Initialize API testing functionality
 */
function initApiTesting() {
    const testButton = document.querySelector('#zzbp-api-status button');
    if (testButton) {
        testButton.addEventListener('click', testApiConnection);
    }
}

/**
 * Test API connection
 */
function testApiConnection() {
    const button = document.querySelector('#zzbp-api-status button');
    const result = document.querySelector('#zzbp-api-result');
    
    if (!button || !result) return;
    
    button.disabled = true;
    button.textContent = 'Testing...';
    result.innerHTML = '';
    
    // Check if zzbp_admin_ajax is available
    if (typeof zzbp_admin_ajax === 'undefined') {
        result.innerHTML = '<span style="color: red;">✗ Admin AJAX not properly loaded</span>';
        button.disabled = false;
        button.textContent = 'Test Connection';
        return;
    }
    
    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'zzbp_test_api_connection',
            nonce: zzbp_admin_ajax.nonce
        })
    })
    .then(response => response.json())
    .then(data => {
        button.disabled = false;
        button.textContent = 'Test Connection';
        
        if (data.success) {
            result.innerHTML = '<span class="zzbp-status-success">✓ ' + data.data.message + '</span>';
        } else {
            result.innerHTML = '<span class="zzbp-status-error">✗ ' + data.data.message + '</span>';
        }
    })
    .catch(error => {
        button.disabled = false;
        button.textContent = 'Test Connection';
        result.innerHTML = '<span class="zzbp-status-error">✗ Connection failed: ' + error.message + '</span>';
        console.error('API test error:', error);
    });
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[action="options.php"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const apiUrl = form.querySelector('input[name="zzbp_settings[api_base_url]"]');
            
            if (apiUrl && apiUrl.value && !isValidUrl(apiUrl.value)) {
                e.preventDefault();
                alert('Please enter a valid API URL');
                apiUrl.focus();
                return false;
            }
        });
    });
}

/**
 * Validate URL format
 */
function isValidUrl(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}

/**
 * Show loading state
 */
function showLoading(element) {
    if (element) {
        element.classList.add('zzbp-loading');
    }
}

/**
 * Hide loading state
 */
function hideLoading(element) {
    if (element) {
        element.classList.remove('zzbp-loading');
    }
}
