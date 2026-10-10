import { defineStore } from 'pinia';
import { ref } from 'vue';
import apiClient from '@/api/client';

/**
 * Small shared state for the customer dashboard: the unread-message badge
 * in the header and a toast line.
 */
export const useMemberAreaStore = defineStore('memberArea', () => {
    const unread = ref(0);
    const toast = ref(null);
    let toastTimer = null;

    async function refreshUnread() {
        try {
            const res = await apiClient.get('/member/messages');
            unread.value = (res?.data ?? []).reduce((sum, row) => sum + (row.unread || 0), 0);
        } catch {
            // The badge is a nicety; ignore failures.
        }
    }

    function notify(message, tone = 'success') {
        toast.value = { message, tone };
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => {
            toast.value = null;
        }, 4000);
    }

    return { unread, toast, refreshUnread, notify };
});
