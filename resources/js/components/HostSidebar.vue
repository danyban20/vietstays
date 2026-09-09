<template>
    <aside class="host-sidebar">
        <div class="host-sidebar__brand">{{ t('brand') }}</div>

        <nav class="host-sidebar__group host-sidebar__group--top">
            <router-link
                :to="{ name: 'dashboard' }"
                class="host-sidebar__link"
                :class="{ 'host-sidebar__link--active': isActive('dashboard') }"
            >
                <SidebarIcon name="dashboard" />
                <span class="host-sidebar__label">{{ t('nav.dashboard') }}</span>
            </router-link>
        </nav>

        <!-- Platform admin / partner (Aug 20: isPlatformRole) -->
        <template v-if="auth.isPlatformRole">
            <nav class="host-sidebar__group">
                <router-link
                    :to="{ name: 'apartments' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': isApartmentsListActive }"
                >
                    <SidebarIcon name="apartments" />
                    <span class="host-sidebar__label">{{ t('nav.apartments') }}</span>
                </router-link>

                <router-link
                    :to="{ name: 'bookings' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': isBookingsSectionActive }"
                >
                    <SidebarIcon name="bookings" />
                    <span class="host-sidebar__label">{{ t('nav.bookings') }}</span>
                </router-link>
            </nav>

            <div class="host-sidebar__divider" />

            <nav class="host-sidebar__group">
                <button
                    type="button"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--parent-active': navOpen === 'hosts' }"
                    @click="toggleNavOpen('hosts')"
                >
                    <SidebarIcon name="host-agents" />
                    <span class="host-sidebar__label">{{ t('nav.hostsPartners') }}</span>
                    <span
                        class="host-sidebar__chevron"
                        :class="{ 'host-sidebar__chevron--open': navOpen === 'hosts' }"
                    >▾</span>
                </button>

                <template v-if="navOpen === 'hosts'">
                    <span class="host-sidebar__link host-sidebar__link--child host-sidebar__link--disabled">
                        {{ t('nav.managementCompanies') }}
                    </span>
                    <span class="host-sidebar__link host-sidebar__link--child host-sidebar__link--disabled">
                        {{ t('nav.hostsList') }}
                    </span>
                    <span class="host-sidebar__link host-sidebar__link--child host-sidebar__link--disabled">
                        {{ t('nav.hostAgents') }}
                    </span>
                    <span class="host-sidebar__link host-sidebar__link--child host-sidebar__link--disabled">
                        {{ t('nav.ambassadors') }}
                    </span>
                    <router-link
                        :to="{ name: 'host-applications' }"
                        class="host-sidebar__link host-sidebar__link--child"
                        :class="{ 'host-sidebar__link--active': isHostApplicationsSection }"
                    >
                        <span class="host-sidebar__label">{{ t('nav.hostApplications') }}</span>
                        <span v-if="pendingApplications > 0" class="host-sidebar__badge">
                            {{ pendingApplications }}
                        </span>
                    </router-link>
                    <span class="host-sidebar__link host-sidebar__link--child host-sidebar__link--disabled">
                        {{ t('nav.hostPointsSystem') }}
                    </span>
                </template>

                <router-link
                    :to="{ name: 'admin-users' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': isAdminUsersSection }"
                >
                    <SidebarIcon name="users" />
                    <span class="host-sidebar__label">{{ t('nav.users') }}</span>
                </router-link>
            </nav>

            <div class="host-sidebar__divider" />

            <nav class="host-sidebar__group">
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="finance" />
                    <span class="host-sidebar__label">{{ t('nav.finance') }}</span>
                </span>
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="marketing" />
                    <span class="host-sidebar__label">{{ t('nav.marketing') }}</span>
                </span>
            </nav>

            <div class="host-sidebar__divider" />

            <nav class="host-sidebar__group">
                <button
                    type="button"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--parent-active': navOpen === 'content' }"
                    @click="toggleNavOpen('content')"
                >
                    <SidebarIcon name="content" />
                    <span class="host-sidebar__label">{{ t('nav.platformContent') }}</span>
                    <span
                        class="host-sidebar__chevron"
                        :class="{ 'host-sidebar__chevron--open': navOpen === 'content' }"
                    >▾</span>
                </button>

                <template v-if="navOpen === 'content'">
                    <span
                        v-for="item in platformContentNav"
                        :key="item.key"
                        class="host-sidebar__link host-sidebar__link--child host-sidebar__link--disabled"
                    >
                        {{ t(item.key) }}
                    </span>
                </template>

                <span
                    v-for="item in platformAdminNav"
                    :key="item.key"
                    class="host-sidebar__link host-sidebar__link--disabled"
                >
                    <SidebarIcon :name="item.icon" />
                    <span class="host-sidebar__label">{{ t(item.key) }}</span>
                </span>
            </nav>

            <div class="host-sidebar__divider" />

            <nav class="host-sidebar__group">
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="communication" />
                    <span class="host-sidebar__label">{{ t('nav.communication') }}</span>
                </span>
            </nav>

            <nav class="host-sidebar__group host-sidebar__group--bottom">
                <div class="host-sidebar__group-title host-sidebar__group-title--admin">
                    {{ t('nav.administration') }}
                </div>
                <router-link
                    :to="{ name: 'settings-email' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': isSettingsSection }"
                >
                    <SidebarIcon name="settings" />
                    <span class="host-sidebar__label">{{ t('nav.settings') }}</span>
                </router-link>
            </nav>
        </template>

        <!-- Host role (Aug 20: isHostRole) -->
        <template v-else-if="auth.isHostRole">
            <nav class="host-sidebar__group">
                <div class="host-sidebar__group-title">{{ t('nav.apartmentsBookings') }}</div>

                <button
                    type="button"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--parent-active': navOpen === 'apartments' }"
                    @click="openApartmentsNav"
                >
                    <SidebarIcon name="apartments" />
                    <span class="host-sidebar__label">{{ t('nav.myApartments') }}</span>
                    <span
                        class="host-sidebar__chevron"
                        :class="{ 'host-sidebar__chevron--open': navOpen === 'apartments' }"
                    >▾</span>
                </button>

                <template v-if="navOpen === 'apartments'">
                    <router-link
                        :to="{ name: 'apartment-add' }"
                        class="host-sidebar__link host-sidebar__link--child host-sidebar__link--add"
                        :class="{ 'host-sidebar__link--active': route.name === 'apartment-add' }"
                    >
                        + {{ t('nav.addApartment') }}
                    </router-link>
                    <span class="host-sidebar__link host-sidebar__link--child host-sidebar__link--prefilled host-sidebar__link--disabled">
                        ⚡ {{ t('nav.prefilledApartmentDemo') }}
                    </span>
                    <router-link
                        :to="{ name: 'apartments' }"
                        class="host-sidebar__link host-sidebar__link--child"
                        :class="{ 'host-sidebar__link--active': isApartmentsListActive }"
                    >
                        {{ t('nav.allApartments') }}
                    </router-link>
                </template>

                <button
                    type="button"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--parent-active': navOpen === 'bookings' }"
                    @click="openBookingsNav"
                >
                    <SidebarIcon name="bookings" />
                    <span class="host-sidebar__label">{{ t('nav.myBookings') }}</span>
                    <span
                        class="host-sidebar__chevron"
                        :class="{ 'host-sidebar__chevron--open': navOpen === 'bookings' }"
                    >▾</span>
                </button>

                <template v-if="navOpen === 'bookings'">
                    <router-link
                        :to="{ name: 'bookings', query: { add: 'manual' } }"
                        class="host-sidebar__link host-sidebar__link--child"
                    >
                        + {{ t('nav.addNew') }}
                    </router-link>
                    <router-link
                        :to="{ name: 'bookings' }"
                        class="host-sidebar__link host-sidebar__link--child"
                        :class="{ 'host-sidebar__link--active': route.name === 'bookings' || route.name === 'booking-detail' }"
                    >
                        {{ t('nav.allBookings') }}
                    </router-link>
                    <router-link
                        :to="{ name: 'bookings-calendar' }"
                        class="host-sidebar__link host-sidebar__link--child"
                        :class="{ 'host-sidebar__link--active': route.name === 'bookings-calendar' }"
                    >
                        {{ t('nav.calendar') }}
                    </router-link>
                </template>

                <router-link
                    :to="{ name: 'customers' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': route.name === 'customers' || route.name === 'customer-detail' }"
                >
                    <SidebarIcon name="customers" />
                    <span class="host-sidebar__label">{{ t('nav.customers') }}</span>
                </router-link>
            </nav>

            <nav class="host-sidebar__group">
                <div class="host-sidebar__group-title">{{ t('nav.myTeam') }}</div>
                <span
                    v-for="item in hostTeamNav"
                    :key="item.key"
                    class="host-sidebar__link host-sidebar__link--disabled"
                >
                    <SidebarIcon :name="item.icon" />
                    <span class="host-sidebar__label">{{ t(item.key) }}</span>
                </span>
            </nav>

            <nav class="host-sidebar__group">
                <div class="host-sidebar__group-title">{{ t('nav.discountVisibility') }}</div>
                <span
                    v-for="item in discountNav"
                    :key="item.key"
                    class="host-sidebar__link host-sidebar__link--disabled"
                >
                    <SidebarIcon :name="item.icon" />
                    <span class="host-sidebar__label">{{ t(item.key) }}</span>
                </span>
            </nav>

            <nav class="host-sidebar__group">
                <div class="host-sidebar__group-title">{{ t('nav.financeReports') }}</div>
                <span
                    v-for="item in financeNav"
                    :key="item.key"
                    class="host-sidebar__link host-sidebar__link--disabled"
                >
                    <SidebarIcon :name="item.icon" />
                    <span class="host-sidebar__label">{{ t(item.key) }}</span>
                </span>
            </nav>

            <nav class="host-sidebar__group host-sidebar__group--bottom">
                <div class="host-sidebar__group-title">{{ t('nav.communicationAccount') }}</div>
                <span
                    v-for="item in communicationNav"
                    :key="item.key"
                    class="host-sidebar__link host-sidebar__link--disabled"
                >
                    <SidebarIcon :name="item.icon" />
                    <span class="host-sidebar__label">{{ t(item.key) }}</span>
                </span>
                <router-link
                    :to="{ name: 'settings-email' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': isSettingsSection }"
                >
                    <SidebarIcon name="settings" />
                    <span class="host-sidebar__label">{{ t('nav.settings') }}</span>
                </router-link>
            </nav>
        </template>
    </aside>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import SidebarIcon from '@/components/SidebarIcon.vue';
