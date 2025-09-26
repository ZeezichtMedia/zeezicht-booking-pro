<?php
/**
 * ZeeZicht Booking Pro - Admin Handler
 * 
 * Handles all admin functionality for the plugin.
 * Follows Single Responsibility Principle.
 * 
 * @package ZeeZichtBookingPro
 * @subpackage Admin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ZZBP_Admin {
    
    /**
     * Initialize admin functionality
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }
    
    /**
     * Add admin menu pages
     */
    public function add_admin_menu() {
        // Main menu page
        add_menu_page(
            'ZeeZicht Booking Pro',
            'Booking Pro',
            'manage_options',
            'zzbp-dashboard',
            array($this, 'dashboard_page'),
            'dashicons-calendar-alt',
            30
        );
        
        // Settings submenu
        add_submenu_page(
            'zzbp-dashboard',
            'Settings',
            'Settings',
            'manage_options',
            'zzbp-settings',
            array($this, 'settings_page')
        );
        
        // Accommodations submenu (read-only view)
        add_submenu_page(
            'zzbp-dashboard',
            'Accommodations',
            'Accommodations',
            'manage_options',
            'zzbp-accommodations',
            array($this, 'accommodations_page')
        );
        
        // Link to SaaS Dashboard submenu
        add_submenu_page(
            'zzbp-dashboard',
            'Manage Bookings',
            'Manage Bookings →',
            'manage_options',
            'zzbp-saas-link',
            array($this, 'saas_redirect_page')
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('zzbp_settings_group', 'zzbp_settings', array(
            'sanitize_callback' => array($this, 'sanitize_settings')
        ));
        
        // API Settings Section
        add_settings_section(
            'zzbp_api_section',
            'API Settings',
            array($this, 'api_section_callback'),
            'zzbp-settings'
        );
        
        add_settings_field(
            'api_base_url',
            'API Base URL',
            array($this, 'api_base_url_callback'),
            'zzbp-settings',
            'zzbp_api_section'
        );
        
        add_settings_field(
            'api_key',
            'API Key',
            array($this, 'api_key_callback'),
            'zzbp-settings',
            'zzbp_api_section'
        );
        
        // Property Information Section (Read-only)
        add_settings_section(
            'zzbp_property_section',
            'Property Information',
            array($this, 'property_section_callback'),
            'zzbp-settings'
        );
    }
    
    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on our admin pages
        if (strpos($hook, 'zzbp-') === false) {
            return;
        }
        
        wp_enqueue_style(
            'zzbp-admin-style',
            plugin_dir_url(ZZBP_PLUGIN_FILE) . 'assets/css/admin.css',
            array(),
            ZZBP_VERSION
        );
        
        wp_enqueue_script(
            'zzbp-admin-script',
            plugin_dir_url(ZZBP_PLUGIN_FILE) . 'assets/js/admin.js',
            array('jquery'),
            ZZBP_VERSION,
            true
        );
        
        wp_localize_script('zzbp-admin-script', 'zzbp_admin_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('zzbp_nonce')
        ));
    }
    
    /**
     * Dashboard page
     */
    public function dashboard_page() {
        ?>
        <div class="wrap">
            <h1>ZeeZicht Booking Pro Dashboard</h1>
            
            <div class="zzbp-dashboard-widgets">
                <div class="zzbp-widget">
                    <h3>Recent Bookings</h3>
                    <p>Overview of recent bookings will be displayed here.</p>
                </div>
                
                <div class="zzbp-widget">
                    <h3>API Status</h3>
                    <div id="zzbp-api-status">
                        <button type="button" class="button" onclick="testApiConnection()">Test Connection</button>
                        <div id="zzbp-api-result"></div>
                    </div>
                </div>
                
                <div class="zzbp-widget">
                    <h3>Quick Actions</h3>
                    <p>
                        <a href="<?php echo admin_url('admin.php?page=zzbp-settings'); ?>" class="button button-primary">Settings</a>
                        <a href="<?php echo admin_url('admin.php?page=zzbp-accommodations'); ?>" class="button">Accommodations</a>
                        <a href="<?php echo admin_url('admin.php?page=zzbp-bookings'); ?>" class="button">Bookings</a>
                    </p>
                </div>
            </div>
        </div>
        
        <script>
        function testApiConnection() {
            const button = document.querySelector('#zzbp-api-status button');
            const result = document.querySelector('#zzbp-api-result');
            
            if (!button || !result) return;
            
            // Check if required variables are available
            if (typeof ajaxurl === 'undefined') {
                result.innerHTML = '<span style="color: red;">✗ WordPress AJAX URL not available</span>';
                return;
            }
            
            if (typeof zzbp_admin_ajax === 'undefined') {
                result.innerHTML = '<span style="color: red;">✗ Admin AJAX variables not loaded</span>';
                return;
            }
            
            button.disabled = true;
            button.textContent = 'Testing...';
            result.innerHTML = '';
            
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
                console.error('API Test Error:', error);
            });
        }
        </script>
        <?php
    }
    
    /**
     * Settings page
     */
    public function settings_page() {
        // Get property info from API if connected
        $property_info = null;
        $api = new ZZBP_Api();
        $connection_test = $api->test_connection();
        
        if ($connection_test['success']) {
            $property_info = $connection_test['data'];
        }
        
        ?>
        <div class="wrap">
            <h1>ZeeZicht Booking Pro Settings</h1>
            
            <?php if ($connection_test['success']): ?>
                <div class="notice notice-success">
                    <p><strong>✅ Connected to SaaS Dashboard</strong></p>
                </div>
            <?php else: ?>
                <div class="notice notice-error">
                    <p><strong>❌ Not connected:</strong> <?php echo esc_html($connection_test['error']); ?></p>
                </div>
            <?php endif; ?>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('zzbp_settings_group');
                do_settings_sections('zzbp-settings');
                submit_button('Save API Settings');
                ?>
            </form>
            
            <?php if ($property_info): ?>
                <div class="zzbp-property-info" style="margin-top: 30px;">
                    <h2>Current Property Information</h2>
                    <div class="zzbp-widget">
                        <table class="form-table">
                            <tr>
                                <th scope="row">Property Name</th>
                                <td><strong><?php echo esc_html($property_info['settings']['business']['name'] ?? $property_info['name'] ?? 'Not set'); ?></strong></td>
                            </tr>
                            <tr>
                                <th scope="row">Contact Email</th>
                                <td><?php echo esc_html($property_info['settings']['business']['email'] ?? $property_info['settings']['contact_email'] ?? 'Not set'); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Website URL</th>
                                <td><?php echo esc_html($property_info['settings']['business']['website_url'] ?? $property_info['settings']['website_url'] ?? 'Not set'); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Business Type</th>
                                <td><?php echo esc_html(ucfirst($property_info['business_type'] ?? 'Not set')); ?></td>
                            </tr>
                            <?php if (isset($property_info['url_structure'])): ?>
                            <tr>
                                <th scope="row">Accommodations URL</th>
                                <td><code><?php echo home_url('/' . $property_info['url_structure']['base'] . '/'); ?></code></td>
                            </tr>
                            <tr>
                                <th scope="row">Booking URL</th>
                                <td><code><?php echo home_url('/' . $property_info['url_structure']['booking'] . '/'); ?></code></td>
                            </tr>
                            <?php endif; ?>
                        </table>
                        
                        <p><em>This information is managed in your <a href="http://admin.zee-zicht.nl" target="_blank">SaaS Dashboard</a>. Changes made there will automatically appear here.</em></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    /**
     * Accommodations page
     */
    public function accommodations_page() {
        ?>
        <div class="wrap">
            <h1>Accommodations</h1>
            <div id="zzbp-accommodations-list">
                <p>Loading accommodations...</p>
            </div>
        </div>
        
        <script>
        // Load accommodations via AJAX - with proper error handling
        document.addEventListener('DOMContentLoaded', function() {
            // Check if required variables are available
            if (typeof ajaxurl === 'undefined') {
                document.getElementById('zzbp-accommodations-list').innerHTML = '<p>Error: WordPress AJAX URL not available.</p>';
                return;
            }
            
            if (typeof zzbp_admin_ajax === 'undefined') {
                document.getElementById('zzbp-accommodations-list').innerHTML = '<p>Error: Admin AJAX variables not loaded properly.</p>';
                return;
            }
            
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'zzbp_get_accommodations',
                    nonce: zzbp_admin_ajax.nonce
                })
            })
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('zzbp-accommodations-list');
                
                if (data.success && data.data.length > 0) {
                    let html = '<table class="wp-list-table widefat fixed striped">';
                    html += '<thead><tr><th>Name</th><th>Type</th><th>Max Guests</th><th>Price</th></tr></thead><tbody>';
                    
                    data.data.forEach(acc => {
                        html += `<tr>
                            <td><strong>${acc.name}</strong></td>
                            <td>${acc.type || 'N/A'}</td>
                            <td>${acc.max_guests || 'N/A'}</td>
                            <td>€${acc.base_price || 'N/A'}</td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table>';
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<p>No accommodations found. Please check your API connection.</p>';
                }
            })
            .catch(error => {
                console.error('AJAX Error:', error);
                document.getElementById('zzbp-accommodations-list').innerHTML = '<p>Error loading accommodations: ' + error.message + '</p>';
            });
        });
        </script>
        <?php
    }
    
    /**
     * SaaS redirect page - Links to the real booking management
     */
    public function saas_redirect_page() {
        ?>
        <div class="wrap">
            <h1>Booking Management</h1>
            
            <div class="zzbp-widget" style="max-width: 600px;">
                <h3>🚀 Manage Your Bookings</h3>
                <p>Booking management happens in your dedicated SaaS dashboard, not in WordPress.</p>
                
                <p><strong>Your SaaS Dashboard:</strong></p>
                <p>
                    <a href="http://admin.zee-zicht.nl" target="_blank" class="button button-primary button-large">
                        Open Booking Dashboard →
                    </a>
                </p>
                
                <hr style="margin: 20px 0;">
                
                <h4>Why separate dashboards?</h4>
                <ul>
                    <li>✅ <strong>Better Performance</strong> - Dedicated app for booking management</li>
                    <li>✅ <strong>Real-time Updates</strong> - Live booking status and notifications</li>
                    <li>✅ <strong>Advanced Features</strong> - Calendar management, pricing rules, analytics</li>
                    <li>✅ <strong>Mobile Optimized</strong> - Manage bookings on any device</li>
                </ul>
                
                <p><em>This WordPress plugin handles the <strong>public booking forms</strong> on your website. 
                The <strong>management interface</strong> is in your SaaS dashboard.</em></p>
            </div>
        </div>
        <?php
    }
    
    // Settings callbacks
    public function api_section_callback() {
        echo '<p>Configure your API connection settings.</p>';
    }
    
    public function property_section_callback() {
        echo '<p>Property information is automatically synced from your SaaS dashboard.</p>';
    }
    
    public function api_base_url_callback() {
        $settings = get_option('zzbp_settings', array());
        $value = $settings['api_base_url'] ?? 'http://localhost:2121/api';
        echo '<input type="url" name="zzbp_settings[api_base_url]" value="' . esc_attr($value) . '" class="regular-text" />';
    }
    
    public function api_key_callback() {
        $settings = get_option('zzbp_settings', array());
        $value = $settings['api_key'] ?? '';
        echo '<input type="password" name="zzbp_settings[api_key]" value="' . esc_attr($value) . '" class="regular-text" />';
    }
    
    
    /**
     * Sanitize settings
     */
    public function sanitize_settings($input) {
        $sanitized = array();
        
        if (isset($input['api_base_url'])) {
            $sanitized['api_base_url'] = esc_url_raw($input['api_base_url']);
        }
        
        if (isset($input['api_key'])) {
            $sanitized['api_key'] = sanitize_text_field($input['api_key']);
        }
        
        return $sanitized;
    }
}
