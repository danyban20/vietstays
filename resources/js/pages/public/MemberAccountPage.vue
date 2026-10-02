<template>
    <div class="member-page">
        <div v-if="loading" class="public-loading">Loading your account…</div>

        <template v-else-if="profile">
            <header class="member-account__header">
                <div>
                    <h1>Hi, {{ profile.name }}</h1>
                    <p v-if="profile.member_since">Member since {{ profile.member_since }}</p>
                </div>
                <button type="button" class="public-btn public-btn--ghost" @click="signOut">Sign out</button>
            </header>

            <div v-if="isDashboardUser" class="member-account__notice">
                You're signed in with a {{ profile.role }} account.
                <a href="/admin" class="public-link">Go to the dashboard</a>
            </div>

            <section class="host-app-card">
                <h2>My bookings</h2>
                <p v-if="!bookings.length" class="member-account__empty">
                    No bookings yet. Bookings you make while signed in will show up here.
                    <router-link :to="{ name: 'public-apartments' }" class="public-link">Find an apartment</router-link>
                </p>
                <ul v-else class="member-bookings">
                    <li v-for="booking in bookings" :key="booking.id" class="member-bookings__item">
                        <div>
                            <strong>{{ booking.apartment_name || 'Apartment' }}</strong>
                            <span class="member-bookings__dates">{{ booking.check_in }} → {{ booking.check_out }}</span>
                            <span v-if="booking.booking_num" class="member-bookings__ref">Booking {{ booking.booking_num }}</span>
                        </div>
                        <div class="member-bookings__side">
                            <span class="member-bookings__status">{{ statusLabel(booking.status) }}</span>
                            <span>{{ formatVnd(booking.total) }}</span>
                        </div>
                    </li>
                </ul>
            </section>

            <section class="host-app-card">
                <h2>My details</h2>
                <div v-if="saveMessage" class="member-account__saved">{{ saveMessage }}</div>
                <div v-if="saveError" class="host-app-alert host-app-alert--error">{{ saveError }}</div>
                <form class="member-form" @submit.prevent="save">
                    <div class="public-field">
                        <label for="account-name">Full name</label>
                        <input id="account-name" v-model="form.name" class="public-input" required maxlength="100" />
                    </div>
                    <div class="public-field">
                        <label for="account-email">Email</label>
                        <input id="account-email" :value="profile.email" class="public-input" type="email" disabled />
                    </div>
                    <div class="public-field">
                        <label for="account-phone">Phone</label>
                        <input id="account-phone" v-model="form.phone" class="public-input" type="tel" maxlength="30" />
                    </div>
                    <button type="submit" class="public-btn public-btn--primary" :disabled="saving">
                        {{ saving ? 'Saving…' : 'Save' }}
                    </button>
                </form>
            </section>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/api/client';
import { useMemberStore } from '@/stores/member';
import { formatVnd } from '@/utils/format';

const STATUS_LABELS = {
    pending: 'Awaiting confirmation',
    confirmed: 'Confirmed',
    cancelled: 'Cancelled',
    completed: 'Completed',
};

const router = useRouter();
const member = useMemberStore();

const loading = ref(true);
const profile = ref(null);
const bookings = ref([]);
const form = reactive({ name: '', phone: '' });
const saving = ref(false);
const saveMessage = ref('');
const saveError = ref('');

const isDashboardUser = computed(() => Boolean(profile.value?.can_use_dashboard));

function statusLabel(status) {
    return STATUS_LABELS[status] ?? status;
}

function applyProfile(data) {
    profile.value = data;
    form.name = data.name ?? '';
    form.phone = data.phone ?? '';
}

async function load() {
    loading.value = true;

    try {
        const res = await apiClient.get('/account');
        applyProfile(res.data.profile);
        bookings.value = res.data.bookings ?? [];
    } catch (err) {
        if (err.status === 401) {
            router.replace({ name: 'member-login', query: { redirect: '/account' } });
            return;
        }
        throw err;
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    saveMessage.value = '';
    saveError.value = '';

    try {
        const res = await apiClient.put('/account', { name: form.name, phone: form.phone || null });
        applyProfile(res.data.profile);
        saveMessage.value = 'Your details were saved.';
        member.fetchUser(true);
    } catch (err) {
        saveError.value = err.message ?? 'Could not save your details.';
    } finally {
        saving.value = false;
    }
}

async function signOut() {
    await member.logout();
    router.push({ name: 'home' });
}

onMounted(load);
</script>
