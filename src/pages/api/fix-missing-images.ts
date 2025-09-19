import type { APIRoute } from 'astro';
import { supabaseAdmin } from '../../lib/supabase';

export const post: APIRoute = async () => {
    try {
        console.log('Checking for missing image files...');
        
        // Get all images from database
        const { data: images, error } = await supabaseAdmin
            .from('accommodation_images')
            .select('*')
            .order('created_at', { ascending: true });
            
        if (error) {
            return new Response(JSON.stringify({
                success: false,
                error: 'Failed to fetch images from database'
            }), {
                status: 500,
                headers: { 'Content-Type': 'application/json' }
            });
        }
        
        const fs = await import('fs');
        const missingFiles = [];
        const fixedFiles = [];
        
        for (const image of images || []) {
            // Skip external URLs (Unsplash, etc.)
            if (image.url.startsWith('http')) {
                continue;
            }
            
            const filePath = `./public${image.url}`;
            
            // Check if file exists
            if (!fs.existsSync(filePath)) {
                missingFiles.push({
                    id: image.id,
                    filename: image.filename,
                    url: image.url,
                    original_name: image.original_name
                });
                
                try {
                    // Create a placeholder image by downloading from Unsplash
                    const placeholderUrl = 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?w=800&h=600&fit=crop&crop=center';
                    
                    const response = await fetch(placeholderUrl);
                    if (response.ok) {
                        const arrayBuffer = await response.arrayBuffer();
                        const buffer = new Uint8Array(arrayBuffer);
                        
                        // Ensure directory exists
                        const dir = filePath.substring(0, filePath.lastIndexOf('/'));
                        if (!fs.existsSync(dir)) {
                            fs.mkdirSync(dir, { recursive: true });
                        }
                        
                        // Save the placeholder image
                        fs.writeFileSync(filePath, buffer);
                        
                        fixedFiles.push({
                            id: image.id,
                            filename: image.filename,
                            status: 'fixed'
                        });
                        
                        console.log(`Fixed missing image: ${image.filename}`);
                    }
                } catch (downloadError) {
                    console.error(`Failed to fix ${image.filename}:`, downloadError);
                }
            }
        }
        
        return new Response(JSON.stringify({
            success: true,
            message: `Checked ${images?.length || 0} images`,
            data: {
                total_images: images?.length || 0,
                missing_files: missingFiles.length,
                fixed_files: fixedFiles.length,
                missing: missingFiles,
                fixed: fixedFiles
            }
        }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' }
        });

    } catch (error) {
        console.error('Error fixing missing images:', error);
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
