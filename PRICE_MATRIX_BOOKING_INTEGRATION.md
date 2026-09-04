# Integration Guide - Price Matrix into Booking Form

## Overview

This guide shows how to integrate the **Price Matrix System** into your `AddBookingModal.vue` component to display **real-time price suggestions** when the user selects an apartment and dates.

## What We've Created

1. **API Endpoint**: `POST /api/bookings/calculate-price`
   - Takes: `apartment_id`, `check_in`, `check_out`
   - Returns: Suggested price from matrix + current price + comparison

2. **Vue Composable**: `usePriceCalculator.js`
   - Handles API calls
   - Provides formatted display values
   - Manages loading/error states

## How to Update AddBookingModal.vue

### Step 1: Add the Composable Import

At the top of your `AddBookingModal.vue` (in the `<script setup>` section):

```vue
import { usePriceCalculator } from '@/composables/usePriceCalculator'

const {
  loading: priceLoading,
  error: priceError,
  priceData,
  calculateBookingPrice,
  suggestedDailyPriceFormatted,
  suggestedTotalFormatted,
  priceDifferenceFormatted,
  priceDifferenceClass,
} = usePriceCalculator()
```

### Step 2: Add Watchers for Apartment & Dates

Add this to your component's reactive data (watch apartment selection and dates):

```vue
// Watch apartment selection
watch(() => form.apartment_id, async () => {
  if (form.apartment_id && form.check_in && form.check_out) {
    await calculateBookingPrice(form.apartment_id, form.check_in, form.check_out)
  }
})

// Watch check-in date
watch(() => form.check_in, async () => {
  if (form.apartment_id && form.check_in && form.check_out) {
    await calculateBookingPrice(form.apartment_id, form.check_in, form.check_out)
  }
})

// Watch check-out date
watch(() => form.check_out, async () => {
  if (form.apartment_id && form.check_in && form.check_out) {
    await calculateBookingPrice(form.apartment_id, form.check_in, form.check_out)
  }
})
```

Alternatively, use a computed watcher:

```vue
const dateKey = computed(() => `${form.apartment_id}-${form.check_in}-${form.check_out}`)

watch(dateKey, async () => {
  if (form.apartment_id && form.check_in && form.check_out) {
    await calculateBookingPrice(form.apartment_id, form.check_in, form.check_out)
  }
})
```

### Step 3: Display Price Suggestion in Form

In your form template, add a section to show the price suggestion:

```vue
<template>
  <div class="modal-dialog">
    <div class="modal-content">
      <!-- ... existing form fields ... -->

      <!-- Apartment Selection -->
      <div class="form-group">
        <label for="apartment">Apartment</label>
        <select 
          id="apartment" 
          v-model="form.apartment_id" 
          class="form-control"
          required
        >
          <option value="">Select apartment</option>
          <option v-for="apt in apartments" :key="apt.ID" :value="apt.ID">
            {{ apt.name }}
          </option>
        </select>
      </div>

      <!-- Dates -->
      <div class="row">
        <div class="col-md-6">
          <div class="form-group">
            <label for="check-in">Check-in</label>
            <input 
              id="check-in"
              v-model="form.check_in" 
              type="date" 
              class="form-control"
              required
            />
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            <label for="check-out">Check-out</label>
            <input 
              id="check-out"
              v-model="form.check_out" 
              type="date" 
              class="form-control"
              required
            />
          </div>
        </div>
      </div>

      <!-- Price Suggestion Section -->
      <div v-if="priceData" class="price-suggestion-card">
        <div class="card">
          <div class="card-header bg-light">
            <h6 class="mb-0">💰 Price Suggestion (from Matrix)</h6>
          </div>
          <div class="card-body">
            <!-- Error -->
            <div v-if="priceError" class="alert alert-warning mb-3">
              {{ priceError }}
            </div>

            <!-- Loading -->
            <div v-if="priceLoading" class="text-center py-3">
              <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>

            <!-- Price Details -->
            <div v-if="!priceLoading" class="price-details">
              <div class="row mb-3 pb-3 border-bottom">
                <div class="col-sm-6">
                  <small class="text-muted">Building</small>
                  <div class="fw-bold">{{ priceData.building_name }}</div>
                </div>
                <div class="col-sm-6">
                  <small class="text-muted">District</small>
                  <div class="fw-bold">{{ priceData.district }}</div>
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-sm-6">
                  <small class="text-muted">Type</small>
                  <div class="fw-bold">{{ priceData.apartment_type }}</div>
                </div>
                <div class="col-sm-6">
                  <small class="text-muted">Nights</small>
                  <div class="fw-bold">{{ priceData.nights }} night{{ priceData.nights !== 1 ? 's' : '' }}</div>
                </div>
              </div>

              <!-- Price Comparison Table -->
              <table class="table table-sm table-borderless">
                <tbody>
                  <tr>
                    <td class="text-muted">Daily Rate (Matrix)</td>
                    <td class="text-end fw-bold text-success">
                      {{ suggestedDailyPriceFormatted }} ₫
                    </td>
                  </tr>
                  <tr>
                    <td class="text-muted">Daily Rate (Current)</td>
                    <td class="text-end">
                      {{ currentDailyPriceFormatted }} ₫
                    </td>
                  </tr>
                  <tr class="table-active">
                    <td class="text-muted">Difference</td>
                    <td class="text-end fw-bold" :class="priceDifferenceClass">
                      {{ priceDifferenceFormatted }} ₫
                      <small v-if="priceData.price_difference_percent" class="ms-2">
                        ({{ priceData.price_difference_percent > 0 ? '+' : '' }}{{ priceData.price_difference_percent }}%)
                      </small>
                    </td>
                  </tr>
                </tbody>
              </table>

              <!-- Room & Fees -->
              <div class="row my-3">
                <div class="col-6">
                  <small class="text-muted">Room Total</small>
                  <div class="fw-bold">{{ formatVnd(priceData.suggested_room_total) }} ₫</div>
                </div>
                <div class="col-6">
                  <small class="text-muted">Cleaning Fee</small>
                  <div class="fw-bold">{{ formatVnd(priceData.cleaning_fee) }} ₫</div>
                </div>
              </div>

              <!-- Total -->
              <div class="alert alert-info mb-0">
                <div class="d-flex justify-content-between align-items-baseline">
                  <span>Suggested Total (incl. 5% fee)</span>
                  <span class="h5 mb-0">{{ suggestedTotalFormatted }} ₫</span>
                </div>
              </div>

              <!-- Info -->
              <small class="text-muted d-block mt-3">
                📌 Suggestion based: <code>{{ priceData.matrix.formula }}</code>
              </small>
            </div>
          </div>
        </div>
      </div>

      <!-- Existing Nightly Rate Field -->
      <div class="form-group">
        <label for="price">Nightly Rate (VND)</label>
        <div class="input-group">
          <input 
            id="price"
            v-model.number="form.price" 
            type="number" 
            step="50000"
            class="form-control"
            placeholder="Enter nightly rate"
            required
          />
          <span class="input-group-text">₫</span>
        </div>
        <small class="form-text text-muted" v-if="priceData">
          💡 Suggestion: {{ suggestedDailyPriceFormatted }} ₫
          <a href="#" @click.prevent="form.price = priceData.suggested_daily_price" class="ms-2">
            Use suggestion
          </a>
        </small>
      </div>

      <!-- ... rest of your form ... -->
    </div>
  </div>
</template>
```

