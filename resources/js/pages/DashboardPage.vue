<template>
    <div>
        <div class="host-page-header">
            <h1 class="host-page-title">Dashboard</h1>
        </div>

        <div v-if="loading" class="host-loading">Loading dashboard…</div>

        <div v-else class="host-stat-grid">
            <div v-for="stat in stats" :key="stat.key" class="host-stat-card">
                <div class="host-stat-card__label">{{ stat.label }}</div>
                <div class="host-stat-card__value">{{ stat.value }}</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import apiClient from '@/api/client';

const loading = ref(true);
const stats = ref([
    { key: 'activeBookings', label: 'Active bookings', value: '—' },
    { key: 'checkInsToday', label: 'Check-ins today', value: '—' },
    { key: 'apartments', label: 'Active apartments', value: '—' },
    { key: 'revenue', label: 'Revenue (month)', value: '—' },
]);

onMounted(async () => {
    try {
        const data = await apiClient.get('/dashboard/stats');
        if (data) {
            stats.value = stats.value.map((stat) => ({
                ...stat,
                value: data[stat.key] ?? stat.value,
            }));
        }
    } catch {
        // Placeholder values until API is available
    } finally {
        loading.value = false;
    }
});
</script>
