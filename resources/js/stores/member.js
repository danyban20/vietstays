import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import apiClient from '@/api/client';

/**
 * Signed-in state on the public site. Any account can sign in here (hosts
 * book stays too); only the dashboard is closed to members.
 */
export const useMemberStore = defineStore('member', () => {
    const user = ref(null);
    const loaded = ref(false);

    const isSignedIn = computed(() => user.value !== null);

    async function fetchUser(force = false) {
        if (loaded.value && !force) {
            return user.value;
        }

        try {
            const res = await apiClient.get('/user');
            user.value = res?.user ?? null;
        } catch {
            user.value = null;
        } finally {
            loaded.value = true;
        }

        return user.value;
    }

    async function login(email, password) {
        const res = await apiClient.post('/login', { email, password, area: 'site' });
        user.value = res?.user ?? null;
        loaded.value = true;
    }

    async function register(payload) {
        const res = await apiClient.post('/register', payload);
        user.value = res?.user ?? null;
        loaded.value = true;
    }

    async function logout() {
        try {
            await apiClient.post('/logout');
        } finally {
            user.value = null;
            loaded.value = true;
        }
    }

    return { user, loaded, isSignedIn, fetchUser, login, register, logout };
});
