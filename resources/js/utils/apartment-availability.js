export const PERIOD_COLORS = {
    vietstays: '#12352b',
    blocked: '#e0793a',
    external: '#c9c2ac',
};

export const PERIOD_LABELS = {
    vietstays: 'Vietstays booking',
    blocked: 'Blocked',
    external: 'External',
};

export function parseIso(value) {
    if (!value) return null;
    const date = new Date(`${value}T00:00:00`);
    return Number.isNaN(date.getTime()) ? null : date;
}

export function isoDate(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

export function startOfDay(date) {
    const next = new Date(date);
    next.setHours(0, 0, 0, 0);
    return next;
}

export function addDays(date, days) {
    const next = new Date(date);
    next.setDate(next.getDate() + days);
    return startOfDay(next);
}

export function formatPeriodRange(startIso, endIso) {
    const start = parseIso(startIso);
    const end = parseIso(endIso);
    if (!start || !end) return '';

    const lastNight = addDays(end, -1);
    const fmt = (date) =>
        date.toLocaleDateString(undefined, { day: 'numeric', month: 'short', year: 'numeric' });

    return `${fmt(start)} – ${fmt(lastNight)}`;
}

export function periodCoversDate(period, iso) {
    const date = parseIso(iso);
    const start = parseIso(period.start_date);
    const end = parseIso(period.end_date);
    if (!date || !start || !end) return false;

    return date >= start && date < end;
}

export function buildMonthGrid(year, month, locale = undefined) {
    const first = new Date(year, month, 1);
    const last = new Date(year, month + 1, 0);
    const startPad = (first.getDay() + 6) % 7;
    const daysInMonth = last.getDate();
    const cells = [];

    for (let i = 0; i < startPad; i += 1) {
        cells.push({ empty: true, key: `pad-${i}` });
    }

    const todayStr = startOfDay(new Date()).toDateString();

    for (let day = 1; day <= daysInMonth; day += 1) {
        const date = new Date(year, month, day);
        cells.push({
            empty: false,
            key: isoDate(date),
            iso: isoDate(date),
            day,
            isToday: date.toDateString() === todayStr,
            weekday: date.toLocaleDateString(locale, { weekday: 'short' }),
        });
    }

    return {
        year,
        month,
        label: first.toLocaleDateString(locale, { month: 'long', year: 'numeric' }),
        cells,
    };
}

export function cellPeriodType(periods, iso, hoverId = null) {
    if (hoverId) {
        const hovered = periods.find((p) => p.id === hoverId);
        if (hovered && periodCoversDate(hovered, iso)) {
            return hovered.period_type;
        }
        return 'dimmed';
    }

    const match = periods.find((p) => periodCoversDate(p, iso));
    return match?.period_type ?? null;
}

export function upcomingPeriods(periods, today = startOfDay(new Date())) {
    return periods.filter((period) => {
        const end = parseIso(period.end_date);
        return end && end >= today;
    });
}
