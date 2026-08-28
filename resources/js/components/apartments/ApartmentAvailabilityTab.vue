<template>
    <div class="host-apt-avail">
        <div v-if="loading" class="host-loading">Loading availability…</div>

        <div v-else class="host-apt-avail__grid">
            <div class="host-apt-avail__list-card host-panel">
                <h2 class="host-apt-avail__card-title">Active / upcoming</h2>

                <ul v-if="listPeriods.length" class="host-apt-avail__periods">
                    <li v-for="period in listPeriods" :key="period.id">
                        <button
                            type="button"
                            class="host-apt-avail__period"
                            :class="[
                                `host-apt-avail__period--${period.period_type}`,
                                {
                                    'host-apt-avail__period--active': selectedPeriod?.id === period.id,
                                    'host-apt-avail__period--hover': hoverId === period.id,
                                },
                            ]"
                            @mouseenter="onPeriodHover(period, $event)"
                            @mousemove="onPeriodHover(period, $event)"
                            @mouseleave="onPeriodLeave"
                            @click="selectPeriod(period)"
                        >
                            <span class="host-apt-avail__period-range">
                                {{ formatPeriodRange(period.start_date, period.end_date) }}
                            </span>
                            <span class="host-apt-avail__period-sub">{{ period.label }}</span>
                            <span class="host-apt-avail__period-chevron" aria-hidden="true">›</span>
                        </button>
                    </li>
                </ul>

                <p v-else class="host-apt-avail__empty">No active or upcoming periods.</p>

                <button type="button" class="host-apt-avail__add-btn" @click="emit('add-period')">
                    + Add period
                </button>
            </div>

            <div class="host-apt-avail__right host-panel">
                <template v-if="selectedPeriod">
                    <div class="host-apt-avail__detail">
                        <button
                            type="button"
                            class="host-apt-avail__detail-close"
                            aria-label="Close detail"
                            @click="selectedPeriod = null"
                        >
                            ✕
                        </button>

                        <div class="host-apt-avail__detail-head">
                            <span
                                class="host-apt-avail__detail-dot"
                                :style="{ background: periodColor(selectedPeriod.period_type) }"
                            />
                            <span
                                class="host-apt-avail__detail-type"
                                :style="{ color: periodColor(selectedPeriod.period_type) }"
                            >
                                {{ periodLabel(selectedPeriod.period_type) }}
                            </span>
                            <h3 class="host-apt-avail__detail-range">
                                {{ formatPeriodRange(selectedPeriod.start_date, selectedPeriod.end_date) }}
                            </h3>
                        </div>

                        <div v-if="selectedPeriod.period_type === 'blocked'" class="host-apt-avail__block-info">
                            <h4 class="host-apt-avail__group-title">About the block</h4>
                            <p class="host-apt-avail__block-text">
                                These dates are blocked and unavailable for new bookings.
                                {{ selectedPeriod.block_reason || selectedPeriod.note || 'No reason provided.' }}
                            </p>
                        </div>

                        <div v-else class="host-apt-avail__detail-body">
                            <div class="host-apt-avail__detail-main">
                                <section class="host-apt-avail__group">
                                    <h4 class="host-apt-avail__group-title">Stay</h4>
                                    <div class="host-apt-avail__fields">
                                        <div class="host-apt-avail__field">
                                            <span class="host-apt-avail__field-label">Guest</span>
                                            <span class="host-apt-avail__field-value">{{
                                                selectedPeriod.stay?.guest ?? '—'
                                            }}</span>
                                        </div>
                                        <div class="host-apt-avail__field">
                                            <span class="host-apt-avail__field-label">Nights</span>
                                            <span class="host-apt-avail__field-value">{{
                                                selectedPeriod.nights
                                            }}</span>
                                        </div>
                                        <div class="host-apt-avail__field">
                                            <span class="host-apt-avail__field-label">Check-in</span>
                                            <span class="host-apt-avail__field-value">{{
                                                formatDate(selectedPeriod.stay?.check_in)
                                            }}</span>
                                        </div>
                                        <div class="host-apt-avail__field">
                                            <span class="host-apt-avail__field-label">Check-out</span>
                                            <span class="host-apt-avail__field-value">{{
                                                formatDate(selectedPeriod.stay?.check_out)
                                            }}</span>
                                        </div>
                                    </div>
                                </section>

                                <section class="host-apt-avail__group">
                                    <h4 class="host-apt-avail__group-title">Extras &amp; discount</h4>
                                    <div class="host-apt-avail__fields">
                                        <div class="host-apt-avail__field">
                                            <span class="host-apt-avail__field-label">Cleaning</span>
                                            <span class="host-apt-avail__field-value">{{
                                                formatOptionalVnd(selectedPeriod.extras?.cleaning)
                                            }}</span>
                                        </div>
                                        <div class="host-apt-avail__field">
                                            <span class="host-apt-avail__field-label">Extra cleaning</span>
                                            <span class="host-apt-avail__field-value">{{
                                                formatOptionalVnd(selectedPeriod.extras?.extra_cleaning, 'None')
                                            }}</span>
                                        </div>
                                        <div class="host-apt-avail__field">
                                            <span class="host-apt-avail__field-label">Discount code</span>
                                            <span class="host-apt-avail__field-value">{{
                                                selectedPeriod.extras?.discount_code || '—'
                                            }}</span>
                                        </div>
                                        <div class="host-apt-avail__field">
                                            <span class="host-apt-avail__field-label">Commission</span>
                                            <span class="host-apt-avail__field-value">{{
                                                selectedPeriod.extras?.commission || 'Cash points (3%)'
                                            }}</span>
                                        </div>
                                    </div>
                                </section>

                                <section class="host-apt-avail__group">
                                    <h4 class="host-apt-avail__group-title">Channel &amp; reference</h4>
                                    <div class="host-apt-avail__fields host-apt-avail__fields--passive">
                                        <div class="host-apt-avail__field">
                                            <span class="host-apt-avail__field-label">Channel</span>
                                            <span class="host-apt-avail__field-value host-apt-avail__field-value--sm">{{
                                                selectedPeriod.channel?.name ?? '—'
                                            }}</span>
                                        </div>
                                        <div class="host-apt-avail__field">
                                            <span class="host-apt-avail__field-label">Reference</span>
                                            <span class="host-apt-avail__field-value host-apt-avail__field-value--sm">{{
                                                selectedPeriod.channel?.reference || '—'
                                            }}</span>
                                        </div>
                                        <div class="host-apt-avail__field">
                                            <span class="host-apt-avail__field-label">Registered by</span>
                                            <span class="host-apt-avail__field-value host-apt-avail__field-value--sm">{{
                                                selectedPeriod.channel?.registered_by ?? '—'
                                            }}</span>
                                        </div>
                                    </div>
                                </section>

                                <section
                                    v-if="selectedPeriod.source_type === 'period'"
                                    class="host-apt-avail__group"
                                >
                                    <h4 class="host-apt-avail__group-title">Internal note</h4>
                                    <textarea
                                        v-model="noteDraft"
                                        class="host-textarea"
                                        rows="3"
                                        placeholder="Add an internal note…"
                                    />
                                </section>
                            </div>

                            <aside v-if="selectedPeriod.finance" class="host-apt-avail__finance">
                                <h4 class="host-apt-avail__group-title">Finance</h4>

                                <div class="host-apt-avail__finance-block">
                                    <span class="host-apt-avail__finance-label">Income</span>
                                    <div class="host-apt-avail__finance-row">
                                        <span
                                            >Nightly rate ({{ selectedPeriod.nights }} ×
                                            {{ formatVnd(selectedPeriod.finance.nightly_rate) }})</span
                                        >
                                        <span>{{ formatVnd(selectedPeriod.finance.room_total) }}</span>
                                    </div>
                                    <div class="host-apt-avail__finance-row">
                                        <span>Cleaning</span>
                                        <span>{{ formatVnd(selectedPeriod.finance.cleaning) }}</span>
                                    </div>
                                    <div
                                        v-if="selectedPeriod.finance.discount"
                                        class="host-apt-avail__finance-row host-apt-avail__finance-row--negative"
                                    >
                                        <span>Discount</span>
                                        <span>−{{ formatVnd(selectedPeriod.finance.discount) }}</span>
                                    </div>
                                    <div class="host-apt-avail__finance-row host-apt-avail__finance-row--emph">
                                        <span>Effective GMV</span>
                                        <span>{{ formatVnd(selectedPeriod.finance.gmv) }}</span>
                                    </div>
                                </div>

                                <div class="host-apt-avail__finance-block">
                                    <span class="host-apt-avail__finance-label">Split</span>
                                    <div class="host-apt-avail__finance-row host-apt-avail__finance-row--negative">
                                        <span>Platform fee 5%</span>
                                        <span>−{{ formatVnd(selectedPeriod.finance.platform_fee) }}</span>
                                    </div>
                                    <div class="host-apt-avail__finance-row host-apt-avail__finance-row--negative">
                                        <span>Cash points 3%</span>
                                        <span>−{{ formatVnd(selectedPeriod.finance.cash_points) }}</span>
                                    </div>
                                    <div class="host-apt-avail__finance-row host-apt-avail__finance-row--emph">
                                        <span>Host net</span>
                                        <span>{{ formatVnd(selectedPeriod.finance.host_net) }}</span>
                                    </div>
                                </div>

                                <div class="host-apt-avail__finance-block">
                                    <span class="host-apt-avail__finance-label">Vietstays net</span>
                                    <div class="host-apt-avail__finance-row">
                                        <span>Platform fee</span>
                                        <span>{{ formatVnd(selectedPeriod.finance.platform_fee) }}</span>
                                    </div>
                                    <div class="host-apt-avail__finance-row host-apt-avail__finance-row--negative">
                                        <span>Cash points</span>
                                        <span>−{{ formatVnd(selectedPeriod.finance.cash_points) }}</span>
                                    </div>
                                    <div class="host-apt-avail__finance-row host-apt-avail__finance-row--emph">
                                        <span>Vietstays net</span>
                                        <span>{{ formatVnd(selectedPeriod.finance.vietstays_net) }}</span>
                                    </div>
                                </div>

                                <div class="host-apt-avail__detail-actions">
                                    <button
                                        v-if="selectedPeriod.source_type === 'booking'"
                                        type="button"
                                        class="host-btn host-btn--accent"
                                        @click="goToBooking(selectedPeriod.source_id)"
                                    >
                                        Edit booking
                                    </button>
                                    <template v-else>
                                        <button
                                            type="button"
                                            class="host-btn host-btn--ghost"
                                            :disabled="deleting"
                                            @click="deletePeriod"
                                        >
                                            {{ deleting ? 'Deleting…' : 'Delete' }}
                                        </button>
                                        <button
                                            type="button"
                                            class="host-btn host-btn--primary"
                                            :disabled="savingNote"
                                            @click="saveNote"
                                        >
                                            {{ savingNote ? 'Saving…' : 'Save note' }}
                                        </button>
                                    </template>
                                </div>
                            </aside>
                        </div>

                        <div
                            v-if="selectedPeriod.period_type === 'blocked'"
                            class="host-apt-avail__detail-actions host-apt-avail__detail-actions--block"
                        >
                            <button
                                type="button"
                                class="host-btn host-btn--ghost"
                                :disabled="deleting"
                                @click="deletePeriod"
                            >
                                {{ deleting ? 'Deleting…' : 'Delete block' }}
                            </button>
                        </div>
                    </div>
                </template>

                <template v-else>
                    <div class="host-apt-avail__calendars">
                        <div v-for="month in calendarMonths" :key="month.label" class="host-apt-avail__month">
                            <h3 class="host-apt-avail__month-label">{{ month.label }}</h3>
                            <div class="host-apt-avail__weekdays">
                                <span v-for="d in weekdayLabels" :key="d">{{ d }}</span>
                            </div>
                            <div class="host-apt-avail__cells">
                                <span
                                    v-for="cell in month.cells"
                                    :key="cell.key"
                                    class="host-apt-avail__cell"
                                    :class="cellClass(cell)"
                                >
                                    {{ cell.empty ? '' : cell.day }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="host-apt-avail__legend">
                        <span
                            v-for="item in legendItems"
                            :key="item.type"
                            class="host-apt-avail__legend-item"
                        >
                            <span class="host-apt-avail__legend-dot" :style="{ background: item.color }" />
                            {{ item.label }}
                        </span>
                    </div>
                </template>
            </div>
        </div>

        <div
            v-if="tooltip.visible"
            class="host-apt-avail__tooltip"
            :style="{ left: `${tooltip.x}px`, top: `${tooltip.y}px` }"
        >
            {{ tooltip.text }}
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/api/client';
import { useToast } from '@/composables/useToast';
import {
    PERIOD_COLORS,
    PERIOD_LABELS,
    buildMonthGrid,
    cellPeriodType,
    formatPeriodRange,
    startOfDay,
    upcomingPeriods,
} from '@/utils/apartment-availability';
import { formatDate, formatVnd } from '@/utils/format';

const props = defineProps({
    apartmentId: { type: [String, Number], required: true },
});

const emit = defineEmits(['add-period', 'live-status']);

const router = useRouter();
const toast = useToast();

const loading = ref(true);
const periods = ref([]);
const hoverId = ref(null);
const selectedPeriod = ref(null);
const noteDraft = ref('');
const savingNote = ref(false);
const deleting = ref(false);

const tooltip = ref({ visible: false, x: 0, y: 0, text: '' });

const weekdayLabels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const legendItems = [
    { type: 'vietstays', label: PERIOD_LABELS.vietstays, color: PERIOD_COLORS.vietstays },
    { type: 'blocked', label: PERIOD_LABELS.blocked, color: PERIOD_COLORS.blocked },
    { type: 'external', label: PERIOD_LABELS.external, color: PERIOD_COLORS.external },
];

const listPeriods = computed(() => upcomingPeriods(periods.value));

const calendarMonths = computed(() => {
    const today = startOfDay(new Date());
    const next = new Date(today.getFullYear(), today.getMonth() + 1, 1);
    return [buildMonthGrid(today.getFullYear(), today.getMonth()), buildMonthGrid(next.getFullYear(), next.getMonth())];
});

function periodColor(type) {
    return PERIOD_COLORS[type] ?? PERIOD_COLORS.vietstays;
}

function periodLabel(type) {
    return PERIOD_LABELS[type] ?? type;
}

function formatOptionalVnd(value, fallback = '—') {
    if (value == null || value === '') return fallback;
    return formatVnd(value);
}

function cellClass(cell) {
    if (cell.empty) return 'host-apt-avail__cell--empty';

    const type = cellPeriodType(periods.value, cell.iso, hoverId.value);
    const classes = [];

    if (type === 'dimmed') classes.push('host-apt-avail__cell--dimmed');
    else if (type) classes.push(`host-apt-avail__cell--${type}`);

    if (cell.isToday) classes.push('host-apt-avail__cell--today');

    return classes;
}

function onPeriodHover(period, event) {
    hoverId.value = period.id;
    tooltip.value = {
        visible: true,
        x: event.clientX + 14,
        y: event.clientY + 14,
        text: periodLabel(period.period_type),
    };
}

function onPeriodLeave() {
    hoverId.value = null;
    tooltip.value = { ...tooltip.value, visible: false };
}

function selectPeriod(period) {
    selectedPeriod.value = period;
    noteDraft.value = period.note ?? '';
}

function goToBooking(id) {
    router.push({ name: 'booking-detail', params: { id } });
}

async function loadPeriods() {
    loading.value = true;

    try {
        const res = await apiClient.get(`/apartments/${props.apartmentId}/periods`);
        const data = res?.data ?? {};
        periods.value = Array.isArray(data.periods) ? data.periods : [];

        if (data.live_status) {
            emit('live-status', data.live_status);
        }
    } catch {
        periods.value = [];
        toast.show('Could not load availability.');
    } finally {
        loading.value = false;
    }
}

async function saveNote() {
    if (!selectedPeriod.value || selectedPeriod.value.source_type !== 'period') return;

    savingNote.value = true;

    try {
        const periodId = selectedPeriod.value.source_id;
        const res = await apiClient.patch(`/apartments/${props.apartmentId}/periods/${periodId}`, {
            note: noteDraft.value,
        });
        const updated = res?.data;
        if (updated) {
            const idx = periods.value.findIndex((p) => p.id === selectedPeriod.value.id);
            if (idx >= 0) periods.value[idx] = updated;
            selectedPeriod.value = updated;
        }
        toast.show('Note saved.');
    } catch (err) {
        toast.show(err.message ?? 'Could not save note.');
    } finally {
        savingNote.value = false;
    }
}

async function deletePeriod() {
    if (!selectedPeriod.value || selectedPeriod.value.source_type !== 'period') return;

    deleting.value = true;

    try {
        const periodId = selectedPeriod.value.source_id;
        await apiClient.delete(`/apartments/${props.apartmentId}/periods/${periodId}`);
        periods.value = periods.value.filter((p) => p.id !== selectedPeriod.value.id);
        selectedPeriod.value = null;
        toast.show('Period removed.');
        await loadPeriods();
    } catch (err) {
        toast.show(err.message ?? 'Could not delete period.');
    } finally {
        deleting.value = false;
    }
}

onMounted(loadPeriods);
watch(() => props.apartmentId, loadPeriods);
</script>
