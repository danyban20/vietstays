<template>
    <div class="host-bk-detail">
        <div v-if="loading" class="host-loading">{{ t('bookingDetail.loading') }}</div>

        <template v-else>
            <header class="host-bk-detail__page-header">
                <span class="host-bk-detail__page-icon" aria-hidden="true">
                    <svg width="31" height="31" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="5" width="18" height="16" rx="2.5" />
                        <path d="M3 10h18M8 3v4M16 3v4" />
                        <path d="M8.6 15.2l2.2 2.2 4.4-4.6" />
                    </svg>
                </span>
                <div class="host-bk-detail__page-title-wrap">
                    <div class="host-bk-detail__page-title">
                        <span class="host-bk-detail__page-id">{{ displayId }}</span>
                        <span class="host-bk-detail__page-sep">|</span>
                        <span class="host-bk-detail__page-guest">{{ editForm.guest_name || booking.guest }}</span>
                    </div>
                    <div class="host-bk-detail__page-meta">
                        <span class="host-bk-detail__page-pill">{{ t('bookingDetail.bookingPill') }}</span>
                        <span class="host-bk-detail__page-apt">{{ apartmentName }}</span>
                        <span v-if="editForm.status === 'cancelled'" class="host-bk-detail__cancelled-pill">
                            {{ t('bookingDetail.cancelledPill') }}
                        </span>
                    </div>
                </div>
                <button
                    v-if="editForm.status !== 'cancelled'"
                    type="button"
                    class="host-bk-detail__cancel-btn"
                    :disabled="cancelling"
                    @click="cancelBooking"
                >
                    {{ cancelling ? t('bookingDetail.cancelling') : t('bookingDetail.cancelBooking') }}
                </button>
                <router-link :to="{ name: 'bookings' }" class="host-bk-detail__back-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 5l-7 7 7 7" />
                    </svg>
                    {{ t('bookingDetail.back') }}
                </router-link>
            </header>

            <div ref="scrollEl" class="host-bk-detail__scroll">
                <div class="host-bk-detail__layout">
                    <div class="host-bk-detail__main-col">
                        <section class="host-bk-detail__main-card">
                            <div class="host-bk-detail__hero-wrap">
                                <img
                                    v-if="heroImage"
                                    :src="heroImage"
                                    alt=""
                                    class="host-bk-detail__hero-img"
                                />
                                <div v-else class="host-bk-detail__hero-placeholder">🏠</div>
                            </div>

                            <div class="host-bk-detail__apt-bar">
                                <div class="host-bk-detail__apt-bar-name">{{ apartmentName }}</div>
                                <router-link
                                    v-if="booking.apartment_id"
                                    :to="{ name: 'apartment-detail', params: { id: booking.apartment_id } }"
                                    class="host-bk-detail__edit-apt-btn"
                                >
                                    {{ t('bookingDetail.editApartment') }}
                                </router-link>
                            </div>

                            <div class="host-bk-detail__tags">
                                <span v-if="matchCode" class="host-bk-detail__tag host-bk-detail__tag--code">{{ matchCode }}</span>
                                <span class="host-bk-detail__tag host-bk-detail__tag--booking">{{ displayId }}</span>
                                <span v-if="receivedLabel">{{ receivedLabel }}</span>
                            </div>

                            <div v-if="showRejectAlert" class="host-bk-detail__alert">
                                <span class="host-bk-detail__alert-icon" aria-hidden="true">⚑</span>
                                <p class="host-bk-detail__alert-text">
                                    {{ t('bookingDetail.rejectDeadline', { countdown: rejectCountdown }) }}
                                </p>
                            </div>

                            <h2 class="host-bk-detail__section-label">{{ t('bookingDetail.detailsTitle') }}</h2>

                            <div class="host-bk-detail__fields">
                                <div
                                    v-for="field in detailFields"
                                    :key="field.id"
                                    class="host-bk-detail__field"
                                    :class="{
                                        'host-bk-detail__field--editable': field.editable,
                                        'host-bk-detail__field--active': editingField === field.id,
                                    }"
                                >
                                    <div class="host-bk-detail__field-head">
                                        <span class="host-bk-detail__field-label">{{ field.label }}</span>
                                        <button
                                            v-if="field.editable && editingField !== field.id"
                                            type="button"
                                            class="host-bk-detail__field-edit"
                                            aria-label="Edit"
                                            @click="startEditField(field.id)"
                                        >
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                                            </svg>
                                        </button>
                                    </div>

                                    <template v-if="editingField === field.id">
                                        <input
                                            v-if="field.type === 'date'"
                                            v-model="editForm[field.model]"
                                            type="date"
                                            class="host-input host-bk-detail__field-input"
                                            @change="onDateFieldChange(field.id)"
                                        />
                                        <input
                                            v-else-if="field.type === 'number'"
                                            v-model.number="editForm[field.model]"
                                            type="number"
                                            min="1"
                                            class="host-input host-bk-detail__field-input"
                                            @keyup.enter="editingField = null"
                                        />
                                        <input
                                            v-else-if="field.type === 'nights'"
                                            v-model.number="nightsDraft"
                                            type="number"
                                            min="1"
                                            class="host-input host-bk-detail__field-input"
                                            @change="applyNightsDraft"
                                        />
                                        <button
                                            type="button"
                                            class="host-bk-detail__field-done"
                                            @click="editingField = null"
                                        >
                                            ✓
                                        </button>
                                    </template>
                                    <template v-else>
                                        <div class="host-bk-detail__field-value">{{ field.display }}</div>
                                        <div v-if="field.sub" class="host-bk-detail__field-sub">{{ field.sub }}</div>
                                    </template>
                                </div>
                            </div>
                        </section>

                        <section class="host-bk-detail__order-card">
                            <div class="host-bk-detail__order-head">
                                <h2 class="host-bk-detail__section-label host-bk-detail__section-label--inline">
                                    {{ t('bookingDetail.orderSummary') }}
                                </h2>
                            </div>

                            <div class="host-price-summary host-bk-detail__summary">
                                <div class="host-bk-detail__summary-row host-bk-detail__summary-row--large">
                                    <span>{{ t('bookingDetail.bookingTotal') }}</span>
                                    <span>{{ formatVnd(liveRoomTotal) }}</span>
                                </div>

                                <div v-if="booking.cleaning_fee" class="host-price-summary__row">
                                    <span>{{ t('bookingDetail.cleaningFee') }}</span>
                                    <span>{{ formatVnd(booking.cleaning_fee) }}</span>
                                </div>

                                <div
                                    v-if="booking.campaign_discount"
                                    class="host-price-summary__row host-price-summary__row--discount"
                                >
                                    <span>{{
                                        booking.promo_code
                                            ? `${t('bookingDetail.discount')} · ${booking.promo_code}`
                                            : t('bookingDetail.discount')
                                    }}</span>
                                    <span>− {{ formatVnd(booking.campaign_discount) }}</span>
                                </div>

                                <div class="host-bk-detail__summary-row host-bk-detail__summary-row--large host-bk-detail__summary-row--net">
                                    <span>{{ t('bookingDetail.netProfit') }}</span>
                                    <span>{{ formatVnd(liveHostNet) }}</span>
                                </div>

                                <div class="host-price-summary__row">
                                    <span>{{ booking.commission_label ?? t('bookingDetail.cashPoints') }}</span>
                                    <span>{{ formatVnd(booking.cash_points) }}</span>
                                </div>

                                <div class="host-price-summary__row">
                                    <span>{{ t('bookingDetail.platformFee') }}</span>
                                    <span>− {{ formatVnd(booking.platform_fee) }}</span>
                                </div>
                            </div>
                        </section>
                    </div>

                    <aside class="host-bk-detail__rail">
                        <section class="host-bk-detail__rail-card">
                            <h3 class="host-bk-detail__rail-title">{{ t('bookingDetail.overview') }}</h3>
                            <div class="host-bk-detail__rail-block">
                                <span class="host-bk-detail__rail-label">{{ t('bookingDetail.apartment') }}</span>
                                <span class="host-bk-detail__rail-value">{{ apartmentName }}</span>
                            </div>
                            <div class="host-bk-detail__rail-row">
                                <div class="host-bk-detail__rail-block">
                                    <span class="host-bk-detail__rail-label">{{ t('bookingDetail.totalAmount') }}</span>
                                    <span class="host-bk-detail__rail-value">{{ formatVnd(liveGuestTotal) }}</span>
                                </div>
                                <div class="host-bk-detail__rail-block">
                                    <span class="host-bk-detail__rail-label">{{ t('bookingDetail.period') }}</span>
                                    <span class="host-bk-detail__rail-value">{{ periodLabel }}</span>
                                </div>
                            </div>
                        </section>

                        <section class="host-bk-detail__rail-card">
                            <h3 class="host-bk-detail__rail-title">{{ t('bookingDetail.commissionDiscount') }}</h3>
                            <template v-if="hasCommission">
                                <div v-if="booking.promo_code" class="host-bk-detail__comm-name">{{ booking.promo_code }}</div>
                                <div v-if="booking.campaign_discount" class="host-bk-detail__comm-sub">
                                    {{ t('bookingDetail.discount') }} − {{ formatVnd(booking.campaign_discount) }}
                                </div>
                            </template>
                            <p v-else class="host-bk-detail__comm-empty">{{ t('bookingDetail.noCommission') }}</p>
                            <p class="host-bk-detail__comm-hint">{{ t('bookingDetail.editInOrderSummary') }}</p>
                        </section>

                        <section class="host-bk-detail__rail-card">
                            <h3 class="host-bk-detail__rail-title">{{ t('bookingDetail.note') }}</h3>
                            <template v-if="editingNote">
                                <textarea
                                    v-model="noteDraft"
                                    class="host-textarea host-bk-detail__note-input"
                                    rows="3"
                                />
                                <div class="host-bk-detail__note-actions">
                                    <button type="button" class="host-bk-detail__note-btn" @click="cancelNoteEdit">
                                        {{ t('common.cancel') }}
                                    </button>
                                    <button type="button" class="host-bk-detail__note-btn host-bk-detail__note-btn--primary" @click="saveNoteEdit">
                                        {{ t('common.save') }}
                                    </button>
                                </div>
                            </template>
                            <button
                                v-else
                                type="button"
                                class="host-bk-detail__note-text"
                                @click="startNoteEdit"
                            >
                                {{ editForm.note || t('bookingDetail.addNote') }}
                            </button>
                        </section>

                        <section class="host-bk-detail__rail-card host-bk-detail__rail-card--sand">
                            <h3 class="host-bk-detail__rail-title host-bk-detail__rail-title--upper">
                                {{ t('bookingDetail.accessTitle') }}
                            </h3>
                            <dl class="host-bk-detail__access-list">
                                <div>
                                    <dt>{{ t('bookingDetail.doorCode') }}</dt>
                                    <dd>{{ booking.access?.door_code || '—' }}</dd>
                                </div>
                                <div>
                                    <dt>{{ t('bookingDetail.wifi') }}</dt>
                                    <dd>{{ booking.access?.wifi_network || '—' }}</dd>
                                </div>
                                <div>
                                    <dt>{{ t('bookingDetail.password') }}</dt>
                                    <dd>{{ booking.access?.wifi_password || '—' }}</dd>
                                </div>
                            </dl>
                        </section>
                    </aside>
                </div>
            </div>

            <div
                class="host-bk-sticky-bar host-bk-sticky-bar--always"
                :class="{ 'host-bk-sticky-bar--dimmed': isScrolling }"
            >
                <div class="host-bk-sticky-bar__left">
                    <span class="host-bk-sticky-bar__status">
                        {{
                            dirty
                                ? t('bookingDetail.changesPending', pendingChangeCount, { count: pendingChangeCount })
                                : t('bookingDetail.noChangesPending')
                        }}
                    </span>
                </div>
                <div class="host-bk-sticky-bar__actions">
                    <button
                        v-if="dirty"
                        type="button"
                        class="host-btn host-btn--ghost host-bk-sticky-bar__reset"
                        @click="resetChanges"
                    >
                        {{ t('bookingDetail.resetChanges') }}
                    </button>
                    <button
                        type="button"
                        class="host-btn host-btn--sand"
                        :disabled="!dirty || saving"
                        @click="save(false)"
                    >
                        {{ saving ? t('bookingDetail.saving') : t('common.save') }}
                    </button>
                    <button
                        type="button"
                        class="host-btn host-btn--accent"
                        :disabled="!dirty || saving"
                        @click="save(true)"
                    >
                        {{ t('bookingDetail.saveAndSend') }}
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import apiClient from '@/api/client';
import { usePageTitle } from '@/composables/usePageTitle';
import { useToast } from '@/composables/useToast';
import { matchCodeFromApartmentName } from '@/utils/apartment-match-code';
import { addDays, isoDate, parseIso, startOfDay } from '@/utils/apartment-availability';
import { resolveApartmentImageUrl } from '@/utils/apartment-images';
import {
    formatCountdown,
    formatDateNumeric,
    formatPeriodShort,
    formatVnd,
    nightsBetween,
} from '@/utils/format';

