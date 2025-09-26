/**
 * Modern Step-by-Step Booking Form JavaScript
 * Handles form navigation, real-time pricing, and validation
 */

class ModernBookingForm {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 4;
        this.basePrice = 26.00; // Base accommodation price per night
        this.nights = 0;
        this.guests = {
            adults: 0,
            children_under_12: 0,
            children_0_3: 0
        };
        this.options = {
            tourist_tax: 0,
            electricity: 0,
            extra_car: 0,
            bike_rental: 0
        };
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateSummary();
        this.setMinDates();
    }
    
    bindEvents() {
        // Step navigation (support both old and new classes)
        document.querySelectorAll('.zzbp-next-step, [data-next]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const nextStep = parseInt(e.target.dataset.next || e.target.getAttribute('data-next'));
                if (this.validateCurrentStep()) {
                    this.goToStep(nextStep);
                }
            });
        });
        
        document.querySelectorAll('.zzbp-prev-step').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const prevStep = parseInt(e.target.dataset.prev);
                this.goToStep(prevStep);
            });
        });
        
        // Date changes
        const checkinInput = document.getElementById('check_in');
        const checkoutInput = document.getElementById('check_out');
        
        if (checkinInput) {
            checkinInput.addEventListener('change', () => {
                this.updateCheckoutMinDate();
                this.calculateNights();
                this.updateDateDisplay();
                this.updateSummary();
            });
        }
        
        if (checkoutInput) {
            checkoutInput.addEventListener('change', () => {
                this.calculateNights();
                this.updateDateDisplay();
                this.updateSummary();
            });
        }
        
        // Guest changes
        document.getElementById('adults').addEventListener('change', () => {
            this.updateGuests();
            this.updateSummary();
        });
        
        document.getElementById('children_under_12').addEventListener('change', () => {
            this.updateGuests();
            this.updateSummary();
        });
        
        document.getElementById('children_0_3').addEventListener('change', () => {
            this.updateGuests();
            this.updateSummary();
        });
        
        // Option changes
        document.querySelectorAll('select[name^="tourist_tax"], select[name^="electricity"], select[name^="extra_car"], select[name^="bike_rental"]').forEach(select => {
            select.addEventListener('change', () => {
                this.updateOptions();
                this.updateSummary();
            });
        });
        
        // Form submission
        document.getElementById('zzbp-booking-form').addEventListener('submit', (e) => {
            this.handleSubmit(e);
        });
    }
    
    setMinDates() {
        const today = new Date().toISOString().split('T')[0];
        const tomorrow = new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        
        document.getElementById('check_in').min = today;
        document.getElementById('check_out').min = tomorrow;
    }
    
    updateCheckoutMinDate() {
        const checkinDate = document.getElementById('check_in').value;
        if (checkinDate) {
            const minCheckout = new Date(checkinDate);
            minCheckout.setDate(minCheckout.getDate() + 1);
            document.getElementById('check_out').min = minCheckout.toISOString().split('T')[0];
            
            // If checkout is before the new minimum, update it
            const currentCheckout = document.getElementById('check_out').value;
            if (currentCheckout && currentCheckout <= checkinDate) {
                document.getElementById('check_out').value = minCheckout.toISOString().split('T')[0];
                this.calculateNights();
            }
        }
    }
    
    calculateNights() {
        const checkin = document.getElementById('check_in').value;
        const checkout = document.getElementById('check_out').value;
        
        if (checkin && checkout) {
            const checkinDate = new Date(checkin);
            const checkoutDate = new Date(checkout);
            const timeDiff = checkoutDate.getTime() - checkinDate.getTime();
            this.nights = Math.ceil(timeDiff / (1000 * 3600 * 24));
            
            if (this.nights < 1) {
                this.nights = 1;
            }
        } else {
            this.nights = 0;
        }
    }
    
    updateDateDisplay() {
        const checkin = document.getElementById('check_in').value;
        const checkout = document.getElementById('check_out').value;
        
        if (checkin && checkout) {
            const checkinFormatted = this.formatDate(checkin);
            const checkoutFormatted = this.formatDate(checkout);
            
            // Update header display
            document.getElementById('display-checkin').textContent = checkinFormatted;
            document.getElementById('display-checkout').textContent = checkoutFormatted;
            
            // Update sidebar summary
            document.getElementById('summary-checkin').textContent = checkinFormatted;
            document.getElementById('summary-checkout').textContent = checkoutFormatted;
            document.getElementById('summary-nights').textContent = this.nights;
        }
    }
    
    formatDate(dateString) {
        const date = new Date(dateString);
        const options = { 
            day: 'numeric', 
            month: 'short', 
            year: 'numeric' 
        };
        return date.toLocaleDateString('nl-NL', options);
    }
    
    updateGuests() {
        this.guests.adults = parseInt(document.getElementById('adults').value) || 0;
        this.guests.children_under_12 = parseInt(document.getElementById('children_under_12').value) || 0;
        this.guests.children_0_3 = parseInt(document.getElementById('children_0_3').value) || 0;
        
        // Update sidebar summary
        document.getElementById('summary-adults').textContent = this.guests.adults;
        document.getElementById('summary-children-12').textContent = this.guests.children_under_12;
        document.getElementById('summary-children-3').textContent = this.guests.children_0_3;
    }
    
    updateOptions() {
        // Tourist tax (per person per night)
        const touristTaxSelect = document.getElementById('tourist_tax');
        this.options.tourist_tax = parseInt(touristTaxSelect.value) || 0;
        const touristTaxTotal = this.options.tourist_tax * 1.40 * (this.guests.adults + this.guests.children_under_12) * this.nights;
        document.getElementById('tourist_tax_total').textContent = `€ ${touristTaxTotal.toFixed(2)}`;
        
        // Electricity (per night)
        const electricitySelect = document.getElementById('electricity');
        this.options.electricity = parseInt(electricitySelect.value) || 0;
        const electricityTotal = this.options.electricity * 4.00 * this.nights;
        document.getElementById('electricity_total').textContent = `€ ${electricityTotal.toFixed(2)}`;
        
        // Extra car (per night)
        const extraCarSelect = document.getElementById('extra_car');
        this.options.extra_car = parseInt(extraCarSelect.value) || 0;
        const extraCarTotal = this.options.extra_car * 1.50 * this.nights;
        document.getElementById('extra_car_total').textContent = `€ ${extraCarTotal.toFixed(2)}`;
        
        // Bike rental (one-time)
        const bikeRentalSelect = document.getElementById('bike_rental');
        this.options.bike_rental = parseInt(bikeRentalSelect.value) || 0;
        const bikeRentalTotal = this.options.bike_rental * 8.50;
        document.getElementById('bike_rental_total').textContent = `€ ${bikeRentalTotal.toFixed(2)}`;
    }
    
    updateSummary() {
        // Base price calculation
        const baseTotal = this.basePrice * this.nights;
        document.getElementById('summary-base-price').textContent = `€ ${baseTotal.toFixed(2)}`;
        
        // Options total
        const touristTaxTotal = this.options.tourist_tax * 1.40 * (this.guests.adults + this.guests.children_under_12) * this.nights;
        const electricityTotal = this.options.electricity * 4.00 * this.nights;
        const extraCarTotal = this.options.extra_car * 1.50 * this.nights;
        const bikeRentalTotal = this.options.bike_rental * 8.50;
        
        const optionsTotal = touristTaxTotal + electricityTotal + extraCarTotal + bikeRentalTotal;
        document.getElementById('summary-options-price').textContent = `€ ${optionsTotal.toFixed(2)}`;
        
        // Grand total
        const grandTotal = baseTotal + optionsTotal;
        document.getElementById('summary-total-price').textContent = `€ ${grandTotal.toFixed(2)}`;
        document.getElementById('total_price').value = grandTotal.toFixed(2);
    }
    
    validateCurrentStep() {
        const currentStepElement = document.querySelector(`[data-step="${this.currentStep}"]`);
        const requiredFields = currentStepElement.querySelectorAll('[required]');
        
        let isValid = true;
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = '#e74c3c';
                isValid = false;
            } else {
                field.style.borderColor = '#e1e5e9';
            }
        });
        
        // Additional validation for step 1 (dates)
        if (this.currentStep === 1) {
            if (this.nights < 1) {
                this.showMessage('Selecteer geldige check-in en check-out datums.', 'error');
                isValid = false;
            }
        }
        
        // Additional validation for step 2 (guests)
        if (this.currentStep === 2) {
            if (this.guests.adults < 1) {
                this.showMessage('Selecteer minimaal 1 volwassene.', 'error');
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    goToStep(stepNumber) {
        // Hide current step
        document.querySelector(`[data-step="${this.currentStep}"]`).style.display = 'none';
        
        // Show new step
        document.querySelector(`[data-step="${stepNumber}"]`).style.display = 'block';
        
        // Update current step
        this.currentStep = stepNumber;
        
        // Scroll to top of form
        const formArea = document.querySelector('.zzbp-form-area') || document.getElementById('zzbp-booking-form');
        if (formArea) {
            formArea.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
            });
        }
        
        // Clear any error messages
        this.clearMessages();
    }
    
    showMessage(message, type = 'info') {
        const messagesContainer = document.querySelector('.zzbp-messages');
        messagesContainer.innerHTML = `<div class="zzbp-message ${type}">${message}</div>`;
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            this.clearMessages();
        }, 5000);
    }
    
    clearMessages() {
        document.querySelector('.zzbp-messages').innerHTML = '';
    }
    
    handleSubmit(e) {
        e.preventDefault();
        
        if (!this.validateCurrentStep()) {
            return;
        }
        
        // Show loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<span class="zzbp-spinner"></span> Bezig met reserveren...';
        submitBtn.disabled = true;
        
        // Simulate API call (replace with actual booking logic)
        setTimeout(() => {
            this.showMessage('🎉 Bedankt voor uw reservering! We nemen zo spoedig mogelijk contact met u op.', 'success');
            
            // Reset form
            document.getElementById('zzbp-booking-form').reset();
            this.goToStep(1);
            this.updateSummary();
            
            // Reset button
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            
            // Clear localStorage if accommodation was pre-selected
            localStorage.removeItem('zzbp_selected_accommodation');
            
        }, 2000);
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if the modern booking form exists
    const bookingForm = document.getElementById('zzbp-booking-form');
    
    if (bookingForm) {
        // Check for booking data from accommodation page
        const bookingData = getBookingData();
        if (bookingData) {
            console.log('🎯 Found booking data:', bookingData);
            
            // Pre-fill dates
            if (bookingData.check_in) {
                const checkinInput = document.getElementById('check_in');
                if (checkinInput) {
                    checkinInput.value = bookingData.check_in;
                    // Trigger change event so ModernBookingForm recognizes it
                    checkinInput.dispatchEvent(new Event('change'));
                }
            }
            
            if (bookingData.check_out) {
                const checkoutInput = document.getElementById('check_out');
                if (checkoutInput) {
                    checkoutInput.value = bookingData.check_out;
                    // Trigger change event so ModernBookingForm recognizes it
                    checkoutInput.dispatchEvent(new Event('change'));
                }
            }
            
            // Update display
            if (bookingData.check_in && bookingData.check_out) {
                const displayCheckin = document.getElementById('display-checkin');
                const displayCheckout = document.getElementById('display-checkout');
                
                if (displayCheckin) displayCheckin.textContent = formatDateForDisplay(bookingData.check_in);
                if (displayCheckout) displayCheckout.textContent = formatDateForDisplay(bookingData.check_out);
            }
            
            // Show confirmation message
            const messages = document.querySelector('.zzbp-messages');
            if (messages) {
                messages.innerHTML = `<div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-4">
                    ✅ <strong>${bookingData.accommodation_name}</strong> geselecteerd voor ${bookingData.nights} nachten
                </div>`;
            }
        }
        
        // Check for pre-selected accommodation from localStorage (fallback)
        const selectedAccommodation = getSelectedAccommodation();
        if (selectedAccommodation && !bookingData) {
            const messages = document.querySelector('.zzbp-messages');
            if (messages) {
                messages.innerHTML = `<div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-lg mb-4">
                    ✅ <strong>${selectedAccommodation.name}</strong> is geselecteerd voor uw reservering.
                </div>`;
            }
        }
        
        // Initialize the modern booking form
        const bookingFormInstance = new ModernBookingForm();
        
        // If we pre-filled dates, trigger updates
        if (bookingData && bookingData.check_in && bookingData.check_out) {
            // Small delay to ensure form is initialized
            setTimeout(() => {
                bookingFormInstance.calculateNights();
                bookingFormInstance.updateDateDisplay();
                bookingFormInstance.updateSummary();
            }, 100);
        }
    }
});

/**
 * Get booking data from localStorage (from accommodation detail page)
 */
function getBookingData() {
    const stored = localStorage.getItem('zzbp_booking_data');
    if (stored) {
        try {
            const data = JSON.parse(stored);
            // Clear after reading to prevent reuse
            localStorage.removeItem('zzbp_booking_data');
            return data;
        } catch (e) {
            console.error('Error parsing booking data:', e);
            localStorage.removeItem('zzbp_booking_data');
        }
    }
    return null;
}

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
 * Format date for display (Dutch format)
 */
function formatDateForDisplay(dateString) {
    const date = new Date(dateString);
    const options = { 
        day: 'numeric', 
        month: 'long', 
        year: 'numeric' 
    };
    return date.toLocaleDateString('nl-NL', options);
}
