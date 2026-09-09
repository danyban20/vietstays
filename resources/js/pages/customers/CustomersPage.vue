<template>
    <div class="host-team-page">
        <div class="host-team-page__head">
            <div>
                <h1 class="host-page-title">{{ t('customers.title') }}</h1>
                <p class="host-page-subtitle">{{ t('customers.subtitle') }}</p>
            </div>
            <div class="host-customers-head__actions">
                <button type="button" class="host-customers-head__export" @click="exportList">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H7a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7z" /><polyline points="14 2 14 7 19 7" /><line x1="9.5" y1="12.5" x2="14.5" y2="17.5" /><line x1="14.5" y1="12.5" x2="9.5" y2="17.5" /></svg>
                    {{ t('customers.export') }}
                </button>
                <button type="button" class="host-btn host-btn--primary host-team-page__cta" @click="addModalOpen = true">
                    + {{ t('customers.newCustomer') }}
                </button>
            </div>
        </div>

        <div class="host-team-tabs">
            <button
                v-for="tab in tabs"
                :key="tab.key"
                type="button"
                class="host-team-tabs__btn"
                :class="{ 'host-team-tabs__btn--active': activeTab === tab.key }"
                @click="activeTab = tab.key"
            >
                {{ tab.label }} ({{ tab.count }})
            </button>
        </div>

        <div class="host-team-stats">
            <div v-for="stat in stats" :key="stat.label" class="host-team-stats__item">
                <span class="host-team-stats__dot" :style="{ background: stat.dot }" />
                <div>
                    <div class="host-team-stats__value">{{ stat.value }}</div>
                    <div class="host-team-stats__label">{{ stat.label }}</div>
                </div>
            </div>
            <div class="host-team-stats__spacer" />
            <div class="host-team-toolbar__search">
                <span class="host-team-toolbar__search-icon">⌕</span>
                <input
                    v-model="search"
                    type="search"
                    class="host-team-toolbar__input"
                    :placeholder="t('customers.searchPlaceholder')"
                />
            </div>
            <button
                type="button"
                class="host-team-toolbar__btn"
                :class="{ 'host-team-toolbar__btn--active': filterOpen || activeFilterCount > 0 }"
                @click="toggleFilter"
            >
                {{ t('customers.filter') }} ▾
                <span v-if="activeFilterCount" class="host-customers-filter__badge">{{ activeFilterCount }}</span>
            </button>
            <div class="host-customers-sort">
                <button type="button" class="host-team-toolbar__btn" @click="sortOpen = !sortOpen">
                    {{ t('customers.sorting') }} ▾
                </button>
                <div v-if="sortOpen" class="host-customers-sort__menu">
                    <button
                        v-for="option in sortOptions"
                        :key="option.key"
                        type="button"
                        class="host-customers-sort__option"
                        :class="{ 'host-customers-sort__option--active': sortKey === option.key }"
                        @click="selectSort(option.key)"
                    >
                        <span>{{ option.label }}</span>
                        <span v-if="sortKey === option.key">✓</span>
                    </button>
                </div>
            </div>
        </div>

        <div v-if="filterOpen" class="host-customers-filter">
            <div class="host-customers-filter__row">
                <span class="host-customers-filter__label">{{ t('customers.country') }}</span>
                <select v-model="draftFilters.country" class="host-customers-filter__select">
                    <option value="">{{ t('customers.allCountries') }}</option>
                    <option v-for="country in countryOptions" :key="country" :value="country">{{ country }}</option>
                </select>
            </div>
            <div class="host-customers-filter__row">
                <span class="host-customers-filter__label">{{ t('customers.segment') }}</span>
                <button
                    v-for="option in segmentOptions"
                    :key="option.value || 'all'"
                    type="button"
                    class="host-customers-filter__chip"
                    :class="{ 'host-customers-filter__chip--active': draftFilters.segment === option.value }"
                    @click="draftFilters.segment = option.value"
                >
                    {{ option.label }}
                </button>
            </div>
            <div class="host-customers-filter__row">
                <span class="host-customers-filter__label">{{ t('customers.stays') }}</span>
                <button
                    v-for="option in minStayOptions"
                    :key="option.value || 'all'"
                    type="button"
                    class="host-customers-filter__chip"
                    :class="{ 'host-customers-filter__chip--active': draftFilters.minStays === option.value }"
                    @click="draftFilters.minStays = option.value"
                >
                    {{ option.label }}
                </button>
                <label class="host-customers-filter__toggle">
                    <input v-model="draftFilters.upcomingOnly" type="checkbox" />
                    <span>{{ t('customers.upcomingOnly') }}</span>
                </label>
            </div>
            <div class="host-customers-filter__footer">
                <p class="host-customers-filter__hint">{{ t('customers.segmentHint') }}</p>
                <div class="host-customers-filter__actions">
                    <button type="button" class="host-btn host-btn--ghost" @click="resetFilters">{{ t('customers.resetFilters') }}</button>
                    <button type="button" class="host-btn host-btn--primary" @click="applyFilters">{{ t('customers.applyFilters') }}</button>
                </div>
            </div>
        </div>

        <p v-if="loading" class="host-customers-table__empty">{{ t('customers.loading') }}</p>
        <p v-else-if="error" class="host-customers-table__empty">{{ error }}</p>

        <div v-else class="host-customers-table">
            <div class="host-customers-table__head">
                <input
                    type="checkbox"
                    class="host-customers-table__checkbox"
                    :checked="allSelected"
                    @change="toggleSelectAll"
                />
                <span>{{ t('customers.colCustomer') }}</span>
                <span>{{ t('customers.colStays') }}</span>
                <span>{{ t('customers.colNights') }}</span>
                <span>{{ t('customers.colNextStay') }}</span>
                <span>{{ t('customers.colStatus') }}</span>
            </div>

            <div v-if="selectedCount" class="host-customers-bulk">
                <select v-model="bulkAction" class="host-customers-bulk__select">
                    <option value="">{{ t('customers.chooseAction') }}</option>
                    <option value="flag">{{ t('customers.actionFlag') }}</option>
                    <option value="unflag">{{ t('customers.actionUnflag') }}</option>
                    <option value="export">{{ t('customers.actionExport') }}</option>
                </select>
                <button type="button" class="host-btn host-btn--primary host-customers-bulk__apply" @click="applyBulk">
                    {{ t('customers.runAction') }}
                </button>
                <span class="host-customers-bulk__count">{{ t('customers.selectedCount', { count: selectedCount }) }}</span>
                <button type="button" class="host-customers-bulk__clear" @click="clearSelection">{{ t('customers.cancelSelection') }}</button>
            </div>

            <button
                v-for="row in visibleRows"
                :key="row.id"
                type="button"
                class="host-customers-table__row"
                :class="{
                    'host-customers-table__row--important': row.important,
                    'host-customers-table__row--active': row.nextActive,
                }"
                @click="openCustomer(row.id)"
            >
                <input
                    type="checkbox"
                    class="host-customers-table__checkbox"
                    :checked="selected.has(row.id)"
                    @click.stop
                    @change="toggleSelect(row.id)"
                />
                <div class="host-customers-table__customer">
                    <span v-if="row.flagEmoji" class="host-customers-table__flag">{{ row.flagEmoji }}</span>
                    <span v-else class="host-customers-table__avatar" :style="{ background: row.bg }">{{ row.initials }}</span>
                    <div class="host-customers-table__customer-body">
                        <div class="host-customers-table__name-row">
                            <span
                                class="host-customers-table__flag-btn"
                                :class="{ 'host-customers-table__flag-btn--on': row.important }"
                                title="Important"
                                @click.stop="toggleImportant(row.id)"
                            >⚑</span>
                            <span class="host-customers-table__name">{{ row.name }}</span>
                            <span
                                v-if="row.showSegment"
                                class="host-customers-table__segment"
                                :style="{ background: row.segmentStyle.bg, color: row.segmentStyle.color }"
                            >
                                {{ row.segment }}
                            </span>
                        </div>
                        <div class="host-customers-table__email">{{ row.email }}</div>
                    </div>
                </div>
                <div class="host-customers-table__center">
                    <strong>{{ row.stays }}</strong>
                    <span>{{ t('customers.longestStay', { nights: row.longestNights }) }}</span>
                </div>
                <div class="host-customers-table__center">
                    <strong>{{ row.nights }}</strong>
                    <span>{{ t('customers.avgStay', { nights: row.avgNights }) }}</span>
                </div>
                <div class="host-customers-table__next">
                    <strong :class="{ 'host-customers-table__next--active': row.nextActive, 'host-customers-table__next--soon': row.nextSoon }">
                        {{ row.nextLabel }}
                    </strong>
                    <span v-if="row.nextAptCode" class="host-customers-table__code">{{ row.nextAptCode }}</span>
                    <span class="host-customers-table__apt">{{ row.nextApt }}</span>
                </div>
                <div class="host-customers-table__status">
                    <span
                        v-if="row.statusStyle.bg"
                        class="host-customers-table__status-pill"
                        :style="{ background: row.statusStyle.bg, color: row.statusStyle.color }"
                    >
                        {{ row.statusStyle.label }}
                    </span>
                    <span v-else class="host-customers-table__status-muted">{{ row.statusStyle.label }}</span>
                </div>
            </button>

            <div v-if="visibleRows.length === 0" class="host-customers-table__empty">
                {{ t('customers.noMatches') }}
            </div>
        </div>

        <AddCustomerModal
            :open="addModalOpen"
            @close="addModalOpen = false"
            @created="onCustomerCreated"
        />
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import apiClient from '@/api/client';
import AddCustomerModal from '@/components/modals/AddCustomerModal.vue';
import { withAppBase } from '@/utils/app-base';
import {
    CUSTOMER_TABS,
    MIN_STAY_OPTIONS,
    SEGMENT_OPTIONS,
    SORT_OPTIONS,
    formatVnd,
    segmentStyle,
    statusStyle,
    countryFlagEmoji,
} from '@/data/customers-content.js';

