import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

/**
 * Upload Image API
 * Handles image upload for accommodations
 */
export const post: APIRoute = async ({ request }) => {
    try {
        const formData = await request.formData();
        const image = formData.get('image') as File;
        const accommodationId = formData.get('accommodation_id') as string;

        if (!image || !accommodationId) {
            return new Response(JSON.stringify({
                success: false,
                error: 'Image and accommodation_id are required'
            }), {
                status: 400,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        // Validate file type
        if (!image.type.startsWith('image/')) {
            return new Response(JSON.stringify({
                success: false,
                error: 'File must be an image'
            }), {
                status: 400,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        // Validate file size (5MB)
        if (image.size > 5 * 1024 * 1024) {
            return new Response(JSON.stringify({
                success: false,
                error: 'File size must be less than 5MB'
            }), {
                status: 400,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        // Generate unique filename
        const timestamp = Date.now();
        const randomString = Math.random().toString(36).substring(2, 15);
        const extension = image.name.split('.').pop()?.toLowerCase() || 'jpg';
        const filename = `accommodation_${accommodationId}_${timestamp}_${randomString}.${extension}`;

        // For now, we'll simulate upload and store a placeholder URL
        // In production, you'd upload to Supabase Storage, AWS S3, or similar
        const imageUrl = `/uploads/${filename}`;

        // Get current image count for ordering
        const { count } = await supabaseAdmin
            .from('accommodation_images')
            .select('*', { count: 'exact', head: true })
            .eq('accommodation_id', accommodationId);

        // Insert image record into database
        const { data: imageRecord, error: insertError } = await supabaseAdmin
            .from('accommodation_images')
            .insert({
                accommodation_id: accommodationId,
                url: imageUrl,
                filename: filename,
                original_name: image.name,
                file_size: image.size,
                mime_type: image.type,
                sort_order: (count || 0),
                is_primary: (count || 0) === 0 // First image is primary
            })
            .select()
            .single();

        if (insertError) {
            console.error('Database insert error:', insertError);
            return new Response(JSON.stringify({
                success: false,
                error: 'Failed to save image record'
            }), {
                status: 500,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        // Save the actual file to the uploads directory
        try {
            const uploadsDir = './public/uploads';
            const filePath = `${uploadsDir}/${filename}`;
            
            // Ensure uploads directory exists
            const fs = await import('fs');
            
            if (!fs.existsSync(uploadsDir)) {
                fs.mkdirSync(uploadsDir, { recursive: true });
            }
            
            // Convert File to Buffer and save
            const arrayBuffer = await image.arrayBuffer();
            const buffer = new Uint8Array(arrayBuffer);
            
            fs.writeFileSync(filePath, buffer);
            console.log(`Image saved successfully: ${filename} (${image.size} bytes)`);
            
        } catch (fileError) {
            console.error('Error saving file:', fileError);
            
            // Clean up database record if file save failed
            await supabaseAdmin
                .from('accommodation_images')
                .delete()
                .eq('id', imageRecord.id);
                
            return new Response(JSON.stringify({
                success: false,
                error: 'Failed to save image file'
            }), {
                status: 500,
                headers: { 'Content-Type': 'application/json' }
            });
        }

        return new Response(JSON.stringify({
            success: true,
            data: {
                id: imageRecord.id,
                url: imageRecord.url,
                filename: imageRecord.filename,
                original_name: imageRecord.original_name,
                sort_order: imageRecord.sort_order,
                is_primary: imageRecord.is_primary
            }
        }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' }
        });

    } catch (error) {
        console.error('Image upload error:', error);
        return new Response(JSON.stringify({
            success: false,
            error: 'Internal server error'
        }), {
            status: 500,
            headers: { 'Content-Type': 'application/json' }
        });
    }
};
