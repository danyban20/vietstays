<template>
    <div v-if="loading" class="host-customers-table__empty">{{ t('customers.loading') }}</div>
    <div v-else-if="error" class="host-customers-table__empty">{{ error }}</div>
    <div v-else-if="customer" class="host-customer-detail">
        <div class="host-customer-detail__top">
            <div class="host-customer-detail__identity">
                <span class="host-customer-detail__avatar" :style="{ background: customer.bg }">{{ customer.initials }}</span>
                <div>
                    <h1 class="host-customer-detail__name">{{ customer.name }}</h1>
                    <div class="host-customer-detail__meta">
                        <span class="host-customer-detail__badge" :title="customer.id">{{ t('customers.customerBadge') }} · {{ shortId }}</span>
                        <span>{{ customer.country }}</span>
                        <span>·</span>
                        <span>{{ customer.email }}</span>
                    </div>
                </div>
            </div>
            <button type="button" class="host-customer-detail__back" @click="goBack">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7" /></svg>
                {{ t('customers.back') }}
            </button>
        </div>

        <div class="host-customer-detail__layout">
            <div class="host-customer-detail__main">
                <section class="host-customer-detail__profile">
                    <div class="host-customer-detail__profile-head">
                        <h2>{{ t('customers.profileTitle') }}</h2>
                        <span class="host-customer-detail__segment" :style="segmentStyles">{{ customer.segment }}</span>
                        <span
                            v-if="statusStyles.bg"
                            class="host-customer-detail__status"
                            :style="{ background: statusStyles.bg, color: statusStyles.color }"
                        >
                            {{ customer.status }}
                        </span>
                    </div>
                    <p class="host-customer-detail__profile-meta">
                        {{ customer.phone }} · {{ t('customers.language') }}: {{ customer.lang }}
                    </p>
                    <p class="host-customer-detail__profile-meta">
                        {{ t('customers.customerSince') }} {{ customer.member }} ·
                        {{ customer.consent ? t('customers.marketingConsent') : t('customers.noMarketingConsent') }}
                    </p>
                    <div v-if="customer.tags.length" class="host-customer-detail__tags">
                        <span v-for="tag in customer.tags" :key="tag" class="host-customer-detail__tag">{{ tag }}</span>
                    </div>
                </section>

                <div class="host-customer-detail__kpis">
                    <article v-for="kpi in kpis" :key="kpi.label" class="host-customer-detail__kpi">
                        <div class="host-customer-detail__kpi-label">{{ kpi.label }}</div>
                        <div class="host-customer-detail__kpi-value">{{ kpi.value }}</div>
                        <div class="host-customer-detail__kpi-sub">{{ kpi.sub }}</div>
                    </article>
                </div>

                <div class="host-customer-detail__tabs">
                    <button
                        v-for="tab in detailTabs"
                        :key="tab.key"
                        type="button"
                        class="host-customer-detail__tab"
                        :class="{ 'host-customer-detail__tab--active': activeTab === tab.key }"
                        @click="activeTab = tab.key"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <section v-if="activeTab === 'bookings'" class="host-customers-table host-customers-table--detail">
                    <div class="host-customers-table__head host-customers-table__head--detail">
                        <span>{{ t('customers.colApartment') }}</span>
                        <span>{{ t('customers.colDates') }}</span>
                        <span>{{ t('customers.colNights') }}</span>
                        <span>{{ t('customers.colAmount') }}</span>
                        <span>{{ t('customers.colChannel') }}</span>
                        <span>{{ t('customers.colStatus') }}</span>
                    </div>
                    <div v-for="booking in customer.bookings" :key="booking.id" class="host-customers-table__row host-customers-table__row--detail">
                        <div>
                            <strong>{{ booking.apartment }}</strong>
                            <div class="host-customers-table__email">{{ booking.id }}</div>
                        </div>
                        <span>{{ booking.dates }}</span>
                        <span class="host-customers-table__center-only">{{ booking.nights }}</span>
                        <span class="host-customers-table__amount">{{ booking.amount }}</span>
                        <span>{{ booking.channel }}</span>
                        <span>
                            <span
                                class="host-customers-table__status-pill"
                                :style="{ background: bookingStatus(booking.status).bg, color: bookingStatus(booking.status).color }"
                            >
                                {{ booking.status }}
                            </span>
                            <span v-if="booking.historic" class="host-customer-detail__archive">{{ t('customers.archive') }}</span>
                        </span>
                    </div>
                </section>

                <section v-else-if="activeTab === 'reviews'" class="host-customer-detail__panel">
                    <p class="host-customer-detail__notice">{{ t('customers.reviewsNotice') }}</p>
                    <article v-for="review in customer.reviews" :key="`${review.host}-${review.date}`" class="host-customer-detail__review">
                        <div class="host-customer-detail__review-head">
                            <strong>{{ review.host }}</strong>
                            <span>{{ review.property }}</span>
                            <span class="host-customer-detail__review-score">{{ review.score }}</span>
                        </div>
                        <p>{{ review.text }}</p>
                        <span class="host-customer-detail__review-date">{{ review.date }}</span>
                    </article>
                    <p v-if="!customer.reviews.length" class="host-customer-detail__empty">{{ t('customers.noReviews') }}</p>
                </section>

                <section v-else-if="activeTab === 'notes'" class="host-customer-detail__panel">
                    <p class="host-customer-detail__notice">{{ t('customers.notesNotice') }}</p>
                    <div class="host-customer-detail__note-form">
                        <input v-model="noteDraft" type="text" class="host-customer-detail__note-input" :placeholder="t('customers.notePlaceholder')" />
                        <button type="button" class="host-btn host-btn--primary" :disabled="!noteDraft.trim()" @click="saveNote">
                            {{ t('customers.saveNote') }}
                        </button>
                    </div>
                    <article v-for="(note, index) in notes" :key="`${note.when}-${index}`" class="host-customer-detail__note">
                        <p>{{ note.text }}</p>
                        <span>{{ note.who }} · {{ note.when }}</span>
                    </article>
                    <p v-if="!notes.length" class="host-customer-detail__empty">{{ t('customers.noNotes') }}</p>
                </section>

                <section v-else-if="activeTab === 'docs'" class="host-customer-detail__docs">
                    <article v-for="doc in customer.docs" :key="doc.name" class="host-customer-detail__doc">
                        <span class="host-customer-detail__doc-ext">{{ doc.ext }}</span>
                        <div>
                            <strong>{{ doc.name }}</strong>
                            <div>{{ doc.meta }}</div>
                        </div>
                    </article>
                    <p v-if="!customer.docs.length" class="host-customer-detail__empty">{{ t('customers.noDocs') }}</p>
                </section>

                <section v-else class="host-customer-detail__panel">
                    <p class="host-customer-detail__notice">{{ t('customers.prefsNotice') }}</p>
                    <div v-for="pref in customer.prefs" :key="pref" class="host-customer-detail__pref">
                        <span />
                        <span>{{ pref }}</span>
                    </div>
                    <p v-if="!customer.prefs.length" class="host-customer-detail__empty">{{ t('customers.noPrefs') }}</p>
                </section>
            </div>

            <aside class="host-customer-detail__rail">
                <div class="host-customer-detail__actions">
                    <h3>{{ t('customers.actionsTitle') }}</h3>
                    <button type="button" class="host-btn host-btn--primary">{{ t('customers.sendMessage') }}</button>
                    <button type="button" class="host-btn host-btn--ghost">{{ t('customers.shareListings') }}</button>
                    <button type="button" class="host-btn host-btn--ghost" @click="createBooking">{{ t('customers.createBooking') }}</button>
                    <button type="button" class="host-btn host-btn--ghost">{{ t('customers.personalDiscount') }}</button>
                    <button type="button" class="host-customer-detail__block">{{ t('customers.blockCustomer') }}</button>
                </div>
            </aside>
        </div>
    </div>
