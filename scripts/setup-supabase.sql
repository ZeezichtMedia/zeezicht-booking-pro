-- Zee-zicht PMS Database Schema
-- Run this in your Supabase SQL editor

-- Enable UUID extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Properties table (tenants/clients)
CREATE TABLE IF NOT EXISTS properties (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    name VARCHAR(255) NOT NULL,
    domain VARCHAR(255) UNIQUE NOT NULL,
    api_key VARCHAR(255) UNIQUE NOT NULL,
    settings JSONB DEFAULT '{}',
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Enable RLS for multi-tenancy
ALTER TABLE properties ENABLE ROW LEVEL SECURITY;

-- Policy: Properties are viewable by API key or service role
DROP POLICY IF EXISTS "Properties access control" ON properties;
CREATE POLICY "Properties access control" ON properties
    FOR ALL USING (
        api_key = current_setting('request.jwt.claims', true)::json->>'api_key'
        OR auth.role() = 'service_role'
        OR auth.role() = 'authenticated'
    );

-- Accommodations table
CREATE TABLE IF NOT EXISTS accommodations (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    property_id UUID REFERENCES properties(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    type VARCHAR(100) NOT NULL,
    max_guests INTEGER NOT NULL,
    surface_area VARCHAR(50),
    description TEXT,
    amenities JSONB DEFAULT '[]',
    photos JSONB DEFAULT '[]',
    base_price DECIMAL(10,2),
    active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Enable RLS for accommodations
ALTER TABLE accommodations ENABLE ROW LEVEL SECURITY;

-- Policy: Accommodations are viewable by property API key
DROP POLICY IF EXISTS "Accommodations access by property" ON accommodations;
CREATE POLICY "Accommodations access by property" ON accommodations
    FOR ALL USING (
        property_id IN (
            SELECT id FROM properties 
            WHERE api_key = current_setting('request.jwt.claims', true)::json->>'api_key'
        )
        OR auth.role() = 'service_role'
        OR auth.role() = 'authenticated'
    );

-- Bookings table (basic structure)
CREATE TABLE IF NOT EXISTS bookings (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    property_id UUID REFERENCES properties(id) ON DELETE CASCADE,
    accommodation_id UUID REFERENCES accommodations(id) ON DELETE CASCADE,
    
    -- Gastgegevens
    guest_name VARCHAR(255) NOT NULL,
    guest_email VARCHAR(255) NOT NULL,
    guest_phone VARCHAR(50),
    
    -- Reservering details
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INTEGER NOT NULL,
    
    -- Basic pricing
    total_price DECIMAL(10,2) NOT NULL,
    
    -- Basic status
    status VARCHAR(50) DEFAULT 'confirmed',
    payment_status VARCHAR(50) DEFAULT 'pending',
    payment_reference VARCHAR(255),
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- Add new columns if they don't exist
DO $$ 
BEGIN
    -- Kosten & Prijzen
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='price_per_night') THEN
        ALTER TABLE bookings ADD COLUMN price_per_night DECIMAL(10,2);
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='discount') THEN
        ALTER TABLE bookings ADD COLUMN discount DECIMAL(10,2) DEFAULT 0;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='deposit') THEN
        ALTER TABLE bookings ADD COLUMN deposit DECIMAL(10,2) DEFAULT 0;
    END IF;
    
    -- Opties & Toeslagen
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='breakfast') THEN
        ALTER TABLE bookings ADD COLUMN breakfast BOOLEAN DEFAULT false;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='linens') THEN
        ALTER TABLE bookings ADD COLUMN linens BOOLEAN DEFAULT false;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='towels') THEN
        ALTER TABLE bookings ADD COLUMN towels BOOLEAN DEFAULT false;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='parking') THEN
        ALTER TABLE bookings ADD COLUMN parking BOOLEAN DEFAULT false;
    END IF;
    
    -- Betalingsvoorwaarden
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='deposit_percentage') THEN
        ALTER TABLE bookings ADD COLUMN deposit_percentage INTEGER DEFAULT 40;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='deposit_amount') THEN
        ALTER TABLE bookings ADD COLUMN deposit_amount DECIMAL(10,2);
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='remaining_amount') THEN
        ALTER TABLE bookings ADD COLUMN remaining_amount DECIMAL(10,2);
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='deposit_due_date') THEN
        ALTER TABLE bookings ADD COLUMN deposit_due_date DATE;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='final_payment_due_date') THEN
        ALTER TABLE bookings ADD COLUMN final_payment_due_date DATE;
    END IF;
    
    -- Opmerkingen
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='public_notes') THEN
        ALTER TABLE bookings ADD COLUMN public_notes TEXT;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='bookings' AND column_name='internal_notes') THEN
        ALTER TABLE bookings ADD COLUMN internal_notes TEXT;
    END IF;
