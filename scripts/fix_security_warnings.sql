-- Fix Security Warnings for Testing Environment
-- Run this after add_guests_table.sql

-- ============================================================================
-- Fix RLS warning for guests table
-- ============================================================================

-- Enable RLS on guests table
ALTER TABLE guests ENABLE ROW LEVEL SECURITY;

-- Create simple policy for testing (allows all operations)
DROP POLICY IF EXISTS "Allow all operations on guests" ON guests;
CREATE POLICY "Allow all operations on guests" ON guests
    FOR ALL USING (true) WITH CHECK (true);

-- ============================================================================
-- Fix SECURITY DEFINER warning for guest_statistics view
-- ============================================================================

-- Force drop and recreate view to remove SECURITY DEFINER
DROP VIEW IF EXISTS guest_statistics CASCADE;

-- Create view without security options (defaults to SECURITY INVOKER)
CREATE VIEW guest_statistics AS
SELECT 
    g.id,
    g.property_id,
    g.name,
    g.email,
    g.phone,
    g.created_at,
    COALESCE(COUNT(b.id), 0) as total_bookings,
    COALESCE(COUNT(CASE WHEN b.status = 'confirmed' THEN 1 END), 0) as confirmed_bookings,
    COALESCE(SUM(CASE WHEN b.status IN ('confirmed', 'checked_in', 'checked_out') THEN b.total_price ELSE 0 END), 0) as total_revenue,
    MAX(b.check_out) as last_visit,
    -- Simple loyalty level
    CASE 
        WHEN COUNT(CASE WHEN b.status = 'checked_out' THEN 1 END) >= 5 THEN 'VIP'
        WHEN COUNT(CASE WHEN b.status = 'checked_out' THEN 1 END) >= 2 THEN 'Gold'
        ELSE 'Bronze'
    END as loyalty_level
FROM guests g
LEFT JOIN bookings b ON g.id = b.guest_id
GROUP BY g.id, g.property_id, g.name, g.email, g.phone, g.created_at;

-- ============================================================================
-- Test the fixes
-- ============================================================================

-- Check if RLS is enabled
SELECT schemaname, tablename, rowsecurity 
FROM pg_tables 
WHERE tablename = 'guests';

-- Test the view
SELECT 'guest_statistics test' as test, COUNT(*) as count FROM guest_statistics;
