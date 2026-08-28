<template>
    <HostModalShell :open="open" title="Move booking" @close="emit('close')">
        <p class="host-cal-move__intro">
            Move <strong>{{ draft?.guest }}</strong> to a new apartment or date range.
        </p>

        <div class="host-cal-move__compare">
            <div class="host-cal-move__col">
                <span class="host-cal-move__label">From</span>
                <p class="host-cal-move__apt">{{ draft?.fromApartmentName }}</p>
                <p class="host-cal-move__dates">
                    {{ formatPeriod(draft?.fromCheckIn, draft?.fromCheckOut) }}
                </p>
            </div>
            <span class="host-cal-move__arrow" aria-hidden="true">→</span>
            <div class="host-cal-move__col">
                <span class="host-cal-move__label">To</span>
                <p class="host-cal-move__apt">{{ draft?.toApartmentName }}</p>
                <p class="host-cal-move__dates">
                    {{ formatPeriod(draft?.toCheckIn, draft?.toCheckOut) }}
                </p>
            </div>
        </div>

        <div class="host-field host-field--full">
            <span class="host-field__label">Notify guest</span>
            <div class="host-cal-move__notify">
                <label v-for="opt in notifyOptions" :key="opt.id" class="host-check-inline">
                    <input v-model="notifyGuest" type="radio" :value="opt.id" />
                    {{ opt.label }}
                </label>
            </div>
        </div>

        <p v-if="error" class="host-form-error">{{ error }}</p>

        <template #footer>
            <div class="host-modal__footer-actions">
                <button type="button" class="host-btn host-btn--ghost" @click="emit('close')">Cancel</button>
                <button type="button" class="host-btn host-btn--accent" :disabled="saving" @click="confirm">
                    {{ saving ? 'Moving…' : 'Confirm move' }}
                </button>
            </div>
        </template>
    </HostModalShell>
</template>

<script setup>
import { ref, watch } from 'vue';
import apiClient from '@/api/client';
import HostModalShell from '@/components/modals/HostModalShell.vue';
import { formatPeriod } from '@/utils/format';

const props = defineProps({
    open: { type: Boolean, default: false },
    draft: { type: Object, default: null },
});

const emit = defineEmits(['close', 'moved']);

const notifyOptions = [
    { id: 'none', label: 'Do not notify' },
    { id: 'email', label: 'Email guest' },
    { id: 'sms', label: 'SMS guest' },
];

const notifyGuest = ref('none');
const saving = ref(false);
const error = ref('');

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            notifyGuest.value = 'none';
            error.value = '';
        }
    },
);

async function confirm() {
    if (!props.draft?.bookingId) return;

    saving.value = true;
    error.value = '';

    try {
        const res = await apiClient.post(`/bookings/${props.draft.bookingId}/move`, {
            apartment_id: props.draft.toApartmentId,
            check_in_date: props.draft.toCheckIn,
            check_out_date: props.draft.toCheckOut,
            notify_guest: notifyGuest.value,
        });
        emit('moved', res?.data);
        emit('close');
    } catch (err) {
        error.value = err.message ?? 'Could not move booking.';
    } finally {
        saving.value = false;
    }
}
</script>
