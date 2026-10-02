<template>
    <HostModalShell :open="open" :title="modalTitle" narrow @close="emit('close')">
        <p class="host-modal__subtitle">{{ modalSubtitle }}</p>

        <div class="host-form-grid host-form-grid--customer">
            <div class="host-field host-field--full">
                <label class="host-field__label" for="team-email">
                    {{ isSales ? t('team.inviteEmail') : t('team.inviteContact') }}
                </label>
                <input
                    id="team-email"
                    v-model="form.contact"
                    :type="isSales ? 'email' : 'tel'"
                    class="host-input"
                    :class="{ 'host-input--invalid': contactTouched && !contactValid }"
                    :placeholder="isSales ? t('team.inviteEmailPlaceholder') : t('team.inviteContactPlaceholder')"
                    @blur="contactTouched = true"
                />
                <p v-if="contactTouched && !contactValid && form.contact.trim()" class="host-field-hint host-field-hint--error">
                    {{ isSales ? t('team.inviteEmailInvalid') : t('team.inviteContactInvalid') }}
                </p>
            </div>

            <div class="host-field">
                <label class="host-field__label" for="team-role">{{ t('team.inviteRole') }}</label>
                <select id="team-role" v-model="form.role" class="host-select" @change="onRoleChange">
                    <option v-for="option in roleOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>

            <div class="host-field">
                <label class="host-field__label" for="team-area">{{ t('team.inviteArea') }}</label>
                <select id="team-area" v-model="form.area" class="host-select">
                    <option value="All buildings">{{ t('team.areaAllBuildings') }}</option>
                    <option v-for="area in mergedAreaOptions" :key="area" :value="area">{{ area }}</option>
                </select>
            </div>

            <div v-if="isSales && showPaySection" class="host-team-invite-pay host-field--full">
                <div class="host-team-invite-pay__title">{{ payTitle }}</div>
                <p class="host-team-invite-pay__help">{{ payHelp }}</p>

                <div v-if="paySetup === 'pooled'" class="host-team-invite-pay__pooled">
                    {{ t('team.invitePooledNote') }}
                </div>

                <template v-else>
                    <label class="host-field__label" for="team-rate">{{ payRateLabel }}</label>
                    <div class="host-team-invite-pay__rate">
                        <input
                            id="team-rate"
                            v-model.number="form.pay_rate"
                            type="number"
                            min="0"
                            max="100"
                            class="host-input"
                        />
                        <span>%</span>
                    </div>

                    <div class="host-team-invite-pay__example">
                        <div class="host-team-invite-pay__example-head">
                            <span>{{ t('team.inviteExample') }}</span>
                            <div class="host-team-invite-pay__example-input">
                                <input
                                    v-model.number="sampleAmount"
                                    type="number"
                                    min="0"
                                    step="100000"
                                    class="host-input"
                                />
                                <span>₫</span>
                            </div>
                        </div>
                        <div class="host-team-invite-pay__example-row">
                            <span>{{ t('team.inviteBookingAmount') }}</span>
                            <span>{{ formatVnd(sampleAmount) }}</span>
                        </div>
                        <div class="host-team-invite-pay__example-row host-team-invite-pay__example-row--cut">
                            <span>{{ t('team.inviteTheirCut', { name: inviteeName, rate: form.pay_rate }) }}</span>
                            <span>− {{ formatVnd(theirCut) }}</span>
                        </div>
                        <div class="host-team-invite-pay__example-row host-team-invite-pay__example-row--total">
                            <span>{{ t('team.inviteYouKeep') }}</span>
                            <span>{{ formatVnd(sampleAmount - theirCut) }}</span>
                        </div>
                    </div>

                    <div v-if="paySetup === 'reciprocal'" class="host-team-invite-pay__reciprocal">
                        <span class="host-team-invite-pay__reciprocal-icon" aria-hidden="true">↔</span>
                        <p>{{ t('team.inviteReciprocalNote', { name: inviteeName }) }}</p>
                    </div>
                </template>
            </div>

            <div class="host-team-invite-rights host-field--full">
                <div class="host-team-invite-rights__title">{{ t('team.inviteAccess') }}</div>
                <label
                    v-for="right in permissionOptions"
                    :key="right.key"
                    class="host-team-invite-rights__item"
                >
                    <span
                        class="host-team-invite-rights__box"
                        :class="{ 'host-team-invite-rights__box--on': form.permissions.includes(right.key) }"
                        aria-hidden="true"
                    >
                        {{ form.permissions.includes(right.key) ? '✓' : '' }}
                    </span>
                    <input
                        v-model="form.permissions"
                        type="checkbox"
                        class="host-team-invite-rights__input"
                        :value="right.key"
                    />
                    <span>{{ right.label }}</span>
                </label>
            </div>

            <p v-if="error" class="host-field-hint host-field-hint--error host-field--full">{{ error }}</p>
        </div>

        <template #footer>
            <div class="host-modal__footer-actions host-modal__footer-actions--end">
                <button type="button" class="host-btn host-btn--ghost" :disabled="saving" @click="emit('close')">
                    {{ t('team.inviteCancel') }}
                </button>
                <button
                    type="button"
                    class="host-btn host-btn--primary"
                    :disabled="!canSubmit || saving"
                    @click="submit"
                >
                    {{ saving ? t('team.inviteSending') : t('team.inviteSend') }}
                </button>
            </div>
        </template>
    </HostModalShell>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import apiClient from '@/api/client';
