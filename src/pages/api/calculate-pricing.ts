import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

export const post: APIRoute = async ({ request }) => {
	try {
		const body = await request.json();
		const {
			accommodation_id,
			check_in,
			check_out,
			adults = 1,
			children_12_plus = 0,
			children_under_12 = 0,
			children_0_3 = 0,
			camping_vehicle_type = 'tent',
			selected_options = [] // Array of {option_id, quantity}
		} = body;

		if (!accommodation_id || !check_in || !check_out) {
			return new Response(JSON.stringify({
				success: false,
				message: 'Missing required fields: accommodation_id, check_in, check_out'
			}), {
				status: 400,
				headers: { 'Content-Type': 'application/json' },
			});
		}

		// Calculate number of nights
		const checkInDate = new Date(check_in);
		const checkOutDate = new Date(check_out);
		const nights = Math.ceil((checkOutDate.getTime() - checkInDate.getTime()) / (1000 * 60 * 60 * 24));

		if (nights <= 0) {
			return new Response(JSON.stringify({
				success: false,
				message: 'Check-out date must be after check-in date'
			}), {
				status: 400,
				headers: { 'Content-Type': 'application/json' },
			});
		}

		// Get accommodation base price
		const { data: accommodation, error: accommodationError } = await supabaseAdmin
			.from('accommodations')
			.select('name, base_price')
			.eq('id', accommodation_id)
			.single();

		if (accommodationError || !accommodation) {
			throw new Error('Accommodation not found');
		}

		const basePricePerNight = parseFloat(accommodation.base_price) || 24.00; // Default price
		const basePrice = basePricePerNight * nights;

		// Get all available options for this accommodation
		const { data: availableOptions, error: optionsError } = await supabaseAdmin
			.from('accommodation_pricing_options')
			.select('*')
			.eq('accommodation_id', accommodation_id)
			.eq('is_active', true);

		if (optionsError) {
			throw optionsError;
		}

		// Calculate selected options
		const recurringOptions: any[] = [];
		const oneTimeOptions: any[] = [];
		let recurringTotal = 0;
		let oneTimeTotal = 0;

		for (const selectedOption of selected_options) {
			const option = availableOptions?.find(opt => opt.id === selectedOption.option_id);
			if (!option) continue;

			const quantity = parseInt(selectedOption.quantity) || 1;
			let totalPrice = 0;

			if (option.category === 'recurring') {
				// Calculate based on nights and quantity
				const pricePerNight = parseFloat(option.price_per_night) || 0;
				
				if (option.unit === 'per_person_per_night') {
					// Calculate per person (adults + children 12+)
					const totalPersons = adults + children_12_plus;
					totalPrice = pricePerNight * totalPersons * nights * quantity;
				} else {
					// Standard per night pricing
					totalPrice = pricePerNight * nights * quantity;
				}

				recurringOptions.push({
					id: option.id,
					name: option.name,
					description: option.description,
					unit: option.unit,
					price_per_night: pricePerNight,
					quantity: quantity,
					nights: nights,
					total_price: totalPrice
				});

				recurringTotal += totalPrice;
			} else {
				// One-time options
				const pricePerStay = parseFloat(option.price_per_stay) || 0;
				totalPrice = pricePerStay * quantity;

				oneTimeOptions.push({
					id: option.id,
					name: option.name,
					description: option.description,
					unit: option.unit,
					price_per_stay: pricePerStay,
					quantity: quantity,
					total_price: totalPrice
				});

				oneTimeTotal += totalPrice;
			}
		}

		// Calculate totals
		const subtotal = basePrice + recurringTotal + oneTimeTotal;
		
		// Prepare response
		const pricingBreakdown = {
			accommodation: {
				name: accommodation.name,
				check_in,
				check_out,
				nights,
				base_price_per_night: basePricePerNight,
				base_total: basePrice
			},
			guests: {
				adults,
				children_12_plus,
				children_under_12,
				children_0_3,
				total_persons: adults + children_12_plus + children_under_12 + children_0_3
			},
			camping_vehicle_type,
			recurring_options: recurringOptions,
			one_time_options: oneTimeOptions,
			totals: {
				base_price: basePrice,
				recurring_options_total: recurringTotal,
				one_time_options_total: oneTimeTotal,
				subtotal: subtotal,
				total: subtotal // Can add taxes/fees here later
			}
		};

		return new Response(JSON.stringify({
			success: true,
			data: pricingBreakdown
		}), {
			status: 200,
			headers: { 'Content-Type': 'application/json' },
		});

	} catch (error) {
		console.error('Error calculating pricing:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Failed to calculate pricing',
			error: (error as Error).message
		}), {
			status: 500,
			headers: { 'Content-Type': 'application/json' },
		});
	}
};
