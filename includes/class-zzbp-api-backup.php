<?php
/**
 * ZeeZicht Booking Pro - API Handler
 * 
 * Handles all external API communication for the plugin.
 * Follows Single Responsibility Principle.
 * 
 * @package ZeeZichtBookingPro
 * @subpackage API
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ZZBP_Api {
    
    private $api_base_url;
    private $api_key;
    
    /**
     * Initialize API handler
     */
    public function __construct() {
        // Always use the new settings format first (priority)
        $settings = get_option('zzbp_settings', array());
        $this->api_base_url = $settings['api_base_url'] ?? '';
        $this->api_key = $settings['api_key'] ?? '';
        
        // Fallback to old settings format if new ones don't exist
        if (empty($this->api_key)) {
            $this->api_key = get_option('zzbp_api_key', '');
            $this->api_base_url = get_option('zzbp_dashboard_url', 'http://localhost:2121');
        }
        
        // Ensure base URL has /api suffix for API calls
        if (!empty($this->api_base_url) && !str_ends_with($this->api_base_url, '/api')) {
            $this->api_base_url = rtrim($this->api_base_url, '/') . '/api';
        }
        
        // Debug logging
        error_log("ZZBP API Debug - Using API Key: " . substr($this->api_key, 0, 10) . "... (length: " . strlen($this->api_key) . ")");
        error_log("ZZBP API Debug - Using Base URL: " . $this->api_base_url);
    }
    
    /**
     * Get all accommodations - Using working method from old plugin
     */
    public function get_accommodations() {
        if (!$this->api_key) {
            return array();
        }
        
        $response = wp_remote_get($this->api_base_url . '/api/plugin/accommodations?' . http_build_query(array(
            'domain' => parse_url(home_url(), PHP_URL_HOST)
        )), array(
            'headers' => array(
                'X-API-Key' => $this->api_key
            )
        ));
        
        if (is_wp_error($response)) {
            error_log('ZZBP API Error: ' . $response->get_error_message());
            return array();
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        return $data['success'] ? $data['data'] : array();
    }
    
    /**
     * Get single accommodation by ID or slug
     */
    public function get_accommodation($identifier) {
        $response = $this->make_request('/accommodations/' . urlencode($identifier));
        
        if (!$response || !isset($response['success']) || !$response['success']) {
            throw new Exception('Failed to fetch accommodation from API');
        }
        
        return $response['data'] ?? null;
    }
    
    /**
     * Calculate pricing for accommodation
     */
    public function calculate_pricing($accommodation_id, $check_in, $check_out, $guests = 2) {
        $data = array(
            'accommodation_id' => $accommodation_id,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'guests' => $guests
        );
        
        $response = $this->make_request('/pricing/calculate', 'POST', $data);
        
        if (!$response || !isset($response['success']) || !$response['success']) {
            throw new Exception('Failed to calculate pricing');
        }
        
        return $response['data'] ?? null;
    }
    
    /**
     * Create a new booking
     */
    public function create_booking($booking_data) {
        $response = $this->make_request('/bookings', 'POST', $booking_data);
        
        if (!$response || !isset($response['success']) || !$response['success']) {
            throw new Exception('Failed to create booking');
     */
    public function test_connection() {
        if (!$this->api_key) {
            error_log("ZZBP API Test: No API key configured");
            return array('success' => false, 'message' => 'No API key configured');
        }
        
        $url = $this->api_base_url . '/accommodations';
        error_log("ZZBP API Test: Testing connection to: " . $url);
        error_log("ZZBP API Test: Using API key: " . substr($this->api_key, 0, 10) . "...");
        
        $response = wp_remote_get($url, array(
            'headers' => array(
                'X-API-Key' => $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log("ZZBP API Test: WP Error - " . $error_message);
            return array('success' => false, 'message' => $error_message);
        error_log('ZZBP API Response: ' . json_encode($data, JSON_PRETTY_PRINT));
        
        return array('success' => true, 'data' => $data['property'] ?? $data['data'] ?? null);
    }
    
    /**
     * Get property information
     */
    public function get_property_info() {
        $response = $this->make_request('/property/info');
        
        if (!$response || !isset($response['success']) || !$response['success']) {
            throw new Exception('Failed to fetch property info from API');
        }
        
        return $response['data'] ?? array();
    }
    
    /**
     * Update property information
     */
    public function update_property_info($property_data) {
        $response = $this->make_request('/property/info', 'PUT', $property_data);
        
        if (!$response || !isset($response['success']) || !$response['success']) {
            throw new Exception('Failed to update property info');
        }
        
        return $response['data'] ?? null;
    }
    
    /**
     * Make API request
     */
    private function make_request($endpoint, $method = 'GET', $data = null) {
        $url = rtrim($this->api_base_url, '/') . '/' . ltrim($endpoint, '/');
        
        $args = array(
            'method' => $method,
            'timeout' => 30,
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'ZeeZicht-Booking-Pro/' . ZZBP_VERSION
            )
        );
        
        // Add API key if available
        if (!empty($this->api_key)) {
            $args['headers']['Authorization'] = 'Bearer ' . $this->api_key;
        }
        
        // Add data for POST/PUT requests
        if ($data && in_array($method, array('POST', 'PUT', 'PATCH'))) {
            $args['body'] = json_encode($data);
        }
        
        // Log the request for debugging
        error_log("ZZBP API Request: {$method} {$url}");
        if ($data) {
            error_log("ZZBP API Data: " . json_encode($data));
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            error_log("ZZBP API Error: " . $response->get_error_message());
            throw new Exception('API request failed: ' . $response->get_error_message());
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        
        error_log("ZZBP API Response: {$status_code}");
        
        if ($status_code >= 400) {
            error_log("ZZBP API Error Response: " . $body);
            throw new Exception("API request failed with status {$status_code}");
        }
        
        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("ZZBP API JSON Error: " . json_last_error_msg());
            throw new Exception('Invalid JSON response from API');
        }
        
        return $decoded;
    }
    
    /**
     * Set API credentials
     */
    public function set_credentials($api_base_url, $api_key = '') {
        $this->api_base_url = $api_base_url;
        $this->api_key = $api_key;
    }
}
