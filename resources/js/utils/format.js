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

export function formatDateNumeric(dateStr) {
    if (!dateStr) return '—';
    const date = new Date(`${dateStr}T00:00:00`);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    return `${day}-${month}-${date.getFullYear()}`;
}

export function formatPeriod(from, to) {
    return `${formatDate(from)} – ${formatDate(to)}`;
}

export function formatPeriodShort(from, to, locale = 'en') {
    if (!from || !to) return '—';
    const formatter = new Intl.DateTimeFormat(locale === 'no' ? 'nb-NO' : locale, {
        day: 'numeric',
        month: 'short',
    });
    return `${formatter.format(new Date(`${from}T00:00:00`))} – ${formatter.format(new Date(`${to}T00:00:00`))}`;
}

export function formatReceivedAt(dateStr, locale = 'en') {
    if (!dateStr) return '';
    const date = new Date(`${dateStr}T12:00:00`);
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const day = new Date(date);
    day.setHours(0, 0, 0, 0);
    const time = new Intl.DateTimeFormat(locale === 'no' ? 'nb-NO' : locale, {
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);

    if (day.getTime() === today.getTime()) {
        return locale === 'no' ? `Mottatt i dag, ${time}` : `Received today, ${time}`;
    }

    return locale === 'no'
        ? `Mottatt ${formatDate(dateStr)}, ${time}`
        : `Received ${formatDate(dateStr)}, ${time}`;
}

export function formatCountdown(deadlineIso) {
    if (!deadlineIso) return '';
    const diff = new Date(deadlineIso).getTime() - Date.now();
    if (diff <= 0) return '0min';

    const hours = Math.floor(diff / 3600000);
    const minutes = Math.floor((diff % 3600000) / 60000);

    if (hours > 0) {
        return `${hours}h ${minutes}min`;
    }

    return `${minutes}min`;
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
