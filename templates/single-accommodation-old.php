<?php
/**
 * Modern Single Accommodation Template v2
 * Inspired by Chic Stay Showcase design
 * WordPress + Tailwind CSS implementation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get accommodation data
global $zzbp_current_accommodation;
$accommodation = $zzbp_current_accommodation;

if (!$accommodation) {
    wp_redirect(home_url('/accommodations/'));
    exit;
}

// Debug: Log accommodation data
error_log("🏨 Accommodation data: " . print_r($accommodation, true));

// Get property info for business details
$property_info = get_option('zzbp_property_info', []);
$business_settings = $property_info['settings']['business'] ?? [];

// Set page title for WordPress
add_filter('wp_title', function() use ($accommodation) {
    return esc_html($accommodation['name']) . ' - ' . get_bloginfo('name');
});

get_header(); ?>

<!-- ZeeZicht Booking Pro - Industry Best Practice Architecture -->
<div class="zzbp-wrapper">
    <div id="zzbp-booking-app" class="min-h-screen bg-gray-50">
    
    <!-- Hero Section -->
    <div class="container mx-auto px-4 py-6">
        <div class="relative overflow-hidden rounded-2xl group" style="height: 70vh;">
            <?php if (!empty($accommodation['primary_image'])): ?>
                <?php 
                $image_url = $accommodation['primary_image'];
                
                // Fix URL if it's relative
                if (strpos($image_url, 'http') !== 0) {
                    if (strpos($image_url, '/uploads/') === 0) {
                        $image_url = 'http://localhost:2121' . $image_url;
                    }
                }
                ?>
                <img src="<?php echo esc_url($image_url); ?>" 
                     alt="<?php echo esc_attr($accommodation['name']); ?>"
                     class="gallery-image"
                     onerror="console.log('Image failed to load:', this.src); this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <?php endif; ?>
            
            <!-- Fallback if image fails -->
            <div class="w-full h-full bg-gradient-to-br from-rose-400 via-rose-500 to-rose-600 flex items-center justify-center" 
                 style="<?php echo !empty($accommodation['primary_image']) ? 'display: none;' : ''; ?>">
                <div class="text-center text-white">
                    <div class="text-6xl mb-4">🏨</div>
                    <h2 class="text-2xl font-semibold"><?php echo esc_html($accommodation['name']); ?></h2>
                    <?php if (!empty($accommodation['primary_image'])): ?>
                        <p class="text-sm mt-4 opacity-75">Afbeelding kan niet worden geladen</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Header Navigation -->
            <div class="absolute top-6 left-6 right-6 flex justify-between items-center" style="z-index: 10;">
                <button onclick="history.back()" class="nav-button" title="Terug">
                    <!-- Back Arrow Icon (Lucide) -->
                    <i data-lucide="arrow-left" class="w-5 h-5 text-gray-700"></i>
                </button>
                
                <div class="flex gap-2">
                    <button class="nav-button" title="Delen" onclick="navigator.share ? navigator.share({title: '<?php echo esc_js($accommodation['name']); ?>', url: window.location.href}) : alert('Kopieer de URL om te delen')">
                        <!-- Share Icon (Lucide) -->
                        <i data-lucide="share-2" class="w-5 h-5 text-gray-700"></i>
                    </button>
                    <button class="nav-button" title="Favoriet" onclick="toggleFavorite('<?php echo esc_js($accommodation['id']); ?>')">
                        <!-- Heart Icon (Lucide) -->
                        <i data-lucide="heart" class="w-5 h-5 text-gray-700"></i>
                    </button>
                </div>
            </div>

            <!-- Gradient Overlay -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent"></div>
        </div>
    </div>

    <!-- Main Content - FORCE Ultra Wide -->
    <div class="mx-auto px-12 pb-12" style="max-width: 1920px !important; width: 100% !important;">
        <!-- FIXED: Correct grid layout -->
        <div class="grid lg:grid-cols-3 gap-8">
            
            <!-- Left Column - Property Details -->
            <div class="lg:col-span-2 space-y-8">
                
                <!-- Property Header -->
                <div class="card">
                    <div class="p-8">
                        <div class="accommodation-header">
                            <div>
                                <h1 class="accommodation-title"><?php echo esc_html($accommodation['name']); ?></h1>
                                <div class="accommodation-meta">
                                    <span class="flex items-center gap-2">
                                        <!-- Home Icon (Lucide) -->
                                        <i data-lucide="home" class="w-4 h-4 text-gray-500"></i>
                                        <span class="text-gray-700 font-medium"><?php echo esc_html(ucfirst(str_replace('_', ' ', $accommodation['type']))); ?></span>
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <!-- Users Icon (Lucide) -->
                                        <i data-lucide="users" class="w-4 h-4 text-gray-500"></i>
                                        <span class="text-gray-700 font-medium">Max <?php echo esc_html($accommodation['max_guests']); ?> gasten</span>
                                    </span>
                                    <?php if (!empty($accommodation['surface_area'])): ?>
                                    <span class="flex items-center gap-2">
                                        <!-- Square Icon (Lucide) -->
                                        <i data-lucide="square" class="w-4 h-4 text-gray-500"></i>
                                        <span class="text-gray-700 font-medium"><?php echo esc_html($accommodation['surface_area']); ?>m²</span>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="accommodation-price">€<?php echo number_format($accommodation['base_price'], 0); ?></div>
                                <div class="text-gray-600">per nacht</div>
                            </div>
                        </div>
                        
                        <!-- Description -->
                        <?php if (!empty($accommodation['description'])): ?>
                        <div class="mt-6">
                            <p class="text-gray-700 leading-relaxed"><?php echo esc_html($accommodation['description']); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Amenities -->
                <?php if (!empty($accommodation['amenities'])): ?>
                <div class="card">
                    <div class="p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Voorzieningen</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php foreach ($accommodation['amenities'] as $amenity): ?>
                            <div class="amenity-item">
                                <div class="amenity-icon">
                                    <?php 
                                    // Lucide icon mapping voor verschillende amenities
                                    $icon_map = [
                                        'wifi' => 'wifi',
                                        'parking' => 'car',
                                        'kitchen' => 'chef-hat',
                                        'tv' => 'tv',
                                        'balcony' => 'building',
                                        'airco' => 'wind',
                                        'air_conditioning' => 'wind',
                                        'pool' => 'waves',
                                        'gym' => 'dumbbell',
                                        'spa' => 'flower',
                                        'restaurant' => 'utensils',
                                        'bar' => 'wine',
                                        'laundry' => 'shirt',
                                        'elevator' => 'move-vertical',
                                        'pets' => 'dog',
                                        'smoking' => 'cigarette',
                                        'wheelchair' => 'accessibility'
                                    ];
                                    
                                    $amenity_key = strtolower(str_replace([' ', '_'], '', $amenity));
                                    $icon_name = $icon_map[$amenity_key] ?? 'check';
                                    ?>
                                    <i data-lucide="<?php echo esc_attr($icon_name); ?>" class="w-4 h-4 text-rose-600"></i>
                                </div>
                                <span class="text-gray-700 capitalize"><?php echo esc_html(str_replace('_', ' ', $amenity)); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Photo Gallery -->
                <div class="card">
                    <div class="p-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Foto's</h2>
                        
                        <?php if (!empty($accommodation['photos']) && count($accommodation['photos']) > 0): ?>
                            <!-- Photo Grid -->
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="photo-gallery">
                                <?php foreach ($accommodation['photos'] as $index => $photo_url): ?>
                                    <?php 
                                    // Fix photo URL if it's relative
                                    $fixed_photo_url = $photo_url;
                                    if (strpos($fixed_photo_url, 'http') !== 0) {
                                        if (strpos($fixed_photo_url, '/uploads/') === 0) {
                                            $fixed_photo_url = 'http://localhost:2121' . $fixed_photo_url;
                                        }
                                    }
                                    ?>
                                    <div class="gallery-item" onclick="openPhotoModal(<?php echo $index; ?>)">
                                        <img src="<?php echo esc_url($fixed_photo_url); ?>" 
                                             alt="<?php echo esc_attr($accommodation['name']); ?> foto <?php echo $index + 1; ?>"
                                             class="gallery-image"
                                             onerror="this.parentElement.style.display='none';">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        
                        <!-- Photo count indicator -->
                        <div class="mt-4 text-center">
                            <span class="text-sm text-gray-600">
                                <?php echo count($accommodation['photos']); ?> foto's
                            </span>
                            <button onclick="openPhotoModal(0)" class="ml-4 text-rose-600 hover:text-rose-700 font-semibold text-sm">
                                Bekijk alle foto's →
                            </button>
                        </div>
                        
                        <!-- Photo Modal -->
                        <div id="photo-modal" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.9); z-index: 9999; display: none; align-items: center; justify-content: center;">
                            <div style="position: relative; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; padding: 1rem;">
                                <!-- Close Button -->
                                <button onclick="closePhotoModal()" 
                                        style="position: absolute; top: 1.5rem; right: 1.5rem; z-index: 10000; background: rgba(255,255,255,0.2); border: none; border-radius: 50%; padding: 0.75rem; cursor: pointer; transition: all 0.2s;">
                                    <svg style="width: 1.5rem; height: 1.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                
                                <!-- Previous Button -->
                                <button onclick="previousPhoto()" 
                                        style="position: absolute; left: 1.5rem; top: 50%; transform: translateY(-50%); z-index: 10000; background: rgba(255,255,255,0.2); border: none; border-radius: 50%; padding: 0.75rem; cursor: pointer; transition: all 0.2s;">
                                    <svg style="width: 1.5rem; height: 1.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </button>
                                
                                <!-- Next Button -->
                                <button onclick="nextPhoto()" 
                                        style="position: absolute; right: 1.5rem; top: 50%; transform: translateY(-50%); z-index: 10000; background: rgba(255,255,255,0.2); border: none; border-radius: 50%; padding: 0.75rem; cursor: pointer; transition: all 0.2s;">
                                    <svg style="width: 1.5rem; height: 1.5rem; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </button>
                                
                                <!-- Main Image -->
                                <div style="max-width: 90%; max-height: 90%; display: flex; align-items: center; justify-content: center;">
                                    <img id="modal-image" src="" alt="" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 0.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.5);">
                                </div>
                                
                                <!-- Photo Counter -->
                                <div style="position: absolute; bottom: 1.5rem; left: 50%; transform: translateX(-50%); background: rgba(0,0,0,0.7); color: white; padding: 0.5rem 1rem; border-radius: 1.5rem; font-size: 0.875rem;">
                                    <span id="photo-counter">1 / <?php echo count($accommodation['photos']); ?></span>
                                </div>
                                
                                <!-- Thumbnails -->
                                <div style="position: absolute; bottom: 5rem; left: 50%; transform: translateX(-50%); display: flex; gap: 0.5rem; max-width: 20rem; overflow-x: auto; padding: 0.5rem;" id="thumbnails-container">
                                    <?php foreach ($accommodation['photos'] as $index => $photo_url): ?>
                                        <?php 
                                        $fixed_photo_url = $photo_url;
                                        if (strpos($fixed_photo_url, 'http') !== 0) {
                                            if (strpos($fixed_photo_url, '/uploads/') === 0) {
                                                $fixed_photo_url = 'http://localhost:2121' . $fixed_photo_url;
                                            }
                                        }
                                        ?>
                                        <img src="<?php echo esc_url($fixed_photo_url); ?>" 
                                             alt="Thumbnail <?php echo $index + 1; ?>"
                                             style="width: 4rem; height: 4rem; object-fit: cover; border-radius: 0.25rem; cursor: pointer; opacity: 0.5; transition: opacity 0.2s; flex-shrink: 0;"
                                             class="thumbnail"
                                             data-index="<?php echo $index; ?>"
                                             onclick="goToPhoto(<?php echo $index; ?>)"
                                             onmouseover="this.style.opacity='1'"
                                             onmouseout="this.style.opacity='0.5'">
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <!-- No photos available -->
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Geen foto's beschikbaar</h3>
                            <p class="text-gray-600">Er zijn nog geen extra foto's toegevoegd voor deze accommodatie.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Host Section -->
                <div class="card">
                    <div class="p-8">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="host-avatar">
                                <span class="text-white font-semibold text-xl">
                                    <?php echo esc_html(substr($business_settings['name'] ?? 'Host', 0, 1)); ?>
                                </span>
                            </div>
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">Gehost door <?php echo esc_html($business_settings['name'] ?? 'Onze host'); ?></h3>
                                <p class="text-gray-600">Superhost · 5 jaar host</p>
                            </div>
                        </div>
                        <p class="text-gray-700 leading-relaxed">
                            Welkom bij <?php echo esc_html($business_settings['name'] ?? 'onze accommodatie'); ?>! 
                            We zorgen ervoor dat uw verblijf onvergetelijk wordt met persoonlijke service en aandacht voor detail.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column - Booking Sidebar -->
            <div class="lg:col-span-1">
                <!-- CRITICAL: Sticky wrapper zoals concurrent -->
                <div class="sticky top-6">
                    <div class="booking-card">
                    
                    <!-- Price Header -->
                    <div class="flex items-baseline gap-2 mb-6">
                        <span class="accommodation-price">€<?php echo number_format($accommodation['base_price'], 0); ?></span>
                        <span class="text-gray-600">per nacht</span>
                    </div>

                    <!-- Booking Form -->
                    <div class="space-y-4 mb-6">
                        <!-- Dates -->
                        <div class="grid grid-cols-2 gap-2">
                            <div class="space-y-2">
                                <label class="form-label">Check-in</label>
                                <div class="relative">
                                    <input type="date" id="modal-checkin" class="form-input pl-10">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="form-label">Check-out</label>
                                <div class="relative">
                                    <input type="date" id="modal-checkout" class="form-input pl-10">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Guests -->
                        <div class="space-y-2">
                            <label class="form-label">Gasten</label>
                            <div class="relative">
                                <select id="modal-guests" class="form-input pl-10 appearance-none">
                                    <option value="1">1 gast</option>
                                    <option value="2" selected>2 gasten</option>
                                    <option value="3">3 gasten</option>
                                    <option value="4">4 gasten</option>
                                    <option value="5">5 gasten</option>
                                    <option value="6">6 gasten</option>
                                </select>
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Breakdown -->
                    <div class="booking-summary mb-6">
                        <div class="flex justify-between text-sm">
                            <span>€<?php echo number_format($accommodation['base_price'], 0); ?> × <span id="nights-count">0</span> nachten</span>
                            <span id="base-total">€0</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Schoonmaakkosten</span>
                            <span>€25</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span>Servicekosten</span>
                            <span id="service-fee">€0</span>
                        </div>
                        <div class="booking-total">
                            <span>Totaal</span>
                            <span id="total-price">€0</span>
                        </div>
                    </div>

                    <!-- Reserve Button -->
                    <button id="reserve-btn" class="btn-primary w-full py-4 text-lg">
                        Reserveren
                    </button>

                    <p class="text-center text-sm text-gray-500 mt-4">
                        Je wordt nog niet meteen in rekening gebracht
                    </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
// Photo Gallery Modal
const photos = <?php echo json_encode(array_map(function($url) {
    return strpos($url, 'http') !== 0 && strpos($url, '/uploads/') === 0 ? 'http://localhost:2121' . $url : $url;
}, $accommodation['photos'] ?? [])); ?>;

let currentPhotoIndex = 0;

function openPhotoModal(index = 0) {
    currentPhotoIndex = index;
    const modal = document.getElementById('photo-modal');
    const modalImage = document.getElementById('modal-image');
    const photoCounter = document.getElementById('photo-counter');
    
    if (photos.length > 0) {
        modalImage.src = photos[currentPhotoIndex];
        photoCounter.textContent = `${currentPhotoIndex + 1} / ${photos.length}`;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        updateThumbnails();
    }
}

function closePhotoModal() {
    const modal = document.getElementById('photo-modal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function nextPhoto() {
    currentPhotoIndex = (currentPhotoIndex + 1) % photos.length;
    updateModalPhoto();
}

function previousPhoto() {
    currentPhotoIndex = (currentPhotoIndex - 1 + photos.length) % photos.length;
    updateModalPhoto();
}

function goToPhoto(index) {
    currentPhotoIndex = index;
    updateModalPhoto();
}

function updateModalPhoto() {
    const modalImage = document.getElementById('modal-image');
    const photoCounter = document.getElementById('photo-counter');
    
    modalImage.src = photos[currentPhotoIndex];
    photoCounter.textContent = `${currentPhotoIndex + 1} / ${photos.length}`;
    updateThumbnails();
}

function updateThumbnails() {
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach((thumb, index) => {
        if (index === currentPhotoIndex) {
            thumb.style.opacity = '1';
            thumb.style.border = '2px solid white';
        } else {
            thumb.style.opacity = '0.5';
            thumb.style.border = 'none';
        }
    });
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const modal = document.getElementById('photo-modal');
    if (modal.style.display === 'flex') {
        if (e.key === 'Escape') {
            closePhotoModal();
        } else if (e.key === 'ArrowRight') {
            nextPhoto();
        } else if (e.key === 'ArrowLeft') {
            previousPhoto();
        }
    }
});

// Header navigation functions
function toggleFavorite(accommodationId) {
    // Get favorites from localStorage
    let favorites = JSON.parse(localStorage.getItem('zzbp_favorites') || '[]');
    
    if (favorites.includes(accommodationId)) {
        // Remove from favorites
        favorites = favorites.filter(id => id !== accommodationId);
        alert('Verwijderd van favorieten');
    } else {
        // Add to favorites
        favorites.push(accommodationId);
        alert('Toegevoegd aan favorieten');
    }
    
    // Save back to localStorage
    localStorage.setItem('zzbp_favorites', JSON.stringify(favorites));
    
    // Update heart icon color
    updateFavoriteIcon(accommodationId);
}

function updateFavoriteIcon(accommodationId) {
    let favorites = JSON.parse(localStorage.getItem('zzbp_favorites') || '[]');
    const heartButton = document.querySelector('button[onclick*="toggleFavorite"]');
    const heartSvg = heartButton?.querySelector('svg');
    
    if (heartSvg && favorites.includes(accommodationId)) {
        heartSvg.style.fill = '#f43f5e';
        heartSvg.style.stroke = '#f43f5e';
    }
}

// Modern booking functionality
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Update favorite icon on page load
    updateFavoriteIcon('<?php echo esc_js($accommodation['id']); ?>');
    const checkinInput = document.getElementById('modal-checkin');
    const checkoutInput = document.getElementById('modal-checkout');
    const guestsSelect = document.getElementById('modal-guests');
    const reserveBtn = document.getElementById('reserve-btn');
    
    const basePrice = <?php echo $accommodation['base_price']; ?>;
    const accommodationId = '<?php echo esc_js($accommodation['id']); ?>';
    const accommodationName = '<?php echo esc_js($accommodation['name']); ?>';
    
    // Set minimum dates
    const today = new Date().toISOString().split('T')[0];
    const tomorrow = new Date(Date.now() + 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    
    checkinInput.min = today;
    checkoutInput.min = tomorrow;
    
    // Update pricing when dates change
    function updatePricing() {
        const checkin = new Date(checkinInput.value);
        const checkout = new Date(checkoutInput.value);
        
        if (checkin && checkout && checkout > checkin) {
            const nights = Math.ceil((checkout - checkin) / (1000 * 60 * 60 * 24));
            const baseTotal = basePrice * nights;
            const serviceFee = Math.round(baseTotal * 0.08);
            const total = baseTotal + 25 + serviceFee;
            
            document.getElementById('nights-count').textContent = nights;
            document.getElementById('base-total').textContent = `€${baseTotal}`;
            document.getElementById('service-fee').textContent = `€${serviceFee}`;
            document.getElementById('total-price').textContent = `€${total}`;
            
            reserveBtn.disabled = false;
            reserveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            document.getElementById('nights-count').textContent = '0';
            document.getElementById('base-total').textContent = '€0';
            document.getElementById('service-fee').textContent = '€0';
            document.getElementById('total-price').textContent = '€0';
            
            reserveBtn.disabled = true;
            reserveBtn.classList.add('opacity-50', 'cursor-not-allowed');
        }
    }
    
    // Update checkout minimum when checkin changes
    checkinInput.addEventListener('change', function() {
        if (this.value) {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            checkoutInput.min = nextDay.toISOString().split('T')[0];
            
            if (checkoutInput.value && checkoutInput.value <= this.value) {
                checkoutInput.value = nextDay.toISOString().split('T')[0];
            }
        }
        updatePricing();
    });
    
    checkoutInput.addEventListener('change', updatePricing);
    guestsSelect.addEventListener('change', updatePricing);
    
    // Reserve button functionality
    reserveBtn.addEventListener('click', function() {
        if (this.disabled) return;
        
        const checkin = checkinInput.value;
        const checkout = checkoutInput.value;
        const guests = guestsSelect.value;
        
        if (checkin && checkout) {
            // Store booking data
            const bookingData = {
                accommodation_id: accommodationId,
                accommodation_name: accommodationName,
                check_in: checkin,
                check_out: checkout,
                guests: guests,
                nights: Math.ceil((new Date(checkout) - new Date(checkin)) / (1000 * 60 * 60 * 24))
            };
            
            localStorage.setItem('zzbp_booking_data', JSON.stringify(bookingData));
            
            // Redirect to booking page
            window.location.href = '<?php echo esc_url(home_url('/reserveren/')); ?>';
        }
    });
    
    // Initialize pricing
    updatePricing();
});
</script>

<?php get_footer(); ?>
