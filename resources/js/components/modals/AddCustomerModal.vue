<template>
    <HostModalShell :open="open" :title="t('customers.addModalTitle')" narrow @close="emit('close')">
        <p class="host-modal__subtitle">{{ t('customers.addModalSubtitle') }}</p>

        <div class="host-form-grid host-form-grid--customer">
            <div class="host-field host-field--full">
                <label class="host-field__label" for="cust-name">{{ t('customers.addName') }}</label>
                <input
                    id="cust-name"
                    v-model="form.name"
                    type="text"
                    class="host-input"
                    :placeholder="t('customers.addNamePlaceholder')"
                />
            </div>

            <div class="host-field">
                <label class="host-field__label" for="cust-email">
                    {{ t('customers.addEmail') }}
                    <span class="host-field__optional">{{ t('customers.optional') }}</span>
                </label>
                <input
                    id="cust-email"
                    v-model="form.email"
                    type="email"
                    class="host-input"
                    :placeholder="t('customers.addEmailPlaceholder')"
                />
            </div>

            <div class="host-field">
                <label class="host-field__label" for="cust-phone">
                    {{ t('customers.addPhone') }}
                    <span class="host-field__optional">{{ t('customers.optional') }}</span>
                </label>
                <input
                    id="cust-phone"
                    v-model="form.phone"
                    type="tel"
                    class="host-input"
                    :placeholder="t('customers.addPhonePlaceholder')"
                />
            </div>

            <div class="host-field">
                <label class="host-field__label" for="cust-country">
                    {{ t('customers.addCountry') }}
                    <span class="host-field__optional">{{ t('customers.optional') }}</span>
                </label>
                <input
                    id="cust-country"
                    v-model="form.country"
                    type="text"
                    class="host-input"
                    :placeholder="t('customers.addCountryPlaceholder')"
                />
            </div>

            <div class="host-field">
                <label class="host-field__label" for="cust-agent">{{ t('customers.addReservedBy') }}</label>
                <select id="cust-agent" v-model="form.reserved_by" class="host-select">
                    <option value="">{{ t('customers.addReservedByPlaceholder') }}</option>
                    <option v-for="agent in agentOptionsList" :key="agent" :value="agent">{{ agent }}</option>
                </select>
            </div>

            <div class="host-field host-field--full">
                <label class="host-field__label" for="cust-note">
                    {{ t('customers.addNote') }}
                    <span class="host-field__optional">{{ t('customers.optional') }}</span>
                </label>
                <input
                    id="cust-note"
                    v-model="form.note"
                    type="text"
                    class="host-input"
                    :placeholder="t('customers.addNotePlaceholder')"
                />
            </div>

            <div v-if="isTemporary" class="host-customer-temp-notice host-field--full">
                <strong>{{ tempHeadline }}</strong>
                <p>{{ t('customers.addTempBody') }}</p>
            </div>

            <button
                type="button"
                class="host-customer-reserve-toggle host-field--full"
                @click="form.reserve_after_create = !form.reserve_after_create"
            >
                <span
                    class="host-customer-reserve-toggle__track"
                    :class="{ 'host-customer-reserve-toggle__track--on': form.reserve_after_create }"
                >
                    <span class="host-customer-reserve-toggle__thumb" />
                </span>
                <span class="host-customer-reserve-toggle__label">{{ t('customers.addReserveToggle') }}</span>
            </button>

            <p v-if="error" class="host-field-hint host-field-hint--error host-field--full">{{ error }}</p>
        </div>

        <template #footer>
            <div class="host-modal__footer-actions host-modal__footer-actions--end">
                <button type="button" class="host-btn host-btn--ghost" :disabled="saving" @click="emit('close')">
                    {{ t('customers.addCancel') }}
                </button>
                <button
                    type="button"
                    class="host-btn host-btn--primary"
                    :disabled="!canSubmit || saving"
                    @click="submit"
                >
                    {{ saving ? t('customers.addSaving') : saveLabel }}
                </button>
            </div>
        </template>
    </HostModalShell>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import apiClient from '@/api/client';
import HostModalShell from '@/components/modals/HostModalShell.vue';
import { useToast } from '@/composables/useToast';
import { useAuthStore } from '@/stores/auth';

const props = defineProps({
    open: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'created']);

const { t } = useI18n();
const router = useRouter();
const toast = useToast();
const auth = useAuthStore();

const saving = ref(false);
const error = ref('');
const agentOptions = ref([]);

const form = reactive({
    name: '',
    email: '',
    phone: '',
    country: '',
    reserved_by: '',
    note: '',
    reserve_after_create: false,
});

const agentOptionsList = computed(() => agentOptions.value);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            resetForm();
            loadAgents();
        }
    },
);

async function loadAgents() {
    const names = new Set();

    if (auth.user?.name) {
        names.add(auth.user.name);
    }

    try {
        const response = await apiClient.get('/team?type=sales');
        (response.data?.members ?? [])
            .filter((member) => member.status === 'active')
            .forEach((member) => names.add(member.name));
    } catch {
        /* optional */
    }

    agentOptions.value = [...names];
}

const isTemporary = computed(() => {
    const email = form.email.trim();

    if (!email) {
        return true;
    }

    return !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
});

const tempHeadline = computed(() => {
    const who = form.reserved_by || t('customers.addTempYou');

    return t('customers.addTempHeadline', { who });
});

const saveLabel = computed(() =>
    isTemporary.value ? t('customers.addSaveTemp') : t('customers.addSave'),
);

const canSubmit = computed(() => form.name.trim().length > 0);

function resetForm() {
    error.value = '';
    saving.value = false;
    Object.assign(form, {
        name: '',
        email: '',
        phone: '',
        country: '',
        reserved_by: auth.user?.name ?? '',
        note: '',
        reserve_after_create: false,
    });
}

async function submit() {
    if (!canSubmit.value || saving.value) {
        return;
    }

    saving.value = true;
    error.value = '';

    try {
        const emailTrimmed = form.email.trim();
        const validEmail = emailTrimmed && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailTrimmed);

        const payload = {
            name: form.name.trim(),
            email: validEmail ? emailTrimmed : null,
            phone: form.phone.trim() || null,
            country: form.country.trim() || null,
            reserved_by: form.reserved_by.trim() || null,
            note: form.note.trim() || null,
            reserve_after_create: form.reserve_after_create,
        };

        const response = await apiClient.post('/customers', payload);
        const customer = response.data;

        toast.show(response.message || t('customers.addSuccess'));

        emit('created', customer);

        if (response.reserve_after_create) {
            emit('close');
            router.push({
                name: 'bookings',
                query: {
                    add: 'manual',
                    guest_name: customer.name,
                    email: customer.rawEmail || '',
                    phone: customer.phone && customer.phone !== '—' ? customer.phone : '',
                },
            });
            return;
        }

        emit('close');
    } catch (err) {
        error.value = err.message || t('customers.addFailed');
    } finally {
        saving.value = false;
    }
}
</script>
