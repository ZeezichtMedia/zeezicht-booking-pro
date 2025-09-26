<?php
/**
 * Template Part: Booking Modal
 * 
 * CLEAN, REUSABLE, NO INLINE STYLES
 */

global $zzbp_current_accommodation;
$accommodation = $zzbp_current_accommodation;

if (!$accommodation) {
    return;
}
?>

<!-- Booking Modal -->
<div id="booking-modal" class="booking-modal-overlay">
    <div class="booking-modal-content">
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
                    <div id="modal-selected-dates" class="modal-selected-dates hidden">
                        <div class="text-sm font-medium text-blue-900 mb-1">Geselecteerde periode</div>
                        <div id="modal-period-text" class="text-blue-700 text-sm"></div>
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs text-blue-600"><span id="modal-nights">0</span> nachten</span>
                            <button type="button" id="modal-clear-selection" class="text-xs text-blue-500 hover:text-blue-700 bg-transparent border-0 cursor-pointer">Wissen</button>
                        </div>
                    </div>
                    
                    <!-- Modal Calendar -->
                    <div class="modal-calendar" style="margin-bottom: 1rem;">
                        <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 mb-8">
                            <h2 class="text-2xl font-semibold text-gray-900 mb-6">Beschikbaarheid</h2>
                            
                            <!-- Calendar Container -->
                            <div class="accommodation-calendar">
                                <div id="modal-accommodation-calendar-<?php echo esc_attr($accommodation['id'] ?? 'demo'); ?>"></div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Calendar JavaScript -->
                    <script>
                    function initModalCalendar() {
                        const accommodationId = '<?php echo esc_js($accommodation['id'] ?? 'demo'); ?>';
                        const calendarContainer = document.getElementById('modal-accommodation-calendar-' + accommodationId);
                        
                        if (!calendarContainer || calendarContainer.hasAttribute('data-modal-initialized')) {
                            return;
                        }
                        
                        calendarContainer.setAttribute('data-modal-initialized', 'true');
                        console.log('🗓️ Initializing modal calendar for:', accommodationId);
                        
                        // Wait for Flatpickr to be available
                        if (!window.flatpickr) {
                            setTimeout(initModalCalendar, 200);
                            return;
                        }
                        
                        // Mock availability data
                        const availabilityData = {
                            unavailable_dates: [
                                '2024-12-25', '2024-12-26', '2024-12-31', '2025-01-01',
                                '2025-01-15', '2025-01-16', '2025-01-17'
                            ]
                        };

                        calendarContainer.innerHTML = `
                            <!-- Selected Dates Display -->
                            <div id="modal-selected-dates-display-${accommodationId}" class="hidden mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                                <div class="text-sm font-medium text-blue-900 mb-1">Geselecteerde periode</div>
                                <div id="modal-selected-period-text-${accommodationId}" class="text-blue-700 text-sm"></div>
                                <div class="flex justify-between items-center mt-2">
                                    <span class="text-xs text-blue-600"><span id="modal-selected-nights-${accommodationId}">0</span> nachten</span>
                                    <button id="modal-clear-selection-${accommodationId}" class="text-xs text-blue-500 hover:text-blue-700">Wissen</button>
                                </div>
                            </div>

                            <!-- Calendar Container -->
                            <div id="modal-calendar-flatpickr-${accommodationId}" class="border border-gray-200 rounded-lg overflow-hidden"></div>

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
                            const flatpickrContainer = calendarContainer.querySelector('#modal-calendar-flatpickr-' + accommodationId);
                            const selectedDisplay = calendarContainer.querySelector('#modal-selected-dates-display-' + accommodationId);
                            const periodText = calendarContainer.querySelector('#modal-selected-period-text-' + accommodationId);
                            const nightsSpan = calendarContainer.querySelector('#modal-selected-nights-' + accommodationId);
                            const clearBtn = calendarContainer.querySelector('#modal-clear-selection-' + accommodationId);

                            if (!flatpickrContainer) {
                                console.error('Modal Flatpickr container not found');
                                return;
                            }

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
                                    handleModalDateSelection(dates, selectedDisplay, periodText, nightsSpan);
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

                            // Store modal calendar instance
                            window.modalCalendarInstance = fp;
                            console.log('Modal calendar initialized successfully');

                            // Clear selection button
                            clearBtn.addEventListener('click', function() {
                                fp.clear();
                                selectedDisplay.classList.add('hidden');
                            });
                        }, 50);
                    }

                    function handleModalDateSelection(dates, selectedDisplay, periodText, nightsSpan) {
                        if (dates.length === 2) {
                            const startDate = dates[0];
                            const endDate = dates[1];
                            const nights = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
                            
                            const startStr = formatDate(startDate);
                            const endStr = formatDate(endDate);
                            
                            periodText.textContent = startStr + ' - ' + endStr;
                            nightsSpan.textContent = nights;
                            selectedDisplay.classList.remove('hidden');
                            
                            // Update hidden form inputs
                            const modalCheckIn = document.getElementById('modal-checkin');
                            const modalCheckOut = document.getElementById('modal-checkout');
                            if (modalCheckIn) modalCheckIn.value = startDate.toISOString().split('T')[0];
                            if (modalCheckOut) modalCheckOut.value = endDate.toISOString().split('T')[0];
                            
                            console.log('Modal selected period:', startStr, 'to', endStr, '(' + nights + ' nights)');
                        } else {
                            selectedDisplay.classList.add('hidden');
                        }
                    }

                    // Initialize modal calendar when modal opens
                    document.addEventListener('DOMContentLoaded', function() {
                        // Override the openBookingModal function to initialize calendar
                        const originalOpenBookingModal = window.openBookingModal;
                        window.openBookingModal = function() {
                            if (originalOpenBookingModal) {
                                originalOpenBookingModal();
                            }
                            setTimeout(initModalCalendar, 100);
                        };
                    });
                    </script>
                    
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
