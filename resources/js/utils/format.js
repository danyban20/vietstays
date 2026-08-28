export function formatVnd(amount) {
    return `${new Intl.NumberFormat('en').format(Math.round(amount))} VND`;
}

export function formatDate(dateStr) {
    if (!dateStr) return '—';
    return new Intl.DateTimeFormat('en', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(new Date(dateStr));
}

export function formatPeriod(from, to) {
    return `${formatDate(from)} – ${formatDate(to)}`;
}

export function nightsBetween(from, to) {
    if (!from || !to) return 0;
    const start = new Date(from);
    const end = new Date(to);
    const diff = Math.round((end - start) / (1000 * 60 * 60 * 24));
    return Math.max(1, diff);
}

export function formatStatus(status) {
    const labels = {
        active: 'Active',
        pending: 'Pending approval',
        draft: 'Draft',
        confirmed: 'Confirmed',
        cancelled: 'Cancelled',
    };
    return labels[status] ?? status;
}
