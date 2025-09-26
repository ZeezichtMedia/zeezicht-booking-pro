<?php
/**
 * Modern Single Accommodation Template
 * Integrated with WordPress theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get accommodation data
global $zzbp_current_accommodation;
$accommodation = $zzbp_current_accommodation;

if (!$accommodation) {
    wp_redirect(home_url('/accommodaties/'));
    exit;
}

// Get property info for business details
$property_info = get_option('zzbp_property_info', []);
$business_settings = $property_info['settings']['business'] ?? [];

// Set page title for WordPress
add_filter('wp_title', function() use ($accommodation) {
    return esc_html($accommodation['name']) . ' - ' . get_bloginfo('name');
});

get_header(); ?>

<!-- Breadcrumbs -->
<nav class="bg-gray-50 border-b border-gray-200" aria-label="Breadcrumb">
    <div class="container mx-auto px-6 lg:px-8 py-4">
        <ol class="flex items-center space-x-2 text-sm">
            <li>
                <a href="<?php echo esc_url(home_url('/')); ?>" class="text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                    <span class="sr-only">Home</span>
                </a>
            </li>
            <li class="flex items-center">
                <svg class="w-4 h-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <a href="<?php echo esc_url(home_url('/accommodaties/')); ?>" class="text-gray-500 hover:text-gray-700 transition-colors">
                    Accommodaties
                </a>
            </li>
            <li class="flex items-center">
                <svg class="w-4 h-4 text-gray-400 mx-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                </svg>
                <span class="text-gray-900 font-medium"><?php echo esc_html($accommodation['name']); ?></span>
            </li>
        </ol>
    </div>
</nav>

<!-- Structured Data for SEO -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "LodgingBusiness",
    "name": "<?php echo esc_js($accommodation['name']); ?>",
    "description": "<?php echo esc_js($accommodation['description']); ?>",
    "image": <?php echo !empty($accommodation['photos']) ? json_encode($accommodation['photos']) : '[]'; ?>,
    "address": {
        "@type": "PostalAddress",
        "addressCountry": "NL"
    },
    "priceRange": "€<?php echo esc_js($accommodation['base_price']); ?>",
    "maximumAttendeeCapacity": <?php echo esc_js($accommodation['max_guests']); ?>,
    "amenityFeature": <?php echo json_encode(array_map(function($amenity) {
        return ["@type" => "LocationFeatureSpecification", "name" => $amenity];
    }, $accommodation['amenities'] ?? [])); ?>,
    "offers": {
        "@type": "Offer",
        "price": "<?php echo esc_js($accommodation['base_price']); ?>",
        "priceCurrency": "EUR",
        "availability": "https://schema.org/InStock"
    }
}
</script>

<!-- Modern Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    brand: {
                        50: '#f0f9ff',
                        100: '#e0f2fe', 
                        500: '#0ea5e9',
                        600: '#0284c7',
                        700: '#0369a1'
                    }
                },
                fontFamily: {
                    sans: ['Inter', 'system-ui', 'sans-serif']
                }
            }
        }
    }
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

.hero-overlay {
    background: linear-gradient(135deg, rgba(3, 105, 161, 0.8) 0%, rgba(2, 132, 199, 0.6) 100%);
}

.accommodation-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.accommodation-card:hover {
    transform: translateY(-2px);
}

.feature-icon {
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
}

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
    margin: 2px !important;
    border-radius: 8px !important;
    border: 1px solid transparent !important;
    font-weight: 500 !important;
    font-size: 14px !important;
    transition: all 0.2s ease !important;
}

/* Pure Flatpickr - minimal custom styling */

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
    transform: scale(1.02) !important;
}

/* In-range hover - stay light blue */
.accommodation-calendar .flatpickr-day.inRange:hover {
    background-color: #BFDBFE !important;
    border-color: #93C5FD !important;
    color: #1E40AF !important;
    transform: scale(1.02) !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .accommodation-calendar .flatpickr-day {
        width: 36px !important;
        height: 36px !important;
        line-height: 36px !important;
        font-size: 13px !important;
    }
}

/* Month navigation styling */
.accommodation-calendar .flatpickr-prev-month,
.accommodation-calendar .flatpickr-next-month {
    width: 32px !important;
    height: 32px !important;
    border-radius: 6px !important;
    background: #e5e7eb !important;
    transition: all 0.2s ease !important;
}

