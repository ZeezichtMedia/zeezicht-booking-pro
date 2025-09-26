<?php
/**
 * Admin Settings Template
 * 
 * Available variables:
 * $connection_status - boolean or null
 * $property_info - array or null
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$api_key = get_option('zzbp_api_key', '');
$dashboard_url = get_option('zzbp_dashboard_url', 'http://localhost:2121');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if ($connection_status === true && $property_info): ?>
        <div class="notice notice-success">
            <p><strong>✅ Connected!</strong> Successfully connected to <strong><?php echo esc_html($property_info['name']); ?></strong></p>
            <p>Business Type: <strong><?php echo esc_html(ucfirst($property_info['business_type'])); ?></strong> | 
               Domain: <strong><?php echo esc_html($property_info['domain']); ?></strong></p>
        </div>
    <?php elseif ($connection_status === false): ?>
        <div class="notice notice-error">
            <p><strong>❌ Connection Failed!</strong> Please check your API key and dashboard URL.</p>
        </div>
    <?php endif; ?>

    <div class="zzbp-admin-container" style="max-width: 800px;">
        
        <!-- Connection Setup -->
        <div class="postbox" style="margin-top: 20px;">
            <div class="postbox-header">
                <h2 class="hndle">🔗 Dashboard Connection</h2>
            </div>
            <div class="inside">
                <form method="post" action="">
                    <?php wp_nonce_field('zzbp_settings_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="zzbp_dashboard_url">Dashboard URL</label>
                            </th>
                            <td>
                                <input type="url" 
                                       id="zzbp_dashboard_url" 
                                       name="zzbp_dashboard_url" 
                                       value="<?php echo esc_attr($dashboard_url); ?>" 
                                       class="regular-text" 
                                       placeholder="http://localhost:2121" />
                                <p class="description">The URL where your booking dashboard is running</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="zzbp_api_key">API Key</label>
                            </th>
                            <td>
                                <input type="text" 
                                       id="zzbp_api_key" 
                                       name="zzbp_api_key" 
                                       value="<?php echo esc_attr($api_key); ?>" 
                                       class="regular-text" 
                                       placeholder="zzbp_live_..." />
                                <p class="description">Get your API key from the dashboard settings</p>
                            </td>
                        </tr>
                    </table>
                    
                    <?php submit_button('Save Settings', 'primary', 'zzbp_save_settings'); ?>
                </form>
            </div>
        </div>

        <?php if ($property_info): ?>
        <!-- Property Information -->
        <div class="postbox" style="margin-top: 20px;">
            <div class="postbox-header">
                <h2 class="hndle">🏨 Property Information</h2>
            </div>
            <div class="inside">
                <table class="widefat">
                    <tbody>
                        <tr>
                            <td><strong>Property Name:</strong></td>
                            <td><?php echo esc_html($property_info['settings']['business']['name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Business Type:</strong></td>
                            <td><?php echo esc_html(ucfirst($property_info['business_type'])); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Contact Email:</strong></td>
                            <td><?php echo esc_html($property_info['settings']['business']['email']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Website URL:</strong></td>
                            <td><?php echo esc_html($property_info['settings']['business']['website_url']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>URL Structure:</strong></td>
                            <td>
                                <code>/<?php echo esc_html($property_info['url_structure']['base']); ?>/</code> (accommodations)<br>
                                <code>/<?php echo esc_html($property_info['url_structure']['booking']); ?>/</code> (booking)
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Refresh Configuration Button -->
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #ddd;">
                    <p><strong>Configuration Changed?</strong> If you changed the business type in your dashboard, click below to update the URL structure:</p>
                    <form method="post" style="display: inline; margin-right: 10px;">
                        <?php wp_nonce_field('zzbp_settings_nonce'); ?>
                        <button type="submit" name="zzbp_refresh_config" class="button button-primary">
                            🔄 Refresh Configuration
                        </button>
                    </form>
                    
                    <form method="post" style="display: inline;">
                        <?php wp_nonce_field('zzbp_settings_nonce'); ?>
                        <button type="submit" name="zzbp_force_clean_pages" class="button button-secondary" onclick="return confirm('This will delete all duplicate pages with plugin shortcodes. Are you sure?')">
                            🗑️ Force Clean Duplicate Pages
                        </button>
                    </form>
                    <p class="description" style="margin-top: 5px;">Use "Force Clean" if you still see duplicate pages after refresh.</p>
                </div>
            </div>
        </div>

        <!-- Auto-Created Pages -->
        <div class="postbox" style="margin-top: 20px;">
            <div class="postbox-header">
                <h2 class="hndle">📄 Auto-Created Pages</h2>
            </div>
            <div class="inside">
                <?php
                $accommodations_page_id = get_option('zzbp_accommodations_page_id');
                $booking_page_id = get_option('zzbp_booking_page_id');
                
                // Handle force create pages
                if (isset($_POST['zzbp_force_create_pages'])) {
                    $plugin = new ZeeZichtBookingPro();
                    if ($plugin->force_create_pages()) {
                        echo '<div class="notice notice-success inline"><p>✅ Pages created/updated successfully!</p></div>';
                        // Refresh the page IDs
                        $accommodations_page_id = get_option('zzbp_accommodations_page_id');
                        $booking_page_id = get_option('zzbp_booking_page_id');
                    } else {
                        echo '<div class="notice notice-error inline"><p>❌ Failed to create pages. Make sure you have a valid API connection.</p></div>';
                    }
                }
                ?>
                
                <p>The following pages should be automatically created for your booking system:</p>
                
                <ul>
                    <?php if ($accommodations_page_id): ?>
                    <li>
                        ✅ <strong><?php echo esc_html($property_info['settings']['booking']['accommodations_title']); ?></strong>
                        - <a href="<?php echo esc_url(get_permalink($accommodations_page_id)); ?>" target="_blank">View Page</a>
                        | <a href="<?php echo esc_url(get_edit_post_link($accommodations_page_id)); ?>">Edit</a>
                    </li>
                    <?php else: ?>
                    <li>❌ <strong>Accommodations page</strong> - Not created yet</li>
                    <?php endif; ?>
                    
                    <?php if ($booking_page_id): ?>
                    <li>
                        ✅ <strong><?php echo esc_html($property_info['settings']['booking']['page_title']); ?></strong>
                        - <a href="<?php echo esc_url(get_permalink($booking_page_id)); ?>" target="_blank">View Page</a>
                        | <a href="<?php echo esc_url(get_edit_post_link($booking_page_id)); ?>">Edit</a>
                    </li>
                    <?php else: ?>
                    <li>❌ <strong>Booking page</strong> - Not created yet</li>
                    <?php endif; ?>
                </ul>
                
                <?php if (!$accommodations_page_id || !$booking_page_id): ?>
                <form method="post" style="margin-top: 15px;">
                    <?php wp_nonce_field('zzbp_settings_nonce'); ?>
                    <button type="submit" name="zzbp_force_create_pages" class="button button-secondary">
                        🔧 Create/Update Pages Now
                    </button>
                </form>
                <?php endif; ?>
                
            </div>
        </div>
        <?php endif; ?>

        <!-- Debug Information -->
        <div class="postbox" style="margin-top: 20px;">
            <div class="postbox-header">
                <h2 class="hndle">🔧 Debug Information</h2>
            </div>
            <div class="inside">
                <p><strong>Plugin Version:</strong> <?php echo ZZBP_VERSION; ?></p>
                <p><strong>API Base:</strong> <code><?php echo ZZBP_API_BASE; ?></code></p>
                <p><strong>Current API Key:</strong> <code><?php echo $api_key ? substr($api_key, 0, 8) . '...' : 'Not set'; ?></code></p>
                
                <?php if ($property_info): ?>
                <details style="margin-top: 10px;">
                    <summary style="cursor: pointer; font-weight: bold;">View Raw Property Data</summary>
                    <pre style="background: #f1f1f1; padding: 10px; margin-top: 10px; overflow: auto; max-height: 300px; font-size: 11px;"><?php echo esc_html(json_encode($property_info, JSON_PRETTY_PRINT)); ?></pre>
                </details>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.zzbp-admin-container .postbox {
    border: 1px solid #c3c4c7;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.zzbp-admin-container .postbox-header {
    border-bottom: 1px solid #c3c4c7;
    background: #f6f7f7;
}

.zzbp-admin-container .postbox-header h2 {
    font-size: 14px;
    padding: 8px 12px;
    margin: 0;
    line-height: 1.4;
}

.zzbp-admin-container .inside {
    padding: 12px;
}

.zzbp-admin-container .notice.inline {
    margin: 5px 0 15px 0;
    padding: 5px 10px;
}

.zzbp-admin-container code {
    background: #f1f1f1;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: Consolas, Monaco, monospace;
}
</style>
