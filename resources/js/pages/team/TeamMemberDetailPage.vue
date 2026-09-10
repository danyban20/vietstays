<template>
    <div v-if="loading" class="host-team-empty">{{ t('team.loading') }}</div>
    <div v-else-if="error" class="host-team-empty">{{ error }}</div>
    <div v-else-if="member" class="host-sales-member-detail">
        <router-link :to="{ name: 'team-sales' }" class="host-sales-member-detail__back">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7" /></svg>
            {{ t('nav.salesTeam') }}
        </router-link>

        <div class="host-sales-member-detail__scroll">
            <div class="host-sales-member-detail__top">
                <div class="host-sales-member-detail__identity">
                    <span class="host-team-avatar host-sales-member-detail__avatar" :style="{ background: member.bg }">
                        {{ initials(member.name) }}
                    </span>
                    <div>
                        <h1 class="host-sales-member-detail__name">{{ member.name }}</h1>
                        <p class="host-sales-member-detail__meta">
                            {{ member.func }} · {{ member.org }} · {{ member.area }} · {{ member.outPct }}% {{ t('team.commissionShort') }}
                        </p>
                    </div>
                </div>
                <div class="host-sales-member-detail__actions">
                    <button type="button" class="host-btn host-btn--primary" disabled>{{ t('team.sendMessage') }}</button>
                    <button type="button" class="host-btn host-btn--ghost" @click="assignModalOpen = true">
                        {{ t('team.editAgreement') }}
                    </button>
                </div>
            </div>

            <div class="host-sales-member-detail__chips">
                <button
                    v-for="chip in roomFilters"
                    :key="`room-${chip.key}`"
                    type="button"
                    class="host-team-tabs__btn"
                    :class="{ 'host-team-tabs__btn--active': roomFilter === chip.key }"
                    @click="roomFilter = chip.key"
                >
                    {{ chip.key === 'all' ? t('team.chipAll') : chip.label }} ({{ chip.count }})
                </button>
                <span class="host-sales-member-detail__chip-sep" />
                <button
                    v-for="chip in discountFilters.filter((c) => c.key !== 'all')"
                    :key="`discount-${chip.key}`"
                    type="button"
                    class="host-team-tabs__btn"
                    :class="{ 'host-team-tabs__btn--active': discountFilter === chip.key }"
                    @click="discountFilter = discountFilter === chip.key ? 'all' : chip.key"
                >
                    {{ chip.key === 'with' ? t('team.filterWithDiscount') : t('team.filterWithoutDiscount') }} ({{ chip.count }})
                </button>
            </div>

            <div class="host-team-stats">
                <div class="host-team-stats__item">
                    <span class="host-team-stats__dot" style="background: #1f7a44" />
                    <div>
                        <div class="host-team-stats__value">{{ member.bookings90 }}</div>
                        <div class="host-team-stats__label">{{ t('team.memberStatBookings') }}</div>
                    </div>
                </div>
                <div class="host-team-stats__item">
                    <span class="host-team-stats__dot" style="background: #b5651d" />
                    <div>
                        <div class="host-team-stats__value">{{ formatMillionsVnd(member.gross90) }}</div>
                        <div class="host-team-stats__label">{{ t('team.memberStatRevenue') }}</div>
                    </div>
                </div>
                <div class="host-team-stats__item">
                    <span class="host-team-stats__dot" style="background: #8a6d3b" />
                    <div>
                        <div class="host-team-stats__value">{{ formatMillionsVnd(member.commissionOwed90) }}</div>
                        <div class="host-team-stats__label">{{ t('team.memberStatCommission') }}</div>
                    </div>
                </div>
                <div class="host-team-stats__spacer" />
                <div class="host-team-toolbar__search">
                    <span class="host-team-toolbar__search-icon">⌕</span>
                    <input v-model="search" type="search" class="host-team-toolbar__input" :placeholder="t('team.searchPlaceholder')" />
                </div>
            </div>

            <div class="host-team-card">
                <div class="host-sales-member-bookings-head">
                    <div>{{ t('team.colBooking') }}</div>
                    <div>{{ t('team.colApartment') }}</div>
                    <div>{{ t('team.colGuest') }}</div>
                    <div>{{ t('team.colDates') }}</div>
                    <div class="host-team-cell--center">N</div>
                    <div>{{ t('team.colChannel') }}</div>
                    <div class="host-team-cell--right">{{ t('team.colAmount') }}</div>
                    <div class="host-team-cell--right">{{ t('team.colCommission') }}</div>
                    <div>{{ t('team.colStatus') }}</div>
                </div>
                <div v-for="booking in visibleBookings" :key="booking.id" class="host-sales-member-bookings-row">
                    <div>{{ booking.bookingNum }}</div>
                    <div>
                        <div>{{ booking.apartment }}</div>
                        <div class="host-team-muted">{{ matchCodeFromApartmentName(booking.apartment) }}</div>
                    </div>
                    <div>{{ booking.guest }}</div>
                    <div>{{ formatPeriodShort(booking.checkIn, booking.checkOut, locale) }}</div>
                    <div class="host-team-cell--center">{{ booking.nights }}</div>
                    <div>{{ booking.channel }}</div>
                    <div class="host-team-cell--right host-team-strong">{{ formatVnd(booking.amount) }}</div>
                    <div class="host-team-cell--right" :class="{ 'host-team-muted': !booking.commission }">
                        {{ booking.commission ? formatVnd(booking.commission) : '—' }}
                    </div>
                    <div>
                        <span
                            class="host-team-status"
                            :class="{
                                'host-team-status--active': booking.status === 'confirmed',
                                'host-team-status--paused': booking.status !== 'confirmed',
                            }"
                        >
                            {{ statusLabel(booking.status) }}
                        </span>
                    </div>
                </div>
                <div v-if="!visibleBookings.length" class="host-team-empty">{{ t('team.noBookings') }}</div>
            </div>
        </div>

        <AssignApartmentsModal
            :open="assignModalOpen"
            :member-id="member.id"
            :member-name="member.name"
            :assigned-apartment-ids="assignedApartmentIds"
            @close="assignModalOpen = false"
            @saved="onAssigned"
        />
    </div>
