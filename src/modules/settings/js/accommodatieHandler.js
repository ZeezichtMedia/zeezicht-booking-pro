/**
 * Accommodatie Handler Module
 * Handles all accommodation-related operations
 */

// Import will be done dynamically to avoid circular dependencies
let apiClient, showNotification;

// State management
let accommodaties = [];
let currentAccommodatieId = null;

// Amenities configuration
const AMENITIES = [
    '10A Stroom', '16A Stroom', 'Water aansluiting', 'Riool aansluiting',
    'Gratis WiFi', 'Kabeltelevisie', 'Vlonders', 'Zeezicht',
    'Balkon/Terras', 'Ontbijt', 'Airconditioning', 'Parkeerplaats'
];

/**
 * Initialize accommodatie functionality
 */
export async function initAccommodatie() {
    // Load dependencies
    try {
        const [apiModule, coreModule] = await Promise.all([
            import('./apiClient.js'),
            import('./settingsCore.js')
        ]);
        
        apiClient = apiModule.apiClient;
        showNotification = coreModule.showNotification;
        
        loadAmenities();
        
        // Make functions globally available
        window.openAccommodatieModal = openAccommodatieModalEnhanced;
        window.closeAccommodatieModal = closeAccommodatieModal;
        window.saveAccommodatie = saveAccommodatie;
        window.editAccommodatie = editAccommodatie;
        window.deleteAccommodatie = deleteAccommodatie;
        window.openPricingOptions = openPricingOptions;
        window.managePhotos = managePhotos;
        window.loadAccommodaties = loadAccommodaties;
        window.switchAccommodatieTab = switchAccommodatieTab;
        window.confirmDeleteAccommodatie = confirmDeleteAccommodatie;
        window.openAccommodatieModalEnhanced = openAccommodatieModalEnhanced;
        window.deleteImage = deleteImage;
        
        console.log('Accommodatie handler initialized');
    } catch (error) {
        console.error('Failed to initialize accommodatie handler:', error);
    }
}

/**
 * Load amenities checkboxes
 */
function loadAmenities() {
    const grid = document.getElementById('amenities-grid');
    if (!grid) return;
    
    grid.innerHTML = AMENITIES.map(amenity => `
        <label class="flex items-center">
            <input type="checkbox" class="w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
            <span class="ml-2 text-sm text-gray-900 dark:text-white">${amenity}</span>
        </label>
    `).join('');
}

/**
 * Open accommodatie modal
 */
function openAccommodatieModal(id = null) {
    currentAccommodatieId = id;
    const modal = document.getElementById('accommodatie-modal');
    const title = document.getElementById('modal-title');
    
    if (id) {
        title.textContent = 'Accommodatie Bewerken';
        loadAccommodatieData(id);
    } else {
        title.textContent = 'Nieuwe Accommodatie';
        clearAccommodatieForm();
    }
    
    modal.classList.remove('hidden');
}

/**
 * Close accommodatie modal
 */
function closeAccommodatieModal() {
    document.getElementById('accommodatie-modal').classList.add('hidden');
    currentAccommodatieId = null;
}

/**
 * Load accommodatie data for editing
 */
function loadAccommodatieData(id) {
    console.log('Loading accommodatie data for ID:', id);
    
    const acc = accommodaties.find(a => a.id === id || a.id == id);
    if (!acc) {
        console.error('Accommodatie not found with id:', id);
        return;
    }
    
    // Set form values
    setFieldValue('acc-naam', acc.name);
    setFieldValue('acc-type', acc.type);
    setFieldValue('acc-max-gasten', acc.max_guests);
    setFieldValue('acc-oppervlakte', acc.surface_area || '');
    setFieldValue('acc-prijs', acc.base_price);
    setFieldValue('acc-status', acc.active ? 'actief' : 'inactief');
    setFieldValue('acc-beschrijving', acc.description);
    setFieldValue('acc-extra-info', acc.extra_info);
    
    // Set amenities checkboxes
    const checkboxes = document.querySelectorAll('#accommodatie-modal input[type="checkbox"]');
    checkboxes.forEach(cb => {
        const label = cb.nextElementSibling ? cb.nextElementSibling.textContent.trim() : '';
        const isChecked = acc.amenities && acc.amenities.includes(label);
        cb.checked = isChecked;
    });
}

