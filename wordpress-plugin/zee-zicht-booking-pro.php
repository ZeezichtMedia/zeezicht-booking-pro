<?php
/**
 * Plugin Name: Zee-zicht Booking Pro
 * Plugin URI: https://zee-zicht.nl
 * Description: Professional booking system for Zee-zicht B&B with flexible pricing, accommodation management, and guest registration.
 * Version: 1.0.0
 * Author: Zee-zicht B&B
 * License: GPL v2 or later
 * Text Domain: zee-zicht-booking-pro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ZZBP_VERSION', '1.0.0');
define('ZZBP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ZZBP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ZZBP_API_BASE', 'http://localhost:2121/api');

/**
 * Main Zee-zicht Booking Pro Class
 */
class ZeeZichtBookingPro {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Register shortcodes
        add_shortcode('zzbp_booking_form', array($this, 'booking_form_shortcode'));
        add_shortcode('zzbp_accommodation_list', array($this, 'accommodation_list_shortcode'));
        add_shortcode('zzbp_pricing_calculator', array($this, 'pricing_calculator_shortcode'));
        
        // AJAX handlers
        add_action('wp_ajax_zzbp_get_accommodations', array($this, 'ajax_get_accommodations'));
        add_action('wp_ajax_nopriv_zzbp_get_accommodations', array($this, 'ajax_get_accommodations'));
        add_action('wp_ajax_zzbp_calculate_pricing', array($this, 'ajax_calculate_pricing'));
        add_action('wp_ajax_nopriv_zzbp_calculate_pricing', array($this, 'ajax_calculate_pricing'));
        add_action('wp_ajax_zzbp_create_booking', array($this, 'ajax_create_booking'));
        add_action('wp_ajax_nopriv_zzbp_create_booking', array($this, 'ajax_create_booking'));
        
        // URL routing for single accommodations
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_single_accommodation'));
        
        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain for translations
        load_plugin_textdomain('zee-zicht-booking-pro', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        // Enqueue CSS
        wp_enqueue_style(
            'zzbp-styles',
            ZZBP_PLUGIN_URL . 'assets/css/booking-styles.css',
            array(),
            ZZBP_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'zzbp-scripts',
            ZZBP_PLUGIN_URL . 'assets/js/booking-scripts.js',
            array('jquery'),
            ZZBP_VERSION,
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('zzbp-scripts', 'zzbp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('zzbp_nonce'),
            'api_base' => ZZBP_API_BASE
        ));
    }
    
