import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

export const get: APIRoute = async ({ url }) => {
	try {
		// For now, use a default property ID (later this will come from authentication)
		const propertyId = url.searchParams.get('property_id') || '00000000-0000-0000-0000-000000000001';
		const bookingId = url.searchParams.get('id');
		
		// If ID is provided, fetch single booking
		if (bookingId) {
			const { data: booking, error } = await supabaseAdmin
				.from('bookings')
				.select(`
					*,
					accommodations (
						name
					)
				`)
				.eq('id', bookingId)
				.eq('property_id', propertyId)
				.single();

			if (error) {
				console.error('Error fetching booking:', error);
				throw error;
			}

			if (!booking) {
				return new Response(JSON.stringify({
					success: false,
					message: 'Booking not found'
				}), {
					status: 404,
					headers: {
						'Content-Type': 'application/json',
					},
				});
			}

			// Transform single booking to match expected format
			const reservering = {
				id: booking.id,
				gast_id: booking.id, // Use booking ID as gast_id for now
				gast_naam: booking.guest_name,
				gast_email: booking.guest_email,
				gast_telefoon: booking.guest_phone || '',
				check_in: booking.check_in,
				check_out: booking.check_out,
				kamer: booking.accommodations?.name || 'Onbekend',
				aantal_gasten: booking.guests,
				prijs_per_nacht: `€${booking.price_per_night || 0}`,
				totaal_prijs: `€${booking.total_price}`,
				status: booking.status,
				betaalstatus: booking.payment_status,
				aanbetaling: `€${booking.deposit_amount || 0}`,
				restbetaling: `€${booking.remaining_amount || 0}`,
				korting: `€${booking.discount || 0}`,
				borg: `€${booking.deposit || 0}`,
				ontbijt: booking.breakfast || false,
				linnen: booking.linens || false,
				handdoeken: booking.towels || false,
				parkeren: booking.parking || false,
				opmerkingen_publiek: booking.public_notes || '',
				opmerkingen_intern: booking.internal_notes || ''
			};

			return new Response(JSON.stringify(reservering), {
				status: 200,
				headers: {
					'Content-Type': 'application/json',
				},
			});
		}
		
		// Otherwise, fetch all bookings
		const { data: bookings, error } = await supabaseAdmin
			.from('bookings')
			.select(`
				*,
				accommodations (
					name
				)
			`)
			.eq('property_id', propertyId)
			.order('created_at', { ascending: false });

		if (error) {
			console.error('Error fetching bookings:', error);
			throw error;
		}

		// Transform to match expected format
		const reserveringen = (bookings || []).map((booking: any) => ({
			id: booking.id,
			gast_naam: booking.guest_name,
			kamer: booking.accommodations?.name || 'Onbekend',
			check_in: booking.check_in,
			check_out: booking.check_out,
			aantal_gasten: booking.guests,
			totaal_prijs: `€${booking.total_price}`,
			status: booking.status,
			betaalstatus: booking.payment_status,
			email: booking.guest_email,
			telefoon: booking.guest_phone || '',
			opmerkingen: ''
		}));

		return new Response(JSON.stringify(reserveringen), {
			status: 200,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	} catch (error) {
		console.error('Error loading reserveringen:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to load reserveringen',
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
		
		console.log('Creating new reservering:', body);
		
		// For now, use a default property ID (later this will come from authentication)
		const propertyId = body.property_id || '00000000-0000-0000-0000-000000000001';
		
		// Find accommodation ID by name
		const { data: accommodation } = await supabaseAdmin
			.from('accommodations')
			.select('id')
			.eq('name', body.kamer)
			.eq('property_id', propertyId)
			.single();

		// First, create or find the guest
		let guestId = null;
		if (body.gast_email && body.gast_naam) {
			// Check if guest already exists
			const { data: existingGuest } = await supabaseAdmin
				.from('guests')
				.select('id')
				.eq('email', body.gast_email)
				.eq('property_id', propertyId)
				.single();
			
			if (existingGuest) {
				guestId = existingGuest.id;
				console.log('Using existing guest:', guestId);
			} else {
				// Create new guest
				const { data: newGuest, error: guestError } = await supabaseAdmin
					.from('guests')
					.insert({
						property_id: propertyId,
						name: body.gast_naam,
						email: body.gast_email,
						phone: body.gast_telefoon || null
					})
					.select('id')
					.single();
				
				if (guestError) {
					console.error('Error creating guest:', guestError);
				} else {
					guestId = newGuest.id;
					console.log('Created new guest:', guestId);
				}
			}
		}

		const accommodationId = accommodation?.id;
		const bookingData = {
			property_id: propertyId,
			accommodation_id: accommodationId,
			check_in: body.check_in,
			check_out: body.check_out,
			guests: parseInt(body.aantal_gasten),
			guest_name: body.gast_naam,
			guest_email: body.gast_email,
			guest_phone: body.gast_telefoon,
			guest_id: guestId, // Link to guest
			total_price: typeof body.totaal_prijs === 'string' 
				? parseFloat(body.totaal_prijs.replace('€', '').replace(',', '.')) 
				: parseFloat(body.totaal_prijs) || 0,
			payment_status: 'pending',
			status: body.status || 'confirmed'
		};
		
		const { data: newBooking, error } = await supabaseAdmin
			.from('bookings')
			.insert([bookingData])
			.select()
			.single();
		
		if (error) {
			console.error('Error creating booking:', error);
			throw error;
		}
		
		return new Response(JSON.stringify({
			success: true,
			message: 'Reservering created successfully',
			data: newBooking
		}), {
			status: 201,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	} catch (error) {
		console.error('Error creating reservering:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to create reservering',
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
		
		console.log('Updating reservering:', body);
		
		const bookingId = body.id;
		if (!bookingId) {
			return new Response(JSON.stringify({
				success: false,
				message: 'Booking ID is required'
			}), {
				status: 400,
				headers: {
					'Content-Type': 'application/json',
				},
			});
		}
		
		// Find accommodation ID by name if kamer is provided
		let accommodationId = null;
		if (body.kamer) {
			console.log('Looking up accommodation with name:', body.kamer);
			const { data: accommodation, error: accommodationError } = await supabaseAdmin
				.from('accommodations')
				.select('id')
				.eq('name', body.kamer)
				.single();
			
			if (accommodationError) {
				console.error('Error finding accommodation:', accommodationError);
			} else {
				accommodationId = accommodation?.id;
				console.log('Found accommodation ID:', accommodationId);
			}
		}

		// Handle guest updates - create or find guest
		let guestId = null;
		const propertyId = '00000000-0000-0000-0000-000000000001'; // Default property
		
		if (body.gast_email && body.gast_naam) {
			// Check if guest already exists
			const { data: existingGuest } = await supabaseAdmin
				.from('guests')
				.select('id')
				.eq('email', body.gast_email)
				.eq('property_id', propertyId)
				.single();
			
			if (existingGuest) {
				guestId = existingGuest.id;
				console.log('Using existing guest for update:', guestId);
			} else {
				// Create new guest
				const { data: newGuest, error: guestError } = await supabaseAdmin
					.from('guests')
					.insert({
						property_id: propertyId,
						name: body.gast_naam,
						email: body.gast_email,
						phone: body.gast_telefoon || null
					})
					.select('id')
					.single();
				
				if (guestError) {
					console.error('Error creating guest during update:', guestError);
				} else {
					guestId = newGuest.id;
					console.log('Created new guest during update:', guestId);
				}
			}
		}

		const updates = {
			accommodation_id: accommodationId,
			check_in: body.check_in,
			check_out: body.check_out,
			guests: parseInt(body.aantal_gasten),
			guest_name: body.gast_naam,
			guest_email: body.gast_email,
			guest_phone: body.gast_telefoon,
			guest_id: guestId, // Link to guest
			price_per_night: typeof body.prijs_per_nacht === 'string' 
				? parseFloat(body.prijs_per_nacht.replace('€', '').replace(',', '.')) 
				: parseFloat(body.prijs_per_nacht) || 0,
			total_price: typeof body.totaal_prijs === 'string' 
				? parseFloat(body.totaal_prijs.replace('€', '').replace(',', '.')) 
				: parseFloat(body.totaal_prijs) || 0,
			discount: typeof body.korting === 'string' 
				? parseFloat(body.korting.replace('€', '').replace(',', '.')) 
				: parseFloat(body.korting) || 0,
			deposit: typeof body.borg === 'string' 
				? parseFloat(body.borg.replace('€', '').replace(',', '.')) 
				: parseFloat(body.borg) || 0,
			deposit_amount: typeof body.aanbetaling === 'string' 
				? parseFloat(body.aanbetaling.replace('€', '').replace(',', '.')) 
				: parseFloat(body.aanbetaling) || 0,
			remaining_amount: typeof body.restbetaling === 'string' 
				? parseFloat(body.restbetaling.replace('€', '').replace(',', '.')) 
				: parseFloat(body.restbetaling) || 0,
			status: body.status || body.reservering_status,
			payment_status: body.betaalstatus,
			breakfast: Boolean(body.ontbijt),
			linens: Boolean(body.linnen),
			towels: Boolean(body.handdoeken),
			parking: Boolean(body.parkeren),
			public_notes: body.opmerkingen_publiek || '',
			internal_notes: body.opmerkingen_intern || ''
		};
		
		const { data: updatedBooking, error } = await supabaseAdmin
			.from('bookings')
			.update(updates)
			.eq('id', bookingId)
			.select()
			.single();
		
		if (error) {
			console.error('Error updating booking:', error);
			throw error;
		}
		
		if (!updatedBooking) {
			return new Response(JSON.stringify({
				success: false,
				message: 'Booking not found'
			}), {
				status: 404,
				headers: {
					'Content-Type': 'application/json',
				},
			});
		}
		
		return new Response(JSON.stringify({
			success: true,
			message: 'Reservering updated successfully',
			data: updatedBooking
		}), {
			status: 200,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	} catch (error) {
		console.error('Error updating reservering:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to update reservering',
			error: (error as Error).message
		}), {
			status: 500,
			headers: {
				'Content-Type': 'application/json',
			},
		});
	}
};
