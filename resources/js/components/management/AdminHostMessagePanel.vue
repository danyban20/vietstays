<template>
    <div class="admin-host-messages">
        <h3 class="admin-host-messages__title">{{ t('mgmtCompany.messagesTitle') }}</h3>
        <p v-if="loading" class="host-team-empty">{{ t('common.loading') }}</p>
        <template v-else>
            <div v-if="!messages.length" class="admin-host-messages__empty">{{ t('mgmtCompany.messagesEmpty') }}</div>
            <div v-else class="admin-host-messages__list">
                <div
                    v-for="message in messages"
                    :key="message.id"
                    class="admin-host-messages__bubble"
                    :class="message.sender_role === 'host' ? 'admin-host-messages__bubble--host' : 'admin-host-messages__bubble--admin'"
                >
                    <div class="admin-host-messages__meta">{{ message.sender_name }}</div>
                    <div class="admin-host-messages__body">{{ message.body }}</div>
                </div>
            </div>
        </template>
        <form class="admin-host-messages__form" @submit.prevent="send">
            <textarea
                v-model="draft"
                class="host-input admin-host-messages__input"
                rows="2"
                :placeholder="t('mgmtCompany.messagePlaceholder')"
            />
            <button type="submit" class="host-btn host-btn--primary" :disabled="sending || !draft.trim()">
                {{ sending ? t('common.loading') : t('mgmtCompany.send') }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import apiClient from '@/api/client';

const { t } = useI18n();

const messages = ref([]);
const loading = ref(true);
const draft = ref('');
const sending = ref(false);

async function loadMessages() {
    loading.value = true;

    try {
        const res = await apiClient.get('/messages/admin-thread');
        messages.value = res?.data ?? [];
    } catch {
        messages.value = [];
    } finally {
        loading.value = false;
    }
}

async function send() {
    if (!draft.value.trim() || sending.value) {
        return;
    }

    sending.value = true;

    try {
        const res = await apiClient.post('/messages/admin-thread', { body: draft.value.trim() });
        if (res?.data) {
            messages.value = [...messages.value, res.data];
        }
        draft.value = '';
    } catch {
        // Leave the draft in place so the host can retry.
    } finally {
        sending.value = false;
    }
}

onMounted(loadMessages);
</script>

<style scoped>
.admin-host-messages {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #eee6d0;
    width: 100%;
}

.admin-host-messages__title {
    margin: 0 0 8px;
    font-size: 14px;
    font-weight: 700;
    color: #1c2b23;
}

.admin-host-messages__empty {
    font-size: 13px;
    color: #5e6b62;
    margin-bottom: 10px;
}

.admin-host-messages__list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 240px;
    overflow-y: auto;
    margin-bottom: 10px;
}

.admin-host-messages__bubble {
    max-width: 80%;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 13.5px;
    line-height: 1.4;
}

.admin-host-messages__bubble--admin {
    align-self: flex-start;
    background: #f0ecdf;
    color: #1c2b23;
}

.admin-host-messages__bubble--host {
    align-self: flex-end;
    background: #12352b;
    color: #f2ead9;
}

.admin-host-messages__meta {
    font-size: 11px;
    opacity: 0.7;
    margin-bottom: 2px;
}

.admin-host-messages__form {
    display: flex;
    gap: 8px;
    align-items: flex-end;
}

.admin-host-messages__input {
    flex: 1;
    resize: vertical;
}
</style>
