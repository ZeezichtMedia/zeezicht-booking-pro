/**
 * ZeeZicht Booking Pro - Main Frontend JavaScript
 * 
 * All JavaScript functionality for the frontend
 */

// Global variables
window.ZZBP = window.ZZBP || {};

/**
 * Photo Gallery Modal Functions
 */
window.ZZBP.PhotoGallery = {
    photos: [],
    currentPhotoIndex: 0,

    init: function(photos) {
        this.photos = photos;
        this.currentPhotoIndex = 0;
    },

    openModal: function(index = 0) {
        this.currentPhotoIndex = index;
        const modal = document.getElementById('photo-modal');
        const modalImage = document.getElementById('modal-image');
        const photoCounter = document.getElementById('photo-counter');
        
        if (this.photos.length > 0) {
            modalImage.src = this.photos[this.currentPhotoIndex];
            photoCounter.textContent = `${this.currentPhotoIndex + 1} / ${this.photos.length}`;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            this.updateThumbnails();
        }
    },

    closeModal: function() {
        const modal = document.getElementById('photo-modal');
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    },

    nextPhoto: function() {
        this.currentPhotoIndex = (this.currentPhotoIndex + 1) % this.photos.length;
        this.updateModalPhoto();
    },

    previousPhoto: function() {
        this.currentPhotoIndex = (this.currentPhotoIndex - 1 + this.photos.length) % this.photos.length;
        this.updateModalPhoto();
    },

    goToPhoto: function(index) {
        this.currentPhotoIndex = index;
        this.updateModalPhoto();
    },

    updateModalPhoto: function() {
        const modalImage = document.getElementById('modal-image');
        const photoCounter = document.getElementById('photo-counter');
        
        modalImage.src = this.photos[this.currentPhotoIndex];
        photoCounter.textContent = `${this.currentPhotoIndex + 1} / ${this.photos.length}`;
        this.updateThumbnails();
    },

    updateThumbnails: function() {
        const thumbnails = document.querySelectorAll('.thumbnail');
        thumbnails.forEach((thumb, index) => {
            if (index === this.currentPhotoIndex) {
                thumb.style.opacity = '1';
                thumb.style.border = '2px solid white';
            } else {
                thumb.style.opacity = '0.5';
                thumb.style.border = 'none';
            }
        });
    }
};

/**
 * Availability Calendar Functions
 */
