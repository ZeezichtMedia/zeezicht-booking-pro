<?php
/**
 * Modern Step-by-Step Booking Form Template
 * Inspired by competitor's excellent UX design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$accommodation_id = $atts['accommodation_id'] ?? $_GET['accommodation'] ?? '';
$show_accommodation_selector = ($atts['show_accommodation_selector'] ?? 'true') === 'true';

// Get accommodations for selector
$api = new ZZBP_Api();
$accommodations = $api->get_accommodations();

if (empty($accommodations)) {
    echo '<div class="zzbp-message error">Booking system is temporarily unavailable. Please try again later.</div>';
    return;
}

// Get property info for business details
$property_info = get_option('zzbp_property_info', []);
$business_settings = $property_info['settings']['business'] ?? [];

// Pre-select accommodation if specified
$selected_accommodation = null;
if ($accommodation_id) {
    foreach ($accommodations as $acc) {
        if ($acc['id'] === $accommodation_id) {
            $selected_accommodation = $acc;
            break;
        }
    }
}
?>

<!-- ZeeZicht Booking Pro - Encapsulated Styles -->
<div id="zzbp-booking-app" class="bg-gray-50 py-8 px-4 min-h-screen">
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 xl:grid-cols-3 lg:grid-cols-2 gap-8">
        
        <!-- Main Form Area -->
        <div class="xl:col-span-2 lg:col-span-1 bg-white rounded-xl shadow-lg border border-gray-200 overflow-hidden order-1">
            <div class="px-8 py-6 border-b border-gray-100">
                <h1 class="text-3xl font-semibold text-gray-900 mb-2">Prijsopgave</h1>
                <div class="text-gray-600" id="booking-dates-display">
                    Reservering van <strong id="display-checkin" class="text-gray-900">datum selecteren</strong> tot <strong id="display-checkout" class="text-gray-900">datum selecteren</strong>
                </div>
            </div>

            <!-- Messages Container -->
            <div class="zzbp-messages px-8"></div>
            
            <!-- Step-by-Step Form -->
            <form id="zzbp-booking-form" class="p-8">
                
                <!-- Step 1: Dates -->
                <div class="zzbp-step" data-step="1">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2 flex items-center gap-2">
                        📅 Selecteer uw datums
                    </h2>
                    <div class="mb-8">
                        <!-- Airbnb-style date inputs -->
                        <div class="border border-gray-300 rounded-lg overflow-hidden grid grid-cols-2">
                            <div class="border-r border-gray-300">
                                <label for="check_in" class="block text-xs font-semibold text-gray-900 uppercase tracking-wide px-3 pt-3 pb-1">
                                    Check-in
                                </label>
                                <input type="date" id="check_in" name="check_in" required 
                                       min="<?php echo date('Y-m-d'); ?>"
                                       class="w-full px-3 pb-3 border-0 text-gray-900 text-base focus:outline-none focus:ring-0">
                            </div>
                            <div>
                                <label for="check_out" class="block text-xs font-semibold text-gray-900 uppercase tracking-wide px-3 pt-3 pb-1">
                                    Check-out
                                </label>
                                <input type="date" id="check_out" name="check_out" required 
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>"
                                       class="w-full px-3 pb-3 border-0 text-gray-900 text-base focus:outline-none focus:ring-0">
                            </div>
                        </div>
                        
                        <div class="flex justify-end mt-6">
                            <button type="button" class="bg-rose-500 hover:bg-rose-600 text-white px-6 py-3 rounded-lg font-semibold transition-all duration-200 hover:shadow-lg zzbp-next-step" data-next="2">
                                Volgende: Gezelschap →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Guests -->
                <div class="zzbp-step" data-step="2" style="display: none;">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2 flex items-center gap-2">
                        👥 Gezelschap
                    </h2>
                    <p class="text-gray-600 mb-6">Geef eerst het aantal personen op voor deze reservering.</p>
                    
                    <div class="space-y-6 mb-8">
                        <div>
                            <label for="adults" class="block text-sm font-semibold text-gray-900 mb-2">
                                Volwassenen en kinderen (vanaf 12 jaar) *
                            </label>
                            <select id="adults" name="adults" required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                                <option value="">Selecteer aantal</option>
                                <option value="1">1 persoon</option>
                                <option value="2" selected>2 personen</option>
                                <option value="3">3 personen</option>
                                <option value="4">4 personen</option>
                                <option value="5">5 personen</option>
                                <option value="6">6 personen</option>
                                <option value="7">7 personen</option>
                                <option value="8">8 personen</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="children_under_12" class="block text-sm font-semibold text-gray-900 mb-2">
                                Kinderen tot 12 jaar
                            </label>
                            <select id="children_under_12" name="children_under_12"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                                <option value="0" selected>0 kinderen</option>
                                <option value="1">1 kind</option>
                                <option value="2">2 kinderen</option>
                                <option value="3">3 kinderen</option>
                                <option value="4">4 kinderen</option>
                                <option value="5">5 kinderen</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="children_0_3" class="block text-sm font-semibold text-gray-900 mb-2">
                                Kinderen 0 tot 3 jaar
                            </label>
                            <select id="children_0_3" name="children_0_3"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                                <option value="0" selected>0 kinderen</option>
                                <option value="1">1 kind</option>
                                <option value="2">2 kinderen</option>
                                <option value="3">3 kinderen</option>
                            </select>
                        </div>
                        
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-sm text-blue-800">
                                Voor deze accommodatie geldt een minimum van één persoon.
                            </p>
                        </div>
                        
                        <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                            <button type="button" class="btn-secondary zzbp-prev-step" data-prev="1">
                                ← Vorige
                            </button>
                            <button type="button" class="btn-primary zzbp-next-step" data-next="3">
                                Volgende: Uw gegevens →
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Contact Info -->
                <div class="zzbp-step" data-step="3" style="display: none;">
                    <h2 class="text-xl font-semibold text-gray-900 mb-2 flex items-center gap-2">
                        📝 Uw gegevens
                    </h2>
                    <p class="text-gray-600 mb-6">Vul uw contactgegevens in om de reservering te voltooien.</p>
                    
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="guest_name" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Volledige naam *
                                </label>
                                <input type="text" id="guest_name" name="guest_name" required 
                                       placeholder="Voor- en achternaam"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                            </div>
                            
                            <div>
                                <label for="guest_email" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Email adres *
                                </label>
                                <input type="email" id="guest_email" name="guest_email" required 
                                       placeholder="uw@email.nl"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                            </div>
                            
                            <div>
                                <label for="guest_phone" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Telefoonnummer *
                                </label>
                                <input type="tel" id="guest_phone" name="guest_phone" required
                                       placeholder="+31 6 12345678"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                            </div>
                            
                            <div>
                                <label for="guest_address" class="block text-sm font-semibold text-gray-900 mb-2">
                                    Adres
                                </label>
                                <input type="text" id="guest_address" name="guest_address" 
                                       placeholder="Straat en huisnummer"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                            </div>
                        </div>
                        
                        <div>
                            <label for="special_requests" class="block text-sm font-semibold text-gray-900 mb-2">
                                Bijzondere wensen
                            </label>
                            <textarea id="special_requests" name="special_requests" rows="3" 
                                      placeholder="Eventuele bijzondere wensen of opmerkingen..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500"></textarea>
                        </div>
                        
                        <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                            <button type="button" class="bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold hover:bg-gray-50 transition-colors zzbp-prev-step" data-prev="2">
                                ← Vorige
                            </button>
                            <button type="submit" class="btn-success px-8 flex items-center gap-2">
                                🎉 Reservering Bevestigen
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hidden fields -->
                <input type="hidden" name="accommodation_id" value="<?php echo esc_attr($accommodation_id ?: ($selected_accommodation['id'] ?? '')); ?>">
                <input type="hidden" name="total_price" id="total_price" value="0">
                
            </form>
        </div>

        <!-- Sticky Sidebar -->
        <div class="xl:col-span-1 lg:col-span-1 order-2 lg:order-2">
            <div class="bg-white rounded-xl shadow-lg border border-gray-200 sticky top-6 h-fit">
                
                <!-- Accommodation Info -->
                <div class="p-6 border-b border-gray-100">
                    <?php 
                    // Debug: Check if we have accommodation data
                    $debug_accommodation = $selected_accommodation ?: ($accommodations[0] ?? null);
                    if ($debug_accommodation): ?>
                        <?php if (!empty($debug_accommodation['primary_image'])): ?>
                            <img src="<?php echo esc_url($debug_accommodation['primary_image']); ?>" 
                                 alt="<?php echo esc_attr($debug_accommodation['name']); ?>"
                                 class="w-full h-48 object-cover rounded-lg mb-4">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gradient-to-br from-rose-500 to-rose-600 rounded-lg mb-4 flex items-center justify-center text-white text-2xl">
                                🏨 <?php echo esc_html($debug_accommodation['name']); ?>
                            </div>
                        <?php endif; ?>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1"><?php echo esc_html($debug_accommodation['name']); ?></h3>
                        <p class="text-gray-600 text-sm capitalize"><?php echo esc_html(str_replace('_', ' ', $debug_accommodation['type'])); ?></p>
                        <p class="text-rose-600 font-semibold mt-2">€<?php echo number_format($debug_accommodation['base_price'], 2); ?> per nacht</p>
                    <?php else: ?>
                        <div class="w-full h-48 bg-gradient-to-br from-gray-400 to-gray-500 rounded-lg mb-4 flex items-center justify-center text-white text-2xl">
                            🏨 Accommodatie
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-1">Selecteer accommodatie</h3>
                        <p class="text-gray-600 text-sm">Geen accommodatie geselecteerd</p>
                        
                        <!-- Debug info -->
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded text-xs">
                            <strong>Debug:</strong><br>
                            Accommodations count: <?php echo count($accommodations); ?><br>
                            Selected ID: <?php echo esc_html($accommodation_id); ?><br>
                            <?php if (!empty($accommodations)): ?>
                                First accommodation: <?php echo esc_html($accommodations[0]['name'] ?? 'No name'); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Booking Summary -->
                <div class="p-6">
                    <h4 class="font-semibold text-gray-900 mb-3">Verblijfsduur</h4>
                    <div class="text-sm text-gray-600 mb-6" id="summary-dates">
                        <span id="summary-checkin">Check-in</span> - <span id="summary-checkout">Check-out</span>
                        <div class="text-xs text-gray-500">(<span id="summary-nights">0</span> nachten)</div>
                    </div>

                    <h4 class="font-semibold text-gray-900 mb-3">Gezelschap</h4>
                    <div class="text-sm text-gray-600 space-y-1 mb-6" id="summary-guests">
                        <div>Volwassenen en kinderen (vanaf 12 jaar): <span id="summary-adults" class="font-medium">0</span></div>
                        <div>Kinderen tot 12 jaar: <span id="summary-children-12" class="font-medium">0</span></div>
                        <div>Kinderen 0 tot 3 jaar: <span id="summary-children-3" class="font-medium">0</span></div>
                    </div>

                    <h4 class="font-semibold text-gray-900 mb-3">Samenvatting</h4>
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span>Totaal verblijf</span>
                            <span id="summary-base-price" class="font-medium">€ 0,00</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Totaal opties en toeslagen</span>
                            <span id="summary-options-price" class="font-medium">€ 0,00</span>
                        </div>
                        <div class="border-t border-gray-200 pt-2 mt-4">
                            <div class="flex justify-between text-lg font-semibold text-gray-900">
                                <span>Totaalbedrag</span>
                                <span id="summary-total-price">€ 0,00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        </div> <!-- End grid -->
    </div> <!-- End container -->
</div> <!-- End main wrapper -->
