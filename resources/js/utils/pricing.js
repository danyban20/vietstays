export const APARTMENT_TYPES = ['Studio', '1BR', '2BR', '3BR', '4BR'];

export const QUALITY_STANDARDS = [
    { value: 'standard', label: 'Standard', sublabel: 'Good standard' },
    { value: 'above_average', label: 'Above average', sublabel: 'High standard' },
    { value: 'premium', label: 'Premium', sublabel: 'Top standard' },
];

export const BASE_PRICES = {
    Studio: 800_000,
    '1BR': 1_200_000,
    '2BR': 2_000_000,
    '3BR': 3_000_000,
    '4BR': 4_500_000,
};

export const STANDARD_MULTIPLIERS = {
    standard: 1.0,
    above_average: 1.1,
    premium: 1.2,
};

export function suggestDailyPrice(apartmentType, qualityStandard) {
    const base = BASE_PRICES[apartmentType] ?? 1_200_000;
    const multiplier = STANDARD_MULTIPLIERS[qualityStandard] ?? 1.0;
    return Math.round(base * multiplier);
}

export function bookingSummary({ nightlyRate, nights, cleaningFee = 0, discount = 0 }) {
    const roomTotal = nightlyRate * nights;
    const subtotal = roomTotal + cleaningFee;
    const guestTotal = Math.max(0, subtotal - discount);
    const gmv = guestTotal;
    const platformFee = Math.round(gmv * 0.05);
    const cashPoints = Math.round(gmv * 0.03);
    const hostNet = gmv - platformFee - cashPoints;

    return { roomTotal, subtotal, guestTotal, gmv, platformFee, cashPoints, hostNet };
}
