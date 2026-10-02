<template>
    <div class="member-page">
        <div class="member-page__grid">
            <section class="host-app-card member-card">
                <h1>Create your free account</h1>
                <p class="member-card__lead">Become a Vietstays member to collect rewards and get member-only offers.</p>

                <div v-if="error" class="host-app-alert host-app-alert--error">{{ error }}</div>

                <form class="member-form" @submit.prevent="submit">
                    <div class="public-field">
                        <label for="member-name">Full name</label>
                        <input id="member-name" v-model="form.name" class="public-input" autocomplete="name" required maxlength="100" />
                    </div>
                    <div class="public-field">
                        <label for="member-email">Email</label>
                        <input id="member-email" v-model="form.email" class="public-input" type="email" autocomplete="email" required />
                    </div>
                    <div class="public-field">
                        <label for="member-phone">Phone (optional)</label>
                        <input id="member-phone" v-model="form.phone" class="public-input" type="tel" autocomplete="tel" maxlength="30" />
                    </div>
                    <div class="public-field">
                        <label for="member-password">Password</label>
                        <input id="member-password" v-model="form.password" class="public-input" type="password" autocomplete="new-password" minlength="8" required />
                        <small class="member-form__hint">At least 8 characters.</small>
                    </div>
                    <div class="public-field">
                        <label for="member-password-confirm">Confirm password</label>
                        <input id="member-password-confirm" v-model="form.password_confirmation" class="public-input" type="password" autocomplete="new-password" minlength="8" required />
                    </div>

                    <button type="submit" class="public-btn public-btn--primary public-btn--block" :disabled="submitting">
                        {{ submitting ? 'Creating account…' : 'Register for free' }}
                    </button>
                </form>

                <p class="member-card__switch">
                    Already a member?
                    <router-link :to="{ name: 'member-login', query: route.query }" class="public-link">Sign in</router-link>
                </p>
            </section>

            <aside class="member-benefits">
                <h2>Free membership and rewards</h2>
                <ul>
                    <li v-for="item in MEMBERSHIP_BENEFITS" :key="item">{{ item }}</li>
                </ul>
                <p class="member-benefits__host">
                    Own an apartment?
                    <router-link :to="{ name: 'host-application' }" class="public-link">Become a host</router-link>
                </p>
            </aside>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { MEMBERSHIP_BENEFITS } from '@/data/home-content.js';
import { useMemberStore } from '@/stores/member';
import { safeRedirect } from '@/utils/safe-redirect';

const route = useRoute();
const router = useRouter();
const member = useMemberStore();

const submitting = ref(false);
const error = ref('');
const form = reactive({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

async function submit() {
    error.value = '';

    if (form.password !== form.password_confirmation) {
        error.value = 'The two passwords do not match.';
        return;
    }

    submitting.value = true;

    try {
        await member.register({ ...form, phone: form.phone || null });
        router.push(safeRedirect(route.query.redirect, { name: 'member-account' }));
    } catch (err) {
        error.value = err.message ?? 'Registration failed. Please try again.';
    } finally {
        submitting.value = false;
    }
}
</script>
