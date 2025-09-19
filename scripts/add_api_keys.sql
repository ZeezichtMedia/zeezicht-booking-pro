-- ============================================================================
-- API KEY AUTHENTICATION SYSTEM
-- ============================================================================

-- Step 1: Add API key columns to properties table
ALTER TABLE properties ADD COLUMN IF NOT EXISTS api_key VARCHAR(100) UNIQUE;
ALTER TABLE properties ADD COLUMN IF NOT EXISTS allowed_domains TEXT[] DEFAULT ARRAY[]::TEXT[];
ALTER TABLE properties ADD COLUMN IF NOT EXISTS plugin_version VARCHAR(20);
ALTER TABLE properties ADD COLUMN IF NOT EXISTS last_plugin_sync TIMESTAMP WITH TIME ZONE;

-- Step 2: Create function to generate secure API keys
CREATE OR REPLACE FUNCTION generate_api_key() 
RETURNS TEXT AS $$
BEGIN
    RETURN 'zzbp_live_' || encode(gen_random_bytes(16), 'hex');
END;
$$ LANGUAGE plpgsql;

-- Step 3: Generate API keys for existing properties
UPDATE properties 
SET api_key = generate_api_key(),
    allowed_domains = ARRAY[domain, 'www.' || domain]
WHERE api_key IS NULL;

-- Step 4: Set default API key for new properties
ALTER TABLE properties 
ALTER COLUMN api_key SET DEFAULT generate_api_key();

-- Step 5: Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_properties_api_key ON properties(api_key);
CREATE INDEX IF NOT EXISTS idx_properties_last_sync ON properties(last_plugin_sync);

-- Step 6: Create plugin_logs table for tracking
CREATE TABLE IF NOT EXISTS plugin_logs (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    property_id UUID NOT NULL REFERENCES properties(id) ON DELETE CASCADE,
    api_key VARCHAR(100) NOT NULL,
    domain VARCHAR(255) NOT NULL,
    endpoint VARCHAR(100) NOT NULL,
    method VARCHAR(10) NOT NULL,
    ip_address INET,
    user_agent TEXT,
    response_status INTEGER,
    response_time_ms INTEGER,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Index for plugin logs
CREATE INDEX IF NOT EXISTS idx_plugin_logs_property_id ON plugin_logs(property_id);
CREATE INDEX IF NOT EXISTS idx_plugin_logs_created_at ON plugin_logs(created_at);
CREATE INDEX IF NOT EXISTS idx_plugin_logs_api_key ON plugin_logs(api_key);

-- Step 7: Create view for plugin statistics
CREATE OR REPLACE VIEW plugin_stats AS
SELECT 
    p.id as property_id,
    p.name as property_name,
    p.domain,
    p.api_key,
    p.plugin_version,
    p.last_plugin_sync,
    COUNT(pl.id) as total_requests,
    COUNT(CASE WHEN pl.created_at > NOW() - INTERVAL '24 hours' THEN 1 END) as requests_24h,
    COUNT(CASE WHEN pl.created_at > NOW() - INTERVAL '7 days' THEN 1 END) as requests_7d,
    AVG(pl.response_time_ms) as avg_response_time,
    MAX(pl.created_at) as last_request
FROM properties p
LEFT JOIN plugin_logs pl ON p.id = pl.property_id
WHERE p.active = true
GROUP BY p.id, p.name, p.domain, p.api_key, p.plugin_version, p.last_plugin_sync;

-- Step 8: Test the setup
SELECT 'API key system setup complete!' as status;

-- Show generated API keys
SELECT 
    name as property_name,
    domain,
    api_key,
    array_to_string(allowed_domains, ', ') as allowed_domains
FROM properties 
WHERE active = true
ORDER BY created_at;

-- Show plugin stats view
SELECT * FROM plugin_stats LIMIT 5;
