/**
 * API Client Module
 * Centralized API communication with error handling
 */

class ApiClient {
    constructor(baseURL = '') {
        this.baseURL = baseURL;
    }

    /**
     * Generic request method
     */
    async request(url, options = {}) {
        const config = {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };

        try {
            const response = await fetch(this.baseURL + url, config);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('API request failed:', error);
            throw error;
        }
    }

    /**
     * GET request
     */
    async get(url, params = {}) {
        const urlParams = new URLSearchParams(params);
        const queryString = urlParams.toString();
        const fullUrl = queryString ? `${url}?${queryString}` : url;
        
        return this.request(fullUrl, {
            method: 'GET'
        });
    }

    /**
     * POST request
     */
    async post(url, data = {}) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    }

    /**
     * PUT request
     */
    async put(url, data = {}) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    }

    /**
     * DELETE request
     */
    async delete(url) {
        return this.request(url, {
            method: 'DELETE'
        });
    }

    /**
     * Upload file
     */
    async upload(url, formData) {
        try {
            const response = await fetch(this.baseURL + url, {
                method: 'POST',
                body: formData // Don't set Content-Type for FormData
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return await response.json();
        } catch (error) {
            console.error('Upload failed:', error);
            throw error;
        }
    }
}

// Create singleton instance
export const apiClient = new ApiClient();

// Export class for custom instances
export default ApiClient;
