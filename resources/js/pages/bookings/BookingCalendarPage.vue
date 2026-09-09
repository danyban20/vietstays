<template>
    <div class="host-cal">
        <div class="host-cal__header">
            <div class="host-cal__title-row">
                <span class="host-cal__icon" aria-hidden="true">📅</span>
                <h1 class="host-cal__title">Calendar overview</h1>
            </div>

            <div ref="addMenuRef" class="host-cal__add-wrap">
                <button type="button" class="host-cal__add-btn" @click="addMenuOpen = !addMenuOpen">
                    + Add new <span class="host-cal__add-chevron">▾</span>
                </button>
                <div v-if="addMenuOpen" class="host-cal__add-menu">
                    <button type="button" class="host-cal__add-item" @click="openAddModal('manual')">
                        Add booking
                    </button>
                    <button type="button" class="host-cal__add-item" @click="openAddModal('block')">
                        Block dates
                    </button>
                    <button type="button" class="host-cal__add-item" @click="openAddModal('external')">
                        External booking
                    </button>
                </div>
            </div>
        </div>

        <p class="host-cal__subtitle">
            Calendar overview of all bookings across your apartments.
        </p>

        <div class="host-cal__filters">
            <button
                v-for="filter in typeFilters"
                :key="filter.id"
                type="button"
                class="host-cal__filter-chip"
                :class="{ 'host-cal__filter-chip--active': typeFilter === filter.id }"
                @click="typeFilter = filter.id"
            >
                <span class="host-cal__filter-dot" :style="{ background: filter.dot }" />
                {{ filter.label }}
            </button>

            <div class="host-cal__filters-spacer" />

            <button type="button" class="host-cal__tool-btn host-cal__tool-btn--muted" disabled>
                Business Partners
            </button>
            <button
                type="button"
                class="host-cal__tool-btn"
                :class="{ 'host-cal__tool-btn--active': filterOpen }"
                @click="toggleFilterPanel"
            >
                <span class="host-cal__tool-chevron">▾</span> Filter
            </button>
            <div class="host-cal__sort-wrap">
                <button type="button" class="host-cal__tool-btn" @click="toggleSortMenu">
                    <span class="host-cal__tool-chevron">▾</span> Sorting
                </button>
                <div v-if="sortOpen" class="host-cal__sort-menu">
                    <button
                        v-for="option in sortOptions"
                        :key="option.id"
                        type="button"
                        class="host-cal__sort-option"
                        :class="{ 'host-cal__sort-option--active': sortBy === option.id }"
                        @click="selectSort(option.id)"
                    >
                        {{ option.label }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="filterOpen" class="host-cal__filter-panel">
            <div class="host-cal__filter-row">
                <span class="host-cal__filter-label">Location</span>
                <select v-model="filterDistrict" class="host-cal__select">
                    <option value="all">All districts</option>
                    <option v-for="district in districtOptions" :key="district" :value="district">
                        {{ district }}
                    </option>
                </select>
                <select v-model="filterBuilding" class="host-cal__select">
                    <option value="all">All buildings</option>
                    <option v-for="building in buildingOptions" :key="building" :value="building">
                        {{ building }}
                    </option>
                </select>
            </div>
            <div class="host-cal__filter-row">
                <span class="host-cal__filter-label">Size</span>
                <button
                    v-for="size in sizeOptions"
                    :key="size.id"
                    type="button"
                    class="host-cal__size-chip"
                    :class="{ 'host-cal__size-chip--active': sizeFilter === size.id }"
                    @click="sizeFilter = size.id"
                >
                    {{ size.label }}
                </button>
            </div>
        </div>

        <div v-if="loading" class="host-loading">Loading calendar…</div>

        <div v-else class="host-cal__panel">
            <div class="host-cal__nav">
                <button type="button" class="host-cal__nav-btn" aria-label="Previous week" @click="shiftWindow(-7)">
                    ‹
                </button>
                <button type="button" class="host-cal__nav-btn" aria-label="Next week" @click="shiftWindow(7)">
                    ›
                </button>
                <button
                    type="button"
                    class="host-cal__today-btn"
                    :class="{ 'host-cal__today-btn--active': usingTodayDefault }"
                    @click="goToday"
                >
                    Today
                </button>
                <label class="host-cal__date-jump">
                    <input type="date" class="host-cal__date-jump-input" :value="windowStartIso" @change="jumpToDate" />
                    <span class="host-cal__date-jump-btn" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none">
                            <rect x="3" y="4" width="18" height="17" rx="2" stroke="#43503f" stroke-width="2" />
                            <line x1="3" y1="9" x2="21" y2="9" stroke="#43503f" stroke-width="2" />
                            <line x1="8" y1="4" x2="8" y2="7" stroke="#43503f" stroke-width="2" />
                            <line x1="16" y1="4" x2="16" y2="7" stroke="#43503f" stroke-width="2" />
                        </svg>
                    </span>
                </label>
                <div class="host-cal__range-label">{{ rangeLabel }}</div>
                <div class="host-cal__nav-spacer" />
                <div class="host-cal__nav-hint">Drag a booking to change date or apartment</div>
            </div>

            <div class="host-cal__grid">
                <div class="host-cal__grid-label">
                    <span class="host-cal__apt-count">{{ calendarRows.length }}</span>
                    active apartments
                </div>
                <div class="host-cal__day-head" :style="dayGridStyle">
                    <div
                        v-for="day in calendarDays"
                        :key="day.iso"
                        class="host-cal__day-head-cell"
                        :class="{ 'host-cal__day-head-cell--today': day.isToday }"
                    >
                        {{ day.dayNum }} {{ day.monthLabel }}
                    </div>
                </div>

                <template v-for="row in calendarRows" :key="row.id">
                    <div class="host-cal__apt-label">
                        <div class="host-cal__apt-name" :title="row.name">{{ row.name }}</div>
                        <span class="host-cal__apt-code">{{ row.code }}</span>
                    </div>
                    <div
                        class="host-cal__timeline"
                        :class="{ 'host-cal__timeline--drop-target': dropTargetRowId === row.id }"
                        :style="dayGridStyle"
                        @dragover.prevent="onTimelineDragOver(row)"
                        @dragleave="onTimelineDragLeave(row)"
                        @drop.prevent="onTimelineDrop($event, row)"
                    >
                        <div
                            v-for="day in calendarDays"
                            :key="`${row.id}-${day.iso}`"
                            class="host-cal__day-cell"
                            :class="{ 'host-cal__day-cell--today': day.isToday }"
                        />
                        <button
                            v-for="bar in row.bookings"
                            :key="bar.id"
                            type="button"
                            class="host-cal__bar"
                            :class="{
                                'host-cal__bar--muted': !bar.draggable,
                                'host-cal__bar--dragging': draggingId === bar.id,
                            }"
                            :draggable="bar.draggable"
                            :style="{
                                left: `${bar.leftPct}%`,
                                width: `calc(${bar.widthPct}% - 4px)`,
                                background: bar.color,
                            }"
                            :title="bar.guest"
                            @click="openItem(bar)"
                            @dragstart="onBarDragStart($event, bar, row)"
                            @dragend="onBarDragEnd"
                        >
                            {{ bar.guest }} · {{ bar.nights }}d
                        </button>
                    </div>
                </template>

                <div v-if="!calendarRows.length" class="host-cal__empty">
                    No apartments match your filters.
                </div>
            </div>
        </div>

        <CalendarMoveModal
            :open="moveModalOpen"
            :draft="moveDraft"
            @close="closeMoveModal"
            @moved="onBookingMoved"
        />

        <AddBookingModal
            :open="addModalOpen"
            :variant="addModalVariant"
            :navigate-after-create="false"
            @close="addModalOpen = false"
            @saved="onAddSaved"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/api/client';
