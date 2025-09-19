# 🏨 Eenvoudige Gasten Tabel Setup

## 🎯 Doel
Voeg een separate gasten tabel toe zodat gasten niet verdwijnen als je reserveringen wijzigt.

## 🚀 Stappen

### 1. Database Script Uitvoeren
1. Open **Supabase Dashboard** → **SQL Editor**
2. Kopieer de inhoud van `scripts/add_guests_table.sql`
3. Plak en voer uit
4. Klaar! 🎉

### 2. Test het Resultaat
Ga naar: `http://localhost:2121/beheer/reserveringen`

**Test scenario:**
1. Maak nieuwe reservering aan met gast "Jan de Vries"
2. Maak nog een reservering aan met gast "Piet Jansen"  
3. Wijzig de eerste reservering naar gast "Piet Jansen"
4. Ga naar gasten overzicht → **Beide gasten bestaan nog!** ✅

## 🔍 Wat het Script Doet

```sql
-- 1. Maakt guests tabel
CREATE TABLE guests (
    id, property_id, name, email, phone, created_at
);

-- 2. Voegt guest_id toe aan bookings
ALTER TABLE bookings ADD COLUMN guest_id UUID;

-- 3. Maakt statistieken view
CREATE VIEW guest_statistics AS ...

-- 4. Migreert bestaande data (als die er is)
INSERT INTO guests SELECT DISTINCT ... FROM bookings;
```

## ✅ Resultaat

**Voor:** Gasten = dynamisch uit reserveringen
**Na:** Gasten = separate tabel, blijven altijd bestaan

**Voordelen:**
- ✅ Gasten verdwijnen niet meer
- ✅ Gast geschiedenis behouden  
- ✅ Loyaliteit tracking mogelijk
- ✅ Betere data integriteit

## 🚨 Probleem?

**Error zien?** Check de browser console en server logs.
**Gasten niet zichtbaar?** Refresh de pagina.
**Nog steeds issues?** Het systeem valt terug op de oude methode.

---
**Simpel en effectief! 🎯**
