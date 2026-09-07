export const OPS_ROLE_DEFS = [
    { key: 'cleaning', label: 'Cleaning', bg: '#e4ecdf', color: '#1f7a44' },
    { key: 'keys', label: 'Key delivery', bg: '#f7e8dd', color: '#b5651d' },
    { key: 'courier', label: 'Courier', bg: '#ece9f5', color: '#6b5aa8' },
    { key: 'cash', label: 'Money collector', bg: '#f2ead9', color: '#8a6d3b' },
];

export function initials(name) {
    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
}

export function formatMillionsVnd(amount) {
    const millions = amount / 1_000_000;
    if (millions >= 1000) {
        const billions = amount / 1_000_000_000;
        return `${billions.toLocaleString('en-GB', { maximumFractionDigits: 2 })} bn ₫`;
    }

    return `${Math.round(millions).toLocaleString('en-GB')} mill ₫`;
}

export function salesCommission(member) {
    if (member.pooled || member.type === 'agent') {
        return 0;
    }

    return Math.round((member.gross90 || 0) * (member.outPct || 0) / 100);
}

export function salesRateLabel(member) {
    if (member.pooled) {
        return '0 %';
    }

    return `${member.outPct} %`;
}

export function linkLabel(link) {
    return link === 'internal' ? 'Internal' : 'External';
}

export function statusLabel(status) {
    const map = {
        active: 'Active',
        paused: 'Paused',
        away: 'Away',
    };

    return map[status] || status;
}

export function opsRoleDef(key) {
    return OPS_ROLE_DEFS.find((role) => role.key === key);
}
