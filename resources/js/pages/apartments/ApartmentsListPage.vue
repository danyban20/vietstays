<template>
    <div class="host-apt-list">
        <div class="host-apt-list__header">
            <div class="host-apt-list__title-row">
                <span class="host-apt-list__icon" aria-hidden="true">🏠</span>
                <h1 class="host-apt-list__title">All apartments</h1>
            </div>
            <router-link :to="{ name: 'apartment-add' }" class="host-apt-list__add-btn">
                + Add new apartment
            </router-link>
        </div>

        <p class="host-apt-list__subtitle">
            Overview of all apartments you manage — status, next booking and activity across buildings.
        </p>

        <div class="host-apt-list__tabs">
            <button
                v-for="tab in quickTabs"
                :key="tab.id"
                type="button"
                class="host-apt-list__tab"
                :class="{ 'host-apt-list__tab--active': activeTab === tab.id }"
                @click="activeTab = tab.id"
            >
                <span v-if="tab.urgent" class="host-apt-list__tab-dot" aria-hidden="true" />
                {{ tab.label }} ( {{ tab.count }} )
            </button>
        </div>

        <div class="host-apt-list__toolbar">
            <div v-for="stat in stats" :key="stat.label" class="host-apt-list__stat">
                <span class="host-apt-list__stat-dot" :style="{ background: stat.dot }" />
                <div>
                    <div class="host-apt-list__stat-value-row">
                        <span class="host-apt-list__stat-value">{{ stat.value }}</span>
                        <span v-if="stat.trend" class="host-apt-list__stat-trend">{{ stat.trend }}</span>
                    </div>
                    <div class="host-apt-list__stat-label">{{ stat.label }}</div>
                    <div v-if="stat.sub" class="host-apt-list__stat-sub">{{ stat.sub }}</div>
                </div>
            </div>

            <div class="host-apt-list__toolbar-spacer" />

            <div class="host-apt-list__search-wrap">
                <span class="host-apt-list__search-icon" aria-hidden="true">⌕</span>
                <input
                    v-model="searchQuery"
                    type="search"
                    class="host-apt-list__search"
                    placeholder="Search…"
                />
            </div>

            <button
                type="button"
                class="host-apt-list__tool-btn"
                :class="{ 'host-apt-list__tool-btn--active': filterOpen }"
                @click="filterOpen = !filterOpen"
            >
                Filter <span class="host-apt-list__chevron">▾</span>
            </button>

            <div class="host-apt-list__sort-wrap">
                <button type="button" class="host-apt-list__tool-btn" @click="sortOpen = !sortOpen">
                    Sorting <span class="host-apt-list__chevron">▾</span>
                </button>
                <div v-if="sortOpen" class="host-apt-list__sort-menu">
                    <button
                        v-for="option in sortOptions"
                        :key="option.id"
                        type="button"
                        class="host-apt-list__sort-option"
                        :class="{ 'host-apt-list__sort-option--active': sortBy === option.id }"
                        @click="selectSort(option.id)"
                    >
                        {{ option.label }}
                        <span v-if="sortBy === option.id" class="host-apt-list__sort-check">✓</span>
                    </button>
                </div>
            </div>
        </div>

        <div v-if="loading" class="host-loading">Loading apartments…</div>

        <div v-else class="host-apt-list__table">
            <div class="host-apt-list__table-head">
                <input
                    type="checkbox"
                    class="host-apt-list__checkbox"
                    :checked="allVisibleSelected"
                    @change="toggleSelectAll"
                />
                <div>Apartment</div>
                <div>Location</div>
                <div>Bedroom</div>
                <div>Current status</div>
                <div>Next booking</div>
                <div>Activity</div>
                <div />
            </div>

            <div
                v-for="apt in visibleApartments"
                :key="apt.id"
                class="host-apt-list__row"
                @click="goToDetail(apt.id)"
            >
                <input
                    type="checkbox"
                    class="host-apt-list__checkbox"
                    :checked="selectedIds.has(apt.id)"
                    @click.stop
                    @change="toggleSelect(apt.id)"
                />

                <div class="host-apt-list__apt-cell">
                    <div class="host-apt-list__apt-title-row">
                        <span v-if="apt.flagged" class="host-apt-list__flag" title="Flagged">⚑</span>
                        <span class="host-apt-list__apt-name" :title="apt.name">{{ apt.name }}</span>
                        <span v-if="hasNewBooking(apt)" class="host-apt-list__new-badge">New booking</span>
                    </div>
                    <div class="host-apt-list__apt-meta">
                        <span class="host-apt-list__apt-code">{{ apt.code ?? `ID ${apt.id}` }}</span>
                        <span v-if="apt.owner_name"> · {{ apt.owner_name }}</span>
                    </div>
                </div>

                <div class="host-apt-list__location" :title="apt.location_label">
                    {{ apt.location_label || `${apt.district ?? '—'}, ${apt.building ?? '—'}` }}
                </div>

                <div class="host-apt-list__bedroom">
                    <div class="host-apt-list__bedroom-count">{{ apt.bedrooms ?? '—' }}</div>
                    <div class="host-apt-list__bedroom-label">{{ apt.bedroom_label ?? apt.type }}</div>
                </div>

                <div>
                    <span class="host-apt-list__status-pill" :class="statusClass(apt.current_status)">
                        {{ apt.current_status_label ?? formatStatus(apt.status) }}
                    </span>
                </div>

                <div class="host-apt-list__next-booking">
                    <template v-if="apt.next_booking">
                        <div class="host-apt-list__next-row">
                            <span class="host-apt-list__next-dot" />
                            <span class="host-apt-list__next-date">{{ apt.next_booking.date_label }}</span>
                        </div>
                        <div class="host-apt-list__next-sub">
                            in {{ apt.next_booking.days_until }} day{{ apt.next_booking.days_until === 1 ? '' : 's' }}
                        </div>
                        <div class="host-apt-list__next-guest">{{ apt.next_booking.guest }}</div>
                    </template>
                    <span v-else class="host-apt-list__muted">None upcoming</span>
                </div>

                <div class="host-apt-list__activity">
                    <div class="host-apt-list__activity-label">{{ apt.activity_label ?? '0 bookings' }}</div>
                    <div v-if="apt.rating" class="host-apt-list__rating">★ {{ apt.rating.toFixed(1) }}</div>
                    <div class="host-apt-list__activity-bar">
                        <span
                            class="host-apt-list__activity-fill"
                            :style="{ width: `${apt.activity_percent ?? 0}%` }"
                        />
                    </div>
                </div>

                <button type="button" class="host-apt-list__menu" aria-label="Actions" @click.stop>
                    ⋮
                </button>
            </div>

            <div v-if="!visibleApartments.length" class="host-apt-list__empty">
                No apartments match your filters.
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/api/client';
import { formatStatus } from '@/utils/format';

