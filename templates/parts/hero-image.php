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
            src="<?php echo esc_url($accommodation['hero_image'] ?? 'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80'); ?>" 
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