import apiClient from '@/api/client';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const router = useRouter();
const { t } = useI18n();
const auth = useAuthStore();

const navOpen = ref(null);
const pendingApplications = ref(0);

const platformContentNav = [
    { key: 'nav.buildings' },
    { key: 'nav.locations' },
    { key: 'nav.priceMatrix' },
    { key: 'nav.houseRulesFacilities' },
    { key: 'nav.opsChecklists' },
];

const platformAdminNav = [
    { key: 'nav.configuration', icon: 'settings' },
    { key: 'nav.payouts', icon: 'payouts' },
    { key: 'nav.reports', icon: 'reports' },
];

const hostTeamNav = [
    { key: 'nav.salesTeam', icon: 'sales-team' },
    { key: 'nav.operationsTeam', icon: 'operations' },
    { key: 'nav.managementCompany', icon: 'management' },
];

const discountNav = [
    { key: 'nav.campaign', icon: 'campaign' },
    { key: 'nav.ambassadors', icon: 'ambassadors' },
];

const financeNav = [
    { key: 'nav.finance', icon: 'finance' },
    { key: 'nav.reports', icon: 'reports' },
];

const communicationNav = [
    { key: 'nav.communication', icon: 'communication' },
    { key: 'nav.marketing', icon: 'marketing' },
    { key: 'nav.hostPoints', icon: 'host-points' },
];