const router = useRouter();

const loading = ref(true);
const apartments = ref([]);
const activeTab = ref('all');
const searchQuery = ref('');
const sortBy = ref('name');
const sortOpen = ref(false);
const filterOpen = ref(false);
const selectedIds = ref(new Set());

const sortOptions = [
    { id: 'name', label: 'Apartment name' },
    { id: 'next_booking', label: 'Next booking' },
    { id: 'bookings', label: 'Most bookings' },
    { id: 'status', label: 'Status' },
];

const quickTabs = computed(() => {
    const all = apartments.value;
    const guestInside = all.filter((apt) => apt.current_status === 'guest_inside');
    const vacancies = all.filter((apt) => apt.current_status !== 'guest_inside');
    const urgent = all.filter((apt) => apt.is_urgent);

    return [
        { id: 'all', label: 'All', count: all.length, urgent: false },
        { id: 'guest_inside', label: 'Guest inside', count: guestInside.length, urgent: false },
        { id: 'vacancies', label: 'Vacancies', count: vacancies.length, urgent: false },
        { id: 'urgent', label: 'Urgent', count: urgent.length, urgent: true },
    ];
});

const stats = computed(() => {
    const total = apartments.value.length;
    const guestInside = apartments.value.filter((apt) => apt.current_status === 'guest_inside').length;
    const availableToday = apartments.value.filter((apt) => apt.current_status === 'available').length;
    const occupancy = total ? Math.round((guestInside / total) * 100) : 0;

    return [
        { dot: '#1c2b23', value: total, label: 'Total apartments' },
        {
            dot: '#c9c2ac',
            value: `${occupancy}%`,
            label: 'occupancy',
            sub: guestInside ? `${guestInside} of ${total} occupied today` : undefined,
            trend: occupancy > 0 ? '↑' : undefined,
        },
        { dot: '#e0793a', value: availableToday, label: 'Available today' },
    ];
});

