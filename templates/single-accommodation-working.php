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
?>
<!-- Booking Modal -->
<div id="booking-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1rem;">
    <div class="bg-white rounded-2xl w-full max-h-[90vh] overflow-y-auto" style="max-width: 56rem;">
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900">Reserveer <?php echo esc_html($accommodation['name']); ?></h2>
                <button onclick="closeBookingModal()" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-2 mt-4">
                <div id="progress-bar" class="bg-rose-500 h-2 rounded-full transition-all duration-500 ease-out" style="width: 25%"></div>
            </div>
        </div>
        
        <!-- Modal Content -->
        <div class="p-6">
            <form id="booking-form">
                
                <!-- Step 1: Date Selection -->
                <div id="step-1" class="booking-step">
                    <div class="text-center space-y-2 mb-6">
                        <h3 class="text-xl font-semibold">Selecteer datums</h3>
                        <p class="text-gray-600">Wanneer wil je verblijven?</p>
                    </div>
                    
                    <!-- Selected Dates Display -->
                    <div id="modal-selected-dates" style="display: none; margin-bottom: 1rem; padding: 0.75rem; background-color: #dbeafe; border-radius: 0.5rem; border: 1px solid #93c5fd;">
                        <div style="font-size: 0.875rem; font-weight: 500; color: #1e3a8a; margin-bottom: 0.25rem;">Geselecteerde periode</div>
                        <div id="modal-period-text" style="color: #1d4ed8; font-size: 0.875rem;"></div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem;">
                            <span style="font-size: 0.75rem; color: #2563eb;"><span id="modal-nights">0</span> nachten</span>
                            <button type="button" id="modal-clear-selection" style="font-size: 0.75rem; color: #3b82f6; background: none; border: none; cursor: pointer;">Wissen</button>
                        </div>
                    </div>
                    
                    <!-- Full Calendar - Direct include -->
                    <div class="modal-calendar" style="margin-bottom: 1rem;">
                        <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 mb-8">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Beschikbaarheid</h2>
                            
                            <!-- Calendar Container -->
                            <div class="accommodation-calendar">
                                <div id="modal-accommodation-calendar-<?php echo esc_attr($accommodation['id'] ?? 'demo'); ?>"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden inputs for form data -->
                    <input type="hidden" id="modal-checkin" name="checkin">
                    <input type="hidden" id="modal-checkout" name="checkout">
                </div>
                
                <!-- Step 2: Guest Selection -->
                <div id="step-2" class="booking-step hidden">
                    <div class="text-center space-y-2 mb-6">
                        <h3 class="text-xl font-semibold">Kies gasten</h3>
                        <p class="text-gray-600">Hoeveel gasten verblijven er?</p>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-4">
                            <div>
                                <p class="font-medium">Volwassenen</p>
                                <p class="text-sm text-gray-500">13 jaar en ouder</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="updateGuestCount('adults', false)" 
                                        class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">
                                    <span class="text-lg">−</span>
                                </button>
                                <span id="adults-count" class="w-8 text-center font-medium">2</span>
                                <button type="button" onclick="updateGuestCount('adults', true)" 
                                        class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">
                                    <span class="text-lg">+</span>
                                </button>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200"></div>
                        
                        <div class="flex items-center justify-between py-4">
                            <div>
                                <p class="font-medium">Kinderen</p>
                                <p class="text-sm text-gray-500">2-12 jaar</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <button type="button" onclick="updateGuestCount('children', false)" 
                                        class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">
                                    <span class="text-lg">−</span>
                                </button>
                                <span id="children-count" class="w-8 text-center font-medium">0</span>
                                <button type="button" onclick="updateGuestCount('children', true)" 
                                        class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">
                                    <span class="text-lg">+</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Step 3: Contact Details -->
                <div id="step-3" class="booking-step hidden">
                    <div class="text-center space-y-2 mb-6">
                        <h3 class="text-xl font-semibold">Jouw gegevens</h3>
                        <p class="text-gray-600">We hebben wat gegevens nodig om je reservering te voltooien</p>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Volledige naam</label>
                            <input type="text" id="guest-name" name="name" required
                                   class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                                   placeholder="Voer je volledige naam in">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">E-mailadres</label>
                            <input type="email" id="guest-email" name="email" required
                                   class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                                   placeholder="Voer je e-mailadres in">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Telefoonnummer</label>
                            <input type="tel" id="guest-phone" name="phone" required
                                   class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                                   placeholder="Voer je telefoonnummer in">
                        </div>
                    </div>
                </div>
                
                <!-- Step 4: Confirmation -->
                <div id="step-4" class="booking-step hidden">
                    <div class="text-center space-y-2 mb-6">
                        <h3 class="text-xl font-semibold">Bevestig reservering</h3>
                        <p class="text-gray-600">Controleer je reserveringsgegevens</p>
                    </div>
                    
                    <div class="bg-gray-50 rounded-lg p-6 space-y-4">
                        <div class="flex justify-between">
                            <span>Datums</span>
                            <span id="confirm-dates" class="font-medium"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Gasten</span>
                            <span id="confirm-guests" class="font-medium"></span>
                        </div>
                        
                        <div class="border-t border-gray-300 pt-4"></div>
                        
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span>€<?php echo number_format($accommodation['base_price'] ?? 150, 0); ?> × <span id="confirm-nights">0</span> nachten</span>
                                <span id="confirm-subtotal">€0</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Schoonmaakkosten</span>
                                <span>€25</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Servicekosten</span>
                                <span id="confirm-service-fee">€0</span>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-300 pt-4"></div>
                        
                        <div class="flex justify-between font-semibold text-lg">
                            <span>Totaal</span>
                            <span id="confirm-total">€25</span>
                        </div>
                    </div>
                </div>
                
            </form>
        </div>
        
        <!-- Modal Footer -->
        <div class="p-6 border-t border-gray-200">
            <div class="flex justify-between">
                <button type="button" id="prev-btn" onclick="prevStep()" 
                        class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span>←</span> Terug
                </button>
                
                <button type="button" id="next-btn" onclick="nextStep()" 
                        class="px-6 py-3 bg-gradient-to-r from-rose-500 to-rose-600 text-white rounded-lg hover:from-rose-600 hover:to-rose-700 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    Volgende <span>→</span>
                </button>
                
                <button type="button" id="submit-btn" onclick="submitBooking()" 
                        class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 hidden">
                    Bevestig Reservering
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal JavaScript -->
<script>
let currentModalStep = 1;
let modalData = {
    adults: 2,
    children: 0,
    basePrice: <?php echo intval($accommodation['base_price'] ?? 150); ?>
};

