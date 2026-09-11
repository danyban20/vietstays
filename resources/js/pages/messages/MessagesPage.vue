<template>
    <div class="host-messages-page">
        <div class="host-messages-list">
            <div class="host-messages-list__head">
                <h1 class="host-page-title">{{ t('messages.title') }}</h1>
                <p class="host-page-subtitle">{{ t('messages.subtitle') }}</p>
                <div class="host-team-toolbar__search host-messages-list__search">
                    <span class="host-team-toolbar__search-icon">⌕</span>
                    <input
                        v-model="search"
                        type="search"
                        class="host-team-toolbar__input"
                        :placeholder="t('messages.searchPlaceholder')"
                    />
                </div>
                <div class="host-team-tabs host-messages-list__tabs">
                    <button
                        type="button"
                        class="host-team-tabs__btn"
                        :class="{ 'host-team-tabs__btn--active': filter === 'all' }"
                        @click="filter = 'all'"
                    >
                        {{ t('messages.filterAll') }} ({{ threads.length }})
                    </button>
                    <button
                        type="button"
                        class="host-team-tabs__btn"
                        :class="{ 'host-team-tabs__btn--active': filter === 'unread' }"
                        @click="filter = 'unread'"
                    >
                        {{ t('messages.filterUnread') }} ({{ unreadThreadCount }})
                    </button>
                </div>
            </div>

            <p v-if="loadingThreads" class="host-team-empty">{{ t('messages.loading') }}</p>
            <template v-else>
                <button
                    v-for="thread in visibleThreads"
                    :key="thread.customerId"
                    type="button"
                    class="host-messages-thread-row"
                    :class="{ 'host-messages-thread-row--active': thread.customerId === activeCustomerId }"
                    @click="selectThread(thread.customerId)"
                >
                    <span class="host-team-avatar" :style="{ background: thread.bg }">{{ thread.initials }}</span>
                    <span class="host-messages-thread-row__body">
                        <span class="host-messages-thread-row__top">
                            <span class="host-messages-thread-row__name">{{ thread.name }}</span>
                            <span class="host-messages-thread-row__time">{{ relativeTime(thread.lastMessageAt) }}</span>
                        </span>
                        <span class="host-messages-thread-row__meta">
                            {{ thread.nextApt || thread.nextLabel || '—' }}
                        </span>
                        <span class="host-messages-thread-row__preview">{{ thread.lastMessage || '—' }}</span>
                    </span>
                    <span v-if="thread.unreadCount" class="host-messages-thread-row__badge">{{ thread.unreadCount }}</span>
                </button>
                <p v-if="!visibleThreads.length" class="host-team-empty">
                    {{ t('messages.noThreads') }}<br />
                    <span class="host-messages-list__hint">{{ t('messages.noThreadsHint') }}</span>
                </p>
            </template>
        </div>

        <div v-if="activeCustomerId" class="host-messages-thread">
            <div class="host-messages-thread__head">
                <span class="host-team-avatar" :style="{ background: activeThreadMeta?.bg }">
                    {{ activeThreadMeta?.initials }}
                </span>
                <div>
                    <div class="host-messages-thread__name">{{ activeThreadMeta?.name }}</div>
                    <div class="host-messages-thread__sub">{{ activeThreadMeta?.nextLabel || '—' }}</div>
                </div>
            </div>

            <div ref="scrollEl" class="host-messages-thread__body">
                <p v-if="loadingMessages && !messages.length" class="host-team-empty">{{ t('messages.loading') }}</p>
                <template v-for="group in groupedMessages" :key="group.label">
                    <div class="host-messages-date-divider">{{ group.label }}</div>
                    <div
                        v-for="message in group.items"
                        :key="message.id"
                        class="host-messages-bubble"
                        :class="message.sender === 'host' ? 'host-messages-bubble--host' : 'host-messages-bubble--guest'"
                    >
                        <p class="host-messages-bubble__text">{{ message.body }}</p>
                        <span class="host-messages-bubble__time">{{ timeOnly(message.sentAt) }}</span>
                    </div>
                </template>
                <p v-if="!loadingMessages && !messages.length" class="host-team-empty">{{ t('messages.emptyThread') }}</p>
            </div>

            <div class="host-messages-composer">
                <textarea
                    v-model="composerText"
                    class="host-messages-composer__input"
                    :placeholder="t('messages.composerPlaceholder')"
                    rows="2"
                    @keydown.enter.exact.prevent="sendMessage"
                />
                <button
                    type="button"
                    class="host-btn host-btn--primary"
                    :disabled="!composerText.trim() || sending"
                    @click="sendMessage"
                >
                    {{ sending ? t('messages.sending') : t('messages.send') }}
                </button>
            </div>
        </div>
        <div v-else class="host-messages-thread host-messages-thread--empty">
            <p class="host-team-empty">{{ t('messages.selectPrompt') }}</p>
        </div>

        <aside v-if="activeCustomer" class="host-messages-context">
            <div class="host-messages-context__identity">
                <span class="host-team-avatar" :style="{ background: activeCustomer.bg }">{{ activeCustomer.initials }}</span>
                <div>
                    <div class="host-messages-thread__name">{{ activeCustomer.name }}</div>
                    <div class="host-messages-thread__sub">{{ activeCustomer.country }} · {{ activeCustomer.email }}</div>
                </div>
            </div>

            <div class="host-team-stats host-messages-context__stats">
                <div class="host-team-stats__item">
                    <span class="host-team-stats__dot" style="background: #1f7a44" />
                    <div>
                        <div class="host-team-stats__value">{{ activeCustomer.stays }}</div>
                        <div class="host-team-stats__label">{{ t('messages.stays') }}</div>
                    </div>
                </div>
                <div class="host-team-stats__item">
                    <span class="host-team-stats__dot" style="background: #b5651d" />
                    <div>
                        <div class="host-team-stats__value">{{ formatVnd(activeCustomer.revenue) }}</div>
                        <div class="host-team-stats__label">{{ t('messages.revenue') }}</div>
                    </div>
                </div>
            </div>

            <div v-if="primaryBooking" class="host-messages-context__booking">
                <div class="host-messages-context__booking-label">
                    {{ activeCustomer.nextActive || activeCustomer.hasUpcoming ? t('messages.nextStay') : t('messages.lastStay') }}
                </div>
                <div class="host-messages-context__booking-apt">{{ primaryBooking.apartment }}</div>
                <div class="host-messages-context__booking-dates">{{ primaryBooking.dates }} · {{ primaryBooking.nights }}n</div>
                <div class="host-messages-context__booking-amount">{{ primaryBooking.amount }}</div>
            </div>
            <p v-else class="host-team-empty host-messages-context__booking">{{ t('messages.noBookingYet') }}</p>

            <router-link
                :to="{ name: 'customer-detail', params: { id: activeCustomerId } }"
                class="host-team-toolbar__btn host-messages-context__link"
            >
                {{ t('messages.viewAllBookings') }}
            </router-link>
        </aside>
    </div>
