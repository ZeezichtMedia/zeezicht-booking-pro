import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

export const post: APIRoute = async () => {
    try {
        console.log('Adding sample images to accommodation_images table...');
        
        // Sample images for the existing accommodations
        const sampleImages = [
            // Images for "Basis kampeerplaats" (7e0d5f49-086e-469e-aa35-e8c63c2e45a8)
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
            },
            {
                accommodation_id: '7e0d5f49-086e-469e-aa35-e8c63c2e45a8',
                url: 'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&h=600&fit=crop&crop=center',
                filename: 'kampeerplaats_3.jpg',
                original_name: 'Faciliteiten.jpg',
                file_size: 180000,
                mime_type: 'image/jpeg',
                sort_order: 2,
                is_primary: false,
                alt_text: 'Basis kampeerplaats - Faciliteiten'
            },
            // Images for "Comfort B&B kamerrrr" (85cc29f4-3515-4798-8f63-66c5df17eccb)
            {
                accommodation_id: '85cc29f4-3515-4798-8f63-66c5df17eccb',
                url: 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop&crop=center',
                filename: 'bnb_kamer_1.jpg',
                original_name: 'Kamer_hoofdfoto.jpg',
                file_size: 140000,
                mime_type: 'image/jpeg',
                sort_order: 0,
                is_primary: true,
                alt_text: 'Comfort B&B kamer - Hoofdfoto'
            },
            {
                accommodation_id: '85cc29f4-3515-4798-8f63-66c5df17eccb',
                url: 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop&crop=center',
                filename: 'bnb_kamer_2.jpg',
                original_name: 'Badkamer.jpg',
                file_size: 110000,
                mime_type: 'image/jpeg',
                sort_order: 1,
                is_primary: false,
                alt_text: 'Comfort B&B kamer - Badkamer'
            }
        ];
        
        // Insert sample images
        const { data: insertedImages, error: insertError } = await supabaseAdmin
            .from('accommodation_images')
            .insert(sampleImages)
            .select();
            
        if (insertError) {
            console.error('Error inserting sample images:', insertError);
            return new Response(JSON.stringify({
                success: false,
                message: 'Failed to insert sample images',
                error: insertError.message
            }), {
                status: 500,
                headers: { 'Content-Type': 'application/json' }
            });
        }
        
        return new Response(JSON.stringify({
            success: true,
            message: 'Sample images added successfully',
            data: insertedImages,
            count: insertedImages?.length || 0
        }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' }
        });

    } catch (error) {
        console.error('Error adding sample images:', error);
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