END $$;

-- Add extra_info column to accommodations if it doesn't exist
DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='accommodations' AND column_name='extra_info') THEN
        ALTER TABLE accommodations ADD COLUMN extra_info TEXT;
    END IF;
END $$;

-- Enable RLS for bookings
ALTER TABLE bookings ENABLE ROW LEVEL SECURITY;

-- Policy: Bookings are viewable by property API key
DROP POLICY IF EXISTS "Bookings access by property" ON bookings;
CREATE POLICY "Bookings access by property" ON bookings
    FOR ALL USING (
        property_id IN (
            SELECT id FROM properties 
            WHERE api_key = current_setting('request.jwt.claims', true)::json->>'api_key'
        )
        OR auth.role() = 'service_role'
        OR auth.role() = 'authenticated'
    );

-- Availability table
CREATE TABLE IF NOT EXISTS availability (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    accommodation_id UUID REFERENCES accommodations(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    available BOOLEAN DEFAULT true,
    price_override DECIMAL(10,2),
    minimum_stay INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW(),
    UNIQUE(accommodation_id, date)
);

-- Enable RLS for availability
ALTER TABLE availability ENABLE ROW LEVEL SECURITY;

-- Policy: Availability is viewable by property API key
DROP POLICY IF EXISTS "Availability access by property" ON availability;
CREATE POLICY "Availability access by property" ON availability
    FOR ALL USING (
        accommodation_id IN (
            SELECT a.id FROM accommodations a
            JOIN properties p ON a.property_id = p.id
            WHERE p.api_key = current_setting('request.jwt.claims', true)::json->>'api_key'
        )
        OR auth.role() = 'service_role'
        OR auth.role() = 'authenticated'
    );

-- Indexes for performance
CREATE INDEX IF NOT EXISTS idx_accommodations_property ON accommodations(property_id);
CREATE INDEX IF NOT EXISTS idx_accommodations_active ON accommodations(active);
CREATE INDEX IF NOT EXISTS idx_bookings_property_dates ON bookings(property_id, check_in, check_out);
CREATE INDEX IF NOT EXISTS idx_bookings_accommodation ON bookings(accommodation_id);
CREATE INDEX IF NOT EXISTS idx_availability_accommodation_date ON availability(accommodation_id, date);
CREATE INDEX IF NOT EXISTS idx_properties_api_key ON properties(api_key);
CREATE INDEX IF NOT EXISTS idx_properties_domain ON properties(domain);

-- Insert default property for development
INSERT INTO properties (id, name, domain, api_key, settings) 
VALUES (
    '00000000-0000-0000-0000-000000000001',
    'Development Property',
    'localhost',
    'dev_api_key_12345',
    '{
        "bedrijfsinfo": {
            "bedrijf_naam": "Zee-zicht B&B",
            "contact_email": "info@zee-zicht.nl",
            "contact_telefoon": "+31 6 12345678",
            "website": "https://zee-zicht.nl",
            "kvk_nummer": "12345678",
            "btw_nummer": "NL123456789B01",
            "bedrijf_adres": "Strandweg 123, 1234 AB Zee-zicht"
        },
        "toeslagen": {
            "ontbijt_prijs": 12.50,
            "linnen_prijs": 15.00,
            "handdoeken_prijs": 8.50,
            "parkeren_prijs": 5.00,
            "standaard_borg": 50.00,
            "aanbetaling_percentage": 40
        }
    }'::jsonb
) ON CONFLICT (id) DO NOTHING;

