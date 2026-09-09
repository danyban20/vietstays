<template>
    <HostModalShell :open="open" :title="modalTitle" @close="emit('close')">
        <div v-if="variant === 'manual'" class="host-form-grid">
            <div class="host-field host-field--full">
                <label class="host-field__label" for="bk-apartment">Apartment *</label>
                <select id="bk-apartment" v-model="form.apartment_id" class="host-select" @change="onApartmentChange">
                    <option value="">Select apartment</option>
                    <option v-for="a in apartments" :key="a.id" :value="a.id">{{ a.name }}</option>
                </select>
            </div>
            <div class="host-field host-field--full">
                <label class="host-field__label" for="bk-guest">Guest name *</label>
                <input id="bk-guest" v-model="form.guest_name" type="text" class="host-input" />
            </div>
            <div class="host-field">
                <label class="host-field__label" for="bk-email">Email</label>
                <input id="bk-email" v-model="form.email" type="email" class="host-input" />
            </div>
            <div class="host-field">
                <label class="host-field__label" for="bk-phone">Phone</label>
                <input id="bk-phone" v-model="form.phone" type="tel" class="host-input" />
            </div>
            <div class="host-field">
                <label class="host-field__label" for="bk-guests">Guests</label>
                <input id="bk-guests" v-model.number="form.guests" type="number" min="1" class="host-input" />
            </div>
            <div class="host-field">
                <label class="host-field__label" for="bk-rate">Nightly rate</label>
                <input id="bk-rate" v-model.number="form.daily_price" type="number" min="0" class="host-input" />
            </div>
            <DateRangeCalendar
                :apartment-id="form.apartment_id"
                v-model:check-in="form.check_in_date"
                v-model:check-out="form.check_out_date"
            />

            <div class="host-field host-field--full">
                <label class="host-check-inline">
                    <input v-model="form.discount_enabled" type="checkbox" />
                    Apply discount
                </label>
            </div>

            <template v-if="form.discount_enabled">
                <div class="host-field">
                    <label class="host-field__label" for="bk-disc-type">Discount type</label>
                    <select id="bk-disc-type" v-model="form.discount_type" class="host-select">
                        <option value="percentage">Percentage</option>
                        <option value="ambassador">Ambassador code</option>
                        <option value="host_agent">Host Agent</option>
                    </select>
                </div>
                <div class="host-field">
                    <label class="host-field__label" for="bk-disc-val">
                        {{ form.discount_type === 'percentage' ? 'Discount %' : 'Amount (VND)' }}
                    </label>
                    <input id="bk-disc-val" v-model.number="form.discount_value" type="number" min="0" class="host-input" />
                </div>
                <div v-if="form.discount_type === 'ambassador'" class="host-field host-field--full">
                    <label class="host-field__label" for="bk-code">Ambassador code</label>
                    <input id="bk-code" v-model="form.discount_code" type="text" class="host-input" />
                </div>
            </template>

            <div class="host-price-summary host-field--full">
                <div class="host-price-summary__row">
                    <span>Room total ({{ nights }} nights)</span>
                    <span>{{ formatVnd(summary.roomTotal) }}</span>
                </div>
                <div v-if="form.discount_enabled && summaryDiscount > 0" class="host-price-summary__row">
                    <span>Discount</span>
                    <span>− {{ formatVnd(summaryDiscount) }}</span>
                </div>
                <div class="host-price-summary__row host-price-summary__row--total">
                    <span>Total guest pays</span>
                    <span>{{ formatVnd(summary.guestTotal) }}</span>
                </div>
                <p class="host-price-summary__note">A 5% platform fee applies to Vietstays direct bookings.</p>
            </div>
        </div>

        <div v-else-if="variant === 'block'" class="host-form-grid">
            <div class="host-field">
                <label class="host-field__label" for="blk-apartment">Apartment *</label>
                <select id="blk-apartment" v-model="form.apartment_id" class="host-select">
                    <option value="">Select apartment</option>
                    <option v-for="a in apartments" :key="a.id" :value="a.id">{{ a.name }}</option>
                </select>
            </div>
            <div class="host-field">
                <label class="host-field__label" for="blk-note">Reason</label>
                <input id="blk-note" v-model="form.note" type="text" class="host-input" placeholder="Maintenance, personal use…" />
            </div>
            <DateRangeCalendar
                :apartment-id="form.apartment_id"
                v-model:check-in="form.start_date"
                v-model:check-out="form.end_date"
            />
        </div>

        <div v-else class="host-form-grid">
            <div class="host-field host-field--full">
                <label class="host-field__label" for="ext-apartment">Apartment *</label>
                <select id="ext-apartment" v-model="form.apartment_id" class="host-select">
                    <option value="">Select apartment</option>
                    <option v-for="a in apartments" :key="a.id" :value="a.id">{{ a.name }}</option>
                </select>
            </div>
            <div class="host-field">
                <label class="host-field__label" for="ext-platform">Source</label>
                <select id="ext-platform" v-model="form.external_platform" class="host-select">
                    <option value="Airbnb">Airbnb</option>
                    <option value="Booking.com">Booking.com</option>
                    <option value="Trip.com">Trip.com</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="host-field">
                <label class="host-field__label" for="ext-ref">Reference</label>
                <input id="ext-ref" v-model="form.reference" type="text" class="host-input" />
            </div>
            <div class="host-field host-field--full">
                <label class="host-field__label" for="ext-guest">Guest name</label>
                <input id="ext-guest" v-model="form.guest_name" type="text" class="host-input" />
            </div>
            <div class="host-field host-field--full">
                <label class="host-field__label host-field__label--important" for="ext-email">Email</label>
                <input id="ext-email" v-model="form.email" type="email" class="host-input" />
            </div>
            <DateRangeCalendar
                :apartment-id="form.apartment_id"
                v-model:check-in="form.check_in_date"
                v-model:check-out="form.check_out_date"
            />
            <div class="host-info-box host-field--full">
                Register external guests with email so you can invite them to book directly on Vietstays later.
            </div>
        </div>

        <p v-if="error" class="host-form-error">{{ error }}</p>

        <template #footer>
            <button type="button" class="host-btn host-btn--ghost" @click="emit('close')">Cancel</button>
            <button
                type="button"
                class="host-btn host-btn--accent"
                :disabled="saving || !canSubmit"
                @click="submit"
            >
                {{ saving ? 'Saving…' : submitLabel }}
            </button>
        </template>
    </HostModalShell>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/api/client';
