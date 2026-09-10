<template>
    <div class="host-team-page">
        <div class="host-team-page__head">
            <div>
                <h1 class="host-page-title">{{ pageTitle }}</h1>
                <p class="host-page-subtitle">{{ pageSubtitle }}</p>
            </div>
            <button type="button" class="host-btn host-btn--primary host-team-page__cta" @click="inviteModalOpen = true">
                {{ ctaLabel }}
            </button>
        </div>

        <div class="host-team-tabs">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                class="host-team-tabs__btn"
                :class="{ 'host-team-tabs__btn--active': activeTab === tab.key }"
                @click="activeTab = tab.key"
            >
                {{ tab.label }} ({{ tab.count }})
            </button>
        </div>

        <div class="host-team-stats">
            <div v-for="stat in stats" :key="stat.label" class="host-team-stats__item">
                <span class="host-team-stats__dot" :style="{ background: stat.dot }" />
                <div>
                    <div class="host-team-stats__value">{{ stat.value }}</div>
                    <div class="host-team-stats__label">{{ stat.label }}</div>
                </div>
            </div>
            <div class="host-team-stats__spacer" />
            <div class="host-team-toolbar__search">
                <span class="host-team-toolbar__search-icon">⌕</span>
                <input
                    v-model="search"
                    type="search"
                    class="host-team-toolbar__input"
                    :placeholder="t('team.searchPlaceholder')"
                />
            </div>
            <div class="host-team-toolbar__dropdown">
                <button
                    type="button"
                    class="host-team-toolbar__btn"
                    @click="filterOpen = !filterOpen; sortOpen = false"
                >
                    {{ t('team.filter') }}{{ activeFilterCount ? ` (${activeFilterCount})` : '' }} ▾
                </button>
                <div v-if="filterOpen" class="host-team-toolbar__menu">
                    <div class="host-team-toolbar__menu-label">{{ t('team.filterByArea') }}</div>
                    <button
                        type="button"
                        class="host-team-toolbar__menu-item"
                        :class="{ 'host-team-toolbar__menu-item--active': filterArea === 'all' }"
                        @click="filterArea = 'all'"
                    >
                        {{ t('team.allAreas') }}
                    </button>
                    <button
                        v-for="area in areaOptions"
                        :key="area"
                        type="button"
                        class="host-team-toolbar__menu-item"
                        :class="{ 'host-team-toolbar__menu-item--active': filterArea === area }"
                        @click="filterArea = area"
                    >
                        {{ area }}
                    </button>

                    <template v-if="activeTab !== 'invitations'">
                        <div class="host-team-toolbar__menu-divider" />
                        <div class="host-team-toolbar__menu-label">{{ t('team.filterByStatus') }}</div>
                        <button
                            type="button"
                            class="host-team-toolbar__menu-item"
                            :class="{ 'host-team-toolbar__menu-item--active': filterStatus === 'all' }"
                            @click="filterStatus = 'all'"
                        >
                            {{ t('team.allStatuses') }}
                        </button>
                        <button
                            v-for="statusOption in availableStatuses"
                            :key="statusOption"
                            type="button"
                            class="host-team-toolbar__menu-item"
                            :class="{ 'host-team-toolbar__menu-item--active': filterStatus === statusOption }"
                            @click="filterStatus = statusOption"
                        >
                            {{ statusLabel(statusOption) }}
                        </button>
                    </template>

                    <button
                        v-if="activeFilterCount"
                        type="button"
                        class="host-team-toolbar__menu-clear"
                        @click="clearFilters"
                    >
                        {{ t('team.clearFilters') }}
                    </button>
                </div>
            </div>

            <div class="host-team-toolbar__dropdown">
                <button
                    type="button"
                    class="host-team-toolbar__btn"
                    @click="sortOpen = !sortOpen; filterOpen = false"
                >
                    {{ t('team.sorting') }} ▾
                </button>
                <div v-if="sortOpen" class="host-team-toolbar__menu">
                    <button
                        type="button"
                        class="host-team-toolbar__menu-item"
                        :class="{ 'host-team-toolbar__menu-item--active': !sortOption }"
                        @click="sortOption = ''; sortOpen = false"
                    >
                        {{ t('team.sortDefault') }}
                    </button>
                    <button
                        v-for="option in sortOptions"
                        :key="option.key"
                        type="button"
                        class="host-team-toolbar__menu-item"
                        :class="{ 'host-team-toolbar__menu-item--active': sortOption === option.key }"
                        @click="sortOption = option.key; sortOpen = false"
                    >
                        {{ option.label }}
                    </button>
                </div>
            </div>
        </div>

        <div
            v-if="isOperations && opsNoticeOpen"
            class="host-team-notice"
        >
            <span class="host-team-notice__icon">🔒</span>
            <div class="host-team-notice__body">
                <div class="host-team-notice__title">{{ t('team.opsNoticeTitle') }}</div>
                <p class="host-team-notice__text">{{ t('team.opsNoticeText') }}</p>
            </div>
            <button type="button" class="host-team-notice__close" @click="opsNoticeOpen = false">✕</button>
        </div>

        <p v-if="loading" class="host-team-empty">{{ t('team.loading') }}</p>
        <p v-else-if="error" class="host-team-empty">{{ error }}</p>

        <div v-else-if="activeTab === 'invitations'" class="host-team-card">
            <div class="host-team-invite-head">
                <div>{{ t('team.colPerson') }}</div>
                <div>{{ t('team.colRole') }}</div>
                <div>{{ t('team.colArea') }}</div>
                <div>{{ t('team.colSent') }}</div>
                <div />
            </div>
            <div
                v-for="invite in visibleInvites"
                :key="invite.id"
                class="host-team-invite-row"
            >
                <div class="host-team-person">
                    <span
                        class="host-team-avatar"
                        :style="{ background: invite.bg }"
                    >{{ initials(invite.name) }}</span>
                    <div class="host-team-person__meta">
                        <div class="host-team-person__name">{{ invite.name }}</div>
                        <div class="host-team-person__sub">{{ invite.email }}</div>
                    </div>
                </div>
                <div>{{ invite.role }}</div>
                <div>{{ invite.area }}</div>
                <div class="host-team-muted">{{ invite.sent }}</div>
                <div class="host-team-invite-actions">
                    <button
                        type="button"
                        class="host-team-invite-actions__btn"
                        :disabled="reminding === invite.id"
                        @click="remindInvite(invite.id)"
                    >
                        {{ reminding === invite.id ? t('team.reminding') : t('team.remind') }}
                    </button>
                    <button type="button" class="host-team-invite-actions__btn host-team-invite-actions__btn--danger" @click="withdrawInvite(invite.id)">
                        {{ t('team.withdraw') }}
                    </button>
                </div>
            </div>
            <div v-if="!visibleInvites.length" class="host-team-empty">{{ t('team.noInvites') }}</div>
        </div>

        <div v-else-if="isSales" class="host-team-card">
            <div class="host-team-sales-head">
                <div>{{ t('team.colPerson') }}</div>
                <div>{{ t('team.colFunction') }}</div>
                <div>{{ t('team.colConnection') }}</div>
                <div class="host-team-cell--center">{{ t('team.colApartments') }}</div>
                <div class="host-team-cell--center">{{ t('team.colBookings') }}</div>
                <div class="host-team-cell--right">{{ t('team.colRevenue') }}</div>
                <div>{{ t('team.colRate') }}</div>
                <div class="host-team-cell--right">{{ t('team.colCommission') }}</div>
                <div class="host-team-cell--center">★</div>
                <div>{{ t('team.colStatus') }}</div>
                <div />
            </div>
            <div
                v-for="member in visibleSalesRows"
                :key="member.id"
                class="host-team-sales-row"
            >
                <div class="host-team-person">
                    <span
                        class="host-team-avatar"
                        :style="{ background: member.bg }"
                    >{{ initials(member.name) }}</span>
                    <div class="host-team-person__meta">
                        <div class="host-team-person__name">{{ member.name }}</div>
                        <div class="host-team-person__sub">{{ member.org }} · {{ member.area }}</div>
                    </div>
                </div>
                <div>{{ member.func }}</div>
                <div>
                    <span
                        class="host-team-chip"
                        :class="member.link === 'internal'
                            ? 'host-team-chip--internal'
                            : 'host-team-chip--external'"
                    >
                        {{ linkLabel(member.link) }}
                    </span>
                </div>
                <div class="host-team-cell--center host-team-strong">{{ member.apartments }}</div>
                <div class="host-team-cell--center host-team-strong">{{ member.bookings90 }}</div>
                <div class="host-team-cell--right host-team-strong host-team-revenue">
                    {{ formatMillionsVnd(member.type === 'agent' ? 0 : member.gross90) }}
                </div>
                <div class="host-team-muted">{{ salesRateLabel(member) }}</div>
                <div
                    class="host-team-cell--right host-team-strong"
                    :class="{ 'host-team-muted': salesCommission(member) === 0 }"
                >
                    {{ formatCommission(salesCommission(member)) }}
                </div>
                <div class="host-team-cell--center host-team-rating">{{ member.rating }}</div>
                <div>
                    <span
                        class="host-team-status"
                        :class="{
                            'host-team-status--active': member.status === 'active',
                            'host-team-status--paused': member.status !== 'active',
                        }"
                    >
                        {{ statusLabel(member.status) }}
                    </span>
                </div>
                <div class="host-team-menu">⋮</div>
            </div>
            <div v-if="!visibleSalesRows.length" class="host-team-empty">{{ t('team.noSalesMatches') }}</div>
        </div>

        <div v-else class="host-team-card">
            <div class="host-team-ops-head">
                <div>{{ t('team.colPerson') }}</div>
                <div>{{ t('team.colRoles') }}</div>
                <div>{{ t('team.colZones') }}</div>
                <div class="host-team-cell--center">{{ t('team.colWeek') }}</div>
                <div>{{ t('team.colAvgTime') }}</div>
                <div>{{ t('team.colGuestInfo') }}</div>
                <div>{{ t('team.colStatus') }}</div>
                <div />
            </div>
            <div
                v-for="member in visibleOpsRows"
                :key="member.id"
                class="host-team-ops-row"
            >
                <div class="host-team-person">
                    <span
                        class="host-team-avatar"
                        :style="{ background: member.bg }"
                    >{{ initials(member.name) }}</span>
                    <div class="host-team-person__meta">
                        <div class="host-team-person__name">{{ member.name }}</div>
                        <div class="host-team-person__sub">{{ member.phone }}</div>
                    </div>
                </div>
                <div class="host-team-role-chips">
                    <span
                        v-for="role in member.roles"
                        :key="role"
                        class="host-team-role-chip"
                        :style="{
                            background: opsRoleDef(role)?.bg,
                            color: opsRoleDef(role)?.color,
                        }"
                    >
                        {{ opsRoleDef(role)?.label }}
                    </span>
                </div>
                <div>{{ member.area }}</div>
                <div class="host-team-cell--center host-team-strong">{{ member.tasksWeek }}</div>
                <div
                    class="host-team-strong"
                    :class="{ 'host-team-time-warn': member.avgTimeWarn }"
                >
                    {{ member.avgTime }}
                </div>
                <div class="host-team-guest-toggle">
                    <button
                        type="button"
                        class="host-team-toggle"
                        :class="{ 'host-team-toggle--on': guestInfoState[member.id] }"
                        @click="toggleGuestInfo(member.id, !guestInfoState[member.id])"
                    >
                        <span class="host-team-toggle__knob" />
                    </button>
                    <span class="host-team-guest-toggle__label">
                        {{ guestInfoState[member.id] ? t('team.guestInfoOn') : t('team.guestInfoOff') }}
                    </span>
                </div>
                <div>
                    <span
                        class="host-team-status"
                        :class="{
                            'host-team-status--active': member.status === 'active',
                            'host-team-status--paused': member.status !== 'active',
                        }"
                    >
                        {{ statusLabel(member.status) }}
                    </span>
                </div>
                <div class="host-team-menu">⋮</div>
            </div>
            <div v-if="!visibleOpsRows.length" class="host-team-empty">{{ t('team.noOpsMatches') }}</div>
        </div>

        <InviteTeamMemberModal
            :open="inviteModalOpen"
            :mode="isSales ? 'sales' : 'operations'"
            :area-options="areaOptions"
            @close="inviteModalOpen = false"
            @invited="onInvited"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import apiClient from '@/api/client';
