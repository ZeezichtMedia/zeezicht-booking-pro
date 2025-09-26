<?php
/**
 * Accommodation List Template
 * 
 * Available variables:
 * $atts - Shortcode attributes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get accommodations from API
$api = new ZZBP_Api();
$accommodations = $api->get_accommodations();

if (empty($accommodations)) {
    echo '<div class="zzbp-message error">No accommodations available at the moment.</div>';
    return;
}

$layout = $atts['layout'] ?? 'grid';
$show_pricing = ($atts['show_pricing'] ?? 'true') === 'true';
$show_amenities = ($atts['show_amenities'] ?? 'true') === 'true';
?>

<div class="zzbp-accommodation-list layout-<?php echo esc_attr($layout); ?>">
    <?php foreach ($accommodations as $accommodation): ?>
    <div class="zzbp-accommodation-item" data-id="<?php echo esc_attr($accommodation['id']); ?>">
        
        <!-- Accommodation Header -->
        <div class="accommodation-header">
            <h3 class="accommodation-title">
                <a href="<?php echo esc_url(home_url('/' . get_option('zzbp_accommodations_page_slug', 'accommodaties') . '/' . $accommodation['slug'])); ?>">
                    <?php echo esc_html($accommodation['name']); ?>
                </a>
            </h3>
            <span class="accommodation-type"><?php echo esc_html(ucfirst(str_replace('-', ' ', $accommodation['type']))); ?></span>
        </div>

        <!-- Accommodation Image -->
        <?php if (!empty($accommodation['photos']) || !empty($accommodation['primary_image'])): ?>
        <div class="accommodation-image">
            <?php 
            // Use primary_image first, then fallback to first photo
            $image_url = $accommodation['primary_image'] ?? $accommodation['photos'][0] ?? '';
            
            // Fix URL if it's relative
            if ($image_url && strpos($image_url, 'http') !== 0) {
                if (strpos($image_url, '/uploads/') === 0) {
                    $image_url = 'http://localhost:2121' . $image_url;
                }
            }
            ?>
            <img src="<?php echo esc_url($image_url); ?>" 
                 alt="<?php echo esc_attr($accommodation['name']); ?>"
                 loading="lazy"
                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            
            <!-- Fallback if image fails -->
            <div class="accommodation-image-fallback" style="display: none; width: 100%; height: 200px; background: linear-gradient(135deg, #ff385c 0%, #e31c5f 100%); border-radius: 8px; color: white; align-items: center; justify-content: center; text-align: center;">
                <div>
                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">🏨</div>
                    <div style="font-weight: 600;"><?php echo esc_html($accommodation['name']); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Accommodation Details -->
        <div class="accommodation-details">
            <?php if ($accommodation['description']): ?>
            <p class="accommodation-description">
                <?php echo esc_html(wp_trim_words($accommodation['description'], 25)); ?>
            </p>
            <?php endif; ?>

            <div class="accommodation-specs">
                <div class="spec-item">
                    <span class="spec-label">👥 Max gasten:</span>
                    <span class="spec-value"><?php echo esc_html($accommodation['max_guests']); ?></span>
                </div>
                
                <?php if ($accommodation['surface_area']): ?>
                <div class="spec-item">
                    <span class="spec-label">📐 Oppervlakte:</span>
                    <span class="spec-value"><?php echo esc_html($accommodation['surface_area']); ?> m²</span>
                </div>
                <?php endif; ?>
                
                <?php if ($show_pricing): ?>
                <div class="spec-item price">
                    <span class="spec-label">💰 Vanaf:</span>
                    <span class="spec-value">€<?php echo number_format($accommodation['base_price'], 2); ?> per nacht</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Amenities -->
        <?php if ($show_amenities && !empty($accommodation['amenities'])): ?>
        <div class="accommodation-amenities">
            <?php foreach (array_slice($accommodation['amenities'], 0, 4) as $amenity): ?>
                <span class="amenity-tag">✓ <?php echo esc_html($amenity); ?></span>
            <?php endforeach; ?>
            <?php if (count($accommodation['amenities']) > 4): ?>
                <span class="amenity-more">+<?php echo count($accommodation['amenities']) - 4; ?> meer</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div class="accommodation-actions">
            <a href="<?php echo esc_url(home_url('/' . get_option('zzbp_booking_page_slug', 'reserveren') . '/')); ?>" 
               class="zzbp-btn zzbp-btn-primary"
               onclick="selectAccommodation('<?php echo esc_js($accommodation['id']); ?>', '<?php echo esc_js($accommodation['name']); ?>')">
                📅 Reserveren
            </a>
            <a href="<?php echo esc_url(home_url('/' . get_option('zzbp_accommodations_page_slug', 'accommodaties') . '/' . $accommodation['slug'])); ?>" 
               class="zzbp-btn zzbp-btn-secondary">
                👁️ Details
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<style>
/* Accommodation List Styles */
.zzbp-accommodation-list {
    display: grid;
    gap: 30px;
    margin: 20px 0;
}

