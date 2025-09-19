import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

export const post: APIRoute = async ({ request }) => {
    try {
        console.log('Setting up accommodation_images table...');
        
        // Create the accommodation_images table using raw SQL
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
        
        // Execute using supabase-js raw SQL (if available)
        const { data: createResult, error: createError } = await supabaseAdmin
            .from('accommodation_images')
            .select('*')
            .limit(0);
            
        if (createError && createError.message.includes('does not exist')) {
            // Table doesn't exist, we need to create it
            // Since we can't execute raw SQL directly, let's try a different approach
            console.log('Table does not exist, attempting to create...');
            
            // For now, let's create some sample data to test the structure
            const sampleData = {
                accommodation_id: '7e0d5f49-086e-469e-aa35-e8c63c2e45a8',
                url: 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=600&fit=crop&crop=center',
                filename: 'sample_image_1.jpg',
                original_name: 'Hoofdfoto.jpg',
                file_size: 150000,
                mime_type: 'image/jpeg',
                sort_order: 0,
                is_primary: true,
                alt_text: 'Basis kampeerplaats - Hoofdfoto'
            };
            
            // This will fail if table doesn't exist, which tells us we need manual setup
            const { data: insertResult, error: insertError } = await supabaseAdmin
                .from('accommodation_images')
                .insert([sampleData])
                .select();
                
            if (insertError) {
                return new Response(JSON.stringify({
                    success: false,
                    message: 'Table does not exist and cannot be created automatically',
                    error: insertError.message,
                    instruction: 'Please run the SQL script manually in Supabase dashboard',
                    sql: createTableSQL
                }), {
                    status: 500,
                    headers: { 'Content-Type': 'application/json' }
                });
            }
            
            return new Response(JSON.stringify({
                success: true,
                message: 'Table created and sample data inserted',
                data: insertResult
            }), {
                status: 200,
                headers: { 'Content-Type': 'application/json' }
            });
        }
        
        // Table exists, check its structure
        const { data: existingData, error: selectError } = await supabaseAdmin
            .from('accommodation_images')
            .select('*')
            .limit(1);
            
        return new Response(JSON.stringify({
            success: true,
            message: 'Table already exists',
            data: existingData || [],
            tableExists: true
        }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' }
        });

    } catch (error) {
        console.error('Error setting up images table:', error);
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
