<template>
    <div class="host-mgmt-wizard">
        <div class="host-mgmt-wizard__stepper">
            <template v-for="(wizardStep, index) in WIZARD_STEPS" :key="wizardStep.id">
                <button
                    type="button"
                    class="host-mgmt-wizard__step"
                    :class="{ 'host-mgmt-wizard__step--active': step === wizardStep.id, 'host-mgmt-wizard__step--done': step > wizardStep.id }"
                    @click="goToStep(wizardStep.id)"
                >
                    <span class="host-mgmt-wizard__step-dot">{{ step > wizardStep.id ? '✓' : wizardStep.id }}</span>
                    <span class="host-mgmt-wizard__step-label">{{ t(wizardStep.labelKey) }}</span>
                </button>
                <div
                    v-if="index < WIZARD_STEPS.length - 1"
                    class="host-mgmt-wizard__step-line"
                    :class="{ 'host-mgmt-wizard__step-line--done': step > wizardStep.id }"
                />
            </template>
        </div>

        <!-- Step 1 -->
        <div v-if="step === 1" class="host-mgmt-wizard__panel">
            <h2 class="host-mgmt-wizard__panel-title">{{ t('mgmtCompany.stepNameForm') }}</h2>
            <div class="host-mgmt-wizard__form-grid">
                <label class="host-mgmt-field">
                    <span class="host-mgmt-field__label">{{ t('mgmtCompany.companyNameLabel') }}</span>
                    <input v-model="form.name" type="text" class="host-mgmt-field__input" :placeholder="t('mgmtCompany.companyNamePlaceholder')" />
                    <span class="host-mgmt-field__hint">{{ t('mgmtCompany.companyNameHint') }}</span>
                </label>
                <label class="host-mgmt-field">
                    <span class="host-mgmt-field__label">
                        {{ t('mgmtCompany.taglineLabel') }}
                        <span class="host-mgmt-field__optional">{{ t('mgmtCompany.optional') }}</span>
                    </span>
                    <input v-model="form.tagline" type="text" class="host-mgmt-field__input" :placeholder="t('mgmtCompany.taglinePlaceholder')" />
                    <span class="host-mgmt-field__hint">{{ t('mgmtCompany.taglineHint') }}</span>
                </label>
            </div>
            <div class="host-mgmt-field__label host-mgmt-field__label--section">{{ t('mgmtCompany.legalFormLabel') }}</div>
            <div class="host-mgmt-wizard__legal-grid">
                <button
                    v-for="option in LEGAL_OPTIONS"
                    :key="String(option.id)"
                    type="button"
                    class="host-mgmt-legal-card"
                    :class="{ 'host-mgmt-legal-card--selected': legalRegistered === option.id }"
                    @click="legalRegistered = option.id"
                >
                    <div class="host-mgmt-legal-card__head">
                        <span class="host-mgmt-radio" :class="{ 'host-mgmt-radio--on': legalRegistered === option.id }" />
                        <span class="host-mgmt-legal-card__title">{{ t(option.titleKey) }}</span>
                    </div>
                    <p class="host-mgmt-legal-card__body">{{ t(option.bodyKey) }}</p>
                    <ul class="host-mgmt-legal-card__points">
                        <li v-for="(point, pointIndex) in option.points" :key="pointIndex">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#1f7a44" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12" /></svg>
                            <span><strong>{{ t(point.strongKey) }}</strong> {{ t(point.restKey) }}</span>
                        </li>
                    </ul>
                </button>
            </div>
        </div>

        <!-- Step 2 -->
        <div v-else-if="step === 2" class="host-mgmt-wizard__panel">
            <h2 class="host-mgmt-wizard__panel-title">{{ t('mgmtCompany.selectApartmentsTitle') }}</h2>
            <p class="host-mgmt-wizard__panel-sub">{{ t('mgmtCompany.selectApartmentsSub', { total: MY_APARTMENTS.length }) }}</p>
            <div class="host-mgmt-wizard__privacy">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#1f5b3f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="11" width="14" height="10" rx="2" /><path d="M8 11V8a4 4 0 0 1 8 0v3" /></svg>
                <span>{{ t('mgmtCompany.privacyNote') }}</span>
            </div>
            <div class="host-mgmt-wizard__stats">
                <div v-for="stat in apartmentStats" :key="stat.labelKey" class="host-mgmt-wizard__stat">
                    <div class="host-mgmt-wizard__stat-label">{{ t(stat.labelKey) }}</div>
                    <div class="host-mgmt-wizard__stat-value">{{ stat.value }}</div>
                    <div class="host-mgmt-wizard__stat-sub">{{ t(stat.subKey) }}</div>
                </div>
            </div>
            <div class="host-mgmt-apt-table">
                <div class="host-mgmt-apt-table__toolbar">
                    <span class="host-mgmt-apt-table__toolbar-label">{{ t('mgmtCompany.filterLabel') }}</span>
                    <button
                        v-for="filter in segmentFilters"
                        :key="filter.id"
                        type="button"
                        class="host-mgmt-apt-table__filter"
                        :class="{ 'host-mgmt-apt-table__filter--active': segmentFilter === filter.id }"
                        @click="segmentFilter = filter.id"
                    >
                        {{ filter.label }}
                    </button>
                    <span class="host-mgmt-apt-table__summary">{{ listSummary }}</span>
                    <button type="button" class="host-mgmt-apt-table__link" @click="selectAll">{{ t('mgmtCompany.selectAll') }}</button>
                    <button type="button" class="host-mgmt-apt-table__link host-mgmt-apt-table__link--muted" @click="selectNone">{{ t('mgmtCompany.selectNone') }}</button>
                </div>
                <div class="host-mgmt-apt-table__head">
                    <span />
                    <span>{{ t('mgmtCompany.colCode') }}</span>
                    <span>{{ t('mgmtCompany.colName') }}</span>
                    <span>{{ t('mgmtCompany.colBeds') }}</span>
                    <span>{{ t('mgmtCompany.colSqm') }}</span>
                    <span>{{ t('mgmtCompany.colDistrict') }}</span>
                    <span>{{ t('mgmtCompany.colSegment') }}</span>
                    <span>{{ t('mgmtCompany.colRate') }}</span>
                </div>
                <button
                    v-for="apt in filteredApartments"
                    :key="apt.code"
                    type="button"
                    class="host-mgmt-apt-table__row"
                    :class="{ 'host-mgmt-apt-table__row--included': isIncluded(apt.code) }"
                    @click="toggleApartment(apt.code)"
                >
                    <span class="host-mgmt-apt-table__check" :class="{ 'host-mgmt-apt-table__check--on': isIncluded(apt.code) }">
                        <span v-if="isIncluded(apt.code)">✓</span>
                    </span>
                    <span class="host-mgmt-apt-table__code">{{ apt.code }}</span>
                    <span class="host-mgmt-apt-table__name">{{ apt.name }}</span>
                    <span>{{ apt.beds }}</span>
                    <span class="host-mgmt-apt-table__right">{{ apt.sqm }}</span>
                    <span>{{ apt.district }}</span>
                    <span class="host-mgmt-apt-table__seg" :style="segmentStyle(apt.segment)">{{ apt.segment }}</span>
                    <span class="host-mgmt-apt-table__rate">{{ apt.rate }}</span>
                </button>
            </div>
        </div>

        <!-- Step 3 -->
        <div v-else-if="step === 3" class="host-mgmt-wizard__panel">
            <h2 class="host-mgmt-wizard__panel-title">{{ t('mgmtCompany.stepInviteHosts') }}</h2>
            <p class="host-mgmt-wizard__panel-sub">{{ t('mgmtCompany.inviteHostsSub') }}</p>
            <div class="host-mgmt-invite-search">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#8a9187" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" /></svg>
                <input
                    v-model="inviteQuery"
                    type="text"
                    class="host-mgmt-invite-search__input"
                    :placeholder="t('mgmtCompany.inviteSearchPlaceholder')"
                    @focus="searchOpen = true"
                    @blur="onSearchBlur"
                />
                <div v-if="searchOpen && inviteQuery.trim()" class="host-mgmt-invite-search__dropdown">
                    <div v-for="host in searchResults" :key="host.id" class="host-mgmt-invite-search__result">
                        <div class="host-mgmt-invite-search__avatar">{{ host.initials }}</div>
                        <div class="host-mgmt-invite-search__meta">
                            <div class="host-mgmt-invite-search__name">
                                {{ host.name }}
                                <span v-if="host.verified" class="host-mgmt-invite-search__verified">{{ t('mgmtCompany.idVerified') }}</span>
                            </div>
                            <div class="host-mgmt-invite-search__sub">{{ host.id }} · {{ host.city }}</div>
                        </div>
                        <button
                            type="button"
                            class="host-mgmt-invite-search__btn"
                            :class="{ 'host-mgmt-invite-search__btn--sent': isPending(host.id) }"
                            @mousedown.prevent="toggleInvite(host)"
                        >
                            {{ isPending(host.id) ? t('mgmtCompany.invited') : t('mgmtCompany.invite') }}
                        </button>
                    </div>
                    <p v-if="searchResults.length === 0" class="host-mgmt-invite-search__empty">{{ t('mgmtCompany.noHostMatches') }}</p>
                    <p class="host-mgmt-invite-search__footer">{{ t('mgmtCompany.invitePrivacyFooter') }}</p>
                </div>
            </div>
            <div class="host-mgmt-member-list">
                <div v-for="member in memberRows" :key="member.name" class="host-mgmt-member-list__row">
                    <div class="host-mgmt-member-list__avatar">{{ member.initials }}</div>
                    <div class="host-mgmt-member-list__body">
                        <div class="host-mgmt-member-list__name">
                            {{ member.name }}
                            <span v-if="member.isYou" class="host-mgmt-member-list__you">{{ t('mgmtCompany.youBadge') }}</span>
                        </div>
                        <div class="host-mgmt-member-list__meta">{{ member.meta }}</div>
                    </div>
                    <span class="host-mgmt-member-list__apt">{{ member.aptLabel }}</span>
                    <span class="host-mgmt-member-list__status host-mgmt-member-list__status--ok">{{ member.status }}</span>
                </div>
                <div v-for="pending in pendingRows" :key="pending.id" class="host-mgmt-member-list__row host-mgmt-member-list__row--pending">
                    <div class="host-mgmt-member-list__avatar">{{ pending.initials }}</div>
                    <div class="host-mgmt-member-list__body">
                        <div class="host-mgmt-member-list__name">{{ pending.name }}</div>
                        <div class="host-mgmt-member-list__meta">{{ pending.meta }}</div>
                    </div>
                    <span class="host-mgmt-member-list__hidden">{{ t('mgmtCompany.hiddenUntilAccept') }}</span>
                    <span class="host-mgmt-member-list__status host-mgmt-member-list__status--wait">{{ t('mgmtCompany.awaitingReply') }}</span>
                </div>
            </div>
        </div>

        <!-- Step 4 -->
        <div v-else class="host-mgmt-wizard__panel">
            <h2 class="host-mgmt-wizard__panel-title">{{ t('mgmtCompany.revenueTitle') }}</h2>
            <p class="host-mgmt-wizard__panel-sub">{{ t('mgmtCompany.revenueSub') }}</p>
            <div class="host-mgmt-model-list">
                <button
                    v-for="model in REVENUE_MODELS"
                    :key="model.id"
                    type="button"
                    class="host-mgmt-model-card"
                    :class="{ 'host-mgmt-model-card--selected': revenueModel === model.id }"
                    @click="revenueModel = model.id"
                >
                    <span class="host-mgmt-radio" :class="{ 'host-mgmt-radio--on': revenueModel === model.id }" />
                    <div>
                        <div class="host-mgmt-model-card__head">
                            <span class="host-mgmt-model-card__title">{{ t(model.titleKey) }}</span>
                            <span class="host-mgmt-model-card__tag" :style="{ background: model.tagBg, color: model.tagColor }">{{ t(model.tagKey) }}</span>
                        </div>
                        <p class="host-mgmt-model-card__body">{{ t(model.bodyKey) }}</p>
                    </div>
                </button>
            </div>
            <div class="host-mgmt-split-table">
                <div class="host-mgmt-split-table__head">
                    <span>{{ t('mgmtCompany.colHost') }}</span>
                    <span>{{ t('mgmtCompany.colBasis') }}</span>
                    <span>{{ t('mgmtCompany.colHostSince') }}</span>
                    <span>{{ t('mgmtCompany.colRating') }}</span>
                    <span>{{ t('mgmtCompany.colOwnership') }}</span>
                </div>
                <div v-for="row in splitRows" :key="row.key" class="host-mgmt-split-table__row">
                    <span class="host-mgmt-split-table__name">{{ row.name }}</span>
                    <span class="host-mgmt-split-table__muted">{{ row.basis }}</span>
                    <span>{{ row.since }}</span>
                    <span>{{ row.rating }}</span>
                    <span class="host-mgmt-split-table__pct">
                        <input
                            type="number"
                            min="0"
                            max="100"
                            :value="shares[row.key]"
                            @input="updateShare(row.key, $event.target.value)"
                        />
                        <span>%</span>
                    </span>
                </div>
                <div v-for="pending in pendingRows" :key="'split-' + pending.id" class="host-mgmt-split-table__row host-mgmt-split-table__row--pending">
                    <span class="host-mgmt-split-table__muted">{{ pending.name }}</span>
                    <span class="host-mgmt-split-table__muted">{{ t('mgmtCompany.awaitingApproval') }}</span>
                    <span class="host-mgmt-split-table__muted">—</span>
                    <span class="host-mgmt-split-table__muted">—</span>
                    <span class="host-mgmt-split-table__muted host-mgmt-split-table__right">—</span>
                </div>
                <div class="host-mgmt-split-table__footer">
                    <span class="host-mgmt-split-table__suggest">{{ suggestedLabel }}</span>
                    <button type="button" class="host-mgmt-apt-table__link" @click="useSuggested">{{ t('mgmtCompany.useSuggested') }}</button>
                    <span class="host-mgmt-split-table__total" :class="{ 'host-mgmt-split-table__total--ok': shareTotal === 100 }">{{ shareTotal }} %</span>
                </div>
                <p class="host-mgmt-split-table__note">
                    {{ t('mgmtCompany.splitSetupNote') }}
                    <strong :class="{ 'host-mgmt-split-table__total--ok': shareTotal === 100, 'host-mgmt-split-table__total--bad': shareTotal !== 100 }">{{ shareTotalNote }}</strong>
                </p>
            </div>
        </div>

        <div class="host-mgmt-wizard__footer">
            <button type="button" class="host-btn host-btn--ghost" @click="onBack">{{ t('mgmtCompany.back') }}</button>
            <span class="host-mgmt-wizard__hint">{{ stepHint }}</span>
            <button type="button" class="host-btn host-btn--primary" :disabled="!canProceed" @click="onNext">
                {{ step === 4 ? t('mgmtCompany.createCompany') : t('mgmtCompany.next') }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import {
    HOST_DIRECTORY,
    LEGAL_OPTIONS,
    MY_APARTMENTS,
    REVENUE_MODELS,
    SEGMENT_STYLES,
    SETUP_MEMBERS,
    WIZARD_STEPS,
    splitMemberRows,
    suggestedShares,
} from '@/data/management-company-content.js';

const emit = defineEmits(['cancel', 'complete']);

const { t } = useI18n();

const step = ref(1);
const form = ref({ name: 'Saigon Homes Management', tagline: '' });
const legalRegistered = ref(false);
const excluded = ref([]);
const segmentFilter = ref('all');
const inviteQuery = ref('');
const searchOpen = ref(false);
const pendingInvites = ref([]);
const revenueModel = ref('pool');
const customShares = ref(null);

const includedCount = computed(() => MY_APARTMENTS.length - excluded.value.length);

const shares = computed(() => customShares.value ?? suggestedShares(includedCount.value));

const shareTotal = computed(() => shares.value.me + shares.value.lars + shares.value.hoa);

const segmentFilters = computed(() => [
    { id: 'all', label: t('mgmtCompany.filterAll') },
    { id: 'Standard', label: 'Standard' },
    { id: 'Mid-range', label: 'Mid-range' },
    { id: 'Premium', label: 'Premium' },
]);

const filteredApartments = computed(() => {
    if (segmentFilter.value === 'all') {
        return MY_APARTMENTS;
    }

    return MY_APARTMENTS.filter((apt) => apt.segment === segmentFilter.value);
});

const listSummary = computed(() => {
    const visible = filteredApartments.value.length;
    const included = filteredApartments.value.filter((apt) => isIncluded(apt.code)).length;

    return t('mgmtCompany.listSummary', { included, visible });
});

const apartmentStats = computed(() => [
    { labelKey: 'mgmtCompany.statTotal', value: String(MY_APARTMENTS.length), subKey: 'mgmtCompany.statTotalSub' },
    { labelKey: 'mgmtCompany.statIncluded', value: String(includedCount.value), subKey: 'mgmtCompany.statIncludedSub' },
    { labelKey: 'mgmtCompany.statPrivate', value: String(excluded.value.length), subKey: 'mgmtCompany.statPrivateSub' },
]);

const searchResults = computed(() => {
    const query = inviteQuery.value.trim().toLowerCase();
    if (!query) {
        return [];
    }

    const memberNames = SETUP_MEMBERS.map((member) => member.name.toLowerCase());

    return HOST_DIRECTORY.filter((host) => {
        if (memberNames.includes(host.name.toLowerCase())) {
            return false;
        }

        return host.name.toLowerCase().includes(query)
            || host.id.toLowerCase().includes(query)
            || host.city.toLowerCase().includes(query);
    });
});

const pendingRows = computed(() =>
    pendingInvites.value.map((id) => {
        const host = HOST_DIRECTORY.find((entry) => entry.id === id);

        return {
            id,
            initials: host?.initials ?? '?',
            name: host?.name ?? id,
            meta: `${id} · ${host?.city ?? ''}`,
        };
    }),
);

const memberRows = computed(() =>
    SETUP_MEMBERS.map((member) => ({
        initials: member.initials,
        name: member.name,
        isYou: member.isYou,
        meta: member.isYou
            ? t('mgmtCompany.creatorMeta')
            : t('mgmtCompany.memberMeta', { date: member.name.includes('Lars') ? '8 Aug' : '9 Aug', count: member.apartments }),
        aptLabel: member.isYou
            ? t('mgmtCompany.apartmentCount', { count: includedCount.value })
            : t('mgmtCompany.apartmentCount', { count: member.apartments }),
        status: member.isYou ? t('mgmtCompany.statusCreator') : t('mgmtCompany.statusAccepted'),
    })),
);

const splitRows = computed(() => splitMemberRows(shares.value, includedCount.value));

const suggestedLabel = computed(() =>
    t('mgmtCompany.suggestedSplit', {
        me: suggestedShares(includedCount.value).me,
        lars: suggestedShares(includedCount.value).lars,
        hoa: suggestedShares(includedCount.value).hoa,
    }),
);

const shareTotalNote = computed(() =>
    shareTotal.value === 100
        ? t('mgmtCompany.shareTotalOk')
        : t('mgmtCompany.shareTotalBad', { total: shareTotal.value }),
);

const stepHint = computed(() => t(`mgmtCompany.stepHint${step.value}`));

const canProceed = computed(() => {
    if (step.value === 2 && includedCount.value === 0) {
        return false;
    }

    if (step.value === 4 && shareTotal.value !== 100) {
        return false;
    }

    return true;
});

function isIncluded(code) {
    return !excluded.value.includes(code);
}

function toggleApartment(code) {
    if (isIncluded(code)) {
        excluded.value = [...excluded.value, code];
    } else {
        excluded.value = excluded.value.filter((entry) => entry !== code);
    }
}

function selectAll() {
    const visibleCodes = new Set(filteredApartments.value.map((apt) => apt.code));
    excluded.value = excluded.value.filter((code) => !visibleCodes.has(code));
}

function selectNone() {
    const visibleCodes = filteredApartments.value.map((apt) => apt.code);
    excluded.value = [...new Set([...excluded.value, ...visibleCodes])];
}

function segmentStyle(segment) {
    const style = SEGMENT_STYLES[segment] ?? SEGMENT_STYLES.Standard;

    return { background: style.bg, color: style.color };
}

function isPending(id) {
    return pendingInvites.value.includes(id);
}

function toggleInvite(host) {
    if (isPending(host.id)) {
        pendingInvites.value = pendingInvites.value.filter((entry) => entry !== host.id);
        return;
    }

    pendingInvites.value = [...pendingInvites.value, host.id];
}

function onSearchBlur() {
    window.setTimeout(() => {
        searchOpen.value = false;
    }, 150);
}

function updateShare(key, value) {
    const parsed = Math.max(0, Math.min(100, Number(value) || 0));
    customShares.value = { ...shares.value, [key]: parsed };
}

function useSuggested() {
    customShares.value = null;
}

function goToStep(target) {
    if (target < step.value) {
        step.value = target;
    }
}

function onBack() {
    if (step.value === 1) {
        emit('cancel');
        return;
    }

    step.value -= 1;
}

function onNext() {
    if (!canProceed.value) {
        return;
    }

    if (step.value === 4) {
        emit('complete', {
            form: { ...form.value },
            legalRegistered: legalRegistered.value,
            excluded: [...excluded.value],
            pendingInvites: [...pendingInvites.value],
            revenueModel: revenueModel.value,
            shares: { ...shares.value },
            includedCount: includedCount.value,
            companyApartmentCount: includedCount.value + 10,
        });
        return;
    }

    step.value += 1;
}
</script>
