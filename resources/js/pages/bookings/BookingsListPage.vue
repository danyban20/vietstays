<template>
    <div>
        <div class="host-page-header">
            <h1 class="host-page-title">All bookings</h1>
            <div class="host-page-actions">
                <div class="host-view-switch">
                    <router-link
                        :to="{ name: 'bookings' }"
                        class="host-view-switch__btn host-view-switch__btn--active"
                    >
                        List
                    </router-link>
                    <router-link
                        :to="{ name: 'bookings-calendar' }"
                        class="host-view-switch__btn"
                    >
                        Calendar
                    </router-link>
                </div>
                <div ref="addMenuRef" class="host-add-menu">
                    <button type="button" class="host-btn host-btn--accent" @click="addMenuOpen = !addMenuOpen">
                        + Add
                    </button>
                    <div v-if="addMenuOpen" class="host-add-menu__dropdown">
                        <button
                            type="button"
                            class="host-add-menu__item"
                            @click="openAddModal('manual')"
                        >
                            Add booking
                        </button>
                        <button
                            type="button"
                            class="host-add-menu__item"
                            @click="openAddModal('block')"
                        >
                            Block dates
                        </button>
                        <button
                            type="button"
                            class="host-add-menu__item"
                            @click="openAddModal('external')"
                        >
                            External booking
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="host-bk-filters">
            <div class="host-bk-filters__row">
                <div class="host-field">
                    <label class="host-field__label" for="filter-from">From</label>
                    <input id="filter-from" v-model="filters.from" type="date" class="host-input" />
                </div>
                <div class="host-field">
                    <label class="host-field__label" for="filter-to">To</label>
                    <input id="filter-to" v-model="filters.to" type="date" class="host-input" />
                </div>
                <div class="host-field">
                    <label class="host-field__label" for="filter-district">District</label>
                    <select id="filter-district" v-model="filters.district" class="host-select">
                        <option value="">All districts</option>
                        <option v-for="d in filterOptions.districts" :key="d.district_id" :value="d.district_id">
                            {{ d.name }}
                        </option>
                    </select>
                </div>
                <div class="host-field">
                    <label class="host-field__label" for="filter-building">Building</label>
                    <select id="filter-building" v-model="filters.building" class="host-select">
                        <option value="">All buildings</option>
                        <option v-for="b in filteredBuildings" :key="b.id" :value="b.id">{{ b.name }}</option>
                    </select>
                </div>
                <div class="host-field">
                    <label class="host-field__label" for="filter-apartment">Apartment</label>
                    <select id="filter-apartment" v-model="filters.apartment" class="host-select">
                        <option value="">All apartments</option>
                        <option v-for="a in filteredApartments" :key="a.id" :value="a.id">{{ a.name }}</option>
                    </select>
                </div>
                <div class="host-field">
                    <label class="host-field__label" for="filter-commission">Commission</label>
                    <select id="filter-commission" v-model="filters.commission" class="host-select">
                        <option value="">All</option>
                        <option value="host_agent">Host agent</option>
                        <option value="ambassador">Ambassador</option>
                        <option value="none">Cash points / none</option>
                    </select>
                </div>
                <div class="host-field">
                    <label class="host-field__label" for="filter-search">Search</label>
                    <input
                        id="filter-search"
                        v-model="filters.search"
                        type="search"
                        class="host-input"
                        placeholder="Guest, reference…"
                    />
                </div>
            </div>

            <div class="host-bk-filters__row host-bk-filters__row--secondary">
                <div class="host-bk-filters__status-group">
                    <span class="host-bk-filters__group-label">Status</span>
                    <button
                        v-for="option in statusOptions"
                        :key="option.id"
                        type="button"
                        class="host-bk-filters__status-chip"
                        :class="{ 'host-bk-filters__status-chip--active': filters.statuses.includes(option.id) }"
                        @click="toggleStatus(option.id)"
                    >
                        {{ option.label }}
                    </button>
                </div>

                <label class="host-bk-filters__outstanding">
                    <input v-model="filters.outstanding_only" type="checkbox" />
                    <span>Outstanding only</span>
                </label>

                <button type="button" class="host-btn host-btn--ghost" @click="resetFilters">Reset</button>
            </div>
        </div>

        <div v-if="loading" class="host-loading">Loading bookings…</div>

        <div v-else class="host-table-wrap">
            <table class="host-table">
                <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Apartment</th>
                        <th>Period</th>
                        <th>Nights</th>
                        <th style="text-align: right">Amount</th>
                        <th>Channel</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="booking in bookings"
                        :key="booking.id"
                        @click="goToBooking(booking.id)"
                    >
                        <td>
                            <strong>{{ booking.guest }}</strong>
                            <div v-if="booking.email" class="host-table__muted">{{ booking.email }}</div>
                        </td>
                        <td>{{ booking.apartment }}</td>
                        <td>{{ formatPeriod(booking.from, booking.to) }}</td>
                        <td>{{ booking.nights }}</td>
                        <td class="host-table__amount">{{ formatVnd(booking.amount) }}</td>
                        <td>
                            <span class="host-bk-table__channel">
                                <span
                                    class="host-bk-table__channel-dot"
                                    :style="{ background: channelColor(booking.channel_type) }"
                                />
                                {{ booking.channel_label ?? booking.channel }}
                            </span>
                        </td>
                        <td>
                            <span class="host-pill" :class="pillClass(booking.status)">
                                {{ formatStatus(booking.status) }}
                            </span>
                        </td>
                    </tr>
                    <tr v-if="bookings.length === 0">
                        <td colspan="7" class="host-loading">No bookings found</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <AddBookingModal
            :open="addModalOpen"
            :variant="addModalVariant"
            :initial-guest-name="addModalGuestName"
            @close="addModalOpen = false"
            @saved="loadBookings"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/api/client';
