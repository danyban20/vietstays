<template>
    <header class="host-topbar">
        <div class="host-topbar__search">
            <svg class="host-topbar__search-icon" width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2" />
                <line x1="16.5" y1="16.5" x2="21" y2="21" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
            <input
                v-model="searchQuery"
                type="search"
                class="host-topbar__search-input"
                :placeholder="t('common.searchPlaceholder')"
            />
        </div>

        <div class="host-topbar__spacer" />

        <div class="host-topbar__actions">
            <button type="button" class="host-topbar__icon-btn" aria-label="Messages">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path
                        d="M21 12a8 8 0 0 1-8 8H8l-5 3 1.2-4.4A8 8 0 1 1 21 12z"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                <span class="host-topbar__badge">3</span>
            </button>

            <button type="button" class="host-topbar__icon-btn" aria-label="Notifications">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path
                        d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"
                        stroke="currentColor"
                        stroke-width="1.9"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path d="M13.73 21a2 2 0 0 1-3.46 0" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" />
                </svg>
            </button>

            <div class="host-topbar__locale-wrap">
                <button
                    type="button"
                    class="host-topbar__locale"
                    :aria-expanded="localeStore.menuOpen ? 'true' : 'false'"
                    aria-haspopup="listbox"
                    @click="localeStore.menuOpen = !localeStore.menuOpen"
                >
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8" />
                        <path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18" stroke="currentColor" stroke-width="1.8" />
                    </svg>
                    <span>{{ localeStore.label }}</span>
                    <span class="host-topbar__chevron">▾</span>
                </button>

                <div v-if="localeStore.menuOpen" class="host-topbar__locale-menu" role="listbox">
                    <button
                        v-for="item in localeStore.available"
                        :key="item.slug"
                        type="button"
                        class="host-topbar__locale-option"
                        :class="{ 'host-topbar__locale-option--active': item.slug === localeStore.current }"
                        role="option"
                        :aria-selected="item.slug === localeStore.current"
                        @click="localeStore.selectLocale(item.slug)"
                    >
                        <span>{{ item.label }}</span>
                        <span class="host-topbar__locale-short">{{ item.short }}</span>
                    </button>
                </div>
            </div>

            <div class="host-topbar__profile">
                <span class="host-topbar__avatar">{{ initials }}</span>
                <div class="host-topbar__profile-text">
                    <span class="host-topbar__profile-name">{{ displayName }}</span>
                    <span class="host-topbar__profile-meta">{{ apartmentCountLabel }}</span>
                </div>
            </div>
        </div>
    </header>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import apiClient from '@/api/client';
import { useLocaleStore } from '@/stores/locale';
import { useAuthStore } from '@/stores/auth';

const { t } = useI18n();
const localeStore = useLocaleStore();
const auth = useAuthStore();

const searchQuery = ref('');
const apartmentCount = ref(null);

const displayName = computed(() => auth.user?.name ?? 'Host');
const initials = computed(() => {
    const parts = displayName.value.trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return 'H';
    return parts
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
});
const apartmentCountLabel = computed(() => {
    if (apartmentCount.value == null) return t('common.hostDashboard');
    const n = apartmentCount.value;
    return n === 1
        ? t('common.hostApartments', { count: n })
        : t('common.hostApartments_other', { count: n });
});

function closeLocaleMenu(event) {
    if (!event.target.closest('.host-topbar__locale-wrap')) {
        localeStore.menuOpen = false;
    }
}

onMounted(async () => {
    document.addEventListener('click', closeLocaleMenu);

    if (!auth.loaded) {
        await auth.fetchUser();
    }

    try {
        const aptRes = await apiClient.get('/apartments');
        const list = Array.isArray(aptRes?.data) ? aptRes.data : [];
        apartmentCount.value = list.length;
    } catch {
        apartmentCount.value = null;
    }
});

onUnmounted(() => {
    document.removeEventListener('click', closeLocaleMenu);
});
</script>