window.ZZBP.Calendar = {
    instances: {},

    initializeAccommodationCalendar: function(calendarContainer, accommodationId, accommodationName) {
        console.log('🗓️ Initializing calendar for:', accommodationName, 'ID:', accommodationId);
        
        // Load Flatpickr if not already loaded
        if (!window.flatpickr) {
            this.loadFlatpickrAndInitCalendar(calendarContainer, accommodationId, accommodationName);
        } else {
            this.createAccommodationCalendar(calendarContainer, accommodationId, accommodationName);
        }
    },

    loadFlatpickrAndInitCalendar: function(container, accommodationId, accommodationName) {
        const self = this;
        // Load JS
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
        script.onload = function() {
            // Load Dutch locale
            const localeScript = document.createElement('script');
            localeScript.src = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/nl.js';
            localeScript.onload = function() {
                if (window.flatpickr && window.flatpickr.l10ns && window.flatpickr.l10ns.nl) {
                    window.flatpickr.localize(window.flatpickr.l10ns.nl);
                }
                self.createAccommodationCalendar(container, accommodationId, accommodationName);
            };
            document.head.appendChild(localeScript);
        };
        document.head.appendChild(script);
    },

    createAccommodationCalendar: function(container, accommodationId, accommodationName) {
        const self = this;
        // Mock availability data - in real implementation this would come from API
        const availabilityData = {
            unavailable_dates: [
                '2024-12-25', '2024-12-26', '2024-12-31', '2025-01-01',
                '2025-01-15', '2025-01-16', '2025-01-17'
            ]
        };

        container.innerHTML = `
            <!-- Selected Dates Display -->
            <div id="selected-dates-display-${accommodationId}" class="hidden mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <div class="text-sm font-medium text-blue-900 mb-1">Geselecteerde periode</div>
                <div id="selected-period-text-${accommodationId}" class="text-blue-700 text-sm"></div>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-blue-600"><span id="selected-nights-${accommodationId}">0</span> nachten</span>
                    <button id="clear-selection-${accommodationId}" class="text-xs text-blue-500 hover:text-blue-700">Wissen</button>
                </div>
            </div>

            <!-- Calendar Container -->
            <div id="calendar-flatpickr-${accommodationId}" class="border border-gray-200 rounded-lg overflow-hidden"></div>

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
            const flatpickrContainer = container.querySelector('#calendar-flatpickr-' + accommodationId);
            const selectedDisplay = container.querySelector('#selected-dates-display-' + accommodationId);
            const periodText = container.querySelector('#selected-period-text-' + accommodationId);
            const nightsSpan = container.querySelector('#selected-nights-' + accommodationId);
            const clearBtn = container.querySelector('#clear-selection-' + accommodationId);

            if (!flatpickrContainer) {
                console.error('Flatpickr container not found');
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
                    self.handleDateSelection(dates, selectedDisplay, periodText, nightsSpan, accommodationId);
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

            // Store calendar instance
            self.instances[accommodationId] = fp;
            console.log('Calendar initialized successfully');

            // Clear selection button
            clearBtn.addEventListener('click', function() {
                fp.clear();
                selectedDisplay.classList.add('hidden');
            });
        }, 50);
    },

    handleDateSelection: function(dates, selectedDisplay, periodText, nightsSpan, accommodationId) {
        if (dates.length === 2) {
            const startDate = dates[0];
            const endDate = dates[1];
            const nights = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
            
            const startStr = this.formatDate(startDate);
            const endStr = this.formatDate(endDate);
            
            periodText.textContent = startStr + ' - ' + endStr;
            nightsSpan.textContent = nights;
            selectedDisplay.classList.remove('hidden');
            
            console.log('Selected period:', startStr, 'to', endStr, '(' + nights + ' nights)');
        } else {
            selectedDisplay.classList.add('hidden');
        }
    },

    formatDate: function(date) {
        return date.toLocaleDateString('nl-NL', {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    }
};

/**
 * Modal Calendar Functions
 */
window.ZZBP.ModalCalendar = {
    init: function(accommodationId) {
        const calendarContainer = document.getElementById('modal-accommodation-calendar-' + accommodationId);
        
        if (!calendarContainer || calendarContainer.hasAttribute('data-modal-initialized')) {
            return;
        }
        
        calendarContainer.setAttribute('data-modal-initialized', 'true');
        console.log('🗓️ Initializing modal calendar for:', accommodationId);
        
        // Wait for Flatpickr to be available
        if (!window.flatpickr) {
            setTimeout(() => this.init(accommodationId), 200);
            return;
        }
        
        this.createModalCalendar(calendarContainer, accommodationId);
    },

    createModalCalendar: function(container, accommodationId) {
        const self = this;
        // Mock availability data
        const availabilityData = {
            unavailable_dates: [
                '2024-12-25', '2024-12-26', '2024-12-31', '2025-01-01',
                '2025-01-15', '2025-01-16', '2025-01-17'
            ]
        };

        container.innerHTML = `
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
            const flatpickrContainer = container.querySelector('#modal-calendar-flatpickr-' + accommodationId);
            const selectedDisplay = container.querySelector('#modal-selected-dates-display-' + accommodationId);
            const periodText = container.querySelector('#modal-selected-period-text-' + accommodationId);
            const nightsSpan = container.querySelector('#modal-selected-nights-' + accommodationId);
            const clearBtn = container.querySelector('#modal-clear-selection-' + accommodationId);

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
                    self.handleModalDateSelection(dates, selectedDisplay, periodText, nightsSpan);
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
    },

    handleModalDateSelection: function(dates, selectedDisplay, periodText, nightsSpan) {
        if (dates.length === 2) {
            const startDate = dates[0];
            const endDate = dates[1];
            const nights = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
            
            const startStr = window.ZZBP.Calendar.formatDate(startDate);
            const endStr = window.ZZBP.Calendar.formatDate(endDate);
            
            periodText.textContent = startStr + ' - ' + endStr;
            nightsSpan.textContent = nights;
            selectedDisplay.classList.remove('hidden');
            
            // Update hidden form inputs
            const modalCheckIn = document.getElementById('modal-checkin');
            const modalCheckOut = document.getElementById('modal-checkout');
            if (modalCheckIn) modalCheckIn.value = startDate.toISOString().split('T')[0];
            if (modalCheckOut) modalCheckOut.value = endDate.toISOString().split('T')[0];
            
            // Update booking modal data
            if (window.ZZBP.BookingModal) {
                window.ZZBP.BookingModal.bookingData.checkin = startDate.toISOString().split('T')[0];
                window.ZZBP.BookingModal.bookingData.checkout = endDate.toISOString().split('T')[0];
                window.ZZBP.BookingModal.bookingData.nights = nights;
            }
            
            console.log('Modal selected period:', startStr, 'to', endStr, '(' + nights + ' nights)');
        } else {
            selectedDisplay.classList.add('hidden');
        }
    }
};

