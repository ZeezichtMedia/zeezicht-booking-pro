<?php
/**
 * Template Part: Availability Calendar - CLEAN VERSION
 * 
 * Pure HTML structure, all CSS/JS moved to external files
 */

global $zzbp_current_accommodation;
$accommodation = $zzbp_current_accommodation;

if (!$accommodation) {
    return;
}
?>

<div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 mb-8">
    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Beschikbaarheid</h2>
    
    <!-- Calendar Container -->
    <div class="accommodation-calendar">
        <div id="accommodation-calendar-<?php echo esc_attr($accommodation['id'] ?? 'demo'); ?>" 
             data-accommodation-name="<?php echo esc_attr($accommodation['name'] ?? 'Demo'); ?>"></div>
    </div>
</div>