const route = useRoute();
const toast = useToast();
const { t, locale } = useI18n();
const { setPageTitle, clearPageTitle } = usePageTitle();

const loading = ref(true);
const saving = ref(false);
const cancelling = ref(false);
const scrollEl = ref(null);
const editingField = ref(null);
const editingNote = ref(false);
const noteDraft = ref('');
const nightsDraft = ref(1);
const isScrolling = ref(false);
const rejectNow = ref(Date.now());

let scrollStopTimer = null;
let rejectTimer = null;

const bookingId = computed(() => route.params.id);

const booking = ref({
    check_in: '',
    check_out: '',
    nights: 0,
    guests: 0,
    guest: '',
    email: '',
    phone: '',
    status: 'pending',
    total: 0,
    apartment_detail: {},
    access: {},
});

const editForm = reactive({
    guest_name: '',
    email: '',
    phone: '',
    status: 'pending',
    check_in_date: '',
    check_out_date: '',
    guests: 1,
    note: '',
});

const snapshot = reactive({
    guest_name: '',
    email: '',
    phone: '',
    status: 'pending',
    check_in_date: '',
    check_out_date: '',
    guests: 1,
    note: '',
});

const apartmentName = computed(
    () => booking.value.apartment_detail?.name ?? booking.value.apartment ?? '—',
);

