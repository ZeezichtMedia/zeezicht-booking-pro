import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../../lib/supabase';

/**
 * Plugin Authentication API
 * Validates API key and domain for WordPress plugin
 */
export const post: APIRoute = async ({ request, clientAddress }) => {
	try {
		const body = await request.json();
		const { api_key, domain, plugin_version } = body;

		// Validate required fields
		if (!api_key || !domain) {
			return new Response(JSON.stringify({
				success: false,
				error: 'API key and domain are required',
				code: 'MISSING_CREDENTIALS'
			}), {
				status: 400,
				headers: { 'Content-Type': 'application/json' }
			});
		}

		// Get property by API key
		const { data: property, error: propertyError } = await supabaseAdmin
			.from('properties')
			.select('*')
			.eq('api_key', api_key)
			.eq('active', true)
			.single();

		if (propertyError || !property) {
			// Log failed authentication attempt
			await logPluginRequest(null, api_key, domain, '/api/plugin/authenticate', 'POST', clientAddress, request.headers.get('user-agent'), 401);
			
			return new Response(JSON.stringify({
				success: false,
				error: 'Invalid API key',
				code: 'INVALID_API_KEY'
			}), {
				status: 401,
				headers: { 'Content-Type': 'application/json' }
			});
		}

		// Validate domain
		const isValidDomain = validateDomain(domain, property.domain, property.allowed_domains);
		if (!isValidDomain) {
			// Log unauthorized domain attempt
			await logPluginRequest(property.id, api_key, domain, '/api/plugin/authenticate', 'POST', clientAddress, request.headers.get('user-agent'), 403);
			
			return new Response(JSON.stringify({
				success: false,
				error: 'Domain not authorized for this API key',
				code: 'UNAUTHORIZED_DOMAIN',
				authorized_domains: [property.domain, ...(property.allowed_domains || [])]
			}), {
				status: 403,
				headers: { 'Content-Type': 'application/json' }
			});
		}

		// Update last sync time and plugin version
		await supabaseAdmin
			.from('properties')
			.update({
				last_plugin_sync: new Date().toISOString(),
				plugin_version: plugin_version || null,
				updated_at: new Date().toISOString()
			})
			.eq('id', property.id);

		// Log successful authentication
		await logPluginRequest(property.id, api_key, domain, '/api/plugin/authenticate', 'POST', clientAddress, request.headers.get('user-agent'), 200);

		// Return property configuration
		const settings = property.settings || {};
		const businessInfo = settings.bedrijfsinfo || {};
		const websiteSettings = settings.website || {};

		return new Response(JSON.stringify({
			success: true,
			property: {
				id: property.id,
				name: property.name,
				domain: property.domain,
				business_type: businessInfo.business_type || 'minicamping',
				settings: {
					business: {
						name: businessInfo.bedrijf_naam || property.name,
						type: businessInfo.business_type || 'minicamping',
						email: businessInfo.contact_email || '',
						phone: businessInfo.bedrijf_telefoon || '',
						address: businessInfo.bedrijf_adres || '',
						website_url: businessInfo.website_url || ''
					},
					booking: {
						page_title: websiteSettings.booking_page_title || 'Reserveren',
						accommodations_title: websiteSettings.accommodations_page_title || 'Onze Accommodaties',
						min_stay_nights: websiteSettings.min_stay_nights || 2,
						max_advance_days: websiteSettings.max_advance_days || 365,
						checkin_time: websiteSettings.checkin_time || '15:00',
						checkout_time: websiteSettings.checkout_time || '11:00',
						auto_confirmation: websiteSettings.auto_confirmation !== false
					},
					display: {
						layout: websiteSettings.accommodation_layout || 'grid',
						show_prices: websiteSettings.show_prices !== false,
						show_availability: websiteSettings.show_availability !== false
					}
				},
				url_structure: getUrlStructure(businessInfo.business_type || 'minicamping')
			}
		}), {
			status: 200,
			headers: { 'Content-Type': 'application/json' }
		});

	} catch (error) {
		console.error('Plugin authentication error:', error);
		return new Response(JSON.stringify({
			success: false,
			error: 'Internal server error',
			code: 'INTERNAL_ERROR'
		}), {
			status: 500,
			headers: { 'Content-Type': 'application/json' }
		});
	}
};

/**
 * Validate domain against allowed domains
 */
function validateDomain(requestDomain: string, primaryDomain: string, allowedDomains: string[] = []): boolean {
	// Remove protocol and trailing slashes
	const cleanDomain = requestDomain.replace(/^https?:\/\//, '').replace(/\/$/, '');
	
	// Check primary domain
	if (cleanDomain === primaryDomain) return true;
	
	// Check www variant
	if (cleanDomain === `www.${primaryDomain}`) return true;
	if (primaryDomain === `www.${cleanDomain}`) return true;
	
	// Check allowed domains
	for (const allowed of allowedDomains) {
		if (cleanDomain === allowed) return true;
		
		// Support wildcard subdomains
		if (allowed.startsWith('*.')) {
			const baseDomain = allowed.substring(2);
			if (cleanDomain.endsWith(`.${baseDomain}`)) return true;
		}
	}
	
	return false;
}

/**
 * Get URL structure based on business type
 */
function getUrlStructure(businessType: string): { base: string, booking: string } {
	const urlMappings: Record<string, string> = {
		'bnb': 'kamers',
		'minicamping': 'accommodaties',
		'camping': 'kampeerplaatsen',
		'hotel': 'kamers',
		'vakantiepark': 'accommodaties',
		'glamping': 'accommodaties',
		'hostel': 'kamers'
	};

	return {
		base: urlMappings[businessType] || 'accommodaties',
		booking: 'reserveren'
	};
}

/**
 * Log plugin API request
 */
async function logPluginRequest(
	propertyId: string | null,
	apiKey: string,
	domain: string,
	endpoint: string,
	method: string,
	ipAddress: string | null,
	userAgent: string | null,
	responseStatus: number,
	responseTimeMs?: number
) {
	try {
		await supabaseAdmin
			.from('plugin_logs')
			.insert({
				property_id: propertyId,
				api_key: apiKey,
				domain: domain,
				endpoint: endpoint,
				method: method,
				ip_address: ipAddress,
				user_agent: userAgent,
				response_status: responseStatus,
				response_time_ms: responseTimeMs || null
			});
	} catch (error) {
		console.error('Failed to log plugin request:', error);
		// Don't throw - logging failures shouldn't break the main flow
	}
}
