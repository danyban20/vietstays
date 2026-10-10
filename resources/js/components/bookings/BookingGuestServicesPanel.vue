<template>
    <section v-if="data && hasContent" class="host-bk-detail__rail-card host-guest-services">
        <h3 class="host-bk-detail__rail-title">From the guest’s account</h3>

        <p v-if="data.cancelled_by_guest" class="host-guest-services__alert">
            The guest cancelled this booking themselves{{ data.cancelled_at ? ` on ${formatDate(data.cancelled_at)}` : '' }}.
        </p>

        <div class="host-guest-services__block">
            <div class="host-guest-services__head">
                <span class="host-bk-detail__rail-label">Guest passports</span>
                <span class="host-guest-services__count">{{ data.passports.registered }} / {{ data.passports.expected }}</span>
            </div>
            <p v-if="!data.passports.guests.length" class="host-guest-services__empty">
                {{ data.member_account ? 'The guest has not added passport details yet.' : 'This guest has no Vietstays account, so no passport details.' }}
            </p>
            <ul v-else class="host-guest-services__list">
                <li v-for="guest in data.passports.guests" :key="guest.id">
                    <strong>{{ guest.position }}. {{ guest.full_name }}</strong>
                    <span>{{ countryName(guest.nationality) }} · Passport {{ guest.passport_number }}</span>
                    <span>Born {{ guest.date_of_birth }} · Expires {{ guest.passport_expiry }}</span>
                    <button v-if="guest.has_photo" type="button" class="host-guest-services__link" @click="downloadPhoto(guest)">
                        Download passport photo
                    </button>
                </li>
            </ul>
        </div>

        <div v-if="data.service_requests.length" class="host-guest-services__block">
            <span class="host-bk-detail__rail-label">Extra cleaning</span>
            <ul class="host-guest-services__list">
                <li v-for="service in data.service_requests" :key="service.id">
                    <strong>{{ service.date_label }} · {{ service.time_label }}</strong>
                    <span>{{ formatVnd(service.price) }} · <em :class="`is-${service.status}`">{{ statusLabel(service.status) }}</em></span>
                    <span v-if="service.status === 'requested'" class="host-guest-services__actions">
                        <button type="button" class="host-btn host-btn--primary" :disabled="busy === service.id" @click="decide(service, 'confirmed')">
                            Confirm
                        </button>
                        <button type="button" class="host-btn host-btn--ghost" :disabled="busy === service.id" @click="decide(service, 'declined')">
                            Decline
                        </button>
                    </span>
                </li>
            </ul>
        </div>

        <div v-if="data.review" class="host-guest-services__block">
            <span class="host-bk-detail__rail-label">Guest review</span>
            <p class="host-guest-services__stars" :aria-label="`${data.review.rating} out of 5`">
                {{ '★'.repeat(data.review.rating) }}<span>{{ '★'.repeat(5 - data.review.rating) }}</span>
            </p>
            <p v-if="data.review.categories.length" class="host-guest-services__cats">
                <span v-for="item in data.review.categories" :key="item.label">{{ item.label }} {{ item.rating }}/5</span>
            </p>
            <p v-if="data.review.comment" class="host-guest-services__comment">“{{ data.review.comment }}”</p>
        </div>
    </section>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import apiClient, { downloadFile } from '@/api/client';
import { useToast } from '@/composables/useToast';
import { countryName } from '@/data/countries';
import { formatVnd } from '@/utils/format';

const props = defineProps({
    bookingId: { type: [String, Number], required: true },
});

const toast = useToast();
const data = ref(null);
const busy = ref(null);

const hasContent = computed(
    () =>
        data.value.member_account ||
        data.value.passports.guests.length ||
        data.value.service_requests.length ||
        data.value.review ||
        data.value.cancelled_by_guest,
);

function statusLabel(status) {
    return { requested: 'Waiting for you', confirmed: 'Confirmed', declined: 'Declined', cancelled: 'Withdrawn by guest' }[status] ?? status;
}

function formatDate(iso) {
    return new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
}

async function load() {
    try {
        const res = await apiClient.get(`/bookings/${props.bookingId}/guest-services`);
        data.value = res.data;
    } catch {
        data.value = null;
    }
}

async function decide(service, status) {
    busy.value = service.id;

    try {
        const res = await apiClient.patch(`/bookings/${props.bookingId}/service-requests/${service.id}`, { status });
        Object.assign(service, res.data);
        toast.show(res.message ?? 'Saved.');
    } catch (err) {
        toast.show(err.message ?? 'Could not update the request.');
    } finally {
        busy.value = null;
    }
}

async function downloadPhoto(guest) {
    try {
        await downloadFile(`/bookings/${props.bookingId}/guests/${guest.id}/photo`, `passport-${guest.position}`);
    } catch (err) {
        toast.show(err.message ?? 'Could not download the photo.');
    }
}

watch(() => props.bookingId, load);
onMounted(load);
</script>
