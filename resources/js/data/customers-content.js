export const CUSTOMER_TABS = [
    { key: 'all', labelKey: 'customers.tabAll' },
    { key: 'action', labelKey: 'customers.tabAction', filter: (customer) => customer.needsAction },
    { key: 'active', labelKey: 'customers.tabActive', filter: (customer) => customer.status === 'Staying now' },
    { key: 'upcoming', labelKey: 'customers.tabUpcoming', filter: (customer) => customer.hasUpcoming },
    { key: 'returning', labelKey: 'customers.tabReturning', filter: (customer) => customer.stays >= 2 },
];

export const SORT_OPTIONS = [
    { key: 'stays', labelKey: 'customers.sortStays' },
    { key: 'nights', labelKey: 'customers.sortNights' },
    { key: 'value', labelKey: 'customers.sortValue' },
    { key: 'next', labelKey: 'customers.sortNext' },
    { key: 'rating', labelKey: 'customers.sortRating' },
    { key: 'name', labelKey: 'customers.sortName' },
];

export const SEGMENT_OPTIONS = [
    { value: '', labelKey: 'customers.segmentAll' },
    { value: 'VIP', labelKey: 'customers.segmentVip' },
    { value: 'Returning', labelKey: 'customers.segmentReturning' },
    { value: 'New customer', labelKey: 'customers.segmentNew' },
];

export const MIN_STAY_OPTIONS = [
    { value: '', labelKey: 'customers.minStayAll' },
    { value: '2', labelKey: 'customers.minStay2' },
    { value: '3', labelKey: 'customers.minStay3' },
    { value: '5', labelKey: 'customers.minStay5' },
];

export function formatVnd(value) {
    return `${Math.round(Number(value) || 0).toLocaleString('en-US')} ₫`;
}

export function segmentStyle(segment) {
    if (segment === 'VIP') {
        return { bg: '#f2ead9', color: '#8a6d3b' };
    }

    if (segment === 'Returning') {
        return { bg: '#e5f3ea', color: '#1f7a44' };
    }

    if (segment === 'Temporary') {
        return { bg: '#f0e7f4', color: '#7a4b8a' };
    }

    if (segment === 'New customer') {
        return { bg: '#eef2f5', color: '#2f6d7a' };
    }

    return { bg: '#eef2f5', color: '#2f6d7a' };
}

export function statusStyle(status) {
    if (status === 'Staying now') {
        return { bg: '#e4ecdf', color: '#1f7a44', label: status };
    }

    if (status === 'Upcoming') {
        return { bg: '#eaf0f5', color: '#2f6d7a', label: status };
    }

    if (status === 'Past') {
        return { muted: true, label: 'No active booking' };
    }

    return { bg: '#f2ead9', color: '#8a6d3b', label: status };
}

export function bookingStatusStyle(status) {
    if (status === 'In progress') {
        return { bg: '#e4ecdf', color: '#1f7a44' };
    }

    if (status === 'Upcoming' || status === 'New booking') {
        return { bg: '#eaf0f5', color: '#2f6d7a' };
    }

    if (status === 'Cancelled') {
        return { bg: '#fbe0df', color: '#c4502a' };
    }

    return { bg: '#f2ead9', color: '#8a6d3b' };
}

export function countryFlagEmoji(flag) {
    if (!flag || flag.length !== 2) {
        return null;
    }

    return flag
        .toUpperCase()
        .split('')
        .map((char) => String.fromCodePoint(127397 + char.charCodeAt(0)))
        .join('');
}