const filteredApartments = computed(() => {
    let list = [...apartments.value];

    if (activeTab.value === 'guest_inside') {
        list = list.filter((apt) => apt.current_status === 'guest_inside');
    } else if (activeTab.value === 'vacancies') {
        list = list.filter((apt) => apt.current_status !== 'guest_inside');
    } else if (activeTab.value === 'urgent') {
        list = list.filter((apt) => apt.is_urgent);
    }

    const q = searchQuery.value.trim().toLowerCase();
    if (q) {
        list = list.filter((apt) => {
            const haystack = [
                apt.name,
                apt.code,
                apt.district,
                apt.building,
                apt.location_label,
                apt.owner_name,
            ]
                .filter(Boolean)
                .join(' ')
                .toLowerCase();
            return haystack.includes(q);
        });
    }

    list.sort((a, b) => {
        if (sortBy.value === 'next_booking') {
            const aDate = a.next_booking?.date ?? '9999-12-31';
            const bDate = b.next_booking?.date ?? '9999-12-31';
            return aDate.localeCompare(bDate);
        }
        if (sortBy.value === 'bookings') {
            return (b.bookings_count ?? 0) - (a.bookings_count ?? 0);
        }
        if (sortBy.value === 'status') {
            return (a.current_status_label ?? '').localeCompare(b.current_status_label ?? '');
        }
        return (a.name ?? '').localeCompare(b.name ?? '');
    });

    return list;
});

const visibleApartments = computed(() => filteredApartments.value);

const allVisibleSelected = computed(
    () =>
        visibleApartments.value.length > 0 &&
        visibleApartments.value.every((apt) => selectedIds.value.has(apt.id)),
);

function hasNewBooking(apt) {
    const days = apt.next_booking?.days_until;
    return days != null && days <= 7;
}

function statusClass(status) {
    return {
        guest_inside: 'host-apt-list__status-pill--inside',
        available: 'host-apt-list__status-pill--available',
        draft: 'host-apt-list__status-pill--draft',
        pending: 'host-apt-list__status-pill--pending',
    }[status] ?? 'host-apt-list__status-pill--available';
}

function toggleSelect(id) {
    const next = new Set(selectedIds.value);
    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }
    selectedIds.value = next;
}

function toggleSelectAll(event) {
    if (event.target.checked) {
        selectedIds.value = new Set(visibleApartments.value.map((apt) => apt.id));
    } else {
        selectedIds.value = new Set();
    }
}

function selectSort(id) {
    sortBy.value = id;
    sortOpen.value = false;
}

function goToDetail(id) {
    router.push({ name: 'apartment-detail', params: { id } });
}

async function loadApartments() {
    loading.value = true;
    try {
        const data = await apiClient.get('/apartments');
        apartments.value = Array.isArray(data?.data) ? data.data : [];
    } catch {
        apartments.value = [];
    } finally {
        loading.value = false;
    }
}

onMounted(loadApartments);
</script>
