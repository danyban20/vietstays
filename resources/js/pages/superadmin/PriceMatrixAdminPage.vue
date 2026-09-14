<template>
    <div>
        <div class="host-page-header host-page-header--row">
            <div>
                <h1 class="host-page-title">
                    {{ t('priceMatrixAdmin.title') }}
                    <span class="host-pill host-pill--sand">{{ t('priceMatrixAdmin.badge') }}</span>
                </h1>
                <p class="host-page-subtitle">{{ t('priceMatrixAdmin.subtitle') }}</p>
            </div>
            <div class="price-matrix__actions">
                <span v-if="dirtyCount" class="price-matrix__dirty">
                    {{ t('priceMatrixAdmin.unsavedChanges', dirtyCount, { count: dirtyCount }) }}
                </span>
                <button type="button" class="host-btn host-btn--ghost" :disabled="!dirtyCount" @click="resetChanges">
                    {{ t('priceMatrixAdmin.resetChanges') }}
                </button>
                <button type="button" class="host-btn host-btn--accent" :disabled="!dirtyCount || saving" @click="saveChanges">
                    {{ saving ? t('common.loading') : t('priceMatrixAdmin.saveChanges') }}
                </button>
            </div>
        </div>

        <div v-if="accessDenied" class="host-form-error">{{ t('priceMatrixAdmin.accessDenied') }}</div>

        <template v-else>
            <div class="price-matrix__info">
                <div class="price-matrix__info-block">
                    <h2>{{ t('priceMatrixAdmin.howCalculatedTitle') }}</h2>
                    <p class="price-matrix__formula">{{ t('priceMatrixAdmin.howCalculatedFormula') }}</p>
                    <div class="price-matrix__tiers">
                        <span class="host-pill">{{ t('priceMatrixAdmin.tierStandard') }} {{ formatPercent(factors.standard) }}</span>
                        <span class="host-pill">{{ t('priceMatrixAdmin.tierAboveAverage') }} {{ formatPercent(factors.above_average) }}</span>
                        <span class="host-pill">{{ t('priceMatrixAdmin.tierPremium') }} {{ formatPercent(factors.premium) }}</span>
                    </div>
                </div>
            </div>

            <div class="host-filters">
                <select v-model.number="filters.districtId" class="host-select" @change="loadMatrix">
                    <option :value="0">{{ t('priceMatrixAdmin.allDistricts') }}</option>
                    <option v-for="d in districts" :key="d.district_id" :value="d.district_id">{{ d.label }}</option>
                </select>
                <input
                    v-model="filters.search"
                    type="search"
                    class="host-input"
                    :placeholder="t('priceMatrixAdmin.searchPlaceholder')"
                    @keyup.enter="loadMatrix"
                />
                <button type="button" class="host-btn host-btn--primary" @click="loadMatrix">⌕</button>
            </div>

            <p v-if="loadError" class="host-form-error">{{ loadError }}</p>
            <p v-if="saveMessage" class="host-form-success">{{ saveMessage }}</p>

            <div v-if="loading" class="host-loading">{{ t('common.loading') }}</div>

            <div v-else-if="!districts.length" class="host-table__empty">{{ t('priceMatrixAdmin.empty') }}</div>

            <div v-else class="host-table-wrap price-matrix__wrap">
                <table class="host-table price-matrix__table">
                    <thead>
                        <tr>
                            <th>{{ t('priceMatrixAdmin.colBuilding') }}</th>
                            <th v-for="column in columns" :key="column">{{ column }}</th>
                        </tr>
                    </thead>
                    <tbody v-for="district in districts" :key="district.district_id">
                        <tr class="price-matrix__district-row">
                            <td :colspan="columns.length + 1">
                                <strong>{{ district.label }}</strong>
                                <span class="price-matrix__district-avg">
                                    {{
                                        t('priceMatrixAdmin.districtAverage', {
                                            column: '2BR+2WC',
                                            value: formatVnd(district.averages['2BR+2WC']),
                                        })
                                    }}
                                </span>
                            </td>
                        </tr>
                        <tr v-for="building in district.buildings" :key="building.id">
                            <td>
                                {{ building.name }}
                                <span v-if="building.short_name" class="host-pill">{{ building.short_name }}</span>
                            </td>
                            <td v-for="column in columns" :key="column">
                                <input
                                    type="number"
                                    step="10000"
                                    class="host-input price-matrix__cell"
                                    :class="{ 'price-matrix__cell--dirty': isDirty(building.id, column) }"
                                    :value="cellValue(building, column)"
                                    @input="onCellInput(building.id, column, $event.target.value)"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import apiClient from '@/api/client';