.accommodation-calendar .flatpickr-prev-month:hover,
.accommodation-calendar .flatpickr-next-month:hover {
    background: #d1d5db !important;
    transform: scale(1.05) !important;
}

.accommodation-calendar .flatpickr-current-month {
    font-weight: 600 !important;
    color: #1f2937 !important;
}

/* Loading animation */
@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.animate-spin {
    animation: spin 1s linear infinite;
}
</style>

<!-- Modern Hero Section -->
<div class="relative h-[50vh] lg:h-[55vh] overflow-hidden">
    <?php if (!empty($accommodation['photos'])): ?>
        <img src="<?php echo esc_url($accommodation['photos'][0]); ?>" 
             alt="<?php echo esc_attr($accommodation['name']); ?>"
             class="w-full h-full object-cover">
    <?php else: ?>
        <div class="w-full h-full bg-gradient-to-br from-brand-500 to-brand-700"></div>
    <?php endif; ?>
    
    <div class="absolute inset-0 hero-overlay"></div>
    
    <!-- Hero Content -->
    <div class="absolute inset-0 flex items-center">
        <div class="container mx-auto px-6 lg:px-8">
            <div class="max-w-2xl text-white">
                <div class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 backdrop-blur-sm text-sm font-medium mb-4">
                    <?php echo esc_html(ucfirst(str_replace('-', ' ', $accommodation['type']))); ?>
                </div>
                
                <h1 class="text-3xl lg:text-5xl font-bold mb-6 leading-tight">
                    <?php echo esc_html($accommodation['name']); ?>
                </h1>
                
                <div class="flex flex-wrap gap-6 text-base lg:text-lg mb-8">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mr-3">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"></path>
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        Vanaf €<?php echo number_format($accommodation['base_price'], 0); ?> per nacht
                    </div>
                    
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mr-3">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                            </svg>
                        </div>
                        Tot <?php echo esc_html($accommodation['max_guests']); ?> gasten
                    </div>
                    
                    <?php if ($accommodation['surface_area']): ?>
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center mr-3">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 01-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <?php echo esc_html($accommodation['surface_area']); ?> m²
                    </div>
                    <?php endif; ?>
                </div>
                
                <a href="<?php echo esc_url(home_url('/reserveren/')); ?>" 
                   onclick="selectAccommodation('<?php echo esc_js($accommodation['id']); ?>', '<?php echo esc_js($accommodation['name']); ?>')"
                   class="inline-flex items-center px-8 py-4 bg-white text-brand-700 font-semibold rounded-lg hover:bg-gray-50 transition-all duration-200 shadow-lg hover:shadow-xl">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Beschikbaarheid bekijken
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Modern Content Section -->
<div class="container mx-auto px-6 lg:px-8 py-16">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-12">
            
            <!-- Description -->
            <?php if ($accommodation['description']): ?>
            <div class="prose prose-lg max-w-none">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">Welkom bij <?php echo esc_html($accommodation['name']); ?></h2>
                <p class="text-gray-600 leading-relaxed">
                    <?php echo esc_html($accommodation['description']); ?>
                </p>
            </div>
            <?php endif; ?>
            
            <!-- Key Features -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-brand-50 to-brand-100 rounded-2xl p-6">
                    <div class="feature-icon w-12 h-12 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Ruimte voor iedereen</h3>
                    <p class="text-gray-600 text-sm">Comfortabel verblijf voor maximaal <?php echo esc_html($accommodation['max_guests']); ?> gasten</p>
                </div>
                
                <?php if ($accommodation['surface_area']): ?>
                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl p-6">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                        </svg>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Ruime accommodatie</h3>
                    <p class="text-gray-600 text-sm"><?php echo esc_html($accommodation['surface_area']); ?> m² aan comfort en privacy</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Amenities -->
            <?php if (!empty($accommodation['amenities'])): ?>
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-8">Wat maakt dit bijzonder?</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach (array_slice($accommodation['amenities'], 0, 6) as $amenity): ?>
                    <div class="flex items-center space-x-4 p-4 bg-white rounded-xl border border-gray-100 hover:border-brand-200 transition-colors">
                        <div class="w-10 h-10 rounded-lg bg-brand-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-brand-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span class="font-medium text-gray-900"><?php echo esc_html($amenity); ?></span>
                    </div>
                    <?php endforeach; ?>
                    
                    <?php if (count($accommodation['amenities']) > 6): ?>
                    <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="w-10 h-10 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span class="font-medium text-gray-600">En nog <?php echo count($accommodation['amenities']) - 6; ?> andere voorzieningen</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Reviews & Social Sharing -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Guest Reviews Placeholder -->
                <div class="bg-gray-50 rounded-2xl p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Gastbeoordelingen</h3>
                    <div class="flex items-center mb-4">
                        <div class="flex items-center">
                            <?php for($i = 0; $i < 5; $i++): ?>
                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                            <?php endfor; ?>
                        </div>
                        <span class="ml-2 text-sm text-gray-600">4.8 (24 beoordelingen)</span>
                    </div>
                    <blockquote class="text-gray-600 italic mb-3">
                        "Prachtige accommodatie met alle comfort. Zeer vriendelijke ontvangst en perfecte locatie!"
                    </blockquote>
                    <div class="text-sm text-gray-500">- Familie van der Berg, augustus 2024</div>
                    <a href="#reviews" class="inline-flex items-center mt-4 text-brand-600 hover:text-brand-700 text-sm font-medium">
                        Alle beoordelingen bekijken
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>

                <!-- Social Sharing -->
                <div class="bg-gray-50 rounded-2xl p-6">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Deel deze accommodatie</h3>
                    <p class="text-gray-600 text-sm mb-4">Laat vrienden en familie weten over deze geweldige accommodatie</p>
                    <div class="flex flex-wrap gap-3">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" 
                           target="_blank" rel="noopener"
                           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                            Facebook
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode('Bekijk deze geweldige accommodatie: ' . get_permalink()); ?>" 
                           target="_blank" rel="noopener"
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893A11.821 11.821 0 0020.885 3.488"/>
                            </svg>
                            WhatsApp
                        </a>
                        <button onclick="copyToClipboard()" 
                                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            Link kopiëren
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Photo Gallery -->
            <?php if (!empty($accommodation['photos']) && count($accommodation['photos']) > 1): ?>
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-8">Impressie</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    <?php foreach (array_slice($accommodation['photos'], 1, 6) as $photo): ?>
                    <div class="aspect-square rounded-2xl overflow-hidden group cursor-pointer">
                        <img src="<?php echo esc_url($photo); ?>" 
                             alt="<?php echo esc_attr($accommodation['name']); ?>"
                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
        
        <!-- Sidebar - Booking Card -->
        <div class="lg:col-span-1">
            <div class="sticky top-8">
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <!-- Price Header -->
                    <div class="bg-gradient-to-r from-brand-500 to-brand-600 text-white p-6 text-center">
                        <div class="text-3xl font-bold mb-1">
                            €<?php echo number_format($accommodation['base_price'], 0); ?>
                        </div>
                        <div class="text-brand-100 text-sm">per nacht</div>
                    </div>
                    
                    <!-- Content -->
                    <div class="p-6">
                        <!-- Quick Facts -->
                        <div class="space-y-4 mb-8">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-brand-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-brand-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                                        </svg>
                                    </div>
                                    <span class="text-gray-600">Gasten</span>
                                </div>
                                <span class="font-semibold text-gray-900">Tot <?php echo esc_html($accommodation['max_guests']); ?></span>
                            </div>
                            
                            <?php if ($accommodation['surface_area']): ?>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 01-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <span class="text-gray-600">Oppervlakte</span>
                                </div>
                                <span class="font-semibold text-gray-900"><?php echo esc_html($accommodation['surface_area']); ?> m²</span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h4a1 1 0 010 2H6.414l2.293 2.293a1 1 0 01-1.414 1.414L5 6.414V8a1 1 0 01-2 0V4zm9 1a1 1 0 010-2h4a1 1 0 011 1v4a1 1 0 01-2 0V6.414l-2.293 2.293a1 1 0 11-1.414-1.414L13.586 5H12zm-9 7a1 1 0 012 0v1.586l2.293-2.293a1 1 0 111.414 1.414L6.414 15H8a1 1 0 010 2H4a1 1 0 01-1-1v-4zm13-1a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 010-2h1.586l-2.293-2.293a1 1 0 111.414-1.414L15 13.586V12a1 1 0 011-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <span class="text-gray-600">Type</span>
                                </div>
                                <span class="font-semibold text-gray-900"><?php echo esc_html(ucfirst(str_replace('-', ' ', $accommodation['type']))); ?></span>
                            </div>
                        </div>
                        
                        <!-- Quick Calendar Link -->
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Beschikbaarheid</h3>
                            <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-5 border border-green-200">
                                <div class="space-y-3">
                                    <div>
                                        <div class="text-sm font-medium text-green-900 mb-1">Real-time beschikbaarheid</div>
                                        <div class="text-xs text-green-700 leading-relaxed">
                                            Bekijk welke datums beschikbaar zijn voor 
                                            <span class="font-medium"><?php echo esc_html($accommodation['name']); ?></span>
                                        </div>
                                    </div>
                                    <a href="#availability-calendar" 
                                       class="inline-flex items-center justify-center w-full px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Bekijk Beschikbaarheid
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- CTA Button -->
                        <a href="<?php echo esc_url(home_url('/reserveren/')); ?>" 
                           onclick="selectAccommodation('<?php echo esc_js($accommodation['id']); ?>', '<?php echo esc_js($accommodation['name']); ?>')"
                           class="w-full bg-gradient-to-r from-brand-500 to-brand-600 text-white text-center py-4 px-6 rounded-xl font-semibold text-lg hover:from-brand-600 hover:to-brand-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 block">
                            Reserveren
                        </a>
                        
                        <!-- Contact -->
                        <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                            <p class="text-sm text-gray-500 mb-3">Vragen? Neem contact op</p>
                            <div class="space-y-2">
                                <?php if (!empty($business_settings['phone'])): ?>
                                <a href="tel:<?php echo esc_attr($business_settings['phone']); ?>" 
                                   class="flex items-center justify-center space-x-2 text-brand-600 hover:text-brand-700 font-medium">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"></path>
                                    </svg>
                                    <span><?php echo esc_html($business_settings['phone']); ?></span>
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($business_settings['email'])): ?>
                                <a href="mailto:<?php echo esc_attr($business_settings['email']); ?>" 
                                   class="flex items-center justify-center space-x-2 text-brand-600 hover:text-brand-700 font-medium">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"></path>
                                        <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
                                    </svg>
                                    <span><?php echo esc_html($business_settings['email']); ?></span>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>