const displayId = computed(
    () => booking.value.display_id ?? (booking.value.booking_num ? `BK-${booking.value.booking_num}` : `BK-${bookingId.value}`),
);

const matchCode = computed(() => matchCodeFromApartmentName(apartmentName.value));

const liveNights = computed(
    () => nightsBetween(editForm.check_in_date, editForm.check_out_date) || booking.value.nights || 0,
);

const liveRoomTotal = computed(() => (booking.value.daily_rate ?? booking.value.price ?? 0) * liveNights.value);

const liveGuestTotal = computed(() => {
    const cleaning = booking.value.cleaning_fee ?? 0;
    const discount = booking.value.campaign_discount ?? 0;
    return Math.max(0, liveRoomTotal.value + cleaning - discount);
});

const liveHostNet = computed(() => {
    const gmv = liveGuestTotal.value;
    const platformFee = Math.round(gmv * 0.05);
    const cashPoints = Math.round(gmv * 0.03);
    return gmv - platformFee - cashPoints;
});

const heroImage = computed(() => {
    const src = booking.value.apartment_detail?.image;
    return src ? resolveApartmentImageUrl(src) : '';
});

const periodLabel = computed(() =>
    formatPeriodShort(editForm.check_in_date, editForm.check_out_date, locale.value),
);

