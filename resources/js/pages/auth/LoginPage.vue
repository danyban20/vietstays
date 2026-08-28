<template>
    <div class="host-login">
        <div class="host-login__card">
            <h1 class="host-login__title">Host login</h1>
            <p class="host-login__subtitle">Sign in to manage your apartments and bookings.</p>

            <form class="host-login__form" @submit.prevent="submit">
                <div v-if="error" class="host-login__error">{{ error }}</div>

                <div class="host-field">
                    <label class="host-field__label" for="email">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="host-input"
                        autocomplete="email"
                        required
                    />
                </div>

                <div class="host-field">
                    <label class="host-field__label" for="password">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        class="host-input"
                        autocomplete="current-password"
                        required
                    />
                </div>

                <button type="submit" class="host-btn host-btn--primary" style="width: 100%" :disabled="submitting">
                    {{ submitting ? 'Signing in…' : 'Sign in' }}
                </button>
            </form>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import apiClient from '@/api/client';

const route = useRoute();
const router = useRouter();
const submitting = ref(false);
const error = ref('');

const form = reactive({
    email: '',
    password: '',
});

async function submit() {
    submitting.value = true;
    error.value = '';

    try {
        await apiClient.post('/login', {
            email: form.email,
            password: form.password,
        });
        const redirect = route.query.redirect;
        if (typeof redirect === 'string' && redirect.startsWith('/admin')) {
            router.push(redirect);
        } else {
            router.push({ name: 'dashboard' });
        }
    } catch (err) {
        error.value = err.message ?? 'Login failed. Please try again.';
    } finally {
        submitting.value = false;
    }
}
</script>