    /**
     * Booking form shortcode
     */
    public function booking_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'accommodation_id' => '',
            'show_accommodation_selector' => 'true'
        ), $atts);
        
        ob_start();
        include ZZBP_PLUGIN_PATH . 'templates/booking-form.php';
        return ob_get_clean();
    }
    
    /**
     * Accommodation list shortcode
     */
    public function accommodation_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'layout' => 'grid', // grid, list, cards
            'show_pricing' => 'true',
            'show_amenities' => 'true'
        ), $atts);
        
        ob_start();
        include ZZBP_PLUGIN_PATH . 'templates/accommodation-list.php';
        return ob_get_clean();
    }
    
    /**
     * Pricing calculator shortcode
     */
    public function pricing_calculator_shortcode($atts) {
        $atts = shortcode_atts(array(
            'accommodation_id' => ''
        ), $atts);
        
        ob_start();
        include ZZBP_PLUGIN_PATH . 'templates/pricing-calculator.php';
        return ob_get_clean();
    }
    
    /**
     * AJAX: Get accommodations
     */
    public function ajax_get_accommodations() {
        check_ajax_referer('zzbp_nonce', 'nonce');
        
        $response = wp_remote_get(ZZBP_API_BASE . '/accommodaties');
        
        if (is_wp_error($response)) {
            wp_send_json_error('Failed to fetch accommodations');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        wp_send_json_success($data);
    }
    
    /**
     * AJAX: Calculate pricing
     */
    public function ajax_calculate_pricing() {
        check_ajax_referer('zzbp_nonce', 'nonce');
        
        $request_data = array(
            'accommodation_id' => sanitize_text_field($_POST['accommodation_id']),
            'check_in' => sanitize_text_field($_POST['check_in']),
            'check_out' => sanitize_text_field($_POST['check_out']),
            'adults' => intval($_POST['adults']),
            'children_12_plus' => intval($_POST['children_12_plus']),
            'children_under_12' => intval($_POST['children_under_12']),
            'children_0_3' => intval($_POST['children_0_3']),
            'camping_vehicle_type' => sanitize_text_field($_POST['camping_vehicle_type']),
            'selected_options' => json_decode(stripslashes($_POST['selected_options']), true)
        );
        
        $response = wp_remote_post(ZZBP_API_BASE . '/calculate-pricing', array(
            'headers' => array('Content-Type' => 'application/json'),
            'body' => json_encode($request_data)
        ));
        
        if (is_wp_error($response)) {
            wp_send_json_error('Failed to calculate pricing');
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        wp_send_json_success($data);
    }
    
    /**
     * AJAX: Create booking
     */
    public function ajax_create_booking() {
        check_ajax_referer('zzbp_nonce', 'nonce');
        
        // TODO: Implement booking creation
        wp_send_json_success(array('message' => 'Booking functionality coming soon'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Zee-zicht Booking Pro',
            'Booking Pro',
            'manage_options',
            'zee-zicht-booking-pro',
            array($this, 'admin_page'),
            'dashicons-calendar-alt',
            30
        );
        
        // Add submenu pages
        add_submenu_page(
            'zee-zicht-booking-pro',
            'Settings',
            'Settings',
            'manage_options',
            'zee-zicht-booking-pro',
            array($this, 'admin_page')
        );
        
        add_submenu_page(
            'zee-zicht-booking-pro',
            'Accommodations',
            'Accommodations',
            'manage_options',
            'zzbp-accommodations',
            array($this, 'accommodations_page')
        );
    }
    
    /**
     * Admin settings page
     */
    public function admin_page() {
        // Handle form submission
        if (isset($_POST['zzbp_save_settings'])) {
            $this->save_settings();
        }
        
        // Handle refresh configuration
        if (isset($_POST['zzbp_refresh_config'])) {
            $this->refresh_configuration();
        }
        
        // Handle force clean pages
        if (isset($_POST['zzbp_force_clean_pages'])) {
            $this->force_clean_pages();
        }
        
        // Test connection if API key is set
        $connection_status = null;
        $property_info = null;
        $api_key = get_option('zzbp_api_key');
        
        if ($api_key) {
            $test_result = $this->test_connection();
            $connection_status = $test_result['success'];
            $property_info = $test_result['data'] ?? null;
        }
        
        include ZZBP_PLUGIN_PATH . 'templates/admin-settings.php';
    }
    
    /**
     * Accommodations page
     */
    public function accommodations_page() {
        $accommodations = $this->get_accommodations();
        include ZZBP_PLUGIN_PATH . 'templates/admin-accommodations.php';
    }
    
    /**
     * Save plugin settings
     */
    private function save_settings() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        check_admin_referer('zzbp_settings_nonce');
        
        $api_key = sanitize_text_field($_POST['zzbp_api_key']);
        $dashboard_url = esc_url_raw($_POST['zzbp_dashboard_url']);
        
        update_option('zzbp_api_key', $api_key);
        update_option('zzbp_dashboard_url', $dashboard_url);
        
        // Test connection with new settings
        $test_result = $this->test_connection();
        
        if ($test_result['success']) {
            // Save property info
            update_option('zzbp_property_info', $test_result['data']);
            
            // Auto-create pages if they don't exist
            $this->auto_create_pages($test_result['data']);
            
            // Flush rewrite rules to activate single accommodation URLs
            flush_rewrite_rules();
            
            add_action('admin_notices', function() {
                echo '<div class="notice notice-success"><p>Settings saved and connection successful! Single accommodation URLs are now active.</p></div>';
            });
        } else {
            add_action('admin_notices', function() use ($test_result) {
                echo '<div class="notice notice-error"><p>Settings saved but connection failed: ' . esc_html($test_result['error']) . '</p></div>';
            });
        }
    }
    
    /**
     * Test API connection
     */
    private function test_connection() {
        $api_key = get_option('zzbp_api_key');
        $dashboard_url = get_option('zzbp_dashboard_url', ZZBP_API_BASE);
        
        if (!$api_key) {
            return array('success' => false, 'error' => 'No API key configured');
        }
        
        $response = wp_remote_post($dashboard_url . '/api/plugin/authenticate', array(
            'body' => json_encode(array(
                'api_key' => $api_key,
                'domain' => parse_url(home_url(), PHP_URL_HOST),
                'plugin_version' => ZZBP_VERSION
            )),
            'headers' => array('Content-Type' => 'application/json'),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            return array('success' => false, 'error' => $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!$data['success']) {
            return array('success' => false, 'error' => $data['error'] ?? 'Unknown error');
        }
        
        return array('success' => true, 'data' => $data['property']);
    }
    
    /**
     * Refresh configuration from dashboard
     */
    private function refresh_configuration() {
        check_admin_referer('zzbp_settings_nonce');
        
        // Test connection to get latest property info
        $test_result = $this->test_connection();
        
        if ($test_result['success']) {
            // Update property info with latest data
            update_option('zzbp_property_info', $test_result['data']);
            
            // Recreate pages with new URL structure
            $this->auto_create_pages($test_result['data']);
            
            // Flush rewrite rules to activate new URL structure
            flush_rewrite_rules();
            
            add_action('admin_notices', function() use ($test_result) {
                $url_base = $test_result['data']['url_structure']['base'];
                
                // Debug: Show current pages
                $debug_pages = get_posts(array(
                    'post_type' => 'page',
                    'post_status' => 'publish',
                    's' => '[zzbp_accommodation_list]',
                    'numberposts' => -1
                ));
                
                $debug_info = '';
                if (!empty($debug_pages)) {
                    $debug_info = ' Found ' . count($debug_pages) . ' page(s) with shortcode: ';
                    foreach ($debug_pages as $page) {
                        $debug_info .= $page->post_name . ' (' . $page->ID . '), ';
                    }
                    $debug_info = rtrim($debug_info, ', ');
                }
                
                echo '<div class="notice notice-success"><p>✅ Configuration refreshed successfully! URL structure updated to <strong>/' . esc_html($url_base) . '/</strong>. Old pages cleanup attempted.' . $debug_info . '</p></div>';
            });
        } else {
            add_action('admin_notices', function() use ($test_result) {
                echo '<div class="notice notice-error"><p>❌ Failed to refresh configuration: ' . esc_html($test_result['error']) . '</p></div>';
            });
        }
    }
    
    /**
     * Force clean duplicate pages
     */
    private function force_clean_pages() {
        check_admin_referer('zzbp_settings_nonce');
        
        $property_info = get_option('zzbp_property_info');
        if (!$property_info) {
            add_action('admin_notices', function() {
                echo '<div class="notice notice-error"><p>❌ No property info found. Please save settings first.</p></div>';
            });
            return;
        }
        
        $current_slug = $property_info['url_structure']['base'];
        $deleted_count = 0;
        
        // Find all pages with accommodation shortcode
        $pages_with_shortcode = get_posts(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            's' => '[zzbp_accommodation_list]',
            'numberposts' => -1
        ));
        
        foreach ($pages_with_shortcode as $page) {
            // Delete pages that don't match current slug
            if ($page->post_name !== $current_slug) {
                wp_delete_post($page->ID, true);
                $deleted_count++;
            }
        }
        
        // Also clean booking pages
        $booking_slug = $property_info['url_structure']['booking'];
        $booking_pages = get_posts(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            's' => '[zzbp_booking_form]',
            'numberposts' => -1
        ));
        
        foreach ($booking_pages as $page) {
            if ($page->post_name !== $booking_slug) {
                wp_delete_post($page->ID, true);
                $deleted_count++;
            }
        }
        
        add_action('admin_notices', function() use ($deleted_count, $current_slug) {
            echo '<div class="notice notice-success"><p>✅ Force cleanup completed! Deleted ' . $deleted_count . ' duplicate page(s). Only /' . esc_html($current_slug) . '/ should remain active.</p></div>';
        });
    }
    
    /**
     * Get accommodations from API
     */
    public function get_accommodations() {
        $api_key = get_option('zzbp_api_key');
        $dashboard_url = get_option('zzbp_dashboard_url', ZZBP_API_BASE);
        
        if (!$api_key) {
            return array();
        }
        
        $response = wp_remote_get($dashboard_url . '/api/plugin/accommodations?' . http_build_query(array(
            'api_key' => $api_key,
            'domain' => parse_url(home_url(), PHP_URL_HOST)
        )));
        
        if (is_wp_error($response)) {
            return array();
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return $data['success'] ? $data['data'] : array();
    }
    
    /**
     * Auto-create WordPress pages
     */
    private function auto_create_pages($property_info) {
        $url_structure = $property_info['url_structure'];
        $settings = $property_info['settings'];
        
        // Get current stored slugs to detect changes
        $current_accommodations_slug = get_option('zzbp_accommodations_page_slug');
        $current_booking_slug = get_option('zzbp_booking_page_slug');
        
        // Handle accommodations page
        $this->handle_accommodations_page($url_structure, $settings, $current_accommodations_slug);
        
        // Handle booking page  
        $this->handle_booking_page($url_structure, $settings, $current_booking_slug);
    }
    
    /**
     * Handle accommodations page creation/update
     */
    private function handle_accommodations_page($url_structure, $settings, $current_slug) {
        $new_slug = $url_structure['base'];
        
        // If slug changed, remove old page(s)
        if ($current_slug && $current_slug !== $new_slug) {
            // Method 1: Remove by stored page ID
            $old_page_id = get_option('zzbp_accommodations_page_id');
            if ($old_page_id) {
                $old_page = get_post($old_page_id);
                if ($old_page && strpos($old_page->post_content, '[zzbp_accommodation_list]') !== false) {
                    wp_delete_post($old_page_id, true);
                }
            }
            
            // Method 2: Find and remove by old slug
            $old_page_by_path = get_page_by_path($current_slug);
            if ($old_page_by_path && strpos($old_page_by_path->post_content, '[zzbp_accommodation_list]') !== false) {
                wp_delete_post($old_page_by_path->ID, true);
            }
            
            // Method 3: Find all pages with our shortcode and wrong slug
            $pages_with_shortcode = get_posts(array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'meta_query' => array(),
                's' => '[zzbp_accommodation_list]',
                'numberposts' => -1
            ));
            
            foreach ($pages_with_shortcode as $page) {
                if ($page->post_name === $current_slug && $page->post_name !== $new_slug) {
                    wp_delete_post($page->ID, true);
                }
            }
        }
        
        // Create or update accommodations page
        $accommodations_page = get_page_by_path($new_slug);
        if (!$accommodations_page) {
            $page_id = wp_insert_post(array(
                'post_title' => $settings['booking']['accommodations_title'],
                'post_name' => $new_slug,
                'post_content' => '[zzbp_accommodation_list]',
                'post_status' => 'publish',
                'post_type' => 'page'
            ));
            
            if ($page_id && !is_wp_error($page_id)) {
                update_option('zzbp_accommodations_page_id', $page_id);
                update_option('zzbp_accommodations_page_slug', $new_slug);
            }
        } else {
            // Update existing page with shortcode if it's empty
            if (empty($accommodations_page->post_content) || strpos($accommodations_page->post_content, '[zzbp_accommodation_list]') === false) {
                wp_update_post(array(
                    'ID' => $accommodations_page->ID,
                    'post_content' => '[zzbp_accommodation_list]'
                ));
            }
            update_option('zzbp_accommodations_page_id', $accommodations_page->ID);
            update_option('zzbp_accommodations_page_slug', $new_slug);
        }
    }
    
    /**
     * Handle booking page creation/update
     */
    private function handle_booking_page($url_structure, $settings, $current_slug) {
        $new_slug = $url_structure['booking'];
        
        // If slug changed, remove old page
        if ($current_slug && $current_slug !== $new_slug) {
            $old_page_id = get_option('zzbp_booking_page_id');
            if ($old_page_id) {
                $old_page = get_post($old_page_id);
                if ($old_page && $old_page->post_name === $current_slug) {
                    // Only delete if it contains our shortcode (to avoid deleting user content)
                    if (strpos($old_page->post_content, '[zzbp_booking_form]') !== false) {
                        wp_delete_post($old_page_id, true);
                    }
                }
            }
        }
        
        // Create or update booking page
        $booking_page = get_page_by_path($new_slug);
        if (!$booking_page) {
            $page_id = wp_insert_post(array(
                'post_title' => $settings['booking']['page_title'],
                'post_name' => $new_slug,
                'post_content' => '[zzbp_booking_form]',
                'post_status' => 'publish',
                'post_type' => 'page'
            ));
            
            if ($page_id && !is_wp_error($page_id)) {
                update_option('zzbp_booking_page_id', $page_id);
                update_option('zzbp_booking_page_slug', $new_slug);
            }
        } else {
            // Update existing page with shortcode if it's empty
            if (empty($booking_page->post_content) || strpos($booking_page->post_content, '[zzbp_booking_form]') === false) {
                wp_update_post(array(
                    'ID' => $booking_page->ID,
                    'post_content' => '[zzbp_booking_form]'
                ));
            }
            update_option('zzbp_booking_page_id', $booking_page->ID);
            update_option('zzbp_booking_page_slug', $new_slug);
        }
    }
    
    /**
     * Force create/update pages (for debugging)
     */
    public function force_create_pages() {
        $property_info = get_option('zzbp_property_info');
        if ($property_info) {
            $this->auto_create_pages($property_info);
            return true;
        }
        return false;
    }
    
    /**
     * Add rewrite rules for single accommodations
     */
    public function add_rewrite_rules() {
        $property_info = get_option('zzbp_property_info');
        if ($property_info && isset($property_info['url_structure']['base'])) {
            $base = $property_info['url_structure']['base'];
            add_rewrite_rule(
                '^' . $base . '/([^/]+)/?$',
                'index.php?zzbp_accommodation_slug=$matches[1]',
                'top'
            );
        }
    }
    
    /**
     * Add query vars
     */
    public function add_query_vars($vars) {
        $vars[] = 'zzbp_accommodation_slug';
        return $vars;
    }
    
    /**
     * Handle single accommodation page
     */
    public function handle_single_accommodation() {
        $accommodation_slug = get_query_var('zzbp_accommodation_slug');
        
        if ($accommodation_slug) {
            // Get accommodation data
            $accommodations = $this->get_accommodations();
            $current_accommodation = null;
            
            foreach ($accommodations as $acc) {
                if ($acc['slug'] === $accommodation_slug) {
                    $current_accommodation = $acc;
                    break;
                }
            }
            
            if ($current_accommodation) {
                // Load single accommodation template
                $this->load_single_accommodation_template($current_accommodation);
                exit;
            } else {
                // 404 if accommodation not found
                global $wp_query;
                $wp_query->set_404();
                status_header(404);
                get_template_part(404);
                exit;
            }
        }
    }
    
    /**
     * Load single accommodation template
     */
    private function load_single_accommodation_template($accommodation) {
        // Set global accommodation data
        global $zzbp_current_accommodation;
        $zzbp_current_accommodation = $accommodation;
        
        // Load template
        include ZZBP_PLUGIN_PATH . 'templates/single-accommodation.php';
    }
    
    /**
     * API Helper: Make API request
     */
    public function make_api_request($endpoint, $method = 'GET', $data = null) {
        $url = ZZBP_API_BASE . $endpoint;
        
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json'
            )
        );
        
        if ($data && in_array($method, array('POST', 'PUT', 'PATCH'))) {
            $args['body'] = json_encode($data);
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return array('success' => false, 'error' => $response->get_error_message());
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body, true);
    }
}

// Initialize the plugin
new ZeeZichtBookingPro();

/**
 * Activation hook
 */
register_activation_hook(__FILE__, 'zzbp_activate');
function zzbp_activate() {
    // Create necessary database tables or options if needed
    flush_rewrite_rules();
}

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, 'zzbp_deactivate');
function zzbp_deactivate() {
    flush_rewrite_rules();
}
