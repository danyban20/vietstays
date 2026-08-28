<template>
    <div class="host-settings-page">
        <header class="host-page-header">
            <div>
                <h1 class="host-page-header__title">{{ t('languages.title') }}</h1>
                <p class="host-page-header__subtitle">{{ t('locale.chooseAdminLanguage') }}</p>
            </div>
        </header>

        <div v-if="error" class="host-alert host-alert--error">{{ error }}</div>
        <div v-if="success" class="host-alert host-alert--success">{{ success }}</div>

        <div class="host-languages-grid">
            <section class="host-panel-card">
                <h2 class="host-panel-card__title">{{ t('languages.installed') }}</h2>
                <table class="host-data-table host-data-table--compact">
                    <thead>
                        <tr>
                            <th>{{ t('languages.language') }}</th>
                            <th>{{ t('languages.stringsLoaded') }}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in locales"
                            :key="item.slug"
                            :class="{ 'host-data-table__row--active': item.slug === selectedSlug }"
                        >
                            <td>
                                <strong>{{ item.label }}</strong>
                                <div class="host-muted">{{ item.short }}</div>
                                <span v-if="item.is_default" class="host-tag host-tag--draft">{{ t('languages.default') }}</span>
                            </td>
                            <td>
                                <span v-if="item.is_default">{{ t('languages.sourceLanguage') }}</span>
                                <template v-else>
                                    {{ item.strings }}
                                    <div v-if="item.modified" class="host-muted">{{ item.modified }}</div>
                                </template>
                            </td>
                            <td class="host-data-table__actions">
                                <button
                                    type="button"
                                    class="host-btn host-btn--ghost host-btn--sm"
                                    @click="selectLocale(item.slug)"
                                >
                                    {{ item.is_default ? t('nav.settings') : t('languages.manage') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <section v-if="selectedSlug && !selectedInfo?.is_default" class="host-panel-card">
                <h2 class="host-panel-card__title">
                    {{ selectedInfo?.label }} — {{ t('languages.preview') }}
                </h2>
                <p class="host-muted">{{ t('languages.uploadHint') }}</p>

                <textarea
                    v-model="jsonDraft"
                    class="host-input host-input--textarea host-languages-editor"
                    rows="18"
                    spellcheck="false"
                />

                <div class="host-form-actions">
                    <button type="button" class="host-btn host-btn--primary" :disabled="saving" @click="saveTranslations">
                        {{ saving ? t('common.loading') : t('languages.saveTranslations') }}
                    </button>
                    <button
                        v-if="!selectedInfo?.is_default"
                        type="button"
                        class="host-btn host-btn--danger host-btn--ghost"
                        @click="deleteLocale"
                    >
                        {{ t('languages.deleteLanguage') }}
                    </button>
                </div>
            </section>

            <section class="host-panel-card">
                <h2 class="host-panel-card__title">{{ t('languages.addLanguage') }}</h2>
                <form class="host-form-grid" @submit.prevent="addLanguage">
                    <label class="host-field">
                        <span class="host-field__label">{{ t('languages.languageCode') }}</span>
                        <input v-model="newLanguage.slug" class="host-input" maxlength="10" pattern="[a-z0-9_]{2,10}" required />
                    </label>
                    <label class="host-field">
                        <span class="host-field__label">{{ t('languages.displayName') }}</span>
                        <input v-model="newLanguage.label" class="host-input" maxlength="80" required />
                    </label>
                    <label class="host-field">
                        <span class="host-field__label">{{ t('languages.shortCode') }}</span>
                        <input v-model="newLanguage.short" class="host-input" maxlength="5" />
                    </label>
                    <div class="host-form-actions">
                        <button type="submit" class="host-btn host-btn--primary" :disabled="adding">
                            {{ adding ? t('common.loading') : t('languages.addLanguage') }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import apiClient from '@/api/client';
import { useLocaleStore } from '@/stores/locale';

const { t } = useI18n();
const localeStore = useLocaleStore();

const locales = ref([]);
const selectedSlug = ref('');
const jsonDraft = ref('{}');
const error = ref('');
const success = ref('');
const saving = ref(false);
const adding = ref(false);
const newLanguage = ref({ slug: '', label: '', short: '' });

const selectedInfo = computed(() => locales.value.find((item) => item.slug === selectedSlug.value));

async function loadLocales() {
    const payload = await apiClient.get('/settings/languages');
    locales.value = payload?.data?.locales ?? [];
    if (! selectedSlug.value && locales.value.length) {
        const firstEditable = locales.value.find((item) => ! item.is_default) ?? locales.value[0];
        selectedSlug.value = firstEditable.slug;
    }
}

async function selectLocale(slug) {
    selectedSlug.value = slug;
    error.value = '';
    success.value = '';

    const info = locales.value.find((item) => item.slug === slug);
    if (! info || info.is_default) {
        jsonDraft.value = '{}';
        return;
    }

    try {
        const payload = await apiClient.get(`/settings/languages/${slug}`);
        jsonDraft.value = JSON.stringify(payload?.data?.translations ?? {}, null, 2);
    } catch (err) {
        error.value = err?.message ?? 'Could not load translations.';
    }
}

async function saveTranslations() {
    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        const translations = JSON.parse(jsonDraft.value);
        const payload = await apiClient.put(`/settings/languages/${selectedSlug.value}`, { translations });
        success.value = payload?.message ?? t('locale.preferenceSaved');
        await loadLocales();
        if (localeStore.current === selectedSlug.value) {
            await localeStore.bootstrap();
        }
    } catch (err) {
        error.value = err?.message ?? 'Invalid JSON or save failed.';
    } finally {
        saving.value = false;
    }
}

async function addLanguage() {
    adding.value = true;
    error.value = '';
    success.value = '';

    try {
        const payload = await apiClient.post('/settings/languages', newLanguage.value);
        success.value = payload?.message ?? 'Language added.';
        newLanguage.value = { slug: '', label: '', short: '' };
        await loadLocales();
        if (payload?.data?.locale) {
            await selectLocale(payload.data.locale);
        }
        await localeStore.fetchAvailable();
    } catch (err) {
        error.value = err?.message ?? 'Could not add language.';
    } finally {
        adding.value = false;
    }
}

async function deleteLocale() {
    if (! window.confirm(t('languages.deleteConfirm'))) {
        return;
    }

    try {
        await apiClient.delete(`/settings/languages/${selectedSlug.value}`);
        success.value = 'Language deleted.';
        selectedSlug.value = '';
        jsonDraft.value = '{}';
        await loadLocales();
        await localeStore.fetchAvailable();
    } catch (err) {
        error.value = err?.message ?? 'Could not delete language.';
    }
}

watch(selectedSlug, (slug) => {
    if (slug) {
        selectLocale(slug);
    }
});

onMounted(loadLocales);
</script>