.zzbp-accommodation-list.layout-grid {
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
}

.zzbp-accommodation-list.layout-list {
    grid-template-columns: 1fr;
}

.zzbp-accommodation-list.layout-cards {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}

.zzbp-accommodation-item {
    background: #ffffff;
    border: 1px solid #e1e5e9;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.zzbp-accommodation-item:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
    transform: translateY(-2px);
}

.accommodation-header {
    padding: 20px 20px 10px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
}

.accommodation-title {
    margin: 0;
    font-size: 22px;
    font-weight: 600;
}

.accommodation-title a {
    color: #2c3e50;
    text-decoration: none;
}

.accommodation-title a:hover {
    color: #3498db;
}

.accommodation-type {
    background: #3498db;
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    text-transform: uppercase;
    font-weight: 600;
}

.accommodation-image {
    width: 100%;
    height: 200px;
    overflow: hidden;
}

.accommodation-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.accommodation-details {
    padding: 20px;
}

.accommodation-description {
    color: #666;
    line-height: 1.6;
    margin-bottom: 15px;
}

.accommodation-specs {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 15px;
}

.spec-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.spec-label {
    color: #666;
    font-size: 14px;
}

.spec-value {
    font-weight: 600;
    color: #2c3e50;
}

.spec-item.price .spec-value {
    color: #27ae60;
    font-size: 18px;
}

.accommodation-amenities {
    padding: 0 20px 15px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.amenity-tag {
    background: #ecf0f1;
    color: #2c3e50;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
}

.amenity-more {
    background: #bdc3c7;
    color: #2c3e50;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-style: italic;
}

.accommodation-actions {
    padding: 20px;
    border-top: 1px solid #ecf0f1;
    display: flex;
    gap: 10px;
}

.zzbp-btn {
    flex: 1;
    padding: 12px 20px;
    border: none;
    border-radius: 6px;
    text-decoration: none;
    text-align: center;
    font-weight: 600;
    transition: all 0.3s ease;
    cursor: pointer;
}

.zzbp-btn-primary {
    background: #3498db;
    color: white;
}

.zzbp-btn-primary:hover {
    background: #2980b9;
    color: white;
}

.zzbp-btn-secondary {
    background: #ecf0f1;
    color: #2c3e50;
}

.zzbp-btn-secondary:hover {
    background: #bdc3c7;
    color: #2c3e50;
}

.zzbp-message {
    padding: 15px 20px;
    border-radius: 6px;
    margin: 20px 0;
}

.zzbp-message.error {
    background: #fee;
    color: #c33;
    border: 1px solid #fcc;
}

/* Responsive Design */
@media (max-width: 768px) {
    .zzbp-accommodation-list {
        grid-template-columns: 1fr !important;
        gap: 20px;
    }
    
    .accommodation-header {
        flex-direction: column;
        gap: 10px;
        align-items: flex-start;
    }
    
    .accommodation-actions {
        flex-direction: column;
    }
}
</style>

<script>
/**
 * Select accommodation and store in localStorage
 */
function selectAccommodation(accommodationId, accommodationName) {
    // Store selected accommodation in localStorage
    const selectedAccommodation = {
        id: accommodationId,
        name: accommodationName,
        selected_at: new Date().toISOString()
    };
    
    localStorage.setItem('zzbp_selected_accommodation', JSON.stringify(selectedAccommodation));
    
    // Optional: Show confirmation
    console.log('Selected accommodation:', accommodationName);
    
    // Continue with the link (return true allows the link to proceed)
    return true;
}

/**
 * Get selected accommodation from localStorage
 */
function getSelectedAccommodation() {
    const stored = localStorage.getItem('zzbp_selected_accommodation');
    if (stored) {
        try {
            return JSON.parse(stored);
        } catch (e) {
            console.error('Error parsing stored accommodation:', e);
            localStorage.removeItem('zzbp_selected_accommodation');
        }
    }
    return null;
}

/**
 * Clear selected accommodation
 */
function clearSelectedAccommodation() {
    localStorage.removeItem('zzbp_selected_accommodation');
}
</script>