// Modal functions will be defined later to avoid duplication

function closeBookingModal() {
    const modal = document.getElementById('booking-modal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
    currentModalStep = 1;
    showStep(1);
}

function showStep(step) {
    // Hide all steps
    document.querySelectorAll('.booking-step').forEach(el => el.style.display = 'none');
    
    // Show current step
    document.getElementById('step-' + step).style.display = 'block';
    
    // Update progress bar
    const progress = (step / 4) * 100;
    document.getElementById('progress-bar').style.width = progress + '%';
    
    // Update buttons
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    
    prevBtn.disabled = step === 1;
    
    if (step === 4) {
        nextBtn.style.display = 'none';
        submitBtn.style.display = 'block';
        updateConfirmation();
    } else {
        nextBtn.style.display = 'block';
        submitBtn.style.display = 'none';
    }
}

function nextStep() {
    if (validateCurrentStep()) {
        currentModalStep++;
        showStep(currentModalStep);
    }
}

function prevStep() {
    if (currentModalStep > 1) {
        currentModalStep--;
        showStep(currentModalStep);
    }
}

function validateCurrentStep() {
    if (currentModalStep === 1) {
        const checkIn = document.getElementById('modal-checkin').value;
        const checkOut = document.getElementById('modal-checkout').value;
        if (!checkIn || !checkOut) {
            alert('Selecteer eerst een periode in de kalender');
            return false;
        }
    } else if (currentModalStep === 3) {
        const name = document.getElementById('guest-name').value.trim();
        const email = document.getElementById('guest-email').value.trim();
        const phone = document.getElementById('guest-phone').value.trim();
        
        if (!name || !email || !phone) {
            alert('Vul alle velden in');
            return false;
        }
        
        if (!email.includes('@')) {
            alert('Voer een geldig e-mailadres in');
            return false;
        }
    }
    return true;
}

function updateGuestCount(type, increment) {
    const currentValue = modalData[type];
    let newValue;
    
    if (increment) {
        newValue = Math.min(currentValue + 1, 8);
    } else {
        newValue = Math.max(type === 'adults' ? 1 : 0, currentValue - 1);
    }
    
    modalData[type] = newValue;
    document.getElementById(type + '-count').textContent = newValue;
}

function updateConfirmation() {
    const checkIn = document.getElementById('modal-checkin').value;
    const checkOut = document.getElementById('modal-checkout').value;
    
    if (checkIn && checkOut) {
        const checkInDate = new Date(checkIn);
        const checkOutDate = new Date(checkOut);
        const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
        
        // Format dates for display
        const checkInDisplay = formatDateForDisplay(checkInDate);
        const checkOutDisplay = formatDateForDisplay(checkOutDate);
        
        document.getElementById('confirm-dates').textContent = checkInDisplay + ' - ' + checkOutDisplay;
        document.getElementById('confirm-guests').textContent = 
            modalData.adults + ' volwassenen' + (modalData.children > 0 ? ', ' + modalData.children + ' kinderen' : '');
        document.getElementById('confirm-nights').textContent = nights;
        
        const subtotal = nights * modalData.basePrice;
        const serviceFee = Math.round(subtotal * 0.08);
        const total = subtotal + 25 + serviceFee;
        
        document.getElementById('confirm-subtotal').textContent = '€' + subtotal;
        document.getElementById('confirm-service-fee').textContent = '€' + serviceFee;
        document.getElementById('confirm-total').textContent = '€' + total;
    }
}

function submitBooking() {
    const formData = {
        accommodation: '<?php echo esc_js($accommodation['name']); ?>',
        checkIn: document.getElementById('modal-checkin').value,
        checkOut: document.getElementById('modal-checkout').value,
        adults: modalData.adults,
        children: modalData.children,
        name: document.getElementById('guest-name').value,
        email: document.getElementById('guest-email').value,
        phone: document.getElementById('guest-phone').value
    };
    
    alert('Reservering ingediend!\n\nWe sturen je binnenkort een bevestigingsmail.\n\nGegevens:\n' + 
          'Naam: ' + formData.name + '\n' +
          'E-mail: ' + formData.email + '\n' +
          'Periode: ' + formData.checkIn + ' - ' + formData.checkOut + '\n' +
          'Gasten: ' + formData.adults + ' volwassenen, ' + formData.children + ' kinderen');
    
    closeBookingModal();
}

// Helper functions
function formatDateForDisplay(date) {
    return date.toLocaleDateString('nl-NL');
}

function parseDisplayDate(dateStr) {
    const parts = dateStr.split('-');
    return new Date(parts[2], parts[1] - 1, parts[0]);
}

// Initialize modal
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing booking modal...');
    showStep(1);
    
    // Multiple attempts to initialize calendar
    function tryInitCalendar(attempts = 0) {
        if (window.flatpickr) {
            initModalDatePickers();
        } else if (attempts < 10) {
            setTimeout(() => tryInitCalendar(attempts + 1), 200);
        }
    }
    
    tryInitCalendar();
});

