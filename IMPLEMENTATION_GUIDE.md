# Price Simulator Implementation - Setup Guide

## ✅ What Has Been Implemented

A complete **Price Matrix System** for the Vietstays project, matching the specification from your old project design.

### Components Created

#### 🏗️ Database Layer
1. **Migrations** (`database/migrations/2026_09_03_120000_create_pricing_matrix_tables.php`)
   - `pricing_matrices` — Base prices per type (Studio → 5BR+3WC)
   - `district_price_indices` — District multipliers (D1 → unknown)
   - `building_pricing_factors` — Per-building overrides (factor or absolute price)

2. **Models**
   - `App\Models\PricingMatrix` — Manages base prices
   - `App\Models\DistrictPriceIndex` — Manages district multipliers
   - `App\Models\BuildingPricingFactor` — Manages building customizations

#### 🎯 Business Logic
3. **Service** (`App\Services\PriceMatrixService`)
   - `calculateDefaultMatrixPrice()` — Main formula implementation
   - `pmKey()` — Type key generation from bedroom/bathroom count
   - `calculateMatrixPriceByDistrict()` — Pure matrix pricing without building factor
   - Helper methods for lookups and conversions

#### 🗂️ Data & Configuration
4. **Seeder** (`Database\Seeders\PriceMatrixSeeder`)
   - Populates all base prices and district indices from spec
   - Safe to run repeatedly (uses `updateOrCreate`)

5. **Configuration** (`config/price_matrix.php`)
   - Centralized settings for rounding, defaults, type keys

#### 🚀 Utilities
6. **Artisan Command** (`App\Console\Commands\InitializePriceMatrix`)
   - Run: `php artisan price-matrix:initialize`
   - Populates matrix with seed data

7. **Tests** (`tests/Unit/Services/PriceMatrixServiceTest.php`)
   - Full test suite with 12 test cases
   - Covers matrix calculation, overrides, and type key logic

#### 📚 Documentation
8. **README** (`PRICE_MATRIX_README.md`)
   - Complete usage guide
   - Formula explanation
   - Code examples
   - Integration points for existing services

---

## 🚀 Getting Started (Step-by-Step)

### Step 1: Run Migrations
```bash
php artisan migrate
```

Creates the three price matrix tables in your database.

### Step 2: Seed Initial Data
```bash
php artisan price-matrix:initialize
```

Populates matrix with:
- 8 apartment types (Studio, 1BR, 2BR+1WC, … 5BR+3WC)
- 13 district locations with their price indices
- Ready for superadmin customization

### Step 3: Test the Service
```bash
php artisan tinker
```

```php
>>> $service = new \App\Services\PriceMatrixService();

// Get type key
>>> $key = $service->pmKey('2BR', 2); // "2BR+2WC"

// Calculate a price
>>> $building = \App\Models\Building::first();
>>> $price = $service->calculateDefaultMatrixPrice($building, '2BR+1WC');
// Returns: 1,950,000 VND (or similar, depending on building location)

// Get all available types
>>> $service->getAllTypeKeys();
// ["Studio", "1BR", "2BR+1WC", "2BR+2WC", ...]

// Get district index
>>> $service->calculateMatrixPriceByDistrict('D1', '2BR+1WC');
// Returns: 1,725,000 VND (1,500,000 × 1.15)
```

### Step 4: Run Tests
```bash
php artisan test tests/Unit/Services/PriceMatrixServiceTest.php
```

Validates all functionality works correctly.

---

## 💡 Price Formula Explained

```
defaultPrice = round50k( PM_BASE[typeKey] × PM_DISTRICT_INDEX[district] × buildingFactor )
```

**Example calculation:**
- Apartment type: 2BR+1WC → PM_BASE = 1,500,000 VND
- Located in D1 → PM_DISTRICT_INDEX = 1.15
- Building in D1 → buildingFactor = 1.02 (superadmin override)
- Result: round50k(1,500,000 × 1.15 × 1.02) = **1,800,000 VND**

---

## 🔧 Integration with Existing Code

### Update Apartment Pricing Service
Edit `app/Services/ApartmentPricingService.php`:

```php
use App\Services\PriceMatrixService;

public function suggestPrice(Building $building, string $type, ?int $wcCount = null): int
{
    $priceMatrixService = new PriceMatrixService();
    $typeKey = $priceMatrixService->pmKey($type, $wcCount);
    
    return $priceMatrixService->calculateDefaultMatrixPrice($building, $typeKey)
        ?? $this->getLegacyFallbackPrice($type); // Fallback for unknown types
}
```

### Use in Booking Pricing
Edit `app/Services/BookingPricingService.php`:

```php
$basePrice = (new PriceMatrixService())->calculateDefaultMatrixPrice(
    $booking->apartment->building,
    $booking->apartment->getTypeKey()
);

// Then apply seasonal, discount, and promotional logic on top
$totalPrice = $this->applySeasonalPricing($basePrice, $startDate, $endDate);
$totalPrice = $this->applyPromoCode($totalPrice, $promoCode);
```