const isApartmentsListActive = computed(
    () => ['apartments', 'apartment-detail', 'apartment-add'].includes(route.name),
);

const isBookingsSectionActive = computed(
    () => ['bookings', 'bookings-calendar', 'booking-detail'].includes(route.name),
);

const isHostApplicationsSection = computed(() =>
    ['host-applications', 'host-application-detail'].includes(route.name),
);

const isAdminUsersSection = computed(() => route.name === 'admin-users');

const isSettingsSection = computed(() =>
    ['settings-email', 'settings-email-templates', 'settings-languages'].includes(route.name),
);

function isActive(name) {
    return route.name === name;
}

function toggleNavOpen(section) {
    navOpen.value = navOpen.value === section ? null : section;
}

function openApartmentsNav() {
    navOpen.value = 'apartments';
    if (!isApartmentsListActive.value) {
        router.push({ name: 'apartments' });
    }
}

function openBookingsNav() {
    navOpen.value = 'bookings';
    if (!isBookingsSectionActive.value) {
        router.push({ name: 'bookings' });
    }
}

function syncNavOpenFromRoute() {
    if (auth.isPlatformRole) {
        if (isHostApplicationsSection.value) {
            navOpen.value = 'hosts';
        }
        return;
    }

    if (isApartmentsListActive.value) {
        navOpen.value = 'apartments';
    } else if (isBookingsSectionActive.value) {
        navOpen.value = 'bookings';
    }
}

async function loadPendingApplications() {
    if (!auth.isPlatformRole) {
        return;
    }

    try {
        const res = await apiClient.get('/host-applications');
        const list = Array.isArray(res?.data) ? res.data : [];
        pendingApplications.value = list.length;
    } catch {
        pendingApplications.value = 0;
    }
}

watch(() => route.name, syncNavOpenFromRoute, { immediate: true });

watch(() => auth.loaded, (loaded) => {
    if (loaded) {
        syncNavOpenFromRoute();
        loadPendingApplications();
    }
});

onMounted(async () => {
    if (!auth.loaded) {
        await auth.fetchUser();
    }
    syncNavOpenFromRoute();
    loadPendingApplications();
});
</script>
