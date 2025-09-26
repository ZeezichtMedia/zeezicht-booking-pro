<?php
/**
 * Single Accommodation Template - CLEAN VERSION
 * 
 * PROFESSIONAL BEST PRACTICE:
 * - No data fetching in template
 * - No inline styles/scripts
 * - Pure layout management
 * - Template parts only
 */

// Data is already loaded by routing logic before this template is called
global $zzbp_current_accommodation;

// Safety check
if (!$zzbp_current_accommodation) {
    echo '<div class="container mx-auto px-4 py-8 text-center">';
    echo '<h1 class="text-2xl font-bold text-gray-900 mb-4">Accommodatie niet gevonden</h1>';
    echo '<p class="text-gray-600">De opgevraagde accommodatie kon niet worden geladen.</p>';
    echo '</div>';
    return;
}

get_header(); 
?>

<div class="zzbp-wrapper">
    <div id="zzbp-booking-app" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Hero Section -->
        <?php echo do_shortcode('[zzbp_hero_image]'); ?>

        <div class="lg:grid lg:grid-cols-3 lg:gap-8 lg:items-start mt-8">
            
            <!-- Main Content - Left Column -->
            <div class="lg:col-span-2 space-y-8">
                <?php echo do_shortcode('[zzbp_property_header]'); ?>
                <?php echo do_shortcode('[zzbp_description]'); ?>
                <?php echo do_shortcode('[zzbp_amenities]'); ?>
                <?php echo do_shortcode('[zzbp_photo_gallery]'); ?>
                <?php echo do_shortcode('[zzbp_availability_calendar]'); ?>
            </div>
            
            <!-- Sidebar - Right Column -->
            <div class="lg:col-span-1 mt-8 lg:mt-0">
                <?php echo do_shortcode('[zzbp_booking_sidebar]'); ?>
            </div>
        </div>
    </div>
</div>

<?php 
// Global modal - included once at page level
include ZZBP_PLUGIN_PATH . 'templates/parts/booking-modal.php'; 

get_footer(); 
?>
