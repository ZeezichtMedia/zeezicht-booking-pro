import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

export const post: APIRoute = async () => {
    try {
        console.log('Creating accommodation_images table manually...');
        
        // Since we can't execute raw SQL directly through supabase-js,
        // let's try to create the table by attempting operations that would fail if it doesn't exist
        
        // First, try to select from the table to see if it exists
        const { data: testData, error: testError } = await supabaseAdmin
            .from('accommodation_images')
            .select('id')
            .limit(1);
            
        if (testError) {
            console.log('Table does not exist, error:', testError.message);
            
            // The table doesn't exist. Since we can't create it via API,
            // let's provide the SQL that needs to be run manually
            const createTableSQL = `
-- Create accommodation_images table
CREATE TABLE accommodation_images (
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
CREATE INDEX idx_accommodation_images_accommodation_id ON accommodation_images(accommodation_id);
CREATE INDEX idx_accommodation_images_sort_order ON accommodation_images(accommodation_id, sort_order);
CREATE INDEX idx_accommodation_images_primary ON accommodation_images(accommodation_id, is_primary);

-- Insert sample data
INSERT INTO accommodation_images (accommodation_id, url, filename, original_name, file_size, mime_type, sort_order, is_primary, alt_text) VALUES
('7e0d5f49-086e-469e-aa35-e8c63c2e45a8', 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=600&fit=crop&crop=center', 'kampeerplaats_1.jpg', 'Hoofdfoto.jpg', 150000, 'image/jpeg', 0, true, 'Basis kampeerplaats - Hoofdfoto'),
('7e0d5f49-086e-469e-aa35-e8c63c2e45a8', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop&crop=center', 'kampeerplaats_2.jpg', 'Omgeving.jpg', 120000, 'image/jpeg', 1, false, 'Basis kampeerplaats - Omgeving'),
('85cc29f4-3515-4798-8f63-66c5df17eccb', 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop&crop=center', 'bnb_kamer_1.jpg', 'Kamer_hoofdfoto.jpg', 140000, 'image/jpeg', 0, true, 'Comfort B&B kamer - Hoofdfoto');
            `;
            
            return new Response(JSON.stringify({
                success: false,
                message: 'accommodation_images table does not exist',
                error: testError.message,
                instruction: 'Please run the following SQL in your Supabase dashboard:',
                sql: createTableSQL.trim()
            }), {
                status: 500,
                headers: { 'Content-Type': 'application/json' }
            });
        }
        
        // Table exists, return success
        return new Response(JSON.stringify({
            success: true,
            message: 'accommodation_images table exists',
            data: testData || []
        }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' }
        });

    } catch (error) {
        console.error('Error checking images table:', error);
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
