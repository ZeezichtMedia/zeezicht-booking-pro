<?php
/**
 * Plugin Name: ZeeZicht Booking Pro
 * Plugin URI: https://zee-zicht.nl
 * Description: Professional booking management plugin with modular architecture
 * Version: 2.0.0
 * Author: ZeeZicht Media
 * License: GPL v2 or later
 * Text Domain: zee-zicht-booking-pro
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ZZBP_PLUGIN_FILE', __FILE__);
define('ZZBP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ZZBP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ZZBP_VERSION', '2.0.0');

/**
 * Main Plugin Class - CLEAN BOOTSTRAPPER
 */
class ZeeZichtBookingPro_Clean {
    
    /**
     * Plugin instances
     */
    private $admin;
    private $ajax;
    private $api;
    private $assets;
    private $routing;
    private $shortcodes;
    
    /**
     * Initialize the plugin
     */
    public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Initialize all plugin components
     */
    public function init() {
        $this->load_dependencies();
        $this->init_components();
    }
    
    /**
     * Load all required files
     */
    private function load_dependencies() {
        require_once ZZBP_PLUGIN_PATH . 'includes/class-zzbp-admin.php';
        require_once ZZBP_PLUGIN_PATH . 'includes/class-zzbp-ajax.php';
        require_once ZZBP_PLUGIN_PATH . 'includes/class-zzbp-api.php';
        require_once ZZBP_PLUGIN_PATH . 'includes/class-zzbp-assets.php';
        require_once ZZBP_PLUGIN_PATH . 'includes/class-zzbp-routing.php';
        require_once ZZBP_PLUGIN_PATH . 'includes/class-zzbp-shortcodes.php';
    }
    
    /**
     * Initialize all components
     */
    private function init_components() {
        // Initialize in correct order
        $this->api = new ZZBP_Api();
        $this->assets = new ZZBP_Assets();
        $this->shortcodes = new ZZBP_Shortcodes();
        $this->routing = new ZZBP_Routing();
        $this->ajax = new ZZBP_Ajax();
        
        // Admin only in admin area
        if (is_admin()) {
            $this->admin = new ZZBP_Admin();
        }
    }
    
    /**
     * Get API instance
     */
    public function get_api() {
        return $this->api;
    }
    
    /**
     * Get accommodations (cached)
     */
    public function get_accommodations() {
        return $this->api->get_accommodations();
    }
}

/**
 * Initialize the plugin
 */
function zzbp_clean_init() {
    return new ZeeZichtBookingPro_Clean();
}

// Start the plugin
zzbp_clean_init();

/**
 * Activation hook
 */
register_activation_hook(__FILE__, 'zzbp_clean_activate');
function zzbp_clean_activate() {
    // Initialize routing to add rewrite rules
    $routing = new ZZBP_Routing();
    $routing->add_rewrite_rules();
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * Deactivation hook
 */
register_deactivation_hook(__FILE__, 'zzbp_clean_deactivate');
function zzbp_clean_deactivate() {
    flush_rewrite_rules();
}