// Modal functions
function openBookingModal() {
    console.log('Opening booking modal...');
    const modal = document.getElementById('booking-modal');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Pre-fill dates from sidebar if available
    const sidebarCheckIn = document.getElementById('check-in-date');
    const sidebarCheckOut = document.getElementById('check-out-date');
    
    if (sidebarCheckIn && sidebarCheckIn.value && sidebarCheckOut && sidebarCheckOut.value) {
        // Set hidden inputs
        const modalCheckIn = document.getElementById('modal-checkin');
        const modalCheckOut = document.getElementById('modal-checkout');
        
        if (modalCheckIn && modalCheckOut) {
            modalCheckIn.value = sidebarCheckIn.value;
            modalCheckOut.value = sidebarCheckOut.value;
        }
        
        // Set calendar dates if calendar is initialized
        if (window.modalCalendarInstance) {
            const checkInDate = new Date(sidebarCheckIn.value);
            const checkOutDate = new Date(sidebarCheckOut.value);
            window.modalCalendarInstance.setDate([checkInDate, checkOutDate], false);
        }
    }
    
    // Ensure calendar is initialized when modal opens
    setTimeout(function() {
        if (!window.modalCalendarInstance && window.flatpickr) {
            console.log('Initializing calendar on modal open...');
            initModalDatePickers();
        }
    }, 100);
}

