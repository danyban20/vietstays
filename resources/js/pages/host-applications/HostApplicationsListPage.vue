<template>
    <div>
        <div class="host-page-header">
            <h1 class="host-page-title">Host applications</h1>
        </div>

        <form class="host-filters" @submit.prevent="loadApplications">
            <input
                v-model="filters.search"
                type="search"
                class="host-input"
                placeholder="Search name, email, ref…"
            />
            <select v-model="filters.status" class="host-select">
                <option value="">Pending approval</option>
                <option value="all">All statuses</option>
                <option v-for="status in statusOptions" :key="status.value" :value="status.value">
                    {{ status.label }}
                </option>
            </select>
            <button type="submit" class="host-btn host-btn--primary">Filter</button>
            <button
                v-if="filters.search || filters.status"
                type="button"
                class="host-btn host-btn--ghost"
                @click="clearFilters"
            >
                Clear
            </button>
        </form>

        <div v-if="loading" class="host-loading">Loading applications…</div>
        <div v-else-if="accessDenied" class="host-form-error">Only administrators can view host applications.</div>

        <div v-else class="host-table-wrap">
            <table class="host-table">
                <thead>
                    <tr>
                        <th>Application ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>City</th>
                        <th>Properties</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th />
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="applications.length === 0">
                        <td colspan="9" class="host-table__empty">No host applications found.</td>
                    </tr>
                    <tr v-for="app in applications" :key="app.id">
                        <td>
                            <router-link :to="{ name: 'host-application-detail', params: { id: app.id } }">
                                {{ app.application_ref }}
                            </router-link>
                        </td>
                        <td>{{ app.full_name }}</td>
                        <td>{{ app.email }}</td>
                        <td>{{ app.type_label }}</td>
                        <td>{{ app.primary_city_name || app.home_city || '—' }}</td>
                        <td>{{ app.num_properties ?? '—' }}</td>
                        <td>{{ formatDate(app.dateadded) }}</td>
                        <td>
                            <span class="host-pill" :class="statusClass(app.status)">{{ app.status_label }}</span>
                        </td>
                        <td class="host-table__actions">
                            <router-link
                                :to="{ name: 'host-application-detail', params: { id: app.id } }"
                                class="host-btn host-btn--sand"
                            >
                                View
                            </router-link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import apiClient from '@/api/client';

const loading = ref(true);
const accessDenied = ref(false);
const applications = ref([]);

const filters = reactive({
    search: '',
    status: '',
});

const statusOptions = [
    { value: 'submitted', label: 'Submitted' },
    { value: 'under_review', label: 'Under review' },
    { value: 'approved', label: 'Approved' },
    { value: 'rejected', label: 'Rejected' },
    { value: 'activated', label: 'Activated' },
];

function formatDate(value) {
    if (! value) {
        return '—';
    }

    return new Date(value).toLocaleString(undefined, {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function statusClass(status) {
    return {
        submitted: 'host-pill--pending',
        under_review: 'host-pill--pending',
        approved: 'host-pill--active',
        rejected: 'host-pill--draft',
        activated: 'host-pill--active',
    }[status] ?? 'host-pill--draft';
}

async function loadApplications() {
    loading.value = true;
    accessDenied.value = false;

    const params = new URLSearchParams();
    if (filters.search) {
        params.set('search', filters.search);
    }
    if (filters.status) {
        params.set('status', filters.status);
    }

    const query = params.toString();
    const path = query ? `/host-applications?${query}` : '/host-applications';

    try {
        const res = await apiClient.get(path);
        applications.value = Array.isArray(res?.data) ? res.data : [];
    } catch (err) {
        if (err.status === 403) {
            accessDenied.value = true;
        } else {
            applications.value = [];
        }
    } finally {
        loading.value = false;
    }
}

function clearFilters() {
    filters.search = '';
    filters.status = '';
    loadApplications();
}

onMounted(loadApplications);
</script>