<!-- Full Availability Calendar Section -->
<section id="availability-calendar" class="py-16 bg-gradient-to-br from-gray-50 to-gray-100">
    <div class="container mx-auto px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Beschikbaarheid <?php echo esc_html($accommodation['name']); ?>
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Bekijk de real-time beschikbaarheid en selecteer uw gewenste verblijfsperiode. 
                    Groene dagen zijn beschikbaar, grijze dagen zijn al gereserveerd.
                </p>
            </div>

            <!-- Calendar Container -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div id="accommodation-availability-calendar" 
                         class="accommodation-calendar"
                         data-accommodation-id="<?php echo esc_attr($accommodation['id']); ?>"
                         data-accommodation-name="<?php echo esc_attr($accommodation['name']); ?>">
                        <!-- Calendar will be loaded here -->
                        <div class="text-center py-16">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-brand-600 mb-4"></div>
                            <p class="text-gray-500">Beschikbaarheid laden...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500 mb-4">
                    Beschikbaarheid wordt real-time bijgewerkt. Prijzen kunnen variëren per seizoen.
                </p>
                <a href="<?php echo esc_url(home_url('/reserveren/')); ?>" 
                   onclick="selectAccommodation('<?php echo esc_js($accommodation['id']); ?>', '<?php echo esc_js($accommodation['name']); ?>')"
                   class="inline-flex items-center px-6 py-3 bg-brand-600 text-white rounded-lg hover:bg-brand-700 transition-colors font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    Direct Reserveren
                </a>
            </div>
        </div>
    </div>
