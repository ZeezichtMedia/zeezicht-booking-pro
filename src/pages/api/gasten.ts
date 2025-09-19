import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

export const get: APIRoute = async ({ url }) => {
	try {
		// For now, use a default property ID (later this will come from authentication)
		const propertyId = url.searchParams.get('property_id') || '00000000-0000-0000-0000-000000000001';
		
		// Fetch guests from the new guests table with statistics
		const { data: guests, error } = await supabaseAdmin
			.from('guest_statistics')
			.select('*')
			.eq('property_id', propertyId)
			.order('created_at', { ascending: false });

		if (error) {
			console.error('Error fetching guests:', error);
			throw error;
		}

		// Transform to expected format
		const gasten = (guests || []).map((guest: any) => ({
			id: guest.id,
			name: guest.name,
			avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(guest.name)}&background=random`,
			email: guest.email,
			telefoon: guest.phone || '',
			biography: `${guest.loyalty_level} gast van Zee-zicht B&B`,
			position: 'Gast',
			country: guest.country || 'Nederland',
			status: 'active',
			aantal_bezoeken: guest.total_bookings || 0,
			total_revenue: guest.total_revenue || 0,
			last_visit: guest.last_visit,
			loyalty_level: guest.loyalty_level || 'Bronze'
		}));

		return new Response(JSON.stringify(gasten), {
			status: 200,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	} catch (error) {
		console.error('Error loading gasten:', error);
		// Fallback to old method if guests table doesn't exist yet
		try {
			const { data: bookings, error: bookingsError } = await supabaseAdmin
				.from('bookings')
				.select('guest_name, guest_email, guest_phone, created_at')
				.eq('property_id', url.searchParams.get('property_id') || '00000000-0000-0000-0000-000000000001')
				.order('created_at', { ascending: false });

			if (bookingsError) throw bookingsError;

			// Transform to unique guests (fallback)
			const uniqueGuests = new Map();
			(bookings || []).forEach((booking: any, index: number) => {
				const email = booking.guest_email;
				if (!uniqueGuests.has(email)) {
					uniqueGuests.set(email, {
						id: `temp-${index + 1}`,
						name: booking.guest_name,
						avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(booking.guest_name)}&background=random`,
						email: booking.guest_email,
						telefoon: booking.guest_phone || '',
						biography: 'Gast van Zee-zicht B&B',
						position: 'Gast',
						country: 'Nederland',
						status: 'active',
						aantal_bezoeken: 1
					});
				}
			});

			return new Response(JSON.stringify(Array.from(uniqueGuests.values())), {
				status: 200,
				headers: { 'Content-Type': 'application/json' },
			});
		} catch (fallbackError) {
			console.error('Fallback error:', fallbackError);
			return new Response(JSON.stringify([]), {
				status: 200,
				headers: { 'Content-Type': 'application/json' },
			});
		}
	}
};

export const post: APIRoute = async ({ request }) => {
	try {
		const body = await request.json();
		const { name, email, phone, property_id } = body;
		
		console.log('Creating/finding gast:', { name, email, phone });
		
		// Use default property ID if not provided
		const propertyIdToUse = property_id || '00000000-0000-0000-0000-000000000001';
		
		// First, check if guest already exists
		const { data: existingGuest, error: findError } = await supabaseAdmin
			.from('guests')
			.select('*')
			.eq('email', email)
			.eq('property_id', propertyIdToUse)
			.single();
		
		if (existingGuest) {
			// Guest already exists, return existing guest
			return new Response(JSON.stringify({
				success: true,
				message: 'Guest already exists',
				data: {
					id: existingGuest.id,
					name: existingGuest.name,
					email: existingGuest.email,
					telefoon: existingGuest.phone || '',
					avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(existingGuest.name)}&background=random`
				}
			}), {
				status: 200,
				headers: { 'Content-Type': 'application/json' },
			});
		}
		
		// Create new guest
		const { data: newGuest, error: createError } = await supabaseAdmin
			.from('guests')
			.insert({
				property_id: propertyIdToUse,
				name: name,
				email: email,
				phone: phone || null,
				country: 'Nederland'
			})
			.select()
			.single();
		
		if (createError) {
			console.error('Error creating guest:', createError);
			throw createError;
		}
		
		return new Response(JSON.stringify({
			success: true,
			message: 'Guest created successfully',
			data: {
				id: newGuest.id,
				name: newGuest.name,
				email: newGuest.email,
				telefoon: newGuest.phone || '',
				avatar: `https://ui-avatars.com/api/?name=${encodeURIComponent(newGuest.name)}&background=random`
			}
		}), {
			status: 201,
			headers: { 'Content-Type': 'application/json' },
		});
		
	} catch (error) {
		console.error('Error creating gast:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to create gast',
			error: (error as Error).message
		}), {
			status: 500,
			headers: { 'Content-Type': 'application/json' },
		});
	}
};

export const put: APIRoute = async ({ request }) => {
	try {
		const body = await request.json();
		
		console.log('Updating gast:', body);
		
		// Here you would normally update in database
		// For now, just simulate success
		
		return new Response(JSON.stringify({
			success: true,
			message: 'Gast updated successfully',
			data: body
		}), {
			status: 200,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	} catch (error) {
		console.error('Error updating gast:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to update gast',
			error: error.message
		}), {
			status: 500,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	}
};
