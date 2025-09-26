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
$plugin = new ZeeZichtBookingPro();
$accommodations = $plugin->get_accommodations();

if (empty($accommodations)) {
    echo '<div class="zzbp-message error">Booking system is temporarily unavailable. Please try again later.</div>';
    return;
}

// Get property info for business details
$property_info = get_option('zzbp_property_info', []);
$business_settings = $property_info['settings']['business'] ?? [];
?>

<!-- Modern Step-by-Step Booking Form -->
<div class="zzbp-modern-booking">
    <div class="zzbp-booking-container">
        
        <!-- Main Form Area -->
        <div class="zzbp-form-area">
            <div class="zzbp-form-header">
                <h1 class="zzbp-form-title">Prijsopgave</h1>
                <div class="zzbp-booking-dates" id="booking-dates-display">
                    Reservering van <strong id="display-checkin">datum selecteren</strong> tot <strong id="display-checkout">datum selecteren</strong>
                </div>
            </div>

            <!-- Messages Container -->
            <div class="zzbp-messages"></div>
            
            <!-- Step-by-Step Form -->
            <form id="zzbp-booking-form" class="zzbp-step-form">
        
        <?php if ($show_accommodation_selector && !$accommodation_id): ?>
        <!-- Accommodation Selection -->
        <div class="zzbp-form-section">
            <h3>Kies uw accommodatie</h3>
            <div class="zzbp-accommodation-selector">
                <?php foreach ($accommodations as $acc): ?>
                <label class="zzbp-accommodation-option">
                    <input type="radio" name="accommodation_id" value="<?php echo esc_attr($acc['id']); ?>" required>
                    <div class="accommodation-card">
                        <h4><?php echo esc_html($acc['name']); ?></h4>
                        <p class="accommodation-type"><?php echo esc_html(ucfirst(str_replace('-', ' ', $acc['type']))); ?></p>
                        <p class="accommodation-price">€<?php echo number_format($acc['base_price'], 2); ?> per nacht</p>
                        <p class="accommodation-guests">Max <?php echo esc_html($acc['max_guests']); ?> gasten</p>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <input type="hidden" name="accommodation_id" value="<?php echo esc_attr($accommodation_id); ?>">
        <?php endif; ?>

        <!-- Dates and Guests -->
        <div class="zzbp-form-section">
            <h3>Datums en gasten</h3>
            
            <div class="zzbp-form-row">
                <div class="zzbp-form-col">
                    <label for="check_in">Check-in datum *</label>
                    <input type="date" id="check_in" name="check_in" required 
                           min="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="zzbp-form-col">
                    <label for="check_out">Check-out datum *</label>
                    <input type="date" id="check_out" name="check_out" required 
                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                </div>
            </div>

            <div class="zzbp-form-row">
                <div class="zzbp-form-col">
                    <label for="adults">Volwassenen *</label>
                    <select id="adults" name="adults" required>
                        <option value="1">1 volwassene</option>
                        <option value="2" selected>2 volwassenen</option>
                        <option value="3">3 volwassenen</option>
                        <option value="4">4 volwassenen</option>
                        <option value="5">5 volwassenen</option>
                        <option value="6">6 volwassenen</option>
                    </select>
                </div>
                <div class="zzbp-form-col">
                    <label for="children_12_plus">Kinderen (12+ jaar)</label>
                    <select id="children_12_plus" name="children_12_plus">
                        <option value="0" selected>0 kinderen</option>
                        <option value="1">1 kind</option>
                        <option value="2">2 kinderen</option>
                        <option value="3">3 kinderen</option>
                        <option value="4">4 kinderen</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Guest Information -->
        <div class="zzbp-form-section">
            <h3>Uw gegevens</h3>
            
            <div class="zzbp-form-row">
                <div class="zzbp-form-col">
                    <label for="guest_name">Volledige naam *</label>
                    <input type="text" id="guest_name" name="guest_name" required 
                           placeholder="Voor- en achternaam">
                </div>
                <div class="zzbp-form-col">
                    <label for="guest_email">Email adres *</label>
                    <input type="email" id="guest_email" name="guest_email" required 
                           placeholder="uw@email.nl">
                </div>
            </div>

            <div class="zzbp-form-row">
                <div class="zzbp-form-col">
                    <label for="guest_phone">Telefoonnummer</label>
                    <input type="tel" id="guest_phone" name="guest_phone" 
                           placeholder="+31 6 12345678">
                </div>
            </div>

            <div class="zzbp-form-group">
                <label for="special_requests">Bijzondere wensen</label>
                <textarea id="special_requests" name="special_requests" rows="3" 
                          placeholder="Eventuele bijzondere wensen of opmerkingen..."></textarea>
            </div>
        </div>

        <!-- Pricing Summary -->
        <div class="zzbp-pricing-summary" style="display: none;">
            <h3>Prijsoverzicht</h3>
            <div id="pricing-details">
                <!-- Pricing will be calculated and displayed here -->
            </div>
        </div>

        <!-- Submit Button -->
        <div class="zzbp-form-actions">
            <button type="submit" class="zzbp-btn zzbp-btn-success">
                📅 Reservering Bevestigen
            </button>
        </div>
    </form>
