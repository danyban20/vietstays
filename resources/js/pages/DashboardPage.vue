<template>
    <div>
        <div class="host-page-header">
            <h1 class="host-page-title">{{ t('nav.dashboard') }}</h1>
        </div>

        <div v-if="loading" class="host-loading">{{ t('common.loading') }}</div>

        <div v-else class="host-stat-grid">
            <div v-for="stat in stats" :key="stat.key" class="host-stat-card">
                <div class="host-stat-card__label">{{ stat.label }}</div>
                <div class="host-stat-card__value">{{ formatStatValue(stat) }}</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import apiClient from '@/api/client';
import { formatVnd } from '@/utils/format';

const { t } = useI18n();

const loading = ref(true);
const stats = ref([]);

function formatStatValue(stat) {
    if (stat.format === 'vnd') {
        return formatVnd(stat.value ?? 0);
    }

    return stat.value ?? '—';
}

onMounted(async () => {
    try {
        const data = await apiClient.get('/dashboard');
        stats.value = Array.isArray(data?.stats) ? data.stats : [];
    } catch {
        stats.value = [];
    } finally {
        loading.value = false;
    }
});
</script>
