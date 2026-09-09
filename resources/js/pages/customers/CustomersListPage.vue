<template>
    <div>
        <div class="host-page-header">
            <div>
                <h1 class="host-page-title">Customers</h1>
                <p class="host-page-subtitle">Everyone who has booked with you — past, active and upcoming.</p>
            </div>
            <div class="host-page-actions">
                <button type="button" class="host-btn host-btn--ghost" @click="exportList">
                    ⬇ Export list
                </button>
                <button type="button" class="host-btn host-btn--accent" @click="addModalOpen = true">
                    + New customer
                </button>
            </div>
        </div>

        <div class="host-cust-tabs">
            <button
                v-for="tabDef in tabs"
                :key="tabDef.id"
                type="button"
                class="host-cust-tab"
                :class="{ 'host-cust-tab--active': tab === tabDef.id }"
                @click="tab = tabDef.id"
            >
                {{ tabDef.label }} ({{ meta.tab_counts?.[tabDef.id] ?? 0 }})
            </button>
        </div>

        <div class="host-cust-stats">
            <div class="host-cust-stat">
                <span class="host-cust-stat__dot host-cust-stat__dot--value" />
                <strong>{{ formatVnd(meta.total_value ?? 0) }}</strong>
                <span class="host-cust-stat__label">customer value</span>
            </div>
            <div class="host-cust-stat">
                <span class="host-cust-stat__dot host-cust-stat__dot--new" />
                <strong>{{ meta.new_this_month ?? 0 }}</strong>
                <span class="host-cust-stat__label">new customers this month</span>
            </div>
            <div class="host-cust-stat">
                <span class="host-cust-stat__dot host-cust-stat__dot--repeat" />
                <strong>{{ meta.repeat_count ?? 0 }}</strong>
                <span class="host-cust-stat__label">repeat customers</span>
            </div>
        </div>

        <div class="host-cust-toolbar">
            <input
                v-model="filters.search"
                type="search"
                class="host-input host-cust-toolbar__search"
                placeholder="Search customer, country or email"
                @input="scheduleLoad"
            />
            <div class="host-cust-dropdown-wrap">
                <button type="button" class="host-btn host-btn--ghost" @click="filterPanelOpen = !filterPanelOpen">
                    Filter ▾
                </button>
            </div>
            <div class="host-cust-dropdown-wrap">
                <button type="button" class="host-btn host-btn--ghost" @click="sortMenuOpen = !sortMenuOpen">
                    Sort ▾
                </button>
                <div v-if="sortMenuOpen" class="host-cust-dropdown">
                    <button
                        v-for="opt in sortOptions"
                        :key="opt.value"
                        type="button"
                        class="host-cust-dropdown__option"
                        :class="{ 'host-cust-dropdown__option--active': filters.sort === opt.value }"
                        @click="selectSort(opt.value)"
                    >
                        {{ opt.label }}
                        <span v-if="filters.sort === opt.value">✓</span>
                    </button>
                </div>
            </div>
        </div>

        <div v-if="filterPanelOpen" class="host-cust-filter-panel">
            <div class="host-field">
                <label class="host-field__label">Country</label>
                <select v-model="filters.country" class="host-select">
                    <option value="">All countries</option>
                    <option v-for="c in meta.countries" :key="c" :value="c">{{ c }}</option>
                </select>
            </div>

            <div class="host-field">
                <label class="host-field__label">Segment</label>
                <div class="host-cust-chip-group">
                    <button
                        v-for="opt in segmentOptions"
                        :key="opt.value"
                        type="button"
                        class="host-cust-chip"
                        :class="{ 'host-cust-chip--active': filters.segment === opt.value }"
                        @click="filters.segment = opt.value"
                    >
                        {{ opt.label }}
                    </button>
                </div>
            </div>

            <div class="host-field">
                <label class="host-field__label">Stays</label>
                <div class="host-cust-chip-group">
                    <button
                        v-for="opt in minStaysOptions"
                        :key="opt.value"
                        type="button"
                        class="host-cust-chip"
                        :class="{ 'host-cust-chip--active': filters.min_stays === opt.value }"
                        @click="filters.min_stays = opt.value"
                    >
                        {{ opt.label }}
                    </button>
                    <label class="host-check-inline host-cust-upcoming-toggle">
                        <input v-model="filters.upcoming_only" type="checkbox" />
                        Only with upcoming stay
                    </label>
                </div>
            </div>

            <div class="host-cust-filter-row">
                <div class="host-field">
                    <label class="host-field__label">From</label>
                    <input v-model="filters.from" type="date" class="host-input" />
                </div>
                <div class="host-field">
                    <label class="host-field__label">To</label>
                    <input v-model="filters.to" type="date" class="host-input" />
                </div>
            </div>

            <p class="host-cust-filter-note">
                VIP = 4+ stays · Repeat = 2–3 stays · New = first stay, and within 30 days of checkout.
            </p>

            <div class="host-cust-filter-actions">
                <button type="button" class="host-btn host-btn--ghost" @click="resetFilters">Reset</button>
                <button type="button" class="host-btn host-btn--accent" @click="applyFilterPanel">Apply filters</button>
            </div>
        </div>

        <div v-if="loading" class="host-loading">Loading customers…</div>

        <div v-else class="host-table-wrap">
            <table class="host-table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Stays</th>
                        <th>Next stay</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="customers.length === 0">
                        <td colspan="4" class="host-table__empty">No customers found.</td>
                    </tr>
                    <tr v-for="customer in customers" :key="customer.id" @click="goToCustomer(customer.id)">
                        <td>
                            <div class="host-cust-name-cell">
                                <span class="host-cust-flag">{{ countryFlag(customer.country) }}</span>
                                <div>
                                    <div class="host-cust-name-row">
                                        <strong>{{ customer.name }}</strong>
                                        <span v-if="customer.segment" class="host-pill" :class="segmentPillClass(customer.segment)">
                                            {{ segmentLabel(customer.segment) }}
                                        </span>
                                    </div>
                                    <div class="host-table__muted">
                                        {{ customer.unregistered ? 'Unregistered · no email' : customer.email }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div><strong>{{ customer.bookings_count }}</strong></div>
                            <div class="host-table__muted">{{ customer.nights_total }} nights</div>
                        </td>
                        <td>
                            <template v-if="customer.next_stay">
                                <div>{{ customer.next_stay.ongoing ? 'Ongoing now' : formatDate(customer.next_stay.date) }}</div>
                                <div class="host-table__muted">
                                    <span class="host-cust-code">{{ customer.next_stay.apartment_code }}</span>
                                    {{ customer.next_stay.apartment }}
                                </div>
                            </template>
                            <span v-else class="host-table__muted">—</span>
                        </td>
                        <td>
                            <span v-if="customer.status" class="host-pill" :class="statusPillClass(customer.status)">
                                {{ statusLabel(customer.status) }}
                            </span>
                            <span v-else class="host-table__muted">—</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <AddCustomerModal
            :open="addModalOpen"
            @close="addModalOpen = false"
            @saved="onCustomerSaved"
        />
    </div>
</template>

<script setup>
import { onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/api/client';
import AddCustomerModal from '@/components/modals/AddCustomerModal.vue';
import { withAppBase } from '@/utils/app-base';
import { countryFlag } from '@/utils/countryFlags';
import { formatDate, formatVnd } from '@/utils/format';

const router = useRouter();

const loading = ref(true);
const customers = ref([]);
const meta = ref({});
const addModalOpen = ref(false);
const filterPanelOpen = ref(false);
const sortMenuOpen = ref(false);
const tab = ref('all');

const tabs = [
    { id: 'all', label: 'All' },
    { id: 'needs_action', label: 'Needs action' },
    { id: 'staying', label: 'Staying' },
    { id: 'upcoming', label: 'Upcoming' },
    { id: 'repeat', label: 'Repeat' },
];

const segmentOptions = [
    { value: '', label: 'All' },
    { value: 'vip', label: 'VIP' },
    { value: 'repeat', label: 'Repeat' },
    { value: 'new', label: 'New customer' },
];

const minStaysOptions = [
    { value: 0, label: 'All' },
    { value: 2, label: '2+' },
    { value: 3, label: '3+' },
    { value: 5, label: '5+' },
];

const sortOptions = [
    { value: 'stays', label: 'Most stays' },
    { value: 'nights', label: 'Most nights' },
    { value: 'value', label: 'Highest customer value' },
    { value: 'next_stay', label: 'Next stay first' },
    { value: 'name', label: 'Name A–Z' },
];

const filters = reactive({
    search: '',
    country: '',
    segment: '',
    min_stays: 0,
    upcoming_only: false,
    from: '',
    to: '',
    sort: 'stays',
});

let searchTimer = null;

function scheduleLoad() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(loadCustomers, 300);
}

function segmentLabel(segment) {
    return { vip: 'VIP', repeat: 'Repeat', new: 'New customer' }[segment] ?? segment;
}

function segmentPillClass(segment) {
    return {
        vip: 'host-pill--active',
        repeat: 'host-pill--confirmed',
        new: 'host-pill--pending',
    }[segment] ?? 'host-pill--draft';
}

function statusLabel(status) {
    return { staying: 'Staying', upcoming: 'Upcoming', past: 'Past guest' }[status] ?? status;
}

function statusPillClass(status) {
    return {
        staying: 'host-pill--active',
        upcoming: 'host-pill--confirmed',
        past: 'host-pill--draft',
    }[status] ?? 'host-pill--draft';
}

function buildParams() {
    const params = new URLSearchParams();
    if (filters.search) params.set('search', filters.search);
    if (filters.country) params.set('country', filters.country);
    if (filters.segment) params.set('segment', filters.segment);
    if (filters.min_stays) params.set('min_stays', filters.min_stays);
    if (filters.upcoming_only) params.set('upcoming_only', '1');
    if (filters.from) params.set('from', filters.from);
    if (filters.to) params.set('to', filters.to);
    params.set('sort', filters.sort);
    params.set('tab', tab.value);
    return params;
}

async function loadCustomers() {
    loading.value = true;
    try {
        const res = await apiClient.get(`/customers?${buildParams().toString()}`);
        customers.value = Array.isArray(res?.data) ? res.data : [];
        meta.value = res?.meta ?? {};
    } catch {
        customers.value = [];
    } finally {
        loading.value = false;
    }
}

function selectSort(value) {
    filters.sort = value;
    sortMenuOpen.value = false;
    loadCustomers();
}

function applyFilterPanel() {
    filterPanelOpen.value = false;
    loadCustomers();
}

function resetFilters() {
    Object.assign(filters, {
        search: filters.search,
        country: '',
        segment: '',
        min_stays: 0,
        upcoming_only: false,
        from: '',
        to: '',
    });
    loadCustomers();
}

function exportList() {
    window.location.href = withAppBase(`/api/customers/export?${buildParams().toString()}`);
}

function goToCustomer(id) {
    router.push({ name: 'customer-detail', params: { id } });
}

function onCustomerSaved() {
    loadCustomers();
}

function onDocumentClick(event) {
    if (!event.target.closest('.host-cust-dropdown-wrap')) {
        sortMenuOpen.value = false;
    }
}

watch(tab, loadCustomers);

onMounted(() => {
    loadCustomers();
    document.addEventListener('click', onDocumentClick);
});

onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
});
</script>
