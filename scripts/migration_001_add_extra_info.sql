-- Migration: Add extra_info field to accommodations
-- Date: 2025-09-19
-- Description: Adds extra_info TEXT field to store additional accommodation information

-- Add extra_info column to accommodations if it doesn't exist
DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='accommodations' AND column_name='extra_info') THEN
        ALTER TABLE accommodations ADD COLUMN extra_info TEXT;
        RAISE NOTICE 'Added extra_info column to accommodations table';
    ELSE
        RAISE NOTICE 'extra_info column already exists in accommodations table';
    END IF;
END $$;