import AddBookingModal from '@/components/modals/AddBookingModal.vue';
import CalendarMoveModal from '@/components/bookings/CalendarMoveModal.vue';
import { useToast } from '@/composables/useToast';
import { addDays, isoDate } from '@/utils/apartment-availability';
import {
    CALENDAR_DAYS,
    buildCalendarDays,
    clipBookingBar,
    defaultWindowStart,
    formatCalendarRange,
    isoDate as calIsoDate,
    matchesCalendarTypeFilter,
    parseIsoDate,
    startOfDay,
} from '@/utils/booking-calendar';

const router = useRouter();
const toast = useToast();

const loading = ref(true);
const addMenuOpen = ref(false);
const addMenuRef = ref(null);
const addModalOpen = ref(false);
const addModalVariant = ref('manual');
const typeFilter = ref('all');
const filterOpen = ref(false);
const sortOpen = ref(false);
const sortBy = ref('building');
const sizeFilter = ref('all');
const filterDistrict = ref('all');
const filterBuilding = ref('all');
const windowStart = ref(defaultWindowStart());
const usingCustomWindow = ref(false);

const apartments = ref([]);
const items = ref([]);
const draggingId = ref(null);
const dragPayload = ref(null);
const dropTargetRowId = ref(null);
const moveModalOpen = ref(false);
const moveDraft = ref(null);

