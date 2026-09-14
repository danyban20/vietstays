<template>
    <div>
        <div class="host-page-header">
            <h1 class="host-page-title">
                {{ t('locationsAdmin.title') }}
                <span class="host-pill host-pill--sand">{{ t('locationsAdmin.badge') }}</span>
            </h1>
            <p class="host-page-subtitle">{{ t('locationsAdmin.subtitle') }}</p>
        </div>

        <div v-if="accessDenied" class="host-form-error">{{ t('locationsAdmin.accessDenied') }}</div>
        <p v-if="loadError" class="host-form-error">{{ loadError }}</p>
        <p v-if="formSuccess" class="host-form-success">{{ formSuccess }}</p>

        <div v-if="!accessDenied" class="locations-admin__columns">
            <section class="locations-admin__column">
                <header class="locations-admin__column-header">
                    <h2>{{ t('locationsAdmin.colCountries') }}</h2>
                    <button type="button" class="host-btn host-btn--ghost" @click="openCreate('country')">
                        {{ t('locationsAdmin.addNew') }}
                    </button>
                </header>
                <ul class="locations-admin__list">
                    <li
                        v-for="country in countries"
                        :key="country.id"
                        class="locations-admin__item"
                        :class="{ 'locations-admin__item--active': selectedCountryId === country.id }"
                        @click="selectCountry(country.id)"
                    >
                        <span class="locations-admin__item-name">{{ country.name }}</span>
                        <span class="locations-admin__item-meta">
                            {{ t('locationsAdmin.citiesCount', country.cities_count, { count: country.cities_count }) }} ·
                            {{ t('locationsAdmin.districtsCount', country.districts_count, { count: country.districts_count }) }}
                        </span>
                    </li>
                </ul>
            </section>

            <section class="locations-admin__column">
                <header class="locations-admin__column-header">
                    <h2>{{ t('locationsAdmin.colCities') }}</h2>
                    <button
                        type="button"
                        class="host-btn host-btn--ghost"
                        :disabled="!selectedCountryId"
                        @click="openCreate('city')"
                    >
                        {{ t('locationsAdmin.addNew') }}
                    </button>
                </header>
                <p v-if="!selectedCountryId" class="locations-admin__hint">{{ t('locationsAdmin.selectCountryFirst') }}</p>
                <ul v-else class="locations-admin__list">
                    <li
                        v-for="city in cities"
                        :key="city.city_id"
                        class="locations-admin__item"
                        :class="{ 'locations-admin__item--active': selectedCityId === city.city_id }"
                        @click="selectCity(city.city_id)"
                    >
                        <span class="locations-admin__item-name">{{ city.name }}</span>
                        <span class="locations-admin__item-meta">
                            {{ t('locationsAdmin.districtsCount', city.districts_count, { count: city.districts_count }) }} ·
                            {{ t('locationsAdmin.buildingsCount', city.buildings_count, { count: city.buildings_count }) }}
                        </span>
                    </li>
                </ul>
            </section>

            <section class="locations-admin__column">
                <header class="locations-admin__column-header">
                    <h2>{{ selectedCity ? t('locationsAdmin.colDistricts') + ' — ' + selectedCity.name : t('locationsAdmin.colDistricts') }}</h2>
                    <button
                        type="button"
                        class="host-btn host-btn--ghost"
                        :disabled="!selectedCityId"
                        @click="openCreate('district')"
                    >
                        {{ t('locationsAdmin.addNew') }}
                    </button>
                </header>
                <p v-if="!selectedCityId" class="locations-admin__hint">{{ t('locationsAdmin.selectCityFirst') }}</p>
                <ul v-else class="locations-admin__list">
                    <li v-for="district in districts" :key="district.district_id" class="locations-admin__item locations-admin__item--static">
                        <span class="locations-admin__item-name">
                            {{ district.name }}
                            <span v-if="district.district_code" class="host-pill">{{ district.district_code }}</span>
                        </span>
                        <span class="locations-admin__item-meta">
                            {{ t('locationsAdmin.buildingsCount', district.buildings_count, { count: district.buildings_count }) }}
                        </span>
                    </li>
                </ul>
            </section>
        </div>

        <HostModalShell :open="createModalOpen" :title="createModalTitle" @close="closeCreate">
            <div class="host-form-grid">
                <p v-if="createError" class="host-form-error host-form-grid__full">{{ createError }}</p>

                <div class="host-field host-field--full">
                    <label class="host-field__label" for="loc-name">{{ t('locationsAdmin.name') }}</label>
                    <input id="loc-name" v-model="createForm.name" type="text" class="host-input" required />
                </div>

                <div v-if="createKind === 'country'" class="host-field host-field--full">
                    <label class="host-field__label" for="loc-code">{{ t('locationsAdmin.code') }}</label>
                    <input id="loc-code" v-model="createForm.code" type="text" maxlength="2" class="host-input" />
                </div>

                <div v-if="createKind === 'district'" class="host-field host-field--full">
                    <label class="host-field__label" for="loc-district-code">{{ t('locationsAdmin.districtCode') }}</label>
                    <input id="loc-district-code" v-model="createForm.districtCode" type="text" class="host-input" />
                    <p class="host-field__hint">{{ t('locationsAdmin.districtCodeHint') }}</p>
                </div>
            </div>

            <template #footer>
                <div class="host-modal__footer-actions">
                    <button type="button" class="host-btn host-btn--ghost" @click="closeCreate">{{ t('common.cancel') }}</button>
                    <button type="button" class="host-btn host-btn--accent" :disabled="creating" @click="submitCreate">
                        {{ creating ? t('common.loading') : t('locationsAdmin.create') }}
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
const districts = ref([]);

