<template>
    <div class="member-page member-page--narrow">
        <section class="host-app-card member-card">
            <h1>Sign in</h1>
            <p class="member-card__lead">Welcome back. Sign in to see your bookings and member offers.</p>

            <div v-if="error" class="host-app-alert host-app-alert--error">{{ error }}</div>

            <form class="member-form" @submit.prevent="submit">
                <div class="public-field">
                    <label for="login-email">Email</label>
                    <input id="login-email" v-model="form.email" class="public-input" type="email" autocomplete="email" required />
                </div>
                <div class="public-field">
                    <label for="login-password">Password</label>
                    <input id="login-password" v-model="form.password" class="public-input" type="password" autocomplete="current-password" required />
                </div>

                <button type="submit" class="public-btn public-btn--primary public-btn--block" :disabled="submitting">
                    {{ submitting ? 'Signing in…' : 'Sign in' }}
                </button>
            </form>

            <p class="member-card__switch">
                New to Vietstays?
                <router-link :to="{ name: 'member-register', query: route.query }" class="public-link">Register for free</router-link>
            </p>
            <p class="member-card__switch">
                Hosts and partners: <a href="/admin/login" class="public-link">sign in to the dashboard</a>
            </p>
        </section>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useMemberStore } from '@/stores/member';
import { safeRedirect } from '@/utils/safe-redirect';

const route = useRoute();
const router = useRouter();
const member = useMemberStore();

const submitting = ref(false);
const error = ref('');
const form = reactive({ email: '', password: '' });

async function submit() {
    submitting.value = true;
    error.value = '';

    try {
        await member.login(form.email, form.password);
        router.push(safeRedirect(route.query.redirect, { name: 'member-account' }));
    } catch (err) {
        error.value = err.message ?? 'Sign in failed. Please try again.';
    } finally {
        submitting.value = false;
    }
}
</script>