const guestsBreakdown = computed(() => {
    const adults = booking.value.adults ?? editForm.guests;
    const children = booking.value.children ?? 0;

    if (children > 0) {
        return locale.value === 'no'
            ? `${adults} voksne, ${children} barn`
            : `${adults} adults, ${children} children`;
    }

    return locale.value === 'no' ? `${editForm.guests} voksne` : `${editForm.guests} adults`;
});

const roomType = computed(() => booking.value.apartment_detail?.type ?? '—');

const receivedLabel = computed(() => {
    if (!booking.value.created_at) return '';
    const time = booking.value.created_at_time;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const created = new Date(`${booking.value.created_at}T12:00:00`);
    created.setHours(0, 0, 0, 0);

    if (created.getTime() === today.getTime()) {
        return locale.value === 'no'
            ? `Mottatt i dag${time ? `, ${time}` : ''}`
            : `Received today${time ? `, ${time}` : ''}`;
    }

    return locale.value === 'no'
        ? `Mottatt ${formatDateNumeric(booking.value.created_at)}${time ? `, ${time}` : ''}`
        : `Received ${formatDateNumeric(booking.value.created_at)}${time ? `, ${time}` : ''}`;
});

const wcCount = computed(() => booking.value.apartment_detail?.num_bathrooms ?? '—');

