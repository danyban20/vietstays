<template>
    <MemberModal :open="open" title="Cancel Reservation" @close="$emit('close')">
        <div class="terms_text_popup">
            <strong>Please review cancellation terms</strong>
            <template v-if="quote.late_percent > 0">
                Free cancellation until {{ quote.free_until_label }}. After this date, {{ quote.late_percent }}% of the total booking amount is charged.
            </template>
            <template v-else>Cancelling is free of charge until check-in.</template>
        </div>
        <div class="booking_cost">
            <ul>
                <li><span>Total Booking Cost</span><span>{{ formatMoney(quote.total, currency) }}</span></li>
                <li>
                    <span>Cancellation Charge{{ quote.is_late ? ` (${quote.late_percent}%)` : '' }}</span>
                    <span :class="{ red_text: quote.charge > 0 }">{{ quote.charge > 0 ? '−' : '' }}{{ formatMoney(quote.charge, currency) }}</span>
                </li>
                <li v-if="quote.pays_on_site"><span>You pay</span><span>{{ formatMoney(quote.charge, currency) }}</span></li>
                <li v-else><span>Estimated Refund</span><span>{{ formatMoney(quote.refund, currency) }}</span></li>
            </ul>
        </div>
        <p v-if="quote.pays_on_site">You have not been charged for this booking, so there is nothing to refund.</p>
        <p v-else>The refund goes back to your original payment method within 5–10 business days.</p>
        <div v-if="error" class="member-form-error">{{ error }}</div>
        <div class="chk_btn">
            <input id="cancel-accept" v-model="accepted" type="checkbox" />
            <span>I understand the cancellation policy</span>
        </div>
        <p v-if="!accepted" class="enable_text">Select the checkbox to enable cancellation.</p>
        <div class="btn_wrap">
            <a href="#" class="btn border_btn" @click.prevent="$emit('close')">Keep reservation</a>
            <button type="button" class="btn cancel_btn" :class="{ is_enabled: accepted }" :disabled="!accepted || saving" @click="submit">
                {{ saving ? 'Cancelling…' : 'Cancel reservation' }}
            </button>
        </div>
    </MemberModal>
</template>

<script setup>
import { ref, watch } from 'vue';
import apiClient from '@/api/client';
import MemberModal from '@/components/member/MemberModal.vue';
import { formatMoney } from '@/utils/member-format';

const props = defineProps({
    open: { type: Boolean, default: false },
    bookingId: { type: [Number, String], required: true },
    quote: { type: Object, required: true },
    currency: { type: String, default: 'VND' },
});

const emit = defineEmits(['close', 'cancelled']);

const accepted = ref(false);
const saving = ref(false);
const error = ref('');

async function submit() {
    saving.value = true;
    error.value = '';

    try {
        const res = await apiClient.post(`/member/reservations/${props.bookingId}/cancel`, { accept_policy: true });
        emit('cancelled', res.data, res.message);
    } catch (err) {
        error.value = err.message || 'Could not cancel the reservation.';
    } finally {
        saving.value = false;
    }
}

watch(
    () => props.open,
    (open) => {
        if (open) {
            accepted.value = false;
            error.value = '';
        }
    },
);
</script>
