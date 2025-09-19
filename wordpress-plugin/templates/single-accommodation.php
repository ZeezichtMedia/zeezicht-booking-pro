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
</style>

<!-- Modern Hero Section -->
<div class="relative h-[60vh] lg:h-[70vh] overflow-hidden">
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
                        
                        <!-- CTA Button -->
                        <a href="<?php echo esc_url(home_url('/reserveren/')); ?>" 
                           onclick="selectAccommodation('<?php echo esc_js($accommodation['id']); ?>', '<?php echo esc_js($accommodation['name']); ?>')"
                           class="w-full bg-gradient-to-r from-brand-500 to-brand-600 text-white text-center py-4 px-6 rounded-xl font-semibold text-lg hover:from-brand-600 hover:to-brand-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 block">
                            Beschikbaarheid & Reserveren
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

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    console.log('🏨 Accommodation page loaded:', <?php echo json_encode($accommodation['name']); ?>);
    
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
</script>

<?php get_footer(); ?>
