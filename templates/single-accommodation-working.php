<?php
/**
 * Single Accommodation Template - Modulaire Shortcode Versie
 * 
 * Deze template gebruikt de nieuwe modulaire shortcodes voor een
 * professionele, onderhoudsbare en flexibele accommodatie pagina.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ECHTE accommodation data ophalen (niet meer mock!)
global $zzbp_current_accommodation;

// Get API instance om accommodaties op te halen
global $zzbp_api_instance;
if (!isset($zzbp_api_instance) || !$zzbp_api_instance) {
    $zzbp_api_instance = new ZZBP_Api();
}

// Haal echte accommodaties op
$accommodations = $zzbp_api_instance->get_accommodations();
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

// Get API instance voor shortcode calls
global $zzbp_api_instance;
if (!$zzbp_api_instance) {
    $zzbp_api_instance = new ZZBP_Api();
}

// Set page title for WordPress
add_filter('wp_title', function() use ($zzbp_current_accommodation) {
    return esc_html($zzbp_current_accommodation['name']) . ' - ' . get_bloginfo('name');
});

get_header(); 

// Force load assets
wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css');
wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js', [], '4.6.13', true);
wp_enqueue_script('flatpickr-nl', 'https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/nl.js', ['flatpickr-js'], '4.6.13', true);
wp_enqueue_script('lucide-icons', 'https://unpkg.com/lucide@latest/dist/umd/lucide.js', [], null, true);

// Load our CSS
$css_file = plugin_dir_path(ZZBP_PLUGIN_FILE) . 'assets/css/tailwind.css';
if (file_exists($css_file)) {
    wp_enqueue_style('zzbp-main-style', plugin_dir_url(ZZBP_PLUGIN_FILE) . 'assets/css/tailwind.css', [], '10.0.0');
}
?>

<!-- Extra CSS voor grid layout -->
<style>
/* Grid classes voor photo gallery */
.grid {
    display: grid;
}

.grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.gap-4 {
    gap: 1rem;
}

