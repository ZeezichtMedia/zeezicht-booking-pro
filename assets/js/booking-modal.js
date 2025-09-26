/**
 * ZeeZicht Booking Pro - Modal JavaScript
 * 
 * CLEAN, EXTERNAL FILE - NO INLINE SCRIPTS
 */

// Modal state
let currentModalStep = 1;
let modalData = {
    adults: 2,
    children: 0,
    basePrice: 150 // Will be overridden by PHP
};

// Modal functions
function openBookingModal() {
    console.log('Opening booking modal...');
    const modal = document.getElementById('booking-modal');
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Pre-fill dates from sidebar if available
        syncSidebarToModal();
        
        // Ensure calendar is initialized
        setTimeout(initModalCalendar, 100);
    }
}

function closeBookingModal() {
    const modal = document.getElementById('booking-modal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        currentModalStep = 1;
        showStep(1);
    }
}

function showStep(step) {
    // Hide all steps
    document.querySelectorAll('.booking-step').forEach(el => el.style.display = 'none');
    
    // Show current step
    const currentStepEl = document.getElementById('step-' + step);
    if (currentStepEl) {
        currentStepEl.style.display = 'block';
    }
    
    // Update progress bar
    const progressBar = document.getElementById('progress-bar');
    if (progressBar) {
        const progress = (step / 4) * 100;
        progressBar.style.width = progress + '%';
    }
    
    // Update buttons
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const submitBtn = document.getElementById('submit-btn');
    
    if (prevBtn) prevBtn.disabled = step === 1;
    
    if (step === 4) {
        if (nextBtn) nextBtn.style.display = 'none';
        if (submitBtn) submitBtn.style.display = 'block';
        updateConfirmation();
    } else {
        if (nextBtn) nextBtn.style.display = 'block';
        if (submitBtn) submitBtn.style.display = 'none';
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
        const checkIn = document.getElementById('modal-checkin')?.value;
        const checkOut = document.getElementById('modal-checkout')?.value;
        if (!checkIn || !checkOut) {
            alert('Selecteer eerst een periode in de kalender');
            return false;
        }
    } else if (currentModalStep === 3) {
        const name = document.getElementById('guest-name')?.value?.trim();
        const email = document.getElementById('guest-email')?.value?.trim();
        const phone = document.getElementById('guest-phone')?.value?.trim();
        
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
    const countEl = document.getElementById(type + '-count');
    if (countEl) {
        countEl.textContent = newValue;
    }
}

function updateConfirmation() {
    const checkIn = document.getElementById('modal-checkin')?.value;
    const checkOut = document.getElementById('modal-checkout')?.value;
    
    if (checkIn && checkOut) {
        const checkInDate = new Date(checkIn);
        const checkOutDate = new Date(checkOut);
        const nights = Math.ceil((checkOutDate - checkInDate) / (1000 * 60 * 60 * 24));
        
        // Format dates for display
        const checkInDisplay = formatDateForDisplay(checkInDate);
        const checkOutDisplay = formatDateForDisplay(checkOutDate);
        
        // Update confirmation display
        const confirmDates = document.getElementById('confirm-dates');
        const confirmGuests = document.getElementById('confirm-guests');
        const confirmNights = document.getElementById('confirm-nights');
        const confirmSubtotal = document.getElementById('confirm-subtotal');
        const confirmServiceFee = document.getElementById('confirm-service-fee');
        const confirmTotal = document.getElementById('confirm-total');
        
        if (confirmDates) confirmDates.textContent = checkInDisplay + ' - ' + checkOutDisplay;
        if (confirmGuests) {
            confirmGuests.textContent = modalData.adults + ' volwassenen' + 
                (modalData.children > 0 ? ', ' + modalData.children + ' kinderen' : '');
        }
        if (confirmNights) confirmNights.textContent = nights;
        
        const subtotal = nights * modalData.basePrice;
        const serviceFee = Math.round(subtotal * 0.08);
        const total = subtotal + 25 + serviceFee;
        
        if (confirmSubtotal) confirmSubtotal.textContent = '€' + subtotal;
        if (confirmServiceFee) confirmServiceFee.textContent = '€' + serviceFee;
        if (confirmTotal) confirmTotal.textContent = '€' + total;
    }
}

function submitBooking() {
    const formData = {
        checkIn: document.getElementById('modal-checkin')?.value,
        checkOut: document.getElementById('modal-checkout')?.value,
        adults: modalData.adults,
        children: modalData.children,
        name: document.getElementById('guest-name')?.value,
        email: document.getElementById('guest-email')?.value,
        phone: document.getElementById('guest-phone')?.value
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

function syncSidebarToModal() {
    const sidebarCheckIn = document.getElementById('check-in-date');
    const sidebarCheckOut = document.getElementById('check-out-date');
    const modalCheckIn = document.getElementById('modal-checkin');
    const modalCheckOut = document.getElementById('modal-checkout');
    
    if (sidebarCheckIn?.value && modalCheckIn) {
        modalCheckIn.value = sidebarCheckIn.value;
    }
    if (sidebarCheckOut?.value && modalCheckOut) {
        modalCheckOut.value = sidebarCheckOut.value;
    }
}

function initModalCalendar() {
    // Calendar initialization will be handled by main calendar script
    console.log('Modal calendar initialization requested');
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
    showStep(1);
    
    // Make functions globally accessible
    window.openBookingModal = openBookingModal;
    window.closeBookingModal = closeBookingModal;
    window.nextStep = nextStep;
    window.prevStep = prevStep;
    window.updateGuestCount = updateGuestCount;
    window.submitBooking = submitBooking;
});
