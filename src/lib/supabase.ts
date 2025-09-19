import { createClient } from '@supabase/supabase-js';

// Supabase configuration
const supabaseUrl = import.meta.env.SUPABASE_URL || 'https://your-project.supabase.co';
const supabaseAnonKey = import.meta.env.SUPABASE_ANON_KEY || 'your-anon-key';
const supabaseServiceKey = import.meta.env.SUPABASE_SERVICE_KEY || 'your-service-role-key';

// Client for browser/frontend use (with RLS)
export const supabase = createClient(supabaseUrl, supabaseAnonKey);

// Admin client for server-side use (bypasses RLS)
export const supabaseAdmin = createClient(supabaseUrl, supabaseServiceKey);

// Database types
export interface Property {
  id: string;
  name: string;
  domain: string;
  api_key: string;
  settings: Record<string, any>;
  active: boolean;
  created_at: string;
  updated_at: string;
}

export interface Accommodation {
  id: string;
  property_id: string;
  name: string;
  type: string;
  max_guests: number;
  surface_area?: string;
  description?: string;
  amenities: string[];
  photos: string[];
  base_price?: number | null;
  extra_info?: string;
  active: boolean;
  created_at: string;
  updated_at: string;
}

export interface Booking {
  id: string;
  property_id: string;
  accommodation_id: string;
  check_in: string;
  check_out: string;
  guests: number;
  guest_name: string;
  guest_email: string;
  guest_phone?: string;
  total_price: number;
  payment_status: string;
  payment_reference?: string;
  status: string;
  created_at: string;
}

export interface Availability {
  id: string;
  accommodation_id: string;
  date: string;
  available: boolean;
  price_override?: number;
  minimum_stay: number;
}

export interface AccommodationImage {
  id: string;
  accommodation_id: string;
  url: string;
  filename: string;
  original_name: string;
  file_size: number;
  mime_type: string;
  sort_order: number;
  is_primary: boolean;
  alt_text?: string;
  caption?: string;
  created_at: string;
  updated_at: string;
}

// Helper functions
export const getPropertyByApiKey = async (apiKey: string): Promise<Property | null> => {
  const { data, error } = await supabaseAdmin
    .from('properties')
    .select('*')
    .eq('api_key', apiKey)
    .eq('active', true)
    .single();

  if (error) {
    console.error('Error fetching property:', error);
    return null;
  }

  return data;
};

export const getAccommodationsByProperty = async (propertyId: string): Promise<Accommodation[]> => {
  const { data, error } = await supabaseAdmin
    .from('accommodations')
    .select('*')
    .eq('property_id', propertyId)
    .eq('active', true)
    .order('created_at', { ascending: true });

  if (error) {
    console.error('Error fetching accommodations:', error);
    return [];
  }

  return data || [];
};

export const createAccommodation = async (accommodation: Omit<Accommodation, 'id' | 'created_at' | 'updated_at'>): Promise<Accommodation | null> => {
  const { data, error } = await supabaseAdmin
    .from('accommodations')
    .insert([accommodation])
    .select()
    .single();

  if (error) {
    console.error('Error creating accommodation:', error);
    return null;
  }

  return data;
};

export const updateAccommodation = async (id: string, updates: Partial<Accommodation>): Promise<Accommodation | null> => {
  const { data, error } = await supabaseAdmin
    .from('accommodations')
    .update({ ...updates, updated_at: new Date().toISOString() })
    .eq('id', id)
    .select()
    .single();

  if (error) {
    console.error('Error updating accommodation:', error);
    return null;
  }

  return data;
};

export const deleteAccommodation = async (id: string): Promise<boolean> => {
  const { error } = await supabaseAdmin
    .from('accommodations')
    .delete()
    .eq('id', id);

  if (error) {
    console.error('Error deleting accommodation:', error);
    return false;
  }

  return true;
};

// Settings helpers
export const getSettings = async (propertyId: string, section?: string): Promise<Record<string, any>> => {
  const { data, error } = await supabaseAdmin
    .from('properties')
    .select('settings')
    .eq('id', propertyId)
    .single();

  if (error) {
    console.error('Error fetching settings:', error);
    return {};
  }

  const settings = data?.settings || {};
  
  // Ensure default structure exists
  const defaultSettings = {
    bedrijfsinfo: {
      bedrijf_naam: '',
      business_type: 'minicamping',
      contact_email: '',
      bedrijf_telefoon: '',
      bedrijf_adres: '',
      website_url: '',
      bedrijf_beschrijving: ''
    },
    website: {
      booking_page_title: 'Reserveren',
      accommodations_page_title: 'Onze Accommodaties',
      min_stay_nights: 2,
      max_advance_days: 365,
      checkin_time: '15:00',
      checkout_time: '11:00',
      booking_notification_email: '',
      auto_confirmation: true,
      accommodation_layout: 'grid',
      show_prices: true,
      show_availability: true
    }
  };

  // Merge with defaults
  const mergedSettings = {
    ...defaultSettings,
    ...settings,
    bedrijfsinfo: { ...defaultSettings.bedrijfsinfo, ...(settings.bedrijfsinfo || {}) },
    website: { ...defaultSettings.website, ...(settings.website || {}) }
  };

  return section ? (mergedSettings[section] || {}) : mergedSettings;
};

export const updateSettings = async (propertyId: string, section: string, sectionData: Record<string, any>): Promise<boolean> => {
  // First get current settings
  const currentSettings = await getSettings(propertyId);
  
  // Update the specific section
  const updatedSettings = {
    ...currentSettings,
    [section]: sectionData
  };

  const { error } = await supabaseAdmin
    .from('properties')
    .update({ 
      settings: updatedSettings,
      updated_at: new Date().toISOString()
    })
    .eq('id', propertyId);

  if (error) {
    console.error('Error updating settings:', error);
    return false;
  }

  return true;
};

// Image management functions
export const getAccommodationImages = async (accommodationId: string): Promise<AccommodationImage[]> => {
  const { data, error } = await supabaseAdmin
    .from('accommodation_images')
    .select('*')
    .eq('accommodation_id', accommodationId)
    .order('sort_order', { ascending: true });

  if (error) {
    console.error('Error fetching accommodation images:', error);
    return [];
  }

  return data || [];
};

export const createAccommodationImage = async (image: Omit<AccommodationImage, 'id' | 'created_at' | 'updated_at'>): Promise<AccommodationImage | null> => {
  const { data, error } = await supabaseAdmin
    .from('accommodation_images')
    .insert([image])
    .select()
    .single();

  if (error) {
    console.error('Error creating accommodation image:', error);
    return null;
  }

  return data;
};

export const deleteAccommodationImage = async (id: string): Promise<boolean> => {
  const { error } = await supabaseAdmin
    .from('accommodation_images')
    .delete()
    .eq('id', id);

  if (error) {
    console.error('Error deleting accommodation image:', error);
    return false;
  }

  return true;
};