const showRejectAlert = computed(
    () => editForm.status === 'pending' && Boolean(booking.value.reject_deadline),
);

const rejectCountdown = computed(() => {
    rejectNow.value;
    return formatCountdown(booking.value.reject_deadline);
});

const hasCommission = computed(
    () => Boolean(booking.value.promo_code || booking.value.campaign_discount),
);

const dirty = computed(
    () =>
        editForm.guest_name !== snapshot.guest_name ||
        editForm.email !== snapshot.email ||
        editForm.phone !== snapshot.phone ||
        editForm.status !== snapshot.status ||
        editForm.check_in_date !== snapshot.check_in_date ||
        editForm.check_out_date !== snapshot.check_out_date ||
        editForm.guests !== snapshot.guests ||
        editForm.note !== snapshot.note,
);

const pendingChangeCount = computed(() => {
    let count = 0;
    if (editForm.guest_name !== snapshot.guest_name) count += 1;
    if (editForm.email !== snapshot.email) count += 1;
    if (editForm.phone !== snapshot.phone) count += 1;
    if (editForm.status !== snapshot.status) count += 1;
    if (editForm.check_in_date !== snapshot.check_in_date) count += 1;
    if (editForm.check_out_date !== snapshot.check_out_date) count += 1;
    if (editForm.guests !== snapshot.guests) count += 1;
    if (editForm.note !== snapshot.note) count += 1;
    return count;
});

const detailFields = computed(() => [
    {
        id: 'check_in',
        label: t('bookingDetail.checkIn'),
        model: 'check_in_date',
        type: 'date',
        editable: true,
        display: formatDateNumeric(editForm.check_in_date),
    },
    {
        id: 'check_out',
        label: t('bookingDetail.checkOut'),
        model: 'check_out_date',
        type: 'date',
        editable: true,
        display: formatDateNumeric(editForm.check_out_date),
    },
    {
        id: 'nights',
        label: t('bookingDetail.nights'),
        model: null,
        type: 'nights',
        editable: true,
        display: String(liveNights.value),
    },
    {
        id: 'guests',
        label: t('bookingDetail.guests'),
        model: 'guests',
        type: 'number',
        editable: true,
        display: String(editForm.guests),
        sub: guestsBreakdown.value,
    },
    {
        id: 'wc',
        label: t('bookingDetail.wc'),
        model: null,
        type: 'static',
        editable: false,
        display: String(wcCount.value),
    },
    {
        id: 'room',
        label: t('bookingDetail.room'),
        model: null,
        type: 'static',
        editable: false,
        display: roomType.value,
    },
]);

function applySnapshotFromBooking(data) {
    editForm.guest_name = data.guest ?? '';
    editForm.email = data.email ?? '';
    editForm.phone = data.phone ?? '';
    editForm.status = data.status ?? 'pending';
    editForm.check_in_date = data.check_in ?? '';
    editForm.check_out_date = data.check_out ?? '';
    editForm.guests = data.guests ?? data.adults ?? 1;
    editForm.note = data.note ?? '';

    snapshot.guest_name = editForm.guest_name;
    snapshot.email = editForm.email;
    snapshot.phone = editForm.phone;
    snapshot.status = editForm.status;
    snapshot.check_in_date = editForm.check_in_date;
    snapshot.check_out_date = editForm.check_out_date;
    snapshot.guests = editForm.guests;
    snapshot.note = editForm.note;
}

function startEditField(fieldId) {
    editingField.value = fieldId;
    if (fieldId === 'nights') {
        nightsDraft.value = liveNights.value || 1;
    }
}

function startNoteEdit() {
    noteDraft.value = editForm.note;
    editingNote.value = true;
}

function cancelNoteEdit() {
    noteDraft.value = editForm.note;
    editingNote.value = false;
}