/* Responsive grid */
@media (min-width: 768px) {
    .md\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

/* Sidebar sticky */
.sticky {
    position: sticky;
}

.top-6 {
    top: 1.5rem;
}

/* Flexbox utilities */
.flex {
    display: flex;
}

.flex-col {
    flex-direction: column;
}

.space-y-8 > * + * {
    margin-top: 2rem;
}

.lg\:grid {
    display: grid;
}

.lg\:grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

.lg\:col-span-2 {
    grid-column: span 2 / span 2;
}

.lg\:col-span-1 {
    grid-column: span 1 / span 1;
}

.lg\:gap-8 {
    gap: 2rem;
}

.lg\:items-start {
    align-items: flex-start;
}

.mt-8 {
    margin-top: 2rem;
}

.lg\:mt-0 {
    margin-top: 0;
}

/* Responsive breakpoint */
@media (min-width: 1024px) {
    .lg\:grid {
        display: grid;
    }
    
    .lg\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    
    .lg\:col-span-2 {
        grid-column: span 2 / span 2;
    }
    
    .lg\:col-span-1 {
        grid-column: span 1 / span 1;
    }
    
    .lg\:gap-8 {
        gap: 2rem;
    }
    
    .lg\:items-start {
        align-items: flex-start;
    }
    
    .lg\:mt-0 {
        margin-top: 0;
    }
}

/* Basic Tailwind-like utilities */
.bg-white { background-color: white; }
.rounded-2xl { border-radius: 1rem; }
.p-8 { padding: 2rem; }
.p-6 { padding: 1.5rem; }
.p-4 { padding: 1rem; }
.shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
.border { border-width: 1px; }
.border-gray-100 { border-color: #f3f4f6; }
.mb-8 { margin-bottom: 2rem; }
.mb-6 { margin-bottom: 1.5rem; }
.mb-4 { margin-bottom: 1rem; }
.mb-2 { margin-bottom: 0.5rem; }
.mt-4 { margin-top: 1rem; }
.mt-6 { margin-top: 1.5rem; }

/* Text styles */
.text-2xl { font-size: 1.5rem; line-height: 2rem; }
.text-xl { font-size: 1.25rem; line-height: 1.75rem; }
.text-lg { font-size: 1.125rem; line-height: 1.75rem; }
.text-sm { font-size: 0.875rem; line-height: 1.25rem; }
.font-semibold { font-weight: 600; }
.font-bold { font-weight: 700; }
.font-medium { font-weight: 500; }
.text-gray-900 { color: #111827; }
.text-gray-700 { color: #374151; }
.text-gray-600 { color: #4b5563; }
.text-gray-500 { color: #6b7280; }
.text-green-500 { color: #10b981; }
.text-rose-600 { color: #dc2626; }
.text-rose-700 { color: #b91c1c; }
.leading-relaxed { line-height: 1.625; }
.capitalize { text-transform: capitalize; }
.text-center { text-align: center; }

/* Layout utilities */
.items-center { align-items: center; }
.justify-between { justify-content: space-between; }
.space-y-4 > * + * { margin-top: 1rem; }
.space-y-2 > * + * { margin-top: 0.5rem; }
.gap-3 { gap: 0.75rem; }
.gap-2 { gap: 0.5rem; }

/* Form styles */
.w-full { width: 100%; }
.px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
.py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
.py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
.px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
.border-gray-300 { border-color: #d1d5db; }
.rounded-lg { border-radius: 0.5rem; }
.focus\:ring-2:focus { outline: 2px solid transparent; outline-offset: 2px; box-shadow: 0 0 0 2px #f43f5e; }
.focus\:ring-rose-500:focus { box-shadow: 0 0 0 2px #f43f5e; }
.focus\:border-rose-500:focus { border-color: #f43f5e; }

/* Button styles */
.bg-gradient-to-br { background-image: linear-gradient(to bottom right, var(--tw-gradient-stops)); }
.from-rose-400 { --tw-gradient-from: #fb7185; --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to, rgba(251, 113, 133, 0)); }
.via-rose-500 { --tw-gradient-stops: var(--tw-gradient-from), #f43f5e, var(--tw-gradient-to, rgba(244, 63, 94, 0)); }
.to-rose-600 { --tw-gradient-to: #e11d48; }
.hover\:from-green-600:hover { --tw-gradient-from: #16a34a; }
.hover\:to-green-700:hover { --tw-gradient-to: #15803d; }
.text-white { color: white; }
.py-4 { padding-top: 1rem; padding-bottom: 1rem; }
.rounded-2xl { border-radius: 1rem; }
.transition-all { transition-property: all; transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1); transition-duration: 150ms; }
.duration-200 { transition-duration: 200ms; }
.hover\:-translate-y-0\.5:hover { transform: translateY(-0.125rem); }
.hover\:shadow-xl:hover { box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); }

/* Border styles */
.border-t { border-top-width: 1px; }
.border-gray-200 { border-color: #e5e7eb; }
.pt-4 { padding-top: 1rem; }
.pt-6 { padding-top: 1.5rem; }

/* Hover effects */
.hover\:text-rose-700:hover { color: #b91c1c; }
.cursor-pointer { cursor: pointer; }
</style>

<!-- ZeeZicht Booking Pro - Single Accommodation (Modulaire Architectuur) -->
<div class="zzbp-wrapper">
    <div id="zzbp-booking-app" class="min-h-screen bg-gray-50" style="max-width: 1920px; margin: 0 auto; padding: 0 3rem; background: #f8fafc;">

        <?php echo do_shortcode('[zzbp_hero_image]'); ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <?php echo do_shortcode('[zzbp_property_header]'); ?>
                <?php echo do_shortcode('[zzbp_description]'); ?>
                <?php echo do_shortcode('[zzbp_amenities]'); ?>
                <?php echo do_shortcode('[zzbp_photo_gallery]'); ?>
                <?php echo do_shortcode('[zzbp_availability_calendar]'); ?>
            </div>
            
            <div>
                <?php echo do_shortcode('[zzbp_booking_sidebar]'); ?>
            </div>
        </div>

    </div>
</div>

<!-- Booking Modal - Direct Include -->
<?php
// Include modal directly to avoid shortcode escaping issues
global $zzbp_current_accommodation;
$accommodation = $zzbp_current_accommodation;
if ($accommodation):
    // Load the booking modal
    include ZZBP_PLUGIN_PATH . 'templates/parts/booking-modal.php';
endif;
?>

<?php get_footer(); ?>