</template>

<script setup>
import { computed, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import apiClient from '@/api/client';
import AssignApartmentsModal from '@/components/modals/AssignApartmentsModal.vue';
import { usePageTitle } from '@/composables/usePageTitle';
import { matchCodeFromApartmentName } from '@/utils/apartment-match-code';
import { formatPeriodShort, formatStatus, formatVnd } from '@/utils/format';
import { formatMillionsVnd, initials } from '@/data/team-content';

const route = useRoute();
const { t, locale } = useI18n();
const { setPageTitle, clearPageTitle } = usePageTitle();

const loading = ref(true);
const error = ref('');
const member = ref(null);
const roomFilters = ref([]);
const discountFilters = ref([]);
const assignedApartmentIds = ref([]);
const bookings = ref([]);
const search = ref('');
const roomFilter = ref('all');
const discountFilter = ref('all');
const assignModalOpen = ref(false);

function statusLabel(status) {
    return formatStatus(status);
}

function matchesSearch(text) {
    const query = search.value.trim().toLowerCase();
    return !query || String(text).toLowerCase().includes(query);
}

const visibleBookings = computed(() => bookings.value.filter((booking) => {
    if (roomFilter.value !== 'all' && String(booking.apartmentRooms) !== roomFilter.value) {
        return false;
    }

    if (discountFilter.value === 'with' && !booking.hasDiscount) {
        return false;
    }

    if (discountFilter.value === 'without' && booking.hasDiscount) {
        return false;
    }

    return matchesSearch(`${booking.guest} ${booking.apartment} ${booking.bookingNum}`);
}));

async function loadMember() {
    loading.value = true;
    error.value = '';

    try {
        const response = await apiClient.get(`/team/members/${route.params.id}`);
        const data = response.data ?? {};
        member.value = data.member ?? null;
        roomFilters.value = data.roomFilters ?? [];
        discountFilters.value = data.discountFilters ?? [];
        assignedApartmentIds.value = data.assignedApartmentIds ?? [];
        bookings.value = data.bookings ?? [];
        roomFilter.value = 'all';
        discountFilter.value = 'all';

        if (!member.value) {
            error.value = t('team.notFound');
        }
    } catch (err) {
        error.value = err.message || t('team.loadMemberFailed');
        member.value = null;
    } finally {
        loading.value = false;
    }
}

function onAssigned(data) {
    member.value = data.member ?? member.value;
    roomFilters.value = data.roomFilters ?? roomFilters.value;
    discountFilters.value = data.discountFilters ?? discountFilters.value;
    assignedApartmentIds.value = data.assignedApartmentIds ?? assignedApartmentIds.value;
    bookings.value = data.bookings ?? bookings.value;
}

watch(() => route.params.id, loadMember, { immediate: true });

watch(member, (value) => {
    if (value) {
        setPageTitle(value.name);
    }
});

onUnmounted(clearPageTitle);
</script>