/**
 * Advanced Booking Modal Functions
 */
window.ZZBP.BookingModal = {
    currentStep: 1,
    totalSteps: 5,
    bookingData: {
        checkin: null,
        checkout: null,
        nights: 0,
        adults: 1,
        children: 0,
        infants: 0,
        campingType: null,
        options: {},
        quantities: {},
        basePrice: 26.00,
        totalPrice: 0
    },

    init: function() {
        // Booking modal initialization is now handled by global openBookingModal function
        console.log('🎯 Booking modal system initialized');
    },

    setupEventListeners: function() {
        // Camping type selection
        document.querySelectorAll('input[name="camping_type"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.camping-type-option div').forEach(div => {
                    div.classList.remove('border-rose-500', 'bg-rose-50');
                    div.classList.add('border-gray-200');
                });
                
                if (this.checked) {
                    const container = this.closest('.camping-type-option').querySelector('div');
                    container.classList.remove('border-gray-200');
                    container.classList.add('border-rose-500', 'bg-rose-50');
                    window.ZZBP.BookingModal.bookingData.campingType = this.value;
                }
            });
        });

        // Option checkboxes
        document.querySelectorAll('.option-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                window.ZZBP.BookingModal.bookingData.options[this.id] = this.checked;
                window.ZZBP.BookingModal.calculateTotal();
            });
        });
    },

    nextStep: function() {
        if (!this.validateCurrentStep()) {
            return;
        }

        if (this.currentStep < this.totalSteps) {
            this.hideStep(this.currentStep);
            this.currentStep++;
            this.showStep(this.currentStep);
            this.updateStepIndicators();
            this.updateButtons();
            
            if (this.currentStep === 5) {
                this.updateSummary();
            }
        }
    },

    previousStep: function() {
        if (this.currentStep > 1) {
            this.hideStep(this.currentStep);
            this.currentStep--;
            this.showStep(this.currentStep);
            this.updateStepIndicators();
            this.updateButtons();
        }
    },

    showStep: function(step) {
        document.getElementById(`step-${step}`).classList.remove('hidden');
    },

    hideStep: function(step) {
        document.getElementById(`step-${step}`).classList.add('hidden');
    },

    updateStepIndicators: function() {
        document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
            const stepNum = index + 1;
            if (stepNum < this.currentStep) {
                indicator.classList.add('completed');
                indicator.classList.remove('active');
            } else if (stepNum === this.currentStep) {
                indicator.classList.add('active');
                indicator.classList.remove('completed');
            } else {
                indicator.classList.remove('active', 'completed');
            }
        });
    },

    updateButtons: function() {
        const prevBtn = document.getElementById('prev-step');
        const nextBtn = document.getElementById('next-step');
        const submitBtn = document.getElementById('submit-booking');

        // Show/hide previous button
        if (prevBtn) {
            if (this.currentStep > 1) {
                prevBtn.classList.remove('hidden');
            } else {
                prevBtn.classList.add('hidden');
            }
        }

        // Show/hide next vs submit button
        if (this.currentStep === this.totalSteps) {
            if (nextBtn) nextBtn.classList.add('hidden');
            if (submitBtn) submitBtn.classList.remove('hidden');
        } else {
            if (nextBtn) nextBtn.classList.remove('hidden');
            if (submitBtn) submitBtn.classList.add('hidden');
        }
    },

    validateCurrentStep: function() {
        switch (this.currentStep) {
            case 1:
                if (!this.bookingData.checkin || !this.bookingData.checkout) {
                    alert('Selecteer eerst een verblijfsperiode.');
                    return false;
                }
                break;
            case 2:
                if (this.bookingData.adults + this.bookingData.children + this.bookingData.infants < 1) {
                    alert('Selecteer minimaal één persoon.');
                    return false;
                }
                break;
            case 3:
                if (!this.bookingData.campingType) {
                    alert('Selecteer een kampeermiddel type.');
                    return false;
                }
                break;
        }
        return true;
    },

    adjustGuests: function(type, delta) {
        const currentValue = this.bookingData[type];
        const newValue = Math.max(0, currentValue + delta);
        
        // Ensure minimum 1 adult
        if (type === 'adults' && newValue < 1) {
            return;
        }
        
        this.bookingData[type] = newValue;
        document.getElementById(`${type}-count`).textContent = newValue;
        document.getElementById(`modal-${type}`).value = newValue;
    },

    adjustQuantity: function(item, delta) {
        const currentValue = this.bookingData.quantities[item] || 0;
        const newValue = Math.max(0, currentValue + delta);
        
        this.bookingData.quantities[item] = newValue;
        document.getElementById(`${item}_qty`).textContent = newValue;
        this.calculateTotal();
    },

    calculateTotal: function() {
        let total = 0;
        
        // Base price calculation
        const baseTotal = this.bookingData.nights * this.bookingData.basePrice;
        total += baseTotal;
        
        // Per night options
        const perNightOptions = {
            'tourist_tax': 1.40,
            'electricity': 4.00,
            'extra_car': 1.50,
            'extra_tent': 5.00,
            'dog': 2.00
        };
        
        Object.keys(perNightOptions).forEach(option => {
            if (this.bookingData.options[option]) {
                total += perNightOptions[option] * this.bookingData.nights;
            }
        });
        
        // One-time options
        const oneTimeOptions = {
            'bike_day': 8.50,
            'ebike_day': 18.50
        };
        
        Object.keys(oneTimeOptions).forEach(option => {
            const quantity = this.bookingData.quantities[option] || 0;
            total += oneTimeOptions[option] * quantity;
        });
        
        this.bookingData.totalPrice = total;
        return total;
    },

    updateSummary: function() {
        // Update dates
        const startDate = new Date(this.bookingData.checkin);
        const endDate = new Date(this.bookingData.checkout);
        const dateStr = `${this.formatDate(startDate)} tot ${this.formatDate(endDate)}`;
        document.getElementById('summary-dates').textContent = dateStr;
        
        // Update guests
        let guestStr = `${this.bookingData.adults} volwassenen`;
        if (this.bookingData.children > 0) {
            guestStr += `, ${this.bookingData.children} kinderen`;
        }
        if (this.bookingData.infants > 0) {
            guestStr += `, ${this.bookingData.infants} baby's`;
        }
        document.getElementById('summary-guests').textContent = guestStr;
        
        // Update camping type
        document.getElementById('summary-camping-type').textContent = this.bookingData.campingType || 'Niet geselecteerd';
        
        // Update price breakdown
        const baseTotal = this.bookingData.nights * this.bookingData.basePrice;
        document.getElementById('base-period').textContent = `${this.formatDate(startDate)} - ${this.formatDate(endDate)}`;
        document.getElementById('base-total').textContent = `€ ${baseTotal.toFixed(2)}`;
        
        // Calculate and display total
        const grandTotal = this.calculateTotal();
        document.getElementById('grand-total').textContent = `€ ${grandTotal.toFixed(2)}`;
    },

    formatDate: function(date) {
        return date.toLocaleDateString('nl-NL', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });
    },

    submitBooking: function() {
        // Collect all form data with null checks
        const firstNameInput = document.querySelector('input[placeholder="Voornaam"]');
        const lastNameInput = document.querySelector('input[placeholder="Achternaam"]');
        const emailInput = document.querySelector('input[type="email"]');
        const phoneInput = document.querySelector('input[type="tel"]');
        const commentsInput = document.querySelector('textarea');
        
        const formData = {
            ...this.bookingData,
            contact: {
                firstName: firstNameInput ? firstNameInput.value : '',
                lastName: lastNameInput ? lastNameInput.value : '',
                email: emailInput ? emailInput.value : '',
                phone: phoneInput ? phoneInput.value : '',
                comments: commentsInput ? commentsInput.value : ''
            }
        };
        
        console.log('Booking submission:', formData);
        
        // Here you would normally send to your API
        alert('Reservering functionaliteit nog niet geïmplementeerd. Check console voor data.');
        
        // Close modal
        this.closeModal();
    },

    closeModal: function() {
        const modal = document.getElementById('booking-modal');
        if (modal) {
            modal.classList.remove('active');
        }
        document.body.style.overflow = 'auto';
        
        // Reset to step 1
        this.hideStep(this.currentStep);
        this.currentStep = 1;
        this.showStep(1);
        this.updateStepIndicators();
        this.updateButtons();
    }
};