import AddBookingModal from '@/components/modals/AddBookingModal.vue';
import { formatPeriod, formatStatus, formatVnd } from '@/utils/format';

import { CALENDAR_TYPE_COLORS } from '@/utils/booking-calendar';

const route = useRoute();
const router = useRouter();
const loading = ref(true);
const bookings = ref([]);
const addMenuOpen = ref(false);
const addMenuRef = ref(null);
const addModalOpen = ref(false);
const addModalVariant = ref('manual');
const addModalGuestName = ref('');

const filterOptions = reactive({
    districts: [],
    buildings: [],
    apartments: [],
});

const statusOptions = [
    { id: 'confirmed', label: 'Confirmed' },
    { id: 'pending', label: 'Pending' },
    { id: 'cancelled', label: 'Cancelled' },
];

const filters = reactive({
    from: '',
    to: '',
    district: '',
    building: '',
    apartment: '',
    statuses: [],
    outstanding_only: false,
    commission: '',
    search: '',
});

const filteredBuildings = computed(() => {
    if (!filters.district) return filterOptions.buildings;
    return filterOptions.buildings.filter((b) => b.district_id === Number(filters.district));
});

const filteredApartments = computed(() => {
    let list = filterOptions.apartments;
    if (filters.district) {
        list = list.filter((a) => a.district_id === Number(filters.district));
    }
    if (filters.building) {
        list = list.filter((a) => a.building_id === Number(filters.building));
    }
    return list;
});

async function loadFilterOptions() {
    try {
        const res = await apiClient.get('/locations/filters');
        Object.assign(filterOptions, res?.data ?? {});
    } catch {
        /* filters optional */
    }
}

function buildQueryString() {
    const params = new URLSearchParams();

    if (filters.from) params.set('from', filters.from);
    if (filters.to) params.set('to', filters.to);
    if (filters.district) params.set('district', filters.district);
    if (filters.building) params.set('building', filters.building);
    if (filters.apartment) params.set('apartment', filters.apartment);
    if (filters.search) params.set('search', filters.search);
    if (filters.commission) params.set('commission', filters.commission);
    if (filters.outstanding_only) params.set('outstanding_only', '1');
    if (filters.statuses.length) params.set('statuses', filters.statuses.join(','));

    return params.toString();
}

function toggleStatus(status) {
    const idx = filters.statuses.indexOf(status);
    if (idx >= 0) {
        filters.statuses.splice(idx, 1);
    } else {
        filters.statuses.push(status);
    }
}

function channelColor(type) {
    return CALENDAR_TYPE_COLORS[type] ?? CALENDAR_TYPE_COLORS.vietstays;
}

async function loadBookings() {
    loading.value = true;
    try {
        const params = buildQueryString();
        const path = params ? `/bookings?${params}` : '/bookings';
        const data = await apiClient.get(path);
        bookings.value = Array.isArray(data?.data) ? data.data : [];
    } catch {
        bookings.value = [];
    } finally {
        loading.value = false;
    }
}

function resetFilters() {
    filters.from = '';
    filters.to = '';
    filters.district = '';
    filters.building = '';
    filters.apartment = '';
    filters.statuses = [];
    filters.outstanding_only = false;
    filters.commission = '';
    filters.search = '';
}

function openAddModal(variant) {
    addModalVariant.value = variant;
    addModalOpen.value = true;
    addMenuOpen.value = false;
}

function goToBooking(id) {
    router.push({ name: 'booking-detail', params: { id } });
}

function pillClass(status) {
    return {
        confirmed: 'host-pill--confirmed',
        pending: 'host-pill--pending',
        cancelled: 'host-pill--cancelled',
    }[status] ?? 'host-pill--draft';
}

function onDocumentClick(event) {
    if (addMenuRef.value && !addMenuRef.value.contains(event.target)) {
        addMenuOpen.value = false;
    }
}

onMounted(() => {
    loadFilterOptions();
    loadBookings();
    document.addEventListener('click', onDocumentClick);

    const addQuery = route.query.add;
    if (addQuery && ['manual', 'block', 'external'].includes(addQuery)) {
        addModalGuestName.value = typeof route.query.guest_name === 'string' ? route.query.guest_name : '';
        openAddModal(addQuery);
        router.replace({ query: {} });
    }
});

onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
});

watch(filters, () => loadBookings(), { deep: true });
</script>
