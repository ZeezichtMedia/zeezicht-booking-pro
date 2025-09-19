import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

/**
 * Delete Image API
 * Delete an accommodation image
 */
export const del: APIRoute = async ({ request }) => {
    try {
        const { id } = await request.json();

        if (!id) {
            return new Response(JSON.stringify({
                success: false,
                error: 'Image ID is required'
            }), {
                status: 400,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        // Try to get image info before deleting
        let image = null;
        
        try {
            const { data, error: fetchError } = await supabaseAdmin
                .from('accommodation_images')
                .select('*')
                .eq('id', id)
                .single();

            if (fetchError) {
                console.warn('accommodation_images table not found or image not found:', fetchError.message);
                return new Response(JSON.stringify({
                    success: false,
                    error: 'Image not found or table does not exist'
                }), {
                    status: 404,
                    headers: { 'Content-Type': 'application/json' }
                });
            }
            
            image = data;
        } catch (error) {
            console.warn('Error accessing accommodation_images table:', error);
            return new Response(JSON.stringify({
                success: false,
                error: 'Database table not available'
            }), {
                status: 503,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        if (!image) {
            return new Response(JSON.stringify({
                success: false,
                error: 'Image not found'
            }), {
                status: 404,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        // Try to delete from database
        try {
            const { error: deleteError } = await supabaseAdmin
                .from('accommodation_images')
                .delete()
                .eq('id', id);

            if (deleteError) {
                console.error('Database delete error:', deleteError);
                return new Response(JSON.stringify({
                    success: false,
                    error: 'Failed to delete image'
                }), {
                    status: 500,
                    headers: { 'Content-Type': 'application/json' }
                });
            }
        } catch (error) {
            console.error('Error deleting from accommodation_images table:', error);
            return new Response(JSON.stringify({
                success: false,
                error: 'Database operation failed'
            }), {
                status: 500,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        // In a real implementation, you would also delete the physical file
        console.log(`Image deleted: ${image.filename}`);

        // If this was the primary image, make the next image primary
        if (image.is_primary) {
            await supabaseAdmin
                .from('accommodation_images')
                .update({ is_primary: true })
                .eq('accommodation_id', image.accommodation_id)
                .order('sort_order', { ascending: true })
                .limit(1);
        }

        return new Response(JSON.stringify({
            success: true,
            message: 'Image deleted successfully'
        }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' }
        });

    } catch (error) {
        console.error('Delete image error:', error);
        return new Response(JSON.stringify({
            success: false,
            error: 'Internal server error'
        }), {
            status: 500,
            headers: { 'Content-Type': 'application/json' }
        });
    }
};
