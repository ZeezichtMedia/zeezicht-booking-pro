<?php
/**
 * Admin Accommodations Template
 * 
 * Available variables:
 * $accommodations - array of accommodations from API
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php if (empty($accommodations)): ?>
        <div class="notice notice-warning">
            <p><strong>⚠️ No accommodations found.</strong></p>
            <p>Make sure your API connection is working and you have accommodations configured in your dashboard.</p>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=zee-zicht-booking-pro')); ?>" class="button">Check Settings</a></p>
        </div>
    <?php else: ?>
        <div class="notice notice-info">
            <p><strong>ℹ️ Data synced from dashboard.</strong> Found <?php echo count($accommodations); ?> accommodation(s).</p>
        </div>
    <?php endif; ?>

    <div class="zzbp-accommodations-container">
        
        <?php if (!empty($accommodations)): ?>
        <div class="zzbp-accommodations-grid">
            <?php foreach ($accommodations as $accommodation): ?>
            <div class="zzbp-accommodation-card">
                <div class="accommodation-header">
                    <h3><?php echo esc_html($accommodation['name']); ?></h3>
                    <span class="accommodation-type"><?php echo esc_html(ucfirst(str_replace('-', ' ', $accommodation['type']))); ?></span>
                </div>
                
                <div class="accommodation-details">
                    <div class="detail-row">
                        <span class="label">Max Guests:</span>
                        <span class="value"><?php echo esc_html($accommodation['max_guests']); ?> persons</span>
                    </div>
                    
                    <?php if ($accommodation['surface_area']): ?>
                    <div class="detail-row">
                        <span class="label">Surface Area:</span>
                        <span class="value"><?php echo esc_html($accommodation['surface_area']); ?> m²</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-row">
                        <span class="label">Base Price:</span>
                        <span class="value price">€<?php echo number_format($accommodation['base_price'], 2); ?> per night</span>
                    </div>
                </div>
                
                <?php if (!empty($accommodation['description'])): ?>
                <div class="accommodation-description">
                    <p><?php echo esc_html(wp_trim_words($accommodation['description'], 20)); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($accommodation['amenities'])): ?>
                <div class="accommodation-amenities">
                    <strong>Amenities:</strong>
                    <div class="amenities-list">
                        <?php foreach ($accommodation['amenities'] as $amenity): ?>
                            <span class="amenity-tag"><?php echo esc_html($amenity); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Pricing Options -->
                <?php 
                $recurring_options = $accommodation['pricing_options']['recurring'] ?? [];
                $onetime_options = $accommodation['pricing_options']['one_time'] ?? [];
                ?>
                
                <?php if (!empty($recurring_options) || !empty($onetime_options)): ?>
                <div class="pricing-options">
                    <h4>Pricing Options</h4>
                    
                    <?php if (!empty($recurring_options)): ?>
                    <div class="options-section">
                        <h5>Per Night Options (<?php echo count($recurring_options); ?>)</h5>
                        <ul class="options-list">
                            <?php foreach (array_slice($recurring_options, 0, 3) as $option): ?>
                            <li>
                                <strong><?php echo esc_html($option['name']); ?></strong>
                                - €<?php echo number_format($option['price_per_night'], 2); ?>
                                <span class="unit"><?php echo esc_html($option['unit']); ?></span>
                            </li>
                            <?php endforeach; ?>
                            <?php if (count($recurring_options) > 3): ?>
                            <li class="more-options">... and <?php echo count($recurring_options) - 3; ?> more</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($onetime_options)): ?>
                    <div class="options-section">
                        <h5>One-time Options (<?php echo count($onetime_options); ?>)</h5>
                        <ul class="options-list">
                            <?php foreach (array_slice($onetime_options, 0, 3) as $option): ?>
                            <li>
                                <strong><?php echo esc_html($option['name']); ?></strong>
                                - €<?php echo number_format($option['price_per_stay'], 2); ?>
                                <span class="unit"><?php echo esc_html($option['unit']); ?></span>
                            </li>
                            <?php endforeach; ?>
                            <?php if (count($onetime_options) > 3): ?>
                            <li class="more-options">... and <?php echo count($onetime_options) - 3; ?> more</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <div class="accommodation-actions">
                    <button type="button" class="button button-secondary" onclick="copyShortcode('<?php echo esc_js($accommodation['id']); ?>')">
                        📋 Copy Shortcode
                    </button>
                    <a href="#" class="button button-primary" target="_blank">
                        👁️ Preview
                    </a>
                </div>
                
                <!-- Hidden shortcode for copying -->
                <input type="hidden" class="shortcode-input" data-id="<?php echo esc_attr($accommodation['id']); ?>" 
                       value='[zzbp_booking_form accommodation_id="<?php echo esc_attr($accommodation['id']); ?>"]'>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Bulk Actions -->
        <div class="zzbp-bulk-actions" style="margin-top: 30px;">
            <div class="postbox">
                <div class="postbox-header">
                    <h2 class="hndle">🔄 Sync & Actions</h2>
                </div>
                <div class="inside">
                    <p>Last synced: <strong><?php echo date('Y-m-d H:i:s'); ?></strong></p>
                    <p>
                        <button type="button" class="button button-secondary" onclick="location.reload();">
                            🔄 Refresh Data
                        </button>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=zee-zicht-booking-pro')); ?>" class="button">
                            ⚙️ Settings
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <?php endif; ?>
    </div>
</div>

<style>
.zzbp-accommodations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.zzbp-accommodation-card {
    border: 1px solid #c3c4c7;
    border-radius: 8px;
    padding: 20px;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.accommodation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e0e0e0;
}

.accommodation-header h3 {
    margin: 0;
    color: #1d2327;
    font-size: 18px;
}

.accommodation-type {
    background: #2271b1;
    color: white;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    text-transform: uppercase;
}

.accommodation-details {
    margin-bottom: 15px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.detail-row .label {
    font-weight: 600;
    color: #646970;
}

.detail-row .value {
    color: #1d2327;
}

.detail-row .value.price {
    color: #00a32a;
    font-weight: 600;
}

.accommodation-description {
    margin-bottom: 15px;
    padding: 10px;
    background: #f6f7f7;
    border-radius: 4px;
}

.accommodation-description p {
    margin: 0;
    color: #646970;
    font-style: italic;
}

.accommodation-amenities {
    margin-bottom: 15px;
}

.amenities-list {
    margin-top: 8px;
}

.amenity-tag {
    display: inline-block;
    background: #f0f6fc;
    color: #0073aa;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
    margin-right: 5px;
    margin-bottom: 3px;
}

.pricing-options {
    margin-bottom: 20px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 6px;
}

.pricing-options h4 {
    margin: 0 0 10px 0;
    color: #1d2327;
    font-size: 14px;
}

.options-section {
    margin-bottom: 15px;
}

.options-section:last-child {
    margin-bottom: 0;
}

.options-section h5 {
    margin: 0 0 8px 0;
    color: #646970;
    font-size: 13px;
    font-weight: 600;
}

.options-list {
    margin: 0;
    padding-left: 15px;
}

.options-list li {
    margin-bottom: 4px;
    font-size: 12px;
}

.options-list .unit {
    color: #646970;
    font-style: italic;
}

.options-list .more-options {
    color: #646970;
    font-style: italic;
}

.accommodation-actions {
    display: flex;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid #e0e0e0;
}

.accommodation-actions .button {
    flex: 1;
    text-align: center;
    justify-content: center;
}

@media (max-width: 768px) {
    .zzbp-accommodations-grid {
        grid-template-columns: 1fr;
    }
    
    .accommodation-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .accommodation-actions {
        flex-direction: column;
    }
}
</style>

<script>
function copyShortcode(accommodationId) {
    const input = document.querySelector('.shortcode-input[data-id="' + accommodationId + '"]');
    if (input) {
        // Create a temporary textarea to copy the text
        const textarea = document.createElement('textarea');
        textarea.value = input.value;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        
        // Show feedback
        alert('Shortcode copied to clipboard!\n\n' + input.value);
    }
}
</script>