const { t } = useI18n();

const loading = ref(true);
const saving = ref(false);
const accessDenied = ref(false);
const loadError = ref('');
const saveMessage = ref('');

const columns = ref([]);
const factors = reactive({ standard: 0.9, above_average: 1.0, premium: 1.1 });
const districts = ref([]);

const filters = reactive({ districtId: 0, search: '' });

// dirty edits keyed by "buildingId:typeKey" -> integer price
const edits = reactive({});

const dirtyCount = computed(() => Object.keys(edits).length);

function cellKey(buildingId, typeKey) {
    return `${buildingId}:${typeKey}`;
}

function isDirty(buildingId, typeKey) {
    return Object.prototype.hasOwnProperty.call(edits, cellKey(buildingId, typeKey));
}

function cellValue(building, typeKey) {
    const key = cellKey(building.id, typeKey);
    if (Object.prototype.hasOwnProperty.call(edits, key)) {
        return edits[key];
    }
    return building.prices[typeKey] ?? '';
}

function onCellInput(buildingId, typeKey, rawValue) {
    const key = cellKey(buildingId, typeKey);
    const value = rawValue === '' ? '' : Number(rawValue);
    edits[key] = value;
}

function resetChanges() {
    Object.keys(edits).forEach((key) => delete edits[key]);
}

function formatPercent(factor) {
    const pct = Math.round((factor - 1) * 100);
    return pct > 0 ? `+${pct}%` : `${pct}%`;
}

function formatVnd(value) {
    if (value === null || value === undefined) {
        return '—';
    }
    return new Intl.NumberFormat('vi-VN').format(value);
}

async function loadMatrix() {
    loading.value = true;
    loadError.value = '';

    try {
        const params = new URLSearchParams();
        if (filters.districtId) params.set('district_id', filters.districtId);
        if (filters.search) params.set('search', filters.search);

        const query = params.toString();
        const res = await apiClient.get(`/admin/price-matrix${query ? `?${query}` : ''}`);
        columns.value = res?.data?.columns ?? [];
        Object.assign(factors, res?.data?.standard_factors ?? {});
        districts.value = res?.data?.districts ?? [];
    } catch (err) {
        if (err.status === 403) {
            accessDenied.value = true;
        } else {
            loadError.value = err.message ?? t('priceMatrixAdmin.loadFailed');
        }
    } finally {
        loading.value = false;
    }
}

async function saveChanges() {
    const changes = Object.entries(edits)
        .filter(([, value]) => value !== '')
        .map(([key, value]) => {
            const [buildingId, typeKey] = key.split(':');
            return { building_id: Number(buildingId), type_key: typeKey, price_vnd: Number(value) };
        });

    if (!changes.length) {
        return;
    }

    saving.value = true;
    saveMessage.value = '';
    loadError.value = '';

    try {
        const res = await apiClient.patch('/admin/price-matrix', { changes });
        saveMessage.value = res?.message ?? t('priceMatrixAdmin.saved');
        resetChanges();
        await loadMatrix();
    } catch (err) {
        loadError.value = err.message ?? t('priceMatrixAdmin.saveFailed');
    } finally {
        saving.value = false;
    }
}

onMounted(loadMatrix);
</script>

<style scoped>
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
    max-width: 60ch;
    color: var(--host-text-muted, #5c6b66);
}

.price-matrix__actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.price-matrix__dirty {
    font-size: 13px;
    color: var(--host-text-muted, #5c6b66);
}

.price-matrix__info {
    background: var(--host-surface, #fff);
    border: 1px solid var(--host-border, #d8e0dc);
    border-radius: 16px;
    padding: 16px 20px;
    margin: 16px 0 24px;
}

.price-matrix__info-block h2 {
    margin: 0 0 8px;
    font-size: 14px;
}

.price-matrix__formula {
    margin: 0 0 12px;
    color: var(--host-text-muted, #5c6b66);
    font-size: 14px;
}

.price-matrix__tiers {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.price-matrix__wrap {
    overflow-x: auto;
}

.price-matrix__table th,
.price-matrix__table td {
    white-space: nowrap;
}

.price-matrix__district-row td {
    background: var(--host-surface-alt, #f4f6f4);
}

.price-matrix__district-avg {
    margin-left: 12px;
    font-size: 12px;
    color: var(--host-text-muted, #5c6b66);
}

.price-matrix__cell {
    width: 110px;
}

.price-matrix__cell--dirty {
    border-color: var(--host-accent, #e08a2b);
    background: var(--host-accent-soft, #fde8d2);
}
</style>
