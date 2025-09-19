-- Migration 002: Separate Guests Table
-- This migration creates a separate guests table and migrates existing guest data
-- Run this script in your Supabase SQL editor

-- ============================================================================
-- STEP 1: Create separate guests table
-- ============================================================================

CREATE TABLE IF NOT EXISTS guests (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    property_id UUID NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Nederland',
    postal_code VARCHAR(20),
    date_of_birth DATE,
    nationality VARCHAR(100),
    id_number VARCHAR(100), -- Passport/ID number for registration
    notes TEXT, -- Internal notes about the guest
    preferences TEXT, -- Guest preferences (dietary, room type, etc.)
    marketing_consent BOOLEAN DEFAULT false, -- GDPR consent for marketing
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    
    -- Constraints
    CONSTRAINT guests_email_property_unique UNIQUE (email, property_id),
    CONSTRAINT guests_email_format CHECK (email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$')
);

-- Add indexes for performance
CREATE INDEX IF NOT EXISTS idx_guests_property_id ON guests(property_id);
CREATE INDEX IF NOT EXISTS idx_guests_email ON guests(email);
CREATE INDEX IF NOT EXISTS idx_guests_name ON guests(name);
CREATE INDEX IF NOT EXISTS idx_guests_created_at ON guests(created_at);

-- ============================================================================
-- STEP 2: Migrate existing guest data from bookings
-- ============================================================================

-- Insert unique guests from existing bookings
INSERT INTO guests (property_id, name, email, phone, created_at)
SELECT DISTINCT 
    b.property_id,
    b.guest_name as name,
    b.guest_email as email,
    b.guest_phone as phone,
    MIN(b.created_at) as created_at -- Use earliest booking date as guest creation date
FROM bookings b
WHERE b.guest_email IS NOT NULL 
    AND b.guest_name IS NOT NULL
    AND b.guest_email != ''
    AND b.guest_name != ''
GROUP BY b.property_id, b.guest_name, b.guest_email, b.guest_phone
ON CONFLICT (email, property_id) DO NOTHING; -- Skip duplicates

-- ============================================================================
-- STEP 3: Add guest_id column to bookings table
-- ============================================================================

-- Add the new guest_id column
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS guest_id UUID;

-- Update existing bookings with guest_id
UPDATE bookings 
SET guest_id = g.id
FROM guests g
WHERE bookings.guest_email = g.email 
    AND bookings.property_id = g.property_id
    AND bookings.guest_id IS NULL;

-- ============================================================================
-- STEP 4: Create guest statistics view (optional but useful)
-- ============================================================================

CREATE OR REPLACE VIEW guest_statistics AS
SELECT 
    g.id,
    g.name,
    g.email,
    g.phone,
    g.created_at as first_visit,
    COUNT(b.id) as total_bookings,
    COUNT(CASE WHEN b.status = 'confirmed' THEN 1 END) as confirmed_bookings,
    COUNT(CASE WHEN b.status = 'checked_out' THEN 1 END) as completed_stays,
    SUM(CASE WHEN b.status IN ('confirmed', 'checked_in', 'checked_out') THEN b.total_price ELSE 0 END) as total_revenue,
    MAX(b.check_out) as last_visit,
    AVG(CASE WHEN b.status = 'checked_out' THEN b.total_price END) as average_booking_value,
    -- Calculate guest loyalty level
    CASE 
        WHEN COUNT(CASE WHEN b.status = 'checked_out' THEN 1 END) >= 10 THEN 'VIP'
        WHEN COUNT(CASE WHEN b.status = 'checked_out' THEN 1 END) >= 5 THEN 'Gold'
        WHEN COUNT(CASE WHEN b.status = 'checked_out' THEN 1 END) >= 2 THEN 'Silver'
        ELSE 'Bronze'
    END as loyalty_level
FROM guests g
LEFT JOIN bookings b ON g.id = b.guest_id
GROUP BY g.id, g.name, g.email, g.phone, g.created_at;

-- ============================================================================
-- STEP 5: Create updated_at trigger for guests table
-- ============================================================================

-- Function to update the updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create trigger for guests table
DROP TRIGGER IF EXISTS update_guests_updated_at ON guests;
CREATE TRIGGER update_guests_updated_at
    BEFORE UPDATE ON guests
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- ============================================================================
-- STEP 6: Enable Row Level Security (RLS) for guests table
-- ============================================================================

-- Enable RLS
ALTER TABLE guests ENABLE ROW LEVEL SECURITY;

-- Policy for property owners/managers to see their guests
CREATE POLICY "Users can view guests from their properties" ON guests
    FOR SELECT USING (
        property_id IN (
            SELECT id FROM properties 
            WHERE owner_id = auth.uid()
        )
    );

-- Policy for property owners/managers to insert guests
CREATE POLICY "Users can insert guests for their properties" ON guests
    FOR INSERT WITH CHECK (
        property_id IN (
            SELECT id FROM properties 
            WHERE owner_id = auth.uid()
        )
    );

-- Policy for property owners/managers to update their guests
CREATE POLICY "Users can update guests from their properties" ON guests
    FOR UPDATE USING (
        property_id IN (
            SELECT id FROM properties 
            WHERE owner_id = auth.uid()
        )
    );

-- Policy for property owners/managers to delete their guests
CREATE POLICY "Users can delete guests from their properties" ON guests
    FOR DELETE USING (
        property_id IN (
            SELECT id FROM properties 
            WHERE owner_id = auth.uid()
        )
    );

-- ============================================================================
-- STEP 7: Verification queries (run these to check the migration)
-- ============================================================================

-- Check if all guests were migrated
-- SELECT 
--     'Unique guests in bookings' as source,
--     COUNT(DISTINCT guest_email) as count
-- FROM bookings 
-- WHERE guest_email IS NOT NULL AND guest_email != ''
-- UNION ALL
-- SELECT 
--     'Guests in guests table' as source,
--     COUNT(*) as count
-- FROM guests;

-- Check if all bookings have guest_id
-- SELECT 
--     COUNT(*) as total_bookings,
--     COUNT(guest_id) as bookings_with_guest_id,
--     COUNT(*) - COUNT(guest_id) as bookings_without_guest_id
-- FROM bookings;

-- Show guest statistics
-- SELECT * FROM guest_statistics ORDER BY total_bookings DESC LIMIT 10;

-- ============================================================================
-- NOTES FOR FUTURE STEPS:
-- ============================================================================

-- After this migration is successful and tested, you can:
-- 1. Update your API endpoints to use guest_id instead of guest_name/email/phone
-- 2. Make guest_id NOT NULL in bookings table
-- 3. Optionally remove guest_name, guest_email, guest_phone from bookings table
-- 4. Add foreign key constraint: ALTER TABLE bookings ADD FOREIGN KEY (guest_id) REFERENCES guests(id);

-- To rollback this migration (if needed):
-- 1. DROP TABLE guests CASCADE;
-- 2. ALTER TABLE bookings DROP COLUMN guest_id;

COMMIT;
