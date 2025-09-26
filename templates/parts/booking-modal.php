<?php
/**
 * Template Part: Advanced Booking Modal - MULTI-STEP WIZARD
 * 
 * Complete booking flow with:
 * - Step 1: Date Selection
 * - Step 2: Guest Configuration  
 * - Step 3: Camping Equipment Type
 * - Step 4: Options & Extras
 * - Step 5: Summary & Confirmation
 */

global $zzbp_current_accommodation;
$accommodation = $zzbp_current_accommodation;

if (!$accommodation) {
    return;
}

// Get accommodation pricing and options from API or settings
$base_price = $accommodation['base_price'] ?? 26.00;
$accommodation_id = $accommodation['id'] ?? 'demo';
?>

<!-- Advanced Booking Modal -->
<div id="booking-modal" class="booking-modal-overlay">
    <div class="booking-modal-container">
        
        <!-- Modal Header -->
        <div class="booking-modal-header">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold">Reservering maken</h2>
                    <p class="text-rose-100 mt-1"><?php 
                        // Debug: log what we have in accommodation data
                        error_log('ZZBP Modal Debug - Accommodation data: ' . print_r($accommodation, true));
                        echo esc_html($accommodation['name'] ?? $accommodation['title'] ?? 'Accommodatie'); 
                    ?></p>
                </div>
                <button onclick="closeBookingModal()" class="text-white hover:text-rose-200 transition-colors">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Progress Steps -->
            <div style="margin-top: 1.5rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; max-width: 500px; margin: 0 auto;">
                    <!-- Step 1 -->
                    <div class="step-indicator active" data-step="1" style="display: flex; flex-direction: column; align-items: center; position: relative;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background-color: white; color: #e11d48; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; z-index: 2;">1</div>
                        <span style="font-size: 11px; color: white; margin-top: 4px; white-space: nowrap;">Datum</span>
                    </div>
                    <!-- Line 1 -->
                    <div style="height: 2px; background-color: rgba(255, 255, 255, 0.3); width: 60px; margin-top: -20px;"></div>
                    <!-- Step 2 -->
                    <div class="step-indicator" data-step="2" style="display: flex; flex-direction: column; align-items: center; position: relative;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.3); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; z-index: 2;">2</div>
                        <span style="font-size: 11px; color: rgba(255, 255, 255, 0.8); margin-top: 4px; white-space: nowrap;">Gezelschap</span>
                    </div>
                    <!-- Line 2 -->
                    <div style="height: 2px; background-color: rgba(255, 255, 255, 0.3); width: 60px; margin-top: -20px;"></div>
                    <!-- Step 3 -->
                    <div class="step-indicator" data-step="3" style="display: flex; flex-direction: column; align-items: center; position: relative;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.3); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; z-index: 2;">3</div>
                        <span style="font-size: 11px; color: rgba(255, 255, 255, 0.8); margin-top: 4px; white-space: nowrap;">Type</span>
                    </div>
                    <!-- Line 3 -->
                    <div style="height: 2px; background-color: rgba(255, 255, 255, 0.3); width: 60px; margin-top: -20px;"></div>
                    <!-- Step 4 -->
                    <div class="step-indicator" data-step="4" style="display: flex; flex-direction: column; align-items: center; position: relative;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.3); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; z-index: 2;">4</div>
                        <span style="font-size: 11px; color: rgba(255, 255, 255, 0.8); margin-top: 4px; white-space: nowrap;">Opties</span>
                    </div>
                    <!-- Line 4 -->
                    <div style="height: 2px; background-color: rgba(255, 255, 255, 0.3); width: 60px; margin-top: -20px;"></div>
                    <!-- Step 5 -->
                    <div class="step-indicator" data-step="5" style="display: flex; flex-direction: column; align-items: center; position: relative;">
                        <div style="width: 32px; height: 32px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.3); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; z-index: 2;">5</div>
                        <span style="font-size: 11px; color: rgba(255, 255, 255, 0.8); margin-top: 4px; white-space: nowrap;">Bevestigen</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Content -->
        <div class="p-6 overflow-y-auto max-h-[60vh]">
            
            <!-- STEP 1: DATE SELECTION -->
            <div id="step-1" class="booking-step">
                <h3 class="text-xl font-semibold mb-4">Selecteer uw verblijfsperiode</h3>
                
                
                <!-- Calendar Container -->
                <div id="modal-accommodation-calendar-<?php echo esc_attr($accommodation_id); ?>" class="accommodation-calendar"></div>
            </div>

            <!-- STEP 2: GUEST CONFIGURATION -->
            <div id="step-2" class="booking-step hidden">
                <h3 class="text-xl font-semibold mb-4">Gezelschap</h3>
                <p class="text-gray-600 mb-6">Geef het aantal personen op voor deze reservering.</p>
                
                <div class="space-y-4">
                    <!-- Adults & Children 12+ -->
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div>
                            <div class="font-medium">Volwassenen en kinderen (vanaf 12 jaar)</div>
                            <div class="text-sm text-gray-500">Standaard tarief</div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button type="button" onclick="adjustGuests('adults', -1)" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">-</button>
                            <span id="adults-count" class="w-8 text-center font-medium">1</span>
                            <button type="button" onclick="adjustGuests('adults', 1)" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">+</button>
                        </div>
                    </div>
                    
                    <!-- Children under 12 -->
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div>
                            <div class="font-medium">Kinderen tot 12 jaar</div>
                            <div class="text-sm text-gray-500">Mogelijk gereduceerd tarief</div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button type="button" onclick="adjustGuests('children', -1)" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">-</button>
                            <span id="children-count" class="w-8 text-center font-medium">0</span>
                            <button type="button" onclick="adjustGuests('children', 1)" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">+</button>
                        </div>
                    </div>
                    
                    <!-- Infants 0-3 -->
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                        <div>
                            <div class="font-medium">Kinderen 0 tot 3 jaar</div>
                            <div class="text-sm text-gray-500">Meestal gratis</div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <button type="button" onclick="adjustGuests('infants', -1)" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">-</button>
                            <span id="infants-count" class="w-8 text-center font-medium">0</span>
                            <button type="button" onclick="adjustGuests('infants', 1)" class="w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50">+</button>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <p class="text-sm text-yellow-800">Voor deze accommodatie geldt een minimum van één persoon.</p>
                </div>
            </div>

            <!-- STEP 3: CAMPING EQUIPMENT TYPE -->
            <div id="step-3" class="booking-step hidden">
                <h3 class="text-xl font-semibold mb-4">Kampeermiddel</h3>
                <p class="text-gray-600 mb-6">Selecteer het type kampeermiddel dat u meebrengt.</p>
                
                <div class="grid grid-cols-2 gap-4">
                    <label class="camping-type-option">
                        <input type="radio" name="camping_type" value="caravan" class="sr-only">
                        <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-rose-300 transition-colors">
                            <div class="text-center">
                                <div class="text-3xl mb-2">🚐</div>
                                <div class="font-medium">Caravan</div>
                            </div>
                        </div>
                    </label>
                    
                    <label class="camping-type-option">
                        <input type="radio" name="camping_type" value="tent" class="sr-only">
                        <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-rose-300 transition-colors">
                            <div class="text-center">
                                <div class="text-3xl mb-2">⛺</div>
                                <div class="font-medium">Tent</div>
                            </div>
                        </div>
                    </label>
                    
                    <label class="camping-type-option">
                        <input type="radio" name="camping_type" value="camperbus" class="sr-only">
                        <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-rose-300 transition-colors">
                            <div class="text-center">
                                <div class="text-3xl mb-2">🚌</div>
                                <div class="font-medium">Camperbus</div>
                            </div>
                        </div>
                    </label>
                    
                    <label class="camping-type-option">
                        <input type="radio" name="camping_type" value="camper" class="sr-only">
                        <div class="p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-rose-300 transition-colors">
                            <div class="text-center">
                                <div class="text-3xl mb-2">🚛</div>
                                <div class="font-medium">Camper</div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- STEP 4: OPTIONS & EXTRAS -->
            <div id="step-4" class="booking-step hidden">
                <h3 class="text-xl font-semibold mb-4">Opties en toeslagen</h3>
                
                <!-- Per Night Options -->
                <div class="mb-6">
                    <h4 class="font-medium mb-3">Opties per nacht</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" id="tourist_tax" class="option-checkbox">
                                <label for="tourist_tax" class="font-medium">Toeristenbelasting kampeerplaats</label>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">€ 1,40</div>
                                <div class="text-sm text-gray-500">per nacht</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" id="electricity" class="option-checkbox">
                                <label for="electricity" class="font-medium">Elektrische aansluiting, 10 ampère</label>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">€ 4,00</div>
                                <div class="text-sm text-gray-500">per nacht</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" id="extra_car" class="option-checkbox">
                                <label for="extra_car" class="font-medium">Extra (tweede) auto</label>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">€ 1,50</div>
                                <div class="text-sm text-gray-500">per nacht</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" id="extra_tent" class="option-checkbox">
                                <label for="extra_tent" class="font-medium">Extra (tweede) tentje</label>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">€ 5,00</div>
                                <div class="text-sm text-gray-500">per nacht</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" id="dog" class="option-checkbox">
                                <label for="dog" class="font-medium">Hond</label>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">€ 2,00</div>
                                <div class="text-sm text-gray-500">per nacht</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- One-time Options -->
                <div>
                    <h4 class="font-medium mb-3">Eenmalige opties</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center space-x-2">
                                    <button type="button" onclick="adjustQuantity('bike_day', -1)" class="w-6 h-6 rounded border border-gray-300 flex items-center justify-center text-sm hover:bg-gray-50">-</button>
                                    <span id="bike_day_qty" class="w-8 text-center text-sm">0</span>
                                    <button type="button" onclick="adjustQuantity('bike_day', 1)" class="w-6 h-6 rounded border border-gray-300 flex items-center justify-center text-sm hover:bg-gray-50">+</button>
                                </div>
                                <label class="font-medium">Fiets per dag</label>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">€ 8,50</div>
                                <div class="text-sm text-gray-500">per stuk</div>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="flex items-center space-x-2">
                                    <button type="button" onclick="adjustQuantity('ebike_day', -1)" class="w-6 h-6 rounded border border-gray-300 flex items-center justify-center text-sm hover:bg-gray-50">-</button>
                                    <span id="ebike_day_qty" class="w-8 text-center text-sm">0</span>
                                    <button type="button" onclick="adjustQuantity('ebike_day', 1)" class="w-6 h-6 rounded border border-gray-300 flex items-center justify-center text-sm hover:bg-gray-50">+</button>
                                </div>
                                <label class="font-medium">Elektrische fiets per dag</label>
                            </div>
                            <div class="text-right">
                                <div class="font-medium">€ 18,50</div>
                                <div class="text-sm text-gray-500">per stuk</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 5: SUMMARY & CONFIRMATION -->
            <div id="step-5" class="booking-step hidden">
                <h3 class="text-xl font-semibold mb-4">Prijsopgave</h3>
                
                <!-- Booking Summary -->
                <div class="bg-gray-50 rounded-lg p-4 mb-6">
                    <div class="font-medium mb-2">Reservering van <span id="summary-dates">...</span></div>
                    <div class="text-sm text-gray-600">
                        <span id="summary-guests">...</span> • 
                        <span id="summary-camping-type">...</span>
                    </div>
                </div>
                
                <!-- Price Breakdown -->
                <div class="space-y-4">
                    <!-- Base Price -->
                    <div class="flex justify-between items-center py-2 border-b border-gray-200">
                        <div>
                            <div class="font-medium">Basis kampeerplaats</div>
                            <div class="text-sm text-gray-500" id="base-period">...</div>
                        </div>
                        <div class="font-medium" id="base-total">€ 0,00</div>
                    </div>
                    
                    <!-- Options -->
                    <div id="options-summary">
                        <!-- Dynamic options will be inserted here -->
                    </div>
                    
                    <!-- Total -->
                    <div class="flex justify-between items-center py-3 border-t-2 border-gray-300 text-lg font-bold">
                        <div>Totaalbedrag</div>
                        <div id="grand-total">€ 0,00</div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="mt-6 space-y-4">
                    <h4 class="font-medium">Contactgegevens</h4>
                    <div class="grid grid-cols-2 gap-4">
                        <input type="text" placeholder="Voornaam" class="p-3 border border-gray-300 rounded-lg">
                        <input type="text" placeholder="Achternaam" class="p-3 border border-gray-300 rounded-lg">
                    </div>
                    <input type="email" placeholder="E-mailadres" class="w-full p-3 border border-gray-300 rounded-lg">
                    <input type="tel" placeholder="Telefoonnummer" class="w-full p-3 border border-gray-300 rounded-lg">
                    <textarea placeholder="Opmerkingen (optioneel)" rows="3" class="w-full p-3 border border-gray-300 rounded-lg"></textarea>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div style="background-color: #f9fafb; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e5e7eb;">
            <button id="prev-step" onclick="previousStep()" style="padding: 0.5rem 1.5rem; color: #6b7280; background: none; border: none; cursor: pointer; display: none;">
                ← Vorige
            </button>
            <div style="display: flex; gap: 1rem;">
                <button id="next-step" onclick="nextStep()" style="padding: 0.75rem 2rem; background-color: #e11d48; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500;">
                    Volgende →
                </button>
                <button id="submit-booking" onclick="submitBooking()" style="padding: 0.75rem 2rem; background-color: #22c55e; color: white; border: none; border-radius: 0.5rem; cursor: pointer; font-weight: 500; display: none;">
                    Bevestigen
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form inputs for data collection -->
<form id="booking-form" style="display: none;">
    <input type="hidden" id="modal-checkin" name="checkin">
    <input type="hidden" id="modal-checkout" name="checkout">
    <input type="hidden" id="modal-adults" name="adults" value="1">
    <input type="hidden" id="modal-children" name="children" value="0">
    <input type="hidden" id="modal-infants" name="infants" value="0">
    <input type="hidden" id="modal-camping-type" name="camping_type">
    <input type="hidden" id="modal-options" name="options">
    <input type="hidden" id="modal-total" name="total">
</form>