function initModalDatePickers() {
    const accommodationId = '<?php echo esc_js($accommodation['id'] ?? 'demo'); ?>';
    const calendarContainer = document.getElementById('modal-accommodation-calendar-' + accommodationId);
    
    if (!calendarContainer) {
        console.log('Modal calendar container not found, retrying...');
        // Retry after a short delay
        setTimeout(initModalDatePickers, 200);
        return;
    }
    
    if (calendarContainer.hasAttribute('data-initialized')) {
        console.log('Modal calendar already initialized');
        return;
    }
    
    console.log('Initializing modal calendar...');
    calendarContainer.setAttribute('data-initialized', 'true');
    
    // Mock availability data - same as main calendar
    const availabilityData = {
        unavailable_dates: [
            '2024-12-25', '2024-12-26', '2024-12-31', '2025-01-01',
            '2025-01-15', '2025-01-16', '2025-01-17'
        ]
    };

    calendarContainer.innerHTML = `
        <!-- Selected Dates Display -->
        <div id="modal-selected-dates-display" class="hidden mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
            <div class="text-sm font-medium text-blue-900 mb-1">Geselecteerde periode</div>
            <div id="modal-selected-period-text" class="text-blue-700 text-sm"></div>
            <div class="flex justify-between items-center mt-2">
                <span class="text-xs text-blue-600"><span id="modal-selected-nights">0</span> nachten</span>
                <button id="modal-clear-btn" class="text-xs text-blue-500 hover:text-blue-700">Wissen</button>
            </div>
        </div>

        <!-- Calendar Container -->
        <div id="modal-calendar-flatpickr" class="border border-gray-200 rounded-lg overflow-hidden"></div>

        <!-- Legend -->
        <div class="mt-4 flex flex-wrap gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #22C55E;"></div>
                <span class="text-gray-700 font-medium">Beschikbaar</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #E5C5C5;"></div>
                <span class="text-gray-700 font-medium">Bezet</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded" style="background-color: #3B82F6;"></div>
                <span class="text-gray-700 font-medium">Geselecteerd</span>
            </div>
        </div>
    `;

    // Wait a moment for HTML to be fully rendered
    setTimeout(function() {
        // Initialize Flatpickr
        const flatpickrContainer = calendarContainer.querySelector('#modal-calendar-flatpickr');
        const selectedDisplay = calendarContainer.querySelector('#modal-selected-dates-display');
        const periodText = calendarContainer.querySelector('#modal-selected-period-text');
        const nightsSpan = calendarContainer.querySelector('#modal-selected-nights');
        const clearBtn = calendarContainer.querySelector('#modal-clear-btn');

        if (!flatpickrContainer) {
            console.error('Flatpickr container not found in modal');
            return;
        }

        let selectedDates = [];
        const unavailableDates = availabilityData.unavailable_dates || [];

        // Initialize Flatpickr
        const fp = window.flatpickr(flatpickrContainer, {
            mode: 'range',
            inline: true,
            dateFormat: 'Y-m-d',
            minDate: 'today',
            maxDate: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000),
            disable: unavailableDates.map(dateStr => new Date(dateStr)),
            locale: 'nl',
            showMonths: window.innerWidth >= 768 ? 2 : 1,
            onChange: function(dates) {
                handleModalDateSelection(dates);
            },
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                const date = dayElem.dateObj;
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (date < today) {
                    dayElem.classList.add('past-date');
                }
            }
        });

        // Store calendar instance globally
        window.modalCalendarInstance = fp;
        console.log('Modal calendar initialized successfully');

        // Clear selection button
        clearBtn.addEventListener('click', function() {
            fp.clear();
        });
    }, 50); // Small delay to ensure HTML is rendered

    // Handle date selection in modal
    function handleModalDateSelection(selectedDates) {
        const selectedDisplay = document.getElementById('modal-selected-dates');
        const periodText = document.getElementById('modal-period-text');
        const nightsSpan = document.getElementById('modal-nights');
        const checkInHidden = document.getElementById('modal-checkin');
        const checkOutHidden = document.getElementById('modal-checkout');

        if (selectedDates.length === 0) {
            selectedDisplay.style.display = 'none';
            checkInHidden.value = '';
            checkOutHidden.value = '';
            return;
        }

        if (selectedDates.length === 1) {
            selectedDisplay.style.display = 'block';
            periodText.textContent = `Startdatum: ${formatDateForDisplay(selectedDates[0])}`;
            nightsSpan.textContent = '0';
            checkInHidden.value = formatDateForInput(selectedDates[0]);
            checkOutHidden.value = '';
            return;
        }

        if (selectedDates.length === 2) {
            const [startDate, endDate] = selectedDates;
            const nights = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));

            selectedDisplay.style.display = 'block';
            periodText.textContent = `${formatDateForDisplay(startDate)} - ${formatDateForDisplay(endDate)}`;
            nightsSpan.textContent = nights;
            
            checkInHidden.value = formatDateForInput(startDate);
            checkOutHidden.value = formatDateForInput(endDate);
            
            // Also update sidebar if it exists
            const sidebarCheckIn = document.getElementById('check-in-date');
            const sidebarCheckOut = document.getElementById('check-out-date');
            if (sidebarCheckIn && sidebarCheckOut) {
                sidebarCheckIn.value = formatDateForInput(startDate);
                sidebarCheckOut.value = formatDateForInput(endDate);
                
                // Update sidebar range picker if it exists
                if (window.sidebarRangePicker) {
                    window.sidebarRangePicker.setDate([startDate, endDate], false);
                }
            }
        }
    }

    // Clear selection button
    document.getElementById('modal-clear-selection').addEventListener('click', function() {
        if (window.modalCalendarInstance) {
            window.modalCalendarInstance.clear();
        }
    });

    // Helper function for input format (YYYY-MM-DD)
    function formatDateForInput(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
}