const { t } = useI18n();
const router = useRouter();

const customers = ref([]);
const loading = ref(true);
const error = ref('');
const addModalOpen = ref(false);
const activeTab = ref('all');
const search = ref('');
const sortKey = ref('stays');
const sortOpen = ref(false);
const filterOpen = ref(false);
const bulkAction = ref('');
const selected = ref(new Set());
const important = ref(new Set());

const filters = reactive({
    country: '',
    segment: '',
    minStays: '',
    upcomingOnly: false,
});

const draftFilters = reactive({
    country: '',
    segment: '',
    minStays: '',
    upcomingOnly: false,
});

const sortOptions = computed(() =>
    SORT_OPTIONS.map((option) => ({
        key: option.key,
        label: t(option.labelKey),
    })),
);

const segmentOptions = computed(() =>
    SEGMENT_OPTIONS.map((option) => ({
        value: option.value,
        label: t(option.labelKey),
    })),
);

const minStayOptions = computed(() =>
    MIN_STAY_OPTIONS.map((option) => ({
        value: option.value,
        label: t(option.labelKey),
    })),
);

const tabs = computed(() =>
    CUSTOMER_TABS.map((tab) => ({
        key: tab.key,
        label: t(tab.labelKey),
        count: tab.filter ? customers.value.filter(tab.filter).length : customers.value.length,
    })),
);

