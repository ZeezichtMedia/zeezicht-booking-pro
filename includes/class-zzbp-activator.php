<?php
/**
 * ZeeZicht Booking Pro - Activator
 * 
 * Handles plugin activation and deactivation.
 * Follows Single Responsibility Principle.
 * 
 * @package ZeeZichtBookingPro
 * @subpackage Activator
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ZZBP_Activator {
    
    /**
     * Plugin activation
     */
    public static function activate() {
        // Create default settings
        self::create_default_settings();
        
        // Create default property info
        self::create_default_property_info();
        
        // Add rewrite rules and flush
        self::setup_rewrite_rules();
        
        // Create database tables if needed
        self::create_database_tables();
        
        // Set activation flag
        update_option('zzbp_activated', true);
        
        error_log('ZZBP: Plugin activated successfully');
    }
    
    /**
     * Plugin deactivation
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Clear any scheduled events
        wp_clear_scheduled_hook('zzbp_daily_cleanup');
        
        // Remove activation flag
        delete_option('zzbp_activated');
        
        error_log('ZZBP: Plugin deactivated');
    }
    
    /**
     * Plugin uninstall (called from uninstall.php)
     */
    public static function uninstall() {
        // Remove all plugin options
        delete_option('zzbp_settings');
        delete_option('zzbp_property_info');
        delete_option('zzbp_activated');
        delete_option('zzbp_version');
        
        // Drop custom database tables if any
        self::drop_database_tables();
        
        // Clear any cached data
        wp_cache_flush();
        
        error_log('ZZBP: Plugin uninstalled and cleaned up');
    }
    
    /**
     * Create default settings
     */
    private static function create_default_settings() {
        $default_settings = array(
            'api_base_url' => 'http://localhost:2121/api',
            'api_key' => '',
            'property_name' => 'My Property',
            'contact_email' => get_option('admin_email'),
            'contact_phone' => '',
            'currency' => 'EUR',
            'date_format' => 'Y-m-d',
            'timezone' => get_option('timezone_string') ?: 'Europe/Amsterdam'
        );
        
        add_option('zzbp_settings', $default_settings);
    }
    
    /**
     * Create default property info
     */
    private static function create_default_property_info() {
        $default_property_info = array(
            'url_structure' => array(
                'base' => 'accommodations'
            ),
            'settings' => array(
                'business' => array(
                    'name' => 'ZeeZicht Media',
                    'phone' => '+31 123 456 789',
                    'email' => get_option('admin_email')
                )
            )
        );
        
        add_option('zzbp_property_info', $default_property_info);
    }
    
    /**
     * Setup rewrite rules
     */
    private static function setup_rewrite_rules() {
        // Add rewrite rules for accommodations
        add_rewrite_rule(
            '^accommodations/([^/]+)/?$',
            'index.php?zzbp_accommodation_slug=$matches[1]',
            'top'
        );
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }
    
    /**
     * Create database tables if needed
     */
    private static function create_database_tables() {
        global $wpdb;
        
        // Example: Create a bookings cache table
        $table_name = $wpdb->prefix . 'zzbp_bookings_cache';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            booking_id varchar(100) NOT NULL,
            accommodation_id varchar(100) NOT NULL,
            check_in date NOT NULL,
            check_out date NOT NULL,
            guest_name varchar(255) NOT NULL,
            guest_email varchar(255) NOT NULL,
            status varchar(50) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY booking_id (booking_id),
            KEY accommodation_id (accommodation_id),
            KEY check_in (check_in),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Store database version
        update_option('zzbp_db_version', '1.0');
    }
    
    /**
     * Drop database tables
     */
    private static function drop_database_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'zzbp_bookings_cache';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        
        delete_option('zzbp_db_version');
    }
    
    /**
     * Check if plugin needs update
     */
    public static function check_version() {
        $installed_version = get_option('zzbp_version');
        
        if ($installed_version !== ZZBP_VERSION) {
            self::update_plugin($installed_version);
            update_option('zzbp_version', ZZBP_VERSION);
        }
    }
    
    /**
     * Update plugin from old version
     */
    private static function update_plugin($old_version) {
        error_log("ZZBP: Updating from version {$old_version} to " . ZZBP_VERSION);
        
        // Version-specific updates
        if (version_compare($old_version, '1.0', '<')) {
            // Update from pre-1.0 versions
            self::update_to_v1_0();
        }
        
        // Always flush rewrite rules on update
        flush_rewrite_rules();
    }
    
    /**
     * Update to version 1.0
     */
    private static function update_to_v1_0() {
        // Migrate old settings format if needed
        $old_settings = get_option('zzbp_old_settings');
        if ($old_settings) {
            $new_settings = array(
                'api_base_url' => $old_settings['api_url'] ?? 'http://localhost:2121/api',
                'api_key' => $old_settings['key'] ?? '',
                'property_name' => $old_settings['name'] ?? 'My Property'
            );
            
            update_option('zzbp_settings', $new_settings);
            delete_option('zzbp_old_settings');
        }
    }
}