// Make functions globally accessible
window.openBookingModal = openBookingModal;
window.closeBookingModal = closeBookingModal;
window.nextStep = nextStep;
window.prevStep = prevStep;
window.updateGuestCount = updateGuestCount;
window.submitBooking = submitBooking;
</script>

<!-- Modal Styles -->
<style>
.booking-step {
    min-height: 400px;
}

#booking-modal {
    backdrop-filter: blur(4px);
}

/* Modal Calendar Styles - reuse existing styles */
.modal-calendar .accommodation-calendar .flatpickr-calendar.inline {
    box-shadow: none !important;
    border: 0 !important;
    background: transparent !important;
    width: 100% !important;
}

.modal-calendar .accommodation-calendar .flatpickr-months {
    background: #f9fafb !important;
    border-radius: 0.75rem 0.75rem 0 0 !important;
    padding: 1rem !important;
}

.modal-calendar .accommodation-calendar .flatpickr-weekdays {
    background: #f3f4f6 !important;
    padding: 0.5rem 0 !important;
}

/* Modern square days with rounded corners */
.modal-calendar .accommodation-calendar .flatpickr-day {
    width: 40px !important;
    height: 40px !important;
    line-height: 40px !important;
    border-radius: 6px !important;
    margin: 2px !important;
    transition: all 0.2s ease !important;
}

