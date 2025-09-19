# Zee-zicht PMS Architecture & Implementation Guide

## 🏗️ System Overview

**Multi-tenant Property Management System (PMS) with API-driven client websites**

```
┌─────────────────────────┐    HTTPS/API    ┌─────────────────────────┐
│   admin.zee-zicht.nl    │◄──────────────► │  klant-website.nl       │
│   (PMS Dashboard)       │                 │  (WordPress + Plugin)   │
│                         │                 │                         │
│ • Accommodaties beheer  │                 │ • Listing pagina        │
│ • Prijzen & seizoenen   │                 │ • Boekingsformulier     │
│ • Foto's & beschrijving │                 │ • Real-time beschikbaar │
│ • Multi-tenant beheer   │                 │ • Lightweight plugin    │
└─────────────────────────┘                 └─────────────────────────┘
                │                                        │
                │          Secure API                    │
                ▼                                        ▼
┌─────────────────────────┐                 ┌─────────────────────────┐
│   PostgreSQL Database   │                 │  Caching Layer          │
│   (Encrypted at rest)   │                 │  (WordPress Transients) │
│                         │                 │                         │
│ • Multi-tenant data     │                 │ • 15min cache           │
│ • Encrypted payments    │                 │ • Background sync       │
│ • Audit logs           │                 │ • Minimal DB queries    │
└─────────────────────────┘                 └─────────────────────────┘
```

## 🔒 Security Architecture

### API Authentication
```javascript
// JWT + API Key hybrid approach
Headers: {
  'Authorization': 'Bearer jwt_token_here',
  'X-API-Key': 'property_specific_api_key',
  'X-Property-ID': 'zee-zicht-bv'
}

// Rate limiting per property
- 1000 requests/hour per property
- 100 booking requests/hour per property
- IP-based throttling for abuse prevention
```

### Payment Security
```javascript
// PCI DSS Compliance approach
- NO card data stored in PMS
- Stripe/Mollie webhook integration
- Payment tokens only
- Encrypted payment references
- Audit trail for all transactions
```

### Data Encryption
```sql
-- Database level encryption
- Passwords: bcrypt with salt
- API keys: AES-256 encryption
- PII data: Field-level encryption
- Backups: Encrypted at rest
- SSL/TLS: Minimum TLS 1.3
```

## ⚡ Performance Architecture

### WordPress Plugin Optimization
```php
// Lightweight plugin strategy
class ZeeZichtBookingPlugin {
    // 1. Aggressive caching
    private $cache_duration = 900; // 15 minutes
    
    // 2. Lazy loading
    public function load_accommodations_on_demand() {
        // Only load when shortcode is used
        // Background AJAX for updates
    }
    
    // 3. Minimal footprint
    // - Single CSS file (minified)
    // - Single JS file (minified) 
    // - No jQuery dependency
    // - Vanilla JS only
}
```

### API Performance
```javascript
// Response optimization
- Gzip compression enabled
- CDN for images (Cloudflare)
- Database connection pooling
- Query optimization with indexes
- Response caching (Redis)
- Pagination for large datasets

// Typical response times target:
- Accommodation list: <200ms
- Availability check: <100ms  
- Booking creation: <500ms
```

### Caching Strategy
```javascript
// Multi-layer caching
1. Browser cache: Static assets (24h)
2. CDN cache: Images & CSS (7 days)
3. WordPress transients: API responses (15min)
4. Redis cache: Database queries (5min)
5. Database query cache: Complex queries
```

## 📊 Database Schema (Supabase PostgreSQL)

### **Supabase Setup:**
```javascript
// Project URL: https://your-project.supabase.co
// Anon Key: your-anon-key
// Service Role Key: your-service-role-key (server-side only)

// Environment variables:
SUPABASE_URL=https://your-project.supabase.co
SUPABASE_ANON_KEY=your-anon-key
SUPABASE_SERVICE_KEY=your-service-role-key
```

### **Multi-tenant Schema with Row Level Security:**
```sql
-- Properties table (tenants)
CREATE TABLE properties (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
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

-- Policy: Properties are viewable by API key
CREATE POLICY "Properties access by API key" ON properties
    FOR ALL USING (
        api_key = current_setting('request.jwt.claims', true)::json->>'api_key'
        OR auth.role() = 'service_role'
    );

CREATE TABLE accommodations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
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
CREATE POLICY "Accommodations access by property" ON accommodations
    FOR ALL USING (
        property_id IN (
            SELECT id FROM properties 
            WHERE api_key = current_setting('request.jwt.claims', true)::json->>'api_key'
        )
        OR auth.role() = 'service_role'
    );

CREATE TABLE bookings (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    property_id UUID REFERENCES properties(id),
    accommodation_id UUID REFERENCES accommodations(id),
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    guests INTEGER NOT NULL,
    guest_name VARCHAR(255) NOT NULL,
    guest_email VARCHAR(255) NOT NULL,
    guest_phone VARCHAR(50),
    total_price DECIMAL(10,2) NOT NULL,
    payment_status VARCHAR(50) DEFAULT 'pending',
    payment_reference VARCHAR(255),
    status VARCHAR(50) DEFAULT 'confirmed',
    created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE availability (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    accommodation_id UUID REFERENCES accommodations(id) ON DELETE CASCADE,
    date DATE NOT NULL,
    available BOOLEAN DEFAULT true,
    price_override DECIMAL(10,2),
    minimum_stay INTEGER DEFAULT 1,
    UNIQUE(accommodation_id, date)
);

-- Indexes for performance
CREATE INDEX idx_accommodations_property ON accommodations(property_id);
CREATE INDEX idx_bookings_property_dates ON bookings(property_id, check_in, check_out);
CREATE INDEX idx_availability_accommodation_date ON availability(accommodation_id, date);
```

