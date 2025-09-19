import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

/**
 * Accommodation Images API
 * Get images for a specific accommodation
 */
export const get: APIRoute = async ({ url }) => {
    try {
        const accommodationId = url.searchParams.get('id');

        if (!accommodationId) {
            return new Response(JSON.stringify({
                success: false,
                error: 'Accommodation ID is required'
            }), {
                status: 400,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        // Get images for this accommodation
        const { data: images, error } = await supabaseAdmin
            .from('accommodation_images')
            .select('id, url, alt_text, is_primary, sort_order, filename, created_at')
            .eq('accommodation_id', accommodationId)
            .order('sort_order', { ascending: true });

        if (error) {
            console.error('Database error:', error);
            return new Response(JSON.stringify({
                success: false,
                error: 'Failed to fetch images'
            }), {
                status: 500,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        return new Response(JSON.stringify({
            success: true,
            data: images || []
        }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' }
        });

    } catch (error) {
        console.error('Get images error:', error);
        return new Response(JSON.stringify({
            success: false,
            error: 'Internal server error'
        }), {
            status: 500,
            headers: { 'Content-Type': 'application/json' }
        });
    }
};
