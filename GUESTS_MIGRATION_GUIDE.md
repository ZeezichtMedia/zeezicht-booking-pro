# 🏨 Gasten Database Migratie Guide

## 📋 Overzicht

Deze guide helpt je bij het migreren van een **dynamische gasten systeem** (gebaseerd op reserveringen) naar een **separate gasten database** voor betere data integriteit en functionaliteit.

## 🎯 Voordelen van Separate Gasten Tabel

- ✅ **Gasten bestaan altijd** (ook zonder actieve reserveringen)
- ✅ **Gast geschiedenis behouden** bij wijzigingen
- ✅ **Uitgebreide gast profielen** (adres, voorkeuren, notities)
- ✅ **Loyaliteit tracking** (aantal bezoeken, uitgaven)
- ✅ **Marketing mogelijkheden** (GDPR-compliant)
- ✅ **Betere prestaties** (geen duplicate data)

## 🚀 Implementatie Stappen

### Stap 1: Database Migratie Uitvoeren

1. **Open Supabase Dashboard** → SQL Editor
2. **Kopieer en plak** de inhoud van `scripts/migration_002_separate_guests.sql`
3. **Voer het script uit** (dit kan enkele minuten duren)
4. **Controleer de resultaten** met de verificatie queries onderaan het script

### Stap 2: Verificatie

Na de migratie, controleer of alles correct is:

```sql
-- Check aantal gasten
SELECT COUNT(*) as total_guests FROM guests;

-- Check of alle reserveringen een guest_id hebben
SELECT 
    COUNT(*) as total_bookings,
    COUNT(guest_id) as bookings_with_guest_id
FROM bookings;

-- Bekijk gast statistieken
SELECT * FROM guest_statistics LIMIT 5;
```

### Stap 3: API Endpoints Testen

De API endpoints zijn al aangepast met **backward compatibility**:

- **GET /api/gasten** → Haalt gasten op uit nieuwe tabel (met fallback)
- **POST /api/gasten** → Maakt nieuwe gasten aan of vindt bestaande

Test de endpoints:
```bash
# Test gasten ophalen
curl http://localhost:2121/api/gasten

# Test nieuwe gast aanmaken
curl -X POST http://localhost:2121/api/gasten \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Gast","email":"test@example.com","phone":"06-12345678"}'
```

### Stap 4: Frontend Testen

1. **Ga naar reserveringen**: `http://localhost:2121/beheer/reserveringen`
2. **Test gast zoeken**: Klik "Reservering toevoegen" → Zoek bestaande gast
3. **Test nieuwe gast**: Klik "Nieuwe gast toevoegen" → Vul gegevens in
4. **Controleer**: Gasten zouden nu persistent moeten zijn

## 📊 Nieuwe Database Structuur

### Guests Tabel
```sql
guests (
    id UUID PRIMARY KEY,
    property_id UUID NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    address TEXT,
    city VARCHAR(100),
    country VARCHAR(100) DEFAULT 'Nederland',
    postal_code VARCHAR(20),
    date_of_birth DATE,
    nationality VARCHAR(100),
    id_number VARCHAR(100),
    notes TEXT,
    preferences TEXT,
    marketing_consent BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
)
```

### Guest Statistics View
```sql
guest_statistics (
    -- Alle guest velden +
    total_bookings INTEGER,
    confirmed_bookings INTEGER,
    completed_stays INTEGER,
    total_revenue DECIMAL,
    last_visit DATE,
    average_booking_value DECIMAL,
    loyalty_level VARCHAR -- Bronze/Silver/Gold/VIP
)
```

## 🔄 Migratie Proces Details

### Wat het script doet:

1. **Maakt guests tabel** met uitgebreide velden
2. **Migreert bestaande data** van bookings naar guests
3. **Voegt guest_id kolom toe** aan bookings tabel
4. **Linkt bestaande reserveringen** aan nieuwe gasten
5. **Maakt statistieken view** voor rapportage
6. **Configureert RLS** voor security
7. **Voegt triggers toe** voor updated_at

### Backward Compatibility:

- ✅ **Oude velden blijven bestaan** (guest_name, guest_email, guest_phone)
- ✅ **API heeft fallback** naar oude methode
- ✅ **Geen breaking changes** voor frontend
- ✅ **Geleidelijke overgang** mogelijk

## 🛠️ Volgende Stappen (Optioneel)

Na succesvolle migratie en testing:

### Stap A: Maak guest_id verplicht
```sql
-- Alleen uitvoeren als alle bookings een guest_id hebben
ALTER TABLE bookings ALTER COLUMN guest_id SET NOT NULL;
ALTER TABLE bookings ADD FOREIGN KEY (guest_id) REFERENCES guests(id);
```

### Stap B: Verwijder oude kolommen (voorzichtig!)
```sql
-- Backup eerst je database!
-- ALTER TABLE bookings DROP COLUMN guest_name;
-- ALTER TABLE bookings DROP COLUMN guest_email;  
-- ALTER TABLE bookings DROP COLUMN guest_phone;
```

### Stap C: Update reserveringen API
Update `src/pages/api/reserveringen.ts` om guest_id te gebruiken in plaats van guest velden.

## 🚨 Rollback Plan

Als er problemen zijn, kun je terugdraaien:

```sql
-- ALLEEN IN NOODGEVAL!
DROP VIEW IF EXISTS guest_statistics;
DROP TABLE IF EXISTS guests CASCADE;
ALTER TABLE bookings DROP COLUMN IF EXISTS guest_id;
```

## 📈 Nieuwe Mogelijkheden

Na de migratie kun je:

- **Gast profielen** uitbreiden met voorkeuren
- **Loyaliteit programma** implementeren
- **Marketing campagnes** opzetten
- **Gast geschiedenis** bekijken
- **Automatische communicatie** instellen
- **Rapportages** maken over gast gedrag

## 🔍 Troubleshooting

### Probleem: "guests table doesn't exist"
- **Oplossing**: Voer het migratie script opnieuw uit

### Probleem: "duplicate key value violates unique constraint"
- **Oplossing**: Er zijn duplicate emails, check handmatig:
```sql
SELECT email, COUNT(*) FROM bookings 
WHERE guest_email IS NOT NULL 
GROUP BY email HAVING COUNT(*) > 1;
```

### Probleem: API geeft lege array terug
- **Oplossing**: Check of guest_statistics view bestaat:
```sql
SELECT * FROM information_schema.views WHERE table_name = 'guest_statistics';
```

## ✅ Checklist

- [ ] Database migratie script uitgevoerd
- [ ] Verificatie queries succesvol
- [ ] API endpoints getest
- [ ] Frontend gast zoeken werkt
- [ ] Nieuwe gast aanmaken werkt
- [ ] Gasten blijven bestaan na reservering wijzigingen
- [ ] Gast statistieken zichtbaar

## 📞 Support

Bij problemen, check:
1. **Supabase logs** voor database errors
2. **Browser console** voor frontend errors  
3. **Server logs** voor API errors
4. **Dit document** voor troubleshooting

---

**🎉 Na succesvolle migratie heb je een professioneel gasten management systeem!**
