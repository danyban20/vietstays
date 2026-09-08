// resources/js/composables/usePriceCalculator.js

import { ref, computed } from 'vue'
import axios from 'axios'

export function usePriceCalculator() {
  const loading = ref(false)
  const error = ref(null)
  const priceData = ref(null)

  const calculateBookingPrice = async (apartmentId, checkIn, checkOut) => {
    loading.value = true
    error.value = null
    priceData.value = null

    try {
      if (!apartmentId || !checkIn || !checkOut) {
        throw new Error('Missing required parameters: apartment_id, check_in, check_out')
      }

      const response = await axios.post('/api/bookings/calculate-price', {
        apartment_id: apartmentId,
        check_in: checkIn,
        check_out: checkOut,
      })

      priceData.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.error || err.message || 'Failed to calculate price'
      console.error('Price calculation error:', error.value)
      return null
    } finally {
      loading.value = false
    }
  }

  // Formatted display values
  const suggestedDailyPriceFormatted = computed(() => {
    if (!priceData.value) return '-'
    return new Intl.NumberFormat('vi-VN').format(priceData.value.suggested_daily_price)
  })

  const suggestedTotalFormatted = computed(() => {
    if (!priceData.value) return '-'
    return new Intl.NumberFormat('vi-VN').format(priceData.value.suggested_total)
  })

  const currentDailyPriceFormatted = computed(() => {
    if (!priceData.value) return '-'
    return new Intl.NumberFormat('vi-VN').format(priceData.value.current_daily_price)
  })

  const priceDifferenceFormatted = computed(() => {
    if (!priceData.value) return '-'
    const diff = priceData.value.price_difference
    const sign = diff > 0 ? '+' : ''
    return `${sign}${new Intl.NumberFormat('vi-VN').format(diff)}`
  })

  const priceDifferenceClass = computed(() => {
    if (!priceData.value) return ''
    const diff = priceData.value.price_difference
    if (diff > 0) return 'text-success' // Increase
    if (diff < 0) return 'text-danger'  // Decrease
    return 'text-muted'                   // No change
  })

  return {
    loading,
    error,
    priceData,
    calculateBookingPrice,
    suggestedDailyPriceFormatted,
    suggestedTotalFormatted,
    currentDailyPriceFormatted,
    priceDifferenceFormatted,
    priceDifferenceClass,
  }
}
