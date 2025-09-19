import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

export const post: APIRoute = async () => {
    try {
        console.log('Running SQL to create accommodation_images table...');
        
        // Step 1: Create the table
        const createTableSQL = `
            CREATE TABLE IF NOT EXISTS accommodation_images (
                id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
                accommodation_id UUID NOT NULL REFERENCES accommodations(id) ON DELETE CASCADE,
                url VARCHAR(500) NOT NULL,
                filename VARCHAR(255) NOT NULL,
                original_name VARCHAR(255) NOT NULL,
                file_size INTEGER NOT NULL,
                mime_type VARCHAR(100) NOT NULL,
                sort_order INTEGER NOT NULL DEFAULT 0,
                is_primary BOOLEAN NOT NULL DEFAULT false,
                alt_text VARCHAR(255),
                caption TEXT,
                created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
                updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
            );
        `;
        
        // Try to execute raw SQL using the .sql() method
        const { data: createResult, error: createError } = await supabaseAdmin
            .from('accommodation_images')
            .select('*')
            .limit(0);
            
        if (createError && createError.message.includes('does not exist')) {
            // Table doesn't exist, let's try to create it using a stored procedure approach
            // First, let's try using the rpc method with a custom function
            
            try {
                // Try to create a simple function that can execute SQL
                const { data: funcResult, error: funcError } = await supabaseAdmin
                    .rpc('create_accommodation_images_table');
                    
                if (funcError) {
                    console.log('Custom function not available, trying direct insert approach...');
                    
                    // Since we can't create the table directly, let's try a different approach
                    // We'll use the PostgreSQL client directly if available
                    const { Client } = await import('pg');
                    
                    // This won't work in browser environment, but let's try
                    throw new Error('Cannot execute raw SQL from client');
                }
                
                return new Response(JSON.stringify({
                    success: true,
                    message: 'Table created using stored procedure',
                    data: funcResult
                }), {
                    status: 200,
                    headers: { 'Content-Type': 'application/json' }
                });
                
            } catch (pgError) {
                console.log('PostgreSQL direct connection not available');
                
                // Final attempt: try to use supabase-js with raw SQL
                // This is a hack but might work
                const supabaseUrl = import.meta.env.SUPABASE_URL;
                const supabaseKey = import.meta.env.SUPABASE_SERVICE_KEY;
                
                if (supabaseUrl && supabaseKey) {
                    try {
                        const response = await fetch(`${supabaseUrl}/rest/v1/rpc/exec_sql`, {
                            method: 'POST',
                            headers: {
                                'Authorization': `Bearer ${supabaseKey}`,
                                'Content-Type': 'application/json',
                                'apikey': supabaseKey
                            },
                            body: JSON.stringify({ sql: createTableSQL })
                        });
                        
                        if (response.ok) {
                            const result = await response.json();
                            return new Response(JSON.stringify({
                                success: true,
                                message: 'Table created via REST API',
                                data: result
                            }), {
                                status: 200,
                                headers: { 'Content-Type': 'application/json' }
                            });
                        }
                    } catch (restError) {
                        console.log('REST API approach failed:', restError);
                    }
                }
                
                // If all else fails, return the SQL for manual execution
                return new Response(JSON.stringify({
                    success: false,
                    message: 'Cannot execute SQL automatically',
                    error: 'Table does not exist and cannot be created via API',
                    instruction: 'Please run this SQL manually in Supabase dashboard:',
                    sql: createTableSQL.trim()
                }), {
                    status: 500,
                    headers: { 'Content-Type': 'application/json' }
                });
            }
        }
        
        // Table exists, now add sample data
        const sampleImages = [
            {
                accommodation_id: '7e0d5f49-086e-469e-aa35-e8c63c2e45a8',
                url: 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=600&fit=crop&crop=center',
                filename: 'kampeerplaats_1.jpg',
                original_name: 'Hoofdfoto.jpg',
                file_size: 150000,
                mime_type: 'image/jpeg',
                sort_order: 0,
                is_primary: true,
                alt_text: 'Basis kampeerplaats - Hoofdfoto'
            },
            {
                accommodation_id: '7e0d5f49-086e-469e-aa35-e8c63c2e45a8',
                url: 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop&crop=center',
                filename: 'kampeerplaats_2.jpg',
                original_name: 'Omgeving.jpg',
                file_size: 120000,
                mime_type: 'image/jpeg',
                sort_order: 1,
                is_primary: false,
                alt_text: 'Basis kampeerplaats - Omgeving'
            }
        ];
        
        const { data: insertResult, error: insertError } = await supabaseAdmin
            .from('accommodation_images')
            .insert(sampleImages)
            .select();
            
        if (insertError) {
            return new Response(JSON.stringify({
                success: false,
                message: 'Failed to insert sample data',
                error: insertError.message
            }), {
                status: 500,
                headers: { 'Content-Type': 'application/json' }
            });
        }
        
        return new Response(JSON.stringify({
            success: true,
            message: 'Sample data inserted successfully',
            data: insertResult
        }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' }
        });

    } catch (error) {
        console.error('Error running SQL:', error);
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
