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
        
        // CORRECTE TIMING: Enqueue assets op het juiste moment
        add_action('wp_enqueue_scripts', array($this, 'conditionally_enqueue_assets'));
        
        // Register shortcodes
        add_shortcode('zzbp_booking_form', array($this, 'booking_form_shortcode'));
        add_shortcode('zzbp_accommodation_list', array($this, 'accommodation_list_shortcode'));
        add_shortcode('zzbp_pricing_calculator', array($this, 'pricing_calculator_shortcode'));
        add_shortcode('zzbp_debug_test', array($this, 'debug_test_shortcode'));
        
        // MODULAIRE COMPONENT SHORTCODES
        add_shortcode('zzbp_hero_image', array($this, 'hero_image_shortcode'));
        add_shortcode('zzbp_property_header', array($this, 'property_header_shortcode'));
        add_shortcode('zzbp_photo_gallery', array($this, 'photo_gallery_shortcode'));
        add_shortcode('zzbp_amenities', array($this, 'amenities_shortcode'));
        add_shortcode('zzbp_booking_sidebar', array($this, 'booking_sidebar_shortcode'));
        add_shortcode('zzbp_description', array($this, 'description_shortcode'));
        
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
     */
    public function enqueue_single_view_assets() {
        // Laad ÉÉN ENKEL, GEBOUWD CSS-bestand
        wp_enqueue_style(
            'zzbp-main-style',
            plugin_dir_url(__FILE__) . 'assets/css/tailwind.css',
            [],
            '5.0.0' // Cache bust - DEBUG 404
        );
        
        // DEBUG: Log de URL
        error_log('ZZBP CSS URL: ' . plugin_dir_url(__FILE__) . 'assets/css/tailwind.css');

        // Laad Lucide Icons
        wp_enqueue_script(
            'lucide-icons', 
            'https://unpkg.com/lucide@latest/dist/umd/lucide.js', 
            [], 
            null, 
            true
        );
        
        // Enqueue custom JavaScript
        wp_enqueue_script(
            'zzbp-main',
            plugin_dir_url(__FILE__) . 'assets/js/main.js',
            array('jquery'),
            '4.0.0',
            true
        );

        // Localize script for AJAX
        wp_localize_script('zzbp-main', 'zzbp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('zzbp_nonce')
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
     * Debug test shortcode - Test sidebar layout met geforceerde CSS
     */
    public function debug_test_shortcode($atts) {
        // KRITIEK: Forceer CSS loading in shortcode context
        if (!wp_style_is('zzbp-main-style', 'enqueued')) {
            wp_enqueue_style(
                'zzbp-main-style',
                plugin_dir_url(__FILE__) . 'assets/css/tailwind.css',
                [],
                '6.0.0' // Shortcode CSS fix
            );
        }
        
        ob_start();
        include ZZBP_PLUGIN_PATH . 'templates/debug-test.php';
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
                'domain' => parse_url(home_url(), PHP_URL_HOST),
                'plugin_version' => ZZBP_VERSION
            )),
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-API-Key' => $api_key
            ),
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
            
            // Auto-create pages with updated info
            $this->auto_create_pages($test_result['data']);
            
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
            'domain' => parse_url(home_url(), PHP_URL_HOST)
        )), array(
            'headers' => array(
                'X-API-Key' => $api_key
            )
        ));
        
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
     * Conditionally enqueue assets - CORRECT TIMING method
     */
    public function conditionally_enqueue_assets() {
        global $post;
        
        // Check if post content contains our shortcode OR single accommodation URL
        $needs_assets = false;
        
        // Method 1: Check for shortcode in post content
        if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'zzbp_debug_test')) {
            $needs_assets = true;
            error_log('ZZBP: Loading CSS for debug shortcode');
        }
        
        // Method 2: Check for single accommodation URL pattern
        if (get_query_var('zzbp_accommodation_slug')) {
            $needs_assets = true;
            error_log('ZZBP: Loading CSS for single accommodation: ' . get_query_var('zzbp_accommodation_slug'));
        }
        
        // Method 3: FALLBACK - Check URL pattern manually (MEEST BETROUWBAAR)
        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($current_url, '/accommodations/') !== false) {
            $needs_assets = true;
            error_log('ZZBP: Loading CSS for accommodations URL: ' . $current_url);
        }
        
        // Method 4: EXTRA FALLBACK - Check if we're in handle_single_accommodation context
        if (defined('ZZBP_LOADING_SINGLE_ACCOMMODATION')) {
            $needs_assets = true;
            error_log('ZZBP: Loading CSS for single accommodation context');
        }
        
        if ($needs_assets) {
            error_log('ZZBP: Enqueuing assets now!');
            $this->enqueue_the_actual_files();
        } else {
            error_log('ZZBP: No assets needed for this page');
        }
    }
    
    /**
     * Enqueue the actual CSS and JS files
     */
    private function enqueue_the_actual_files() {
        wp_enqueue_style(
            'zzbp-main-style',
            plugin_dir_url(__FILE__) . 'assets/css/tailwind.css',
            [],
            '9.0.0' // CORRECTE TIMING versie
        );

        wp_enqueue_script(
            'lucide-icons', 
            'https://unpkg.com/lucide@latest/dist/umd/lucide.js', 
            [], 
            null, 
            true
        );
        
        // Alleen JS laden als het bestand bestaat
        $js_file = plugin_dir_path(__FILE__) . 'assets/js/main.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'zzbp-main',
                plugin_dir_url(__FILE__) . 'assets/js/main.js',
                array('jquery'),
                '9.0.0',
                true
            );
            
            wp_localize_script('zzbp-main', 'zzbp_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('zzbp_nonce')
            ));
        }
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
            // Define dat we een single accommodation laden (voor CSS detectie)
            define('ZZBP_LOADING_SINGLE_ACCOMMODATION', true);
            // Debug logging
            error_log("ZZBP Debug: Looking for accommodation slug: " . $accommodation_slug);
            
            // Get accommodation data
            $accommodations = $this->get_accommodations();
            error_log("ZZBP Debug: Found " . count($accommodations) . " accommodations");
            
            $current_accommodation = null;
            
            foreach ($accommodations as $acc) {
                error_log("ZZBP Debug: Checking accommodation: " . $acc['name'] . " with slug: " . ($acc['slug'] ?? 'NO SLUG'));
                if (isset($acc['slug']) && $acc['slug'] === $accommodation_slug) {
                    $current_accommodation = $acc;
                    break;
                }
            }
            
            if ($current_accommodation) {
                error_log("ZZBP Debug: Found matching accommodation: " . $current_accommodation['name']);
                // Load single accommodation template
                $this->load_single_accommodation_template($current_accommodation);
                exit;
            } else {
                error_log("ZZBP Debug: No matching accommodation found for slug: " . $accommodation_slug);
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
        
        // CSS wordt nu geladen via conditionally_enqueue_assets (correcte timing)
        
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
    
    // ==========================================
    // MODULAIRE COMPONENT SHORTCODES
    // ==========================================
    
    /**
     * Hero Image Component
     */
    public function hero_image_shortcode($atts) {
        global $zzbp_current_accommodation;
        $accommodation = $zzbp_current_accommodation;
        
        if (!$accommodation) return '';
        
        $this->ensure_assets_loaded();
        
        // Fix image URL
        $image_url = $accommodation['primary_image'] ?? 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80';
        if (strpos($image_url, 'http') !== 0 && strpos($image_url, '/uploads/') === 0) {
            $image_url = 'http://localhost:2121' . $image_url;
        }
        
        ob_start();
        ?>
        <div class="relative h-[70vh] lg:h-[65vh] overflow-hidden rounded-2xl mb-8">
            <div class="absolute inset-0">
                <img 
                    src="<?php echo esc_url($image_url); ?>" 
                    alt="<?php echo esc_attr($accommodation['name']); ?>"
                    class="w-full h-full object-cover"
                    onerror="this.src='https://images.unsplash.com/photo-1571896349842-33c89424de2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80'"
                />
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent"></div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Property Header Component
     */
    public function property_header_shortcode($atts) {
        global $zzbp_current_accommodation;
        $accommodation = $zzbp_current_accommodation;
        
        if (!$accommodation) return '';
        
        $this->ensure_assets_loaded();
        
        ob_start();
        ?>
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 mb-8">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-4xl font-bold text-gray-900 mb-4"><?php echo esc_html($accommodation['name']); ?></h1>
                    <div class="flex items-center gap-6 text-sm text-gray-500">
                        <span class="flex items-center gap-2">
                            <i data-lucide="home" class="w-4 h-4"></i>
                            <?php echo esc_html(ucfirst($accommodation['type'] ?? 'Appartement')); ?>
                        </span>
                        <span class="flex items-center gap-2">
                            <i data-lucide="users" class="w-4 h-4"></i>
                            Max <?php echo esc_html($accommodation['max_guests'] ?? 4); ?> gasten
                        </span>
                        <?php if (!empty($accommodation['surface_area'])): ?>
                        <span class="flex items-center gap-2">
                            <i data-lucide="square" class="w-4 h-4"></i>
                            <?php echo esc_html($accommodation['surface_area']); ?>m²
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-gray-900">€<?php echo number_format($accommodation['base_price'] ?? 150, 0); ?></div>
                    <div class="text-gray-600">per nacht</div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Description Component
     */
    public function description_shortcode($atts) {
        global $zzbp_current_accommodation;
        $accommodation = $zzbp_current_accommodation;
        
        if (!$accommodation) return '';
        
        $this->ensure_assets_loaded();
        
        ob_start();
        ?>
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Beschrijving</h2>
            <p class="text-gray-700 leading-relaxed">
                <?php echo esc_html($accommodation['description'] ?? 'Welkom in onze prachtige accommodatie! Perfect voor een ontspannen verblijf.'); ?>
            </p>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Amenities Component
     */
    public function amenities_shortcode($atts) {
        global $zzbp_current_accommodation;
        $accommodation = $zzbp_current_accommodation;
        
        if (!$accommodation) return '';
        
        $this->ensure_assets_loaded();
        
        $amenities = $accommodation['amenities'] ?? ['wifi', 'parking', 'kitchen', 'tv', 'balcony', 'airco'];
        
        ob_start();
        ?>
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Voorzieningen</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <?php foreach ($amenities as $amenity): ?>
                <div class="flex items-center gap-3">
                    <span class="text-green-500">✓</span>
                    <span class="text-gray-700 capitalize"><?php echo esc_html(str_replace('_', ' ', $amenity)); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Photo Gallery Component - Met mooie modal zoals single accommodation
     */
    public function photo_gallery_shortcode($atts) {
        global $zzbp_current_accommodation;
        $accommodation = $zzbp_current_accommodation;
        
        if (!$accommodation) return '';
        
        $this->ensure_assets_loaded();
        
        $photos = $accommodation['photos'] ?? [];
        
        ob_start();
        ?>
        <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 mb-8">
            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Foto's</h2>
            
            <?php if (!empty($photos) && count($photos) > 0): ?>
                <!-- Photo Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="photo-gallery">
                    <?php foreach ($photos as $index => $photo_url): ?>
                        <?php 
                        // Fix photo URL
                        $fixed_photo_url = $photo_url;
                        if (strpos($fixed_photo_url, 'http') !== 0 && strpos($fixed_photo_url, '/uploads/') === 0) {
                            $fixed_photo_url = 'http://localhost:2121' . $fixed_photo_url;
                        }
                        ?>
                        <div class="gallery-item" onclick="openPhotoModal(<?php echo $index; ?>)">
                            <img 
                                src="<?php echo esc_url($fixed_photo_url); ?>" 
                                alt="<?php echo esc_attr($accommodation['name']); ?> foto <?php echo $index + 1; ?>"
                                class="gallery-image"
                                onerror="this.parentElement.style.display='none'"
                            />
                        </div>
                    <?php endforeach; ?>
                </div>
            
                <!-- Photo count indicator -->
                <div class="mt-4 text-center">
                    <span class="text-sm text-gray-600">
                        <?php echo count($photos); ?> foto's
                    </span>
                    <button onclick="openPhotoModal(0)" class="ml-4 text-rose-600 hover:text-rose-700 font-semibold text-sm">
                        Bekijk alle foto's →
                    </button>
                </div>
                
                <!-- Photo Modal -->
                <div id="photo-modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.9); z-index: 9999; display: none; align-items: center; justify-content: center;">
                    <div style="position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; padding: 1rem;">
                        <!-- Close Button -->
                        <button onclick="closePhotoModal()" 
                                style="position: absolute; top: 1.5rem; right: 1.5rem; z-index: 10000; background: rgba(255,255,255,0.2); border: none; border-radius: 50%; padding: 0.75rem; cursor: pointer; transition: all 0.2s;">
                            <svg style="width: 1.5rem; height: 1.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        
                        <!-- Previous Button -->
                        <button onclick="previousPhoto()" 
                                style="position: absolute; left: 1.5rem; top: 50%; transform: translateY(-50%); z-index: 10000; background: rgba(255,255,255,0.2); border: none; border-radius: 50%; padding: 0.75rem; cursor: pointer; transition: all 0.2s;">
                            <svg style="width: 1.5rem; height: 1.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                        </button>
                        
                        <!-- Next Button -->
                        <button onclick="nextPhoto()" 
                                style="position: absolute; right: 1.5rem; top: 50%; transform: translateY(-50%); z-index: 10000; background: rgba(255,255,255,0.2); border: none; border-radius: 50%; padding: 0.75rem; cursor: pointer; transition: all 0.2s;">
                            <svg style="width: 1.5rem; height: 1.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        
                        <!-- Main Image -->
                        <div style="max-width: 90%; max-height: 90%; display: flex; align-items: center; justify-content: center;">
                            <img id="modal-image" src="" alt="" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 0.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
                        </div>
                        
                        <!-- Photo Counter -->
                        <div style="position: absolute; bottom: 1.5rem; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.7); color: white; padding: 0.5rem 1rem; border-radius: 1.5rem; font-size: 0.875rem;">
                            <span id="photo-counter">1 / <?php echo count($photos); ?></span>
                        </div>
                        
                        <!-- Thumbnails -->
                        <div style="position: absolute; bottom: 5rem; left: 50%; transform: translateX(-50%); display: flex; gap: 0.5rem; max-width: 20rem; overflow-x: auto; padding: 0.5rem;" id="thumbnails-container">
                            <?php foreach ($photos as $index => $photo_url): ?>
                                <?php 
                                $fixed_photo_url = $photo_url;
                                if (strpos($fixed_photo_url, 'http') !== 0 && strpos($fixed_photo_url, '/uploads/') === 0) {
                                    $fixed_photo_url = 'http://localhost:2121' . $fixed_photo_url;
                                }
                                ?>
                                <img src="<?php echo esc_url($fixed_photo_url); ?>" 
                                     alt="Thumbnail <?php echo $index + 1; ?>"
                                     style="width: 4rem; height: 4rem; object-fit: cover; border-radius: 0.25rem; cursor: pointer; opacity: 0.5; transition: opacity 0.2s; flex-shrink: 0;"
                                     class="thumbnail"
                                     data-index="<?php echo $index; ?>"
                                     onclick="goToPhoto(<?php echo $index; ?>)"
                                     onmouseover="this.style.opacity='1'"
                                     onmouseout="this.style.opacity='0.5'">
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <!-- JavaScript voor modal -->
                <script>
                // Photo Gallery Modal
                const photos = <?php echo json_encode(array_map(function($url) {
                    return strpos($url, 'http') !== 0 && strpos($url, '/uploads/') === 0 ? 'http://localhost:2121' . $url : $url;
                }, $photos)); ?>;

                let currentPhotoIndex = 0;

                function openPhotoModal(index = 0) {
                    currentPhotoIndex = index;
                    const modal = document.getElementById('photo-modal');
                    const modalImage = document.getElementById('modal-image');
                    const photoCounter = document.getElementById('photo-counter');
                    
                    if (photos.length > 0) {
                        modalImage.src = photos[currentPhotoIndex];
                        photoCounter.textContent = `${currentPhotoIndex + 1} / ${photos.length}`;
                        modal.style.display = 'flex';
                        document.body.style.overflow = 'hidden';
                        
                        updateThumbnails();
                    }
                }

                function closePhotoModal() {
                    const modal = document.getElementById('photo-modal');
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }

                function nextPhoto() {
                    currentPhotoIndex = (currentPhotoIndex + 1) % photos.length;
                    updateModalPhoto();
                }

                function previousPhoto() {
                    currentPhotoIndex = (currentPhotoIndex - 1 + photos.length) % photos.length;
                    updateModalPhoto();
                }

                function goToPhoto(index) {
                    currentPhotoIndex = index;
                    updateModalPhoto();
                }

                function updateModalPhoto() {
                    const modalImage = document.getElementById('modal-image');
                    const photoCounter = document.getElementById('photo-counter');
                    
                    modalImage.src = photos[currentPhotoIndex];
                    photoCounter.textContent = `${currentPhotoIndex + 1} / ${photos.length}`;
                    updateThumbnails();
                }

                function updateThumbnails() {
                    const thumbnails = document.querySelectorAll('.thumbnail');
                    thumbnails.forEach((thumb, index) => {
                        if (index === currentPhotoIndex) {
                            thumb.style.opacity = '1';
                            thumb.style.border = '2px solid white';
                        } else {
                            thumb.style.opacity = '0.5';
                            thumb.style.border = 'none';
                        }
                    });
                }

                // Keyboard navigation
                document.addEventListener('keydown', function(e) {
                    const modal = document.getElementById('photo-modal');
                    if (modal && modal.style.display === 'flex') {
                        if (e.key === 'Escape') {
                            closePhotoModal();
                        } else if (e.key === 'ArrowRight') {
                            nextPhoto();
                        } else if (e.key === 'ArrowLeft') {
                            previousPhoto();
                        }
                    }
                });
                </script>
                
            <?php else: ?>
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Geen foto's beschikbaar</h3>
                    <p class="text-gray-600">Er zijn nog geen extra foto's toegevoegd voor deze accommodatie.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- CSS voor gallery styling -->
        <style>
        .gallery-item {
            aspect-ratio: 1;
            overflow: hidden;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .gallery-item:hover {
            transform: scale(1.02);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .gallery-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        </style>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Booking Sidebar Component
     */
    public function booking_sidebar_shortcode($atts) {
        global $zzbp_current_accommodation;
        $accommodation = $zzbp_current_accommodation;
        
        if (!$accommodation) return '';
        
        $this->ensure_assets_loaded();
        
        ob_start();
        ?>
        <div class="sticky top-6">
            <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100">
                <div class="mb-6">
                    <div class="text-2xl font-bold text-gray-900">€<?php echo number_format($accommodation['base_price'] ?? 150, 0); ?></div>
                    <p class="text-gray-600">per nacht</p>
                </div>
                
                <div class="space-y-4 mb-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Check-in</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Check-out</label>
                            <input type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Guests</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                            <option>2 guests</option>
                            <option>3 guests</option>
                            <option>4 guests</option>
                        </select>
                    </div>
                    
                    <button class="w-full bg-gradient-to-br from-rose-400 via-rose-500 to-rose-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-4 px-6 rounded-2xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl">
                        Check Availability
                    </button>
                </div>
                
                <div class="pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">Host: ZeeZicht Media</p>
                    <p class="text-sm text-gray-600">Phone: +31 123 456 789</p>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Ensure assets are loaded for shortcodes
     */
    private function ensure_assets_loaded() {
        if (!wp_style_is('zzbp-main-style', 'enqueued')) {
            wp_enqueue_style(
                'zzbp-main-style',
                plugin_dir_url(__FILE__) . 'assets/css/tailwind.css',
                [],
                '10.0.0'
            );
        }
        
        if (!wp_script_is('lucide-icons', 'enqueued')) {
            wp_enqueue_script(
                'lucide-icons', 
                'https://unpkg.com/lucide@latest/dist/umd/lucide.js', 
                [], 
                null, 
                true
            );
        }
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
