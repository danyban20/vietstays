import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import apiClient from '@/api/client';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null);
    const loaded = ref(false);

    const role = computed(() => user.value?.role ?? null);
    const isPlatformRole = computed(() => role.value === 'admin' || role.value === 'partner');
    const isHostRole = computed(() => role.value === 'host');

    async function fetchUser() {
        try {
            const res = await apiClient.get('/user');
            user.value = res?.user ?? null;
        } catch {
            user.value = null;
        } finally {
            loaded.value = true;
        }
    }

    async function logout() {
        try {
            await apiClient.post('/logout');
        } catch {
            // Even if the request fails, drop local state so the UI reflects "logged out".
        } finally {
            user.value = null;
            loaded.value = false;
        }
    }

    return {
        user,
        loaded,
        role,
        isPlatformRole,
        isHostRole,
        fetchUser,
        logout,
    };
});