const countryOptions = computed(() =>
    [...new Set(customers.value.map((customer) => customer.country))].sort(),
);

const activeFilterCount = computed(() =>
    [filters.country, filters.segment, filters.minStays, filters.upcomingOnly ? '1' : ''].filter(Boolean).length,
);

const filteredCustomers = computed(() => {
    const tab = CUSTOMER_TABS.find((entry) => entry.key === activeTab.value);
    const query = search.value.trim().toLowerCase();

    return customers.value
        .filter((customer) => !tab?.filter || tab.filter(customer))
        .filter((customer) => {
            if (!query) {
                return true;
            }

            return `${customer.name} ${customer.country} ${customer.email}`.toLowerCase().includes(query);
        })
        .filter((customer) => !filters.country || customer.country === filters.country)
        .filter((customer) => !filters.segment || customer.segment === filters.segment)
        .filter((customer) => !filters.minStays || customer.stays >= Number(filters.minStays))
        .filter((customer) => !filters.upcomingOnly || customer.hasUpcoming)
        .sort((left, right) => {
            const importantRank = (customer) => (important.value.has(customer.id) ? 0 : 1);
            const statusRank = (customer) => {
                if (customer.status === 'Staying now') {
                    return 0;
                }

                if (customer.status === 'Upcoming') {
                    return 1;
                }

                return 2;
            };

            const base = importantRank(left) - importantRank(right)
                || statusRank(left) - statusRank(right);

            if (base !== 0) {
                return base;
            }

            switch (sortKey.value) {
            case 'name':
                return left.name.localeCompare(right.name);
            case 'nights':
                return right.nights - left.nights || left.name.localeCompare(right.name);
            case 'value':
                return right.revenue - left.revenue || left.name.localeCompare(right.name);
            case 'next':
                return (left.nextLabel === '—') - (right.nextLabel === '—') || left.name.localeCompare(right.name);
            case 'rating':
                return (right.rating ?? 0) - (left.rating ?? 0) || left.name.localeCompare(right.name);
            default:
                return right.stays - left.stays || left.name.localeCompare(right.name);
            }
        });
});

