<template>
    <div class="drc">
        <div class="drc__boxes">
            <button type="button" class="drc__box" :class="{ 'drc__box--active': pickingCheckIn }" @click="pickCheckIn">
                <span class="drc__box-label">Check-in</span>
                <span class="drc__box-value">{{ checkInLabel }}</span>
            </button>
            <button type="button" class="drc__box" :class="{ 'drc__box--active': pickingCheckOut }" @click="pickCheckOut">
                <span class="drc__box-label">Check-out</span>
                <span class="drc__box-value">{{ checkOutLabel }}</span>
            </button>
        </div>

        <div class="drc__nav">
            <button type="button" class="drc__nav-btn" :disabled="atCurrentMonth" @click="goToPrevMonth">‹</button>
            <span class="drc__nav-label">{{ monthLabel }}</span>
            <button type="button" class="drc__nav-btn" @click="goToNextMonth">›</button>
        </div>

        <div class="drc__weekdays">
            <span v-for="w in weekdayLabels" :key="w">{{ w }}</span>
        </div>

        <div class="drc__grid">
            <template v-for="(cell, index) in gridCells" :key="index">
                <button
                    v-if="cell"
                    type="button"
                    class="drc__day"
                    :class="{
                        'drc__day--past': cell.isPast,
                        'drc__day--occupied': cell.isOccupied,
                        'drc__day--checkin': cell.isCheckIn,
                        'drc__day--checkout': cell.isCheckOut,
                        'drc__day--inrange': cell.isInRange,
                    }"
                    :disabled="cell.isPast || cell.isOccupied"
                    @click="selectDate(cell.date)"
                >
                    {{ cell.dayNum }}
                </button>
                <span v-else class="drc__day drc__day--blank" />
            </template>
        </div>

        <p v-if="apartmentId && loading" class="drc__hint">Loading availability…</p>
        <p v-else-if="!apartmentId" class="drc__hint">Select an apartment to see booked dates.</p>
        <p v-else class="drc__hint">
            <span class="drc__legend-dot drc__legend-dot--occupied" /> Booked
            <span class="drc__legend-dot drc__legend-dot--selected" /> Selected
        </p>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import apiClient from '@/api/client';

const props = defineProps({
    apartmentId: { type: [Number, String], default: '' },
    checkIn: { type: String, default: '' },
    checkOut: { type: String, default: '' },
});

const emit = defineEmits(['update:checkIn', 'update:checkOut']);

const weekdayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