import InviteTeamMemberModal from '@/components/modals/InviteTeamMemberModal.vue';
import { useToast } from '@/composables/useToast';
import {
    OPS_ROLE_DEFS,
    formatMillionsVnd,
    initials,
    linkLabel,
    opsRoleDef,
    salesCommission,
    salesRateLabel,
    statusLabel,
} from '@/data/team-content';

const route = useRoute();
const { t } = useI18n();
const toast = useToast();

const search = ref('');
const activeTab = ref('all');
const opsNoticeOpen = ref(true);
const inviteModalOpen = ref(false);
const loading = ref(true);
const error = ref('');
const members = ref([]);
const filterOpen = ref(false);
const sortOpen = ref(false);
const filterArea = ref('all');
const filterStatus = ref('all');
const sortOption = ref('');
const reminding = ref(null);
const invitations = ref([]);
const teamStats = ref({});
const areaOptions = ref([]);
const guestInfoState = reactive({});

const isSales = computed(() => route.meta.teamMode === 'sales');
const isOperations = computed(() => route.meta.teamMode === 'operations');

const pageTitle = computed(() => (
    isSales.value ? t('nav.salesTeam') : t('nav.operationsTeam')
));

const pageSubtitle = computed(() => (
    isSales.value ? t('team.salesSubtitle') : t('team.opsSubtitle')
));

