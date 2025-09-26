<?php
/**
 * ZeeZicht Booking Pro - CLEAN Shortcodes Handler
 * 
 * PROFESSIONAL BEST PRACTICE VERSION
 * - No inline styles/scripts
 * - Template parts only
 * - Single responsibility
 * 
 * @package ZeeZichtBookingPro
 * @subpackage Shortcodes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ZZBP_Shortcodes {
    
    /**
     * Initialize shortcodes
     */
    public function __construct() {
        $this->register_shortcodes();
    }
    
    /**
     * Register all shortcodes - CLEAN VERSION
     */
    private function register_shortcodes() {
        // Main shortcodes
        add_shortcode('zzbp_accommodation_list', array($this, 'accommodation_list_shortcode'));
        
        // Modular component shortcodes - TEMPLATE PARTS ONLY
        add_shortcode('zzbp_hero_image', array($this, 'hero_image_shortcode'));
        add_shortcode('zzbp_property_header', array($this, 'property_header_shortcode'));
        add_shortcode('zzbp_booking_sidebar', array($this, 'booking_sidebar_shortcode'));
        add_shortcode('zzbp_amenities', array($this, 'amenities_shortcode'));
        add_shortcode('zzbp_photo_gallery', array($this, 'photo_gallery_shortcode'));
        add_shortcode('zzbp_availability_calendar', array($this, 'availability_calendar_shortcode'));
        add_shortcode('zzbp_booking_modal', array($this, 'booking_modal_shortcode'));
        add_shortcode('zzbp_description', array($this, 'description_shortcode'));
    }
    
    /**
     * Accommodation list shortcode - CLEAN LOADER
     */
    public function accommodation_list_shortcode($atts) {
        $atts = shortcode_atts(array(
            'layout' => 'grid',
            'show_pricing' => 'true',
            'show_amenities' => 'true'
        ), $atts);
        
        ob_start();
        include ZZBP_PLUGIN_PATH . 'templates/accommodation-list.php';
        return ob_get_clean();
    }
    
    /**
     * Hero image shortcode - CLEAN LOADER
     */
    public function hero_image_shortcode($atts) {
        ob_start();
        include ZZBP_PLUGIN_PATH . 'templates/parts/hero-image.php';
        return ob_get_clean();
    }
    
    /**
     * Property header shortcode - CLEAN LOADER
     */
    public function property_header_shortcode($atts) {
        ob_start();
        include ZZBP_PLUGIN_PATH . 'templates/parts/property-header.php';
        return ob_get_clean();
    }
    
    /**
     * Booking sidebar shortcode - CLEAN LOADER
     */
    public function booking_sidebar_shortcode($atts) {
        ob_start();
        include ZZBP_PLUGIN_PATH . 'templates/parts/booking-sidebar.php';
        return ob_get_clean();
    }
    
    /**
     * Amenities shortcode - CLEAN LOADER
     */
    public function amenities_shortcode($atts) {
        ob_start();
        include ZZBP_PLUGIN_PATH . 'templates/parts/amenities.php';
        return ob_get_clean();
    }
    
    /**
     * Photo gallery shortcode - CLEAN LOADER
     */
    public function photo_gallery_shortcode($atts) {
        ob_start();
        include ZZBP_PLUGIN_PATH . 'templates/parts/photo-gallery.php';
        return ob_get_clean();
    }
    
    /**
     * Availability calendar shortcode - CLEAN LOADER
     */
    public function availability_calendar_shortcode($atts) {
        ob_start();
        include ZZBP_PLUGIN_PATH . 'templates/parts/availability-calendar.php';
        return ob_get_clean();
    }
    
    /**
     * Booking modal shortcode - CLEAN LOADER
     */
    public function booking_modal_shortcode($atts) {
        ob_start();
        include ZZBP_PLUGIN_PATH . 'templates/parts/booking-modal.php';
        return ob_get_clean();
    }
    
    /**
     * Description shortcode - CLEAN LOADER
     */
    public function description_shortcode($atts) {
        ob_start();
        include ZZBP_PLUGIN_PATH . 'templates/parts/description.php';
        return ob_get_clean();
    }
}
