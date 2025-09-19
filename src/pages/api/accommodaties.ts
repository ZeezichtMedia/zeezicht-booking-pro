import type { APIRoute } from 'astro';
import { getAccommodationsByProperty, updateAccommodation, createAccommodation, deleteAccommodation } from '../../lib/supabase';

export const get: APIRoute = async ({ url }) => {
	try {
		// For now, use a default property ID (later this will come from authentication)
		const propertyId = url.searchParams.get('property_id') || '00000000-0000-0000-0000-000000000001';
		
		const accommodaties = await getAccommodationsByProperty(propertyId);
		
		return new Response(JSON.stringify({
			success: true,
			data: accommodaties
		}), {
			status: 200,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	} catch (error) {
		console.error('Error loading accommodaties:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to load accommodaties',
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
		
		console.log('Creating new accommodatie:', body);
		
		// For now, use a default property ID (later this will come from authentication)
		const propertyId = body.property_id || '00000000-0000-0000-0000-000000000001';
		
		const accommodatieData = {
			property_id: propertyId,
			name: body.naam || body.name,
			type: body.type,
			max_guests: parseInt(body.max_gasten || body.max_guests) || 1,
			surface_area: body.oppervlakte || body.surface_area,
			description: body.beschrijving || body.description,
			amenities: body.voorzieningen || body.amenities || [],
			photos: body.photos || [],
			base_price: parseFloat(body.base_price) || null,
			extra_info: body.extraInfo || body.extra_info,
			active: true
		};
		
		const newAccommodatie = await createAccommodation(accommodatieData);
		
		if (!newAccommodatie) {
			throw new Error('Failed to create accommodation in database');
		}
		
		return new Response(JSON.stringify({
			success: true,
			message: 'Accommodatie created successfully',
			data: newAccommodatie
		}), {
			status: 201,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	} catch (error) {
		console.error('Error creating accommodatie:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to create accommodatie',
			error: (error as Error).message
		}), {
			status: 500,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	}
};

export const put: APIRoute = async ({ request }) => {
	try {
		const body = await request.json();
		
		console.log('Updating accommodatie:', body);
		
		const accommodatieId = body.id;
		if (!accommodatieId) {
			return new Response(JSON.stringify({
				success: false,
				message: 'Accommodatie ID is required'
			}), {
				status: 400,
				headers: {
					'Content-Type': 'application/json',
				},
			});
		}
		
		// For now, use a default property ID (later this will come from authentication)
		const propertyId = body.property_id || '00000000-0000-0000-0000-000000000001';
		
		const accommodatieData = {
			property_id: propertyId,
			name: body.naam || body.name,
			type: body.type,
			max_guests: parseInt(body.max_gasten || body.max_guests) || 1,
			surface_area: body.oppervlakte || body.surface_area,
			description: body.beschrijving || body.description,
			amenities: body.voorzieningen || body.amenities || [],
			photos: body.photos || [],
			base_price: parseFloat(body.prijs || body.base_price) || null,
			extra_info: body.extraInfo || body.extra_info,
			active: body.status === 'actief' || body.active === true
		};
		
		const updatedAccommodatie = await updateAccommodation(accommodatieId, accommodatieData);
		
		if (!updatedAccommodatie) {
			return new Response(JSON.stringify({
				success: false,
				message: 'Accommodatie not found or failed to update'
			}), {
				status: 404,
				headers: {
					'Content-Type': 'application/json',
				},
			});
		}
		
		return new Response(JSON.stringify({
			success: true,
			message: 'Accommodatie updated successfully',
			data: updatedAccommodatie
		}), {
			status: 200,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	} catch (error) {
		console.error('Error updating accommodatie:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to update accommodatie',
			error: (error as Error).message
		}), {
			status: 500,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	}
};

export const del: APIRoute = async ({ url }) => {
	try {
		const accommodatieId = url.searchParams.get('id');
		
		if (!accommodatieId) {
			return new Response(JSON.stringify({
				success: false,
				message: 'Accommodatie ID is required'
			}), {
				status: 400,
				headers: {
					'Content-Type': 'application/json',
				},
			});
		}
		
		console.log('Deleting accommodatie:', accommodatieId);
		
		const success = await deleteAccommodation(accommodatieId);
		
		if (!success) {
			return new Response(JSON.stringify({
				success: false,
				message: 'Accommodatie not found or failed to delete'
			}), {
				status: 404,
				headers: {
					'Content-Type': 'application/json',
				},
			});
		}
		
		return new Response(JSON.stringify({
			success: true,
			message: 'Accommodatie deleted successfully'
		}), {
			status: 200,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	} catch (error) {
		console.error('Error deleting accommodatie:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to delete accommodatie',
			error: (error as Error).message
		}), {
			status: 500,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	}
};
