<template>
    <div>
        <div class="host-page-header">
            <h1 class="host-page-title">{{ t('adminUsers.title') }}</h1>
            <p class="host-page-subtitle">{{ t('adminUsers.subtitle') }}</p>
        </div>

        <div v-if="accessDenied" class="host-form-error">{{ t('adminUsers.accessDenied') }}</div>

        <template v-else>
            <section class="host-panel host-panel--form">
                <h2 class="host-panel__title">{{ t('adminUsers.createTitle') }}</h2>
                <form class="host-form-grid" @submit.prevent="createUser">
                    <div v-if="formError" class="host-form-error host-form-grid__full">{{ formError }}</div>
                    <div v-if="formSuccess" class="host-form-success host-form-grid__full">{{ formSuccess }}</div>

                    <div class="host-field">
                        <label class="host-field__label" for="admin-name">{{ t('adminUsers.name') }}</label>
                        <input id="admin-name" v-model="form.name" type="text" class="host-input" required />
                    </div>

                    <div class="host-field">
                        <label class="host-field__label" for="admin-email">{{ t('adminUsers.email') }}</label>
                        <input id="admin-email" v-model="form.email" type="email" class="host-input" required />
                    </div>

                    <div class="host-field">
                        <label class="host-field__label" for="admin-password">{{ t('adminUsers.password') }}</label>
                        <input
                            id="admin-password"
                            v-model="form.password"
                            type="password"
                            class="host-input"
                            minlength="8"
                            required
                        />
                    </div>

                    <div class="host-field">
                        <label class="host-field__label" for="admin-role">{{ t('adminUsers.role') }}</label>
                        <select id="admin-role" v-model="form.role" class="host-select">
                            <option v-for="role in roleOptions" :key="role.value" :value="role.value">
                                {{ role.label }}
                            </option>
                        </select>
                    </div>

                    <div class="host-form-grid__actions">
                        <button type="submit" class="host-btn host-btn--primary" :disabled="creating">
                            {{ creating ? t('common.loading') : t('adminUsers.createButton') }}
                        </button>
                    </div>
                </form>
            </section>

            <form class="host-filters" @submit.prevent="loadUsers">
                <input
                    v-model="filters.search"
                    type="search"
                    class="host-input"
                    :placeholder="t('adminUsers.searchPlaceholder')"
                />
                <select v-model="filters.role" class="host-select">
                    <option value="">{{ t('adminUsers.allRoles') }}</option>
                    <option v-for="role in roleOptions" :key="role.value" :value="role.value">
                        {{ role.label }}
                    </option>
                </select>
                <button type="submit" class="host-btn host-btn--primary">{{ t('adminUsers.filter') }}</button>
                <button
                    v-if="filters.search || filters.role"
                    type="button"
                    class="host-btn host-btn--ghost"
                    @click="clearFilters"
                >
                    {{ t('common.cancel') }}
                </button>
            </form>

            <div v-if="loading" class="host-loading">{{ t('common.loading') }}</div>

            <div v-else class="host-table-wrap">
                <table class="host-table">
                    <thead>
                        <tr>
                            <th>{{ t('adminUsers.name') }}</th>
                            <th>{{ t('adminUsers.email') }}</th>
                            <th>{{ t('adminUsers.role') }}</th>
                            <th>{{ t('adminUsers.updated') }}</th>
                            <th>{{ t('adminUsers.actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!users.length">
                            <td colspan="5" class="host-table__empty">{{ t('adminUsers.empty') }}</td>
                        </tr>
                        <tr v-for="user in users" :key="user.id">
                            <td>{{ user.name }}</td>
                            <td>{{ user.email }}</td>
                            <td>
                                <span class="host-pill" :class="roleClass(user.role)">{{ user.role_label }}</span>
                            </td>
                            <td>{{ formatDate(user.updated_at) }}</td>
                            <td class="host-table__actions">
                                <button
                                    type="button"
                                    class="host-btn host-btn--sand"
                                    @click="openEdit(user)"
                                >
                                    {{ t('adminUsers.editUser') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <HostModalShell
            :open="editModalOpen"
            :title="t('adminUsers.editTitle')"
            @close="closeEdit"
        >
            <template v-if="editingUser">
                <p class="admin-users-edit__identity">
                    <strong>{{ editingUser.name }}</strong>
                    <span class="admin-users-edit__email">{{ editingUser.email }}</span>
                </p>

                <div class="host-field host-field--full">
                    <label class="host-field__label" for="edit-role">{{ t('adminUsers.role') }}</label>
                    <select id="edit-role" v-model="editForm.role" class="host-select">
                        <option v-for="role in roleOptions" :key="role.value" :value="role.value">
                            {{ role.label }}
                        </option>
                    </select>
                </div>

                <div class="host-field host-field--full">
                    <label class="host-field__label" for="edit-password">{{ t('adminUsers.newPassword') }}</label>
                    <input
                        id="edit-password"
                        v-model="editForm.password"
                        type="password"
                        class="host-input"
                        minlength="8"
                        autocomplete="new-password"
                    />
                    <p class="host-field__hint">{{ t('adminUsers.passwordHint') }}</p>
                </div>

                <p v-if="editError" class="host-form-error">{{ editError }}</p>
            </template>

            <template #footer>
                <div class="host-modal__footer-actions">
                    <button type="button" class="host-btn host-btn--ghost" @click="closeEdit">
                        {{ t('common.cancel') }}
                    </button>
                    <button
                        type="button"
                        class="host-btn host-btn--accent"
                        :disabled="savingEdit"
                        @click="saveEdit"
                    >
                        {{ savingEdit ? t('common.loading') : t('common.save') }}
                    </button>
                </div>
            </template>
        </HostModalShell>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import apiClient from '@/api/client';
import HostModalShell from '@/components/modals/HostModalShell.vue';

const { t } = useI18n();

const loading = ref(true);
const creating = ref(false);
const accessDenied = ref(false);
const formError = ref('');
const formSuccess = ref('');
const users = ref([]);

const editModalOpen = ref(false);
const editingUser = ref(null);
const editError = ref('');
const savingEdit = ref(false);

const filters = reactive({
    search: '',
    role: '',
});

const form = reactive({
    name: '',
    email: '',
    password: '',
    role: 'admin',
});

const editForm = reactive({
    role: 'admin',
    password: '',
});

const roleOptions = [
    { value: 'admin', label: 'Administrator' },
    { value: 'partner', label: 'Partner' },
    { value: 'host', label: 'Host' },
    { value: 'staff', label: 'Staff' },
    { value: 'ambassador', label: 'Ambassador' },
];

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

function roleClass(role) {
    return {
        'host-pill--success': role === 'admin',
        'host-pill--sand': role === 'partner',
        'host-pill--muted': role === 'host',
    };
}

async function loadUsers() {
    loading.value = true;
    accessDenied.value = false;

    try {
        const params = new URLSearchParams();
        if (filters.search) {
            params.set('search', filters.search);
        }
        if (filters.role) {
            params.set('role', filters.role);
        }

        const query = params.toString();
        const res = await apiClient.get(`/admin/users${query ? `?${query}` : ''}`);
        users.value = res?.data ?? [];
    } catch (err) {
        if (err.status === 403) {
            accessDenied.value = true;
            users.value = [];
        } else {
            formError.value = err.message ?? t('adminUsers.loadFailed');
        }
    } finally {
        loading.value = false;
    }
}

async function createUser() {
    creating.value = true;
    formError.value = '';
    formSuccess.value = '';

    try {
        const res = await apiClient.post('/admin/users', {
            name: form.name.trim(),
            email: form.email.trim(),
            password: form.password,
            role: form.role,
        });

        formSuccess.value = res?.message ?? t('adminUsers.created');
        form.name = '';
        form.email = '';
        form.password = '';
        form.role = 'admin';
        await loadUsers();
    } catch (err) {
        formError.value = err.message ?? t('adminUsers.createFailed');
    } finally {
        creating.value = false;
    }
}

function openEdit(user) {
    editingUser.value = user;
    editForm.role = user.role;
    editForm.password = '';
    editError.value = '';
    editModalOpen.value = true;
}

function closeEdit() {
    editModalOpen.value = false;
    editingUser.value = null;
    editForm.password = '';
    editError.value = '';
}

async function saveEdit() {
    if (!editingUser.value) {
        return;
    }

    if (editForm.password && editForm.password.length < 8) {
        editError.value = t('adminUsers.passwordTooShort');
        return;
    }

    savingEdit.value = true;
    editError.value = '';
    formError.value = '';
    formSuccess.value = '';

    const payload = { role: editForm.role };
    if (editForm.password) {
        payload.password = editForm.password;
    }

    try {
        const res = await apiClient.patch(`/admin/users/${editingUser.value.id}`, payload);
        formSuccess.value = res?.message ?? t('adminUsers.updateSuccess');
        closeEdit();
        await loadUsers();
    } catch (err) {
        editError.value = err.message ?? t('adminUsers.updateFailed');
    } finally {
        savingEdit.value = false;
    }
}

function clearFilters() {
    filters.search = '';
    filters.role = '';
    loadUsers();
}

onMounted(loadUsers);
</script>

<style scoped>
.host-page-subtitle {
    margin: 8px 0 0;
    color: var(--host-text-muted, #5c6b66);
}

.host-panel {
    background: var(--host-surface, #fff);
    border: 1px solid var(--host-border, #d8e0dc);
    border-radius: 16px;
    padding: 24px;
    margin-bottom: 24px;
}

.host-panel__title {
    margin: 0 0 16px;
    font-size: 18px;
    font-weight: 600;
}

.host-form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.host-form-grid__full {
    grid-column: 1 / -1;
}

.host-form-grid__actions {
    grid-column: 1 / -1;
    display: flex;
    justify-content: flex-start;
}

.host-form-success {
    padding: 12px 16px;
    border-radius: 12px;
    background: #e8f5ef;
    color: #1f5c45;
}

.admin-users-edit__identity {
    margin: 0 0 20px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.admin-users-edit__email {
    color: var(--host-text-muted, #5c6b66);
    font-size: 14px;
}

@media (max-width: 768px) {
    .host-form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
