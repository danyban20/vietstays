<template>
    <div id="booking_confirmation">
        <div class="container">
            <div class="booking_confirmation_inn">
                <div v-if="confirmed" class="booking-checkout-success">
                    <h2>Booking submitted</h2>
                    <p>Thank you, {{ form.guest_name }}. Your reservation request has been received.</p>
                    <p v-if="confirmedBooking?.booking_num">
                        Reference: <strong>{{ confirmedBooking.booking_num }}</strong>
                    </p>
                    <p class="booking-checkout-success__note">
                        We will email you at {{ form.email }} with confirmation details. Payment is due on arrival
                        unless you chose to pay by card (card payments are not yet available online).
                    </p>
                    <router-link :to="{ name: 'home' }" class="btn">Back to home</router-link>
                </div>

                <template v-else>
                    <h2>
                        <router-link
                            v-if="apartmentId"
                            :to="{ name: 'public-apartment', params: { id: apartmentId }, hash: '#book-now' }"
                            class="back_arr"
                        />
                        Booking confirmation
                    </h2>

                    <div v-if="pageError" class="alert alert-danger mb-3" role="alert">{{ pageError }}</div>

                    <div v-if="loading" class="host-apartment-loading">Loading your booking…</div>

                    <form v-else-if="apartment.id" id="formBooking" @submit.prevent="submitBooking">
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="booking_conf_left">
                                    <div class="order_details">
                                        <h4>1 - Your booking</h4>
                                        <ul>
                                            <li>
                                                Dates
                                                <strong>{{ dateSummary }}</strong>
                                                <router-link
                                                    :to="{
                                                        name: 'public-apartment',
                                                        params: { id: apartmentId },
                                                        hash: '#book-now',
                                                    }"
                                                    class="change_btn"
                                                >
                                                    Change
                                                </router-link>
                                            </li>
                                            <li>
                                                Duration
                                                <strong>{{ nightsLabel }}</strong>
                                            </li>
                                            <li>
                                                Guests
                                                <strong>{{ guestsSummary }}</strong>
                                                <router-link
                                                    :to="{
                                                        name: 'public-apartment',
                                                        params: { id: apartmentId },
                                                        hash: '#book-now',
                                                    }"
                                                    class="change_btn"
                                                >
                                                    Change
                                                </router-link>
                                            </li>
                                        </ul>
                                    </div>
                                    <hr />

                                    <div v-if="showExtras" class="stay_block_wrap">
                                        <h4>2 - Add services to your stay</h4>
                                        <div class="stay_block_inner">
                                            <div v-if="Number(apartment.cleaning_fee) > 0" class="stay_block">
                                                <h5>+ Extra cleaning service</h5>
                                                <p>
                                                    Keep your apartment fresh during your stay by booking additional
                                                    cleaning.
                                                </p>
                                                <select v-model.number="form.num_cleaning" @change="refreshQuote">
                                                    <option :value="0">NO</option>
                                                    <option
                                                        v-for="n in quote?.nights || 1"
                                                        :key="n"
                                                        :value="n"
                                                    >
                                                        {{ n }} {{ n === 1 ? 'time' : 'times' }}
                                                    </option>
                                                </select>
                                            </div>
                                            <div v-if="apartment.airport_pickup" class="stay_block">
                                                <h5>+ Airport pick-up service</h5>
                                                <p>Start your trip stress-free with a convenient airport pick-up.</p>
                                                <div class="switch_btn">
                                                    <input
                                                        id="airport_pickup"
                                                        v-model="form.airport_pickup"
                                                        type="checkbox"
                                                        true-value="1"
                                                        false-value="0"
                                                        @change="refreshQuote"
                                                    />
                                                    <label for="airport_pickup">
                                                        <span class="yes_text">YES</span>
                                                        <span class="no_text">NO</span>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr v-if="showExtras" />

                                    <div class="payment_method">
                                        <h4>{{ showExtras ? '3' : '2' }} - Choose how you want to pay</h4>
                                        <div class="payment_method_list">
                                            <div class="block">
                                                <h5>Pay by card now</h5>
                                                <p>
                                                    Pay the full amount ({{ formatVnd(quote?.total || 0) }}) now — coming
                                                    soon
                                                </p>
                                                <input
                                                    v-model="form.payment_method"
                                                    type="radio"
                                                    value="card"
                                                    disabled
                                                />
                                                <span class="circle_btn" />
                                                <span class="border_btn" />
                                            </div>
                                            <div class="block">
                                                <h5>Pay when you arrive</h5>
                                                <p>
                                                    Reserve your order and pay the full amount ({{
                                                        formatVnd(quote?.total || 0)
                                                    }}) when you arrive
                                                </p>
                                                <input
                                                    v-model="form.payment_method"
                                                    type="radio"
                                                    value="onsite"
                                                    @change="refreshQuote"
                                                />
                                                <span class="circle_btn" />
                                                <span class="border_btn" />
                                            </div>
                                        </div>
                                    </div>
                                    <hr />

                                    <div class="login_reg_order">
                                        <h4>{{ showExtras ? '4' : '3' }} - Your details</h4>
                                        <input
                                            v-model="form.guest_name"
                                            type="text"
                                            name="guest_name"
                                            placeholder="Full name"
                                            class="form-input"
                                            required
                                        />
                                        <input
                                            v-model="form.email"
                                            type="email"
                                            name="email"
                                            placeholder="Email"
                                            class="form-input"
                                            required
                                        />
                                        <input
                                            v-model="form.phone"
                                            type="tel"
                                            name="phone"
                                            placeholder="Phone (optional)"
                                            class="form-input"
                                        />
                                        <button type="submit" class="btnCheckUser" :disabled="submitting">
                                            {{ submitting ? 'Submitting…' : 'Confirm booking' }}
                                        </button>
                                        <p>We will send booking confirmation to your email.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-5">
                                <div class="booking_conf_right">
                                    <div class="your_order_block">
                                        <div class="order_list">
                                            <div class="block">
                                                <div v-if="apartmentImage" class="img">
                                                    <img :src="apartmentImage" :alt="apartment.name" />
                                                </div>
                                                <div class="desc">
                                                    <h6>Apartment</h6>
                                                    <h5>{{ apartment.name }}</h5>
                                                </div>
                                            </div>
                                        </div>
                                        <hr />
                                        <div class="order_total">
                                            <h4>Your order</h4>
                                            <ul>
                                                <li v-for="(line, index) in orderLines" :key="index">
                                                    <span>{{ line.label }}</span>
                                                    <span class="valtxt">{{ line.value }}</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div v-else class="host-apartment-empty">
                        <p>Missing booking details. Please select dates on the apartment page first.</p>
                        <router-link :to="{ name: 'public-apartments' }">Browse apartments</router-link>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import apiClient from '@/api/client';