const visibleRows = computed(() =>
    filteredCustomers.value.map((customer) => ({
        ...customer,
        flagEmoji: countryFlagEmoji(customer.flag),
        segmentStyle: segmentStyle(customer.segment),
        statusStyle: statusStyle(customer.status),
        ratingLabel: customer.rating != null ? Number(customer.rating).toFixed(1) : '—',
        reviewCount: (customer.reviews ?? []).length,
        showSegment: !['Temporary', 'Customer'].includes(customer.segment),
        important: important.value.has(customer.id),
    })),
);

const stats = computed(() => {
    const scope = activeFilterCount.value || search.value || activeTab.value !== 'all' ? ` (${t('customers.filtered')})` : '';
    const returning = filteredCustomers.value.filter((customer) => customer.stays >= 2).length;

    return [
        {
            value: formatVnd(filteredCustomers.value.reduce((sum, customer) => sum + customer.revenue, 0)),
            label: `${t('customers.statValue')}${scope}`,
            dot: '#b5651d',
        },
        {
            value: filteredCustomers.value.filter((customer) => customer.segment === 'New customer').length,
            label: `${t('customers.statNew')}${scope}`,
            dot: '#1f7a44',
        },
        {
            value: returning,
            label: `${t('customers.statReturning')}${scope}`,
            dot: '#12352b',
        },
    ];
});

const selectedCount = computed(() => selected.value.size);
const allSelected = computed(() =>
    visibleRows.value.length > 0 && visibleRows.value.every((row) => selected.value.has(row.id)),
);

function toggleFilter() {
    filterOpen.value = !filterOpen.value;
    sortOpen.value = false;

    if (filterOpen.value) {
        Object.assign(draftFilters, filters);
    }
}

function applyFilters() {
    Object.assign(filters, draftFilters);
    filterOpen.value = false;
}

function resetFilters() {
    draftFilters.country = '';
    draftFilters.segment = '';
    draftFilters.minStays = '';
    draftFilters.upcomingOnly = false;
    Object.assign(filters, draftFilters);
    filterOpen.value = false;
}

function selectSort(key) {
    sortKey.value = key;
    sortOpen.value = false;
}

function toggleSelect(id) {
    const next = new Set(selected.value);

    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }

    selected.value = next;
}

function toggleSelectAll() {
    if (allSelected.value) {
        selected.value = new Set();
        return;
    }

    selected.value = new Set(visibleRows.value.map((row) => row.id));
}

function clearSelection() {
    selected.value = new Set();
    bulkAction.value = '';
}

function toggleImportant(id) {
    const next = new Set(important.value);

    if (next.has(id)) {
        next.delete(id);
    } else {
        next.add(id);
    }

    important.value = next;
}

function applyBulk() {
    if (!bulkAction.value || !selectedCount.value) {
        return;
    }

    const next = new Set(important.value);

    if (bulkAction.value === 'flag') {
        selected.value.forEach((id) => next.add(id));
        important.value = next;
    }

    if (bulkAction.value === 'unflag') {
        selected.value.forEach((id) => next.delete(id));
        important.value = next;
    }

    clearSelection();
}

function openCustomer(id) {
    router.push({ name: 'customer-detail', params: { id } });
}

function exportList() {
    window.location.href = withAppBase('/api/customers/export');
}

async function loadCustomers() {
    loading.value = true;
    error.value = '';

    try {
        const response = await apiClient.get('/customers');
        customers.value = response.data ?? [];
    } catch (err) {
        error.value = err.message || t('customers.loadFailed');
        customers.value = [];
    } finally {
        loading.value = false;
    }
}

function onCustomerCreated() {
    loadCustomers();
}

onMounted(loadCustomers);
</script>