### Step 4: Add Helper Function for Currency Formatting

Add this helper to your component:

```vue
<script setup>
const formatVnd = (value) => {
  return new Intl.NumberFormat('vi-VN').format(value)
}
</script>
```

## Complete Example

Here's a simplified `AddBookingModal.vue` structure:

```vue
<template>
  <div class="modal fade" id="addBookingModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Manual Booking</h5>
          <button type="button" class="btn-close" @click="closeModal"></button>
        </div>

        <form @submit.prevent="submitForm">
          <div class="modal-body">
            
            <!-- Apartment Selection -->
            <div class="form-group mb-3">
              <label for="apt">Apartment *</label>
              <select 
                id="apt"
                v-model.number="form.apartment_id"
                class="form-select"
                required
              >
                <option :value="null">Select apartment</option>
                <option v-for="apt in apartments" :key="apt.ID" :value="apt.ID">
                  {{ apt.name }} - {{ apt.building_display }}
                </option>
              </select>
            </div>

            <!-- Dates -->
            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="checkin">Check-in *</label>
                  <input
                    id="checkin"
                    v-model="form.check_in"
                    type="date"
                    class="form-control"
                    required
                  />
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="checkout">Check-out *</label>
                  <input
                    id="checkout"
                    v-model="form.check_out"
                    type="date"
                    class="form-control"
                    required
                  />
                </div>
              </div>
            </div>

            <!-- Price Suggestion Card -->
            <div v-if="priceData && !priceLoading" class="card border-info mb-3">
              <div class="card-header bg-info bg-opacity-10">
                <h6 class="mb-0">💰 Matrix Price Suggestion</h6>
              </div>
              <div class="card-body">
                <div class="row mb-2">
                  <div class="col-6">
                    <small class="text-muted">Suggested Daily Rate</small>
                    <div class="h6 text-success mb-0">{{ suggestedDailyPriceFormatted }} ₫</div>
                  </div>
                  <div class="col-6">
                    <small class="text-muted">Current Daily Rate</small>
                    <div class="h6 mb-0">{{ currentDailyPriceFormatted }} ₫</div>
                  </div>
                </div>
                <div class="progress mb-2">
                  <div class="progress-bar" role="progressbar" :style="{width: '100%'}"></div>
                </div>
                <small class="text-muted">
                  Difference: <span :class="priceDifferenceClass" class="fw-bold">
                    {{ priceDifferenceFormatted }} ₫
                  </span>
                </small>
              </div>
            </div>

            <!-- Nightly Rate -->
            <div class="form-group mb-3">
              <label for="rate">Nightly Rate (VND) *</label>
              <div class="input-group">
                <input
                  id="rate"
                  v-model.number="form.price"
                  type="number"
                  step="50000"
                  class="form-control"
                  required
                />
                <span class="input-group-text">₫</span>
                <button
                  v-if="priceData"
                  type="button"
                  class="btn btn-outline-secondary"
                  @click="form.price = priceData.suggested_daily_price"
                >
                  Use Suggestion
                </button>
              </div>
            </div>

            <!-- Guest Info -->
            <div class="form-group mb-3">
              <label for="guest">Guest Name *</label>
              <input
                id="guest"
                v-model="form.guest_name"
                type="text"
                class="form-control"
                required
              />
            </div>

            <!-- ... other fields ... -->

          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">
              Cancel
            </button>
            <button
              type="submit"
              class="btn btn-primary"
              :disabled="priceLoading || !form.apartment_id || !form.check_in || !form.check_out"
            >
              Create Booking
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { usePriceCalculator } from '@/composables/usePriceCalculator'

const {
  loading: priceLoading,
  error: priceError,
  priceData,
  calculateBookingPrice,
  suggestedDailyPriceFormatted,
  currentDailyPriceFormatted,
  priceDifferenceFormatted,
  priceDifferenceClass,
} = usePriceCalculator()

const apartments = ref([])
const form = ref({
  apartment_id: null,
  check_in: '',
  check_out: '',
  price: null,
  guest_name: '',
  // ... other fields
})

// Calculate price when apartment or dates change
watch(
  () => [form.value.apartment_id, form.value.check_in, form.value.check_out],
  async ([aptId, checkIn, checkOut]) => {
    if (aptId && checkIn && checkOut) {
      await calculateBookingPrice(aptId, checkIn, checkOut)
    }
  }
)

const submitForm = async () => {
  // Submit form logic
}

const closeModal = () => {
  // Close modal logic
}

const formatVnd = (value) => {
  return new Intl.NumberFormat('vi-VN').format(value)
}
</script>

<style scoped>
.price-suggestion-card {
  margin: 20px 0;
}
</style>
```

