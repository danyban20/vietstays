<template>
    <div>
        <div class="host-page-header host-page-header--row">
            <div>
                <h1 class="host-page-title">
                    {{ t('managementCompaniesAdmin.title') }}
                    <span class="host-pill host-pill--sand">{{ t('managementCompaniesAdmin.badge') }}</span>
                </h1>
                <p class="host-page-subtitle">{{ t('managementCompaniesAdmin.subtitle') }}</p>
            </div>
            <button
                v-if="!accessDenied"
                type="button"
                class="host-btn host-btn--accent"
                @click="openCreate"
            >
                {{ t('managementCompaniesAdmin.addButton') }}
            </button>
        </div>

        <div v-if="accessDenied" class="host-form-error">{{ t('managementCompaniesAdmin.accessDenied') }}</div>

        <template v-else>
            <div class="mgmt-companies-admin__stats">
                <div class="mgmt-companies-admin__stat">
                    <span class="mgmt-companies-admin__stat-value">{{ meta.companies_total }}</span>
                    <span class="mgmt-companies-admin__stat-label">{{ t('managementCompaniesAdmin.statCompanies') }}</span>
                </div>
                <div class="mgmt-companies-admin__stat">
                    <span class="mgmt-companies-admin__stat-value">{{ meta.hosts_total }}</span>
                    <span class="mgmt-companies-admin__stat-label">{{ t('managementCompaniesAdmin.statHosts') }}</span>
                </div>
                <div class="mgmt-companies-admin__stat">
                    <span class="mgmt-companies-admin__stat-value">{{ meta.apartments_total }}</span>
                    <span class="mgmt-companies-admin__stat-label">{{ t('managementCompaniesAdmin.statApartments') }}</span>
                </div>
                <div class="mgmt-companies-admin__stat">
                    <span class="mgmt-companies-admin__stat-value">{{ formatMoney(meta.revenue_90d_total) }}</span>
                    <span class="mgmt-companies-admin__stat-label">{{ t('managementCompaniesAdmin.statRevenue') }}</span>
                </div>
            </div>

            <div class="host-filters">
                <select v-model.number="filters.countryId" class="host-select" @change="onCountryChange">
                    <option :value="0">{{ t('managementCompaniesAdmin.allCountries') }}</option>
                    <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <select v-model.number="filters.cityId" class="host-select" @change="loadCompanies">
                    <option :value="0">{{ t('managementCompaniesAdmin.allCities') }}</option>
                    <option v-for="c in cities" :key="c.city_id" :value="c.city_id">{{ c.name }}</option>
                </select>
                <select v-model="filters.status" class="host-select" @change="loadCompanies">
                    <option value="">{{ t('managementCompaniesAdmin.allStatuses', { count: meta.statuses.length }) }}</option>
                    <option v-for="s in meta.statuses" :key="s.value" :value="s.value">{{ statusLabel(s.value) }}</option>
                </select>
                <input
                    v-model="filters.search"
                    type="search"
                    class="host-input"
                    :placeholder="t('managementCompaniesAdmin.searchPlaceholder')"
                    @keyup.enter="loadCompanies"
                />
                <button type="button" class="host-btn host-btn--primary" @click="loadCompanies">
                    {{ t('managementCompaniesAdmin.filter') }}
                </button>
            </div>

            <p v-if="loadError" class="host-form-error">{{ loadError }}</p>
            <p v-if="formSuccess" class="host-form-success">{{ formSuccess }}</p>

            <div v-if="loading" class="host-loading">{{ t('common.loading') }}</div>

            <div v-else class="host-table-wrap">
                <table class="host-table">
                    <thead>
                        <tr>
                            <th>{{ t('managementCompaniesAdmin.colCompany') }}</th>
                            <th>{{ t('managementCompaniesAdmin.colMarket') }}</th>
                            <th>{{ t('managementCompaniesAdmin.colHosts') }}</th>
                            <th>{{ t('managementCompaniesAdmin.colApartments') }}</th>
                            <th>{{ t('managementCompaniesAdmin.colBookings90d') }}</th>
                            <th>{{ t('managementCompaniesAdmin.colRevenue') }}</th>
                            <th>{{ t('managementCompaniesAdmin.colStatus') }}</th>
                            <th>{{ t('managementCompaniesAdmin.colActions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!companies.length">
                            <td colspan="8" class="host-table__empty">{{ t('managementCompaniesAdmin.empty') }}</td>
                        </tr>
                        <tr v-for="company in companies" :key="company.id">
                            <td>
                                <div class="mgmt-companies-admin__name">{{ company.name }}</div>
                                <div class="mgmt-companies-admin__muted">
                                    <span v-if="company.manager_name">{{ company.manager_name }}</span>
                                    <span v-if="company.manager_since">
                                        · {{ t('managementCompaniesAdmin.managerSince', { date: formatDate(company.manager_since) }) }}
                                    </span>
                                </div>
                            </td>
                            <td>{{ company.market || '—' }}</td>
                            <td>{{ company.hosts_count }}</td>
                            <td>{{ company.apartments_count }}</td>
                            <td>{{ company.bookings_90d }}</td>
                            <td>
                                <div>{{ formatMoney(company.revenue_90d) }}</div>
                                <div class="mgmt-companies-admin__muted">
                                    {{ company.outstanding > 0
                                        ? t('managementCompaniesAdmin.outstanding', { amount: formatMoney(company.outstanding) })
                                        : t('managementCompaniesAdmin.settled') }}
                                </div>
                            </td>
                            <td>
                                <span class="host-pill" :class="statusPillClass(company.status)">{{ statusLabel(company.status) }}</span>
                                <div v-if="company.status === 'rejected' && company.rejection_reason_label" class="mgmt-companies-admin__muted">
                                    {{ company.rejection_reason_label }}
                                </div>
                            </td>
                            <td class="host-table__actions">
                                <template v-if="company.status === 'pending'">
                                    <button type="button" class="host-btn host-btn--ghost" @click="approve(company)">
                                        {{ t('managementCompaniesAdmin.approve') }}
                                    </button>
                                    <button type="button" class="host-btn host-btn--ghost" @click="openReject(company)">
                                        {{ t('managementCompaniesAdmin.reject') }}
                                    </button>
                                </template>
                                <button v-else-if="company.status === 'active'" type="button" class="host-btn host-btn--ghost" @click="pause(company)">
                                    {{ t('managementCompaniesAdmin.pause') }}
                                </button>
                                <button v-else-if="company.status === 'paused'" type="button" class="host-btn host-btn--ghost" @click="approve(company)">
                                    {{ t('managementCompaniesAdmin.reactivate') }}
                                </button>
                                <span v-else-if="company.status === 'invited'" class="mgmt-companies-admin__muted">
                                    {{ t('managementCompaniesAdmin.awaitingHost') }}
                                </span>
                                <button
                                    v-if="company.manager_user_id"
                                    type="button"
                                    class="host-btn host-btn--ghost"
                                    :title="company.manager_email"
                                    @click="openMessages(company)"
                                >
                                    {{ t('managementCompaniesAdmin.messageHost') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <HostModalShell :open="createModalOpen" :title="t('managementCompaniesAdmin.createTitle')" @close="closeCreate">
            <div class="host-form-grid">
                <p v-if="createError" class="host-form-error host-form-grid__full">{{ createError }}</p>
                <div class="host-field host-field--full">
                    <label class="host-field__label" for="mgmt-company-name">{{ t('managementCompaniesAdmin.name') }}</label>
                    <input id="mgmt-company-name" v-model="createForm.name" type="text" class="host-input" required />
                </div>
                <div class="host-field host-field--full">
                    <label class="host-field__label" for="mgmt-company-number">{{ t('managementCompaniesAdmin.companyNumber') }}</label>
                    <input id="mgmt-company-number" v-model="createForm.companyNumber" type="text" class="host-input" />
                </div>
            </div>
            <template #footer>
                <div class="host-modal__footer-actions">
                    <button type="button" class="host-btn host-btn--ghost" @click="closeCreate">{{ t('common.cancel') }}</button>
                    <button type="button" class="host-btn host-btn--accent" :disabled="creating" @click="submitCreate">
                        {{ creating ? t('common.loading') : t('managementCompaniesAdmin.create') }}
                    </button>
                </div>
            </template>
        </HostModalShell>

        <HostModalShell :open="rejectModalOpen" :title="t('managementCompaniesAdmin.rejectTitle')" @close="closeReject">
            <div class="host-form-grid">
                <p v-if="rejectError" class="host-form-error host-form-grid__full">{{ rejectError }}</p>
                <div class="host-field host-field--full">
                    <label class="host-field__label" for="mgmt-reject-reason">{{ t('managementCompaniesAdmin.rejectReason') }}</label>
                    <select id="mgmt-reject-reason" v-model="rejectForm.reason" class="host-select">
                        <option value="">{{ t('managementCompaniesAdmin.selectReason') }}</option>
                        <option v-for="r in meta.rejection_reasons" :key="r.value" :value="r.value">{{ r.label }}</option>
                    </select>
                </div>
            </div>
            <template #footer>
                <div class="host-modal__footer-actions">
                    <button type="button" class="host-btn host-btn--ghost" @click="closeReject">{{ t('common.cancel') }}</button>
                    <button type="button" class="host-btn host-btn--accent" :disabled="rejecting" @click="submitReject">
                        {{ rejecting ? t('common.loading') : t('managementCompaniesAdmin.reject') }}
                    </button>
                </div>
            </template>
        </HostModalShell>

        <HostModalShell :open="messagesModalOpen" :title="t('managementCompaniesAdmin.messagesTitle', { name: messagesHostName })" @close="closeMessages">
            <div class="mgmt-companies-admin__messages">
                <p v-if="messagesLoading" class="host-loading">{{ t('common.loading') }}</p>
                <template v-else>
                    <div v-if="!hostMessages.length" class="mgmt-companies-admin__muted">{{ t('managementCompaniesAdmin.messagesEmpty') }}</div>
                    <div v-else class="mgmt-companies-admin__message-list">
                        <div
                            v-for="message in hostMessages"
                            :key="message.id"
                            class="mgmt-companies-admin__bubble"
                            :class="message.sender_role === 'admin' ? 'mgmt-companies-admin__bubble--admin' : 'mgmt-companies-admin__bubble--host'"
                        >
                            <div class="mgmt-companies-admin__bubble-meta">{{ message.sender_name }}</div>
                            <div>{{ message.body }}</div>
                        </div>
                    </div>
                </template>
                <textarea
                    v-model="messageDraft"
                    class="host-input mgmt-companies-admin__message-input"
                    rows="2"
                    :placeholder="t('managementCompaniesAdmin.messagePlaceholder')"
                />
            </div>
            <template #footer>
                <div class="host-modal__footer-actions">
                    <button type="button" class="host-btn host-btn--ghost" @click="closeMessages">{{ t('common.cancel') }}</button>
                    <button type="button" class="host-btn host-btn--accent" :disabled="sendingMessage || !messageDraft.trim()" @click="sendMessage">
                        {{ sendingMessage ? t('common.loading') : t('managementCompaniesAdmin.send') }}
                    </button>
                </div>
            </template>
        </HostModalShell>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import apiClient from '@/api/client';
import HostModalShell from '@/components/modals/HostModalShell.vue';

const { t } = useI18n();

const loading = ref(true);
const accessDenied = ref(false);
const loadError = ref('');
const formSuccess = ref('');

const countries = ref([]);
const cities = ref([]);
const companies = ref([]);
const meta = reactive({
    companies_total: 0,
    hosts_total: 0,
    apartments_total: 0,
    revenue_90d_total: 0,
    statuses: [],
    rejection_reasons: [],
});

const filters = reactive({
    countryId: 0,
    cityId: 0,
    status: '',
    search: '',
});

const createModalOpen = ref(false);
const creating = ref(false);
const createError = ref('');
const createForm = reactive({ name: '', companyNumber: '' });

const rejectModalOpen = ref(false);
const rejecting = ref(false);
const rejectError = ref('');
const rejectForm = reactive({ companyId: null, reason: '' });

const messagesModalOpen = ref(false);
const messagesLoading = ref(false);
const sendingMessage = ref(false);
const messageDraft = ref('');
const hostMessages = ref([]);
const messagesHostId = ref(null);
const messagesHostName = ref('');
const messagesCompanyName = ref('');

const STATUS_LABEL_KEYS = {
    pending: 'managementCompaniesAdmin.statusPending',
    active: 'managementCompaniesAdmin.statusActive',
    paused: 'managementCompaniesAdmin.statusPaused',
    rejected: 'managementCompaniesAdmin.statusRejected',
    invited: 'managementCompaniesAdmin.statusInvited',
};

const STATUS_PILL_CLASS = {
    pending: 'host-pill--pending',
    active: 'host-pill--active',
    paused: 'host-pill--draft',
    rejected: 'host-pill--cancelled',
    invited: 'host-pill--confirmed',
};

function statusLabel(status) {
    return STATUS_LABEL_KEYS[status] ? t(STATUS_LABEL_KEYS[status]) : status;
}

function statusPillClass(status) {
    return STATUS_PILL_CLASS[status] ?? 'host-pill--draft';
}

function formatMoney(amount) {
    return new Intl.NumberFormat('vi-VN').format(Math.round(amount ?? 0));
}

function formatDate(iso) {
    if (!iso) return '';
    return new Date(iso).toLocaleDateString();
}

async function loadCountries() {
    const res = await apiClient.get('/admin/countries');
    countries.value = res?.data ?? [];
}

async function loadCities(countryId) {
    const query = countryId ? `?country_id=${countryId}` : '';
    const res = await apiClient.get(`/admin/cities${query}`);
    return res?.data ?? [];
}

async function onCountryChange() {
    filters.cityId = 0;
    cities.value = filters.countryId ? await loadCities(filters.countryId) : [];
    loadCompanies();
}

async function loadCompanies() {
    loading.value = true;
    loadError.value = '';

    try {
        const params = new URLSearchParams();
        if (filters.countryId) params.set('country_id', filters.countryId);
        if (filters.cityId) params.set('city_id', filters.cityId);
        if (filters.status) params.set('status', filters.status);
        if (filters.search) params.set('search', filters.search);

        const query = params.toString();
        const res = await apiClient.get(`/admin/management-companies${query ? `?${query}` : ''}`);
        companies.value = res?.data ?? [];
        Object.assign(meta, res?.meta ?? {});
    } catch (err) {
        if (err.status === 403) {
            accessDenied.value = true;
        } else {
            loadError.value = err.message ?? t('managementCompaniesAdmin.loadFailed');
        }
    } finally {
        loading.value = false;
    }
}

async function setStatus(company, status, extra = {}) {
    formSuccess.value = '';
    try {
        const res = await apiClient.patch(`/admin/management-companies/${company.id}/status`, { status, ...extra });
        formSuccess.value = res?.message ?? '';
        await loadCompanies();
    } catch (err) {
        loadError.value = err.message ?? t('managementCompaniesAdmin.updateFailed');
    }
}

function approve(company) {
    setStatus(company, 'active');
}

function pause(company) {
    setStatus(company, 'paused');
}

function openReject(company) {
    rejectError.value = '';
    rejectForm.companyId = company.id;
    rejectForm.reason = '';
    rejectModalOpen.value = true;
}

function closeReject() {
    rejectModalOpen.value = false;
}

async function submitReject() {
    if (!rejectForm.reason) {
        rejectError.value = t('managementCompaniesAdmin.selectReasonError');
        return;
    }

    rejecting.value = true;
    rejectError.value = '';

    try {
        await apiClient.patch(`/admin/management-companies/${rejectForm.companyId}/status`, {
            status: 'rejected',
            rejection_reason: rejectForm.reason,
        });
        closeReject();
        await loadCompanies();
    } catch (err) {
        rejectError.value = err.message ?? t('managementCompaniesAdmin.updateFailed');
    } finally {
        rejecting.value = false;
    }
}

async function openMessages(company) {
    messagesHostId.value = company.manager_user_id;
    messagesHostName.value = company.manager_name || company.manager_email || '';
    messagesCompanyName.value = company.name;
    messageDraft.value = '';
    messagesModalOpen.value = true;
    messagesLoading.value = true;

    try {
        const res = await apiClient.get(`/admin/hosts/${messagesHostId.value}/messages`);
        hostMessages.value = res?.data ?? [];

        if (!hostMessages.value.length) {
            const intro = t('managementCompaniesAdmin.autoMessageBody', { company: messagesCompanyName.value });
            const sendRes = await apiClient.post(`/admin/hosts/${messagesHostId.value}/messages`, { body: intro });
            if (sendRes?.data) {
                hostMessages.value = [sendRes.data];
            }
        }
    } catch (err) {
        loadError.value = err.message ?? t('managementCompaniesAdmin.loadFailed');
    } finally {
        messagesLoading.value = false;
    }
}

function closeMessages() {
    messagesModalOpen.value = false;
}

async function sendMessage() {
    if (!messageDraft.value.trim() || sendingMessage.value) {
        return;
    }

    sendingMessage.value = true;

    try {
        const res = await apiClient.post(`/admin/hosts/${messagesHostId.value}/messages`, { body: messageDraft.value.trim() });
        if (res?.data) {
            hostMessages.value = [...hostMessages.value, res.data];
        }
        messageDraft.value = '';
    } catch {
        // Leave the draft in place so the admin can retry.
    } finally {
        sendingMessage.value = false;
    }
}

function openCreate() {
    createError.value = '';
    createForm.name = '';
    createForm.companyNumber = '';
    createModalOpen.value = true;
}

function closeCreate() {
    createModalOpen.value = false;
}

async function submitCreate() {
    if (!createForm.name.trim()) {
        createError.value = t('managementCompaniesAdmin.createFailed');
        return;
    }

    creating.value = true;
    createError.value = '';

    try {
        const res = await apiClient.post('/admin/management-companies', {
            name: createForm.name.trim(),
            company_number: createForm.companyNumber.trim() || null,
        });
        formSuccess.value = res?.message ?? t('managementCompaniesAdmin.created');
        closeCreate();
        await loadCompanies();
    } catch (err) {
        createError.value = err.message ?? t('managementCompaniesAdmin.createFailed');
    } finally {
        creating.value = false;
    }
}

onMounted(async () => {
    await loadCountries();
    await loadCompanies();
});
</script>

<style scoped>
.mgmt-companies-admin__messages {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.mgmt-companies-admin__message-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 280px;
    overflow-y: auto;
}

.mgmt-companies-admin__bubble {
    max-width: 80%;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 13.5px;
    line-height: 1.4;
}

.mgmt-companies-admin__bubble--admin {
    align-self: flex-end;
    background: #12352b;
    color: #f2ead9;
}

.mgmt-companies-admin__bubble--host {
    align-self: flex-start;
    background: #f0ecdf;
    color: #1c2b23;
}

.mgmt-companies-admin__bubble-meta {
    font-size: 11px;
    opacity: 0.7;
    margin-bottom: 2px;
}

.mgmt-companies-admin__message-input {
    resize: vertical;
}

.host-page-header--row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    flex-wrap: wrap;
}

.host-page-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.host-page-subtitle {
    margin: 8px 0 0;
    color: var(--host-text-muted, #5c6b66);
}

.host-table td {
    vertical-align: middle;
}

.mgmt-companies-admin__stats {
    display: flex;
    gap: 24px;
    margin: 16px 0 24px;
    flex-wrap: wrap;
}

.mgmt-companies-admin__stat {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.mgmt-companies-admin__stat-value {
    font-size: 24px;
    font-weight: 700;
}

.mgmt-companies-admin__stat-label {
    font-size: 13px;
    color: var(--host-text-muted, #5c6b66);
}

.mgmt-companies-admin__name {
    font-weight: 600;
}

.mgmt-companies-admin__muted {
    font-size: 12px;
    color: var(--host-text-muted, #5c6b66);
}

.host-table__actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}

.host-form-grid__full {
    grid-column: 1 / -1;
}

@media (max-width: 768px) {
    .host-form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