/**
 * Clear accommodatie form
 */
function clearAccommodatieForm() {
    setFieldValue('acc-naam', '');
    setFieldValue('acc-type', '');
    setFieldValue('acc-max-gasten', '');
    setFieldValue('acc-oppervlakte', '');
    setFieldValue('acc-prijs', '');
    setFieldValue('acc-status', 'actief');
    setFieldValue('acc-beschrijving', '');
    setFieldValue('acc-extra-info', '');
    
    // Clear checkboxes
    document.querySelectorAll('#accommodatie-modal input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
    });
}

/**
 * Save accommodatie
 */
async function saveAccommodatie() {
    const formData = collectFormData();
    
    // Validate required fields
    if (!formData.naam || !formData.type || !formData.maxGasten || !formData.prijs || !formData.beschrijving) {
        showNotification('Vul alle verplichte velden in', 'error');
        return;
    }
    
    try {
        let response;
        let successMessage;
        
        if (currentAccommodatieId) {
            formData.id = currentAccommodatieId;
            response = await apiClient.put('/api/accommodaties', formData);
            successMessage = 'Accommodatie bijgewerkt!';
        } else {
            response = await apiClient.post('/api/accommodaties', formData);
            successMessage = 'Nieuwe accommodatie aangemaakt!';
        }
        
        await loadAccommodaties();
        showNotification(successMessage, 'success');
        closeAccommodatieModal();
        
    } catch (error) {
        console.error('Error saving accommodatie:', error);
        showNotification('Fout bij het opslaan van accommodatie', 'error');
    }
}

/**
 * Edit accommodatie
 */
function editAccommodatie(id) {
    openAccommodatieModal(id);
}

/**
 * Delete accommodatie
 */
async function deleteAccommodatie(id) {
    if (confirm('Weet je zeker dat je deze accommodatie wilt verwijderen?')) {
        try {
            await apiClient.delete(`/api/accommodaties?id=${id}`);
            await loadAccommodaties();
            showNotification('Accommodatie verwijderd', 'success');
        } catch (error) {
            console.error('Error deleting accommodatie:', error);
            showNotification('Fout bij het verwijderen van accommodatie', 'error');
        }
    }
}

/**
 * Open pricing options
 */
function openPricingOptions(accommodationId) {
    // This will be handled by pricing options module
    window.location.href = `/beheer/accommodatie-opties?accommodation=${accommodationId}`;
}

/**
 * Manage photos
 */
function managePhotos(accommodationId) {
    window.location.href = `/beheer/accommodatie-fotos?accommodation=${accommodationId}`;
}

/**
 * Load accommodaties from API
 */
export async function loadAccommodaties() {
    try {
        const result = await apiClient.get('/api/accommodaties');
        
        if (result.success) {
            accommodaties = result.data;
            renderAccommodatiesList();
        }
    } catch (error) {
        console.error('Error loading accommodaties:', error);
    }
}

/**
 * Render accommodaties list
 */
function renderAccommodatiesList() {
    const container = document.getElementById('accommodaties-list');
    if (!container) return;
    
    if (accommodaties.length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-8">Nog geen accommodaties toegevoegd.</p>';
        return;
    }
    
    container.innerHTML = accommodaties.map(acc => `
        <div class="border border-gray-200 rounded-lg p-4 dark:border-gray-600">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">${acc.name}</h4>
                        <span class="bg-${acc.active ? 'green' : 'red'}-100 text-${acc.active ? 'green' : 'red'}-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-${acc.active ? 'green' : 'red'}-900 dark:text-${acc.active ? 'green' : 'red'}-300">
                            ${acc.active ? 'Actief' : 'Inactief'}
                        </span>
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
                            ${getTypeLabel(acc.type)}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                        <div class="text-sm">
                            <span class="font-medium text-gray-500 dark:text-gray-400">Max. gasten:</span>
                            <span class="text-gray-900 dark:text-white ml-1">${acc.max_guests}</span>
                        </div>
                        <div class="text-sm">
                            <span class="font-medium text-gray-500 dark:text-gray-400">Oppervlakte:</span>
                            <span class="text-gray-900 dark:text-white ml-1">${acc.surface_area} m²</span>
                        </div>
                        <div class="text-sm">
                            <span class="font-medium text-gray-500 dark:text-gray-400">Prijs/nacht:</span>
                            <span class="text-gray-900 dark:text-white ml-1">€${parseFloat(acc.base_price).toFixed(2)}</span>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">${acc.description}</p>
                    <div class="flex flex-wrap gap-1">
                        ${(acc.amenities || []).map(amenity => 
                            `<span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded dark:bg-gray-700 dark:text-gray-300">${amenity}</span>`
                        ).join('')}
                    </div>
                </div>
                <div class="flex flex-col gap-2 ml-4">
                    <button onclick="openAccommodatieModalEnhanced('${acc.id}')" class="text-primary-600 hover:text-primary-700 text-sm font-medium whitespace-nowrap">Bewerken</button>
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * Helper functions
 */
