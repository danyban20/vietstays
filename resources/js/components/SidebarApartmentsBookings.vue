<template>
    <nav class="host-sidebar__group">
        <div class="host-sidebar__group-title">{{ t('nav.apartmentsBookings') }}</div>

        <button
            type="button"
            class="host-sidebar__link"
            :class="{ 'host-sidebar__link--parent-active': isOpen('apartments') }"
            @click="onApartmentsClick"
        >
            <SidebarIcon name="apartments" />
            <span class="host-sidebar__label">{{ t('nav.myApartments') }}</span>
            <span
                class="host-sidebar__chevron"
                :class="{ 'host-sidebar__chevron--open': isOpen('apartments') }"
            >▾</span>
        </button>

        <template v-if="isOpen('apartments')">
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
                :class="{ 'host-sidebar__link--active': isAllApartmentsActive }"
            >
                {{ t('nav.allApartments') }}
            </router-link>
        </template>

        <button
            type="button"
            class="host-sidebar__link"
            :class="{ 'host-sidebar__link--parent-active': isOpen('bookings') }"
            @click="onBookingsClick"
        >
            <SidebarIcon name="bookings" />
            <span class="host-sidebar__label">{{ t('nav.myBookings') }}</span>
            <span
                class="host-sidebar__chevron"
                :class="{ 'host-sidebar__chevron--open': isOpen('bookings') }"
            >▾</span>
        </button>

        <template v-if="isOpen('bookings')">
            <router-link
                :to="{ name: 'bookings', query: { add: 'manual' } }"
                class="host-sidebar__link host-sidebar__link--child"
            >
                {{ t('nav.addNew') }}
            </router-link>
            <router-link
                :to="{ name: 'bookings' }"
                class="host-sidebar__link host-sidebar__link--child"
                :class="{ 'host-sidebar__link--active': isAllBookingsActive }"
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
            :class="{ 'host-sidebar__link--active': isCustomersActive }"
        >
            <SidebarIcon name="customers" />
            <span class="host-sidebar__label">{{ t('nav.customers') }}</span>
            <span class="host-sidebar__count">{{ customerCount }}</span>
        </router-link>
    </nav>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import SidebarIcon from '@/components/SidebarIcon.vue';

const props = defineProps({
    openSections: {
        type: Object,
        required: true,
    },
    customerCount: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['open-section']);

const route = useRoute();
const router = useRouter();
const { t } = useI18n();

const isAllApartmentsActive = computed(() =>
    ['apartments', 'apartment-detail'].includes(route.name),
);

const isAllBookingsActive = computed(() =>
    ['bookings', 'booking-detail'].includes(route.name),
);

const isCustomersActive = computed(() =>
    ['customers', 'customer-detail'].includes(route.name),
);

function isOpen(section) {
    return Boolean(props.openSections[section]);
}

function onApartmentsClick() {
    emit('open-section', 'apartments');
    if (!isAllApartmentsActive.value && route.name !== 'apartment-add') {
        router.push({ name: 'apartments' });
    }
}

function onBookingsClick() {
    emit('open-section', 'bookings');
    if (!isAllBookingsActive.value && route.name !== 'bookings-calendar') {
        router.push({ name: 'bookings' });
    }
}
</script>
