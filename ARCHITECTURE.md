# Price Matrix System - Architecture

## Data Flow Diagram

```
                    ┌─────────────────────────────────┐
                    │   Superadmin Dashboard (Future)  │
                    └────────────────┬──────────────────┘
                                     │
                    ┌────────────────▼──────────────────┐
        ┌─── Edit ──►│   pricing_matrices              │
        │           │   district_price_indices        │
        │           │   building_pricing_factors      │
        │           └────────────────┬──────────────────┘
        │                            │
        │                    ┌───────▼────────┐
        │                    │   Database     │
        │                    │   (3 tables)   │
        │                    └───────┬────────┘
        │                            │
    ┌───┴──────────────────────────────┤
    │                                  │
    │   PriceMatrixService             │
    │   ├─ pmKey()                     │
    │   ├─ calculateDefaultMatrixPrice()
    │   ├─ calculateMatrixPriceByDistrict()
    │   └─ getAllTypeKeys/Prices/Indices
    │                                  │
    ├──────────────┬───────────────────┤
    │              │                   │
    │         ┌────▼────┐          ┌───▼──────┐
    │         │ Apartment │      │ Booking   │
    │         │ Pricing   │      │ Pricing   │
    │         │ Service   │      │ Service   │
    │         └───┬──────┘      └───┬──────┘
    │             │                 │
    └──────┬──────┴────────┬────────┤
           │               │        │
       ┌───▼───┐      ┌────▼───┐  ┌▼──────┐
       │Suggest│      │Calculate│ │ Apply  │
       │ Price │      │ Base    │ │ Promo  │
       │       │      │ Price   │ │ Logic  │
       └───────┘      └─────────┘ └────────┘
```

## Component Hierarchy

```
┌─────────────────────────────────────────────────────┐
│           PRICE MATRIX SYSTEM                        │
├─────────────────────────────────────────────────────┤
│                                                      │
│  ┌─ DATABASE LAYER ─────────────────────────────┐  │
│  │                                               │  │
│  │  ▪ PricingMatrix (8 types × prices)         │  │
│  │  ▪ DistrictPriceIndex (13 districts)        │  │
│  │  ▪ BuildingPricingFactor (N buildings)      │  │
│  │                                               │  │
│  └───────────────────────────────────────────────┘  │
│                                                      │
│  ┌─ SERVICE LAYER ───────────────────────────────┐  │
│  │                                                │  │
│  │  PriceMatrixService                          │  │
│  │  ├─ Core Methods                            │  │
│  │  │  ├─ calculateDefaultMatrixPrice()        │  │
│  │  │  ├─ calculateMatrixPriceByDistrict()    │  │
│  │  │  └─ pmKey()                              │  │
│  │  └─ Helper Methods                          │  │
│  │     ├─ getDefaultBuildingFactor()          │  │
│  │     ├─ round50k()                           │  │
│  │     └─ getFirstExistingTypeKey()           │  │
│  │                                               │  │
│  └───────────────────────────────────────────────┘  │
│                                                      │
│  ┌─ SEEDING & CONFIG ────────────────────────────┐  │
│  │                                                │  │
│  │  Seeder: PriceMatrixSeeder                  │  │
│  │  Config: config/price_matrix.php             │  │
│  │  Command: php artisan price-matrix:init     │  │
│  │                                               │  │
│  └───────────────────────────────────────────────┘  │
│                                                      │
└─────────────────────────────────────────────────────┘
```

## Price Calculation Flow

```
calculateDefaultMatrixPrice(Building, typeKey)
│
├─ 1️⃣  Get Base Price
│     └─ PricingMatrix::getBasePrice(typeKey)
│        └─ Returns: PM_BASE[typeKey]
│
├─ 2️⃣  Get District Index
│     └─ Building.district → DistrictPriceIndex::getPriceIndex(code)
│        └─ Returns: PM_DISTRICT_INDEX[district] (or 0.85 default)
│
├─ 3️⃣  Get Building Factor
│     └─ BuildingPricingFactor::getPriceOverride() (Priority 1)
│        ├─ If set: RETURN round50k(priceOverride) ✓
│        └─ If not:
│           └─ BuildingPricingFactor::getFactor() (Priority 2)
│              ├─ If set: factor = factorOverride
│              └─ If not: factor = getDefaultBuildingFactor(name) (0.96-1.06)
│
├─ 4️⃣  Calculate
│     └─ price = round50k(PM_BASE × PM_DISTRICT × factor)
│
└─ 5️⃣  Return Result
        └─ price (integer VND, multiple of 50,000)
```

