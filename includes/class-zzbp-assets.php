<?php
/**
 * ZeeZicht Booking Pro - Assets Handler
 * 
 * Handles all CSS and JavaScript enqueuing for the plugin.
 * Follows Single Responsibility Principle.
 * 
 * @package ZeeZichtBookingPro
 * @subpackage Assets
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
        global $post;
        
        $needs_assets = false;
        
        // Method 1: Check for ANY of our shortcodes in post content
        $our_shortcodes = [
            'zzbp_accommodation_list',
            'zzbp_hero_image',
            'zzbp_property_header', 
            'zzbp_booking_sidebar',
            'zzbp_amenities',
            'zzbp_photo_gallery',
            'zzbp_availability_calendar',
            'zzbp_booking_modal'
        ];
        
        if (is_a($post, 'WP_Post')) {
            foreach ($our_shortcodes as $shortcode) {
                if (has_shortcode($post->post_content, $shortcode)) {
                    $needs_assets = true;
                    break;
                }
            }
        }
        
        // Method 2: Check for single accommodation URL pattern
        if (get_query_var('zzbp_accommodation_slug') || 
            defined('ZZBP_LOADING_SINGLE_ACCOMMODATION')) {
            $needs_assets = true;
        }
        
        // Method 3: Check URL pattern for accommodations (FALLBACK)
        $current_url = $_SERVER['REQUEST_URI'] ?? '';
        if (strpos($current_url, '/accommodations/') !== false) {
            $needs_assets = true;
        }
        
        if ($needs_assets) {
            error_log('ZZBP: Enqueuing assets - URL: ' . $current_url);
            $this->enqueue_all_assets();
        } else {
            error_log('ZZBP: No assets needed - URL: ' . $current_url);
        }
    }
    
    /**
     * Enqueue ALL assets - ONE PLACE, ONE TIME
     */
    private function enqueue_all_assets() {
        // Main CSS - Built version preferred, fallback to dev version
        $css_dist = plugin_dir_path(ZZBP_PLUGIN_FILE) . 'dist/css/style.min.css';
        $css_dev = plugin_dir_path(ZZBP_PLUGIN_FILE) . 'assets/css/tailwind.css';
        
        if (file_exists($css_dist)) {
            wp_enqueue_style(
                'zzbp-main-style',
                plugin_dir_url(ZZBP_PLUGIN_FILE) . 'dist/css/style.min.css',
                [],
                '10.0.0'
            );
        } elseif (file_exists($css_dev)) {
            wp_enqueue_style(
                'zzbp-main-style',
                plugin_dir_url(ZZBP_PLUGIN_FILE) . 'assets/css/tailwind.css',
                [],
                '10.0.0'
            );
        }

        // External Libraries
        wp_enqueue_script(
            'flatpickr-js',
            'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js',
            [],
            '4.6.13',
            true
        );
        
        wp_enqueue_style(
            'flatpickr-css',
            'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css',
            [],
            '4.6.13'
        );
        
        wp_enqueue_script(
            'flatpickr-nl',
            'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/nl.js',
            ['flatpickr-js'],
            '4.6.13',
            true
        );

        wp_enqueue_script(
            'lucide-icons', 
            'https://unpkg.com/lucide@latest/dist/umd/lucide.js', 
            [], 
            null, 
            true
        );
        
        // Main JS - Built version preferred, fallback to dev version
        $js_dist = plugin_dir_path(ZZBP_PLUGIN_FILE) . 'dist/js/app.min.js';
        $js_dev = plugin_dir_path(ZZBP_PLUGIN_FILE) . 'assets/js/main.js';
        
        if (file_exists($js_dist)) {
            wp_enqueue_script(
                'zzbp-main',
                plugin_dir_url(ZZBP_PLUGIN_FILE) . 'dist/js/app.min.js',
                ['jquery', 'flatpickr-js'],
                '10.0.0',
                true
            );
        } elseif (file_exists($js_dev)) {
            wp_enqueue_script(
                'zzbp-main',
                plugin_dir_url(ZZBP_PLUGIN_FILE) . 'assets/js/main.js',
                ['jquery', 'flatpickr-js'],
                '10.0.0',
                true
            );
        }
        
        // Localize script if any JS was loaded
        if (file_exists($js_dist) || file_exists($js_dev)) {
            wp_localize_script('zzbp-main', 'zzbp_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('zzbp_nonce')
            ));
        }
    }
    
    /**
     * Force enqueue assets (for routing that needs immediate loading)
     */
    public function force_enqueue_assets() {
        $this->enqueue_all_assets();
    }
}
