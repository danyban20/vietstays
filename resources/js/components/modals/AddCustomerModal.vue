<template>
    <HostModalShell :open="open" title="New customer" @close="emit('close')">
        <p class="host-cust-modal-subtitle">Only name is required — the rest can be filled in later.</p>

        <div class="host-form-grid">
            <div class="host-field host-field--full">
                <label class="host-field__label" for="cust-name">Name *</label>
                <input id="cust-name" v-model="form.name" type="text" class="host-input" placeholder="e.g. Kari Nordmann" />
            </div>
            <div class="host-field">
                <label class="host-field__label" for="cust-email">Email (optional)</label>
                <input id="cust-email" v-model="form.email" type="email" class="host-input" placeholder="can be left blank" />
            </div>
            <div class="host-field">
                <label class="host-field__label" for="cust-phone">Phone (optional)</label>
                <input id="cust-phone" v-model="form.phone" type="tel" class="host-input" placeholder="can be left blank" />
            </div>
            <div class="host-field host-field--full">
                <label class="host-field__label" for="cust-country">Country (optional)</label>
                <input id="cust-country" v-model="form.country" type="text" class="host-input" placeholder="e.g. Vietnam" />
            </div>
            <div class="host-field host-field--full">
                <label class="host-field__label" for="cust-note">Internal note (optional)</label>
                <input id="cust-note" v-model="form.note" type="text" class="host-input" placeholder="e.g. called in, wants a 2BR in D1" />
            </div>
        </div>

        <div v-if="!form.email" class="host-cust-temp-notice">
            Without an email, the customer is created as a Temporary account.
            The account can be booked now and transferred to the customer's own account later.
        </div>

        <label class="host-check-inline host-cust-reserve-toggle">
            <input v-model="form.goToBooking" type="checkbox" />
            Go straight to a new booking after creating
        </label>

        <p v-if="error" class="host-form-error">{{ error }}</p>

        <template #footer>
            <button type="button" class="host-btn host-btn--ghost" @click="emit('close')">Cancel</button>
            <button
                type="button"
                class="host-btn host-btn--accent"
                :disabled="saving || !form.name.trim()"
                @click="submit"
            >
                {{ saving ? 'Saving…' : (form.goToBooking ? 'Create customer and book' : 'Add customer') }}
            </button>
        </template>
    </HostModalShell>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/api/client';
import HostModalShell from '@/components/modals/HostModalShell.vue';
import { useToast } from '@/composables/useToast';

const props = defineProps({
    open: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'saved']);

const router = useRouter();
const toast = useToast();
const saving = ref(false);
const error = ref('');

const form = reactive({
    name: '',
    email: '',
    phone: '',
    country: '',
    note: '',
    goToBooking: true,
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            error.value = '';
            saving.value = false;
            Object.assign(form, { name: '', email: '', phone: '', country: '', note: '', goToBooking: true });
        }
    },
);

async function submit() {
    saving.value = true;
    error.value = '';

    try {
        const res = await apiClient.post('/customers', {
            name: form.name,
            email: form.email || null,
            phone: form.phone || null,
            country: form.country || null,
            note: form.note || null,
        });

        toast.show(res?.message ?? 'Customer added.');
        emit('saved', res?.data);
        emit('close');

        if (form.goToBooking) {
            router.push({ name: 'bookings', query: { add: 'manual', guest_name: form.name } });
        }
    } catch (err) {
        error.value = err.payload?.message ?? err.message ?? 'Could not save.';
    } finally {
        saving.value = false;
    }
}
</script>
