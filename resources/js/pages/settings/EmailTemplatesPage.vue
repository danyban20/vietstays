<template>
    <div>
        <div class="host-page-header">
            <h1 class="host-page-title">Email templates</h1>
        </div>

        <div v-if="loading" class="host-loading">Loading email templates…</div>

        <div v-else-if="accessDenied" class="host-form-error">
            Only administrators can manage email templates.
        </div>

        <p v-else-if="loadError" class="host-form-error">{{ loadError }}</p>

        <div v-else class="host-settings-card host-email-templates">
            <aside class="host-email-templates__nav">
                <button
                    v-for="code in codes"
                    :key="code"
                    type="button"
                    class="host-email-templates__nav-item"
                    :class="{ 'host-email-templates__nav-item--active': code === activeCode }"
                    @click="selectCode(code)"
                >
                    {{ templateLabel(code) }}
                </button>
            </aside>

            <section v-if="activeCode" class="host-email-templates__editor">
                <div class="host-email-templates__locales">
                    <button
                        v-for="locale in locales"
                        :key="locale.slug"
                        type="button"
                        class="host-email-templates__locale"
                        :class="{ 'host-email-templates__locale--active': locale.slug === activeLocale }"
                        @click="activeLocale = locale.slug"
                    >
                        {{ locale.short }}
                    </button>
                </div>

                <form class="host-email-templates__form" @submit.prevent="save">
                    <div class="host-field host-field--full">
                        <label class="host-field__label">Name</label>
                        <input
                            type="text"
                            class="host-input host-input--readonly"
                            :value="templateLabel(activeCode)"
                            readonly
                        />
                    </div>
                    <div class="host-field host-field--full">
                        <label class="host-field__label" for="template-subject">Subject</label>
                        <input
                            id="template-subject"
                            v-model="form.subject"
                            type="text"
                            class="host-input"
                        />
                    </div>
                    <div class="host-field host-field--full">
                        <label class="host-field__label">Message</label>
                        <TrumbowygEditor
                            :key="`${activeCode}-${activeLocale}`"
                            v-model="form.body"
                        />
                    </div>

                    <p v-if="saveError" class="host-form-error">{{ saveError }}</p>
                    <p v-if="success" class="host-form-success">{{ success }}</p>

                    <div class="host-email-templates__actions">
                        <button type="submit" class="host-btn host-btn--primary" :disabled="saving">
                            {{ saving ? 'Saving…' : 'Save' }}
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/api/client';
import TrumbowygEditor from '@/components/TrumbowygEditor.vue';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const saving = ref(false);
const accessDenied = ref(false);
const loadError = ref('');
const saveError = ref('');
const success = ref('');

const codes = ref([]);
const locales = ref([]);
const templates = ref({});

const activeCode = ref('');
const activeLocale = ref('en');

const form = reactive({
    subject: '',
    body: '',
});

const currentTemplate = computed(() => {
    if (! activeCode.value) {
        return null;
    }

    return templates.value?.[activeCode.value]?.locales?.[activeLocale.value] ?? null;
});

function templateLabel(code) {
    return templates.value?.[code]?.name || code.replace(/_/g, ' ');
}

function applyFormFromTemplate() {
    const template = currentTemplate.value;
    form.subject = template?.subject ?? '';
    form.body = template?.body ?? '';
}

function selectCode(code) {
    activeCode.value = code;
    router.replace({ hash: `#${code}` });
}

async function loadTemplates() {
    loading.value = true;
    loadError.value = '';
    accessDenied.value = false;

    try {
        const payload = await apiClient.get('/settings/email-templates');
        const data = payload?.data ?? {};

        codes.value = Array.isArray(data.codes) ? data.codes : [];
        locales.value = Array.isArray(data.locales) ? data.locales : [];
        templates.value = data.templates ?? {};

        const hashCode = route.hash.replace('#', '');
        activeCode.value = codes.value.includes(hashCode) ? hashCode : (codes.value[0] ?? '');
        activeLocale.value = 'en';
        applyFormFromTemplate();
    } catch (err) {
        if (err.status === 403) {
            accessDenied.value = true;
        } else {
            loadError.value = err.message || 'Could not load email templates.';
        }
    } finally {
        loading.value = false;
    }
}

async function save() {
    if (! activeCode.value) {
        return;
    }

    saving.value = true;
    saveError.value = '';
    success.value = '';

    try {
        const payload = await apiClient.put(`/settings/email-templates/${activeCode.value}`, {
            locale: activeLocale.value,
            subject: form.subject,
            body: form.body,
        });

        const saved = payload?.data;
        if (saved && templates.value[activeCode.value]) {
            templates.value[activeCode.value].locales[activeLocale.value] = saved;
        }

        success.value = payload?.message || 'Email template saved.';
    } catch (err) {
        saveError.value = err.message || 'Could not save email template.';
    } finally {
        saving.value = false;
    }
}

watch(activeLocale, () => {
    applyFormFromTemplate();
    success.value = '';
});

watch(activeCode, () => {
    applyFormFromTemplate();
    success.value = '';
});

onMounted(loadTemplates);
</script>
