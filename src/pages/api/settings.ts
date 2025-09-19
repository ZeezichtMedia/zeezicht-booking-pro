import type { APIRoute } from 'astro';
import { getSettings } from '../../lib/supabase';

export const get: APIRoute = async ({ url }) => {
	try {
		// For now, use a default property ID (later this will come from authentication)
		const propertyId = url.searchParams.get('property_id') || '00000000-0000-0000-0000-000000000001';
		const section = url.searchParams.get('section');
		
		const settings = await getSettings(propertyId, section || undefined);
		
		return new Response(JSON.stringify({
			success: true,
			data: settings
		}), {
			status: 200,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	} catch (error) {
		console.error('Error loading settings:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to load settings',
			error: (error as Error).message
		}), {
			status: 500,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	}
};

export const post: APIRoute = async ({ request }) => {
	try {
		const body = await request.json();
		const { section, data } = body;
		
		console.log('Saving settings:', { section, data });
		
		// For now, use a default property ID (later this will come from authentication)
		const propertyId = body.property_id || '00000000-0000-0000-0000-000000000001';
		
		const { updateSettings } = await import('../../lib/supabase');
		const success = await updateSettings(propertyId, section, data);
		
		if (!success) {
			throw new Error('Failed to update settings in database');
		}
		
		return new Response(JSON.stringify({
			success: true,
			message: `${section} settings saved successfully`,
			data: data
		}), {
			status: 200,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	} catch (error) {
		console.error('Error saving settings:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to save settings',
			error: (error as Error).message
		}), {
			status: 500,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	}
};