</div>

<style>
/* Additional booking form styles */
.zzbp-accommodation-selector {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.zzbp-accommodation-option {
    cursor: pointer;
}

.zzbp-accommodation-option input[type="radio"] {
    display: none;
}

.zzbp-accommodation-option .accommodation-card {
    border: 2px solid #e1e5e9;
    border-radius: 8px;
    padding: 15px;
    background: white;
    transition: all 0.3s ease;
}

.zzbp-accommodation-option:hover .accommodation-card {
    border-color: #3498db;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.2);
}

.zzbp-accommodation-option input[type="radio"]:checked + .accommodation-card {
    border-color: #3498db;
    background: #f8fbff;
}

.zzbp-accommodation-option .accommodation-card h4 {
    margin: 0 0 8px 0;
    color: #2c3e50;
    font-size: 16px;
}

.zzbp-accommodation-option .accommodation-type {
    color: #7f8c8d;
    font-size: 12px;
    margin: 0 0 8px 0;
    text-transform: uppercase;
}

.zzbp-accommodation-option .accommodation-price {
    color: #27ae60;
    font-weight: 600;
    font-size: 18px;
    margin: 0 0 5px 0;
}

.zzbp-accommodation-option .accommodation-guests {
    color: #7f8c8d;
    font-size: 12px;
    margin: 0;
}

.zzbp-form-section {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.zzbp-form-section h3 {
    margin: 0 0 20px 0;
    color: #2c3e50;
    font-size: 20px;
}

.zzbp-form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
}

.zzbp-form-col {
    flex: 1;
}

.zzbp-form-group {
    margin-bottom: 20px;
}

.zzbp-form-group:last-child {
    margin-bottom: 0;
}

.zzbp-form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #2c3e50;
}

.zzbp-form-group input,
.zzbp-form-group select,
.zzbp-form-group textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.zzbp-form-group input:focus,
.zzbp-form-group select:focus,
.zzbp-form-group textarea:focus {
    outline: none;
    border-color: #3498db;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.zzbp-form-actions {
    text-align: center;
    margin-top: 30px;
}

.zzbp-btn-success {
    background: #27ae60;
    color: white;
    padding: 15px 30px;
    font-size: 16px;
    font-weight: 600;
}

.zzbp-btn-success:hover {
    background: #229954;
}

@media (max-width: 768px) {
    .zzbp-form-row {
        flex-direction: column;
        gap: 0;
    }
    
    .zzbp-accommodation-selector {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
// Booking form functionality with localStorage integration
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('zzbp-booking-form');
    
    // Check for pre-selected accommodation from localStorage
    const selectedAccommodation = getSelectedAccommodation();
    if (selectedAccommodation) {
        // Pre-select the accommodation if it exists in the form
        const accommodationRadio = document.querySelector(`input[name="accommodation_id"][value="${selectedAccommodation.id}"]`);
        if (accommodationRadio) {
            accommodationRadio.checked = true;
            
            // Show confirmation message
            const messages = document.querySelector('.zzbp-messages');
            messages.innerHTML = `<div class="zzbp-message info">✅ <strong>${selectedAccommodation.name}</strong> is geselecteerd voor uw reservering.</div>`;
        }
    }
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Show simple success message for now
            const messages = document.querySelector('.zzbp-messages');
            messages.innerHTML = '<div class="zzbp-message success">Bedankt voor uw reservering! We nemen zo spoedig mogelijk contact met u op.</div>';
            
            // Clear the selected accommodation after booking
            clearSelectedAccommodation();
            
            // Scroll to top
            form.scrollIntoView({ behavior: 'smooth' });
        });
    }
});

/**
 * Get selected accommodation from localStorage
 */
function getSelectedAccommodation() {
    const stored = localStorage.getItem('zzbp_selected_accommodation');
    if (stored) {
        try {
            return JSON.parse(stored);
        } catch (e) {
            console.error('Error parsing stored accommodation:', e);
            localStorage.removeItem('zzbp_selected_accommodation');
        }
    }
    return null;
}

/**
 * Clear selected accommodation
 */
function clearSelectedAccommodation() {
    localStorage.removeItem('zzbp_selected_accommodation');
}
</script>
