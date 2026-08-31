<template>
    <div>
        <div class="host-page-header">
            <h1 class="host-page-title">Email settings</h1>
        </div>

        <SettingsSubnav />

        <div v-if="loading" class="host-loading">Loading email settings…</div>

        <div v-else-if="accessDenied" class="host-form-error">
            Only administrators can manage email settings.
        </div>

        <form v-else class="host-settings-card" @submit.prevent="save">
            <div class="host-settings-columns">
                <section class="host-settings-panel">
                    <h2 class="host-settings-panel__title">Incoming — IMAP settings</h2>
                    <div class="host-settings-panel__body host-form-grid">
                        <div class="host-field host-field--full">
                            <label class="host-field__label" for="imap-host">Host</label>
                            <input id="imap-host" v-model="form.host" type="text" class="host-input" />
                        </div>
                        <div class="host-field">
                            <label class="host-field__label" for="imap-port">Port</label>
                            <input id="imap-port" v-model="form.port" type="text" class="host-input" />
                        </div>
                        <div class="host-field">
                            <label class="host-field__label" for="imap-encryption">Encryption</label>
                            <select id="imap-encryption" v-model="form.encryption" class="host-select">
                                <option value="ssl">SSL</option>
                                <option value="tls">TLS</option>
                            </select>
                        </div>
                        <div class="host-field host-field--full">
                            <label class="host-field__label" for="imap-username">Username</label>
                            <input id="imap-username" v-model="form.username" type="text" class="host-input" />
                        </div>
                        <div class="host-field host-field--full">
                            <label class="host-field__label" for="imap-password">Password</label>
                            <input id="imap-password" v-model="form.password" type="password" class="host-input" />
                        </div>
                    </div>
                </section>

                <section class="host-settings-panel">
                    <h2 class="host-settings-panel__title">Outgoing — SMTP settings</h2>
                    <div class="host-settings-panel__body host-form-grid">
                        <div class="host-field host-field--full">
                            <label class="host-field__label" for="sender-email">Sender email</label>
                            <input id="sender-email" v-model="form.sender_email" type="email" class="host-input" />
                        </div>
                        <div class="host-field host-field--full">
                            <label class="host-field__label" for="sender-name">Sender name</label>
                            <input id="sender-name" v-model="form.sender_name" type="text" class="host-input" />
                        </div>
                        <div class="host-field host-field--full">
                            <label class="host-field__label" for="smtp-host">Host</label>
                            <input id="smtp-host" v-model="form.host_out" type="text" class="host-input" />
                        </div>
                        <div class="host-field">
                            <label class="host-field__label" for="smtp-port">Port</label>
                            <input id="smtp-port" v-model="form.port_out" type="text" class="host-input" />
                        </div>
                        <div class="host-field">
                            <label class="host-field__label" for="smtp-encryption">Encryption</label>
                            <select id="smtp-encryption" v-model="form.encryption_out" class="host-select">
                                <option value="ssl">SSL</option>
                                <option value="tls">TLS</option>
                            </select>
                        </div>
                        <div class="host-field host-field--full">
                            <label class="host-field__label" for="smtp-username">Username</label>
                            <input id="smtp-username" v-model="form.username_out" type="text" class="host-input" />
                        </div>
                        <div class="host-field host-field--full">
                            <label class="host-field__label" for="smtp-password">Password</label>
                            <input id="smtp-password" v-model="form.password_out" type="password" class="host-input" />
                        </div>
                        <div class="host-field host-field--full">
                            <label class="host-check-inline">
                                <input v-model="form.disable_ssl_verification_out" type="checkbox" />
                                Disable SSL verification
                            </label>
                        </div>
                        <div class="host-field host-field--full">
                            <label class="host-field__label" for="admin-email">New service admin email</label>
                            <input
                                id="admin-email"
                                v-model="form.new_service_admin_email"
                                type="email"
                                class="host-input"
                            />
                        </div>
                    </div>
                </section>
            </div>

            <p v-if="error" class="host-form-error">{{ error }}</p>
            <p v-if="success" class="host-form-success">{{ success }}</p>

            <div class="host-settings-card__footer">
                <button type="submit" class="host-btn host-btn--primary" :disabled="saving">
                    {{ saving ? 'Saving…' : 'Save changes' }}
                </button>
            </div>
        </form>
    </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import apiClient from '@/api/client';
import SettingsSubnav from '@/components/SettingsSubnav.vue';

const loading = ref(true);
const saving = ref(false);
const accessDenied = ref(false);
const error = ref('');
const success = ref('');

const form = reactive({
    host: '',
    port: '',
    username: '',
    password: '',
    encryption: 'ssl',
    sender_email: '',
    sender_name: '',
    host_out: '',
    port_out: '',
    username_out: '',
    password_out: '',
    encryption_out: 'tls',
    disable_ssl_verification_out: false,
    new_service_admin_email: '',
});

function applySettings(data) {
    form.host = data.host ?? '';
    form.port = data.port ?? '';
    form.username = data.username ?? '';
    form.password = data.password ?? '';
    form.encryption = data.encryption ?? 'ssl';
    form.sender_email = data.sender_email ?? '';
    form.sender_name = data.sender_name ?? '';
    form.host_out = data.host_out ?? '';
    form.port_out = data.port_out ?? '';
    form.username_out = data.username_out ?? '';
    form.password_out = data.password_out ?? '';
    form.encryption_out = data.encryption_out ?? 'tls';
    form.disable_ssl_verification_out = Boolean(data.disable_ssl_verification_out);
    form.new_service_admin_email = data.new_service_admin_email ?? '';
}

async function loadSettings() {
    loading.value = true;
    error.value = '';
    accessDenied.value = false;

    try {
        const payload = await apiClient.get('/settings/email');
        applySettings(payload?.data ?? {});
    } catch (err) {
        if (err.status === 403) {
            accessDenied.value = true;
        } else {
            error.value = err.message || 'Could not load email settings.';
        }
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    error.value = '';
    success.value = '';

    try {
        const payload = await apiClient.put('/settings/email', { ...form });
        applySettings(payload?.data ?? {});
        success.value = payload?.message || 'Email settings saved.';
    } catch (err) {
        error.value = err.message || 'Could not save email settings.';
    } finally {
        saving.value = false;
    }
}

onMounted(loadSettings);
</script>