</template>

<script setup>
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/api/client';
import { useToast } from '@/composables/useToast';
import { formatVnd } from '@/utils/format';

const THREAD_POLL_MS = 30000;
const MESSAGE_POLL_MS = 4000;

const route = useRoute();
const router = useRouter();
const { t, locale } = useI18n();
const toast = useToast();

const search = ref('');
const filter = ref('all');
const threads = ref([]);
const loadingThreads = ref(true);
const activeCustomerId = ref(typeof route.query.customer === 'string' ? route.query.customer : null);
const activeCustomer = ref(null);
const messages = ref([]);
const loadingMessages = ref(false);
const composerText = ref('');
const sending = ref(false);
const scrollEl = ref(null);

let threadPollTimer = null;
let messagePollTimer = null;

const activeThreadMeta = computed(() =>
    threads.value.find((thread) => thread.customerId === activeCustomerId.value) ?? activeCustomer.value,
);

const unreadThreadCount = computed(() => threads.value.filter((thread) => thread.unreadCount > 0).length);

const visibleThreads = computed(() => {
    const query = search.value.trim().toLowerCase();

    return threads.value.filter((thread) => {
        if (filter.value === 'unread' && !thread.unreadCount) {
            return false;
        }

        if (!query) {
            return true;
        }

        return `${thread.name} ${thread.lastMessage ?? ''}`.toLowerCase().includes(query);
    });
});

const primaryBooking = computed(() => activeCustomer.value?.bookings?.[0] ?? null);

