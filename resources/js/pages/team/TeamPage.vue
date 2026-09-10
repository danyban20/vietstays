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
            <button type="button" class="host-team-toolbar__btn">
                {{ t('team.filter') }} ▾
            </button>
            <button type="button" class="host-team-toolbar__btn">
                {{ t('team.sorting') }} ▾
            </button>
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
                    <button type="button" class="host-team-invite-actions__btn">{{ t('team.remind') }}</button>
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
                role="button"
                tabindex="0"
                @click="goToMember(member.id)"
                @keydown.enter="goToMember(member.id)"
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
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/api/client';
import InviteTeamMemberModal from '@/components/modals/InviteTeamMemberModal.vue';
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
const router = useRouter();
const { t } = useI18n();

const search = ref('');
const activeTab = ref('all');
const opsNoticeOpen = ref(true);
const inviteModalOpen = ref(false);
const loading = ref(true);
const error = ref('');
const members = ref([]);
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

const visibleSalesRows = computed(() => members.value.filter((member) => {
    if (!salesMatchesTab(member, activeTab.value)) {
        return false;
    }

    return matchesSearch(`${member.name} ${member.org} ${member.area} ${member.func}`);
}));

const visibleOpsRows = computed(() => members.value.filter((member) => {
    if (!opsMatchesTab(member, activeTab.value)) {
        return false;
    }

    return matchesSearch(`${member.name} ${member.phone} ${member.area}`);
}));

const visibleInvites = computed(() =>
    invitations.value.filter((invite) => matchesSearch(`${invite.name} ${invite.email} ${invite.role}`)),
);

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

function goToMember(id) {
    router.push({ name: 'team-sales-member', params: { id } });
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

watch(() => route.meta.teamMode, () => {
    activeTab.value = 'all';
    loadTeam();
});

onMounted(loadTeam);

function formatCommission(amount) {
    if (!amount) {
        return '0 ₫';
    }

    return formatMillionsVnd(amount);
}
</script>