-- Clean up any duplicate accommodations first
DELETE FROM accommodations 
WHERE property_id = '00000000-0000-0000-0000-000000000001'
AND name IN ('Basis kampeerplaats', 'Comfort B&B kamer');

-- Insert sample accommodations for development
INSERT INTO accommodations (property_id, name, type, max_guests, surface_area, description, amenities, base_price) 
VALUES 
(
    '00000000-0000-0000-0000-000000000001',
    'Basis kampeerplaats',
    'kampeerplaats',
    6,
    '150',
    'Ruime kampeerplaats van 150 m² met 10 ampère stroom, water- en rioolaansluiting. Perfect voor campers en caravans.',
    '["10A Stroom", "Water aansluiting", "Gratis WiFi", "Kabeltelevisie"]'::jsonb,
    25.00
),
(
    '00000000-0000-0000-0000-000000000001',
    'Comfort B&B kamer',
    'bnb-kamer',
    2,
    '25',
    'Gezellige B&B kamer met eigen badkamer en prachtig uitzicht op de tuin.',
    '["Gratis WiFi", "Balkon/Terras", "Ontbijt"]'::jsonb,
    85.00
);

-- Insert sample bookings for development
INSERT INTO bookings (
    property_id, accommodation_id, check_in, check_out, guests, 
    guest_name, guest_email, guest_phone, 
    price_per_night, total_price, discount, deposit,
    status, payment_status, 
    breakfast, linens, towels, parking,
    deposit_percentage, deposit_amount, remaining_amount,
    public_notes, internal_notes
) 
VALUES 
(
    '00000000-0000-0000-0000-000000000001',
    (SELECT id FROM accommodations WHERE name = 'Basis kampeerplaats' LIMIT 1),
    '2024-07-15',
    '2024-07-18',
    4,
    'Jan de Vries',
    'jan@example.com',
    '+31 6 12345678',
    25.00,
    75.00,
    0.00,
    50.00,
    'confirmed',
    'paid',
    true,
    false,
    true,
    false,
    40,
    30.00,
    45.00,
    'Vroege check-in gewenst',
    'Vaste klant, altijd netjes'
),
(
    '00000000-0000-0000-0000-000000000001',
    (SELECT id FROM accommodations WHERE name = 'Comfort B&B kamer' LIMIT 1),
    '2024-07-20',
    '2024-07-22',
    2,
    'Maria Janssen',
    'maria@example.com',
    '+31 6 87654321',
    85.00,
    170.00,
    10.00,
    50.00,
    'confirmed',
    'paid',
    true,
    true,
    true,
    true,
    40,
    68.00,
    102.00,
    'Glutenvrij ontbijt gewenst',
    'Heeft allergie voor noten'
),
(
    '00000000-0000-0000-0000-000000000001',
    (SELECT id FROM accommodations WHERE name = 'Basis kampeerplaats' LIMIT 1),
    '2024-08-01',
    '2024-08-05',
    6,
    'Peter van der Berg',
    'peter@example.com',
    '+31 6 11223344',
    25.00,
    100.00,
    0.00,
    50.00,
    'confirmed',
    'pending',
    false,
    false,
    false,
    true,
    40,
    40.00,
    60.00,
    'Grote familie, extra handdoeken meenemen',
    'Aanbetaling nog niet ontvangen'
) ON CONFLICT DO NOTHING;

-- Function to update updated_at timestamp
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER 
SECURITY DEFINER
SET search_path = public
LANGUAGE plpgsql
AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$;

-- Triggers to automatically update updated_at
DROP TRIGGER IF EXISTS update_properties_updated_at ON properties;
CREATE TRIGGER update_properties_updated_at BEFORE UPDATE ON properties FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_accommodations_updated_at ON accommodations;
CREATE TRIGGER update_accommodations_updated_at BEFORE UPDATE ON accommodations FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_bookings_updated_at ON bookings;
CREATE TRIGGER update_bookings_updated_at BEFORE UPDATE ON bookings FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

DROP TRIGGER IF EXISTS update_availability_updated_at ON availability;
CREATE TRIGGER update_availability_updated_at BEFORE UPDATE ON availability FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