function setFieldValue(fieldId, value) {
    const field = document.getElementById(fieldId);
    if (field) {
        field.value = value || '';
    }
}

function collectFormData() {
    const voorzieningen = [];
    document.querySelectorAll('#accommodatie-modal input[type="checkbox"]:checked').forEach(cb => {
        const label = cb.nextElementSibling ? cb.nextElementSibling.textContent.trim() : '';
        if (label) {
            voorzieningen.push(label);
        }
    });
    
    return {
        naam: document.getElementById('acc-naam').value,
        type: document.getElementById('acc-type').value,
        maxGasten: parseInt(document.getElementById('acc-max-gasten').value),
        oppervlakte: document.getElementById('acc-oppervlakte').value || null,
        prijs: parseFloat(document.getElementById('acc-prijs').value),
        status: document.getElementById('acc-status').value,
        beschrijving: document.getElementById('acc-beschrijving').value,
        extraInfo: document.getElementById('acc-extra-info').value,
        voorzieningen: voorzieningen
    };
}

function getTypeLabel(type) {
    const labels = {
        'kampeerplaats': 'Kampeerplaats',
        'bnb-kamer': 'B&B Kamer',
        'chalet': 'Chalet',
        'stacaravan': 'Stacaravan',
        'glamping': 'Glamping'
    };
    return labels[type] || type;
}

// Initialize when module is loaded
initAccommodatie();

/**
 * Switch between tabs in the accommodatie modal
 */
function switchAccommodatieTab(tabName) {
    console.log(`Switching to accommodatie tab: ${tabName}`);
    
    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-primary-600', 'text-primary-600');
        button.classList.add('border-transparent');
    });
    
    // Show selected tab
    const selectedTab = document.getElementById(`${tabName}-tab`);
    if (selectedTab) {
        selectedTab.classList.remove('hidden');
    }
    
    // Add active class to selected button
    const activeButton = document.querySelector(`[data-tab="${tabName}"]`);
    if (activeButton) {
        activeButton.classList.add('active', 'border-primary-600', 'text-primary-600');
        activeButton.classList.remove('border-transparent');
    }
    
    // Load tab-specific content
    if (tabName === 'fotos') {
        loadAccommodatieImages();
        setupImageUpload();
    }
}

/**
 * Load images for current accommodatie
 */
async function loadAccommodatieImages() {
    const currentAccommodatieId = document.getElementById('accommodatie-form').dataset.accommodatieId;
    if (!currentAccommodatieId) return;
    
    try {
        const response = await fetch(`/api/accommodation-images?id=${currentAccommodatieId}`);
        const result = await response.json();
        
        if (result.success) {
            renderImageGrid(result.data);
        }
    } catch (error) {
        console.error('Error loading images:', error);
    }
}

/**
 * Render image grid
 */
function renderImageGrid(images) {
    const grid = document.getElementById('current-images-grid');
    if (!grid) return;
    
    grid.innerHTML = images.map(image => `
        <div class="relative group">
            <img src="${image.url}" alt="${image.alt_text || 'Accommodatie foto'}" 
                 class="w-full h-32 object-cover rounded-lg">
            <div class="absolute inset-0 bg-black bg-opacity-50 opacity-0 group-hover:opacity-100 transition-opacity rounded-lg flex items-center justify-center">
                <button onclick="deleteImage('${image.id}')" 
                        class="text-white hover:text-red-300 p-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8 7a1 1 0 012 0v4a1 1 0 11-2 0V7zM12 7a1 1 0 10-2 0v4a1 1 0 102 0V7z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            ${image.is_primary ? '<div class="absolute top-2 left-2 bg-green-500 text-white text-xs px-2 py-1 rounded">Hoofdfoto</div>' : ''}
        </div>
    `).join('');
}