/**
 * Initialize everything when DOM is ready
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 ZZBP Frontend JavaScript loaded');
    
    // Initialize photo gallery if photos exist
    const photoGalleryScript = document.querySelector('script[data-zzbp-photos]');
    if (photoGalleryScript) {
        try {
            const photos = JSON.parse(photoGalleryScript.getAttribute('data-zzbp-photos'));
            window.ZZBP.PhotoGallery.init(photos);
        } catch (e) {
            console.error('Failed to parse photo gallery data:', e);
        }
    }
    
    // Initialize calendars
    document.querySelectorAll('[id^="accommodation-calendar-"]').forEach(function(calendarEl) {
        const accommodationId = calendarEl.id.replace('accommodation-calendar-', '');
        const accommodationName = calendarEl.getAttribute('data-accommodation-name') || 'Demo';
        
        if (!calendarEl.hasAttribute('data-initialized')) {
            calendarEl.setAttribute('data-initialized', 'true');
            window.ZZBP.Calendar.initializeAccommodationCalendar(calendarEl, accommodationId, accommodationName);
        }
    });
    
    // Initialize booking modal
    window.ZZBP.BookingModal.init();
    
    // Keyboard navigation for photo gallery
    document.addEventListener('keydown', function(e) {
        const modal = document.getElementById('photo-modal');
        if (modal && modal.style.display === 'flex') {
            if (e.key === 'ArrowRight') window.ZZBP.PhotoGallery.nextPhoto();
            if (e.key === 'ArrowLeft') window.ZZBP.PhotoGallery.previousPhoto();
            if (e.key === 'Escape') window.ZZBP.PhotoGallery.closeModal();
        }
    });
});

// Global functions for backward compatibility
window.openPhotoModal = function(index) { window.ZZBP.PhotoGallery.openModal(index); };
window.closePhotoModal = function() { window.ZZBP.PhotoGallery.closeModal(); };
window.nextPhoto = function() { window.ZZBP.PhotoGallery.nextPhoto(); };
window.previousPhoto = function() { window.ZZBP.PhotoGallery.previousPhoto(); };
window.goToPhoto = function(index) { window.ZZBP.PhotoGallery.goToPhoto(index); };

// Booking Modal Global Functions
window.openBookingModal = function() {
    const modal = document.getElementById('booking-modal');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Initialize booking modal if available
        if (window.ZZBP.BookingModal) {
            // Get accommodation data from WordPress localized script
            if (window.ZZBP_ACCOMMODATION_DATA) {
                window.ZZBP.BookingModal.bookingData.basePrice = parseFloat(window.ZZBP_ACCOMMODATION_DATA.base_price) || 26.00;
            }
            
            // Initialize modal calendar
            const accommodationId = window.ZZBP_ACCOMMODATION_DATA?.accommodation_id || 'demo';
            setTimeout(() => {
                window.ZZBP.ModalCalendar.init(accommodationId);
                window.ZZBP.BookingModal.setupEventListeners();
            }, 100);
        }
        
        console.log('🎯 Booking modal opened');
    } else {
        console.error('❌ Booking modal element not found');
    }
};

window.closeBookingModal = function() {
    if (window.ZZBP.BookingModal) {
        window.ZZBP.BookingModal.closeModal();
    } else {
        const modal = document.getElementById('booking-modal');
        if (modal) {
            modal.classList.remove('active');
        }
        document.body.style.overflow = 'auto';
    }
};

window.nextStep = function() {
    if (window.ZZBP.BookingModal) {
        window.ZZBP.BookingModal.nextStep();
    }
};

window.previousStep = function() {
    if (window.ZZBP.BookingModal) {
        window.ZZBP.BookingModal.previousStep();
    }
};

window.adjustGuests = function(type, delta) {
    if (window.ZZBP.BookingModal) {
        window.ZZBP.BookingModal.adjustGuests(type, delta);
    }
};

window.adjustQuantity = function(item, delta) {
    if (window.ZZBP.BookingModal) {
        window.ZZBP.BookingModal.adjustQuantity(item, delta);
    }
};

window.submitBooking = function() {
    if (window.ZZBP.BookingModal) {
        window.ZZBP.BookingModal.submitBooking();
    }
};