function isoOf(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

function parseIso(value) {
    if (!value) return null;
    const date = new Date(`${value}T00:00:00`);
    return Number.isNaN(date.getTime()) ? null : date;
}

function startOfDay(date) {
    const next = new Date(date);
    next.setHours(0, 0, 0, 0);
    return next;
}

const today = startOfDay(new Date());
const occupied = ref(new Set());
const loading = ref(false);

const initialMonth = parseIso(props.checkIn) ?? today;
const viewMonth = ref(new Date(initialMonth.getFullYear(), initialMonth.getMonth(), 1));

const pickingCheckIn = computed(() => !props.checkIn || Boolean(props.checkIn && props.checkOut));
const pickingCheckOut = computed(() => Boolean(props.checkIn && !props.checkOut));

const atCurrentMonth = computed(
    () => viewMonth.value.getFullYear() === today.getFullYear() && viewMonth.value.getMonth() === today.getMonth(),
);

const monthLabel = computed(() => viewMonth.value.toLocaleDateString('en', { month: 'long', year: 'numeric' }));

function formatBoxDate(iso) {
    const date = parseIso(iso);
    if (!date) return 'Select on calendar';
    return date.toLocaleDateString('en', { month: 'short', day: 'numeric', year: 'numeric' });
}

const checkInLabel = computed(() => formatBoxDate(props.checkIn));
const checkOutLabel = computed(() => formatBoxDate(props.checkOut));

function rangeHasOccupied(start, end) {
    const cursor = new Date(start);
    cursor.setDate(cursor.getDate() + 1);
    while (cursor < end) {
        if (occupied.value.has(isoOf(cursor))) return true;
        cursor.setDate(cursor.getDate() + 1);
    }
    return false;
}

const gridCells = computed(() => {
    const year = viewMonth.value.getFullYear();
    const month = viewMonth.value.getMonth();
    const firstOfMonth = new Date(year, month, 1);
    const leadBlanks = (firstOfMonth.getDay() + 6) % 7; // Monday-first
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const checkInDate = parseIso(props.checkIn);
    const checkOutDate = parseIso(props.checkOut);

    const cells = [];
    for (let i = 0; i < leadBlanks; i++) cells.push(null);

    for (let d = 1; d <= daysInMonth; d++) {
        const date = new Date(year, month, d);
        const iso = isoOf(date);

        cells.push({
            date,
            dayNum: d,
            isPast: date < today,
            isOccupied: occupied.value.has(iso),
            isCheckIn: iso === props.checkIn,
            isCheckOut: iso === props.checkOut,
            isInRange: Boolean(checkInDate && checkOutDate && date > checkInDate && date < checkOutDate),
        });
    }

    while (cells.length % 7 !== 0) cells.push(null);

    return cells;
});

function goToPrevMonth() {
    if (atCurrentMonth.value) return;
    viewMonth.value = new Date(viewMonth.value.getFullYear(), viewMonth.value.getMonth() - 1, 1);
}

function goToNextMonth() {
    viewMonth.value = new Date(viewMonth.value.getFullYear(), viewMonth.value.getMonth() + 1, 1);
}

function pickCheckIn() {
    emit('update:checkIn', '');
    emit('update:checkOut', '');
}

function pickCheckOut() {
    if (props.checkIn) emit('update:checkOut', '');
}

function selectDate(date) {
    if (date < today || occupied.value.has(isoOf(date))) return;

    const iso = isoOf(date);
    const checkInDate = parseIso(props.checkIn);

    if (!checkInDate || props.checkOut) {
        emit('update:checkIn', iso);
        emit('update:checkOut', '');
        return;
    }

    if (date <= checkInDate || rangeHasOccupied(checkInDate, date)) {
        emit('update:checkIn', iso);
        emit('update:checkOut', '');
        return;
    }

    emit('update:checkOut', iso);
}

async function fetchOccupied() {
    if (!props.apartmentId) {
        occupied.value = new Set();
        return;
    }

    loading.value = true;
    try {
        const from = isoOf(today);
        const to = isoOf(new Date(today.getFullYear() + 1, today.getMonth() + 6, today.getDate()));
        const res = await apiClient.get(`/apartments/${props.apartmentId}/periods?from=${from}&to=${to}`);
        const periods = res?.data?.periods ?? [];

        const set = new Set();
        for (const period of periods) {
            const start = parseIso(period.start_date);
            const end = parseIso(period.end_date);
            if (!start || !end) continue;

            const cursor = new Date(start);
            while (cursor < end) {
                set.add(isoOf(cursor));
                cursor.setDate(cursor.getDate() + 1);
            }
        }
        occupied.value = set;
    } catch {
        occupied.value = new Set();
    } finally {
        loading.value = false;
    }
}

watch(() => props.apartmentId, fetchOccupied, { immediate: true });
</script>

<style scoped>
.drc {
    display: flex;
    flex-direction: column;
    gap: 12px;
    grid-column: 1 / -1;
}

.drc__boxes {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.drc__box {
    display: flex;
    flex-direction: column;
    gap: 4px;
    align-items: flex-start;
    padding: 10px 14px;
    border: 1px solid var(--vs-border-input, var(--vs-border));
    border-radius: var(--vs-radius-sm);
    background: var(--vs-surface);
    cursor: pointer;
    text-align: left;
}

.drc__box--active {
    border-color: var(--vs-accent);
    box-shadow: inset 0 0 0 1px var(--vs-accent);
}

.drc__box-label {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    color: #8a9187;
}

.drc__box-value {
    font-size: 14px;
    color: var(--vs-text);
}

.drc__nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.drc__nav-btn {
    width: 28px;
    height: 28px;
    border: 1px solid var(--vs-border);
    border-radius: var(--vs-radius-sm);
    background: var(--vs-surface);
    cursor: pointer;
    font-size: 15px;
    line-height: 1;
}

.drc__nav-btn:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}

.drc__nav-label {
    font-weight: 700;
    font-size: 14px;
    color: var(--vs-text);
}

.drc__weekdays,
.drc__grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 4px;
}

.drc__weekdays span {
    text-align: center;
    font-size: 11px;
    font-weight: 700;
    color: #8a9187;
    text-transform: uppercase;
}

.drc__day {
    aspect-ratio: 1;
    border: none;
    border-radius: var(--vs-radius-sm);
    background: var(--vs-panel-sand);
    color: var(--vs-text);
    font-size: 13px;
    cursor: pointer;
}

.drc__day--blank {
    background: transparent;
    cursor: default;
}

.drc__day--past {
    opacity: 0.35;
    cursor: not-allowed;
}

.drc__day--occupied {
    background: color-mix(in srgb, #c0392b 18%, var(--vs-panel-sand));
    color: #a13a2c;
    cursor: not-allowed;
    text-decoration: line-through;
}

.drc__day--inrange {
    background: color-mix(in srgb, var(--vs-accent) 18%, var(--vs-surface));
}

.drc__day--checkin,
.drc__day--checkout {
    background: var(--vs-accent);
    color: #fff;
    font-weight: 700;
}

.drc__hint {
    margin: 0;
    font-size: 12px;
    color: #8a9187;
    display: flex;
    align-items: center;
    gap: 6px;
}

.drc__legend-dot {
    display: inline-block;
    width: 9px;
    height: 9px;
    border-radius: 50%;
    margin-left: 8px;
}

.drc__legend-dot:first-child {
    margin-left: 0;
}

.drc__legend-dot--occupied {
    background: #c0392b;
}

.drc__legend-dot--selected {
    background: var(--vs-accent);
}
</style>