function timeOnly(iso) {
    if (!iso) {
        return '';
    }

    return new Intl.DateTimeFormat(locale.value === 'no' ? 'nb-NO' : 'en', {
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(iso));
}

function dateLabel(iso) {
    const date = new Date(iso);
    const startOfDay = (value) => new Date(value.getFullYear(), value.getMonth(), value.getDate());
    const today = startOfDay(new Date());
    const day = startOfDay(date);
    const diffDays = Math.round((today - day) / 86400000);

    if (diffDays === 0) {
        return t('messages.today');
    }

    if (diffDays === 1) {
        return t('messages.yesterday');
    }

    return new Intl.DateTimeFormat(locale.value === 'no' ? 'nb-NO' : 'en', {
        day: 'numeric',
        month: 'short',
        year: day.getFullYear() === today.getFullYear() ? undefined : 'numeric',
    }).format(date);
}

function relativeTime(iso) {
    if (!iso) {
        return '';
    }

    const diffMs = Date.now() - new Date(iso).getTime();
    const minutes = Math.floor(diffMs / 60000);

    if (minutes < 1) {
        return 'now';
    }

    if (minutes < 60) {
        return `${minutes}m`;
    }

    const hours = Math.floor(minutes / 60);

    if (hours < 24) {
        return `${hours}h`;
    }

    const days = Math.floor(hours / 24);

    return `${days}d`;
}

const groupedMessages = computed(() => {
    const groups = [];
    let currentLabel = null;
    let currentItems = null;

    for (const message of messages.value) {
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

async function loadThreads() {
    try {
        const response = await apiClient.get('/messages');
        threads.value = response.data ?? [];
    } catch {
        threads.value = [];
    } finally {
        loadingThreads.value = false;
    }
}

async function loadCustomer(customerId) {
    try {
        const response = await apiClient.get(`/customers/${customerId}`);
        activeCustomer.value = response.data ?? null;
    } catch {
        activeCustomer.value = null;
    }
}

async function loadMessages(customerId) {
    try {
        const response = await apiClient.get(`/customers/${customerId}/messages`);
        messages.value = response.data?.messages ?? [];
        await nextTick();
        scrollToBottom();
    } catch {
        // keep last known messages on transient poll failure
    } finally {
        loadingMessages.value = false;
    }
}

function scrollToBottom() {
    if (scrollEl.value) {
        scrollEl.value.scrollTop = scrollEl.value.scrollHeight;
    }
}

function stopMessagePoll() {
    if (messagePollTimer) {
        clearInterval(messagePollTimer);
        messagePollTimer = null;
    }
}

function startMessagePoll(customerId) {
    stopMessagePoll();

    if (document.hidden) {
        return;
    }

    messagePollTimer = setInterval(() => loadMessages(customerId), MESSAGE_POLL_MS);
}

function stopThreadPoll() {
    if (threadPollTimer) {
        clearInterval(threadPollTimer);
        threadPollTimer = null;
    }
}

function startThreadPoll() {
    stopThreadPoll();

    if (document.hidden) {
        return;
    }

    threadPollTimer = setInterval(loadThreads, THREAD_POLL_MS);
}

function selectThread(customerId) {
    if (activeCustomerId.value === customerId) {
        return;
    }

    activeCustomerId.value = customerId;
    router.replace({ query: { ...route.query, customer: customerId } });
}

async function openActiveThread(customerId) {
    stopMessagePoll();
    loadingMessages.value = true;
    messages.value = [];
    activeCustomer.value = null;

    await Promise.all([loadCustomer(customerId), loadMessages(customerId)]);

    startMessagePoll(customerId);
}

async function handleVisibilityChange() {
    if (document.hidden) {
        stopThreadPoll();
        stopMessagePoll();

        return;
    }

    // Tab just came back into focus — catch up immediately rather than
    // waiting for the next interval tick, then resume polling.
    await loadThreads();
    startThreadPoll();

    if (activeCustomerId.value) {
        await loadMessages(activeCustomerId.value);
        startMessagePoll(activeCustomerId.value);
    }
}

watch(activeCustomerId, (customerId) => {
    if (customerId) {
        openActiveThread(customerId);
    } else {
        stopMessagePoll();
    }
});

async function sendMessage() {
    const text = composerText.value.trim();

    if (!text || sending.value || !activeCustomerId.value) {
        return;
    }

    sending.value = true;

    try {
        await apiClient.post(`/customers/${activeCustomerId.value}/messages`, { text });
        composerText.value = '';
        await Promise.all([loadMessages(activeCustomerId.value), loadThreads()]);
    } catch (err) {
        toast.show(err.message ?? t('messages.sendFailed'));
    } finally {
        sending.value = false;
    }
}

onMounted(async () => {
    await loadThreads();
    startThreadPoll();

    if (activeCustomerId.value) {
        openActiveThread(activeCustomerId.value);
    }

    document.addEventListener('visibilitychange', handleVisibilityChange);
});

onUnmounted(() => {
    stopThreadPoll();
    stopMessagePoll();
    document.removeEventListener('visibilitychange', handleVisibilityChange);
});
</script>
