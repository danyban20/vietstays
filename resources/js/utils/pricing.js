export const APARTMENT_TYPES = ['Studio', '1BR', '2BR', '3BR', '4BR'];

export const QUALITY_STANDARDS = [
    { value: 'standard', label: 'Standard', sublabel: 'Good standard' },
    { value: 'above_average', label: 'Above average', sublabel: 'High standard' },
    { value: 'premium', label: 'Premium', sublabel: 'Top standard' },
];

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