const selectedCountryId = ref(0);
const selectedCityId = ref(0);

const selectedCity = computed(() => cities.value.find((c) => c.city_id === selectedCityId.value) ?? null);

const createModalOpen = ref(false);
const createKind = ref('country');
const creating = ref(false);
const createError = ref('');
const createForm = reactive({ name: '', code: '', districtCode: '' });

const createModalTitle = computed(() => {
    if (createKind.value === 'country') return t('locationsAdmin.newCountryTitle');
    if (createKind.value === 'city') return t('locationsAdmin.newCityTitle');
    return t('locationsAdmin.newDistrictTitle');
});

async function loadCountries() {
    const res = await apiClient.get('/admin/countries');
    countries.value = res?.data ?? [];
}

async function loadCities(countryId) {
    const res = await apiClient.get(`/admin/cities?country_id=${countryId}`);
    cities.value = res?.data ?? [];
}

async function loadDistricts(cityId) {
    const res = await apiClient.get(`/admin/districts?city_id=${cityId}`);
    districts.value = res?.data ?? [];
}

async function selectCountry(id) {
    selectedCountryId.value = id;
    selectedCityId.value = 0;
    districts.value = [];
    await loadCities(id);
}

async function selectCity(id) {
    selectedCityId.value = id;
    await loadDistricts(id);
}

function openCreate(kind) {
    createKind.value = kind;
    createError.value = '';
    createForm.name = '';
    createForm.code = '';
    createForm.districtCode = '';
    createModalOpen.value = true;
}

function closeCreate() {
    createModalOpen.value = false;
}

async function submitCreate() {
    if (!createForm.name.trim()) {
        createError.value = t('locationsAdmin.createFailed');
        return;
    }

    creating.value = true;
    createError.value = '';
    formSuccess.value = '';

    try {
        if (createKind.value === 'country') {
            const res = await apiClient.post('/admin/countries', {
                name: createForm.name.trim(),
                code: createForm.code.trim() || null,
            });
            formSuccess.value = res?.message ?? '';
            await loadCountries();
        } else if (createKind.value === 'city') {
            const res = await apiClient.post('/admin/cities', {
                name: createForm.name.trim(),
                country_id: selectedCountryId.value,
            });
            formSuccess.value = res?.message ?? '';
            await loadCountries();
            await loadCities(selectedCountryId.value);
        } else {
            const res = await apiClient.post('/admin/districts', {
                name: createForm.name.trim(),
                city_id: selectedCityId.value,
                district_code: createForm.districtCode.trim() || null,
            });
            formSuccess.value = res?.message ?? '';
            await loadCities(selectedCountryId.value);
            await loadDistricts(selectedCityId.value);
        }

        closeCreate();
    } catch (err) {
        createError.value = err.message ?? t('locationsAdmin.createFailed');
    } finally {
        creating.value = false;
    }
}

onMounted(async () => {
    loading.value = true;
    loadError.value = '';

    try {
        await loadCountries();
        if (countries.value.length) {
            await selectCountry(countries.value[0].id);
        }
    } catch (err) {
        if (err.status === 403) {
            accessDenied.value = true;
        } else {
            loadError.value = err.message ?? t('locationsAdmin.loadFailed');
        }
    } finally {
        loading.value = false;
    }
});
</script>

<style scoped>
.host-page-title {
    display: flex;
    align-items: center;
    gap: 12px;
}

.host-page-subtitle {
    margin: 8px 0 0;
    color: var(--host-text-muted, #5c6b66);
}

.locations-admin__columns {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.locations-admin__column {
    background: var(--host-surface, #fff);
    border: 1px solid var(--host-border, #d8e0dc);
    border-radius: 16px;
    padding: 16px;
    min-height: 200px;
}

.locations-admin__column-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.locations-admin__column-header h2 {
    margin: 0;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--host-text-muted, #5c6b66);
}

.locations-admin__list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.locations-admin__item {
    display: flex;
    flex-direction: column;
    gap: 2px;
    padding: 10px 12px;
    border-radius: 10px;
    cursor: pointer;
}

.locations-admin__item:hover {
    background: var(--host-surface-alt, #f4f6f4);
}

.locations-admin__item--active {
    background: var(--host-accent-soft, #fde8d2);
    border: 1px solid var(--host-accent, #e08a2b);
}

.locations-admin__item--static {
    cursor: default;
}

.locations-admin__item-name {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
}

.locations-admin__item-meta {
    font-size: 12px;
    color: var(--host-text-muted, #5c6b66);
}

.locations-admin__hint {
    color: var(--host-text-muted, #5c6b66);
    font-size: 14px;
}

.host-form-grid__full {
    grid-column: 1 / -1;
}

.host-field__hint {
    margin: 4px 0 0;
    font-size: 12px;
    color: var(--host-text-muted, #5c6b66);
}

@media (max-width: 900px) {
    .locations-admin__columns {
        grid-template-columns: 1fr;
    }
}
</style>