const ctaLabel = computed(() => (
    isSales.value ? t('team.salesCta') : t('team.opsCta')
));

function matchesSearch(text) {
    const query = search.value.trim().toLowerCase();
    if (!query) {
        return true;
    }

    return String(text).toLowerCase().includes(query);
}

function salesMatchesTab(member, tab) {
    if (tab === 'all') {
        return true;
    }

    if (tab === 'internal') {
        return member.link === 'internal' && member.type !== 'agent';
    }

    if (tab === 'external') {
        return member.link === 'external' && member.type !== 'agent';
    }

    if (tab === 'agents') {
        return member.type === 'agent';
    }

    return true;
}

function opsMatchesTab(member, tab) {
    if (tab === 'all') {
        return true;
    }

    return member.roles.includes(tab);
}

const tabs = computed(() => {
    if (isSales.value) {
        const defs = [
            { key: 'all', label: t('team.tabAll') },
            { key: 'internal', label: t('team.tabInternal') },
            { key: 'external', label: t('team.tabExternal') },
            { key: 'agents', label: t('team.tabHostAgents') },
            { key: 'invitations', label: t('team.tabInvitations') },
        ];

        return defs.map((tab) => ({
            ...tab,
            count: tab.key === 'invitations'
                ? invitations.value.length
                : members.value.filter((member) => salesMatchesTab(member, tab.key)).length,
        }));
    }

    const defs = [
        { key: 'all', label: t('team.tabAll') },
        ...OPS_ROLE_DEFS.map((role) => ({ key: role.key, label: role.label })),
        { key: 'invitations', label: t('team.tabInvitations') },
    ];

    return defs.map((tab) => ({
        ...tab,
        count: tab.key === 'invitations'
            ? invitations.value.length
            : members.value.filter((member) => opsMatchesTab(member, tab.key)).length,
    }));
});

