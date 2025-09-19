import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

export const post: APIRoute = async ({ request }) => {
	try {
		const body = await request.json();
		const { sql } = body;
		
		if (!sql) {
			return new Response(JSON.stringify({
				success: false,
				message: 'SQL query is required'
			}), {
				status: 400,
				headers: { 'Content-Type': 'application/json' }
			});
		}

		console.log('Executing SQL:', sql);
		
		// Try different approaches to execute SQL
		let data, error;
		
		// Approach 1: Try to use a custom RPC function (if it exists)
		try {
			const result = await supabaseAdmin.rpc('execute_sql', { sql_query: sql });
			data = result.data;
			error = result.error;
		} catch (rpcError) {
			console.log('RPC approach failed, trying direct execution...');
			
			// Approach 2: Try to execute specific SQL patterns
			if (sql.toLowerCase().includes('create table accommodation_images')) {
				// Special handling for creating accommodation_images table
				try {
					// Try to insert a test record to see if table exists
					const testResult = await supabaseAdmin
						.from('accommodation_images')
						.select('id')
						.limit(1);
						
					if (testResult.error && testResult.error.message.includes('does not exist')) {
						// Table doesn't exist, we need manual creation
						data = null;
						error = { message: 'Table creation requires manual SQL execution in Supabase dashboard' };
					} else {
						// Table exists
						data = { message: 'Table already exists' };
						error = null;
					}
				} catch (tableError) {
					data = null;
					error = { message: 'Cannot determine table status' };
				}
			} else {
				// For other SQL queries, return error
				data = null;
				error = { message: 'Direct SQL execution not supported' };
			}
		}
		
		if (error) {
			console.error('SQL execution error:', error);
			return new Response(JSON.stringify({
				success: false,
				message: 'Database query failed',
				error: error.message
			}), {
				status: 500,
				headers: { 'Content-Type': 'application/json' }
			});
		}

		return new Response(JSON.stringify({
			success: true,
			data: data
		}), {
			status: 200,
			headers: { 'Content-Type': 'application/json' }
		});

	} catch (error) {
		console.error('Error executing SQL:', error);
		return new Response(JSON.stringify({
			success: false,
			message: 'Internal server error',
			error: (error as Error).message
		}), {
			status: 500,
			headers: { 'Content-Type': 'application/json' }
		});
	}
};