</section>

<script>
/**
 * Modern accommodation selection with localStorage
 */
function selectAccommodation(accommodationId, accommodationName) {
    const selectedAccommodation = {
        id: accommodationId,
        name: accommodationName,
        selected_at: new Date().toISOString()
    };
    
    localStorage.setItem('zzbp_selected_accommodation', JSON.stringify(selectedAccommodation));
    
    // Optional: Show a subtle confirmation
    console.log('✅ Selected:', accommodationName);
    
    return true;
}

/**
 * Copy current page URL to clipboard
 */
function copyToClipboard() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        // Show success message
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = `
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Gekopieerd!
        `;
        button.classList.remove('bg-gray-600', 'hover:bg-gray-700');
        button.classList.add('bg-green-600', 'hover:bg-green-700');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('bg-green-600', 'hover:bg-green-700');
            button.classList.add('bg-gray-600', 'hover:bg-gray-700');
        }, 2000);
    }).catch(function(err) {
        console.error('Could not copy text: ', err);
        alert('Link kopiëren niet ondersteund door uw browser');
    });
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('🏨 Accommodation page loaded:', <?php echo json_encode($accommodation['name']); ?>);
    
    // Initialize availability calendar
    initAvailabilityCalendar();
    
    // Add smooth scrolling to internal links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });
});

