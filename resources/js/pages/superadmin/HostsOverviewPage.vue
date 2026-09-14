<template>
    <div>
        <div class="host-page-header">
            <h1 class="host-page-title">{{ t('hostsOverview.title') }}</h1>
            <p class="host-page-subtitle">{{ t('hostsOverview.subtitle') }}</p>
        </div>

        <div v-if="accessDenied" class="host-form-error">{{ t('hostsOverview.accessDenied') }}</div>

        <template v-else>
            <form class="host-filters" @submit.prevent="loadHosts">
                <input
                    v-model="filters.search"
                    type="search"
                    class="host-input"
                    :placeholder="t('hostsOverview.searchPlaceholder')"
                />
                <select v-model="filters.role" class="host-select">
                    <option value="">{{ t('hostsOverview.allRoles') }}</option>
                    <option value="partner">{{ t('hostsOverview.rolePartner') }}</option>
                    <option value="host">{{ t('hostsOverview.roleHost') }}</option>
                </select>
                <button type="submit" class="host-btn host-btn--primary">{{ t('hostsOverview.filter') }}</button>
                <button
                    v-if="filters.search || filters.role"
                    type="button"
                    class="host-btn host-btn--ghost"
                    @click="clearFilters"
                >
                    {{ t('common.cancel') }}
                </button>
            </form>

            <p v-if="loadError" class="host-form-error">{{ loadError }}</p>

            <div v-if="loading" class="host-loading">{{ t('common.loading') }}</div>

            <div v-else class="host-table-wrap">
                <table class="host-table">
                    <thead>
                        <tr>
                            <th>{{ t('hostsOverview.colName') }}</th>
                            <th>{{ t('hostsOverview.colEmail') }}</th>
                            <th>{{ t('hostsOverview.colRole') }}</th>
                            <th>{{ t('hostsOverview.colApartments') }}</th>
                            <th>{{ t('hostsOverview.colBookings90d') }}</th>
                            <th>{{ t('hostsOverview.colJoined') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!hosts.length">
                            <td colspan="6" class="host-table__empty">{{ t('hostsOverview.empty') }}</td>
                        </tr>
                        <tr v-for="host in hosts" :key="host.id">
                            <td>{{ host.name }}</td>
                            <td>{{ host.email }}</td>
                            <td>
                                <span class="host-pill" :class="roleClass(host.role)">
                                    {{ host.role === 'partner' ? t('hostsOverview.rolePartner') : t('hostsOverview.roleHost') }}
                                </span>
                            </td>
                            <td>{{ host.apartments_count }}</td>
                            <td>{{ host.bookings_90d }}</td>
                            <td>{{ formatDate(host.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import apiClient from '@/api/client';

const { t } = useI18n();

const loading = ref(true);
const accessDenied = ref(false);
const loadError = ref('');
const hosts = ref([]);

const filters = reactive({
    search: '',
    role: '',
});

function roleClass(role) {
    return {
        'host-pill--sand': role === 'partner',
        'host-pill--muted': role === 'host',
    };
}

function formatDate(value) {
    if (!value) {
        return '—';
    }

    return new Date(value).toLocaleString(undefined, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

async function loadHosts() {
    loading.value = true;
    accessDenied.value = false;
    loadError.value = '';

    try {
        const params = new URLSearchParams();
        if (filters.search) {
            params.set('search', filters.search);
        }
        if (filters.role) {
            params.set('role', filters.role);
        }

        const query = params.toString();
        const res = await apiClient.get(`/admin/hosts${query ? `?${query}` : ''}`);
        hosts.value = res?.data ?? [];
    } catch (err) {
        if (err.status === 403) {
            accessDenied.value = true;
            hosts.value = [];
        } else {
            loadError.value = err.message ?? t('hostsOverview.loadFailed');
        }
    } finally {
        loading.value = false;
    }
}

function clearFilters() {
    filters.search = '';
    filters.role = '';
    loadHosts();
}

onMounted(loadHosts);
</script>

<style scoped>
.host-page-subtitle {
    margin: 8px 0 0;
    color: var(--host-text-muted, #5c6b66);
}
</style>