import DateRangeCalendar from '@/components/bookings/DateRangeCalendar.vue';
import HostModalShell from '@/components/modals/HostModalShell.vue';
import { useToast } from '@/composables/useToast';
import { formatVnd, nightsBetween } from '@/utils/format';
import { bookingSummary } from '@/utils/pricing';

const props = defineProps({
    open: { type: Boolean, default: false },
    variant: { type: String, default: 'manual' },
    prefill: { type: Object, default: () => ({}) },
    navigateAfterCreate: { type: Boolean, default: true },
});

const emit = defineEmits(['close', 'saved']);

const router = useRouter();
const toast = useToast();

const apartments = ref([]);
const saving = ref(false);
const error = ref('');

const form = reactive({
    apartment_id: '',
    guest_name: '',
    email: '',
    phone: '',
    guests: 2,
    daily_price: 0,
    check_in_date: '',
    check_out_date: '',
    start_date: '',
    end_date: '',
    note: '',
    external_platform: 'Airbnb',
    reference: '',
    discount_enabled: false,
    discount_type: 'percentage',
    discount_value: 0,
    discount_code: '',
});

const modalTitle = computed(() => ({
    manual: 'Add booking',
    block: 'Block dates',
    external: 'External booking',
}[props.variant] ?? 'Add booking'));

const submitLabel = computed(() => ({
    manual: 'Create booking',
    block: 'Block dates',
    external: 'Register external booking',
}[props.variant] ?? 'Save'));