## Type Key Generation Logic

```
pmKey(type, wcCount)
│
├─ If type in [Studio, 1BR]
│  └─ Return type (no WC variant) ✓
│
├─ Else if wcCount provided
│  └─ Return "{type}+{wcCount}WC" ✓
│
├─ Else (wcCount = null)
│  ├─ Look up DEFAULT_WC[type]
│  │  └─ If exists: wcCount = DEFAULT_WC[type]
│  │     └─ Return "{type}+{wcCount}WC" ✓
│  │
│  └─ Fallback: getFirstExistingTypeKey(type)
│     └─ Query database for first matching "{type}+*WC"
│        └─ Return first match ✓
```

## Override Priority

When calculating price for a building × type pair:

```
┌─────────────────────────────────────────┐
│  1. Price Override (if set)             │  ← HIGHEST PRIORITY
│     └─ Absolute VND price               │
│        └─ Ignores formula entirely      │
└─────────────┬───────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────┐
│  2. Factor Override (if set)            │
│     └─ Multiplier (0.96-1.06)           │
│        └─ Applied to formula            │
└─────────────┬───────────────────────────┘
              │
              ▼
┌─────────────────────────────────────────┐
│  3. Default Hash-Based Factor           │  ← LOWEST PRIORITY
│     └─ 0.96 + (hash % 7)/58             │
│        └─ Demo-only, for plausibility   │
└─────────────────────────────────────────┘
```

## Integration Points

```
┌─────────────────────────────────────────────────────────┐
│         Existing Services (Integration Points)           │
├─────────────────────────────────────────────────────────┤
│                                                           │
│  1. ApartmentPricingService                            │
│     ├─ import PriceMatrixService                       │
│     └─ Use: calculateDefaultMatrixPrice() for base    │
│                                                           │
│  2. BookingPricingService                              │
│     ├─ import PriceMatrixService                       │
│     └─ Use: calculateDefaultMatrixPrice() for base    │
│        └─ Then apply seasonal, promo, discount logic  │
│                                                           │
│  3. ApartmentCreationService                           │
│     ├─ import PriceMatrixService                       │
│     └─ Use: calculateDefaultMatrixPrice() to suggest  │
│        └─ When creating new apartments                │
│                                                           │
└─────────────────────────────────────────────────────────┘
```

## Test Coverage

```
PriceMatrixServiceTest (12 tests)
├─ Type Key Generation (pmKey)
│  ├─ Studio (no WC variant)
│  ├─ 1BR (no WC variant)
│  ├─ With default WC count
│  └─ With explicit WC count
│
├─ Price Calculation (matrix formula)
│  ├─ Basic calculation (with auto factor)
│  ├─ With factor override
│  ├─ With absolute price override
│  └─ By district (no building factor)
│
├─ Lookups & Retrieval
│  ├─ getAllTypeKeys()
│  ├─ getAllBasePrices()
│  └─ getAllDistrictIndices()
│
└─ Rounding Verification
   └─ All results are multiples of 50,000 VND
```

## Example Calculation

```
Apartment: 2-bedroom, 2-bathroom
Building: "The Golden Tower" in District 1
Superadmin Config: No overrides (uses default factor)

Step 1: Get type key
  pmKey('2BR', 2) → "2BR+2WC"

Step 2: Get base price
  PricingMatrix.getBasePrice('2BR+2WC') → 1,900,000 VND

Step 3: Get district index
  Building.district.code = 'D1'
  DistrictPriceIndex.getPriceIndex('D1') → 1.15

Step 4: Get building factor
  BuildingPricingFactor for (building_id, '2BR+2WC') → null (no override)
  Default factor = 0.96 + (crc32("The Golden Tower") % 7) / 58
              = 0.96 + 2/58
              = 0.96 + 0.0345
              = 0.9945

Step 5: Calculate
  price = round50k(1,900,000 × 1.15 × 0.9945)
        = round50k(2,185,018)
        = 2,200,000 VND (rounded up to nearest 50k)

Result: 2,200,000 VND/night
```

---

**Architecture designed for:**
- ✅ Clarity and maintainability
- ✅ Flexibility (layered overrides)
- ✅ Testability (isolated logic)
- ✅ Scalability (database-backed, not hardcoded)
- ✅ Production-readiness (no randomization)
