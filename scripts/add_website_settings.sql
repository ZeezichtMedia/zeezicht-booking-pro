-- ============================================================================
-- HYBRID SETTINGS SYSTEM - Best Practice Implementation
-- ============================================================================

-- Step 1: Create business_settings table for core business configuration
CREATE TABLE IF NOT EXISTS business_settings (
    property_id UUID PRIMARY KEY REFERENCES properties(id) ON DELETE CASCADE,
    business_type VARCHAR(50) NOT NULL DEFAULT 'minicamping',
    website_url VARCHAR(255),
    contact_email VARCHAR(255),
    contact_phone VARCHAR(50),
    business_address TEXT,
    business_description TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    
    -- Constraints
    CONSTRAINT valid_business_type CHECK (business_type IN (
        'bnb', 'minicamping', 'camping', 'hotel', 'vakantiepark', 'glamping', 'hostel'
    )),
    CONSTRAINT valid_email CHECK (contact_email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'),
    CONSTRAINT valid_website CHECK (website_url IS NULL OR website_url ~* '^https?://')
);

-- Step 2: Create booking_settings table for booking configuration
CREATE TABLE IF NOT EXISTS booking_settings (
    property_id UUID PRIMARY KEY REFERENCES properties(id) ON DELETE CASCADE,
    min_stay_nights INTEGER DEFAULT 2 CHECK (min_stay_nights >= 1),
    max_advance_days INTEGER DEFAULT 365 CHECK (max_advance_days >= 30),
    checkin_time TIME DEFAULT '15:00:00',
    checkout_time TIME DEFAULT '11:00:00',
    booking_notification_email VARCHAR(255),
    auto_confirmation BOOLEAN DEFAULT true,
    booking_page_title VARCHAR(100) DEFAULT 'Reserveren',
    accommodations_page_title VARCHAR(100) DEFAULT 'Onze Accommodaties',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    
    -- Constraints
    CONSTRAINT valid_notification_email CHECK (
        booking_notification_email IS NULL OR 
        booking_notification_email ~* '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$'
    ),
    CONSTRAINT valid_times CHECK (checkin_time < checkout_time)
);

-- Step 3: Create display_settings table for UI preferences
CREATE TABLE IF NOT EXISTS display_settings (
    property_id UUID PRIMARY KEY REFERENCES properties(id) ON DELETE CASCADE,
    accommodation_layout VARCHAR(20) DEFAULT 'grid' CHECK (accommodation_layout IN ('grid', 'list', 'masonry')),
    show_prices BOOLEAN DEFAULT true,
    show_availability BOOLEAN DEFAULT true,
    show_amenities BOOLEAN DEFAULT true,
    items_per_page INTEGER DEFAULT 12 CHECK (items_per_page BETWEEN 6 AND 50),
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Step 4: Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_business_settings_business_type ON business_settings(business_type);
CREATE INDEX IF NOT EXISTS idx_business_settings_updated_at ON business_settings(updated_at);
CREATE INDEX IF NOT EXISTS idx_booking_settings_updated_at ON booking_settings(updated_at);
CREATE INDEX IF NOT EXISTS idx_display_settings_updated_at ON display_settings(updated_at);

-- Step 5: Insert default settings for existing properties
INSERT INTO business_settings (
    property_id, 
    business_type, 
    contact_email,
    business_description
)
SELECT 
    id,
    'minicamping',
    'info@zee-zicht.nl',
    'Welkom bij onze accommodatie'
FROM properties 
WHERE id NOT IN (SELECT property_id FROM business_settings);

INSERT INTO booking_settings (
    property_id,
    min_stay_nights,
    max_advance_days,
    checkin_time,
    checkout_time,
    auto_confirmation,
    booking_page_title,
    accommodations_page_title
)
SELECT 
    id,
    2,
    365,
    '15:00:00',
    '11:00:00',
    true,
    'Reserveren',
    'Onze Accommodaties'
FROM properties 
WHERE id NOT IN (SELECT property_id FROM booking_settings);

INSERT INTO display_settings (
    property_id,
    accommodation_layout,
    show_prices,
    show_availability,
    show_amenities
)
SELECT 
    id,
    'grid',
    true,
    true,
    true
FROM properties 
WHERE id NOT IN (SELECT property_id FROM display_settings);

-- Step 6: Create a view for easy access to all settings
CREATE OR REPLACE VIEW property_settings_view AS
SELECT 
    p.id as property_id,
    p.name as property_name,
    p.domain,
    
    -- Business settings
    bs.business_type,
    bs.website_url,
    bs.contact_email,
    bs.contact_phone,
    bs.business_address,
    bs.business_description,
    
    -- Booking settings
    bks.min_stay_nights,
    bks.max_advance_days,
    bks.checkin_time,
    bks.checkout_time,
    bks.booking_notification_email,
    bks.auto_confirmation,
    bks.booking_page_title,
    bks.accommodations_page_title,
    
    -- Display settings
    ds.accommodation_layout,
    ds.show_prices,
    ds.show_availability,
    ds.show_amenities,
    ds.items_per_page,
    
    -- Timestamps
    GREATEST(bs.updated_at, bks.updated_at, ds.updated_at) as last_updated
    
FROM properties p
LEFT JOIN business_settings bs ON p.id = bs.property_id
LEFT JOIN booking_settings bks ON p.id = bks.property_id  
LEFT JOIN display_settings ds ON p.id = ds.property_id
WHERE p.active = true;

-- Step 7: Create update triggers to maintain updated_at timestamps
CREATE OR REPLACE FUNCTION update_settings_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

CREATE TRIGGER update_business_settings_timestamp
    BEFORE UPDATE ON business_settings
    FOR EACH ROW
    EXECUTE FUNCTION update_settings_timestamp();

CREATE TRIGGER update_booking_settings_timestamp
    BEFORE UPDATE ON booking_settings
    FOR EACH ROW
    EXECUTE FUNCTION update_settings_timestamp();

CREATE TRIGGER update_display_settings_timestamp
    BEFORE UPDATE ON display_settings
    FOR EACH ROW
    EXECUTE FUNCTION update_settings_timestamp();

-- Step 8: Test the setup
SELECT 'Hybrid settings system created successfully!' as status;

-- Show current settings for verification
SELECT 
    property_name,
    business_type,
    website_url,
    booking_page_title,
    accommodation_layout,
    last_updated
FROM property_settings_view
LIMIT 5;
