<template>
    <div class="host-app-page">
        <div v-if="loading && !thread" class="host-app-page__header">
            <p>{{ t('guestMessage.loading') }}</p>
        </div>

        <div v-else-if="!thread" class="host-app-page__header">
            <p>{{ t('guestMessage.notFound') }}</p>
            <router-link :to="{ name: 'home' }" class="public-btn public-btn--primary">
                {{ t('guestMessage.backHome') }}
            </router-link>
        </div>

        <template v-else>
            <header class="host-app-page__header">
                <h1>{{ t('guestMessage.title', { host: thread.hostName }) }}</h1>
            </header>

            <section class="host-app-card host-messages-thread host-messages-thread--guest">
                <div ref="scrollEl" class="host-messages-thread__body">
                    <template v-for="group in groupedMessages" :key="group.label">
                        <div class="host-messages-date-divider">{{ group.label }}</div>
                        <div
                            v-for="message in group.items"
                            :key="message.id"
                            class="host-messages-bubble"
                            :class="message.sender === 'guest' ? 'host-messages-bubble--host' : 'host-messages-bubble--guest'"
                        >
                            <p class="host-messages-bubble__text">{{ message.body }}</p>
                            <span class="host-messages-bubble__time">{{ timeOnly(message.sentAt) }}</span>
                        </div>
                    </template>
                    <p v-if="!thread.messages.length" class="host-messages-empty">{{ t('guestMessage.empty') }}</p>
                </div>

                <div v-if="error" class="host-app-alert host-app-alert--error">
                    <ul>
                        <li>{{ error }}</li>
                    </ul>
                </div>

                <div class="host-messages-composer">
                    <textarea
                        v-model="composerText"
                        class="host-messages-composer__input"
                        :placeholder="t('guestMessage.composerPlaceholder')"
                        rows="2"
                        @keydown.enter.exact.prevent="send"
                    />
                    <button
                        type="button"
                        class="public-btn public-btn--primary"
                        :disabled="!composerText.trim() || sending"
                        @click="send"
                    >
                        {{ sending ? t('guestMessage.sending') : t('guestMessage.send') }}
                    </button>
                </div>
            </section>

            <section v-if="thread.bookings?.length" class="host-app-card">
                <h2>{{ t('guestMessage.yourStays') }}</h2>
                <p v-for="booking in thread.bookings" :key="booking.id">
                    {{ booking.apartment }} · {{ booking.dates }}
                </p>
            </section>
        </template>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute } from 'vue-router';
import apiClient from '@/api/client';

const POLL_MS = 4000;

const route = useRoute();
const { t } = useI18n();

const loading = ref(true);
const thread = ref(null);
const composerText = ref('');
const sending = ref(false);
const error = ref('');
const scrollEl = ref(null);

let pollTimer = null;

function timeOnly(iso) {
    if (!iso) {
        return '';
    }

    return new Intl.DateTimeFormat('en', { hour: '2-digit', minute: '2-digit' }).format(new Date(iso));
}

function dateLabel(iso) {
    const date = new Date(iso);
    const startOfDay = (value) => new Date(value.getFullYear(), value.getMonth(), value.getDate());
    const today = startOfDay(new Date());
    const day = startOfDay(date);
    const diffDays = Math.round((today - day) / 86400000);

    if (diffDays === 0) {
        return 'Today';
    }

    if (diffDays === 1) {
        return 'Yesterday';
    }

    return new Intl.DateTimeFormat('en', { day: 'numeric', month: 'short', year: 'numeric' }).format(date);
}

const groupedMessages = computed(() => {
    const groups = [];
    let currentLabel = null;
    let currentItems = null;

    for (const message of thread.value?.messages ?? []) {
        const label = dateLabel(message.sentAt);

        if (label !== currentLabel) {
            currentLabel = label;
            currentItems = [];
            groups.push({ label, items: currentItems });
        }

        currentItems.push(message);
    }

    return groups;
});

function scrollToBottom() {
    if (scrollEl.value) {
        scrollEl.value.scrollTop = scrollEl.value.scrollHeight;
    }
}

async function loadThread() {
    try {
        const response = await apiClient.get(`/public/messages/${route.params.token}`);
        thread.value = response.data ?? null;
        await nextTick();
        scrollToBottom();
    } catch {
        thread.value = thread.value ?? null;
    } finally {
        loading.value = false;
    }
}

async function send() {
    const text = composerText.value.trim();

    if (!text || sending.value) {
        return;
    }

    sending.value = true;
    error.value = '';

    try {
        const response = await apiClient.post(`/public/messages/${route.params.token}`, { text });
        thread.value = response.data ?? thread.value;
        composerText.value = '';
        await nextTick();
        scrollToBottom();
    } catch (err) {
        error.value = err.message || t('guestMessage.sendFailed');
    } finally {
        sending.value = false;
    }
}

function stopPoll() {
    if (pollTimer) {
        clearInterval(pollTimer);
        pollTimer = null;
    }
}

function startPoll() {
    stopPoll();

    if (document.hidden) {
        return;
    }

    pollTimer = setInterval(loadThread, POLL_MS);
}

async function handleVisibilityChange() {
    if (document.hidden) {
        stopPoll();

        return;
    }

    await loadThread();
    startPoll();
}

onMounted(async () => {
    await loadThread();
    startPoll();
    document.addEventListener('visibilitychange', handleVisibilityChange);
});

onUnmounted(() => {
    stopPoll();
    document.removeEventListener('visibilitychange', handleVisibilityChange);
});
</script>
