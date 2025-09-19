-- ============================================================================
-- ACCOMMODATION IMAGES SYSTEM
-- ============================================================================

-- Step 1: Create accommodation_images table
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

-- Step 2: Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_accommodation_images_accommodation_id ON accommodation_images(accommodation_id);
CREATE INDEX IF NOT EXISTS idx_accommodation_images_sort_order ON accommodation_images(accommodation_id, sort_order);
CREATE INDEX IF NOT EXISTS idx_accommodation_images_primary ON accommodation_images(accommodation_id, is_primary);

-- Step 3: Create trigger to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_accommodation_images_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_accommodation_images_timestamp
    BEFORE UPDATE ON accommodation_images
    FOR EACH ROW
    EXECUTE FUNCTION update_accommodation_images_timestamp();

-- Step 4: Create trigger to ensure only one primary image per accommodation
CREATE OR REPLACE FUNCTION ensure_single_primary_image()
RETURNS TRIGGER AS $$
BEGIN
    -- If setting this image as primary, unset all others for this accommodation
    IF NEW.is_primary = true THEN
        UPDATE accommodation_images 
        SET is_primary = false 
        WHERE accommodation_id = NEW.accommodation_id 
        AND id != NEW.id;
    END IF;
    
    -- If unsetting primary and no other primary exists, make this one primary
    IF NEW.is_primary = false THEN
        IF NOT EXISTS (
            SELECT 1 FROM accommodation_images 
            WHERE accommodation_id = NEW.accommodation_id 
            AND is_primary = true 
            AND id != NEW.id
        ) THEN
            NEW.is_primary = true;
        END IF;
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER ensure_single_primary_image
    BEFORE INSERT OR UPDATE ON accommodation_images
    FOR EACH ROW
    EXECUTE FUNCTION ensure_single_primary_image();

-- Step 5: Add sample images for existing accommodations (placeholder URLs)
INSERT INTO accommodation_images (accommodation_id, url, filename, original_name, file_size, mime_type, sort_order, is_primary, alt_text)
SELECT 
    id,
    'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=600&fit=crop&crop=center',
    'sample_' || id || '_1.jpg',
    'Hoofdfoto.jpg',
    150000,
    'image/jpeg',
    0,
    true,
    name || ' - Hoofdfoto'
FROM accommodations 
WHERE id NOT IN (SELECT DISTINCT accommodation_id FROM accommodation_images);

-- Add secondary images
INSERT INTO accommodation_images (accommodation_id, url, filename, original_name, file_size, mime_type, sort_order, is_primary, alt_text)
SELECT 
    id,
    'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=800&h=600&fit=crop&crop=center',
    'sample_' || id || '_2.jpg',
    'Interieur.jpg',
    120000,
    'image/jpeg',
    1,
    false,
    name || ' - Interieur'
FROM accommodations 
WHERE id NOT IN (
    SELECT accommodation_id FROM accommodation_images 
    WHERE sort_order = 1
);

-- Add third images
INSERT INTO accommodation_images (accommodation_id, url, filename, original_name, file_size, mime_type, sort_order, is_primary, alt_text)
SELECT 
    id,
    'https://images.unsplash.com/photo-1571896349842-33c89424de2d?w=800&h=600&fit=crop&crop=center',
    'sample_' || id || '_3.jpg',
    'Omgeving.jpg',
    180000,
    'image/jpeg',
    2,
    false,
    name || ' - Omgeving'
FROM accommodations 
WHERE id NOT IN (
    SELECT accommodation_id FROM accommodation_images 
    WHERE sort_order = 2
);

-- Step 6: Update accommodations table to include photos array (for backward compatibility)
UPDATE accommodations 
SET photos = ARRAY(
    SELECT url 
    FROM accommodation_images 
    WHERE accommodation_id = accommodations.id 
    ORDER BY sort_order ASC
)
WHERE EXISTS (
    SELECT 1 FROM accommodation_images 
    WHERE accommodation_id = accommodations.id
);

-- Step 7: Create view for easy access to accommodation with primary image
CREATE OR REPLACE VIEW accommodations_with_images AS
SELECT 
    a.*,
    ai.url as primary_image_url,
    ai.alt_text as primary_image_alt,
    (
        SELECT COUNT(*) 
        FROM accommodation_images 
        WHERE accommodation_id = a.id
    ) as total_images,
    (
        SELECT ARRAY_AGG(url ORDER BY sort_order) 
        FROM accommodation_images 
        WHERE accommodation_id = a.id
    ) as all_image_urls
FROM accommodations a
LEFT JOIN accommodation_images ai ON a.id = ai.accommodation_id AND ai.is_primary = true
WHERE a.active = true;

-- Step 8: Test the setup
SELECT 'Accommodation images system created successfully!' as status;

-- Show sample data
SELECT 
    a.name,
    COUNT(ai.id) as image_count,
    STRING_AGG(
        CASE WHEN ai.is_primary THEN '(PRIMARY) ' ELSE '' END || ai.original_name, 
        ', ' ORDER BY ai.sort_order
    ) as images
FROM accommodations a
LEFT JOIN accommodation_images ai ON a.id = ai.accommodation_id
GROUP BY a.id, a.name
ORDER BY a.created_at;