## What the User Sees

When booking form is filled:

```
┌─────────────────────────────────────┐
│ Apartment Selection ▼               │ ← User selects apartment
│ Check-in: 2026-09-05                │ ← User picks dates
│ Check-out: 2026-09-10               │
├─────────────────────────────────────┤
│ 💰 Matrix Price Suggestion          │ ← Auto-calculated from matrix
│                                     │
│ Building: Azure Towers              │
│ District: District 1                │
│ Type: 2BR+2WC                       │
│ Nights: 5                           │
│                                     │
│ Suggested Daily: 2,150,000 ₫        │ ← From PM_BASE × index × factor
│ Current Daily: 2,000,000 ₫          │ ← From database
│ Difference: +150,000 ₫ (+7.5%)      │ ← Comparison
│                                     │
│ Room Total: 10,750,000 ₫            │
│ Subtotal: 10,950,000 ₫              │
│ [Use Suggestion] button             │ ← Click to use matrix price
├─────────────────────────────────────┤
│ Nightly Rate (VND): [2150000] ₫     │ ← Pre-filled or user can change
│ Guest Name: ________________        │
│ ...                                 │
├─────────────────────────────────────┤
│ [Cancel] [Create Booking]           │
└─────────────────────────────────────┘
```

## API Response Example

```json
{
  "success": true,
  "apartment_id": 123,
  "apartment_name": "Ben Van Don - City View Apartment",
  "building_name": "Azure Towers",
  "district": "District 1",
  "check_in": "2026-09-05",
  "check_out": "2026-09-10",
  "nights": 5,
  "suggested_daily_price": 2150000,
  "suggested_room_total": 10750000,
  "suggested_total_before_fees": 10950000,
  "suggested_total": 11497500,
  "current_daily_price": 2000000,
  "current_room_total": 10000000,
  "current_total": 10525000,
  "cleaning_fee": 200000,
  "booking_fee": 547500,
  "price_difference": 150000,
  "price_difference_percent": 7.5,
  "matrix": {
    "base_price_vnd": 1900000,
    "district_index": 1.15,
    "building_factor": 0.98
  }
}
```

## Features

✅ **Real-time calculation** - Updates as user types
✅ **Price comparison** - Shows suggested vs current
✅ **Percentage difference** - Easy to see impact
✅ **One-click apply** - "Use Suggestion" button
✅ **Full transparency** - Shows formula breakdown
✅ **Error handling** - Graceful fallback if calculation fails
✅ **Loading state** - Shows spinner while calculating
✅ **Formatted display** - Vietnamese number format

## Next Steps

1. Find your `resources/js/components/modals/AddBookingModal.vue`
2. Add the imports and watchers from Step 1-2 above
3. Add the price suggestion card HTML from Step 3
4. Test by opening booking modal and selecting apartment + dates
5. Price should auto-calculate and display!

**Need help?** Let me know which Vue framework version you're using (Vue 2 or 3 with Composition API vs Options API).