const typeFilters = [
    { id: 'all', label: 'All', dot: '#8a9187' },
    { id: 'vietstays', label: 'Vietstays / manual', dot: '#1f7a44' },
    { id: 'airbnb', label: 'Airbnb', dot: '#8a9187' },
    { id: 'blocked', label: 'Blocked', dot: '#e0a458' },
];

const sortOptions = [
    { id: 'building', label: 'Building' },
    { id: 'name', label: 'Apartment name' },
    { id: 'district', label: 'District' },
    { id: 'size', label: 'Size' },
];

const calendarDays = computed(() => buildCalendarDays(windowStart.value));
const windowStartIso = computed(() => calIsoDate(windowStart.value));
const windowEnd = computed(() => {
    const end = new Date(windowStart.value);
    end.setDate(end.getDate() + CALENDAR_DAYS);
    return end;
});
const usingTodayDefault = computed(() => !usingCustomWindow.value);
const rangeLabel = computed(() => formatCalendarRange(calendarDays.value, usingTodayDefault.value));
const dayGridStyle = computed(() => ({
    gridTemplateColumns: `repeat(${CALENDAR_DAYS}, minmax(0, 1fr))`,
}));

const districtOptions = computed(() =>
    [...new Set(apartments.value.map((apt) => apt.district).filter(Boolean))].sort(),
);

const buildingOptions = computed(() =>
    [
        ...new Set(
            apartments.value
                .filter((apt) => filterDistrict.value === 'all' || apt.district === filterDistrict.value)
                .map((apt) => apt.building)
                .filter(Boolean),
        ),
    ].sort(),
);

const sizeOptions = computed(() => {
    const sizes = [...new Set(apartments.value.map((apt) => apt.type).filter(Boolean))].sort();
    return [{ id: 'all', label: 'All sizes' }, ...sizes.map((size) => ({ id: size, label: size }))];
});

const filteredApartments = computed(() => {
    let list = [...apartments.value];

    if (filterDistrict.value !== 'all') {
        list = list.filter((apt) => apt.district === filterDistrict.value);
    }
    if (filterBuilding.value !== 'all') {
        list = list.filter((apt) => apt.building === filterBuilding.value);
    }
    if (sizeFilter.value !== 'all') {
        list = list.filter((apt) => apt.type === sizeFilter.value);
    }

    list.sort((a, b) => {
        if (sortBy.value === 'name') return (a.name ?? '').localeCompare(b.name ?? '');
        if (sortBy.value === 'district') {
            return (a.district ?? '').localeCompare(b.district ?? '') || (a.name ?? '').localeCompare(b.name ?? '');
        }
        if (sortBy.value === 'size') {
            return (a.type ?? '').localeCompare(b.type ?? '') || (a.name ?? '').localeCompare(b.name ?? '');
        }
        return (
            (a.building ?? '').localeCompare(b.building ?? '') || (a.name ?? '').localeCompare(b.name ?? '')
        );
    });

    return list;
});

const calendarRows = computed(() => {
    const start = startOfDay(windowStart.value);
    const end = windowEnd.value;

    return filteredApartments.value.map((apt) => {
        const bookings = items.value
            .filter((item) => item.apartment_id === apt.id)
            .filter((item) => matchesCalendarTypeFilter(item, typeFilter.value))
            .map((item) => clipBookingBar(item, start, end))
            .filter(Boolean);

        return {
            ...apt,
            bookings,
        };
    });
});

function shiftWindow(days) {
    usingCustomWindow.value = true;
    const next = new Date(windowStart.value);
    next.setDate(next.getDate() + days);
    windowStart.value = startOfDay(next);
}

