-- ============================================================================
-- COMPLETE GUESTS SETUP - ONE SCRIPT FOR EVERYTHING
-- Run this once in Supabase SQL Editor to set up separate guests table
-- ============================================================================

-- Step 1: Create guests table
CREATE TABLE IF NOT EXISTS guests (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    property_id UUID NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    CONSTRAINT guests_email_property_unique UNIQUE (email, property_id)
);

-- Step 2: Add indexes
CREATE INDEX IF NOT EXISTS idx_guests_property_id ON guests(property_id);
CREATE INDEX IF NOT EXISTS idx_guests_email ON guests(email);

-- Step 3: Add guest_id to bookings table
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_id UUID REFERENCES guests(id);

-- Step 4: Migrate existing data from bookings to guests
INSERT INTO guests (property_id, name, email, phone)
SELECT DISTINCT 
    b.property_id,
    b.guest_name as name,
    b.guest_email as email,
    b.guest_phone as phone
FROM bookings b
WHERE b.guest_email IS NOT NULL 
    AND b.guest_name IS NOT NULL
    AND b.guest_email != ''
    AND b.guest_name != ''
ON CONFLICT (email, property_id) DO NOTHING;

-- Step 5: Link existing bookings to guests
UPDATE bookings 
SET guest_id = g.id
FROM guests g
WHERE bookings.guest_email = g.email 
    AND bookings.property_id = g.property_id
    AND bookings.guest_id IS NULL;

-- Step 6: Remove any existing problematic view
DROP VIEW IF EXISTS guest_statistics CASCADE;

-- Step 7: Create simple guest statistics view
CREATE VIEW guest_statistics AS
SELECT 
    g.id,
    g.property_id,
    g.name,
    g.email,
    g.phone,
    g.created_at,
    COUNT(b.id) as total_bookings,
    COUNT(CASE WHEN b.status = 'confirmed' THEN 1 END) as confirmed_bookings,
    SUM(CASE WHEN b.status IN ('confirmed', 'checked_in', 'checked_out') THEN COALESCE(b.total_price, 0) ELSE 0 END) as total_revenue,
    MAX(b.check_out) as last_visit,
    CASE 
        WHEN COUNT(CASE WHEN b.status = 'checked_out' THEN 1 END) >= 5 THEN 'VIP'
        WHEN COUNT(CASE WHEN b.status = 'checked_out' THEN 1 END) >= 2 THEN 'Gold'
        ELSE 'Bronze'
    END as loyalty_level
FROM guests g
LEFT JOIN bookings b ON g.id = b.guest_id
GROUP BY g.id, g.property_id, g.name, g.email, g.phone, g.created_at;

-- Step 8: Enable RLS on guests table
ALTER TABLE guests ENABLE ROW LEVEL SECURITY;

-- Step 9: Create simple policy for testing (allows all operations)
DROP POLICY IF EXISTS "Allow all operations on guests" ON guests;
CREATE POLICY "Allow all operations on guests" ON guests
    FOR ALL USING (true) WITH CHECK (true);

-- Step 10: Test everything works
SELECT 
    'Setup Complete!' as status,
    COUNT(*) as guest_count 
FROM guests
UNION ALL
SELECT 
    'Bookings with guest_id' as status,
    COUNT(*) as count 
FROM bookings 
WHERE guest_id IS NOT NULL
UNION ALL
SELECT 
    'Guest statistics view' as status,
    COUNT(*) as count 
FROM guest_statistics;

-- ============================================================================
-- DONE! 
-- Your guests are now in a separate table and won't disappear when you 
-- change reservations. The API will automatically create/link guests.
-- ============================================================================
