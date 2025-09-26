<?php
/**
 * Template Part: Amenities - SIMPLE VERSION LIKE OLD
 */

global $zzbp_current_accommodation;
$accommodation = $zzbp_current_accommodation;

if (!$accommodation) {
    return;
}

$amenities = $accommodation['amenities'] ?? ['wifi', 'parking', 'kitchen', 'tv', 'balcony', 'airco'];
?>

<div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 mb-8">
    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Voorzieningen</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <?php foreach ($amenities as $amenity): ?>
        <div class="flex items-center gap-3">
            <span class="text-green-500">✓</span>
            <span class="text-gray-700 capitalize"><?php echo esc_html(str_replace('_', ' ', $amenity)); ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
