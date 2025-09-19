-- Force fix for SECURITY DEFINER warning
-- Run this if the previous script didn't work

-- ============================================================================
-- Completely remove and recreate guest_statistics view
-- ============================================================================

-- Drop view with CASCADE to remove any dependencies
DROP VIEW IF EXISTS guest_statistics CASCADE;

-- Recreate view without any security options (defaults to SECURITY INVOKER)
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
    CASE 
        WHEN COUNT(CASE WHEN b.status = 'checked_out' THEN 1 END) >= 5 THEN 'VIP'
        WHEN COUNT(CASE WHEN b.status = 'checked_out' THEN 1 END) >= 2 THEN 'Gold'
        ELSE 'Bronze'
    END as loyalty_level
FROM guests g
LEFT JOIN bookings b ON g.id = b.guest_id
GROUP BY g.id, g.property_id, g.name, g.email, g.phone, g.created_at;

-- ============================================================================
-- Alternative: Create a simple table instead of view (if view keeps causing issues)
-- ============================================================================

-- Uncomment below if view continues to have security issues:

-- DROP VIEW IF EXISTS guest_statistics CASCADE;
-- 
-- CREATE TABLE guest_statistics_cache AS
-- SELECT 
--     g.id,
--     g.property_id,
--     g.name,
--     g.email,
--     g.phone,
--     g.created_at,
--     COALESCE(COUNT(b.id), 0) as total_bookings,
--     COALESCE(COUNT(CASE WHEN b.status = 'confirmed' THEN 1 END), 0) as confirmed_bookings,
--     COALESCE(SUM(CASE WHEN b.status IN ('confirmed', 'checked_in', 'checked_out') THEN b.total_price ELSE 0 END), 0) as total_revenue,
--     MAX(b.check_out) as last_visit,
--     CASE 
--         WHEN COUNT(CASE WHEN b.status = 'checked_out' THEN 1 END) >= 5 THEN 'VIP'
--         WHEN COUNT(CASE WHEN b.status = 'checked_out' THEN 1 END) >= 2 THEN 'Gold'
--         ELSE 'Bronze'
--     END as loyalty_level
-- FROM guests g
-- LEFT JOIN bookings b ON g.id = b.guest_id
-- GROUP BY g.id, g.property_id, g.name, g.email, g.phone, g.created_at;

-- ============================================================================
-- Verify the fix
-- ============================================================================

-- Check view properties
SELECT 
    schemaname, 
    viewname,
    definition
FROM pg_views 
WHERE viewname = 'guest_statistics';

-- Test the view works
SELECT COUNT(*) as guest_count FROM guest_statistics;
