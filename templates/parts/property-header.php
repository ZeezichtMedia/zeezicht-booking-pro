<?php
/**
 * Template Part: Property Header
 * 
 * CLEAN, REUSABLE, NO INLINE STYLES
 */

global $zzbp_current_accommodation;
$accommodation = $zzbp_current_accommodation;

if (!$accommodation) {
    return;
}
?>

<div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 mb-8">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-6">
        <div class="flex-1">
            <h1 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-2">
                <?php echo esc_html($accommodation['name'] ?? 'Zeezicht Suite'); ?>
            </h1>
            <p class="text-lg text-gray-600 mb-4">
                <?php echo esc_html($accommodation['subtitle'] ?? 'Luxe accommodatie met adembenemend zeezicht'); ?>
            </p>
            <div class="flex items-center space-x-6 text-gray-700">
                <span class="flex items-center">
                    <i data-lucide="map-pin" class="w-5 h-5 mr-2"></i>
                    <?php echo esc_html($accommodation['location'] ?? 'Zeeland, Nederland'); ?>
                </span>
                <span class="flex items-center">
                    <i data-lucide="users" class="w-5 h-5 mr-2"></i>
                    <?php echo esc_html($accommodation['max_guests'] ?? '4'); ?> gasten
                </span>
                <span class="flex items-center">
                    <i data-lucide="bed" class="w-5 h-5 mr-2"></i>
                    <?php echo esc_html($accommodation['bedrooms'] ?? '2'); ?> slaapkamers
                </span>
            </div>
        </div>
        
        <div class="mt-6 lg:mt-0 lg:ml-8">
            <div class="flex items-center space-x-4">
                <div class="text-right">
                    <div class="text-3xl font-bold text-gray-900">
                        €<?php echo number_format($accommodation['base_price'] ?? 150, 0); ?>
                    </div>
                    <div class="text-gray-600">per nacht</div>
                </div>
                <div class="flex items-center space-x-1">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <i data-lucide="star" class="w-4 h-4 <?php echo $i <= ($accommodation['rating'] ?? 5) ? 'text-yellow-400 fill-current' : 'text-gray-300'; ?>"></i>
                    <?php endfor; ?>
                    <span class="ml-2 text-sm text-gray-600">(<?php echo esc_html($accommodation['reviews_count'] ?? '24'); ?> reviews)</span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="border-t border-gray-200 pt-6">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <i data-lucide="wifi" class="w-6 h-6 mx-auto mb-2 text-green-600"></i>
                <div class="text-sm font-medium">Gratis WiFi</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <i data-lucide="car" class="w-6 h-6 mx-auto mb-2 text-blue-600"></i>
                <div class="text-sm font-medium">Parkeren</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <i data-lucide="waves" class="w-6 h-6 mx-auto mb-2 text-cyan-600"></i>
                <div class="text-sm font-medium">Zeezicht</div>
            </div>
            <div class="text-center p-4 bg-gray-50 rounded-lg">
                <i data-lucide="coffee" class="w-6 h-6 mx-auto mb-2 text-amber-600"></i>
                <div class="text-sm font-medium">Keuken</div>
            </div>
        </div>
    </div>
</div>
