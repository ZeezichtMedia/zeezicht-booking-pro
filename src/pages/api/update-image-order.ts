import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

/**
 * Update Image Order API
 * Update the sort order of accommodation images
 */
export const post: APIRoute = async ({ request }) => {
    try {
        const { accommodation_id, images } = await request.json();

        if (!accommodation_id || !Array.isArray(images)) {
            return new Response(JSON.stringify({
                success: false,
                error: 'Accommodation ID and images array are required'
            }), {
                status: 400,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        // Update each image's sort order and primary status
        const updatePromises = images.map(async (img, index) => {
            return supabaseAdmin
                .from('accommodation_images')
                .update({
                    sort_order: index,
                    is_primary: index === 0 // First image is primary
                })
                .eq('id', img.id)
                .eq('accommodation_id', accommodation_id);
        });

        // Execute all updates
        const results = await Promise.all(updatePromises);

        // Check for errors
        const errors = results.filter(result => result.error);
        if (errors.length > 0) {
            console.error('Update errors:', errors);
            return new Response(JSON.stringify({
                success: false,
                error: 'Failed to update some images'
            }), {
                status: 500,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        return new Response(JSON.stringify({
            success: true,
            message: 'Image order updated successfully'
        }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' }
        });

    } catch (error) {
        console.error('Update image order error:', error);
        return new Response(JSON.stringify({
            success: false,
            error: 'Internal server error'
        }), {
            status: 500,
            headers: { 'Content-Type': 'application/json' }
        });
    }
};
