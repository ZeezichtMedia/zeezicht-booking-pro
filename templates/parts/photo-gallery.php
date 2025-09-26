<?php
/**
 * Template Part: Photo Gallery - CLEAN VERSION
 * 
 * Pure HTML structure, all CSS/JS moved to external files
 */

global $zzbp_current_accommodation;
$accommodation = $zzbp_current_accommodation;

if (!$accommodation) {
    return;
}

$photos = $accommodation['photos'] ?? [];

// Get base URL from settings instead of hardcoded localhost
$settings = get_option('zzbp_settings', []);
$base_url = $settings['api_base_url'] ?? 'http://localhost:2121';
?>

<div class="bg-white rounded-2xl p-8 shadow-lg border border-gray-100 mb-8">
    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Foto's</h2>
    
    <?php if (!empty($photos) && count($photos) > 0): ?>
        <!-- Photo Grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="photo-gallery">
            <?php foreach ($photos as $index => $photo_url): ?>
                <?php 
                // Fix photo URL using settings base URL
                $fixed_photo_url = $photo_url;
                if (strpos($fixed_photo_url, 'http') !== 0 && strpos($fixed_photo_url, '/uploads/') === 0) {
                    $fixed_photo_url = $base_url . $fixed_photo_url;
                }
                ?>
                <div class="gallery-item" onclick="openPhotoModal(<?php echo $index; ?>)">
                    <img 
                        src="<?php echo esc_url($fixed_photo_url); ?>" 
                        alt="<?php echo esc_attr($accommodation['name']); ?> foto <?php echo $index + 1; ?>"
                        class="gallery-image"
                        onerror="this.parentElement.style.display='none'"
                    />
                </div>
            <?php endforeach; ?>
        </div>
    
        <!-- Photo count indicator -->
        <div class="mt-4 text-center">
            <span class="text-sm text-gray-600">
                <?php echo count($photos); ?> foto's
            </span>
            <button onclick="openPhotoModal(0)" class="ml-4 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg font-semibold text-sm transition-colors">
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
                    <span id="photo-counter">1 / <?php echo count($photos); ?></span>
                </div>
                
                <!-- Thumbnails -->
                <div style="position: absolute; bottom: 5rem; left: 50%; transform: translateX(-50%); display: flex; gap: 0.5rem; max-width: 20rem; overflow-x: auto; padding: 0.5rem;" id="thumbnails-container">
                    <?php foreach ($photos as $index => $photo_url): ?>
                        <?php 
                        $fixed_photo_url = $photo_url;
                        if (strpos($fixed_photo_url, 'http') !== 0 && strpos($fixed_photo_url, '/uploads/') === 0) {
                            $fixed_photo_url = $base_url . $fixed_photo_url;
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
        
        <!-- Photo data for JavaScript -->
        <script data-zzbp-photos='<?php echo json_encode(array_map(function($url) use ($base_url) {
            return strpos($url, 'http') !== 0 && strpos($url, '/uploads/') === 0 ? $base_url . $url : $url;
        }, $photos)); ?>'></script>
        
    <?php else: ?>
        <p class="text-gray-500 text-center py-8">Geen foto's beschikbaar</p>
    <?php endif; ?>
</div>