function goToday() {
    usingCustomWindow.value = false;
    windowStart.value = defaultWindowStart();
}

function jumpToDate(event) {
    const date = parseIsoDate(event.target.value);
    if (!date) return;
    usingCustomWindow.value = true;
    windowStart.value = startOfDay(date);
}

function toggleFilterPanel() {
    filterOpen.value = !filterOpen.value;
    sortOpen.value = false;
}

function toggleSortMenu() {
    sortOpen.value = !sortOpen.value;
    filterOpen.value = false;
}

function selectSort(id) {
    sortBy.value = id;
    sortOpen.value = false;
}

function onBarDragStart(event, bar, row) {
    if (!bar.draggable || !bar.booking_id) {
        event.preventDefault();
        return;
    }

    draggingId.value = bar.id;
    dragPayload.value = {
        bar,
        sourceApartmentId: row.id,
        sourceApartmentName: row.name,
    };
    event.dataTransfer.effectAllowed = 'move';
    event.dataTransfer.setData('text/plain', String(bar.booking_id));
}

function onBarDragEnd() {
    draggingId.value = null;
    dragPayload.value = null;
    dropTargetRowId.value = null;
}

function onTimelineDragOver(row) {
    if (!dragPayload.value) return;
    dropTargetRowId.value = row.id;
}

function onTimelineDragLeave(row) {
    if (dropTargetRowId.value === row.id) {
        dropTargetRowId.value = null;
    }
}

function onTimelineDrop(event, row) {
    dropTargetRowId.value = null;

    const payload = dragPayload.value;
    if (!payload?.bar?.booking_id) return;

    const timeline = event.currentTarget;
    const rect = timeline.getBoundingClientRect();
    const x = event.clientX - rect.left;
    const colWidth = rect.width / CALENDAR_DAYS;
    const dayIndex = Math.min(CALENDAR_DAYS - 1, Math.max(0, Math.floor(x / colWidth)));
    const newCheckIn = calendarDays.value[dayIndex]?.iso;

    if (!newCheckIn) return;

    const checkInDate = parseIsoDate(newCheckIn);
    const checkOutDate = addDays(checkInDate, payload.bar.nights);

    moveDraft.value = {
        bookingId: payload.bar.booking_id,
        guest: payload.bar.guest,
        fromApartmentId: payload.sourceApartmentId,
        fromApartmentName: payload.sourceApartmentName,
        toApartmentId: row.id,
        toApartmentName: row.name,
        fromCheckIn: payload.bar.check_in,
        fromCheckOut: payload.bar.check_out,
        toCheckIn: newCheckIn,
        toCheckOut: isoDate(checkOutDate),
    };
    moveModalOpen.value = true;
    onBarDragEnd();
}

function closeMoveModal() {
    moveModalOpen.value = false;
    moveDraft.value = null;
}

function onBookingMoved() {
    toast.show('Booking moved.');
    loadCalendar();
}

function openAddModal(variant) {
    addModalVariant.value = variant;
    addModalOpen.value = true;
    addMenuOpen.value = false;
}

function onAddSaved() {
    loadCalendar();
}

function openItem(bar) {
    if (bar.booking_id) {
        router.push({ name: 'booking-detail', params: { id: bar.booking_id } });
    }
}

function onDocumentClick(event) {
    if (addMenuRef.value && !addMenuRef.value.contains(event.target)) {
        addMenuOpen.value = false;
    }
    if (!event.target.closest('.host-cal__sort-wrap')) {
        sortOpen.value = false;
    }
}

async function loadCalendar() {
    loading.value = true;
    try {
        const from = calIsoDate(windowStart.value);
        const to = calIsoDate(windowEnd.value);
        const res = await apiClient.get(`/calendar?from=${from}&to=${to}`);
        apartments.value = Array.isArray(res?.data?.apartments) ? res.data.apartments : [];
        items.value = Array.isArray(res?.data?.items) ? res.data.items : [];
    } catch {
        apartments.value = [];
        items.value = [];
    } finally {
        loading.value = false;
    }
}

watch(windowStart, loadCalendar);

onMounted(() => {
    loadCalendar();
    document.addEventListener('click', onDocumentClick);
});

onUnmounted(() => {
    document.removeEventListener('click', onDocumentClick);
});
</script>
