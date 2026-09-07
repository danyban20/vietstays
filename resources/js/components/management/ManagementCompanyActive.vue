<template>
    <div class="host-mgmt-active">
        <div class="host-mgmt-active__kpis">
            <article v-for="kpi in kpis" :key="kpi.labelKey" class="host-mgmt-active__kpi">
                <div class="host-mgmt-active__kpi-label">{{ t(kpi.labelKey) }}</div>
                <div class="host-mgmt-active__kpi-value">{{ kpi.value }}</div>
                <div class="host-mgmt-active__kpi-sub">{{ kpi.sub }}</div>
            </article>
        </div>

        <div class="host-mgmt-active__grid">
            <section class="host-mgmt-wizard__panel">
                <h2 class="host-mgmt-wizard__panel-title">{{ t('mgmtCompany.activeMembersTitle') }}</h2>
                <p class="host-mgmt-wizard__panel-sub">{{ t('mgmtCompany.activeMembersSub') }}</p>
                <div class="host-mgmt-member-list host-mgmt-member-list--active">
                    <div v-for="member in members" :key="member.name" class="host-mgmt-member-list__row host-mgmt-member-list__row--active">
                        <div class="host-mgmt-member-list__avatar">{{ member.initials }}</div>
                        <div class="host-mgmt-member-list__body">
                            <div class="host-mgmt-member-list__name">
                                {{ member.name }}
                                <span v-if="member.isYou" class="host-mgmt-member-list__you">{{ t('mgmtCompany.youBadge') }}</span>
                            </div>
                            <div class="host-mgmt-member-list__meta">{{ member.role }}</div>
                        </div>
                        <div class="host-mgmt-member-list__count">
                            <strong>{{ member.apartments }}</strong>
                            <span>{{ t('mgmtCompany.inCompany') }}</span>
                        </div>
                        <div class="host-mgmt-member-list__share">
                            <strong>{{ member.share }}%</strong>
                            <span>{{ member.shareBasis }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="host-mgmt-wizard__panel">
                <h2 class="host-mgmt-wizard__panel-title">{{ t('mgmtCompany.activeModelTitle') }}</h2>
                <p class="host-mgmt-model-card__title">{{ modelTitle }}</p>
                <p class="host-mgmt-model-card__body">{{ modelBody }}</p>
                <div class="host-mgmt-active__legal">
                    <span class="host-mgmt-active__legal-badge" :class="legalRegistered ? 'host-mgmt-active__legal-badge--reg' : 'host-mgmt-active__legal-badge--agr'">
                        {{ legalRegistered ? t('mgmtCompany.legalRegisteredBadge') : t('mgmtCompany.legalAgreementBadge') }}
                    </span>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import { REVENUE_MODELS, SETUP_MEMBERS } from '@/data/management-company-content.js';

const props = defineProps({
    company: {
        type: Object,
        required: true,
    },
});

const { t } = useI18n();

const modelDef = computed(() => REVENUE_MODELS.find((model) => model.id === props.company.revenueModel) ?? REVENUE_MODELS[0]);

const modelTitle = computed(() => t(modelDef.value.titleKey));
const modelBody = computed(() => t(modelDef.value.bodyKey));
const legalRegistered = computed(() => props.company.legalRegistered);

const kpis = computed(() => [
    {
        labelKey: 'mgmtCompany.kpiApartments',
        value: String(props.company.companyApartmentCount),
        sub: t('mgmtCompany.kpiApartmentsSub', { mine: props.company.includedCount }),
    },
    {
        labelKey: 'mgmtCompany.kpiRevenue',
        value: '412.5M',
        sub: t('mgmtCompany.kpiRevenueSub'),
    },
    {
        labelKey: 'mgmtCompany.kpiHosts',
        value: String(3 + props.company.pendingInvites.length),
        sub: t('mgmtCompany.kpiHostsSub'),
    },
]);

const members = computed(() => {
    const shareKeys = ['me', 'lars', 'hoa'];

    return SETUP_MEMBERS.map((member, index) => ({
        initials: member.initials,
        name: member.name,
        isYou: member.isYou,
        role: member.isYou ? t('mgmtCompany.roleCreator') : t('mgmtCompany.roleHost'),
        apartments: member.isYou ? props.company.includedCount : member.apartments,
        share: props.company.shares[shareKeys[index]],
        shareBasis: t('mgmtCompany.shareBasis'),
    }));
});
</script>
