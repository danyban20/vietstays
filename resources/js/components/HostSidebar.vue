<template>
    <aside class="host-sidebar">
        <div class="host-sidebar__brand">{{ t('brand') }}</div>

        <!-- ============ Superadmin / Supervisor: flat platform-wide nav ============ -->
        <template v-if="auth.canUseSuperadminTools">
            <nav class="host-sidebar__group host-sidebar__group--top">
                <router-link
                    :to="{ name: 'dashboard' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': isActive('dashboard') }"
                >
                    <SidebarIcon name="dashboard" />
                    <span class="host-sidebar__label">{{ t('nav.dashboard') }}</span>
                </router-link>
                <router-link
                    :to="{ name: 'apartments' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': ['apartments', 'apartment-detail'].includes(route.name) }"
                >
                    <SidebarIcon name="apartments" />
                    <span class="host-sidebar__label">{{ t('nav.apartments') }}</span>
                </router-link>
                <router-link
                    :to="{ name: 'bookings' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': ['bookings', 'bookings-calendar', 'booking-detail'].includes(route.name) }"
                >
                    <SidebarIcon name="bookings" />
                    <span class="host-sidebar__label">{{ t('nav.bookings') }}</span>
                </router-link>
            </nav>

            <nav class="host-sidebar__group">
                <button
                    type="button"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--parent-active': hostsPartnersOpen }"
                    @click="hostsPartnersOpen = !hostsPartnersOpen"
                >
                    <SidebarIcon name="sales-team" />
                    <span class="host-sidebar__label">{{ t('nav.hostsPartners') }}</span>
                    <span class="host-sidebar__chevron" :class="{ 'host-sidebar__chevron--open': hostsPartnersOpen }">▾</span>
                </button>
                <template v-if="hostsPartnersOpen">
                    <span class="host-sidebar__link host-sidebar__link--child host-sidebar__link--disabled">
                        {{ t('nav.managementCompanies') }}
                    </span>
                    <router-link
                        :to="{ name: 'superadmin-hosts' }"
                        class="host-sidebar__link host-sidebar__link--child"
                        :class="{ 'host-sidebar__link--active': isActive('superadmin-hosts') }"
                    >
                        {{ t('nav.hostsList') }}
                    </router-link>
                    <span class="host-sidebar__link host-sidebar__link--child host-sidebar__link--disabled">
                        {{ t('nav.hostAgents') }}
                    </span>
                    <span class="host-sidebar__link host-sidebar__link--child host-sidebar__link--disabled">
                        {{ t('nav.ambassadors') }}
                    </span>
                    <router-link
                        :to="{ name: 'host-applications' }"
                        class="host-sidebar__link host-sidebar__link--child"
                        :class="{ 'host-sidebar__link--active': isActive('host-applications') }"
                    >
                        {{ t('nav.hostApplications') }}
                    </router-link>
                    <span class="host-sidebar__link host-sidebar__link--child host-sidebar__link--disabled">
                        {{ t('nav.hostPointsSystem') }}
                    </span>
                </template>
            </nav>

            <nav class="host-sidebar__group">
                <router-link
                    :to="{ name: 'admin-users' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': isActive('admin-users') }"
                >
                    <SidebarIcon name="users" />
                    <span class="host-sidebar__label">{{ t('nav.users') }}</span>
                </router-link>
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="finance" />
                    <span class="host-sidebar__label">{{ t('nav.finance') }}</span>
                </span>
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="marketing" />
                    <span class="host-sidebar__label">{{ t('nav.marketing') }}</span>
                </span>
            </nav>

            <nav class="host-sidebar__group">
                <button
                    type="button"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--parent-active': platformContentOpen }"
                    @click="platformContentOpen = !platformContentOpen"
                >
                    <SidebarIcon name="content" />
                    <span class="host-sidebar__label">{{ t('nav.platformContent') }}</span>
                    <span class="host-sidebar__chevron" :class="{ 'host-sidebar__chevron--open': platformContentOpen }">▾</span>
                </button>
                <template v-if="platformContentOpen">
                    <router-link
                        :to="{ name: 'superadmin-buildings' }"
                        class="host-sidebar__link host-sidebar__link--child"
                        :class="{ 'host-sidebar__link--active': isActive('superadmin-buildings') }"
                    >
                        {{ t('nav.buildings') }}
                    </router-link>
                    <router-link
                        :to="{ name: 'superadmin-locations' }"
                        class="host-sidebar__link host-sidebar__link--child"
                        :class="{ 'host-sidebar__link--active': isActive('superadmin-locations') }"
                    >
                        {{ t('nav.locations') }}
                    </router-link>
                    <router-link
                        :to="{ name: 'superadmin-price-matrix' }"
                        class="host-sidebar__link host-sidebar__link--child"
                        :class="{ 'host-sidebar__link--active': isActive('superadmin-price-matrix') }"
                    >
                        {{ t('nav.priceMatrix') }}
                    </router-link>
                    <span class="host-sidebar__link host-sidebar__link--child host-sidebar__link--disabled">
                        {{ t('nav.houseRulesFacilities') }}
                    </span>
                    <span class="host-sidebar__link host-sidebar__link--child host-sidebar__link--disabled">
                        {{ t('nav.opsChecklists') }}
                    </span>
                </template>
            </nav>

            <nav class="host-sidebar__group">
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="settings" />
                    <span class="host-sidebar__label">{{ t('nav.configuration') }}</span>
                </span>
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="payouts" />
                    <span class="host-sidebar__label">{{ t('nav.payouts') }}</span>
                </span>
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="reports" />
                    <span class="host-sidebar__label">{{ t('nav.reports') }}</span>
                </span>
                <router-link
                    :to="{ name: 'messages' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': isActive('messages') }"
                >
                    <SidebarIcon name="communication" />
                    <span class="host-sidebar__label">{{ t('nav.communication') }}</span>
                </router-link>
            </nav>

            <nav class="host-sidebar__group host-sidebar__group--bottom">
                <div class="host-sidebar__group-title host-sidebar__group-title--admin">{{ t('nav.administration') }}</div>
                <router-link
                    :to="{ name: 'settings-email' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': isSettingsSection }"
                >
                    <SidebarIcon name="settings" />
                    <span class="host-sidebar__label">{{ t('nav.settings') }}</span>
                </router-link>
                <span class="host-sidebar__link host-sidebar__link--disabled host-sidebar__link--role-permissions">
                    <SidebarIcon name="administration" />
                    <span class="host-sidebar__label">{{ t('nav.rolePermissions') }}</span>
                </span>
            </nav>
        </template>

        <!-- ============ Host / Partner: personal "my" nav (unchanged) ============ -->
        <template v-else>
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

            <SidebarApartmentsBookings
                :open-sections="openSections"
                :customer-count="customerCount"
                @open-section="openSection"
            />

            <nav class="host-sidebar__group">
                <div class="host-sidebar__group-title">{{ t('nav.myTeam') }}</div>
                <router-link
                    :to="{ name: 'team-sales' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': isActive('team-sales') }"
                >
                    <SidebarIcon name="sales-team" />
                    <span class="host-sidebar__label">{{ t('nav.salesTeam') }}</span>
                </router-link>
                <router-link
                    :to="{ name: 'team-operations' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': isActive('team-operations') }"
                >
                    <SidebarIcon name="operations" />
                    <span class="host-sidebar__label">{{ t('nav.operationsTeam') }}</span>
                </router-link>
                <router-link
                    :to="{ name: 'team-management-company' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': isActive('team-management-company') }"
                >
                    <SidebarIcon name="management" />
                    <span class="host-sidebar__label">{{ t('nav.managementCompany') }}</span>
                </router-link>
                <span
                    class="host-sidebar__link host-sidebar__link--child host-sidebar__link--disabled"
                >
                    {{ t('nav.managementCompanyDemo') }}
                </span>
            </nav>

            <nav class="host-sidebar__group">
                <div class="host-sidebar__group-title">{{ t('nav.discountVisibility') }}</div>
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="campaign" />
                    <span class="host-sidebar__label">{{ t('nav.campaign') }}</span>
                </span>
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="ambassadors" />
                    <span class="host-sidebar__label">{{ t('nav.ambassadors') }}</span>
                </span>
            </nav>

            <nav class="host-sidebar__group">
                <div class="host-sidebar__group-title">{{ t('nav.financeReports') }}</div>
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="finance" />
                    <span class="host-sidebar__label">{{ t('nav.finance') }}</span>
                </span>
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="reports" />
                    <span class="host-sidebar__label">{{ t('nav.reports') }}</span>
                </span>
            </nav>

            <nav class="host-sidebar__group host-sidebar__group--bottom">
                <div class="host-sidebar__group-title">{{ t('nav.communicationAccount') }}</div>
                <router-link
                    :to="{ name: 'messages' }"
                    class="host-sidebar__link"
                    :class="{ 'host-sidebar__link--active': isActive('messages') }"
                >
                    <SidebarIcon name="communication" />
                    <span class="host-sidebar__label">{{ t('nav.communication') }}</span>
                </router-link>
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="marketing" />
                    <span class="host-sidebar__label">{{ t('nav.marketing') }}</span>
                </span>
                <span class="host-sidebar__link host-sidebar__link--disabled">
                    <SidebarIcon name="host-points" />
                    <span class="host-sidebar__label">{{ t('nav.hostPoints') }}</span>
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
import { onMounted, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import apiClient from '@/api/client';
import SidebarApartmentsBookings from '@/components/SidebarApartmentsBookings.vue';
import SidebarIcon from '@/components/SidebarIcon.vue';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const { t } = useI18n();
const auth = useAuthStore();

const customerCount = ref(0);
const hostsPartnersOpen = ref(false);
const platformContentOpen = ref(true);

const openSections = reactive({
    apartments: true,
    bookings: true,
});

const isSettingsSection = () =>
    ['settings-email', 'settings-email-templates', 'settings-languages'].includes(route.name);

function isActive(name) {
    return route.name === name;
}

function openSection(section) {
    openSections[section] = true;
}

function syncOpenSectionsFromRoute() {
    if (['apartments', 'apartment-detail', 'apartment-add'].includes(route.name)) {
        openSections.apartments = true;
    }

    if (['bookings', 'bookings-calendar', 'booking-detail', 'customers', 'customer-detail'].includes(route.name)) {
        openSections.bookings = true;
    }
}

watch(() => route.name, syncOpenSectionsFromRoute, { immediate: true });

onMounted(() => {
    syncOpenSectionsFromRoute();
    loadCustomerCount();
});

async function loadCustomerCount() {
    try {
        const response = await apiClient.get('/customers?count_only=1');
        customerCount.value = Number(response.total ?? 0);
    } catch {
        customerCount.value = 0;
    }
}
</script>
