<?php
/**
 * Template Part: Availability Calendar - WORKING VERSION
 * 
 * With Flatpickr initialization like the old version
 */

global $zzbp_current_accommodation;
$accommodation = $zzbp_current_accommodation;

if (!$accommodation) {
    return;
}
?>

<div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 mb-8">
    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Beschikbaarheid</h2>
    
    <!-- Calendar Container -->
    <div class="accommodation-calendar">
        <div id="accommodation-calendar-<?php echo esc_attr($accommodation['id'] ?? 'demo'); ?>"></div>
    </div>
</div>

<!-- Calendar Styling -->
<style>
/* Availability Calendar Styles */
.accommodation-calendar .flatpickr-calendar.inline {
    box-shadow: none !important;
    border: 0 !important;
    background: transparent !important;
    width: 100% !important;
}

.accommodation-calendar .flatpickr-months {
    background: #f9fafb !important;
    border-radius: 0.75rem 0.75rem 0 0 !important;
    padding: 1rem !important;
}

.accommodation-calendar .flatpickr-weekdays {
    background: #f3f4f6 !important;
    padding: 0.5rem 0 !important;
}

/* Modern square days with rounded corners */
.accommodation-calendar .flatpickr-day {
    width: 40px !important;
    height: 40px !important;
    line-height: 40px !important;
    border-radius: 6px !important;
    margin: 2px !important;
    transition: all 0.2s ease !important;
}

/* Available dates - green background - but NOT if selected */
.accommodation-calendar .flatpickr-day:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay):not(.selected):not(.startRange):not(.endRange):not(.inRange) {
    background-color: #22C55E !important;
    color: white !important;
    border-color: #16A34A !important;
}

/* Available dates hover - but NOT if selected */
.accommodation-calendar .flatpickr-day:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay):not(.selected):not(.startRange):not(.endRange):not(.inRange):hover {
    background-color: #16A34A !important;
    color: white !important;
    transform: scale(1.05) !important;
}

/* Disabled dates (unavailable) - dusty rose */
.accommodation-calendar .flatpickr-day.flatpickr-disabled {
    background-color: #E5C5C5 !important;
    color: #8B5A5A !important;
    opacity: 1 !important;
    cursor: not-allowed !important;
}

/* Past dates - greyed out (higher priority than disabled) */
.accommodation-calendar .flatpickr-day.past-date {
    background-color: #F3F4F6 !important;
    color: #9CA3AF !important;
    opacity: 0.6 !important;
    cursor: not-allowed !important;
}

/* Selection overrides green - blue for selected dates (highest priority) */
.accommodation-calendar .flatpickr-day.selected,
.accommodation-calendar .flatpickr-day.startRange,
.accommodation-calendar .flatpickr-day.endRange {
    background-color: #3B82F6 !important;
    border-color: #3B82F6 !important;
    color: white !important;
    transform: scale(1.05) !important;
}

/* Selected dates hover - stay blue */
.accommodation-calendar .flatpickr-day.selected:hover,
.accommodation-calendar .flatpickr-day.startRange:hover,
.accommodation-calendar .flatpickr-day.endRange:hover {
    background-color: #2563EB !important;
    border-color: #2563EB !important;
    color: white !important;
    transform: scale(1.05) !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3) !important;
}

.accommodation-calendar .flatpickr-day.inRange {
    background-color: #DBEAFE !important;
    border-color: #BFDBFE !important;
    color: #1E40AF !important;
    transform: none !important;
}

/* In-range hover - stay light blue */
.accommodation-calendar .flatpickr-day.inRange:hover {
    background-color: #BFDBFE !important;
    border-color: #93C5FD !important;
    color: #1E40AF !important;
    transform: scale(1.02) !important;
}

/* Toon volgende maand dagen */
.accommodation-calendar .flatpickr-day.nextMonthDay,
.accommodation-calendar .flatpickr-day.prevMonthDay {
    display: block !important;
    visibility: visible !important;
    opacity: 0.4 !important;
    color: #9CA3AF !important;
    background-color: #F9FAFB !important;
}

/* Hover effect voor volgende/vorige maand dagen */
.accommodation-calendar .flatpickr-day.nextMonthDay:hover,
.accommodation-calendar .flatpickr-day.prevMonthDay:hover {
    opacity: 0.6 !important;
    background-color: #F3F4F6 !important;
}

/* Zorg dat alle dagen zichtbaar zijn */
.accommodation-calendar.show-next-month-days .flatpickr-day {
    display: block !important;
    visibility: visible !important;
}
</style>

<!-- JavaScript voor calendar -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const accommodationId = '<?php echo esc_js($accommodation['id'] ?? 'demo'); ?>';
    const accommodationName = '<?php echo esc_js($accommodation['name'] ?? 'Demo'); ?>';
    const calendarContainer = document.getElementById('accommodation-calendar-' + accommodationId);
    
    if (calendarContainer && !calendarContainer.hasAttribute('data-initialized')) {
        calendarContainer.setAttribute('data-initialized', 'true');
        initializeAccommodationCalendar(calendarContainer, accommodationId, accommodationName);
    }
});

function initializeAccommodationCalendar(calendarContainer, accommodationId, accommodationName) {
    console.log('🗓️ Initializing calendar for:', accommodationName, 'ID:', accommodationId);
    
    // Load Flatpickr if not already loaded
    if (!window.flatpickr) {
        loadFlatpickrAndInitCalendar(calendarContainer, accommodationId, accommodationName);
    } else {
        createAccommodationCalendar(calendarContainer, accommodationId, accommodationName);
    }
}

function loadFlatpickrAndInitCalendar(container, accommodationId, accommodationName) {
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
            createAccommodationCalendar(container, accommodationId, accommodationName);
        };
        document.head.appendChild(localeScript);
    };
    document.head.appendChild(script);
}

function createAccommodationCalendar(container, accommodationId, accommodationName) {
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
        // Initialize Flatpickr
        const flatpickrContainer = container.querySelector('#calendar-flatpickr-' + accommodationId);
        const selectedDisplay = container.querySelector('#selected-dates-display-' + accommodationId);
        const periodText = container.querySelector('#selected-period-text-' + accommodationId);
        const nightsSpan = container.querySelector('#selected-nights-' + accommodationId);
        const clearBtn = container.querySelector('#clear-selection-' + accommodationId);

        if (!flatpickrContainer) {
            console.error('Flatpickr container not found');
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
                handleDateSelection(dates, selectedDisplay, periodText, nightsSpan);
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
        window.calendarInstance = fp;
        console.log('Calendar initialized successfully');

        // Clear selection button
        clearBtn.addEventListener('click', function() {
            fp.clear();
            selectedDisplay.classList.add('hidden');
        });
    }, 50); // Small delay to ensure HTML is rendered
}

function handleDateSelection(dates, selectedDisplay, periodText, nightsSpan) {
    if (dates.length === 2) {
        const startDate = dates[0];
        const endDate = dates[1];
        const nights = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));
        
        const startStr = formatDate(startDate);
        const endStr = formatDate(endDate);
        
        periodText.textContent = startStr + ' - ' + endStr;
        nightsSpan.textContent = nights;
        selectedDisplay.classList.remove('hidden');
        
        console.log('Selected period:', startStr, 'to', endStr, '(' + nights + ' nights)');
    } else {
        selectedDisplay.classList.add('hidden');
    }
}

function formatDate(date) {
    return date.toLocaleDateString('nl-NL', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
}
</script>
