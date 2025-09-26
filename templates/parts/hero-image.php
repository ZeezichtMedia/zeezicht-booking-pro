<?php
/**
 * Template Part: Hero Image
 * 
 * CLEAN, REUSABLE, NO INLINE STYLES
 */

global $zzbp_current_accommodation;
$accommodation = $zzbp_current_accommodation;

if (!$accommodation) {
    return;
}
?>

<div class="container mx-auto px-4 py-6">
    <div class="relative overflow-hidden rounded-2xl group" style="height: 70vh;">
        <img 
            src="<?php 
                // Try different possible image fields from API
                $hero_image = $accommodation['primary_image'] ?? 
                             $accommodation['hero_image'] ?? 
                             $accommodation['featured_image'] ?? 
                             ($accommodation['photos'][0] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');
                
                // If it's a relative path, make it absolute using the SaaS dashboard URL
                if ($hero_image && !str_starts_with($hero_image, 'http')) {
                    $settings = get_option('zzbp_settings', array());
                    $dashboard_url = rtrim($settings['api_base_url'] ?? 'http://localhost:2121', '/api');
                    $dashboard_url = rtrim($dashboard_url, '/');
                    $hero_image = $dashboard_url . '/' . ltrim($hero_image, '/');
                }
                
                echo esc_url($hero_image); 
            ?>" 
            alt="<?php echo esc_attr($accommodation['name'] ?? 'Accommodation'); ?>"
            class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
        >
        
        <!-- Gradient Overlay -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent"></div>
        
        <!-- Content Overlay -->
        <div class="absolute bottom-0 left-0 right-0 p-8 text-white">
            <div class="max-w-4xl">
                <h1 class="text-4xl md:text-6xl font-bold mb-4 leading-tight">
                    <?php echo esc_html($accommodation['name'] ?? 'Zeezicht Suite'); ?>
                </h1>
                <p class="text-xl md:text-2xl text-gray-200 mb-6">
                    <?php echo esc_html($accommodation['subtitle'] ?? 'Luxe accommodatie met adembenemend zeezicht'); ?>
                </p>
                <div class="flex items-center space-x-6 text-lg">
                    <span class="flex items-center">
                        <i data-lucide="map-pin" class="w-5 h-5 mr-2"></i>
                        <?php echo esc_html($accommodation['location'] ?? 'Zeeland, Nederland'); ?>
                    </span>
                    <span class="flex items-center">
                        <i data-lucide="users" class="w-5 h-5 mr-2"></i>
                        <?php echo esc_html($accommodation['max_guests'] ?? '4'); ?> gasten
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