const stats = computed(() => {
    if (isSales.value) {
        return [
            { dot: '#1f7a44', value: String(teamStats.value.bookings90 ?? 0), label: t('team.statBookings90') },
            { dot: '#b5651d', value: formatMillionsVnd(teamStats.value.gross90 ?? 0), label: t('team.statRevenue90') },
            { dot: '#8a6d3b', value: formatMillionsVnd(teamStats.value.commissionOwed ?? 0), label: t('team.statCommissionOwed') },
        ];
    }

    return [
        { dot: '#1f7a44', value: String(teamStats.value.tasksWeek ?? 0), label: t('team.statTasksWeek') },
        { dot: '#b5651d', value: teamStats.value.avgTime ?? '—', label: t('team.statAvgTime') },
        {
            dot: '#8a9187',
            value: `${teamStats.value.guestInfoSeeing ?? 0} of ${teamStats.value.guestInfoTotal ?? 0}`,
            label: t('team.statGuestInfo'),
        },
    ];
});

function matchesFilters(item) {
    if (filterArea.value !== 'all' && item.area !== filterArea.value) {
        return false;
    }

    if (filterStatus.value !== 'all' && item.status !== undefined && item.status !== filterStatus.value) {
        return false;
    }

    return true;
}

function compareBy(a, b, field, numeric) {
    if (numeric) {
        return (Number(a[field]) || 0) - (Number(b[field]) || 0);
    }

    const va = String(a[field] ?? '').toLowerCase();
    const vb = String(b[field] ?? '').toLowerCase();

    return va < vb ? -1 : va > vb ? 1 : 0;
}