function saveNoteEdit() {
    editForm.note = noteDraft.value;
    editingNote.value = false;
}

function applyNightsDraft() {
    const checkIn = parseIso(editForm.check_in_date);
    const nights = Math.max(1, Number(nightsDraft.value) || 1);
    if (checkIn) {
        editForm.check_out_date = isoDate(addDays(checkIn, nights));
    }
    editingField.value = null;
}

function onDateFieldChange(fieldId) {
    if (fieldId === 'check_in') {
        const checkIn = parseIso(editForm.check_in_date);
        if (checkIn) {
            const nights = liveNights.value || 1;
            editForm.check_out_date = isoDate(addDays(checkIn, nights));
        }
    } else if (fieldId === 'check_out') {
        const checkIn = parseIso(editForm.check_in_date);
        const checkOut = parseIso(editForm.check_out_date);
        if (checkIn && checkOut && checkOut <= checkIn) {
            editForm.check_out_date = isoDate(addDays(checkIn, 1));
        }
    }
}

function resetChanges() {
    editForm.guest_name = snapshot.guest_name;
    editForm.email = snapshot.email;
    editForm.phone = snapshot.phone;
    editForm.status = snapshot.status;
    editForm.check_in_date = snapshot.check_in_date;
    editForm.check_out_date = snapshot.check_out_date;
    editForm.guests = snapshot.guests;
    editForm.note = snapshot.note;
    editingField.value = null;
    editingNote.value = false;
}

function onScroll() {
    isScrolling.value = true;
    clearTimeout(scrollStopTimer);
    scrollStopTimer = setTimeout(() => {
        isScrolling.value = false;
    }, 150);
}

async function loadBooking() {
    loading.value = true;

    try {
        const res = await apiClient.get(`/bookings/${bookingId.value}`);
        booking.value = res?.data ?? {};
        applySnapshotFromBooking(booking.value);
        setPageTitle(`${displayId.value} | ${editForm.guest_name || booking.value.guest || 'Booking'}`);
    } catch {
        booking.value = {};
        toast.show(t('bookingDetail.loadFailed'));
    } finally {
        loading.value = false;
    }
}

async function save(notifyGuest) {
    saving.value = true;

    try {
        const payload = {
            guest_name: editForm.guest_name,
            email: editForm.email,
            phone: editForm.phone,
            status: editForm.status,
            guests: editForm.guests,
            check_in_date: editForm.check_in_date,
            check_out_date: editForm.check_out_date,
            note: editForm.note,
            notify_guest: notifyGuest ? 'email' : 'none',
        };

        const res = await apiClient.put(`/bookings/${bookingId.value}`, payload);
        booking.value = res?.data ?? booking.value;
        applySnapshotFromBooking(booking.value);
        editingField.value = null;
        editingNote.value = false;
        toast.show(res?.message ?? t('bookingDetail.saved'));
    } catch (err) {
        toast.show(err.message ?? t('bookingDetail.saveFailed'));
    } finally {
        saving.value = false;
    }
}

async function cancelBooking() {
    if (! window.confirm(t('bookingDetail.cancelConfirm'))) {
        return;
    }

    cancelling.value = true;

    try {
        const res = await apiClient.put(`/bookings/${bookingId.value}`, {
            status: 'cancelled',
            notify_guest: 'none',
        });
        booking.value = res?.data ?? booking.value;
        applySnapshotFromBooking(booking.value);
        editingField.value = null;
        editingNote.value = false;
        toast.show(t('bookingDetail.cancelSuccess'));
    } catch (err) {
        toast.show(err.message ?? t('bookingDetail.cancelFailed'));
    } finally {
        cancelling.value = false;
    }
}

onMounted(() => {
    loadBooking();
    scrollEl.value?.addEventListener('scroll', onScroll, { passive: true });
    rejectTimer = setInterval(() => {
        rejectNow.value = Date.now();
    }, 60000);
});

onUnmounted(() => {
    scrollEl.value?.removeEventListener('scroll', onScroll);
    clearTimeout(scrollStopTimer);
    clearInterval(rejectTimer);
    clearPageTitle();
});

watch(bookingId, loadBooking);
</script>