</template>

<script setup>
import { computed, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/api/client';
import {
    bookingStatusStyle,
    formatVnd,
    segmentStyle,
    statusStyle,
} from '@/data/customers-content.js';
import { usePageTitle } from '@/composables/usePageTitle';

const route = useRoute();
const router = useRouter();
const { t } = useI18n();
const { setPageTitle, clearPageTitle } = usePageTitle();

const customer = ref(null);
const loading = ref(true);
const error = ref('');
const activeTab = ref('bookings');
const noteDraft = ref('');
const localNotes = ref([]);

const notes = computed(() => {
    if (!customer.value) {
        return [];
    }

    return [...localNotes.value, ...(customer.value.notes ?? [])];
});

const segmentStyles = computed(() => {
    if (!customer.value) {
        return {};
    }

    const style = segmentStyle(customer.value.segment);

    return { background: style.bg, color: style.color };
});

const statusStyles = computed(() => {
    if (!customer.value) {
        return {};
    }

    return statusStyle(customer.value.status);
});

const shortId = computed(() => {
    const id = customer.value?.id ?? '';
    return id.length > 10 ? `${id.slice(0, 8)}…` : id;
});

const kpis = computed(() => {
    if (!customer.value) {
        return [];
    }

    return [
        {
            label: t('customers.kpiStays'),
            value: String(customer.value.stays),
            sub: t('customers.kpiStaysSub'),
        },
        {
            label: t('customers.kpiNights'),
            value: String(customer.value.nights),
            sub: t('customers.kpiNightsSub', { avg: customer.value.avgNights }),
        },
        {
            label: t('customers.kpiValue'),
            value: formatVnd(customer.value.revenue),
            sub: t('customers.kpiValueSub'),
        },
        {
            label: t('customers.kpiRating'),
            value: customer.value.rating != null ? Number(customer.value.rating).toFixed(1) : '—',
            sub: t('customers.kpiRatingSub', { count: (customer.value.reviews ?? []).length }),
        },
    ];
});

const detailTabs = computed(() => [
    { key: 'bookings', label: t('customers.tabBookings') },
    { key: 'reviews', label: t('customers.tabReviews') },
    { key: 'notes', label: t('customers.tabNotes') },
    { key: 'docs', label: t('customers.tabDocs') },
    { key: 'prefs', label: t('customers.tabPrefs') },
]);

function bookingStatus(status) {
    return bookingStatusStyle(status);
}

function goBack() {
    router.push({ name: 'customers' });
}

function createBooking() {
    if (!customer.value) {
        return;
    }

    router.push({
        name: 'bookings',
        query: {
            add: 'manual',
            guest_name: customer.value.name,
            email: customer.value.rawEmail || '',
            phone: customer.value.phone && customer.value.phone !== '—' ? customer.value.phone : '',
        },
    });
}

async function loadCustomer() {
    loading.value = true;
    error.value = '';
    customer.value = null;

    try {
        const response = await apiClient.get(`/customers/${route.params.id}`);
        customer.value = response.data ?? null;

        if (!customer.value) {
            error.value = t('customers.notFound');
        }
    } catch (err) {
        error.value = err.message || t('customers.loadFailed');
    } finally {
        loading.value = false;
    }
}

watch(() => route.params.id, loadCustomer, { immediate: true });

watch(customer, (value) => {
    if (value) {
        setPageTitle(value.name);
    }
});

onUnmounted(clearPageTitle);

function saveNote() {
    const text = noteDraft.value.trim();

    if (!text) {
        return;
    }

    localNotes.value = [
        { who: 'You', when: new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }), text },
        ...localNotes.value,
    ];
    noteDraft.value = '';
}
</script>