## 🚀 API Endpoints

### Authentication
```javascript
POST /api/v1/auth/login
POST /api/v1/auth/refresh
POST /api/v1/auth/logout
```

### Properties Management
```javascript
GET    /api/v1/properties
POST   /api/v1/properties
PUT    /api/v1/properties/{id}
DELETE /api/v1/properties/{id}
```

### Accommodations
```javascript
GET    /api/v1/properties/{property_id}/accommodations
POST   /api/v1/properties/{property_id}/accommodations
PUT    /api/v1/properties/{property_id}/accommodations/{id}
DELETE /api/v1/properties/{property_id}/accommodations/{id}
POST   /api/v1/properties/{property_id}/accommodations/{id}/photos
```

### Bookings & Availability
```javascript
GET    /api/v1/properties/{property_id}/availability?from=2024-01-01&to=2024-12-31
GET    /api/v1/properties/{property_id}/bookings
POST   /api/v1/properties/{property_id}/bookings
PUT    /api/v1/properties/{property_id}/bookings/{id}
GET    /api/v1/properties/{property_id}/pricing
```

## 🔧 Implementation Phases

### Phase 1: Core PMS (Current)
- [x] Basic accommodations CRUD
- [x] Settings management  
- [x] File-based storage (temporary)
- [ ] **UPGRADE TO POSTGRESQL** ⚠️
- [ ] Multi-tenant support
- [ ] API authentication
- [ ] Photo upload system

### Phase 2: Security & Performance
- [ ] JWT authentication
- [ ] API rate limiting
- [ ] Database encryption
- [ ] Audit logging
- [ ] Performance monitoring

### Phase 3: WordPress Plugin
- [ ] Lightweight WordPress plugin
- [ ] Caching implementation
- [ ] Shortcode system
- [ ] Booking form integration
- [ ] Payment integration

### Phase 4: Advanced Features
- [ ] Real-time availability
- [ ] Seasonal pricing
- [ ] Email notifications
- [ ] Reporting dashboard
- [ ] Mobile app API

## ⚠️ Critical Checks & Validations

### Security Checklist
```javascript
// Before ANY production deployment:
□ All API endpoints require authentication
□ Input validation on all endpoints
□ SQL injection prevention (parameterized queries)
□ XSS prevention (output escaping)
□ CSRF protection
□ Rate limiting implemented
□ HTTPS enforced (no HTTP)
□ API keys rotated regularly
□ Payment data NEVER stored directly
□ Audit logs for all sensitive operations
```

### Performance Checklist
```javascript
// Performance validation:
□ Database queries optimized with EXPLAIN
□ Indexes on all foreign keys
□ API responses < 200ms average
□ WordPress plugin < 50KB total
□ Images optimized and CDN served
□ Caching implemented at all layers
□ Database connection pooling
□ Memory usage monitored
```

### Data Integrity Checks
```javascript
// Data validation:
□ All dates validated (check-in < check-out)
□ Guest count within accommodation limits
□ Price calculations verified
□ Booking conflicts prevented
□ Availability sync verified
□ Backup & restore tested
```

## 🚨 Anti-Hallucination Checks

### When Implementing Database Changes:
1. **ALWAYS** backup existing data first
2. **VERIFY** migration scripts on staging
3. **TEST** all API endpoints after changes
4. **CONFIRM** WordPress plugin still works
5. **VALIDATE** no data loss occurred

### When Adding New Features:
1. **SECURITY FIRST** - authenticate before functionality
2. **PERFORMANCE IMPACT** - measure before/after
3. **BACKWARD COMPATIBILITY** - don't break existing clients
4. **ERROR HANDLING** - graceful failures with user feedback
5. **LOGGING** - track all important operations

### Code Quality Gates:
```javascript
// Before any commit:
□ TypeScript/PHP errors resolved
□ Security scan passed
□ Performance tests passed
□ Unit tests written and passing
□ API documentation updated
□ Database migrations tested
```

## 📈 Monitoring & Alerting

### Key Metrics to Track:
- API response times
- Database query performance  
- WordPress plugin load times
- Booking conversion rates
- Error rates by endpoint
- Security incidents
- Cache hit rates

### Alerts Setup:
- API response time > 500ms
- Database connections > 80%
- Failed authentication attempts > 10/min
- Payment processing errors
- WordPress plugin errors

## 🎯 Current Status & Next Steps

**Current State:** Development with JSON file storage
**Immediate Priority:** Database migration to PostgreSQL
**Security Status:** Basic (needs JWT + API keys)
**Performance Status:** Not optimized (needs caching)

**Next Action Items:**
1. Migrate from JSON to PostgreSQL
2. Implement multi-tenant support
3. Add JWT authentication
4. Build WordPress plugin foundation
5. Implement caching layer

---

**Remember:** This is a financial system handling bookings and payments. Security and reliability are MORE important than feature velocity. Always validate, test, and secure before deploying.
