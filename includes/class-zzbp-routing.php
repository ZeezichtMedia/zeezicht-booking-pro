<?php
/**
 * ZeeZicht Booking Pro - Routing Handler
 * 
 * Handles URL rewrites and template routing for the plugin.
 * Follows Single Responsibility Principle.
 * 
 * @package ZeeZichtBookingPro
 * @subpackage Routing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class ZZBP_Routing {
    
    /**
     * Initialize routing
     */
    public function __construct() {
        add_action('init', array($this, 'add_rewrite_rules'));
        add_filter('query_vars', array($this, 'add_query_vars'));
        add_action('template_redirect', array($this, 'handle_single_accommodation'));
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
            // Define that we're loading a single accommodation (for CSS detection)
            define('ZZBP_LOADING_SINGLE_ACCOMMODATION', true);
            
            // Debug logging
            error_log("ZZBP Debug: Looking for accommodation slug: " . $accommodation_slug);
            
            try {
                // Get accommodation data
                $api = new ZZBP_Api();
                $accommodations = $api->get_accommodations();
                error_log("ZZBP Debug: Found " . count($accommodations) . " accommodations");
                
                $current_accommodation = null;
                
                foreach ($accommodations as $acc) {
                    // Use API slug if available, fallback to generated slug
                    $acc_slug = $acc['slug'] ?? $this->generate_slug($acc['name']);
                    error_log("ZZBP Debug: Checking accommodation: " . $acc['name'] . " (slug: " . $acc_slug . ")");
                    
                    if ($acc_slug === $accommodation_slug) {
                        $current_accommodation = $acc;
                        error_log("ZZBP Debug: Found matching accommodation: " . $acc['name']);
                        break;
                    }
                }
                
                if ($current_accommodation) {
                    $this->load_single_accommodation_template($current_accommodation);
                    exit;
                } else {
                    error_log("ZZBP Debug: No accommodation found for slug: " . $accommodation_slug);
                    // Redirect to 404 or accommodations list
                    wp_redirect(home_url('/accommodations/'));
                    exit;
                }
            } catch (Exception $e) {
                error_log("ZZBP Error: " . $e->getMessage());
                wp_redirect(home_url('/accommodations/'));
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
        
        // Force load assets for single accommodation
        if (class_exists('ZZBP_Assets')) {
            $assets = new ZZBP_Assets();
            $assets->force_enqueue_assets();
        }
        
        // Load the working template (with styling and assets)
        $template = ZZBP_PLUGIN_PATH . 'templates/single-accommodation-working.php';
        
        if (file_exists($template)) {
            include $template;
        } else {
            wp_die('Template not found');
        }
    }
    
    /**
     * Generate URL slug from accommodation name
     */
    private function generate_slug($name) {
        return sanitize_title($name);
    }
    
    /**
     * Get accommodation URL
     */
    public function get_accommodation_url($accommodation) {
        $property_info = get_option('zzbp_property_info');
        $base = $property_info['url_structure']['base'] ?? 'accommodations';
        // Use API slug if available, fallback to generated slug
        $slug = $accommodation['slug'] ?? $this->generate_slug($accommodation['name']);
        
        return home_url("/{$base}/{$slug}/");
    }
    
    /**
     * Force rewrite rules flush (for activation/settings changes)
     */
    public function flush_rewrite_rules() {
        $this->add_rewrite_rules();
        flush_rewrite_rules();
    }
}