function sortRows(rows, key) {
    if (!key) {
        return rows;
    }

    const sorted = [...rows];

    switch (key) {
        case 'name_asc': return sorted.sort((a, b) => compareBy(a, b, 'name', false));
        case 'name_desc': return sorted.sort((a, b) => -compareBy(a, b, 'name', false));
        case 'apartments_desc': return sorted.sort((a, b) => -compareBy(a, b, 'apartments', true));
        case 'bookings_desc': return sorted.sort((a, b) => -compareBy(a, b, 'bookings90', true));
        case 'revenue_desc': return sorted.sort((a, b) => -compareBy(a, b, 'gross90', true));
        case 'rating_desc': return sorted.sort((a, b) => -compareBy(a, b, 'rating', true));
        case 'tasks_desc': return sorted.sort((a, b) => -compareBy(a, b, 'tasksWeek', true));
        case 'tasks_asc': return sorted.sort((a, b) => compareBy(a, b, 'tasksWeek', true));
        case 'sent_desc': return sorted.sort((a, b) => -compareBy(a, b, 'sentAt', false));
        case 'sent_asc': return sorted.sort((a, b) => compareBy(a, b, 'sentAt', false));
        default: return rows;
    }
}

const availableStatuses = computed(() =>
    Array.from(new Set(members.value.map((member) => member.status).filter(Boolean))),
);

const activeFilterCount = computed(() =>
    (filterArea.value !== 'all' ? 1 : 0) + (filterStatus.value !== 'all' ? 1 : 0),
);

const sortOptions = computed(() => {
    if (activeTab.value === 'invitations') {
        return [
            { key: 'name_asc', label: t('team.sortNameAsc') },
            { key: 'name_desc', label: t('team.sortNameDesc') },
            { key: 'sent_desc', label: t('team.sortSentDesc') },
            { key: 'sent_asc', label: t('team.sortSentAsc') },
        ];
    }

    if (isSales.value) {
        return [
            { key: 'name_asc', label: t('team.sortNameAsc') },
            { key: 'name_desc', label: t('team.sortNameDesc') },
            { key: 'apartments_desc', label: t('team.sortApartmentsDesc') },
            { key: 'bookings_desc', label: t('team.sortBookingsDesc') },
            { key: 'revenue_desc', label: t('team.sortRevenueDesc') },
            { key: 'rating_desc', label: t('team.sortRatingDesc') },
        ];
    }

    return [
        { key: 'name_asc', label: t('team.sortNameAsc') },
        { key: 'name_desc', label: t('team.sortNameDesc') },
        { key: 'tasks_desc', label: t('team.sortTasksDesc') },
        { key: 'tasks_asc', label: t('team.sortTasksAsc') },
    ];
});

