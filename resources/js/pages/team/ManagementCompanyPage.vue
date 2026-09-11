<template>
    <div class="host-mgmt-page">
        <div class="host-mgmt-page__head">
            <div>
                <h1 class="host-page-title">{{ pageTitle }}</h1>
                <p class="host-page-subtitle">{{ pageSubtitle }}</p>
            </div>
            <button
                v-if="mode === 'empty'"
                type="button"
                class="host-btn host-btn--primary"
                @click="startSetup"
            >
                {{ t('mgmtCompany.createCta') }}
            </button>
            <button
                v-else-if="mode === 'setup'"
                type="button"
                class="host-btn host-btn--ghost"
                @click="cancelSetup"
            >
                {{ t('mgmtCompany.cancelSetup') }}
            </button>
        </div>

        <p v-if="loading" class="host-team-empty">{{ t('common.loading') }}</p>

        <ManagementCompanyWizard
            v-else-if="mode === 'setup'"
            :saving="saving"
            @cancel="cancelSetup"
            @complete="onComplete"
        />

        <ManagementCompanyActive
            v-else-if="mode === 'active'"
            :company="company"
        />

        <template v-else>
            <div class="host-mgmt-hero">
                <div class="host-mgmt-hero__top">
                    <div class="host-mgmt-hero__icon-wrap">
                        <SidebarIcon name="management" class="host-mgmt-hero__icon" />
                    </div>
                    <div class="host-mgmt-hero__copy">
                        <h2 class="host-mgmt-hero__title">{{ t('mgmtCompany.emptyTitle') }}</h2>
                        <p class="host-mgmt-hero__text">{{ t('mgmtCompany.emptyText') }}</p>
                    </div>
                    <button type="button" class="host-mgmt-hero__how-btn">
                        {{ t('mgmtCompany.howItWorks') }}
                    </button>
                </div>

                <div class="host-mgmt-benefits">
                    <article v-for="benefit in benefits" :key="benefit.key" class="host-mgmt-benefit">
                        <div class="host-mgmt-benefit__icon" v-html="benefit.icon" />
                        <div>
                            <h3 class="host-mgmt-benefit__title">{{ t(benefit.titleKey) }}</h3>
                            <p class="host-mgmt-benefit__text">{{ t(benefit.textKey) }}</p>
                        </div>
                    </article>
                </div>

                <div class="host-mgmt-hero__footer">
                    <button type="button" class="host-btn host-btn--primary" @click="startSetup">
                        {{ t('mgmtCompany.createCta') }}
                    </button>
                    <p class="host-mgmt-hero__note">{{ t('mgmtCompany.createNote') }}</p>
                </div>
            </div>

            <div class="host-mgmt-grid">
                <article class="host-mgmt-card">
                    <h2 class="host-mgmt-card__title">{{ t('mgmtCompany.cohostTitle') }}</h2>
                    <p class="host-mgmt-card__text">{{ t('mgmtCompany.cohostText') }}</p>
                    <button type="button" class="host-mgmt-card__outline-btn">
                        {{ t('mgmtCompany.cohostCta') }}
                    </button>
                </article>

                <article class="host-mgmt-card host-mgmt-card--invite">
                    <div class="host-mgmt-invite__icon">🔔</div>
                    <div class="host-mgmt-invite__body">
                        <div class="host-mgmt-invite__title-row">
                            <h2 class="host-mgmt-card__title host-mgmt-invite__title">
                                {{ t('mgmtCompany.inviteTitle') }}
                            </h2>
                            <span class="host-mgmt-invite__dot" />
                        </div>
                        <p class="host-mgmt-invite__meta">{{ t('mgmtCompany.inviteMeta') }}</p>
                        <p class="host-mgmt-card__text">{{ t('mgmtCompany.inviteText', { count: apartmentCount }) }}</p>
                        <div class="host-mgmt-invite__actions">
                            <button type="button" class="host-btn host-btn--primary host-mgmt-invite__primary">
                                {{ t('mgmtCompany.viewInvite') }}
                            </button>
                            <button type="button" class="host-btn host-btn--ghost">
                                {{ t('mgmtCompany.declineInvite') }}
                            </button>
                        </div>
                    </div>
                </article>

                <article class="host-mgmt-card">
                    <h2 class="host-mgmt-card__title host-mgmt-card__title--muted">{{ t('mgmtCompany.portalTitle') }}</h2>
                    <p class="host-mgmt-card__text">{{ t('mgmtCompany.portalText') }}</p>
                    <button type="button" class="host-mgmt-card__outline-btn">
                        {{ t('mgmtCompany.portalCta') }}
                    </button>
                </article>
            </div>
        </template>
    </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import apiClient from '@/api/client';
