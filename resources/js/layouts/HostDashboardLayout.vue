<template>
    <div
        class="host-viewport-shell"
        :class="{ 'host-viewport-shell--fluid': stageFluid }"
    >
        <div
            class="host-viewport-stage"
            :class="{ 'host-viewport-stage--fluid': stageFluid }"
            :style="stageStyle"
        >
            <div
                class="host-app"
                :class="{ 'host-app--narrow': narrowLayout }"
            >
                <HostSidebar />

                <div class="host-main">
                    <HostTopBar />

                    <header class="host-breadcrumb">
                        <span>{{ t('brand') }}</span>
                        <template v-for="(crumb, index) in breadcrumbs" :key="`${crumb.label}-${index}`">
                            <span class="host-breadcrumb__sep">›</span>
                            <router-link v-if="crumb.to" :to="crumb.to">
                                {{ crumb.label }}
                            </router-link>
                            <span v-else class="host-breadcrumb__current">{{ crumb.label }}</span>
                        </template>
                        <template v-if="pageTitle">
                            <span class="host-breadcrumb__sep">›</span>
                            <span class="host-breadcrumb__current">{{ pageTitle }}</span>
                        </template>
                    </header>

                    <main
                        class="host-content"
                        :class="{
                            'host-content--wizard': route.meta.wizard,
                            'host-content--detail': route.meta.detailShell,
                        }"
                    >
                        <router-view />
                    </main>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import HostSidebar from '@/components/HostSidebar.vue';
import HostTopBar from '@/components/HostTopBar.vue';
import { pageTitle } from '@/composables/usePageTitle';
import { useHostViewportScale } from '@/composables/useHostViewportScale';
import { useAuthStore } from '@/stores/auth';

const route = useRoute();
const auth = useAuthStore();
const { t, locale } = useI18n();

const { stageW, stageH, stageScale, stageFluid, narrowLayout } = useHostViewportScale({
    fluid: false,
    stageW: 1520,
    stageH: 720,
});

const fittedStageH = computed(() => {
    if (stageFluid.value) {
        return null;
    }

    const vh = typeof window !== 'undefined'
        ? window.innerHeight || document.documentElement.clientHeight || stageH.value
        : stageH.value;
    return Math.max(stageH.value, Math.ceil(vh / (stageScale.value || 1)));
});

const stageStyle = computed(() => {
    if (stageFluid.value) {
        return {};
    }

    return {
        width: `${stageW.value}px`,
        height: `${fittedStageH.value}px`,
        transform: `scale(${stageScale.value})`,
    };
});

const breadcrumbs = computed(() => {
    locale.value;
    auth.role;

    const crumbs = [{ label: t('nav.manager'), to: { name: 'dashboard' } }];

    if (route.name === 'dashboard') {
        crumbs.push({ label: t('nav.dashboard') });
        return crumbs;
    }

    if (route.name === 'bookings' || route.name === 'bookings-calendar' || route.name === 'booking-detail') {
        crumbs.push({ label: t('nav.myApartments'), to: { name: 'apartments' } });
        if (route.name === 'booking-detail') {
            crumbs.push({ label: t('nav.myBookings'), to: { name: 'bookings' } });
            if (route.meta?.breadcrumbKey) {
                crumbs.push({ label: t(route.meta.breadcrumbKey) });
            }
        } else {
            crumbs.push({ label: t('nav.myBookings') });
        }
    }

    if (route.name === 'apartments' || route.name === 'apartment-detail' || route.name === 'apartment-add') {
        crumbs.push({ label: t('nav.apartmentsBookings'), to: { name: 'apartments' } });
    }

    if (route.meta?.settingsSection) {
        crumbs.push({ label: t('nav.settings') });
    }

    if (route.name === 'host-applications' || route.name === 'host-application-detail') {
        crumbs.push({ label: t('nav.hostsPartners') });
        crumbs.push({ label: t('nav.hostApplications') });
        return crumbs;
    }

    if (route.name === 'admin-users') {
        crumbs.push({ label: t('nav.users') });
        return crumbs;
    }

    if (route.meta?.breadcrumbKey) {
        if (!(route.name === 'apartment-detail' && pageTitle.value)) {
            crumbs.push({ label: t(route.meta.breadcrumbKey) });
        }
    }

    return crumbs;
});
</script>
