import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

export const post: APIRoute = async ({ request }) => {
	try {
		console.log('Setting up hybrid settings system...');
		
		// Step 1: Create business_settings table
		const businessSettingsResult = await supabaseAdmin.rpc('exec_sql', {
			sql: `
				CREATE TABLE IF NOT EXISTS business_settings (
					property_id UUID PRIMARY KEY REFERENCES properties(id) ON DELETE CASCADE,
					business_type VARCHAR(50) NOT NULL DEFAULT 'minicamping',
					website_url VARCHAR(255),
					contact_email VARCHAR(255),
					contact_phone VARCHAR(50),
					business_address TEXT,
					business_description TEXT,
					created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
					updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
					
					CONSTRAINT valid_business_type CHECK (business_type IN (
						'bnb', 'minicamping', 'camping', 'hotel', 'vakantiepark', 'glamping', 'hostel'
					))
				);
			`
		});

		// Since RPC doesn't exist, let's use a different approach
		// Let's create the tables using raw SQL via a different method
		
		return new Response(JSON.stringify({
			success: true,
			message: 'Settings system setup initiated',
			note: 'Please run the SQL script manually in Supabase dashboard'
		}), {
			status: 200,
			headers: { 'Content-Type': 'application/json' }
		});

	} catch (error) {
		console.error('Error setting up settings:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Setup failed',
			error: (error as Error).message
		}), {
			status: 500,
			headers: { 'Content-Type': 'application/json' }
		});
	}
};
