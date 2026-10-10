<template>
    <div id="dashboard" class="booking_list member-messages-page">
        <div class="container">
            <h1>Messages</h1>

            <div v-if="loading" class="member-loading">Loading messages…</div>
            <div v-else-if="error" class="member-error">
                <p>{{ error }}</p>
                <button type="button" class="btn" @click="loadList">Try again</button>
            </div>

            <div v-else-if="!conversations.length" class="icon_white_block">
                <div class="img"><img :src="asset('/member/images/messages-square.svg')" alt="" /></div>
                <h3>No messages yet</h3>
                <p>Once you book a stay you can message your host here.</p>
                <router-link :to="{ name: 'public-apartments' }" class="btn">Browse apartments</router-link>
            </div>

            <div v-else class="member-messages" :class="{ 'member-messages--thread-open': activeId }">
                <ul class="member-messages__list" aria-label="Conversations">
                    <li v-for="conversation in conversations" :key="conversation.id">
                        <router-link
                            :to="{ name: 'member-messages', params: { conversation: conversation.id } }"
                            class="member-messages__item"
                            :class="{ 'is-active': conversation.id === activeId }"
                        >
                            <span class="member-avatar member-avatar--light">{{ conversation.host_initials }}</span>
                            <span class="member-messages__meta">
                                <strong>{{ conversation.host_name }}</strong>
                                <span class="member-messages__apt">{{ conversation.apartments.join(', ') }}</span>
                                <span class="member-messages__preview">
                                    {{ conversation.last_message ? `${conversation.last_message_mine ? 'You: ' : ''}${conversation.last_message}` : 'Start a conversation' }}
                                </span>
                            </span>
                            <span class="member-messages__side">
                                <span v-if="conversation.last_message_at">{{ formatTimeAgo(conversation.last_message_at) }}</span>
                                <span v-if="conversation.unread" class="member-badge">{{ conversation.unread }}</span>
                            </span>
                        </router-link>
                    </li>
                </ul>

                <section v-if="thread" class="member-messages__thread" aria-live="polite">
                    <header class="member-messages__head">
                        <router-link :to="{ name: 'member-messages' }" class="member-messages__back" aria-label="All conversations">←</router-link>
                        <div>
                            <strong>{{ thread.host_name }}</strong>
                            <span>{{ thread.apartments.join(', ') }}</span>
                        </div>
                    </header>
                    <div ref="scroller" class="member-messages__body">
                        <p v-if="!thread.messages.length" class="member-messages__empty">
                            Say hello to {{ thread.host_name }} — ask about arrival, parking or anything else for your stay.
                        </p>
                        <div
                            v-for="message in thread.messages"
                            :key="message.id"
                            class="member-bubble"
                            :class="message.mine ? 'member-bubble--mine' : 'member-bubble--theirs'"
                        >
                            <p>{{ message.body }}</p>
                            <span>{{ message.mine ? 'You' : message.author }} · {{ formatStamp(message.sent_at) }}</span>
                        </div>
                    </div>
                    <form class="member-messages__compose" @submit.prevent="send">
                        <select v-if="thread.bookings.length > 1" v-model="bookingId" aria-label="About which booking">
                            <option v-for="item in thread.bookings" :key="item.id" :value="item.id">{{ item.label }}</option>
                        </select>
                        <div class="member-messages__row">
                            <textarea
                                v-model="draft"
                                rows="2"
                                maxlength="4000"
                                placeholder="Write a message…"
                                aria-label="Message"
                                @keydown.enter.exact.prevent="send"
                            />
                            <button type="submit" class="btn" :disabled="sending || !draft.trim()">{{ sending ? 'Sending…' : 'Send' }}</button>
                        </div>
                        <p v-if="sendError" class="member-form-error">{{ sendError }}</p>
                    </form>
                </section>
                <section v-else-if="threadLoading" class="member-messages__thread member-messages__placeholder">Loading conversation…</section>
                <section v-else class="member-messages__thread member-messages__placeholder">Choose a conversation.</section>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/api/client';
import { useMemberAreaStore } from '@/stores/memberArea';
import { withAppBase } from '@/utils/app-base';
import { formatTimeAgo } from '@/utils/member-format';

const route = useRoute();
const router = useRouter();
const area = useMemberAreaStore();

const loading = ref(true);
const error = ref('');
const conversations = ref([]);
const thread = ref(null);
const threadLoading = ref(false);
const draft = ref('');
const bookingId = ref(null);
const sending = ref(false);
const sendError = ref('');
const scroller = ref(null);
let pollTimer = null;

const asset = (path) => withAppBase(path);
const activeId = computed(() => (route.params.conversation ? String(route.params.conversation) : null));

function formatStamp(iso) {
    return new Date(iso).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

async function scrollToEnd() {
    await nextTick();
    if (scroller.value) scroller.value.scrollTop = scroller.value.scrollHeight;
}

async function loadList() {
    error.value = '';

    try {
        const res = await apiClient.get('/member/messages');
        conversations.value = res.data ?? [];
        area.unread = conversations.value.reduce((sum, row) => sum + (row.unread || 0), 0);

        // On a wide screen open the newest conversation straight away.
        if (!activeId.value && conversations.value.length && window.matchMedia('(min-width: 768px)').matches) {
            router.replace({ name: 'member-messages', params: { conversation: conversations.value[0].id } });
        }
    } catch (err) {
        error.value = err.message || 'Could not load your messages.';
    } finally {
        loading.value = false;
    }
}

async function loadThread(quiet = false) {
    if (!activeId.value) {
        thread.value = null;
        return;
    }

    if (!quiet) threadLoading.value = true;

    try {
        const res = await apiClient.get(`/member/messages/${encodeURIComponent(activeId.value)}`);
        const before = thread.value?.messages?.length ?? 0;
        thread.value = res.data;

        if (!quiet || res.data.messages.length !== before) {
            scrollToEnd();
        }

        const requested = Number(route.query.booking);
        if (!quiet) {
            bookingId.value = res.data.bookings.some((item) => item.id === requested) ? requested : res.data.default_booking_id;
        }

        const row = conversations.value.find((item) => item.id === activeId.value);
        if (row && row.unread) {
            row.unread = 0;
            area.unread = conversations.value.reduce((sum, item) => sum + (item.unread || 0), 0);
        }
    } catch (err) {
        if (!quiet) {
            thread.value = null;
            area.notify(err.status === 404 ? 'Conversation not found.' : err.message || 'Could not load the conversation.', 'error');
        }
    } finally {
        threadLoading.value = false;
    }
}

async function send() {
    const text = draft.value.trim();

    if (!text || sending.value) return;

    sending.value = true;
    sendError.value = '';

    try {
        const res = await apiClient.post(`/member/messages/${encodeURIComponent(activeId.value)}`, {
            text,
            booking_id: bookingId.value,
        });
        draft.value = '';
        thread.value = res.data;
        scrollToEnd();

        // A first message turns "host-12" into a real thread id.
        if (res.data.id !== activeId.value) {
            router.replace({ name: 'member-messages', params: { conversation: res.data.id } });
        }

        loadList();
    } catch (err) {
        sendError.value = err.message || 'Could not send your message.';
    } finally {
        sending.value = false;
    }
}

watch(activeId, () => {
    sendError.value = '';
    loadThread();
});

onMounted(async () => {
    await loadList();
    await loadThread();
    pollTimer = setInterval(() => {
        if (document.visibilityState === 'visible' && activeId.value) loadThread(true);
    }, 15000);
});

onBeforeUnmount(() => clearInterval(pollTimer));
</script>