/**
 * Initialize the availability calendar for this accommodation
 */
function initAvailabilityCalendar() {
    const calendarContainer = document.getElementById('accommodation-availability-calendar');
    if (!calendarContainer) return;
    
    const accommodationId = calendarContainer.dataset.accommodationId;
    const accommodationName = calendarContainer.dataset.accommodationName;
    
    console.log('🗓️ Initializing calendar for:', accommodationName, 'ID:', accommodationId);
    
    // Load Flatpickr if not already loaded
    if (!window.flatpickr) {
        loadFlatpickrAndInitCalendar(calendarContainer, accommodationId, accommodationName);
    } else {
        createAccommodationCalendar(calendarContainer, accommodationId, accommodationName);
    }
}

/**
 * Load Flatpickr library and initialize calendar
 */
function loadFlatpickrAndInitCalendar(container, accommodationId, accommodationName) {
    // Load CSS
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css';
    document.head.appendChild(link);

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
        localeScript.onerror = function() {
            // Continue even if locale fails
            createAccommodationCalendar(container, accommodationId, accommodationName);
        };
        document.head.appendChild(localeScript);
    };
    script.onerror = function() {
        console.error('Failed to load Flatpickr');
        container.innerHTML = '<div class="text-center py-4 text-red-500">Kalender kon niet worden geladen</div>';
    };
    document.head.appendChild(script);
}

/**
 * Create the accommodation-specific calendar
 */
