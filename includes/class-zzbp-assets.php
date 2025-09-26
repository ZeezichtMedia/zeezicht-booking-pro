<?php
/**
 * ZeeZicht Booking Pro - Assets Management
 * 
 * Handles CSS and JavaScript loading with conditional enqueuing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ZZBP_Assets {
    
    /**
     * Initialize assets handling
     */
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'conditionally_enqueue_assets'));
    }
    
    /**
     * Conditionally enqueue assets - PROFESSIONAL BEST PRACTICE
     */
    public function conditionally_enqueue_assets() {
        // Check if we're on a page that needs our assets
        $needs_assets = false;
        
        // Check for shortcodes in post content
        if (is_singular()) {
            global $post;
            if ($post && (
                has_shortcode($post->post_content, 'zzbp_accommodation_list') ||
                has_shortcode($post->post_content, 'zzbp_hero_image') ||
                has_shortcode($post->post_content, 'zzbp_photo_gallery') ||
                has_shortcode($post->post_content, 'zzbp_availability_calendar') ||
                has_shortcode($post->post_content, 'zzbp_booking_modal')
            )) {
                $needs_assets = true;
            }
        }
        
        // Check for custom accommodation URLs
        if (get_query_var('accommodation_slug')) {
            $needs_assets = true;
        }
        
        // Check if we're on admin pages that need assets
        if (is_admin() && isset($_GET['page']) && strpos($_GET['page'], 'zzbp') === 0) {
            $needs_assets = true;
        }
        
        if ($needs_assets) {
            $this->enqueue_all_assets();
        }
    }
    
    /**
     * Enqueue all necessary assets
     */
    public function enqueue_all_assets() {
        // Enqueue Tailwind CSS (compiled)
        wp_enqueue_style(
            'zzbp-tailwind-css',
            ZZBP_PLUGIN_URL . 'assets/css/tailwind.css',
            array(),
            ZZBP_VERSION
        );
        
        // Enqueue Flatpickr CSS (external dependency)
        wp_enqueue_style(
            'flatpickr-css',
            'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css',
            array(),
            '4.6.13'
        );
        
        // Enqueue our main CSS (built from Tailwind)
        wp_enqueue_style(
            'zzbp-main-style',
            ZZBP_PLUGIN_URL . 'assets/css/tailwind.css',
            array('flatpickr-css'),
            ZZBP_VERSION
        );
        
        // Enqueue Flatpickr JS (external dependency)
        wp_enqueue_script(
            'flatpickr-js',
            'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js',
            array(),
            '4.6.13',
            true
        );
        
        // Enqueue Flatpickr Dutch locale
        wp_enqueue_script(
            'flatpickr-nl',
            'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/nl.js',
            array('flatpickr-js'),
            '4.6.13',
            true
        );
        
        // Enqueue our main JavaScript
        wp_enqueue_script(
            'zzbp-main-script',
            ZZBP_PLUGIN_URL . 'src/js/app.js',
            array('jquery', 'flatpickr-js', 'flatpickr-nl'),
            ZZBP_VERSION,
            true
        );
        
        // Localize script for AJAX and accommodation data
        wp_localize_script('zzbp-main-script', 'zzbp_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('zzbp_nonce')
        ));
        
        // Pass accommodation data to JavaScript if available
        global $zzbp_current_accommodation;
        $accommodation_data = array(
            'accommodation_id' => $zzbp_current_accommodation['id'] ?? 'demo',
            'accommodation_name' => $zzbp_current_accommodation['name'] ?? 'Demo',
            'base_price' => $zzbp_current_accommodation['base_price'] ?? 26.00
        );
        
        wp_localize_script('zzbp-main-script', 'ZZBP_ACCOMMODATION_DATA', $accommodation_data);
    }
    
    /**
     * Force enqueue assets (for routing that needs immediate loading)
     */
    public function force_enqueue_assets() {
        $this->enqueue_all_assets();
    }
}