/* Available dates - green background - but NOT if selected */
.modal-calendar .accommodation-calendar .flatpickr-day:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay):not(.selected):not(.startRange):not(.endRange):not(.inRange) {
    background-color: #22C55E !important;
    color: white !important;
    border-color: #16A34A !important;
}

/* Available dates hover - but NOT if selected */
.modal-calendar .accommodation-calendar .flatpickr-day:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay):not(.selected):not(.startRange):not(.endRange):not(.inRange):hover {
    background-color: #16A34A !important;
    color: white !important;
    transform: scale(1.05) !important;
}

/* Disabled dates (unavailable) - dusty rose */
.modal-calendar .accommodation-calendar .flatpickr-day.flatpickr-disabled {
    background-color: #E5C5C5 !important;
    color: #8B5A5A !important;
    opacity: 1 !important;
    cursor: not-allowed !important;
}

/* Past dates - greyed out (higher priority than disabled) */
.modal-calendar .accommodation-calendar .flatpickr-day.past-date {
    background-color: #F3F4F6 !important;
    color: #9CA3AF !important;
    opacity: 0.6 !important;
    cursor: not-allowed !important;
}

/* Selection overrides green - blue for selected dates (highest priority) */
.modal-calendar .accommodation-calendar .flatpickr-day.selected,
.modal-calendar .accommodation-calendar .flatpickr-day.startRange,
.modal-calendar .accommodation-calendar .flatpickr-day.endRange {
    background-color: #3B82F6 !important;
    border-color: #3B82F6 !important;
    color: white !important;
    transform: scale(1.05) !important;
}

/* Selected dates hover - stay blue */
.modal-calendar .accommodation-calendar .flatpickr-day.selected:hover,
.modal-calendar .accommodation-calendar .flatpickr-day.startRange:hover,
.modal-calendar .accommodation-calendar .flatpickr-day.endRange:hover {
    background-color: #2563EB !important;
    border-color: #2563EB !important;
    color: white !important;
    transform: scale(1.05) !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
}

.modal-calendar .accommodation-calendar .flatpickr-day.inRange {
    background-color: #DBEAFE !important;
    border-color: #BFDBFE !important;
    color: #1E40AF !important;
    transform: none !important;
}

/* In-range hover - stay light blue */
.modal-calendar .accommodation-calendar .flatpickr-day.inRange:hover {
    background-color: #BFDBFE !important;
    border-color: #93C5FD !important;
    color: #1E40AF !important;
    transform: scale(1.02) !important;
}

/* Toon volgende maand dagen */
.modal-calendar .accommodation-calendar .flatpickr-day.nextMonthDay,
.modal-calendar .accommodation-calendar .flatpickr-day.prevMonthDay {
    display: block !important;
    visibility: visible !important;
    opacity: 0.4 !important;
    color: #9CA3AF !important;
    background-color: #F9FAFB !important;
}

/* Responsive modal sizing */
@media (max-width: 768px) {
    #booking-modal > div {
        max-width: 95% !important;
        margin: 0.5rem !important;
    }
}

/* Ensure proper styling */
.bg-white { background-color: white; }
.rounded-2xl { border-radius: 1rem; }
.max-w-2xl { max-width: 42rem; }
.w-full { width: 100%; }
.p-6 { padding: 1.5rem; }
.border-b { border-bottom-width: 1px; }
.border-gray-200 { border-color: #e5e7eb; }
.flex { display: flex; }
.justify-between { justify-content: space-between; }
.items-center { align-items: center; }
.text-2xl { font-size: 1.5rem; }
.font-bold { font-weight: 700; }
.text-gray-900 { color: #111827; }
.bg-gray-200 { background-color: #e5e7eb; }
.rounded-full { border-radius: 9999px; }
.h-2 { height: 0.5rem; }
.mt-4 { margin-top: 1rem; }
.bg-rose-500 { background-color: #f43f5e; }
.transition-all { transition-property: all; }
.duration-500 { transition-duration: 500ms; }
.ease-out { transition-timing-function: cubic-bezier(0, 0, 0.2, 1); }
</style>
<?php endif; ?>

<?php get_footer(); ?>
