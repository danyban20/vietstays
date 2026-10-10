<template>
    <div id="dashboard" class="booking_list member-profile-page">
        <div class="container">
            <h1>Profile</h1>
            <div class="current_date_text">
                <p v-if="profile?.member_since">Member since {{ formatDay(profile.member_since, { month: 'long', year: 'numeric' }) }}</p>
            </div>

            <div v-if="loading" class="member-loading">Loading your profile…</div>
            <div v-else-if="error" class="member-error">
                <p>{{ error }}</p>
                <button type="button" class="btn" @click="load">Try again</button>
            </div>

            <template v-else>
                <div v-if="profile.can_use_dashboard" class="msz_box">
                    <div class="msz_text">You’re signed in with a {{ profile.role }} account.</div>
                    <a href="/admin" class="open_msz">Go to the host dashboard</a>
                </div>

                <div class="member-profile-grid">
                    <section class="member-card">
                        <h5>My details</h5>
                        <form novalidate @submit.prevent="saveDetails">
                            <label for="profile-name">Full name</label>
                            <input id="profile-name" v-model="details.name" type="text" maxlength="100" autocomplete="name" required />
                            <label for="profile-email">Email</label>
                            <input id="profile-email" :value="profile.email" type="email" disabled />
                            <label for="profile-phone">Phone</label>
                            <input id="profile-phone" v-model="details.phone" type="tel" maxlength="30" autocomplete="tel" />
                            <p v-if="detailsError" class="member-form-error">{{ detailsError }}</p>
                            <button type="submit" class="btn" :disabled="savingDetails || !details.name.trim()">
                                {{ savingDetails ? 'Saving…' : 'Save details' }}
                            </button>
                        </form>
                    </section>

                    <section class="member-card">
                        <h5>Change password</h5>
                        <form novalidate @submit.prevent="savePassword">
                            <label for="profile-current">Current password</label>
                            <input id="profile-current" v-model="password.current_password" type="password" autocomplete="current-password" />
                            <label for="profile-new">New password</label>
                            <input id="profile-new" v-model="password.password" type="password" minlength="8" autocomplete="new-password" />
                            <label for="profile-confirm">Repeat new password</label>
                            <input id="profile-confirm" v-model="password.password_confirmation" type="password" autocomplete="new-password" />
                            <p v-if="passwordError" class="member-form-error">{{ passwordError }}</p>
                            <button type="submit" class="btn" :disabled="savingPassword || !canSavePassword">
                                {{ savingPassword ? 'Saving…' : 'Change password' }}
                            </button>
                        </form>
                    </section>
                </div>

                <section class="member-card member-card--quiet">
                    <h5>Help &amp; privacy</h5>
                    <p>
                        Read our <router-link :to="{ name: 'terms' }">terms, cancellation policy and privacy notice</router-link>, or
                        <router-link :to="{ name: 'terms', hash: '#contact' }">contact us</router-link>.
                    </p>
                    <button type="button" class="btn border_btn" @click="signOut">Sign out</button>
                </section>
            </template>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import apiClient from '@/api/client';
import { useMemberStore } from '@/stores/member';
import { useMemberAreaStore } from '@/stores/memberArea';
import { formatDay } from '@/utils/member-format';

const router = useRouter();
const member = useMemberStore();
const area = useMemberAreaStore();

const loading = ref(true);
const error = ref('');
const profile = ref(null);

const details = reactive({ name: '', phone: '' });
const savingDetails = ref(false);
const detailsError = ref('');

const password = reactive({ current_password: '', password: '', password_confirmation: '' });
const savingPassword = ref(false);
const passwordError = ref('');

const canSavePassword = computed(
    () => password.current_password && password.password.length >= 8 && password.password_confirmation,
);

async function load() {
    loading.value = true;
    error.value = '';

    try {
        const res = await apiClient.get('/account');
        profile.value = res.data.profile;
        details.name = profile.value.name ?? '';
        details.phone = profile.value.phone ?? '';
    } catch (err) {
        error.value = err.message || 'Could not load your profile.';
    } finally {
        loading.value = false;
    }
}

async function saveDetails() {
    savingDetails.value = true;
    detailsError.value = '';

    try {
        const res = await apiClient.put('/account', { name: details.name.trim(), phone: details.phone.trim() || null });
        profile.value = res.data.profile;
        await member.fetchUser(true);
        area.notify(res.message || 'Profile updated.');
    } catch (err) {
        detailsError.value = err.message || 'Could not save your details.';
    } finally {
        savingDetails.value = false;
    }
}

async function savePassword() {
    passwordError.value = '';

    if (password.password !== password.password_confirmation) {
        passwordError.value = 'The new passwords do not match.';
        return;
    }

    savingPassword.value = true;

    try {
        const res = await apiClient.put('/account/password', { ...password });
        Object.assign(password, { current_password: '', password: '', password_confirmation: '' });
        area.notify(res.message || 'Password changed.');
    } catch (err) {
        passwordError.value = err.message || 'Could not change your password.';
    } finally {
        savingPassword.value = false;
    }
}

async function signOut() {
    await member.logout();
    router.push({ name: 'home' });
}

onMounted(load);
</script>
