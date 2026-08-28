<template>
    <div>
        <div class="host-page-header">
            <div>
                <router-link :to="{ name: 'host-applications' }" class="host-back-link">← Back to list</router-link>
                <h1 class="host-page-title">{{ application?.application_ref || 'Host application' }}</h1>
            </div>
        </div>

        <div v-if="loading" class="host-loading">Loading application…</div>
        <div v-else-if="accessDenied" class="host-form-error">Only administrators can view host applications.</div>
        <div v-else-if="loadError" class="host-form-error">{{ loadError }}</div>

        <div v-else-if="application" class="host-detail-grid">
            <div class="host-detail-main">
                <section class="host-settings-card host-detail-card">
                    <h2 class="host-detail-card__title">Applicant details</h2>
                    <dl class="host-detail-list">
                        <div><dt>Application ID</dt><dd>{{ application.application_ref }}</dd></div>
                        <div><dt>Status</dt><dd>{{ application.status_label }}</dd></div>
                        <div><dt>Host type</dt><dd>{{ application.type_label }}</dd></div>
                        <div><dt>Full name</dt><dd>{{ application.full_name }}</dd></div>
                        <div><dt>Email</dt><dd><a :href="`mailto:${application.email}`">{{ application.email }}</a></dd></div>
                        <div><dt>Phone</dt><dd>{{ application.phone }}</dd></div>
                        <div><dt>Submitted</dt><dd>{{ formatDate(application.dateadded) }}</dd></div>
                        <div v-if="application.company_name"><dt>Company</dt><dd>{{ application.company_name }}</dd></div>
                        <div v-if="application.portfolio_url"><dt>Portfolio</dt><dd><a :href="application.portfolio_url" target="_blank" rel="noopener">{{ application.portfolio_url }}</a></dd></div>
                    </dl>
                </section>

                <section class="host-settings-card host-detail-card">
                    <h2 class="host-detail-card__title">Portfolio &amp; experience</h2>
                    <dl class="host-detail-list">
                        <div><dt>Primary city</dt><dd>{{ application.primary_city_name || application.home_city || '—' }}</dd></div>
                        <div><dt>Apartments</dt><dd>{{ application.num_properties ?? '—' }}</dd></div>
                        <div><dt>Districts</dt><dd>{{ application.district_names?.join(', ') || '—' }}</dd></div>
                        <div><dt>Years managing</dt><dd>{{ application.years_managing_label || '—' }}</dd></div>
                        <div><dt>Typical guests</dt><dd>{{ application.guest_profile_label || '—' }}</dd></div>
                        <div><dt>Platforms</dt><dd>{{ application.platforms_used?.join(', ') || '—' }}</dd></div>
                    </dl>
                    <p class="host-detail-description">{{ application.description }}</p>
                </section>

                <section v-if="application.applicant_type === 'single_property'" class="host-settings-card host-detail-card">
                    <h2 class="host-detail-card__title">Single apartment listing</h2>
                    <dl class="host-detail-list">
                        <div><dt>Address</dt><dd>{{ application.property_address || '—' }}</dd></div>
                        <div><dt>Space type</dt><dd>{{ application.property_type_label || '—' }}</dd></div>
                        <div><dt>Beds</dt><dd>{{ application.property_beds ?? '—' }}</dd></div>
                        <div><dt>Bathrooms</dt><dd>{{ application.property_bathrooms ?? '—' }}</dd></div>
                        <div><dt>Price / night</dt><dd>{{ formatVnd(application.property_price_daily) }}</dd></div>
                    </dl>
                </section>

                <section v-if="application.status_history?.length" class="host-settings-card host-detail-card">
                    <h2 class="host-detail-card__title">Status history</h2>
                    <table class="host-table host-table--compact">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>When</th>
                                <th>By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(entry, index) in application.status_history" :key="index">
                                <td>{{ entry.status }}</td>
                                <td>{{ formatDate(entry.at) }}</td>
                                <td>{{ entry.by || '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            </div>

            <aside class="host-detail-side">
                <section class="host-settings-card host-detail-card">
                    <h2 class="host-detail-card__title">Update status</h2>
                    <form @submit.prevent="saveStatus">
                        <div class="host-field host-field--full">
                            <label class="host-field__label">Status</label>
                            <select v-model="statusForm.status" class="host-select" required>
                                <option v-for="status in meta.statuses" :key="status.value" :value="status.value">
                                    {{ status.label }}
                                </option>
                            </select>
                        </div>

                        <template v-if="statusForm.status === 'rejected'">
                            <div class="host-field host-field--full">
                                <label class="host-field__label">Rejection reason</label>
                                <select v-model="statusForm.rejection_reason" class="host-select" required>
                                    <option value="">Select reason</option>
                                    <option
                                        v-for="reason in meta.rejection_reasons"
                                        :key="reason.value"
                                        :value="reason.value"
                                    >
                                        {{ reason.label }}
                                    </option>
                                </select>
                            </div>
                            <div class="host-field host-field--full">
                                <label class="host-field__label">Comment (optional)</label>
                                <textarea v-model="statusForm.rejection_comment" class="host-textarea" maxlength="150" />
                            </div>
                        </template>

                        <p v-if="saveError" class="host-form-error">{{ saveError }}</p>
                        <p v-if="saveSuccess" class="host-form-success">{{ saveSuccess }}</p>

                        <button type="submit" class="host-btn host-btn--primary" :disabled="saving">
                            {{ saving ? 'Saving…' : 'Save status' }}
                        </button>
                    </form>
                </section>
            </aside>
        </div>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute } from 'vue-router';
import apiClient from '@/api/client';
import { formatVnd } from '@/utils/format';

const route = useRoute();

const loading = ref(true);
const accessDenied = ref(false);
const loadError = ref('');
const saving = ref(false);
const saveError = ref('');
const saveSuccess = ref('');

const application = ref(null);
const meta = reactive({
    statuses: [],
    rejection_reasons: [],
});

const statusForm = reactive({
    status: 'submitted',
    rejection_reason: '',
    rejection_comment: '',
});

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

async function loadApplication() {
    loading.value = true;
    loadError.value = '';
    accessDenied.value = false;

    try {
        const res = await apiClient.get(`/host-applications/${route.params.id}`);
        application.value = res?.data ?? null;
        meta.statuses = res?.meta?.statuses ?? [];
        meta.rejection_reasons = res?.meta?.rejection_reasons ?? [];
        statusForm.status = application.value?.status ?? 'submitted';
        statusForm.rejection_reason = application.value?.rejection_reason ?? '';
        statusForm.rejection_comment = application.value?.rejection_comment ?? '';
    } catch (err) {
        if (err.status === 403) {
            accessDenied.value = true;
        } else {
            loadError.value = err.message || 'Could not load application.';
        }
    } finally {
        loading.value = false;
    }
}

async function saveStatus() {
    saving.value = true;
    saveError.value = '';
    saveSuccess.value = '';

    try {
        const res = await apiClient.patch(`/host-applications/${route.params.id}/status`, { ...statusForm });
        application.value = res?.data ?? application.value;
        statusForm.status = application.value?.status ?? statusForm.status;
        saveSuccess.value = res?.message || 'Status updated.';
    } catch (err) {
        saveError.value = err.message || 'Could not update status.';
    } finally {
        saving.value = false;
    }
}

onMounted(loadApplication);
</script>