import { usePublicLegacyStyles } from '@/composables/usePublicLegacyStyles';
import { formatDate, formatVnd, nightsBetween } from '@/utils/format';

const route = useRoute();
const { mount, unmount } = usePublicLegacyStyles('booking');

const loading = ref(true);
const submitting = ref(false);
const pageError = ref('');
const confirmed = ref(false);
const confirmedBooking = ref(null);
const apartment = ref({});
const quote = ref(null);

const apartmentId = computed(() => route.params.apartmentId);

const form = reactive({
    guest_name: '',
    email: '',
    phone: '',
    check_in: '',
    check_out: '',
    adults: 2,
    children: 0,
    num_cleaning: 0,
    airport_pickup: '0',
    payment_method: 'onsite',
});

const nights = computed(() => nightsBetween(form.check_in, form.check_out));

const dateSummary = computed(() => {
    if (!form.check_in || !form.check_out) {
        return '—';
    }
    return `${formatDate(form.check_in)} – ${formatDate(form.check_out)}`;
});

const nightsLabel = computed(() => {
    const n = nights.value;
    return n === 1 ? '1 night' : `${n} nights`;
});

const guestsSummary = computed(() => {
    const total = Number(form.adults) + Number(form.children);
    return total === 1 ? '1 guest' : `${total} guests`;
});

const apartmentImage = computed(() => {
    const images = apartment.value.images ?? [];
    const first = images.find((image) => image.thumb || image.full);
    return first?.thumb || first?.full || apartment.value.image || null;
});

const showExtras = computed(
    () => Number(apartment.value.cleaning_fee) > 0 || apartment.value.airport_pickup,
);

