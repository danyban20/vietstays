import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import apiClient from '@/api/client';
import { DEFAULT_LOCALE, loadLocaleMessages, setUserLocale } from '@/i18n';

export const useLocaleStore = defineStore('locale', () => {
    const current = ref(DEFAULT_LOCALE);
    const preference = ref('auto');
    const label = ref('English');
    const short = ref('EN');
    const intl = ref('en_US');
    const available = ref([]);
    const menuOpen = ref(false);
    const loading = ref(false);

    const currentInfo = computed(() => available.value.find((item) => item.slug === current.value));

    async function fetchAvailable() {
        const payload = await apiClient.get('/locales');
        available.value = payload?.data?.locales ?? [];
    }

    async function bootstrap() {
        loading.value = true;
        try {
            await fetchAvailable();
            const payload = await apiClient.get('/locale');
            applyPayload(payload?.data ?? payload);
            await loadLocaleMessages(current.value);
        } catch {
            current.value = DEFAULT_LOCALE;
            await loadLocaleMessages(DEFAULT_LOCALE);
        } finally {
            loading.value = false;
        }
    }

    function applyPayload(payload) {
        current.value = payload?.locale ?? DEFAULT_LOCALE;
        preference.value = payload?.preference ?? 'auto';
        label.value = payload?.label ?? 'English';
        short.value = payload?.short ?? 'EN';
        intl.value = payload?.intl ?? 'en_US';
    }

    async function selectLocale(slug) {
        loading.value = true;
        menuOpen.value = false;
        try {
            const payload = await setUserLocale(slug);
            applyPayload(payload);
        } finally {
            loading.value = false;
        }
    }

    return {
        current,
        preference,
        label,
        short,
        intl,
        available,
        menuOpen,
        loading,
        currentInfo,
        bootstrap,
        fetchAvailable,
        selectLocale,
    };
});