import HostModalShell from '@/components/modals/HostModalShell.vue';
import { useToast } from '@/composables/useToast';
import { formatVnd } from '@/data/customers-content.js';

const props = defineProps({
    open: { type: Boolean, default: false },
    mode: { type: String, default: 'sales' },
    areaOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'invited']);

const { t } = useI18n();
const toast = useToast();

const saving = ref(false);
const error = ref('');
const contactTouched = ref(false);
const sampleAmount = ref(12_000_000);
const loadedAreaOptions = ref([]);

const form = reactive({
    contact: '',
    role: '',
    area: 'All buildings',
    pay_rate: 12,
    permissions: [],
});

const isSales = computed(() => props.mode === 'sales');

const emailPattern = /^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i;

const mergedAreaOptions = computed(() => {
    const names = new Set([...props.areaOptions, ...loadedAreaOptions.value]);

    return [...names].filter(Boolean).sort((a, b) => a.localeCompare(b));
});

const contactValid = computed(() => {
    const contact = form.contact.trim();

    if (!contact) {
        return false;
    }

    if (isSales.value) {
        return emailPattern.test(contact);
    }

    return contact.includes('@')
        ? emailPattern.test(contact)
        : contact.replace(/\D/g, '').length >= 8;
});

const modalTitle = computed(() => (
    isSales.value ? t('team.inviteSalesTitle') : t('team.inviteOpsTitle')
));

const modalSubtitle = computed(() => (
    isSales.value ? t('team.inviteSalesSubtitle') : t('team.inviteOpsSubtitle')
));

const roleOptions = computed(() => {
    if (isSales.value) {
        return [
            { value: 'host_internal', label: t('team.roleHostInternal') },
            { value: 'cohost_external', label: t('team.roleCohostExternal') },
            { value: 'host_agent', label: t('team.roleHostAgent') },
        ];
    }

    return [
        { value: 'cleaning', label: t('team.opsRoleCleaning') },
        { value: 'keys', label: t('team.opsRoleKeys') },
        { value: 'courier', label: t('team.opsRoleCourier') },
        { value: 'cash', label: t('team.opsRoleCash') },
    ];
});

const permissionOptions = computed(() => {
    if (isSales.value) {
        return [
            { key: 'bookings', label: t('team.rightBookings') },
            { key: 'prices', label: t('team.rightPrices') },
            { key: 'guests', label: t('team.rightGuests') },
            { key: 'economy', label: t('team.rightEconomy') },
        ];
    }

    return [
        { key: 'tasks', label: t('team.rightTasks') },
        { key: 'guest_info', label: t('team.rightGuestInfo') },
        { key: 'photos', label: t('team.rightPhotos') },
        { key: 'keycode', label: t('team.rightKeycode') },
    ];
});