import { useToast } from '@/composables/useToast';
import SidebarIcon from '@/components/SidebarIcon.vue';
import ManagementCompanyWizard from '@/components/management/ManagementCompanyWizard.vue';
import ManagementCompanyActive from '@/components/management/ManagementCompanyActive.vue';

const { t } = useI18n();
const toast = useToast();

const mode = ref('empty');
const company = ref(null);
const apartmentCount = ref(11);
const loading = ref(true);
const saving = ref(false);

const pageTitle = computed(() => {
    if (mode.value === 'setup') {
        return t('mgmtCompany.setupTitle');
    }

    if (mode.value === 'active' && company.value) {
        return company.value.form.name.trim() || t('mgmtCompany.defaultCompanyName');
    }

    return t('mgmtCompany.title');
});

const pageSubtitle = computed(() => {
    if (mode.value === 'setup') {
        return t('mgmtCompany.setupSubtitle');
    }

    if (mode.value === 'active' && company.value) {
        return t('mgmtCompany.activeSubtitle', {
            hosts: 3 + company.value.pendingInvites.length,
            apartments: company.value.companyApartmentCount,
        });
    }

    return t('mgmtCompany.subtitle');
});

const benefits = [
    {
        key: 'portfolio',
        titleKey: 'mgmtCompany.benefitPortfolioTitle',
        textKey: 'mgmtCompany.benefitPortfolioText',
        icon: '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#1f5b3f" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"></path><rect x="3" y="9" width="7" height="12"></rect><rect x="14" y="4" width="7" height="17"></rect><line x1="6" y1="13" x2="6.01" y2="13"></line><line x1="17" y1="9" x2="17.01" y2="9"></line></svg>',
    },
    {
        key: 'help',
        titleKey: 'mgmtCompany.benefitHelpTitle',
        textKey: 'mgmtCompany.benefitHelpText',
        icon: '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#1f5b3f" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M17 20v-1.5a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4V20"></path><circle cx="9.5" cy="7" r="3.5"></circle><path d="M17 4.2a3.5 3.5 0 0 1 0 6.6"></path><path d="M19.5 20v-1.5a4 4 0 0 0-2.5-3.7"></path></svg>',
    },
    {
        key: 'ops',
        titleKey: 'mgmtCompany.benefitOpsTitle',
        textKey: 'mgmtCompany.benefitOpsText',
        icon: '<svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="#1f5b3f" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="9" cy="6" rx="6" ry="2.6"></ellipse><path d="M3 6v5c0 1.4 2.7 2.6 6 2.6s6-1.2 6-2.6V6"></path><path d="M3 11v5c0 1.4 2.7 2.6 6 2.6 1 0 2-.1 2.8-.3"></path><circle cx="17" cy="16" r="4.4"></circle><path d="M17 14.1v3.8M18.2 15.1h-1.7a.9.9 0 0 0 0 1.8h1a.9.9 0 0 1 0 1.8h-1.7"></path></svg>',
    },
];

function startSetup() {
    mode.value = 'setup';
}

function cancelSetup() {
    mode.value = company.value ? 'active' : 'empty';
}

async function loadCompany() {
    loading.value = true;

    try {
        const response = await apiClient.get('/management-company');
        company.value = response.data ?? null;
        mode.value = company.value ? 'active' : 'empty';
    } catch {
        company.value = null;
        mode.value = 'empty';
    } finally {
        loading.value = false;
    }
}

async function onComplete(payload) {
    if (saving.value) {
        return;
    }

    saving.value = true;

    try {
        const response = await apiClient.post('/management-company', payload);
        company.value = response.data ?? payload;
        mode.value = 'active';
        toast.show(response.message || t('mgmtCompany.saved'));
    } catch (err) {
        toast.show(err.message || t('mgmtCompany.saveFailed'));
    } finally {
        saving.value = false;
    }
}

onMounted(loadCompany);
</script>
