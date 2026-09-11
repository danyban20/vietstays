<template>
    <div class="host-app-page">
        <div v-if="accepted" class="host-app-success">
            <h1>{{ t('teamInvite.acceptedTitle') }}</h1>
            <p>{{ t('teamInvite.acceptedText', { inviter: invite?.inviterName ?? '' }) }}</p>
            <router-link :to="{ name: 'home' }" class="public-btn public-btn--primary">
                {{ t('teamInvite.backHome') }}
            </router-link>
        </div>

        <div v-else-if="loading" class="host-app-page__header">
            <p>{{ t('teamInvite.loading') }}</p>
        </div>

        <div v-else-if="!invite" class="host-app-page__header">
            <p>{{ t('teamInvite.notFound') }}</p>
        </div>

        <template v-else>
            <header class="host-app-page__header">
                <h1>{{ t('teamInvite.title', { inviter: invite.inviterName }) }}</h1>
                <p>{{ t('teamInvite.roleLine', { role: invite.role }) }}</p>
                <p v-if="invite.area">{{ t('teamInvite.areaLine', { area: invite.area }) }}</p>
            </header>

            <div v-if="error" class="host-app-alert host-app-alert--error">
                <ul>
                    <li>{{ error }}</li>
                </ul>
            </div>

            <section class="host-app-card">
                <button type="button" class="public-btn public-btn--primary public-btn--block" :disabled="accepting" @click="accept">
                    {{ accepting ? t('teamInvite.accepting') : t('teamInvite.accept') }}
                </button>
            </section>
        </template>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import apiClient from '@/api/client';

const route = useRoute();
const { t } = useI18n();

const loading = ref(true);
const accepting = ref(false);
const accepted = ref(false);
const error = ref('');
const invite = ref(null);

async function loadInvite() {
    loading.value = true;

    try {
        const response = await apiClient.get(`/public/team-invitations/${route.params.token}`);
        invite.value = response.data ?? null;
    } catch {
        invite.value = null;
    } finally {
        loading.value = false;
    }
}

async function accept() {
    if (accepting.value) {
        return;
    }

    accepting.value = true;
    error.value = '';

    try {
        await apiClient.post(`/public/team-invitations/${route.params.token}/accept`);
        accepted.value = true;
    } catch (err) {
        error.value = err.message || t('teamInvite.acceptFailed');
    } finally {
        accepting.value = false;
    }
}

onMounted(loadInvite);
</script>
