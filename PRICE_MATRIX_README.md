# Price Matrix System

## Overview

The Price Matrix System provides a **superadmin-controlled pricing foundation** for all apartments. It consists of:

1. **Pricing Matrix** (`pricing_matrices` table): Base prices per apartment type (Studio, 1BR, 2BR+1WC, etc.)
2. **District Indices** (`district_price_indices` table): Geographic multipliers per district
3. **Building Factors** (`building_pricing_factors` table): Per-building customization (factor or absolute price override)

## Price Calculation Formula

```
defaultPrice = round50k( PM_BASE[typeKey] × PM_DISTRICT_INDEX[district] × buildingFactor )
```

Where:
- **PM_BASE**: Base price from pricing matrix (VND/night) — set by superadmin
- **PM_DISTRICT_INDEX**: District multiplier — set by superadmin
- **buildingFactor**: Building-specific multiplier — can be auto-generated or overridden by superadmin
- **round50k**: Round to nearest 50,000 VND

## Setup

### 1. Run Migrations

```bash
php artisan migrate
```

This creates three tables:
- `pricing_matrices` - Base prices and type keys
- `district_price_indices` - District multipliers
- `building_pricing_factors` - Per-building overrides

### 2. Initialize Matrix Data

```bash
php artisan price-matrix:initialize
```

This populates the matrix with default values from the spec:
- 8 apartment types (Studio → 5BR+3WC)
- 13 district locations and indices
- Falls back to 0.85 for unknown districts

## Usage in Code

### Calculate Price for an Apartment

```php
use App\Services\PriceMatrixService;
use App\Models\Building;

$service = new PriceMatrixService();
$building = Building::find(1);

// Calculate price for 2BR+1WC apartment in this building
$price = $service->calculateDefaultMatrixPrice($building, '2BR+1WC');
// Returns: 1,950,000 VND (or null if type key not found)
```

### Get Type Key from Bedroom/Bathroom Count

```php
$service = new PriceMatrixService();

// Without WC count (uses default: 2BR → 1WC)
$typeKey = $service->pmKey('2BR'); // Returns: "2BR+1WC"

// With explicit WC count
$typeKey = $service->pmKey('2BR', 2); // Returns: "2BR+2WC"

// Studio/1BR (no WC variant)
$typeKey = $service->pmKey('Studio'); // Returns: "Studio"
```

### Calculate Matrix Price by District (Pure Formula)

```php
$service = new PriceMatrixService();

// Calculate base price for D1 without building factor
$price = $service->calculateMatrixPriceByDistrict('D1', '2BR+1WC');
// Returns: round50k(1,500,000 VND × 1.15) = 1,725,000 VND
```

### Get Available Type Keys

```php
$service = new PriceMatrixService();

$types = $service->getAllTypeKeys();
// Returns: ['Studio', '1BR', '2BR+1WC', '2BR+2WC', ...]

$prices = $service->getAllBasePrices();
// Returns: ['Studio' => 900000, '1BR' => 1150000, ...]

$indices = $service->getAllDistrictIndices();
// Returns: ['D1' => 1.15, 'D2' => 1.05, ...]
```

## Building Factor Behavior

### Default Factor (No Superadmin Override)

If no override is set, a **deterministic hash-based factor** (0.96–1.06) is generated from the building name:

```php
factor = 0.96 + (crc32(buildingName) % 7) / 58
```

This makes demo pricing look plausible but is **not** intended for production. ⚠️

### Superadmin Overrides

Superadmin can set **either**:

1. **Factor Override** (e.g., 1.02) — applies formula:
   ```
   price = round50k( PM_BASE × PM_DISTRICT × factor )
   ```

2. **Price Override** (absolute VND) — ignores formula:
   ```
   price = priceOverride
   ```

Priority: Price override > Factor override > Default hash-based factor

## Superadmin Management (Future UI)

Superadmin panel should provide:

### Matrix Management
- [ ] View/edit base prices per type
- [ ] View/edit district indices

### Building Customization
- [ ] Set factor override per building × type
- [ ] Set absolute price override per building × type
- [ ] Preview calculated prices before saving

### Data Audit
- [ ] See which apartments use default matrix vs custom override
- [ ] Compare calculated vs stored prices
- [ ] Sync stale prices to matrix after changes

## Integration Points

### Apartment Pricing Service
Update [App\Services\ApartmentPricingService](../Services/ApartmentPricingService.php) to:
```php
$matrixService = new PriceMatrixService();
$suggestedPrice = $matrixService->calculateDefaultMatrixPrice($building, $typeKey);
```

### Booking Price Calculation
[App\Services\BookingPricingService](../Services/BookingPricingService.php) can use matrix price as baseline:
```php
$basePrice = $matrixService->calculateDefaultMatrixPrice($building, $typeKey);
// Then apply seasonal, discount, and promotion logic on top
```

### Apartment Creation
When creating new apartments, use matrix as default:
```php
$suggestedPrice = $matrixService->calculateDefaultMatrixPrice($building, $typeKey);
$apartment->price_daily = $suggestedPrice;
```

## Testing

```bash
# Test the service
php artisan tinker

>>> $service = new \App\Services\PriceMatrixService();
>>> $building = \App\Models\Building::first();
>>> $service->calculateDefaultMatrixPrice($building, '2BR+1WC');
```

## Database Schema Details

### pricing_matrices
```
id, type_key (unique), base_price_vnd, timestamps
```

### district_price_indices
```
id, district_code (unique), district_name, price_index, timestamps
```

### building_pricing_factors
```
id, building_id (FK), type_key, price_override_vnd, factor_override, timestamps
UNIQUE(building_id, type_key)
```

## Notes & Assumptions

1. **Type Keys** are predefined (Studio, 1BR, 2BR+1WC, etc.) — not dynamically created
2. **District Codes** map to existing `building.district` (e.g., D1, BT, TD)
3. **Building Factor** defaults are **demo-only** — production must use stored overrides
4. **Currency** is always VND (configurable via `config/vietstays.php`)
5. Falls back to `unknown` district (0.85 index) if district_code not found
