/**
 * Settings Core JavaScript
 * Handles tab switching, notifications, and core functionality
 */

// Import will be done dynamically
let apiClient;

export class SettingsCore {
    constructor() {
        this.currentTab = 'bedrijfsinfo';
        this.init();
    }

    /**
     * Initialize the settings system
     */
    async init() {
        try {
            // Load API client
            const apiModule = await import('./apiClient.js');
            apiClient = apiModule.apiClient;
            
            // Wait a bit to ensure all methods are available
            await new Promise(resolve => setTimeout(resolve, 10));
            
            this.bindEvents();
            this.makeGloballyAvailable();
            this.loadInitialContent();
            
            console.log('Settings core initialized');
        } catch (error) {
            console.error('Failed to initialize settings core:', error);
        }
    }

    /**
     * Bind global events
     */
    bindEvents() {
        document.addEventListener('DOMContentLoaded', () => {
            this.showTab('bedrijfsinfo');
        });
    }

    /**
     * Make core functions globally available
     */
    makeGloballyAvailable() {
        const self = this;
        
        window.showTab = function(tabName) {
            return self.showTab(tabName);
        };
        
        window.showNotification = function(message, type = 'success') {
            return self.showNotification(message, type);
        };
        
        window.saveSettings = function(section) {
            return self.saveSettings(section);
        };
    }

    /**
     * Show specific settings tab
     */
    showTab(tabName) {
        console.log(`Switching to tab: ${tabName}`);
        
        // Hide all content sections
        const contentSections = document.querySelectorAll('.settings-content');
        console.log(`Found ${contentSections.length} content sections`);
        
        contentSections.forEach(content => {
            content.classList.add('hidden');
            console.log(`Hidden: ${content.id}`);
        });
        
        // Remove active class from all tabs
        document.querySelectorAll('.settings-tab-btn').forEach(btn => {
            btn.classList.remove('bg-primary-100', 'text-primary-700', 'dark:bg-primary-900', 'dark:text-primary-300');
            btn.classList.add('text-gray-700', 'dark:text-gray-300');
        });
        
        // Show selected content
        const contentElement = document.getElementById(`content-${tabName}`);
        if (contentElement) {
            contentElement.classList.remove('hidden');
            console.log(`Shown: content-${tabName}`);
        } else {
            console.error(`Content element not found: content-${tabName}`);
        }
        
        // Add active class to selected tab
        const activeTab = document.getElementById(`tab-${tabName}`);
        if (activeTab) {
            activeTab.classList.add('bg-primary-100', 'text-primary-700', 'dark:bg-primary-900', 'dark:text-primary-300');
            activeTab.classList.remove('text-gray-700', 'dark:text-gray-300');
            console.log(`Activated tab: tab-${tabName}`);
        } else {
            console.error(`Tab element not found: tab-${tabName}`);
        }
        
        // Load specific tab functionality
        if (tabName === 'accommodatie') {
            // Load accommodaties when accommodatie tab is shown
            if (window.loadAccommodaties) {
                window.loadAccommodaties();
            }
        }
        
        this.currentTab = tabName;
    }


    /**
     * Load initial content
     */
    loadInitialContent() {
        // Load bedrijfsinfo by default
        setTimeout(() => {
            this.showTab('bedrijfsinfo');
        }, 100);
    }

    /**
     * Show notification message
     */
    showNotification(message, type = 'success') {
        const notification = document.getElementById('notification');
        const alertDiv = document.getElementById('notification-content');
        const messageSpan = document.getElementById('notification-message');
        
        if (!notification || !alertDiv || !messageSpan) return;
        
        messageSpan.textContent = message;
        
        // Set notification style based on type
        if (type === 'success') {
            alertDiv.className = 'flex items-center p-4 mb-4 text-sm rounded-lg shadow-lg text-green-800 bg-green-50 dark:bg-gray-800 dark:text-green-400';
        } else if (type === 'error') {
            alertDiv.className = 'flex items-center p-4 mb-4 text-sm rounded-lg shadow-lg text-red-800 bg-red-50 dark:bg-gray-800 dark:text-red-400';
        } else {
            alertDiv.className = 'flex items-center p-4 mb-4 text-sm rounded-lg shadow-lg text-blue-800 bg-blue-50 dark:bg-gray-800 dark:text-blue-400';
        }
        
        // Show notification
        notification.classList.remove('hidden');
        
        // Hide after 3 seconds
        setTimeout(() => {
            notification.classList.add('hidden');
        }, 3000);
    }

    /**
     * Save settings for a specific section
     */
    async saveSettings(section) {
        try {
            console.log(`Saving settings for section: ${section}`);
            // This will be implemented by specific handlers
            this.showNotification(`${section} instellingen opgeslagen!`, 'success');
        } catch (error) {
            console.error('Error saving settings:', error);
            this.showNotification('Fout bij het opslaan van instellingen', 'error');
        }
    }

    /**
     * Make API request
     */
    async makeApiRequest(endpoint, method = 'GET', data = null) {
        const url = endpoint.startsWith('http') ? endpoint : `/api${endpoint}`;
        
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
            }
        };

        if (data && ['POST', 'PUT', 'PATCH'].includes(method)) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(url, options);
            const result = await response.json();
            
            if (!response.ok) {
                throw new Error(result.message || `HTTP error! status: ${response.status}`);
            }
            
            return result;
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }

    /**
     * Validate form data
     */
    validateForm(formData, requiredFields = []) {
        const errors = [];
        
        requiredFields.forEach(field => {
            if (!formData[field] || formData[field].trim() === '') {
                errors.push(`${field} is verplicht`);
            }
        });

        return {
            isValid: errors.length === 0,
            errors
        };
    }

    /**
     * Format currency
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('nl-NL', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    }

    /**
     * Format date
     */
    formatDate(date) {
        return new Intl.DateTimeFormat('nl-NL').format(new Date(date));
    }

    /**
     * Debounce function
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

// Create singleton instance
const settingsCore = new SettingsCore();

// Export functions for external use
export async function initSettings() {
    await settingsCore.init();
    return settingsCore;
}

export function showNotification(message, type = 'success') {
    return settingsCore.showNotification(message, type);
}

export function showTab(tabName) {
    return settingsCore.showTab(tabName);
}

export function saveSettings(section) {
    return settingsCore.saveSettings(section);
}

export default settingsCore;
