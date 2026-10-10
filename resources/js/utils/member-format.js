// Formatting for the customer dashboard. Dates from the API are plain
// YYYY-MM-DD strings in Vietnam time; they are parsed as local calendar
// dates so the browser's own timezone never shifts them by a day.

export function parseDay(value) {
    if (!value) {
        return null;
    }

    const [year, month, day] = String(value).slice(0, 10).split('-').map(Number);

    return new Date(year, month - 1, day);
}

export function formatDay(value, options = { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' }) {
    const date = parseDay(value);

    return date ? date.toLocaleDateString('en-US', options) : '';
}

export function formatShortRange(from, to) {
    const start = parseDay(from);
    const end = parseDay(to);

    if (!start || !end) {
        return '';
    }

    const sameYear = start.getFullYear() === end.getFullYear();
    const startLabel = start.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        ...(sameYear ? {} : { year: 'numeric' }),
    });
    const endLabel = end.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

    return `${startLabel} – ${endLabel}`;
}

export function formatMonthYear(value) {
    return formatDay(value, { month: 'short', year: 'numeric' });
}

export function formatMoney(amount, currency = 'VND') {
    const value = Math.round(Number(amount) || 0);

    return `${value.toLocaleString('en-US')} ${currency}`;
}

export function plural(count, word, pluralWord = `${word}s`) {
    return `${count} ${count === 1 ? word : pluralWord}`;
}

export function guestsLabel(adults, children = 0) {
    const parts = [plural(adults, 'adult')];

    if (children > 0) {
        parts.push(plural(children, 'child', 'children'));
    }

    return parts.join(', ');
}

export function formatTimeAgo(iso) {
    if (!iso) {
        return '';
    }

    const date = new Date(iso);
    const diff = (Date.now() - date.getTime()) / 1000;

    if (diff < 60) {
        return 'Just now';
    }

    if (diff < 3600) {
        return `${Math.floor(diff / 60)} min ago`;
    }

    if (diff < 86400 && new Date().getDate() === date.getDate()) {
        return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    }

    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
}

export async function copyText(text) {
    try {
        await navigator.clipboard.writeText(text);
        return true;
    } catch {
        const area = document.createElement('textarea');
        area.value = text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();
        const ok = document.execCommand('copy');
        area.remove();
        return ok;
    }
}
