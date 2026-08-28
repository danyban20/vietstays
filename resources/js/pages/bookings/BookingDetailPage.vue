<template>
    <div class="host-bk-detail">
        <div v-if="loading" class="host-loading">Loading booking…</div>

        <template v-else>
            <div ref="scrollEl" class="host-bk-detail__scroll">
                <header class="host-bk-detail__hero">
                    <div class="host-bk-detail__hero-media">
                        <img
                            v-if="heroImage"
                            :src="heroImage"
                            alt=""
                            class="host-bk-detail__hero-img"
                        />
                        <div v-else class="host-bk-detail__hero-placeholder">🏠</div>
                    </div>
                    <div class="host-bk-detail__hero-body">
                        <router-link
                            v-if="booking.apartment_id"
                            :to="{ name: 'apartment-detail', params: { id: booking.apartment_id } }"
                            class="host-bk-detail__apt-link"
                        >
                            {{ booking.apartment_detail?.name ?? booking.apartment }}
                        </router-link>
                        <h1 v-else class="host-bk-detail__apt-name">
                            {{ booking.apartment_detail?.name ?? booking.apartment }}
                        </h1>
                        <p class="host-bk-detail__meta">
                            Booking {{ booking.booking_num ? `#${booking.booking_num}` : `#${bookingId}` }}
                            <span v-if="booking.created_at"> · Created {{ formatDate(booking.created_at) }}</span>
                        </p>
                        <p v-if="locationLabel" class="host-bk-detail__location">{{ locationLabel }}</p>
                    </div>
                </header>

                <div class="host-bk-detail__key-fields">
                    <div v-for="field in keyFields" :key="field.id" class="host-bk-detail__key-field">
                        <span class="host-bk-detail__key-label">{{ field.label }}</span>
                        <div class="host-bk-detail__key-value-row">
                            <template v-if="editingField === field.id">
                                <input
                                    v-if="field.type === 'date'"
                                    v-model="editForm[field.model]"
                                    type="date"
                                    class="host-input host-bk-detail__key-input"
                                    @change="onDateFieldChange(field.id)"
                                />
                                <input
                                    v-else-if="field.type === 'number'"
                                    v-model.number="editForm[field.model]"
                                    type="number"
                                    min="1"
                                    class="host-input host-bk-detail__key-input"
                                />
                                <input
                                    v-else-if="field.type === 'nights'"
                                    v-model.number="nightsDraft"
                                    type="number"
                                    min="1"
                                    class="host-input host-bk-detail__key-input"
                                    @change="applyNightsDraft"
                                />
                                <button
                                    type="button"
                                    class="host-bk-detail__key-done"
                                    aria-label="Done editing"
                                    @click="editingField = null"
                                >
                                    ✓
                                </button>
                            </template>
                            <template v-else>
                                <span class="host-bk-detail__key-value">{{ field.display }}</span>
                                <button
                                    type="button"
                                    class="host-bk-detail__key-edit"
                                    aria-label="Edit"
                                    @click="startEditField(field.id)"
                                >
                                    ✎
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="host-bk-detail__grid">
                    <div class="host-bk-detail__main">
                        <section class="host-panel host-bk-detail__card">
                            <h2 class="host-bk-detail__card-title">Order summary</h2>
                            <div class="host-price-summary host-bk-detail__summary">
                                <div class="host-price-summary__row">
                                    <span
                                        >Nightly rate × {{ liveNights }} night{{
                                            liveNights === 1 ? '' : 's'
                                        }}</span
                                    >
                                    <span>{{ formatVnd(liveRoomTotal) }}</span>
                                </div>
                                <div v-if="booking.cleaning_fee" class="host-price-summary__row">
                                    <span>Cleaning fee</span>
                                    <span>{{ formatVnd(booking.cleaning_fee) }}</span>
                                </div>
                                <div v-if="booking.extra_cleaning_fee" class="host-price-summary__row">
                                    <span>Extra cleaning</span>
                                    <span>{{ formatVnd(booking.extra_cleaning_fee) }}</span>
                                </div>
                                <div
                                    v-if="booking.campaign_discount"
                                    class="host-price-summary__row host-price-summary__row--discount"
                                >
                                    <span>{{
                                        booking.promo_code
                                            ? `Discount · ${booking.promo_code}`
                                            : 'Discount'
                                    }}</span>
                                    <span>− {{ formatVnd(booking.campaign_discount) }}</span>
                                </div>
                                <div class="host-price-summary__row host-price-summary__row--total">
                                    <span>Total the guest pays</span>
                                    <span>{{ formatVnd(liveGuestTotal) }}</span>
                                </div>
                                <div class="host-price-summary__row">
                                    <span>{{ booking.commission_label ?? 'Cash points (3%)' }}</span>
                                    <span>{{ formatVnd(booking.cash_points) }}</span>
                                </div>
                                <div class="host-price-summary__row">
                                    <span>Platform fee (5%)</span>
                                    <span>− {{ formatVnd(booking.platform_fee) }}</span>
                                </div>
                                <div class="host-price-summary__row host-price-summary__row--emph">
                                    <span>Net to you</span>
                                    <span>{{ formatVnd(liveHostNet) }}</span>
                                </div>
                            </div>
                        </section>

                        <section class="host-panel host-bk-detail__card">
                            <h2 class="host-bk-detail__card-title">Stay timeline</h2>
                            <div class="host-bk-detail__timeline">
                                <div class="host-bk-detail__timeline-track">
                                    <div
                                        class="host-bk-detail__timeline-stay"
                                        :style="timelineStayStyle"
                                    />
                                    <div
                                        v-if="timelineTodayPct != null"
                                        class="host-bk-detail__timeline-today"
                                        :style="{ left: `${timelineTodayPct}%` }"
                                    />
                                </div>
                                <div class="host-bk-detail__timeline-labels">
                                    <span>{{ formatDate(editForm.check_in_date) }}</span>
                                    <span v-if="timelineTodayPct != null" class="host-bk-detail__timeline-now"
                                        >Today</span
                                    >
                                    <span>{{ formatDate(editForm.check_out_date) }}</span>
                                </div>
                            </div>
                        </section>

                        <section class="host-panel host-bk-detail__card">
                            <h2 class="host-bk-detail__card-title">Cleaning schedule</h2>
                            <p class="host-bk-detail__cleaning-text">
                                Standard cleaning scheduled after check-out on
                                <strong>{{ formatDate(editForm.check_out_date) }}</strong>
                                <span v-if="booking.apartment_detail?.check_out_time">
                                    (check-out from {{ booking.apartment_detail.check_out_time }})</span
                                >.
                            </p>
                        </section>
                    </div>

                    <aside class="host-bk-detail__rail">
                        <section class="host-panel host-bk-detail__rail-card">
                            <h2 class="host-bk-detail__card-title">Actions</h2>
                            <div class="host-bk-detail__actions">
                                <a
                                    v-if="editForm.email"
                                    :href="`mailto:${editForm.email}`"
                                    class="host-btn host-btn--ghost host-bk-detail__action-btn"
                                >
                                    Message guest
                                </a>
                                <button
                                    type="button"
                                    class="host-btn host-btn--ghost host-bk-detail__action-btn"
                                    @click="scrollToGuest"
                                >
                                    Edit guest details
                                </button>
                                <button
                                    v-if="editForm.status !== 'cancelled'"
                                    type="button"
                                    class="host-btn host-btn--ghost host-bk-detail__action-btn host-bk-detail__action-btn--danger"
                                    @click="cancelBooking"
                                >
                                    Cancel booking
                                </button>
                            </div>
                        </section>

                        <section class="host-panel host-bk-detail__rail-card">
                            <h2 class="host-bk-detail__card-title">Status</h2>
                            <span class="host-pill" :class="statusPillClass">{{ formatStatus(editForm.status) }}</span>
                        </section>

                        <section v-if="booking.next_task" class="host-panel host-bk-detail__rail-card host-bk-detail__next-task">
                            <h2 class="host-bk-detail__card-title">Next task</h2>
                            <p class="host-bk-detail__next-task-text">{{ booking.next_task }}</p>
                        </section>

                        <section ref="guestSection" class="host-panel host-bk-detail__rail-card host-bk-detail__rail-card--sand">
                            <h2 class="host-bk-detail__card-title">Guest information</h2>
                            <div class="host-field">
                                <label class="host-field__label" for="edit-guest">Guest name</label>
                                <input id="edit-guest" v-model="editForm.guest_name" type="text" class="host-input" />
                            </div>
                            <div class="host-field">
                                <label class="host-field__label" for="edit-email">Email</label>
                                <input id="edit-email" v-model="editForm.email" type="email" class="host-input" />
                            </div>
                            <div class="host-field">
                                <label class="host-field__label" for="edit-phone">Phone</label>
                                <input id="edit-phone" v-model="editForm.phone" type="tel" class="host-input" />
                            </div>
                        </section>

                        <section class="host-panel host-bk-detail__rail-card">
                            <h2 class="host-bk-detail__card-title">Booking overview</h2>
                            <dl class="host-detail-list">
                                <div>
                                    <dt>Channel</dt>
                                    <dd>{{ booking.channel_label ?? booking.channel }}</dd>
                                </div>
                                <div>
                                    <dt>Reference</dt>
                                    <dd>{{ booking.reference ?? booking.booking_num ?? '—' }}</dd>
                                </div>
                                <div>
                                    <dt>Guests</dt>
                                    <dd>{{ editForm.guests }}</dd>
                                </div>
                            </dl>
                        </section>

                        <section class="host-panel host-bk-detail__rail-card">
                            <h2 class="host-bk-detail__card-title">Note</h2>
                            <textarea
                                v-model="editForm.note"
                                class="host-textarea"
                                rows="4"
                                placeholder="Internal note for this booking…"
                            />
                        </section>

                        <section class="host-panel host-bk-detail__rail-card">
                            <h2 class="host-bk-detail__card-title">Access</h2>
                            <dl class="host-detail-list host-bk-detail__access">
                                <div>
                                    <dt>Door code</dt>
                                    <dd>{{ booking.access?.door_code || '—' }}</dd>
                                </div>
                                <div>
                                    <dt>WiFi network</dt>
                                    <dd>{{ booking.access?.wifi_network || '—' }}</dd>
                                </div>
                                <div>
                                    <dt>WiFi password</dt>
                                    <dd>{{ booking.access?.wifi_password || '—' }}</dd>
                                </div>
                            </dl>
                        </section>
                    </aside>
                </div>
            </div>

            <div
                class="host-bk-sticky-bar"
                :class="{
                    'host-bk-sticky-bar--visible': stickyVisible || dirty,
                    'host-bk-sticky-bar--dimmed': isScrolling,
                }"
            >
                <div class="host-bk-sticky-bar__left">
                    <router-link :to="{ name: 'bookings' }" class="host-bk-sticky-bar__back">
                        ← All bookings
                    </router-link>
                    <span class="host-bk-sticky-bar__status">
                        {{
                            dirty
                                ? `${pendingChangeCount} change${pendingChangeCount === 1 ? '' : 's'} pending`
                                : 'All saved'
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
                        Reset my changes
                    </button>
                    <button
                        type="button"
                        class="host-btn"
                        :class="dirty ? 'host-btn--accent' : 'host-btn--sand'"
                        :disabled="!dirty || saving"
                        @click="save(false)"
                    >
                        {{ saving ? 'Saving…' : 'Save' }}
                    </button>
                    <button
                        type="button"
                        class="host-btn host-btn--accent"
                        :disabled="!dirty || saving"
                        @click="save(true)"
                    >
                        Save and send to guest
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import apiClient from '@/api/client';
import { usePageTitle } from '@/composables/usePageTitle';
import { useToast } from '@/composables/useToast';
import { addDays, isoDate, parseIso, startOfDay } from '@/utils/apartment-availability';
import { resolveApartmentImageUrl } from '@/utils/apartment-images';
import { formatDate, formatStatus, formatVnd, nightsBetween } from '@/utils/format';

const route = useRoute();
const toast = useToast();
const { setPageTitle, clearPageTitle } = usePageTitle();

const loading = ref(true);
const saving = ref(false);
const scrollEl = ref(null);
const guestSection = ref(null);
const editingField = ref(null);
const nightsDraft = ref(1);
const stickyVisible = ref(false);
const isScrolling = ref(false);

let scrollStopTimer = null;

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

const liveNights = computed(() =>
    nightsBetween(editForm.check_in_date, editForm.check_out_date) || booking.value.nights || 0,
);

const liveRoomTotal = computed(() => (booking.value.daily_rate ?? 0) * liveNights.value);

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

const locationLabel = computed(() => {
    const d = booking.value.apartment_detail;
    if (!d) return '';
    return [d.district, d.building].filter(Boolean).join(' · ');
});

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

const statusPillClass = computed(() => {
    const status = editForm.status;
    return {
        confirmed: 'host-pill--confirmed',
        pending: 'host-pill--pending',
        cancelled: 'host-pill--cancelled',
    }[status] ?? 'host-pill--draft';
});

const keyFields = computed(() => [
    {
        id: 'check_in',
        label: 'Check-in',
        model: 'check_in_date',
        type: 'date',
        display: formatDate(editForm.check_in_date),
    },
    {
        id: 'check_out',
        label: 'Check-out',
        model: 'check_out_date',
        type: 'date',
        display: formatDate(editForm.check_out_date),
    },
    {
        id: 'nights',
        label: 'Nights',
        model: null,
        type: 'nights',
        display: String(liveNights.value),
    },
    {
        id: 'guests',
        label: 'Guests',
        model: 'guests',
        type: 'number',
        display: String(editForm.guests),
    },
]);

const timelineStayStyle = computed(() => {
    const start = parseIso(editForm.check_in_date);
    const end = parseIso(editForm.check_out_date);
    if (!start || !end || end <= start) {
        return { left: '0%', width: '100%' };
    }

    const padStart = new Date(start);
    padStart.setDate(padStart.getDate() - 2);
    const padEnd = new Date(end);
    padEnd.setDate(padEnd.getDate() + 2);
    const span = padEnd - padStart;
    const left = ((start - padStart) / span) * 100;
    const width = ((end - start) / span) * 100;

    return {
        left: `${Math.max(0, left)}%`,
        width: `${Math.min(100 - left, width)}%`,
    };
});

const timelineTodayPct = computed(() => {
    const start = parseIso(editForm.check_in_date);
    const end = parseIso(editForm.check_out_date);
    if (!start || !end || end <= start) return null;

    const padStart = new Date(start);
    padStart.setDate(padStart.getDate() - 2);
    const padEnd = new Date(end);
    padEnd.setDate(padEnd.getDate() + 2);
    const today = startOfDay(new Date());

    if (today < padStart || today > padEnd) return null;

    return ((today - padStart) / (padEnd - padStart)) * 100;
});

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
}

function scrollToGuest() {
    guestSection.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function cancelBooking() {
    if (window.confirm('Cancel this booking?')) {
        editForm.status = 'cancelled';
    }
}

function onScroll() {
    const scrollTop = scrollEl.value?.scrollTop ?? 0;
    stickyVisible.value = scrollTop > 20;

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
        setPageTitle(
            booking.value.booking_num
                ? `Booking #${booking.value.booking_num}`
                : `Booking #${bookingId.value}`,
        );
    } catch {
        booking.value = {};
        toast.show('Could not load booking.');
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
        toast.show(res?.message ?? 'Booking saved.');
    } catch (err) {
        toast.show(err.message ?? 'Could not save booking.');
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadBooking();
    scrollEl.value?.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
});

onUnmounted(() => {
    scrollEl.value?.removeEventListener('scroll', onScroll);
    clearTimeout(scrollStopTimer);
    clearPageTitle();
});

watch(bookingId, loadBooking);
</script>
