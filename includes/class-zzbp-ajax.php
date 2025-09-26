<?php
/**
 * ZeeZicht Booking Pro - AJAX Handler
 * 
 * Handles all AJAX requests for the plugin.
 * Follows Single Responsibility Principle.
 * 
 * @package ZeeZichtBookingPro
 * @subpackage AJAX
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ZZBP_Ajax {
    
    /**
     * Initialize AJAX handlers
     */
    public function __construct() {
        $this->register_ajax_handlers();
    }
    
    /**
     * Register all AJAX handlers
     */
    private function register_ajax_handlers() {
        // Public and logged-in user handlers
        add_action('wp_ajax_zzbp_get_accommodations', array($this, 'ajax_get_accommodations'));
        add_action('wp_ajax_nopriv_zzbp_get_accommodations', array($this, 'ajax_get_accommodations'));
        
        add_action('wp_ajax_zzbp_calculate_pricing', array($this, 'ajax_calculate_pricing'));
        add_action('wp_ajax_nopriv_zzbp_calculate_pricing', array($this, 'ajax_calculate_pricing'));
        
        add_action('wp_ajax_zzbp_create_booking', array($this, 'ajax_create_booking'));
        add_action('wp_ajax_nopriv_zzbp_create_booking', array($this, 'ajax_create_booking'));
        
        // Admin-only handlers
        add_action('wp_ajax_zzbp_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_zzbp_test_api_connection', array($this, 'ajax_test_api_connection'));
    }
    
    /**
     * AJAX: Get accommodations - Using working method from old plugin
     */
    public function ajax_get_accommodations() {
        check_ajax_referer('zzbp_nonce', 'nonce');
        
        $api = new ZZBP_Api();
        $accommodations = $api->get_accommodations();
        
        wp_send_json_success($accommodations);
    }
    
    /**
     * AJAX: Calculate pricing
     */
    public function ajax_calculate_pricing() {
        check_ajax_referer('zzbp_nonce', 'nonce');
        
        $accommodation_id = sanitize_text_field($_POST['accommodation_id'] ?? '');
        $check_in = sanitize_text_field($_POST['check_in'] ?? '');
        $check_out = sanitize_text_field($_POST['check_out'] ?? '');
        $guests = intval($_POST['guests'] ?? 2);
        
        if (empty($accommodation_id) || empty($check_in) || empty($check_out)) {
            wp_send_json_error(array(
                'message' => 'Missing required parameters'
            ));
            return;
        }
        
        try {
            $api = new ZZBP_Api();
            $pricing = $api->calculate_pricing($accommodation_id, $check_in, $check_out, $guests);
            
            wp_send_json_success($pricing);
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Failed to calculate pricing: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * AJAX: Create booking
     */
    public function ajax_create_booking() {
        check_ajax_referer('zzbp_nonce', 'nonce');
        
        $booking_data = array(
            'accommodation_id' => sanitize_text_field($_POST['accommodation_id'] ?? ''),
            'check_in' => sanitize_text_field($_POST['check_in'] ?? ''),
            'check_out' => sanitize_text_field($_POST['check_out'] ?? ''),
            'guests' => intval($_POST['guests'] ?? 2),
            'guest_name' => sanitize_text_field($_POST['guest_name'] ?? ''),
            'guest_email' => sanitize_email($_POST['guest_email'] ?? ''),
            'guest_phone' => sanitize_text_field($_POST['guest_phone'] ?? ''),
            'special_requests' => sanitize_textarea_field($_POST['special_requests'] ?? '')
        );
        
        // Validate required fields
        $required_fields = ['accommodation_id', 'check_in', 'check_out', 'guest_name', 'guest_email'];
        foreach ($required_fields as $field) {
            if (empty($booking_data[$field])) {
                wp_send_json_error(array(
                    'message' => "Missing required field: {$field}"
                ));
                return;
            }
        }
        
        try {
            $api = new ZZBP_Api();
            $booking = $api->create_booking($booking_data);
            
            wp_send_json_success($booking);
        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => 'Failed to create booking: ' . $e->getMessage()
            ));
        }
    }
    
    /**
     * AJAX: Save settings (admin only)
     */
    public function ajax_save_settings() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => 'Insufficient permissions'
            ));
            return;
        }
        
        check_ajax_referer('zzbp_nonce', 'nonce');
        
        $settings = array(
            'api_base_url' => esc_url_raw($_POST['api_base_url'] ?? ''),
            'api_key' => sanitize_text_field($_POST['api_key'] ?? ''),
            'property_name' => sanitize_text_field($_POST['property_name'] ?? ''),
            'contact_email' => sanitize_email($_POST['contact_email'] ?? ''),
            'contact_phone' => sanitize_text_field($_POST['contact_phone'] ?? '')
        );
        
        update_option('zzbp_settings', $settings);
        
        wp_send_json_success(array(
            'message' => 'Settings saved successfully'
        ));
    }
    
    /**
     * AJAX: Test API connection (admin only) - Using working method
     */
    public function ajax_test_api_connection() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => 'Insufficient permissions'
            ));
            return;
        }
        
        check_ajax_referer('zzbp_nonce', 'nonce');
        
        $api = new ZZBP_Api();
        $result = $api->test_connection();
        
        if ($result['success']) {
            wp_send_json_success(array(
                'message' => 'API connection successful'
            ));
        } else {
            wp_send_json_error(array(
                'message' => $result['error'] ?? 'API connection failed'
            ));
        }
    }
}
