<template>
    <HostModalShell :open="open" :title="t('team.assignApartmentsTitle', { name: memberName })" narrow @close="onClose">
        <p class="host-modal__subtitle">{{ t('team.assignApartmentsSubtitle') }}</p>

        <p v-if="loading" class="host-team-empty">{{ t('team.loading') }}</p>
        <p v-else-if="!apartments.length" class="host-team-empty">{{ t('team.assignApartmentsEmpty') }}</p>

        <div v-else class="host-assign-apartments-list">
            <label
                v-for="apartment in apartments"
                :key="apartment.id"
                class="host-assign-apartments-item"
            >
                <input
                    v-model="selectedIds"
                    type="checkbox"
                    :value="apartment.id"
                />
                <span>{{ apartment.name }}</span>
                <span class="host-assign-apartments-item__rooms">
                    {{ apartment.rooms === 1 ? t('team.roomStudio') : t('team.roomCount', { count: apartment.rooms }) }}
                </span>
            </label>
        </div>

        <template #footer>
            <div class="host-modal__footer-actions host-modal__footer-actions--end">
                <button type="button" class="host-btn host-btn--ghost" :disabled="saving" @click="onClose">
                    {{ t('common.cancel') }}
                </button>
                <button
                    type="button"
                    class="host-btn host-btn--primary"
                    :disabled="saving || loading"
                    @click="submit"
                >
                    {{ saving ? t('team.saving') : t('common.save') }}
                </button>
            </div>
        </template>
    </HostModalShell>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import apiClient from '@/api/client';
import HostModalShell from '@/components/modals/HostModalShell.vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    memberId: { type: [String, Number], default: null },
    memberName: { type: String, default: '' },
    assignedApartmentIds: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'saved']);

const { t } = useI18n();

const apartments = ref([]);
const selectedIds = ref([]);
const loading = ref(false);
const saving = ref(false);

async function loadApartments() {
    loading.value = true;

    try {
        const response = await apiClient.get('/apartments');
        apartments.value = Array.isArray(response.data) ? response.data : [];
    } catch {
        apartments.value = [];
    } finally {
        loading.value = false;
    }
}

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        selectedIds.value = [...props.assignedApartmentIds];
        loadApartments();
    }
});

function onClose() {
    emit('close');
}

async function submit() {
    saving.value = true;

    try {
        const response = await apiClient.patch(`/team/members/${props.memberId}/apartments`, {
            apartment_ids: selectedIds.value,
        });
        emit('saved', response.data);
        emit('close');
    } finally {
        saving.value = false;
    }
}
</script>
