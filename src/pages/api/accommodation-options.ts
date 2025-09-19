import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

// GET - Fetch pricing options for an accommodation
export const get: APIRoute = async ({ url }) => {
	try {
		const accommodationId = url.searchParams.get('accommodation_id');
		
		if (!accommodationId) {
			return new Response(JSON.stringify({
				success: false,
				message: 'Accommodation ID is required'
			}), {
				status: 400,
				headers: { 'Content-Type': 'application/json' },
			});
		}

		const { data: options, error } = await supabaseAdmin
			.from('accommodation_pricing_options')
			.select('*')
			.eq('accommodation_id', accommodationId)
			.eq('is_active', true)
			.order('category', { ascending: true })
			.order('sort_order', { ascending: true });

		if (error) {
			console.error('Error fetching accommodation options:', error);
			throw error;
		}

		// Group by category
		const groupedOptions = {
			recurring: options?.filter(opt => opt.category === 'recurring') || [],
			one_time: options?.filter(opt => opt.category === 'one_time') || []
		};

		return new Response(JSON.stringify({
			success: true,
			data: groupedOptions
		}), {
			status: 200,
			headers: { 'Content-Type': 'application/json' },
		});

	} catch (error) {
		console.error('Error loading accommodation options:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to load accommodation options',
			error: (error as Error).message
		}), {
			status: 500,
			headers: { 'Content-Type': 'application/json' },
		});
	}
};

// POST - Create new pricing option
export const post: APIRoute = async ({ request }) => {
	try {
		const body = await request.json();
		const { 
			accommodation_id, 
			category, 
			name, 
			description, 
			price_per_night, 
			price_per_stay, 
			unit,
			sort_order 
		} = body;

		if (!accommodation_id || !category || !name || !unit) {
			return new Response(JSON.stringify({
				success: false,
				message: 'Missing required fields: accommodation_id, category, name, unit'
			}), {
				status: 400,
				headers: { 'Content-Type': 'application/json' },
			});
		}

		const optionData = {
			accommodation_id,
			category,
			name,
			description: description || null,
			price_per_night: category === 'recurring' ? parseFloat(price_per_night) || 0 : null,
			price_per_stay: category === 'one_time' ? parseFloat(price_per_stay) || 0 : null,
			unit,
			sort_order: parseInt(sort_order) || 0,
			is_active: true
		};

		const { data: newOption, error } = await supabaseAdmin
			.from('accommodation_pricing_options')
			.insert([optionData])
			.select()
			.single();

		if (error) {
			console.error('Error creating pricing option:', error);
			throw error;
		}

		return new Response(JSON.stringify({
			success: true,
			message: 'Pricing option created successfully',
			data: newOption
		}), {
			status: 201,
			headers: { 'Content-Type': 'application/json' },
		});

	} catch (error) {
		console.error('Error creating pricing option:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to create pricing option',
			error: (error as Error).message
		}), {
			status: 500,
			headers: { 'Content-Type': 'application/json' },
		});
	}
};

// PUT - Update pricing option
export const put: APIRoute = async ({ request }) => {
	try {
		const body = await request.json();
		const { 
			id,
			name, 
			description, 
			price_per_night, 
			price_per_stay, 
			unit,
			sort_order,
			is_active 
		} = body;

		if (!id) {
			return new Response(JSON.stringify({
				success: false,
				message: 'Option ID is required'
			}), {
				status: 400,
				headers: { 'Content-Type': 'application/json' },
			});
		}

		const updates = {
			name,
			description: description || null,
			price_per_night: price_per_night ? parseFloat(price_per_night) : null,
			price_per_stay: price_per_stay ? parseFloat(price_per_stay) : null,
			unit,
			sort_order: parseInt(sort_order) || 0,
			is_active: is_active !== undefined ? is_active : true,
			updated_at: new Date().toISOString()
		};

		const { data: updatedOption, error } = await supabaseAdmin
			.from('accommodation_pricing_options')
			.update(updates)
			.eq('id', id)
			.select()
			.single();

		if (error) {
			console.error('Error updating pricing option:', error);
			throw error;
		}

		return new Response(JSON.stringify({
			success: true,
			message: 'Pricing option updated successfully',
			data: updatedOption
		}), {
			status: 200,
			headers: { 'Content-Type': 'application/json' },
		});

	} catch (error) {
		console.error('Error updating pricing option:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to update pricing option',
			error: (error as Error).message
		}), {
			status: 500,
			headers: { 'Content-Type': 'application/json' },
		});
	}
};

// DELETE - Soft delete pricing option
export const del: APIRoute = async ({ request }) => {
	try {
		const body = await request.json();
		const { id } = body;

		if (!id) {
			return new Response(JSON.stringify({
				success: false,
				message: 'Option ID is required'
			}), {
				status: 400,
				headers: { 'Content-Type': 'application/json' },
			});
		}

		// Soft delete by setting is_active to false
		const { data: deletedOption, error } = await supabaseAdmin
			.from('accommodation_pricing_options')
			.update({ 
				is_active: false,
				updated_at: new Date().toISOString()
			})
			.eq('id', id)
			.select()
			.single();

		if (error) {
			console.error('Error deleting pricing option:', error);
			throw error;
		}

		return new Response(JSON.stringify({
			success: true,
			message: 'Pricing option deleted successfully',
			data: deletedOption
		}), {
			status: 200,
			headers: { 'Content-Type': 'application/json' },
		});

	} catch (error) {
		console.error('Error deleting pricing option:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to delete pricing option',
			error: (error as Error).message
		}), {
			status: 500,
			headers: { 'Content-Type': 'application/json' },
		});
	}
};