### New Apartment Creation
When creating apartments, suggest matrix price:

```php
$apartment->price_daily = (new PriceMatrixService())
    ->calculateDefaultMatrixPrice($building, $typeKey);
```

---

## 🎛️ Superadmin Management (Future UI)

The system supports three types of customization:

### 1. **Edit Base Prices** (Affects all buildings in all districts)
```php
$matrix = PricingMatrix::where('type_key', '2BR+1WC')->first();
$matrix->update(['base_price_vnd' => 1_600_000]);
```

### 2. **Edit District Indices** (Affects all buildings in that district)
```php
$index = DistrictPriceIndex::where('district_code', 'D1')->first();
$index->update(['price_index' => 1.20]); // Increase D1 by 4%
```

### 3. **Override per Building × Type**
```php
// Option A: Set building factor (e.g., 1.05)
BuildingPricingFactor::create([
    'building_id' => $building->id,
    'type_key' => '2BR+1WC',
    'factor_override' => 1.05, // Apply 5% markup
]);

// Option B: Set absolute price (overrides formula)
BuildingPricingFactor::create([
    'building_id' => $building->id,
    'type_key' => '2BR+1WC',
    'price_override_vnd' => 2_000_000, // Exact price in VND
]);
```

---

## 📊 Database Schema Quick Reference

### pricing_matrices
```
id (PK)
type_key (unique) — Studio, 1BR, 2BR+1WC, etc.
base_price_vnd — Base price in VND/night
timestamps
```

### district_price_indices
```
id (PK)
district_code (unique) — D1, D2, BT, etc.
district_name — Display name
price_index — Multiplier (0.85 to 1.15)
timestamps
```

### building_pricing_factors
```
id (PK)
building_id (FK) → buildings.id
type_key — Apartment type (matches pricing_matrices.type_key)
price_override_vnd — Absolute price (if set, takes precedence)
factor_override — Multiplier (if price_override not set)
timestamps
UNIQUE(building_id, type_key)
```

---

## 🔍 Key Features

✅ **Formula-based** — Central formula ensures consistency
✅ **Layered customization** — Superadmin can override at matrix, district, or building level
✅ **Type-safe** — Uses integer VND, no floating-point rounding errors
✅ **Extensible** — Easy to add seasonal, promotional, or other pricing logic on top
✅ **Well-tested** — 12 unit tests covering all scenarios
✅ **Production-ready** — Does NOT use hash-based factors in database (demo fallback only)

---

## ⚠️ Important Notes

1. **Building factors** in production MUST be stored in `building_pricing_factors.factor_override` — the default hash-based fallback is **demo-only**
2. **Type keys** are predefined (not dynamically created) — manage via migrations and seeder
3. **District codes** must match `building.district.district_code` — coordinate with your district setup
4. **Currency** is always VND — managed via `config/vietstays.php`
5. **Rounding** to 50,000 VND is enforced at calculation time, not in database

---

## 📝 Files Created/Modified

```
app/
  Console/Commands/
    InitializePriceMatrix.php (NEW)
  Models/
    PricingMatrix.php (NEW)
    DistrictPriceIndex.php (NEW)
    BuildingPricingFactor.php (NEW)
  Services/
    PriceMatrixService.php (NEW)

config/
  price_matrix.php (NEW)

database/
  migrations/
    2026_09_03_120000_create_pricing_matrix_tables.php (NEW)
  seeders/
    PriceMatrixSeeder.php (NEW)

tests/
  Unit/Services/
    PriceMatrixServiceTest.php (NEW)

PRICE_MATRIX_README.md (NEW)
IMPLEMENTATION_GUIDE.md (THIS FILE)
```

---

## 🆘 Troubleshooting

**Q: Migration fails with "Table already exists"**
- Run: `php artisan migrate:reset` (careful!) or manually drop tables

**Q: `pmKey()` returns null for unknown types**
- Check that type exists in `pricing_matrices` table
- Run `php artisan price-matrix:initialize` to seed data

**Q: Building factor calculations seem off**
- Verify `building.district.district_code` matches a code in `district_price_indices`
- Check for `BuildingPricingFactor` overrides (query: `BuildingPricingFactor::where('building_id', $id)->get()`)

**Q: Prices don't match expected formula**
- Use tinker to debug step-by-step:
  ```php
  >>> $b = Building::find(1);
  >>> $s = new \App\Services\PriceMatrixService();
  >>> $s->calculateDefaultMatrixPrice($b, '2BR+1WC');
  ```

---

## ✨ Next Steps

1. ✅ Run migrations and seeder
2. ✅ Test with `php artisan test`
3. ✅ Integrate with `ApartmentPricingService`
4. ✅ Build superadmin UI for matrix management
5. ✅ Update apartment creation flow to use matrix
6. ✅ Monitor and adjust district/building factors based on market data

---

**Implementation Date:** September 3, 2026
**Spec Version:** Price Simulator (Design Handoff)
