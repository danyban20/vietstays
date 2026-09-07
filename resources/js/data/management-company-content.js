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

export const MY_APARTMENTS = [
    { code: 'SUN-1204', name: 'Sunrise - City View & Pool - D7 - 2BR', beds: '2 bedrooms', sqm: '68 m²', district: 'D7', rate: '1.8M', segment: 'Mid-range' },
    { code: 'SUN-1807', name: 'Sunrise - High floor - D7 - 1BR', beds: '1 bedroom', sqm: '52 m²', district: 'D7', rate: '1.4M', segment: 'Standard' },
    { code: 'VIN-A1203', name: 'Vinhomes CP - Park View - Bình Thạnh - 1BR', beds: '1 bedroom', sqm: '54 m²', district: 'Bình Thạnh', rate: '1.6M', segment: 'Standard' },
    { code: 'VIN-B0905', name: 'Vinhomes CP - Landmark view - Bình Thạnh - 2BR', beds: '2 bedrooms', sqm: '61 m²', district: 'Bình Thạnh', rate: '1.7M', segment: 'Mid-range' },
    { code: 'MAS-B1702', name: 'Masteri - Riverside - Thủ Đức - 2BR', beds: '2 bedrooms', sqm: '72 m²', district: 'Thủ Đức', rate: '2.1M', segment: 'Premium' },
    { code: 'MAS-C0410', name: 'Masteri - Quiet side - Thủ Đức - 1BR', beds: '1 bedroom', sqm: '58 m²', district: 'Thủ Đức', rate: '1.5M', segment: 'Standard' },
    { code: 'NAS-0906', name: 'Nassim - Corner unit - D2 - 2BR', beds: '2 bedrooms', sqm: '61 m²', district: 'D2', rate: '1.9M', segment: 'Mid-range' },
    { code: 'SAP-R1401', name: 'Saigon Pearl - Ruby Tower - Bình Thạnh - 3BR', beds: '3 bedrooms', sqm: '88 m²', district: 'Bình Thạnh', rate: '2.6M', segment: 'Premium' },
    { code: 'SAP-T2103', name: 'Saigon Pearl - Topaz river view - Bình Thạnh - 2BR', beds: '2 bedrooms', sqm: '76 m²', district: 'Bình Thạnh', rate: '2.2M', segment: 'Mid-range' },
    { code: 'SUW-0508', name: 'Sunwah - Pool level - D3 - 2BR', beds: '2 bedrooms', sqm: '64 m²', district: 'D3', rate: '1.8M', segment: 'Standard' },
    { code: 'MIL-1109', name: 'Millennium - Canal view - D4 - 1BR', beds: '1 bedroom', sqm: '48 m²', district: 'D4', rate: '1.1M', segment: 'Standard' },
    { code: 'MIL-1802', name: 'Millennium - Compact south - D4 - Studio', beds: 'Studio', sqm: '32 m²', district: 'D4', rate: '1.0M', segment: 'Standard' },
    { code: 'TRE-0703', name: 'Tresor - River promenade - D4 - 1BR', beds: '1 bedroom', sqm: '55 m²', district: 'D4', rate: '1.3M', segment: 'Standard' },
    { code: 'ZEN-0201', name: 'Zenity - Central compact - D1 - Studio', beds: 'Studio', sqm: '34 m²', district: 'D1', rate: '0.9M', segment: 'Standard' },
    { code: 'GRM-1501', name: 'Grand Marina - Family apartment - D1 - 3BR', beds: '3 bedrooms', sqm: '96 m²', district: 'D1', rate: '—', segment: 'Premium' },
];

export const HOST_DIRECTORY = [
    { id: 'VS-2841', initials: 'TN', name: 'Thanh Ngo', city: 'Ho Chi Minh City', verified: true },
    { id: 'VS-3190', initials: 'KA', name: 'Kari Aasen', city: 'Ho Chi Minh City', verified: true },
    { id: 'VS-1176', initials: 'MH', name: 'Minh Ha Le', city: 'Ha Noi', verified: true },
    { id: 'VS-4402', initials: 'JD', name: 'Jonas Dahl', city: 'Da Nang', verified: false },
    { id: 'VS-2255', initials: 'PT', name: 'Phuong Tran', city: 'Ho Chi Minh City', verified: true },
];

export const SETUP_MEMBERS = [
    { initials: 'MN', name: 'Mai Nguyen', role: 'Creator', isYou: true, apartments: 12, status: 'Creator' },
    { initials: 'LB', name: 'Lars Ø. Bakken', role: 'Host', isYou: false, apartments: 7, status: 'Accepted' },
    { initials: 'HN', name: 'Hoa Nguyen', role: 'Host', isYou: false, apartments: 3, status: 'Accepted' },
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

export function suggestedShares(includedCount, totalApartments = MY_APARTMENTS.length) {
    const counts = [includedCount, 7, 3];
    const total = counts.reduce((sum, value) => sum + value, 0) || 1;
    const raw = counts.map((value) => Math.round((value / total) * 100));
    raw[0] += 100 - raw.reduce((sum, value) => sum + value, 0);

    return { me: raw[0], lars: raw[1], hoa: raw[2] };
}

export function splitMemberRows(shares, includedCount) {
    const names = ['Mai Nguyen (you)', 'Lars Ø. Bakken', 'Hoa Nguyen'];
    const bases = [
        `${includedCount} apartments included`,
        'Agreed ownership share',
        'Agreed ownership share',
    ];
    const since = ['Mar 2021 · 5 yrs', 'Nov 2023 · 2 yrs', 'Jan 2019 · 7 yrs'];
    const ratings = ['4.86 · 318 stays', '4.62 · 96 stays', '4.91 · 640 stays'];

    return ['me', 'lars', 'hoa'].map((key, index) => ({
        key,
        name: names[index],
        basis: bases[index],
        since: since[index],
        rating: ratings[index],
        pct: shares[key],
    }));
}