const nights = computed(() => nightsBetween(form.check_in_date, form.check_out_date));

const summaryDiscount = computed(() => {
    if (!form.discount_enabled) return 0;
    const subtotal = form.daily_price * nights.value;
    if (form.discount_type === 'percentage') {
        return Math.round(subtotal * (form.discount_value / 100));
    }
    return Math.min(form.discount_value, subtotal);
});

const summary = computed(() =>
    bookingSummary({
        nightlyRate: form.daily_price || 0,
        nights: nights.value || 1,
        discount: summaryDiscount.value,
    }),
);

const canSubmit = computed(() => {
    if (!form.apartment_id) return false;
    if (props.variant === 'block') {
        return form.start_date && form.end_date;
    }
    if (props.variant === 'external') {
        return form.check_in_date && form.check_out_date;
    }
    return form.guest_name.trim() && form.check_in_date && form.check_out_date;
});

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            resetForm();
            loadApartments();
        }
    },
);

async function loadApartments() {
    try {
        const res = await apiClient.get('/locations/filters');
        apartments.value = res?.data?.apartments ?? [];
    } catch {
        const fallback = await apiClient.get('/apartments');
        apartments.value = (fallback?.data ?? []).map((a) => ({ id: a.id, name: a.name, price_daily: a.price_daily }));
    }
}

function onApartmentChange() {
    const apt = apartments.value.find((a) => a.id === Number(form.apartment_id));
    if (apt?.price_daily && !form.daily_price) {
        form.daily_price = apt.price_daily;
    }
}

function resetForm() {
    error.value = '';
    saving.value = false;
    Object.assign(form, {
        apartment_id: '',
        guest_name: '',
        email: '',
        phone: '',
        guests: 2,
        daily_price: 0,
        check_in_date: '',
        check_out_date: '',
        start_date: '',
        end_date: '',
        note: '',
        external_platform: 'Airbnb',
        reference: '',
        discount_enabled: false,
        discount_type: 'percentage',
        discount_value: 0,
        discount_code: '',
    });

    if (props.prefill?.guest_name) {
        form.guest_name = props.prefill.guest_name;
    }

    if (props.prefill?.email) {
        form.email = props.prefill.email;
    }

    if (props.prefill?.phone) {
        form.phone = props.prefill.phone;
    }
}

async function submit() {
    saving.value = true;
    error.value = '';

    try {
        let payload = { type: props.variant, apartment_id: Number(form.apartment_id) };

        if (props.variant === 'manual') {
            payload = {
                ...payload,
                guest_name: form.guest_name,
                email: form.email,
                phone: form.phone,
                guests: form.guests,
                adults: form.guests,
                daily_price: form.daily_price,
                check_in_date: form.check_in_date,
                check_out_date: form.check_out_date,
                discount_enabled: form.discount_enabled,
                discount_type: form.discount_type,
                discount_value: form.discount_value,
                discount_code: form.discount_code,
            };
        } else if (props.variant === 'block') {
            payload = {
                ...payload,
                start_date: form.start_date,
                end_date: form.end_date,
                note: form.note,
            };
        } else {
            payload = {
                ...payload,
                guest_name: form.guest_name,
                email: form.email,
                external_platform: form.external_platform,
                reference: form.reference,
                check_in_date: form.check_in_date,
                check_out_date: form.check_out_date,
            };
        }

        const res = await apiClient.post('/bookings', payload);

        if (props.variant === 'manual' && res?.data?.id) {
            toast.show('Booking created.');
            emit('saved', res.data);
            emit('close');
            if (props.navigateAfterCreate) {
                router.push({ name: 'booking-detail', params: { id: res.data.id } });
            }
        } else {
            toast.show(res?.message ?? 'Saved.');
            emit('saved', res?.data);
            emit('close');
        }
    } catch (err) {
        error.value = err.payload?.message ?? err.message ?? 'Could not save.';
    } finally {
        saving.value = false;
    }
}
</script>
