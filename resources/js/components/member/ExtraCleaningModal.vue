<template>
    <MemberModal :open="open" title="Order Extra Cleaning" @close="$emit('close')">
        <form novalidate @submit.prevent="submit">
            <div v-if="error" class="member-form-error">{{ error }}</div>
            <label for="xc-date">Select Date</label>
            <input
                id="xc-date"
                v-model="date"
                type="date"
                class="input_date"
                :min="options.earliest_date"
                :max="options.latest_date"
                required
            />
            <label>Preferred Time Slot</label>
            <div class="row">
                <div v-for="slot in options.slots" :key="slot.key" class="col-sm-6">
                    <div class="radio_btn">
                        <input v-model="timeSlot" type="radio" name="xc-slot" :value="slot.key" :aria-label="slot.label" />
                        <div class="red_text"><strong>{{ slot.label }}</strong>{{ slot.hours }}</div>
                    </div>
                </div>
            </div>
            <div class="sesion_price">
                <ul>
                    <li><span>Session Price</span><span>{{ formatMoney(options.price, currency) }}</span></li>
                    <li><span>Order Total</span><span>{{ formatMoney(options.price, currency) }}</span></li>
                </ul>
            </div>
            <div class="info_text">
                Same-day cleaning must be ordered before {{ options.cutoff_label }}. Your host confirms the request; you pay on site.
            </div>
            <div class="btn_wrap">
                <a href="#" class="btn border_btn" @click.prevent="$emit('close')">Cancel</a>
                <button type="submit" class="btn" :disabled="saving || !date">{{ saving ? 'Sending…' : 'Confirm order' }}</button>
            </div>
        </form>
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
    options: { type: Object, required: true },
    currency: { type: String, default: 'VND' },
});

const emit = defineEmits(['close', 'ordered']);

const date = ref('');
const timeSlot = ref('morning');
const saving = ref(false);
const error = ref('');

async function submit() {
    saving.value = true;
    error.value = '';

    try {
        const res = await apiClient.post(`/member/reservations/${props.bookingId}/services`, {
            service_date: date.value,
            time_slot: timeSlot.value,
        });
        emit('ordered', res.message);
    } catch (err) {
        error.value = err.message || 'Could not order the cleaning.';
    } finally {
        saving.value = false;
    }
}

watch(
    () => props.open,
    (open) => {
        if (open) {
            date.value = props.options.earliest_date ?? '';
            timeSlot.value = props.options.slots?.[0]?.key ?? 'morning';
            error.value = '';
        }
    },
);
</script>
