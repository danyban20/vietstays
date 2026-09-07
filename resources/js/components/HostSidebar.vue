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
            <span class="host-sidebar__link host-sidebar__link--disabled">
                <SidebarIcon name="communication" />
                <span class="host-sidebar__label">{{ t('nav.communication') }}</span>
            </span>
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
    </aside>
</template>

<script setup>
import { onMounted, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import apiClient from '@/api/client';
import SidebarApartmentsBookings from '@/components/SidebarApartmentsBookings.vue';
import SidebarIcon from '@/components/SidebarIcon.vue';

const route = useRoute();
const { t } = useI18n();

const customerCount = ref(0);

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
