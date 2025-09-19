import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../../lib/supabase';

/**
 * Plugin Accommodations API
 * Returns accommodations data for WordPress plugin
 */
export const get: APIRoute = async ({ url, request, clientAddress }) => {
	try {
		const apiKey = url.searchParams.get('api_key');
		const domain = url.searchParams.get('domain') || request.headers.get('origin') || '';

		// Validate API key and domain
		const authResult = await authenticatePlugin(apiKey, domain);
		if (!authResult.success) {
			return new Response(JSON.stringify(authResult), {
				status: authResult.status || 400,
				headers: { 'Content-Type': 'application/json' }
			});
		}

		const property = authResult.property;

		// Get accommodations with images
		const { data: accommodations, error: accommodationsError } = await supabaseAdmin
			.from('accommodations')
			.select(`
				*,
				accommodation_images (
					id,
					url,
					alt_text,
					is_primary,
					sort_order
				)
			`)
			.eq('property_id', property.id)
			.eq('active', true)
			.order('created_at', { ascending: true });

		if (accommodationsError) {
			console.error('Error fetching accommodations:', accommodationsError);
			return new Response(JSON.stringify({
				success: false,
				error: 'Failed to fetch accommodations'
			}), {
				status: 500,
				headers: { 'Content-Type': 'application/json' }
			});
		}

		// Get pricing options for each accommodation
		const accommodationIds = accommodations.map(acc => acc.id);
		const { data: pricingOptions } = await supabaseAdmin
			.from('accommodation_pricing_options')
			.select('*')
			.in('accommodation_id', accommodationIds)
			.eq('is_active', true)
			.order('sort_order', { ascending: true });

		// Group pricing options by accommodation
		const optionsByAccommodation = (pricingOptions || []).reduce((acc, option) => {
			if (!acc[option.accommodation_id]) {
				acc[option.accommodation_id] = { recurring: [], one_time: [] };
			}
			acc[option.accommodation_id][option.category].push(option);
			return acc;
		}, {});

		// Format accommodations for plugin
		const formattedAccommodations = accommodations.map((acc: any) => {
			// Process images
			const images = (acc.accommodation_images || [])
				.sort((a: any, b: any) => {
					// Primary image first, then by sort_order
					if (a.is_primary && !b.is_primary) return -1;
					if (!a.is_primary && b.is_primary) return 1;
					return (a.sort_order || 0) - (b.sort_order || 0);
				});
			
			const imageUrls = images.map((img: any) => img.url);
			const primaryImage = images.find((img: any) => img.is_primary)?.url || imageUrls[0] || null;
			
			return {
				id: acc.id,
				name: acc.name,
				slug: createSlug(acc.name),
				type: acc.type,
				description: acc.description,
				max_guests: acc.max_guests,
				surface_area: acc.surface_area,
				base_price: parseFloat(acc.base_price) || 0,
				amenities: acc.amenities || [],
				photos: imageUrls,
				primary_image: primaryImage,
				total_images: images.length,
				pricing_options: optionsByAccommodation[acc.id] || { recurring: [], one_time: [] },
				created_at: acc.created_at,
				updated_at: acc.updated_at
			};
		});

		// Log successful request
		await logPluginRequest(
			property.id,
			apiKey,
			domain,
			'/api/plugin/accommodations',
			'GET',
			clientAddress,
			request.headers.get('user-agent'),
			200
		);

		return new Response(JSON.stringify({
			success: true,
			data: formattedAccommodations,
			meta: {
				total: formattedAccommodations.length,
				property: {
					id: property.id,
					name: property.name,
					business_type: property.settings?.bedrijfsinfo?.business_type || 'minicamping'
				}
			}
		}), {
			status: 200,
			headers: { 'Content-Type': 'application/json' }
		});

	} catch (error) {
		console.error('Plugin accommodations error:', error);
		return new Response(JSON.stringify({
			success: false,
			error: 'Internal server error'
		}), {
			status: 500,
			headers: { 'Content-Type': 'application/json' }
		});
	}
};

/**
 * Authenticate plugin request
 */
async function authenticatePlugin(apiKey: string | null, domain: string) {
	if (!apiKey || !domain) {
		return {
			success: false,
			error: 'API key and domain are required',
			status: 400
		};
	}

	// Get property by API key
	const { data: property, error: propertyError } = await supabaseAdmin
		.from('properties')
		.select('*')
		.eq('api_key', apiKey)
		.eq('active', true)
		.single();

	if (propertyError || !property) {
		return {
			success: false,
			error: 'Invalid API key',
			status: 401
		};
	}

	// Validate domain (simplified for this endpoint)
	const cleanDomain = domain.replace(/^https?:\/\//, '').replace(/\/$/, '');
	if (cleanDomain !== property.domain && cleanDomain !== `www.${property.domain}`) {
		return {
			success: false,
			error: 'Domain not authorized',
			status: 403
		};
	}

	return {
		success: true,
		property: property
	};
}

/**
 * Create URL-friendly slug from accommodation name
 */
function createSlug(name: string): string {
	return name
		.toLowerCase()
		.replace(/[^a-z0-9\s-]/g, '') // Remove special characters
		.replace(/\s+/g, '-') // Replace spaces with hyphens
		.replace(/-+/g, '-') // Replace multiple hyphens with single
		.trim();
}

/**
 * Log plugin API request
 */
async function logPluginRequest(
	propertyId: string,
	apiKey: string | null,
	domain: string,
	endpoint: string,
	method: string,
	ipAddress: string | null,
	userAgent: string | null,
	responseStatus: number
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
				response_status: responseStatus
			});
	} catch (error) {
		console.error('Failed to log plugin request:', error);
	}
}
