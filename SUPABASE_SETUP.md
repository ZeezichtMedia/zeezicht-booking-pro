# Supabase Setup Guide - Zee-zicht PMS

## 🚀 Quick Setup (5 minuten)

### 1. Create Supabase Project
1. Ga naar [supabase.com](https://supabase.com)
2. Klik "Start your project"
3. Sign up/login met GitHub
4. Klik "New Project"
5. Kies organisatie en vul in:
   - **Name:** `zee-zicht-pms`
   - **Database Password:** (genereer sterke password)
   - **Region:** `West EU (Paris)` (voor GDPR compliance + lage latency)
6. Klik "Create new project"
7. Wacht 2-3 minuten tot project klaar is

### 2. Get API Keys
1. In je Supabase dashboard, ga naar **Settings** → **API**
2. Kopieer de volgende keys:
   - **Project URL:** `https://your-project.supabase.co`
   - **anon public key:** `eyJ...` (voor frontend)
   - **service_role key:** `eyJ...` (voor backend, GEHEIM!)

### 3. Setup Environment Variables
1. Kopieer `.env.example` naar `.env`:
```bash
cp .env.example .env
```

2. Vul je Supabase credentials in `.env`:
```bash
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
SUPABASE_SERVICE_KEY=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

### 4. Create Database Schema
1. In Supabase dashboard, ga naar **SQL Editor**
2. Klik "New query"
3. Kopieer de inhoud van `scripts/setup-supabase.sql`
4. Plak in de SQL editor
5. Klik "Run" (▶️)
6. Controleer dat er geen errors zijn

### 5. Verify Setup
1. Ga naar **Table Editor** in Supabase
2. Je zou moeten zien:
   - ✅ `properties` table (1 row: Development Property)
   - ✅ `accommodations` table (2 rows: sample data)
   - ✅ `bookings` table (empty)
   - ✅ `availability` table (empty)

### 6. Test API Connection
1. Start Astro development server:
```bash
npm run dev
```

2. Ga naar `http://localhost:2122/beheer/settings`
3. Test of settings laden/opslaan werkt
4. Ga naar accommodatie tab en test CRUD operaties

## 🔒 Security Setup

### Row Level Security (RLS)
Supabase RLS is automatisch ingeschakeld voor multi-tenant security:

```sql
-- Elke property heeft eigen data
-- API key bepaalt toegang tot data
-- Geen cross-tenant data leakage mogelijk
```

### API Key Management
```javascript
// Frontend (public, safe to expose)
SUPABASE_ANON_KEY=eyJ... 

// Backend (secret, never expose!)
SUPABASE_SERVICE_KEY=eyJ...
```

## 📊 Database Structure

### Properties (Tenants)
```sql
properties
├── id (UUID, primary key)
├── name (B&B naam)
├── domain (website domain)
├── api_key (voor WordPress plugin)
├── settings (JSONB, alle instellingen)
└── active (boolean)
```

### Accommodations
```sql
accommodations
├── id (UUID, primary key)
├── property_id (foreign key)
├── name (accommodatie naam)
├── type (kampeerplaats, bnb-kamer, etc.)
├── max_guests (aantal gasten)
├── amenities (JSONB array)
└── base_price (basis prijs)
```

## 🎯 Multi-tenant Architecture

### How It Works:
```javascript
// Elke API call filtert automatisch op property_id
// RLS policies zorgen voor data isolatie
// WordPress plugins krijgen eigen API key
// Geen data leakage tussen klanten mogelijk
```

### API Authentication:
```javascript
// PMS Dashboard (JWT)
headers: {
  'Authorization': 'Bearer jwt_token'
}

// WordPress Plugin (API Key)
headers: {
  'X-API-Key': 'property_specific_key'
}
```

## 🔧 Development vs Production

### Development (Current)
- **Property ID:** `default-property`
- **API Key:** `dev_api_key_12345`
- **Sample data:** Included
- **RLS:** Enabled but bypassed for development

### Production (Later)
- **Multiple properties:** Each client gets own property
- **Unique API keys:** Generated per client
- **Real authentication:** JWT tokens
- **Strict RLS:** Full multi-tenant isolation

## 🚨 Troubleshooting

### Common Issues:

**1. "Failed to load accommodaties"**
```bash
# Check environment variables
echo $SUPABASE_URL
echo $SUPABASE_ANON_KEY

# Verify in .env file
cat .env
```

**2. "Database connection failed"**
```sql
-- Check if tables exist in Supabase
SELECT table_name FROM information_schema.tables 
WHERE table_schema = 'public';
```

**3. "RLS policy violation"**
```sql
-- Temporarily disable RLS for debugging
ALTER TABLE accommodations DISABLE ROW LEVEL SECURITY;
-- Remember to re-enable after testing!
```

**4. "API key not working"**
```javascript
// Verify API key in Supabase dashboard
// Settings → API → Check keys match .env
```

## 📈 Next Steps

### Phase 1: Basic Multi-tenant (This Week)
- [x] Database schema created
- [x] RLS policies configured  
- [x] API endpoints updated
- [ ] Authentication system
- [ ] Property management UI

### Phase 2: WordPress Plugin (Next Week)
- [ ] Lightweight WordPress plugin
- [ ] API key authentication
- [ ] Accommodation listing shortcode
- [ ] Booking form integration

### Phase 3: Production Features (Month 2)
- [ ] Real-time availability
- [ ] Payment integration
- [ ] Email notifications
- [ ] Advanced reporting

## 💰 Cost Monitoring

### Free Tier Limits:
- **Database:** 500MB (plenty for 5-10 properties)
- **API calls:** 50,000/month (plenty for development)
- **Bandwidth:** 2GB/month
- **Storage:** 1GB

### Upgrade Triggers:
- **10+ properties:** Consider Pro plan (€20/month)
- **Heavy usage:** Monitor API calls
- **Large images:** Consider CDN for photos

---

**🎉 Setup Complete!**

Your Supabase PMS is now ready for development. All API endpoints use real database storage with multi-tenant security built-in.