/**
 * Setup image upload functionality
 */
function setupImageUpload() {
    const uploadInput = document.getElementById('image-upload');
    if (!uploadInput) return;
    
    uploadInput.addEventListener('change', async (event) => {
        const files = event.target.files;
        if (!files || files.length === 0) return;
        
        const currentAccommodatieId = document.getElementById('accommodatie-form').dataset.accommodatieId;
        if (!currentAccommodatieId) {
            showNotification('Sla eerst de accommodatie op voordat je foto\'s uploadt', 'error');
            return;
        }
        
        for (const file of files) {
            await uploadImage(file, currentAccommodatieId);
        }
        
        // Reload images after upload
        loadAccommodatieImages();
    });
}

/**
 * Upload single image
 */
async function uploadImage(file, accommodationId) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('accommodation_id', accommodationId);
    
    try {
        const response = await fetch('/api/upload-image', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Foto succesvol geüpload!', 'success');
        } else {
            showNotification(`Upload fout: ${result.error}`, 'error');
        }
    } catch (error) {
        console.error('Upload error:', error);
        showNotification('Fout bij uploaden van foto', 'error');
    }
}

/**
 * Delete image
 */
async function deleteImage(imageId) {
    if (!confirm('Weet je zeker dat je deze foto wilt verwijderen?')) return;
    
    try {
        const response = await fetch('/api/delete-image', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: imageId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Foto verwijderd!', 'success');
            loadAccommodatieImages(); // Reload images
        } else {
            showNotification(`Fout: ${result.error}`, 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showNotification('Fout bij verwijderen van foto', 'error');
    }
}

/**
 * Confirm delete accommodatie
 */
function confirmDeleteAccommodatie() {
    const currentAccommodatieId = document.getElementById('accommodatie-form').dataset.accommodatieId;
    if (!currentAccommodatieId) return;
    
    if (confirm('Weet je zeker dat je deze accommodatie wilt verwijderen? Deze actie kan niet ongedaan worden gemaakt.')) {
        deleteAccommodatie(currentAccommodatieId);
    }
}

/**
 * Update edit accommodatie function to show delete tab for existing accommodations
 */
function editAccommodatieEnhanced(accommodatieId) {
    // Call original edit function
    editAccommodatie(accommodatieId);
    
    // Show delete tab for existing accommodations
    const deleteTabLi = document.getElementById('delete-tab-li');
    if (deleteTabLi) {
        deleteTabLi.style.display = 'block';
    }
    
    // Set accommodatie ID on form for reference
    const form = document.getElementById('accommodatie-form');
    if (form) {
        form.dataset.accommodatieId = accommodatieId;
    }
}

/**
 * Update open modal function to handle new/edit modes
 */
function openAccommodatieModalEnhanced(accommodatieId = null) {
    const modal = document.getElementById('accommodatie-modal');
    const modalTitle = document.getElementById('modal-title');
    const deleteTabLi = document.getElementById('delete-tab-li');
    const form = document.getElementById('accommodatie-form');
    
    if (accommodatieId) {
        // Edit mode
        modalTitle.textContent = 'Accommodatie Bewerken';
        deleteTabLi.style.display = 'block';
        form.dataset.accommodatieId = accommodatieId;
        editAccommodatie(accommodatieId);
    } else {
        // New mode
        modalTitle.textContent = 'Nieuwe Accommodatie';
        deleteTabLi.style.display = 'none';
        form.removeAttribute('data-accommodatie-id');
        clearAccommodatieForm();
    }
    
    // Always start with algemeen tab
    switchAccommodatieTab('algemeen');
    
    modal.classList.remove('hidden');
}

export default {
    initAccommodatie,
    loadAccommodaties,
    openAccommodatieModal,
    closeAccommodatieModal,
    saveAccommodatie,
    editAccommodatie,
    deleteAccommodatie,
    switchAccommodatieTab,
    confirmDeleteAccommodatie
};
