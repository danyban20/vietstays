export const SEGMENT_STYLES = {
    Standard: { bg: '#f0ecdf', color: '#5e6b62' },
    'Mid-range': { bg: '#e6ecf6', color: '#2f4f86' },
    Premium: { bg: '#f7ecdd', color: '#8a5a2b' },
};

export const WIZARD_STEPS = [
    { id: 1, labelKey: 'mgmtCompany.stepNameForm' },
    { id: 2, labelKey: 'mgmtCompany.stepApartments' },
    { id: 3, labelKey: 'mgmtCompany.stepInviteHosts' },
    { id: 4, labelKey: 'mgmtCompany.stepRevenue' },
];

export const REVENUE_MODELS = [
    {
        id: 'pool',
        titleKey: 'mgmtCompany.modelPoolTitle',
        tagKey: 'mgmtCompany.modelPoolTag',
        tagBg: '#e5f3e9',
        tagColor: '#1f7a44',
        bodyKey: 'mgmtCompany.modelPoolBody',
    },
    {
        id: 'ownership',
        titleKey: 'mgmtCompany.modelOwnershipTitle',
        tagKey: 'mgmtCompany.modelOwnershipTag',
        tagBg: '#e6ecf6',
        tagColor: '#2f4f86',
        bodyKey: 'mgmtCompany.modelOwnershipBody',
    },
    {
        id: 'commission',
        titleKey: 'mgmtCompany.modelCommissionTitle',
        tagKey: 'mgmtCompany.modelCommissionTag',
        tagBg: '#f7ecdd',
        tagColor: '#8a5a2b',
        bodyKey: 'mgmtCompany.modelCommissionBody',
    },
];

export const LEGAL_OPTIONS = [
    {
        id: false,
        titleKey: 'mgmtCompany.legalAgreementTitle',
        bodyKey: 'mgmtCompany.legalAgreementBody',
        points: [
            { strongKey: 'mgmtCompany.legalAgreementP1Strong', restKey: 'mgmtCompany.legalAgreementP1Rest' },
            { strongKey: 'mgmtCompany.legalAgreementP2Strong', restKey: 'mgmtCompany.legalAgreementP2Rest' },
            { strongKey: 'mgmtCompany.legalAgreementP3Strong', restKey: 'mgmtCompany.legalAgreementP3Rest' },
        ],
    },
    {
        id: true,
        titleKey: 'mgmtCompany.legalRegisteredTitle',
        bodyKey: 'mgmtCompany.legalRegisteredBody',
        points: [
            { strongKey: 'mgmtCompany.legalRegisteredP1Strong', restKey: 'mgmtCompany.legalRegisteredP1Rest' },
            { strongKey: 'mgmtCompany.legalRegisteredP2Strong', restKey: 'mgmtCompany.legalRegisteredP2Rest' },
            { strongKey: 'mgmtCompany.legalRegisteredP3Strong', restKey: 'mgmtCompany.legalRegisteredP3Rest' },
        ],
    },
];

/**
 * Maps an apartment's real quality_standard value onto the segment style
 * buckets above (no separate "no co-hosts yet" segment vocabulary needed).
 */
export function segmentForStandard(standard) {
    if (standard === 'premium') {
        return 'Premium';
    }

    if (standard === 'above_average') {
        return 'Mid-range';
    }

    return 'Standard';
}

/**
 * There is exactly one real member (the creator) until the invite-a-co-host
 * flow ships, so the split is always 100% — no fabricated co-hosts.
 */
export function splitMemberRows(hostName, includedCount) {
    return [
        {
            key: 'me',
            name: hostName,
            basis: `${includedCount} apartments included`,
            pct: 100,
        },
    ];
}