const paySetup = computed(() => {
    if (form.role === 'host_internal') {
        return 'pooled';
    }

    if (form.role === 'cohost_external') {
        return 'reciprocal';
    }

    return 'one_way';
});

const showPaySection = computed(() => isSales.value);

const payTitle = computed(() => {
    if (paySetup.value === 'pooled') {
        return t('team.invitePooledTitle');
    }

    return t('team.invitePayTitle');
});

const payHelp = computed(() => {
    if (paySetup.value === 'pooled') {
        return t('team.invitePooledHelp');
    }

    if (paySetup.value === 'reciprocal') {
        return t('team.inviteReciprocalHelp');
    }

    return t('team.inviteOneWayHelp');
});

const inviteeName = computed(() => {
    const contact = form.contact.trim();

    if (contact.includes('@')) {
        const local = contact.split('@')[0];

        return local
            .split(/[._-]+/)
            .filter(Boolean)
            .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
            .join(' ');
    }

    return contact || t('team.invitePersonFallback');
});

const payRateLabel = computed(() => t('team.invitePayRateLabel', { name: inviteeName.value }));

const theirCut = computed(() => Math.round(sampleAmount.value * (Number(form.pay_rate) || 0) / 100));

const canSubmit = computed(() => contactValid.value);

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            resetForm();
            loadAreaOptions();
        }
    },
);

watch(
    () => form.role,
    (role) => {
        if (role === 'host_internal') {
            form.pay_rate = 0;
        } else if (role === 'cohost_external') {
            form.pay_rate = 12;
        } else if (role === 'host_agent') {
            form.pay_rate = 8;
        }
    },
);

async function loadAreaOptions() {
    if (props.areaOptions.length > 0) {
        loadedAreaOptions.value = [];

        return;
    }

    try {
        const res = await apiClient.get('/locations/filters');
        const districts = res?.data?.districts ?? [];

        loadedAreaOptions.value = districts.map((d) => d.name).filter(Boolean);
    } catch {
        loadedAreaOptions.value = [];
    }
}

function resetForm() {
    error.value = '';
    saving.value = false;
    contactTouched.value = false;
    sampleAmount.value = 12_000_000;

    Object.assign(form, {
        contact: '',
        role: isSales.value ? 'host_internal' : 'cleaning',
        area: 'All buildings',
        pay_rate: isSales.value ? 0 : 12,
        permissions: isSales.value ? ['bookings', 'guests'] : ['tasks', 'photos'],
    });
}

function onRoleChange() {
    if (paySetup.value === 'pooled') {
        form.pay_rate = 0;
    } else if (paySetup.value === 'reciprocal') {
        form.pay_rate = 12;
    } else {
        form.pay_rate = 8;
    }
}

function roleLabel(value) {
    return roleOptions.value.find((option) => option.value === value)?.label ?? value;
}

function splitContact(contact) {
    const trimmed = contact.trim();

    if (trimmed.includes('@')) {
        return { email: trimmed, phone: null };
    }

    return { email: null, phone: trimmed };
}

function formatSubmitError(err) {
    const payload = err?.payload;

    if (payload?.errors && typeof payload.errors === 'object') {
        return Object.values(payload.errors).flat().join(' ');
    }

    return err?.message || t('team.inviteFailed');
}

async function submit() {
    contactTouched.value = true;

    if (!canSubmit.value || saving.value) {
        return;
    }

    saving.value = true;
    error.value = '';

    try {
        const { email, phone } = splitContact(form.contact.trim());

        const response = await apiClient.post('/team/invitations', {
            type: props.mode,
            email,
            phone,
            role: roleLabel(form.role),
            role_key: form.role,
            area: form.area,
            permissions: form.permissions,
            pay_rate: showPaySection.value && paySetup.value !== 'pooled' ? form.pay_rate : null,
            pay_setup: showPaySection.value ? paySetup.value : null,
        });

        toast.show(response.message || t('team.inviteSuccess'));
        emit('invited');
        emit('close');
    } catch (err) {
        error.value = formatSubmitError(err);
    } finally {
        saving.value = false;
    }
}
</script>
