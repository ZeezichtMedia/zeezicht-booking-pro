<?php
/**
 * Template Part: Booking Sidebar
 * 
 * CLEAN, REUSABLE, NO INLINE STYLES
 */

global $zzbp_current_accommodation;
$accommodation = $zzbp_current_accommodation;

if (!$accommodation) {
    return;
}
?>

<div class="sticky top-6">
    <div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100">
        <div class="mb-6">
            <div class="text-2xl font-bold text-gray-900">€<?php echo number_format($accommodation['base_price'] ?? 150, 0); ?></div>
            <p class="text-gray-600">per nacht</p>
        </div>
        
        <div class="space-y-4 mb-6">
            <!-- Date Range Picker -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Check-in & Check-out</label>
                <input type="text" id="date-range-picker" name="date_range" 
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                       placeholder="Selecteer periode" readonly>
                <!-- Hidden inputs for form submission -->
                <input type="hidden" id="check-in-date" name="check_in">
                <input type="hidden" id="check-out-date" name="check_out">
            </div>
            
            <!-- Guests -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Gasten</label>
                <select id="guests-count" name="guests" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                    <option value="1">1 gast</option>
                    <option value="2" selected>2 gasten</option>
                    <option value="3">3 gasten</option>
                    <option value="4">4 gasten</option>
                    <option value="5">5 gasten</option>
                    <option value="6">6 gasten</option>
                </select>
            </div>
        </div>
        
        <!-- Price Breakdown -->
        <div id="price-breakdown" class="hidden mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex justify-between text-sm mb-2">
                <span>€<?php echo number_format($accommodation['base_price'] ?? 150, 0); ?> × <span id="nights-count">0</span> nachten</span>
                <span id="subtotal">€0</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600 mt-1">
                <span>Schoonmaakkosten</span>
                <span>€25</span>
            </div>
            <div class="border-t border-gray-300 mt-2 pt-2 flex justify-between font-semibold">
                <span>Totaal</span>
                <span id="total-price">€25</span>
            </div>
        </div>
        
        <button id="booking-submit-btn" onclick="openBookingModal()" 
                class="w-full bg-gradient-to-br from-rose-400 via-rose-500 to-rose-600 hover:from-green-600 hover:to-green-700 text-white font-semibold py-4 px-6 rounded-2xl transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl">
            Reserveer Nu
        </button>
        
        <div class="pt-4 border-t border-gray-200 mt-6">
            <p class="text-sm text-gray-600">Host: ZeeZicht Media</p>
            <p class="text-sm text-gray-600">Phone: +31 123 456 789</p>
        </div>
    </div>
</div>
