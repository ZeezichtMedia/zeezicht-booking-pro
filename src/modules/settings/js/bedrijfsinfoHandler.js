/**
 * Bedrijfsinfo Handler
 * Handles company information settings logic
 */

export class BedrijfsinfoHandler {
    constructor() {
        this.settings = null;
        this.urlMappings = {
            'bnb': 'kamers',
            'minicamping': 'accommodaties',
            'camping': 'kampeerplaatsen',
            'hotel': 'kamers',
            'vakantiepark': 'accommodaties',
            'glamping': 'accommodaties',
            'hostel': 'kamers'
        };
    }

    /**
     * Initialize the bedrijfsinfo handler
     */
    async init() {
        try {
            // Load existing settings
            await this.loadSettings();
            
            // Bind events
            this.bindEvents();
            
            // Update URL preview
            this.updateUrlPreview();

            console.log('Bedrijfsinfo handler initialized');
        } catch (error) {
            console.error('Failed to initialize bedrijfsinfo handler:', error);
        }
    }

    /**
     * Render the bedrijfsinfo content
     */
    async render(container) {
        try {
            // Load the component HTML
            const response = await fetch('/src/modules/settings/BedrijfsinfoSettings.astro');
            const html = await response.text();
            container.innerHTML = html;

            // Load existing settings
            await this.loadSettings();
            
            // Bind events
            this.bindEvents();
            
            // Update URL preview
            this.updateUrlPreview();

        } catch (error) {
            console.error('Error rendering bedrijfsinfo:', error);
            container.innerHTML = '<p class="p-8 text-center text-red-500">Fout bij het laden van bedrijfsinfo</p>';
        }
    }

    /**
     * Load existing settings from API
     */
    async loadSettings() {
        try {
            const response = await fetch('/api/settings');
            const result = await response.json();
            
            if (result.success && result.data) {
                this.settings = result.data;
                this.populateForm();
            }
        } catch (error) {
            console.error('Error loading settings:', error);
        }
    }

    /**
     * Populate form with existing data
     */
    populateForm() {
        if (!this.settings || !this.settings.bedrijfsinfo) return;

        const bedrijf = this.settings.bedrijfsinfo;
        
        // Populate basic fields
        this.setFieldValue('bedrijf-naam', bedrijf.bedrijf_naam);
        this.setFieldValue('business-type', bedrijf.business_type);
        this.setFieldValue('contact-email', bedrijf.contact_email);
        this.setFieldValue('bedrijf-telefoon', bedrijf.bedrijf_telefoon);
        this.setFieldValue('bedrijf-adres', bedrijf.bedrijf_adres);
        this.setFieldValue('website-url', bedrijf.website_url);
        this.setFieldValue('bedrijf-beschrijving', bedrijf.bedrijf_beschrijving);
    }

    /**
     * Set field value safely
     */
    setFieldValue(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (field && value) {
            field.value = value;
        }
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Business type change - update URL preview
        const businessTypeField = document.getElementById('business-type');
        if (businessTypeField) {
            businessTypeField.addEventListener('change', () => {
                this.updateUrlPreview();
            });
        }

        // Website URL change - update preview
        const websiteUrlField = document.getElementById('website-url');
        if (websiteUrlField) {
            websiteUrlField.addEventListener('input', () => {
                this.updateUrlPreview();
            });
        }

        // Make save function globally available
        window.saveBedrijfsinfo = this.save.bind(this);
    }

    /**
     * Update URL preview based on business type and website URL
     */
    updateUrlPreview() {
        const businessType = document.getElementById('business-type')?.value;
        const websiteUrl = document.getElementById('website-url')?.value;
        const previewContainer = document.getElementById('url-preview');
        
        if (!businessType || !websiteUrl || !previewContainer) {
            previewContainer?.classList.add('hidden');
            return;
        }

        const urlBase = this.urlMappings[businessType] || 'accommodaties';
        const baseUrl = websiteUrl.replace(/\/$/, ''); // Remove trailing slash

        // Update preview URLs
        const accommodationsPreview = document.getElementById('preview-accommodations-url');
        const bookingPreview = document.getElementById('preview-booking-url');

        if (accommodationsPreview) {
            accommodationsPreview.textContent = `${baseUrl}/${urlBase}/`;
        }

        if (bookingPreview) {
            bookingPreview.textContent = `${baseUrl}/reserveren/`;
        }

        previewContainer.classList.remove('hidden');
    }

    /**
     * Save bedrijfsinfo settings
     */
    async save() {
        try {
            const formData = this.getFormData();
            
            // Validate required fields
            const validation = this.validateForm(formData);
            if (!validation.isValid) {
                window.showNotification(`Validatie fout: ${validation.errors.join(', ')}`, 'error');
                return;
            }

            // Save to API
            const response = await fetch('/api/settings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    section: 'bedrijfsinfo',
                    data: formData
                })
            });

            const result = await response.json();

            if (result.success) {
                window.showNotification('Bedrijfsinfo succesvol opgeslagen!', 'success');
                this.settings = result.data;
            } else {
                window.showNotification(`Fout bij opslaan: ${result.message}`, 'error');
            }

        } catch (error) {
            console.error('Error saving bedrijfsinfo:', error);
            window.showNotification('Er is een fout opgetreden bij het opslaan', 'error');
        }
    }

    /**
     * Get form data
     */
    getFormData() {
        return {
            bedrijf_naam: document.getElementById('bedrijf-naam')?.value || '',
            business_type: document.getElementById('business-type')?.value || '',
            contact_email: document.getElementById('contact-email')?.value || '',
            bedrijf_telefoon: document.getElementById('bedrijf-telefoon')?.value || '',
            bedrijf_adres: document.getElementById('bedrijf-adres')?.value || '',
            website_url: document.getElementById('website-url')?.value || '',
            bedrijf_beschrijving: document.getElementById('bedrijf-beschrijving')?.value || ''
        };
    }

    /**
     * Validate form data
     */
    validateForm(data) {
        const errors = [];
        
        if (!data.bedrijf_naam.trim()) {
            errors.push('Bedrijfsnaam is verplicht');
        }
        
        if (!data.business_type) {
            errors.push('Type bedrijf is verplicht');
        }
        
        if (!data.contact_email.trim()) {
            errors.push('Contact email is verplicht');
        } else if (!this.isValidEmail(data.contact_email)) {
            errors.push('Contact email is niet geldig');
        }

        if (data.website_url && !this.isValidUrl(data.website_url)) {
            errors.push('Website URL is niet geldig');
        }

        return {
            isValid: errors.length === 0,
            errors
        };
    }

    /**
     * Validate email format
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Validate URL format
     */
    isValidUrl(url) {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }
}

// Create singleton instance
const bedrijfsinfoHandler = new BedrijfsinfoHandler();

// Export functions for external use
export async function initBedrijfsinfo() {
    await bedrijfsinfoHandler.init();
    return bedrijfsinfoHandler;
}

export default bedrijfsinfoHandler;