async function createAccommodationCalendar(container, accommodationId, accommodationName) {
    try {
        // Load availability data
        const apiUrl = `<?php echo ZZBP_API_BASE; ?>/availability?accommodation_id=${accommodationId}&property_id=<?php echo esc_js($property_info['id'] ?? ''); ?>`;
        const response = await fetch(apiUrl);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const availabilityData = await response.json();
        
        if (availabilityData.error) {
            throw new Error(availabilityData.error);
        }

        // Create calendar HTML structure
        container.innerHTML = `
            <!-- Selected Dates Display -->
            <div id="selected-dates-display" class="hidden mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <div class="text-sm font-medium text-blue-900 mb-1">Geselecteerde periode</div>
                <div id="selected-period-text" class="text-blue-700 text-sm"></div>
                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-blue-600"><span id="selected-nights">0</span> nachten</span>
                    <button id="clear-selection" class="text-xs text-blue-500 hover:text-blue-700">Wissen</button>
                </div>
            </div>

            <!-- Calendar Container -->
            <div id="calendar-flatpickr" class="border border-gray-200 rounded-lg overflow-hidden"></div>

            <!-- Legend -->
            <div class="mt-3 flex flex-wrap gap-3 text-xs">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded mr-1"></div>
                    <span class="text-gray-600">Beschikbaar</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded mr-1" style="background-color: #E5C5C5;"></div>
                    <span class="text-gray-600">Bezet</span>
                </div>
            </div>

            <!-- Booking Button -->
            <div id="booking-section" class="hidden mt-4">
                <button id="book-selected-dates" class="w-full px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-all duration-200 font-medium text-sm">
                    Reserveer deze periode
                </button>
            </div>
        `;

        // Initialize Flatpickr
        const flatpickrContainer = container.querySelector('#calendar-flatpickr');
        const selectedDisplay = container.querySelector('#selected-dates-display');
        const periodText = container.querySelector('#selected-period-text');
        const nightsSpan = container.querySelector('#selected-nights');
        const clearBtn = container.querySelector('#clear-selection');
        const bookingSection = container.querySelector('#booking-section');
        const bookBtn = container.querySelector('#book-selected-dates');

        let selectedDates = [];
        const unavailableDates = availabilityData.unavailable_dates || [];

        // Let Flatpickr handle EVERYTHING - minimal onDayCreate for past dates only
        const fp = window.flatpickr(flatpickrContainer, {
            mode: 'range',
            inline: true,
            dateFormat: 'Y-m-d',
            minDate: 'today',
            maxDate: new Date(Date.now() + 365 * 24 * 60 * 60 * 1000),
            // ONLY use Flatpickr's disable - let it handle the styling
            disable: unavailableDates.map(dateStr => new Date(dateStr)),
            locale: 'nl',
            showMonths: window.innerWidth >= 768 ? 2 : 1, // Two months on desktop, one on mobile
            onChange: function(dates) {
                handleDateSelection(dates);
            },
            onDayCreate: function(dObj, dStr, fp, dayElem) {
                const date = dayElem.dateObj;
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                // Only add class for past dates - let Flatpickr handle everything else
                if (date < today) {
                    dayElem.classList.add('past-date');
                }
            }
        });

        // Handle date selection
        function handleDateSelection(dates) {
            selectedDates = dates;

            if (dates.length === 0) {
                selectedDisplay.classList.add('hidden');
                bookingSection.classList.add('hidden');
                return;
            }

            if (dates.length === 1) {
                selectedDisplay.classList.remove('hidden');
                periodText.textContent = `Startdatum: ${formatDate(dates[0])}`;
                nightsSpan.textContent = '0';
                bookingSection.classList.add('hidden');
                return;
            }

            if (dates.length === 2) {
                const [startDate, endDate] = dates;
                const nights = Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24));

                selectedDisplay.classList.remove('hidden');
                periodText.textContent = `${formatDate(startDate)} - ${formatDate(endDate)}`;
                nightsSpan.textContent = nights;
                bookingSection.classList.remove('hidden');
            }
        }

        // Format date for display
        function formatDate(date) {
            return date.toLocaleDateString('nl-NL', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
        }

        // Event handlers
        clearBtn.addEventListener('click', () => {
            fp.clear();
            selectedDates = [];
            selectedDisplay.classList.add('hidden');
            bookingSection.classList.add('hidden');
        });

        // Helper function to format date for input (avoid timezone issues)
        function formatDateForInput(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        bookBtn.addEventListener('click', () => {
            if (selectedDates.length === 2) {
                // Store selected dates and redirect to booking
                const bookingData = {
                    accommodation_id: accommodationId,
                    accommodation_name: accommodationName,
                    check_in: formatDateForInput(selectedDates[0]),
                    check_out: formatDateForInput(selectedDates[1]),
                    nights: Math.ceil((selectedDates[1] - selectedDates[0]) / (1000 * 60 * 60 * 24))
                };
                
                localStorage.setItem('zzbp_booking_data', JSON.stringify(bookingData));
                
                // Redirect to booking page
                window.location.href = '<?php echo esc_url(home_url('/reserveren/')); ?>';
            }
        });

        console.log('✅ Calendar initialized successfully for', accommodationName);

    } catch (error) {
        console.error('❌ Error loading calendar:', error);
        container.innerHTML = `
            <div class="text-center py-4">
                <div class="text-red-500 mb-2">
                    <svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                </div>
                <p class="text-sm text-gray-600">Beschikbaarheid kon niet worden geladen</p>
                <button onclick="initAvailabilityCalendar()" class="mt-2 text-xs text-blue-600 hover:text-blue-800">Opnieuw proberen</button>
            </div>
        `;
    }
}
</script>

<?php get_footer(); ?>
