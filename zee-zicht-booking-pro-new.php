<?php
/**
 * Plugin Name: ZeeZicht Booking Pro
 * Plugin URI: https://zee-zicht.nl
 * Description: Professional booking management system for accommodations. Modular, scalable, and SEO-friendly.
 * Version: 2.0.0
 * Author: ZeeZicht Media
 * Author URI: https://zee-zicht.nl
 * Text Domain: zee-zicht-booking-pro
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('ZZBP_VERSION', '2.0.0');
define('ZZBP_PLUGIN_FILE', __FILE__);
define('ZZBP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ZZBP_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Main Plugin Class - Minimal Bootstrapper
 * 
 * This class only handles initialization and loading of other components.
 * All actual functionality is delegated to specialized classes.
 */
class ZeeZichtBookingPro {
    
    private $shortcodes;
    private $assets;
    private $ajax;
    private $api;
    private $routing;
    private $admin;
    
    /**
     * Initialize the plugin
     */
    public function __construct() {
        $this->load_dependencies();
        $this->init_components();
        $this->setup_hooks();
    }
    
    /**
     * Load all required class files
     */
    private function load_dependencies() {
        require_once ZZBP_PLUGIN_PATH . 'includes/class-zzbp-activator.php';
        require_once ZZBP_PLUGIN_PATH . 'includes/class-zzbp-api.php';
        require_once ZZBP_PLUGIN_PATH . 'includes/class-zzbp-assets.php';
        require_once ZZBP_PLUGIN_PATH . 'includes/class-zzbp-ajax.php';
        require_once ZZBP_PLUGIN_PATH . 'includes/class-zzbp-routing.php';
        require_once ZZBP_PLUGIN_PATH . 'includes/class-zzbp-shortcodes.php';
        
        // Only load admin classes in admin area
        if (is_admin()) {
            require_once ZZBP_PLUGIN_PATH . 'includes/class-zzbp-admin.php';
        }
    }
    
    /**
     * Initialize all components
     */
    private function init_components() {
        // Initialize core components
        $this->api = new ZZBP_Api();
        $this->assets = new ZZBP_Assets();
        $this->ajax = new ZZBP_Ajax();
        $this->routing = new ZZBP_Routing();
        $this->shortcodes = new ZZBP_Shortcodes();
        
        // Initialize admin components only in admin area
        if (is_admin()) {
            $this->admin = new ZZBP_Admin();
        }
    }
    
    /**
     * Setup WordPress hooks
     */
    private function setup_hooks() {
        // Plugin lifecycle hooks
        register_activation_hook(__FILE__, array('ZZBP_Activator', 'activate'));
        register_deactivation_hook(__FILE__, array('ZZBP_Activator', 'deactivate'));
        
        // Version check
        add_action('plugins_loaded', array('ZZBP_Activator', 'check_version'));
        
        // Internationalization
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }
    
    /**
     * Load plugin text domain for internationalization
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'zee-zicht-booking-pro',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }
    
    /**
     * Get API instance
     */
    public function get_api() {
        return $this->api;
    }
    
    /**
     * Get assets instance
     */
    public function get_assets() {
        return $this->assets;
    }
    
    /**
     * Get routing instance
     */
    public function get_routing() {
        return $this->routing;
    }
    
    /**
     * Legacy method compatibility - Get accommodations
     * Delegates to API class
     */
    public function get_accommodations() {
        try {
            return $this->api->get_accommodations();
        } catch (Exception $e) {
            error_log('ZZBP Error getting accommodations: ' . $e->getMessage());
            return array();
        }
    }
}

/**
 * Initialize the plugin
 */
function zzbp_init() {
    global $zzbp_plugin_instance;
    $zzbp_plugin_instance = new ZeeZichtBookingPro();
}

// Start the plugin
add_action('init', 'zzbp_init', 0);

/**
 * Plugin uninstall hook
 */
if (!function_exists('zzbp_uninstall')) {
    function zzbp_uninstall() {
        require_once plugin_dir_path(__FILE__) . 'includes/class-zzbp-activator.php';
        ZZBP_Activator::uninstall();
    }
}

register_uninstall_hook(__FILE__, 'zzbp_uninstall');
