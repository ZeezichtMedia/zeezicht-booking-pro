/**
 * Zee-zicht Booking Pro JavaScript
 * Version: 1.0.0
 */

(function($) {
    'use strict';

    // Global variables
    let currentStep = 1;
    let selectedAccommodation = null;
    let selectedOptions = [];
    let pricingData = null;

    // Initialize when document is ready
    $(document).ready(function() {
        initBookingForm();
        loadAccommodations();
        bindEvents();
    });

    /**
     * Initialize booking form
     */
    function initBookingForm() {
        showStep(1);
        updateStepIndicators();
    }

    /**
     * Load accommodations from API
     */
    function loadAccommodations() {
        $.ajax({
            url: zzbp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zzbp_get_accommodations',
                nonce: zzbp_ajax.nonce
            },
            beforeSend: function() {
                $('.zzbp-accommodation-grid').html('<div class="zzbp-loading"></div> Accommodaties laden...');
            },
            success: function(response) {
                if (response.success && response.data.success) {
                    displayAccommodations(response.data.data);
                } else {
                    showMessage('Fout bij het laden van accommodaties', 'error');
                }
            },
            error: function() {
                showMessage('Verbindingsfout bij het laden van accommodaties', 'error');
            }
        });
    }

    /**
     * Display accommodations
     */
    function displayAccommodations(accommodations) {
        let html = '';
        
        accommodations.forEach(function(acc) {
            html += `
                <div class="zzbp-accommodation-card" data-id="${acc.id}">
                    <h3>${acc.name}</h3>
                    <div class="price">€${parseFloat(acc.base_price).toFixed(2)} per nacht</div>
                    <p>${acc.description}</p>
                    <div class="zzbp-accommodation-details">
                        <span><strong>Max gasten:</strong> ${acc.max_guests}</span><br>
                        <span><strong>Oppervlakte:</strong> ${acc.surface_area} m²</span>
                    </div>
                    <div class="amenities">
                        ${(acc.amenities || []).map(amenity => 
                            `<span class="zzbp-amenity-tag">${amenity}</span>`
                        ).join('')}
                    </div>
                </div>
            `;
        });
        
        $('.zzbp-accommodation-grid').html(html);
    }

    /**
     * Bind events
     */
    function bindEvents() {
        // Accommodation selection
        $(document).on('click', '.zzbp-accommodation-card', function() {
            $('.zzbp-accommodation-card').removeClass('selected');
            $(this).addClass('selected');
            
            selectedAccommodation = {
                id: $(this).data('id'),
                name: $(this).find('h3').text(),
                price: parseFloat($(this).find('.price').text().replace(/[€\s]/g, '').replace(',', '.'))
            };
            
            loadAccommodationOptions(selectedAccommodation.id);
        });

        // Step navigation
        $('.zzbp-btn-next').on('click', function() {
            if (validateCurrentStep()) {
                nextStep();
            }
        });

        $('.zzbp-btn-prev').on('click', function() {
            prevStep();
        });

        // Date change - recalculate pricing
        $(document).on('change', '#check_in, #check_out', function() {
            if ($('#check_in').val() && $('#check_out').val()) {
                calculatePricing();
            }
        });

        // Guest count change - recalculate pricing
        $(document).on('change', '#adults, #children_12_plus, #children_under_12, #children_0_3', function() {
            calculatePricing();
        });

        // Option selection - recalculate pricing
        $(document).on('change', '.zzbp-option-checkbox', function() {
            updateSelectedOptions();
            calculatePricing();
        });

        // Form submission
        $('.zzbp-booking-form').on('submit', function(e) {
            e.preventDefault();
            submitBooking();
        });
    }

    /**
     * Load accommodation options
     */
    function loadAccommodationOptions(accommodationId) {
        $.get(zzbp_ajax.api_base + '/accommodation-options?accommodation_id=' + accommodationId)
            .done(function(response) {
                if (response.success) {
                    displayOptions(response.data);
                }
            })
            .fail(function() {
                showMessage('Fout bij het laden van opties', 'error');
            });
    }

    /**
     * Display options
     */
    function displayOptions(options) {
        let recurringHtml = '';
        let oneTimeHtml = '';

        // Recurring options
        options.recurring.forEach(function(option) {
            recurringHtml += createOptionHtml(option);
        });

        // One-time options
        options.one_time.forEach(function(option) {
            oneTimeHtml += createOptionHtml(option);
        });

        $('#recurring-options').html(recurringHtml);
        $('#onetime-options').html(oneTimeHtml);
    }

    /**
     * Create option HTML
     */
    function createOptionHtml(option) {
        const price = option.category === 'recurring' ? option.price_per_night : option.price_per_stay;
        const unit = getUnitLabel(option.unit);
        
        return `
            <div class="zzbp-option-item">
                <label>
                    <input type="checkbox" class="zzbp-option-checkbox" 
                           data-id="${option.id}" 
                           data-price="${price}"
                           data-category="${option.category}"
                           data-unit="${option.unit}">
                    <div>
                        <div class="zzbp-option-name">${option.name}</div>
                        <div class="zzbp-option-description">${option.description || ''}</div>
                    </div>
                    <div class="zzbp-option-price">€${parseFloat(price).toFixed(2)} ${unit}</div>
                </label>
            </div>
        `;
    }

    /**
     * Get unit label in Dutch
     */
    function getUnitLabel(unit) {
        const labels = {
            'per_night': 'per nacht',
            'per_person_per_night': 'per persoon per nacht',
            'per_stay': 'per verblijf',
            'per_person': 'per persoon',
            'per_day': 'per dag',
            'per_week': 'per week',
            'per_use': 'per gebruik'
        };
        return labels[unit] || unit;
    }

    /**
     * Update selected options
     */
    function updateSelectedOptions() {
        selectedOptions = [];
        $('.zzbp-option-checkbox:checked').each(function() {
            selectedOptions.push({
                option_id: $(this).data('id'),
                quantity: 1 // For now, always 1
            });
        });
    }

    /**
     * Calculate pricing
     */
    function calculatePricing() {
        if (!selectedAccommodation || !$('#check_in').val() || !$('#check_out').val()) {
            return;
        }

        const requestData = {
            accommodation_id: selectedAccommodation.id,
            check_in: $('#check_in').val(),
            check_out: $('#check_out').val(),
            adults: parseInt($('#adults').val()) || 1,
            children_12_plus: parseInt($('#children_12_plus').val()) || 0,
            children_under_12: parseInt($('#children_under_12').val()) || 0,
            children_0_3: parseInt($('#children_0_3').val()) || 0,
            camping_vehicle_type: $('#camping_vehicle_type').val() || '',
            selected_options: selectedOptions
        };

        $.ajax({
            url: zzbp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zzbp_calculate_pricing',
                nonce: zzbp_ajax.nonce,
                ...requestData
            },
            success: function(response) {
                if (response.success && response.data.success) {
                    pricingData = response.data.data;
                    displayPricingSummary(pricingData);
                } else {
                    showMessage('Fout bij prijsberekening: ' + (response.data.message || 'Onbekende fout'), 'error');
                }
            },
            error: function() {
                showMessage('Verbindingsfout bij prijsberekening', 'error');
            }
        });
    }

    /**
     * Display pricing summary
     */
    function displayPricingSummary(pricing) {
        let html = '<h3>Prijsoverzicht</h3>';
        
        // Base price
        html += `
            <div class="zzbp-price-line">
                <span>${pricing.accommodation.name} (${pricing.accommodation.nights} nachten)</span>
                <span>€${pricing.accommodation.base_total.toFixed(2)}</span>
            </div>
        `;

        // Recurring options
        pricing.recurring_options.forEach(function(option) {
            html += `
                <div class="zzbp-price-line">
                    <span>${option.name}</span>
                    <span>€${option.total_price.toFixed(2)}</span>
                </div>
            `;
        });

        // One-time options
        pricing.one_time_options.forEach(function(option) {
            html += `
                <div class="zzbp-price-line">
                    <span>${option.name}</span>
                    <span>€${option.total_price.toFixed(2)}</span>
                </div>
            `;
        });

        // Total
        html += `
            <div class="zzbp-price-line total">
                <span>Totaal</span>
                <span>€${pricing.totals.total.toFixed(2)}</span>
            </div>
        `;

        $('.zzbp-pricing-summary').html(html).show();
    }

    /**
     * Step navigation
     */
    function showStep(step) {
        $('.zzbp-form-section').removeClass('active');
        $(`.zzbp-form-section[data-step="${step}"]`).addClass('active');
        currentStep = step;
        updateStepIndicators();
    }

    function nextStep() {
        if (currentStep < 4) {
            showStep(currentStep + 1);
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            showStep(currentStep - 1);
        }
    }

    function updateStepIndicators() {
        $('.zzbp-step').each(function(index) {
            const stepNum = index + 1;
            $(this).removeClass('active completed');
            
            if (stepNum === currentStep) {
                $(this).addClass('active');
            } else if (stepNum < currentStep) {
                $(this).addClass('completed');
            }
        });
    }

    /**
     * Validate current step
     */
    function validateCurrentStep() {
        switch(currentStep) {
            case 1:
                if (!selectedAccommodation) {
                    showMessage('Selecteer eerst een accommodatie', 'error');
                    return false;
                }
                break;
            case 2:
                if (!$('#check_in').val() || !$('#check_out').val()) {
                    showMessage('Vul check-in en check-out datums in', 'error');
                    return false;
                }
                if (new Date($('#check_in').val()) >= new Date($('#check_out').val())) {
                    showMessage('Check-out datum moet na check-in datum zijn', 'error');
                    return false;
                }
                break;
            case 3:
                // Options are optional, so always valid
                break;
            case 4:
                if (!$('#guest_name').val() || !$('#guest_email').val()) {
                    showMessage('Vul naam en email in', 'error');
                    return false;
                }
                break;
        }
        return true;
    }

    /**
     * Submit booking
     */
    function submitBooking() {
        if (!validateCurrentStep()) {
            return;
        }

        const bookingData = {
            accommodation_id: selectedAccommodation.id,
            check_in: $('#check_in').val(),
            check_out: $('#check_out').val(),
            adults: parseInt($('#adults').val()) || 1,
            children_12_plus: parseInt($('#children_12_plus').val()) || 0,
            children_under_12: parseInt($('#children_under_12').val()) || 0,
            children_0_3: parseInt($('#children_0_3').val()) || 0,
            camping_vehicle_type: $('#camping_vehicle_type').val(),
            selected_options: selectedOptions,
            guest_name: $('#guest_name').val(),
            guest_email: $('#guest_email').val(),
            guest_phone: $('#guest_phone').val(),
            special_requests: $('#special_requests').val(),
            pricing_data: pricingData
        };

        $.ajax({
            url: zzbp_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'zzbp_create_booking',
                nonce: zzbp_ajax.nonce,
                booking_data: JSON.stringify(bookingData)
            },
            beforeSend: function() {
                $('.zzbp-btn-submit').prop('disabled', true).html('<div class="zzbp-loading"></div> Boeking verwerken...');
            },
            success: function(response) {
                if (response.success) {
                    showMessage('Boeking succesvol aangemaakt!', 'success');
                    // Redirect or show confirmation
                } else {
                    showMessage('Fout bij het aanmaken van de boeking: ' + response.data, 'error');
                }
            },
            error: function() {
                showMessage('Verbindingsfout bij het aanmaken van de boeking', 'error');
            },
            complete: function() {
                $('.zzbp-btn-submit').prop('disabled', false).html('Boeking Bevestigen');
            }
        });
    }

    /**
     * Show message
     */
    function showMessage(message, type) {
        const messageHtml = `<div class="zzbp-message ${type}">${message}</div>`;
        $('.zzbp-messages').html(messageHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.zzbp-messages').empty();
        }, 5000);
    }

    // Make functions available globally if needed
    window.ZZBPBooking = {
        loadAccommodations: loadAccommodations,
        calculatePricing: calculatePricing,
        showMessage: showMessage
    };

})(jQuery);