function clearFilters() {
    filterArea.value = 'all';
    filterStatus.value = 'all';
}

function closeToolbarMenus(event) {
    if (!event.target.closest('.host-team-toolbar__dropdown')) {
        filterOpen.value = false;
        sortOpen.value = false;
    }
}

const visibleSalesRows = computed(() => sortRows(members.value.filter((member) => {
    if (!salesMatchesTab(member, activeTab.value) || !matchesFilters(member)) {
        return false;
    }

    return matchesSearch(`${member.name} ${member.org} ${member.area} ${member.func}`);
}), sortOption.value));

const visibleOpsRows = computed(() => sortRows(members.value.filter((member) => {
    if (!opsMatchesTab(member, activeTab.value) || !matchesFilters(member)) {
        return false;
    }

    return matchesSearch(`${member.name} ${member.phone} ${member.area}`);
}), sortOption.value));

const visibleInvites = computed(() => sortRows(invitations.value.filter((invite) =>
    matchesFilters(invite) && matchesSearch(`${invite.name} ${invite.email} ${invite.role}`)
), sortOption.value));

async function loadTeam() {
    loading.value = true;
    error.value = '';

    try {
        const teamType = isSales.value ? 'sales' : 'operations';
        const response = await apiClient.get(`/team?type=${teamType}`);
        const data = response.data ?? {};

        members.value = data.members ?? [];
        invitations.value = data.invitations ?? [];
        teamStats.value = data.stats ?? {};
        areaOptions.value = data.areas ?? [];

        Object.keys(guestInfoState).forEach((key) => delete guestInfoState[key]);
        members.value.forEach((member) => {
            guestInfoState[member.id] = member.guestInfo;
        });
    } catch (err) {
        error.value = err.message || t('team.loadFailed');
        members.value = [];
        invitations.value = [];
    } finally {
        loading.value = false;
    }
}

function onInvited() {
    activeTab.value = 'invitations';
    loadTeam();
}

async function toggleGuestInfo(id, enabled) {
    guestInfoState[id] = enabled;

    try {
        await apiClient.patch(`/team/members/${id}/guest-info`, { enabled });
        await loadTeam();
    } catch {
        guestInfoState[id] = !enabled;
    }
}

async function withdrawInvite(id) {
    try {
        await apiClient.delete(`/team/invitations/${id}`);
        await loadTeam();
    } catch (err) {
        window.alert(err.message || t('team.inviteWithdrawFailed'));
    }
}

async function remindInvite(id) {
    if (reminding.value) {
        return;
    }

    reminding.value = id;

    try {
        await apiClient.post(`/team/invitations/${id}/remind`);
        await loadTeam();
        toast.show(t('team.reminderSent'));
    } catch (err) {
        toast.show(err.message ?? t('team.reminderFailed'));
    } finally {
        reminding.value = null;
    }
}

watch(() => route.meta.teamMode, () => {
    activeTab.value = 'all';
    clearFilters();
    sortOption.value = '';
    loadTeam();
});

onMounted(() => {
    loadTeam();
    document.addEventListener('click', closeToolbarMenus);
});

onUnmounted(() => {
    document.removeEventListener('click', closeToolbarMenus);
});

function formatCommission(amount) {
    if (!amount) {
        return '0 ₫';
    }

    return formatMillionsVnd(amount);
}
</script>
