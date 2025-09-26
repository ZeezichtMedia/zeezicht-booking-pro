<?php
/**
 * Debug Test Template - Nu met modulaire shortcodes!
 * WordPress + Tailwind CSS implementation
 */

// ECHTE accommodation data ophalen (niet meer mock!)
global $zzbp_current_accommodation;

// Get plugin instance om accommodaties op te halen
global $zzbp_plugin_instance;
if (!isset($zzbp_plugin_instance) || !$zzbp_plugin_instance) {
    $zzbp_plugin_instance = new ZeeZichtBookingPro();
}

// Haal echte accommodaties op
$accommodations = $zzbp_plugin_instance->get_accommodations();
$zzbp_current_accommodation = null;

// Zoek naar 'zeezicht-suite' of neem de eerste beschikbare
foreach ($accommodations as $acc) {
    if (isset($acc['slug']) && $acc['slug'] === 'zeezicht-suite') {
        $zzbp_current_accommodation = $acc;
        break;
    }
}

// Fallback: neem de eerste accommodatie als zeezicht-suite niet gevonden
if (!$zzbp_current_accommodation && !empty($accommodations)) {
    $zzbp_current_accommodation = $accommodations[0];
}

// Als nog steeds geen data, gebruik fallback
if (!$zzbp_current_accommodation) {
    $zzbp_current_accommodation = [
        'name' => 'Geen accommodatie gevonden',
        'type' => 'apartment',
        'max_guests' => 4,
        'surface_area' => 85,
        'base_price' => 150,
        'description' => 'Kon geen echte accommodatie data ophalen van de API.',
        'primary_image' => 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80',
        'amenities' => ['wifi', 'parking', 'kitchen'],
        'photos' => []
    ];
}

// Get plugin instance voor shortcode calls
global $zzbp_plugin_instance;
if (!$zzbp_plugin_instance) {
    $zzbp_plugin_instance = new ZeeZichtBookingPro();
}
?>

<!-- ZeeZicht Booking Pro - MODULAIRE SHORTCODE VERSIE -->
<div id="zzbp-booking-app" style="max-width: 1920px; margin: 0 auto; padding: 0 3rem; background: #f8fafc;">

    <h1 style="font-size: 2rem; font-weight: bold; margin-bottom: 1.5rem; color: #374151;">🎯 MODULAIRE SHORTCODE TEST</h1>
    <p style="color: #6b7280; margin-bottom: 2rem;"><strong>Nu met herbruikbare components!</strong></p>

    <?php echo do_shortcode('[zzbp_hero_image]'); ?>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
        <div style="display: flex; flex-direction: column; gap: 2rem;">
            <?php echo do_shortcode('[zzbp_property_header]'); ?>
            <?php echo do_shortcode('[zzbp_description]'); ?>
            <?php echo do_shortcode('[zzbp_amenities]'); ?>
            <?php echo do_shortcode('[zzbp_photo_gallery]'); ?>
        </div>
        
        <div>
            <?php echo do_shortcode('[zzbp_booking_sidebar]'); ?>
        </div>
    </div>

</div>
