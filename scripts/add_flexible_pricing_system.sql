-- ============================================================================
-- FLEXIBLE PRICING SYSTEM - Customizable per Accommodation
-- ============================================================================

-- Step 1: Add pricing fields to bookings table
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS adults INTEGER DEFAULT 1;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS children_12_plus INTEGER DEFAULT 0;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS children_under_12 INTEGER DEFAULT 0;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS children_0_3 INTEGER DEFAULT 0;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS camping_vehicle_type VARCHAR(50); -- caravan, tent, camperbus, camper
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS base_price DECIMAL(10,2) DEFAULT 0;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS options_total DECIMAL(10,2) DEFAULT 0;
ALTER TABLE bookings ADD COLUMN IF NOT EXISTS one_time_options_total DECIMAL(10,2) DEFAULT 0;

-- Step 2: Create accommodation pricing options table (customizable per property)
CREATE TABLE IF NOT EXISTS accommodation_pricing_options (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    accommodation_id UUID NOT NULL REFERENCES accommodations(id) ON DELETE CASCADE,
    category VARCHAR(50) NOT NULL, -- 'recurring' or 'one_time'
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price_per_night DECIMAL(10,2),
    price_per_stay DECIMAL(10,2), -- For one-time options
    unit VARCHAR(50), -- 'per_night', 'per_stay', 'per_person', 'per_day', 'per_week'
    is_active BOOLEAN DEFAULT true,
    sort_order INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Step 3: Create booking options (selected options for each booking)
CREATE TABLE IF NOT EXISTS booking_options (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    booking_id UUID NOT NULL REFERENCES bookings(id) ON DELETE CASCADE,
    option_id UUID NOT NULL REFERENCES accommodation_pricing_options(id),
    quantity INTEGER DEFAULT 1,
    price_per_unit DECIMAL(10,2) NOT NULL, -- Store price at time of booking
    total_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

-- Step 4: Add indexes
CREATE INDEX IF NOT EXISTS idx_accommodation_pricing_options_accommodation_id ON accommodation_pricing_options(accommodation_id);
CREATE INDEX IF NOT EXISTS idx_accommodation_pricing_options_category ON accommodation_pricing_options(category);
CREATE INDEX IF NOT EXISTS idx_booking_options_booking_id ON booking_options(booking_id);

-- Step 5: Insert default pricing options for existing accommodations
INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_night, unit, sort_order)
SELECT 
    id as accommodation_id,
    'recurring' as category,
    'Toeristenbelasting kampeerplaats' as name,
    'Verplichte toeristenbelasting per persoon per nacht' as description,
    1.40 as price_per_night,
    'per_person_per_night' as unit,
    1 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_night, unit, sort_order)
SELECT 
    id as accommodation_id,
    'recurring' as category,
    'Elektrische aansluiting, 10 ampère' as name,
    'Elektriciteit voor caravan/camper' as description,
    4.00 as price_per_night,
    'per_night' as unit,
    2 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_night, unit, sort_order)
SELECT 
    id as accommodation_id,
    'recurring' as category,
    'Extra (tweede) auto' as name,
    'Parkeerplaats voor tweede voertuig' as description,
    1.50 as price_per_night,
    'per_night' as unit,
    3 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_night, unit, sort_order)
SELECT 
    id as accommodation_id,
    'recurring' as category,
    'Extra (tweede) tentje' as name,
    'Tweede tent op dezelfde plaats' as description,
    5.00 as price_per_night,
    'per_night' as unit,
    4 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_night, unit, sort_order)
SELECT 
    id as accommodation_id,
    'recurring' as category,
    'Hond' as name,
    'Huisdier per nacht' as description,
    2.00 as price_per_night,
    'per_night' as unit,
    5 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_night, unit, sort_order)
SELECT 
    id as accommodation_id,
    'recurring' as category,
    'Huur Humax kastje voor digitale televisie' as name,
    'TV decoder huur per nacht' as description,
    1.00 as price_per_night,
    'per_night' as unit,
    6 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_night, unit, sort_order)
SELECT 
    id as accommodation_id,
    'recurring' as category,
    'Kabeltelevisie aansluiting' as name,
    'Kabel TV per nacht' as description,
    1.00 as price_per_night,
    'per_night' as unit,
    7 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

-- One-time options
INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_stay, unit, sort_order)
SELECT 
    id as accommodation_id,
    'one_time' as category,
    'Dagbezoek' as name,
    'Bezoek van familie/vrienden per persoon' as description,
    0.50 as price_per_stay,
    'per_person' as unit,
    1 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_stay, unit, sort_order)
SELECT 
    id as accommodation_id,
    'one_time' as category,
    'Elektrische fiets per dag' as name,
    'E-bike verhuur per dag' as description,
    18.50 as price_per_stay,
    'per_day' as unit,
    2 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_stay, unit, sort_order)
SELECT 
    id as accommodation_id,
    'one_time' as category,
    'Elektrische fiets per week' as name,
    'E-bike verhuur per week' as description,
    87.50 as price_per_stay,
    'per_week' as unit,
    3 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_stay, unit, sort_order)
SELECT 
    id as accommodation_id,
    'one_time' as category,
    'Fiets per dag' as name,
    'Normale fiets verhuur per dag' as description,
    8.50 as price_per_stay,
    'per_day' as unit,
    4 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_stay, unit, sort_order)
SELECT 
    id as accommodation_id,
    'one_time' as category,
    'Fiets per week' as name,
    'Normale fiets verhuur per week' as description,
    40.00 as price_per_stay,
    'per_week' as unit,
    5 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_stay, unit, sort_order)
SELECT 
    id as accommodation_id,
    'one_time' as category,
    'Wasmachine munt' as name,
    'Wasmachine gebruik per keer' as description,
    5.50 as price_per_stay,
    'per_use' as unit,
    6 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

INSERT INTO accommodation_pricing_options (accommodation_id, category, name, description, price_per_stay, unit, sort_order)
SELECT 
    id as accommodation_id,
    'one_time' as category,
    'Droger munt' as name,
    'Droger gebruik per keer' as description,
    4.50 as price_per_stay,
    'per_use' as unit,
    7 as sort_order
FROM accommodations
ON CONFLICT DO NOTHING;

-- Step 6: Create view for easy pricing calculations
CREATE OR REPLACE VIEW booking_pricing_summary AS
SELECT 
    b.id as booking_id,
    b.accommodation_id,
    b.check_in,
    b.check_out,
    b.adults,
    b.children_12_plus,
    b.children_under_12,
    b.children_0_3,
    b.camping_vehicle_type,
    b.base_price,
    COALESCE(SUM(CASE WHEN apo.category = 'recurring' THEN bo.total_price END), 0) as recurring_options_total,
    COALESCE(SUM(CASE WHEN apo.category = 'one_time' THEN bo.total_price END), 0) as one_time_options_total,
    b.base_price + COALESCE(SUM(bo.total_price), 0) as total_price
FROM bookings b
LEFT JOIN booking_options bo ON b.id = bo.booking_id
LEFT JOIN accommodation_pricing_options apo ON bo.option_id = apo.id
GROUP BY b.id, b.accommodation_id, b.check_in, b.check_out, b.adults, b.children_12_plus, 
         b.children_under_12, b.children_0_3, b.camping_vehicle_type, b.base_price;

-- Step 7: Test the setup
SELECT 'Pricing system setup complete!' as status;
SELECT COUNT(*) as total_options FROM accommodation_pricing_options;
SELECT accommodation_id, COUNT(*) as options_count 
FROM accommodation_pricing_options 
GROUP BY accommodation_id;
