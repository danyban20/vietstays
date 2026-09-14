<template>
    <div>
        <div class="host-page-header host-page-header--row">
            <div>
                <h1 class="host-page-title">
                    {{ t('buildingsAdmin.title') }}
                    <span class="host-pill host-pill--sand">{{ t('buildingsAdmin.badge') }}</span>
                </h1>
                <p class="host-page-subtitle">{{ t('buildingsAdmin.subtitle') }}</p>
            </div>
            <button
                v-if="!accessDenied"
                type="button"
                class="host-btn host-btn--accent"
                @click="openCreate"
            >
                {{ t('buildingsAdmin.addButton') }}
            </button>
        </div>

        <div v-if="accessDenied" class="host-form-error">{{ t('buildingsAdmin.accessDenied') }}</div>

        <template v-else>
            <div class="host-filters">
                <select v-model.number="filters.countryId" class="host-select" @change="onCountryChange">
                    <option :value="0">{{ t('buildingsAdmin.allCountries') }}</option>
                    <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <select v-model.number="filters.cityId" class="host-select" @change="onCityChange">
                    <option :value="0">{{ t('buildingsAdmin.allCities') }}</option>
                    <option v-for="c in cities" :key="c.city_id" :value="c.city_id">{{ c.name }}</option>
                </select>
                <select v-model.number="filters.districtId" class="host-select" @change="loadBuildings">
                    <option :value="0">{{ t('buildingsAdmin.allDistricts') }}</option>
                    <option v-for="d in districts" :key="d.district_id" :value="d.district_id">{{ d.name }}</option>
                </select>
            </div>

            <div class="host-filters">
                <input
                    v-model="filters.search"
                    type="search"
                    class="host-input"
                    :placeholder="t('buildingsAdmin.searchPlaceholder')"
                    @keyup.enter="loadBuildings"
                />
                <button type="button" class="host-btn host-btn--primary" @click="loadBuildings">
                    {{ t('buildingsAdmin.sort') }}
                </button>
                <label class="buildings-admin__toggle">
                    <input v-model="filters.archived" type="checkbox" @change="loadBuildings" />
                    {{ t('buildingsAdmin.showArchived') }}
                </label>
            </div>

            <div class="buildings-admin__stats">
                <div class="buildings-admin__stat">
                    <span class="buildings-admin__stat-value">{{ meta.total }}</span>
                    <span class="buildings-admin__stat-label">{{ t('buildingsAdmin.statTotal') }}</span>
                </div>
                <div class="buildings-admin__stat">
                    <span class="buildings-admin__stat-value">{{ meta.districts_with_buildings }}</span>
                    <span class="buildings-admin__stat-label">{{ t('buildingsAdmin.statDistricts') }}</span>
                </div>
                <div class="buildings-admin__stat" :class="{ 'buildings-admin__stat--warn': meta.short_name_conflicts > 0 }">
                    <span class="buildings-admin__stat-value">{{ meta.short_name_conflicts }}</span>
                    <span class="buildings-admin__stat-label">{{ t('buildingsAdmin.statConflicts') }}</span>
                </div>
            </div>

            <p v-if="loadError" class="host-form-error">{{ loadError }}</p>
            <p v-if="formSuccess" class="host-form-success">{{ formSuccess }}</p>

            <div v-if="loading" class="host-loading">{{ t('common.loading') }}</div>

            <div v-else class="host-table-wrap">
                <table class="host-table">
                    <thead>
                        <tr>
                            <th>{{ t('buildingsAdmin.colBuilding') }}</th>
                            <th>{{ t('buildingsAdmin.colShortName') }}</th>
                            <th>{{ t('buildingsAdmin.colCityDistrict') }}</th>
                            <th>{{ t('buildingsAdmin.colApartments') }}</th>
                            <th>{{ t('buildingsAdmin.colStatus') }}</th>
                            <th>{{ t('buildingsAdmin.colActions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!buildings.length">
                            <td colspan="6" class="host-table__empty">{{ t('buildingsAdmin.empty') }}</td>
                        </tr>
                        <tr v-for="building in buildings" :key="building.id">
                            <td>{{ building.name }}</td>
                            <td>
                                <span v-if="building.short_name" class="host-pill">{{ building.short_name }}</span>
                                <span v-else>—</span>
                            </td>
                            <td>
                                <div>{{ building.district?.name ?? '—' }}</div>
                                <div class="buildings-admin__muted">{{ building.city?.name }}</div>
                            </td>
                            <td>{{ building.apartments_count }}</td>
                            <td>
                                <span class="host-pill" :class="building.status === 'archived' ? 'host-pill--draft' : 'host-pill--active'">
                                    {{ building.status === 'archived' ? t('buildingsAdmin.statusArchived') : t('buildingsAdmin.statusPublished') }}
                                </span>
                            </td>
                            <td class="host-table__actions">
                                <button type="button" class="host-btn host-btn--ghost" @click="toggleArchive(building)">
                                    {{ building.status === 'archived' ? t('buildingsAdmin.restore') : t('buildingsAdmin.archive') }}
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>

        <HostModalShell :open="createModalOpen" :title="t('buildingsAdmin.createTitle')" @close="closeCreate">
            <div class="host-form-grid">
                <p v-if="createError" class="host-form-error host-form-grid__full">{{ createError }}</p>

                <div class="host-field host-field--full">
                    <label class="host-field__label" for="building-name">{{ t('buildingsAdmin.name') }}</label>
                    <input id="building-name" v-model="createForm.name" type="text" class="host-input" required />
                </div>

                <div class="host-field host-field--full">
                    <label class="host-field__label" for="building-short-name">{{ t('buildingsAdmin.shortName') }}</label>
                    <input id="building-short-name" v-model="createForm.shortName" type="text" class="host-input" />
                    <p class="host-field__hint">{{ t('buildingsAdmin.shortNameHint') }}</p>
                </div>

                <div class="host-field">
                    <label class="host-field__label" for="building-country">{{ t('buildingsAdmin.country') }}</label>
                    <select id="building-country" v-model.number="createForm.countryId" class="host-select" @change="onCreateCountryChange">
                        <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                </div>

                <div class="host-field">
                    <label class="host-field__label" for="building-city">{{ t('buildingsAdmin.city') }}</label>
                    <select id="building-city" v-model.number="createForm.cityId" class="host-select" @change="onCreateCityChange">
                        <option v-for="c in createCities" :key="c.city_id" :value="c.city_id">{{ c.name }}</option>
                    </select>
                </div>

                <div class="host-field host-field--full">
                    <label class="host-field__label" for="building-district">{{ t('buildingsAdmin.district') }}</label>
                    <select id="building-district" v-model.number="createForm.districtId" class="host-select">
                        <option v-for="d in createDistricts" :key="d.district_id" :value="d.district_id">{{ d.name }}</option>
                    </select>
                </div>
            </div>

            <template #footer>
                <div class="host-modal__footer-actions">
                    <button type="button" class="host-btn host-btn--ghost" @click="closeCreate">{{ t('common.cancel') }}</button>
                    <button type="button" class="host-btn host-btn--accent" :disabled="creating" @click="submitCreate">
                        {{ creating ? t('common.loading') : t('buildingsAdmin.create') }}
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
const accessDenied = ref(false);
const loadError = ref('');
const formSuccess = ref('');

const countries = ref([]);
const cities = ref([]);
const districts = ref([]);
const buildings = ref([]);
const meta = reactive({ total: 0, districts_with_buildings: 0, short_name_conflicts: 0 });

const filters = reactive({
    countryId: 0,
    cityId: 0,
    districtId: 0,
    search: '',
    archived: false,
});

const createModalOpen = ref(false);
const creating = ref(false);
const createError = ref('');
const createCities = ref([]);
const createDistricts = ref([]);
const createForm = reactive({
    name: '',
    shortName: '',
    countryId: 0,
    cityId: 0,
    districtId: 0,
});

async function loadCountries() {
    const res = await apiClient.get('/admin/countries');
    countries.value = res?.data ?? [];
}

async function loadCities(countryId) {
    const query = countryId ? `?country_id=${countryId}` : '';
    const res = await apiClient.get(`/admin/cities${query}`);
    return res?.data ?? [];
}

async function loadDistricts(cityId) {
    const query = cityId ? `?city_id=${cityId}` : '';
    const res = await apiClient.get(`/admin/districts${query}`);
    return res?.data ?? [];
}

async function onCountryChange() {
    filters.cityId = 0;
    filters.districtId = 0;
    cities.value = filters.countryId ? await loadCities(filters.countryId) : [];
    districts.value = [];
    loadBuildings();
}

async function onCityChange() {
    filters.districtId = 0;
    districts.value = filters.cityId ? await loadDistricts(filters.cityId) : [];
    loadBuildings();
}

async function onCreateCountryChange() {
    createForm.cityId = 0;
    createForm.districtId = 0;
    createCities.value = createForm.countryId ? await loadCities(createForm.countryId) : [];
    createDistricts.value = [];
}

async function onCreateCityChange() {
    createForm.districtId = 0;
    createDistricts.value = createForm.cityId ? await loadDistricts(createForm.cityId) : [];
}

async function loadBuildings() {
    loading.value = true;
    loadError.value = '';

    try {
        const params = new URLSearchParams();
        if (filters.countryId) params.set('country_id', filters.countryId);
        if (filters.cityId) params.set('city_id', filters.cityId);
        if (filters.districtId) params.set('district_id', filters.districtId);
        if (filters.search) params.set('search', filters.search);
        if (filters.archived) params.set('archived', '1');

        const query = params.toString();
        const res = await apiClient.get(`/admin/buildings${query ? `?${query}` : ''}`);
        buildings.value = res?.data ?? [];
        Object.assign(meta, res?.meta ?? {});
    } catch (err) {
        if (err.status === 403) {
            accessDenied.value = true;
        } else {
            loadError.value = err.message ?? t('buildingsAdmin.loadFailed');
        }
    } finally {
        loading.value = false;
    }
}

async function toggleArchive(building) {
    formSuccess.value = '';
    try {
        const archived = building.status !== 'archived';
        const res = await apiClient.patch(`/admin/buildings/${building.id}/archive`, { archived });
        formSuccess.value = res?.message ?? '';
        await loadBuildings();
    } catch (err) {
        loadError.value = err.message ?? t('buildingsAdmin.archiveFailed');
    }
}

async function openCreate() {
    createError.value = '';
    createForm.name = '';
    createForm.shortName = '';
    createForm.countryId = countries.value[0]?.id ?? 0;
    createForm.cityId = 0;
    createForm.districtId = 0;
    createCities.value = createForm.countryId ? await loadCities(createForm.countryId) : [];
    createDistricts.value = [];
    createModalOpen.value = true;
}

function closeCreate() {
    createModalOpen.value = false;
}

async function submitCreate() {
    if (!createForm.name.trim() || !createForm.districtId) {
        createError.value = t('buildingsAdmin.createFailed');
        return;
    }

    creating.value = true;
    createError.value = '';

    try {
        const res = await apiClient.post('/admin/buildings', {
            name: createForm.name.trim(),
            short_name: createForm.shortName.trim() || null,
            district_id: createForm.districtId,
        });
        formSuccess.value = res?.message ?? t('buildingsAdmin.created');
        closeCreate();
        await loadBuildings();
    } catch (err) {
        createError.value = err.message ?? t('buildingsAdmin.createFailed');
    } finally {
        creating.value = false;
    }
}

onMounted(async () => {
    await loadCountries();
    await loadBuildings();
});
</script>

<style scoped>
.host-page-header--row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    flex-wrap: wrap;
}

.host-page-subtitle {
    margin: 8px 0 0;
    color: var(--host-text-muted, #5c6b66);
}

.host-page-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.buildings-admin__toggle {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--host-text-muted, #5c6b66);
}

.buildings-admin__stats {
    display: flex;
    gap: 24px;
    margin: 16px 0 24px;
    flex-wrap: wrap;
}

.buildings-admin__stat {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.buildings-admin__stat-value {
    font-size: 24px;
    font-weight: 700;
}

.buildings-admin__stat-label {
    font-size: 13px;
    color: var(--host-text-muted, #5c6b66);
}

.buildings-admin__stat--warn .buildings-admin__stat-value {
    color: #b3541e;
}

.buildings-admin__muted {
    font-size: 12px;
    color: var(--host-text-muted, #5c6b66);
}

.host-form-grid__full {
    grid-column: 1 / -1;
}

.host-field__hint {
    margin: 4px 0 0;
    font-size: 12px;
    color: var(--host-text-muted, #5c6b66);
}

@media (max-width: 768px) {
    .host-form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
