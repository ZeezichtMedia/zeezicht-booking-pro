import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

export const post: APIRoute = async () => {
    try {
        console.log('Creating accommodation_images table via RPC...');
        
        // First, let's create the RPC function in Supabase that can execute our SQL
        const createFunctionSQL = `
CREATE OR REPLACE FUNCTION create_accommodation_images_table()
RETURNS text
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
BEGIN
    -- Create the table
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
    
    -- Create indexes
    CREATE INDEX IF NOT EXISTS idx_accommodation_images_accommodation_id ON accommodation_images(accommodation_id);
    CREATE INDEX IF NOT EXISTS idx_accommodation_images_sort_order ON accommodation_images(accommodation_id, sort_order);
    CREATE INDEX IF NOT EXISTS idx_accommodation_images_primary ON accommodation_images(accommodation_id, is_primary);
    
    -- Insert sample data (only if table is empty)
    INSERT INTO accommodation_images (accommodation_id, url, filename, original_name, file_size, mime_type, sort_order, is_primary, alt_text)
    SELECT * FROM (VALUES
        ('7e0d5f49-086e-469e-aa35-e8c63c2e45a8'::UUID, 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=600&fit=crop&crop=center', 'kampeerplaats_1.jpg', 'Hoofdfoto.jpg', 150000, 'image/jpeg', 0, true, 'Basis kampeerplaats - Hoofdfoto'),
        ('7e0d5f49-086e-469e-aa35-e8c63c2e45a8'::UUID, 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop&crop=center', 'kampeerplaats_2.jpg', 'Omgeving.jpg', 120000, 'image/jpeg', 1, false, 'Basis kampeerplaats - Omgeving'),
        ('85cc29f4-3515-4798-8f63-66c5df17eccb'::UUID, 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop&crop=center', 'bnb_kamer_1.jpg', 'Kamer_hoofdfoto.jpg', 140000, 'image/jpeg', 0, true, 'Comfort B&B kamer - Hoofdfoto')
    ) AS t(accommodation_id, url, filename, original_name, file_size, mime_type, sort_order, is_primary, alt_text)
    WHERE NOT EXISTS (SELECT 1 FROM accommodation_images LIMIT 1);
    
    RETURN 'accommodation_images table created successfully with sample data';
END;
$$;
        `;
        
        // Try to call the RPC function
        const { data, error } = await supabaseAdmin
            .rpc('create_accommodation_images_table');
            
        if (error) {
            console.error('RPC function error:', error);
            
            // If the function doesn't exist, provide instructions
            return new Response(JSON.stringify({
                success: false,
                message: 'RPC function does not exist',
                error: error.message,
                instruction: 'Please run the following SQL in your Supabase dashboard to create the RPC function and table:',
                sql: createFunctionSQL.trim()
            }), {
                status: 500,
                headers: { 'Content-Type': 'application/json' }
            });
        }
        
        // Success! Now test if the table works
        const { data: testData, error: testError } = await supabaseAdmin
            .from('accommodation_images')
            .select('*')
            .limit(3);
            
        return new Response(JSON.stringify({
            success: true,
            message: 'accommodation_images table created successfully',
            data: testData || [],
            rpcResult: data
        }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' }
        });

    } catch (error) {
        console.error('Error creating table via RPC:', error);
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