const orderLines = computed(() => {
    if (!quote.value) {
        return [{ label: 'Calculating…', value: '—' }];
    }

    const lines = [];

    if (quote.value.rate_lines?.length) {
        quote.value.rate_lines.forEach((line) => {
            lines.push({
                label: `${formatVnd(line.price)} × ${line.nights} night${line.nights === 1 ? '' : 's'}`,
                value: formatVnd(line.price * line.nights),
            });
        });
    } else {
        lines.push({
            label: quote.value.label || 'Accommodation',
            value: formatVnd(quote.value.room_total || 0),
        });
    }

    if (Number(quote.value.campaign_discount) > 0) {
        lines.push({
            label: 'Campaign discount',
            value: `- ${formatVnd(quote.value.campaign_discount)}`,
        });
    }

    if (Number(quote.value.basic_discount_amount) > 0) {
        lines.push({
            label: `Extended stay discount (${quote.value.basic_discount_percent}%)`,
            value: `- ${formatVnd(quote.value.basic_discount_amount)}`,
        });
    }

    if (Number(quote.value.booking_fee) > 0) {
        lines.push({
            label: `Booking fee (${quote.value.booking_fee_percent}%)`,
            value: formatVnd(quote.value.booking_fee),
        });
    }

    if (Number(quote.value.cleaning_fee) > 0) {
        lines.push({
            label: 'Cleaning fee',
            value: formatVnd(quote.value.cleaning_fee),
        });
    }

    if (Number(quote.value.extra_cleaning_total) > 0) {
        lines.push({
            label: 'Extra cleaning',
            value: formatVnd(quote.value.extra_cleaning_total),
        });
    }

    if (Number(quote.value.airport_pickup_cost) > 0) {
        lines.push({
            label: 'Airport pick-up',
            value: formatVnd(quote.value.airport_pickup_cost),
        });
    }

    lines.push({
        label: 'Total',
        value: formatVnd(quote.value.total || 0),
    });

    return lines;
});

function readQueryParams() {
    form.check_in = String(route.query.check_in || '');
    form.check_out = String(route.query.check_out || '');
    form.adults = Math.max(1, Number(route.query.adults || 2));
    form.children = Math.max(0, Number(route.query.children || 0));
}

async function loadApartment() {
    if (!apartmentId.value) {
        apartment.value = {};
        return;
    }

    const res = await apiClient.get(`/public/apartments/${apartmentId.value}`);
    apartment.value = res?.data ?? {};
}

async function refreshQuote() {
    pageError.value = '';

    if (!form.check_in || !form.check_out || !apartmentId.value) {
        quote.value = null;
        return;
    }

    try {
        const res = await apiClient.post('/public/bookings/quote', {
            apartment_id: Number(apartmentId.value),
            check_in_date: form.check_in,
            check_out_date: form.check_out,
            adults: form.adults,
            children: form.children,
            num_cleaning: form.num_cleaning,
            airport_pickup: form.airport_pickup === '1' || form.airport_pickup === true,
            payment_method: form.payment_method,
        });
        quote.value = res?.data ?? null;
    } catch (err) {
        quote.value = null;
        pageError.value = err.message || 'Could not calculate price for these dates.';
    }
}

async function bootstrap() {
    loading.value = true;
    pageError.value = '';
    readQueryParams();

    if (!apartmentId.value || !form.check_in || !form.check_out) {
        loading.value = false;
        return;
    }

    try {
        await loadApartment();
        await refreshQuote();
    } catch (err) {
        pageError.value = err.message || 'Could not load booking details.';
    } finally {
        loading.value = false;
    }
}

async function submitBooking() {
    pageError.value = '';
    submitting.value = true;

    try {
        const res = await apiClient.post('/public/bookings', {
            apartment_id: Number(apartmentId.value),
            check_in_date: form.check_in,
            check_out_date: form.check_out,
            guest_name: form.guest_name,
            email: form.email,
            phone: form.phone || null,
            adults: form.adults,
            children: form.children,
            num_cleaning: form.num_cleaning,
            airport_pickup: form.airport_pickup === '1' || form.airport_pickup === true,
            payment_method: form.payment_method,
        });

        confirmedBooking.value = res?.data ?? null;
        confirmed.value = true;
    } catch (err) {
        pageError.value = err.message || 'Could not submit your booking.';
    } finally {
        submitting.value = false;
    }
}

onMounted(async () => {
    mount();
    await bootstrap();
});

onUnmounted(() => {
    unmount();
});

watch(
    () => route.fullPath,
    async () => {
        if (!confirmed.value) {
            await bootstrap();
        }
    },
);
</script>

<style scoped>
.booking-checkout-success {
    max-width: 640px;
    padding: 40px 0;
}

.booking-checkout-success__note {
    margin: 16px 0 24px;
    color: #333;
}

.alert-danger {
    background: #fdecea;
    border: 1px solid #f5c2c0;
    color: #842029;
    padding: 12px 16px;
    border-radius: 6px;
}
</style>
