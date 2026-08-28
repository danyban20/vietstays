export const CALENDAR_DAYS = 14;

export const CALENDAR_TYPE_COLORS = {
    vietstays: '#12352b',
    airbnb: '#c9c2ac',
    external: '#c9c2ac',
    blocked: '#e0793a',
};

export function isoDate(date) {
    const y = date.getFullYear();
    const m = String(date.getMonth() + 1).padStart(2, '0');
    const d = String(date.getDate()).padStart(2, '0');
    return `${y}-${m}-${d}`;
}

export function parseIsoDate(value) {
    if (!value) return null;
    const date = new Date(`${value}T00:00:00`);
    return Number.isNaN(date.getTime()) ? null : date;
}

export function startOfDay(date) {
    const next = new Date(date);
    next.setHours(0, 0, 0, 0);
    return next;
}

export function defaultWindowStart() {
    const today = startOfDay(new Date());
    today.setDate(today.getDate() - 2);
    return today;
}

export function buildCalendarDays(windowStart, dayCount = CALENDAR_DAYS) {
    const start = startOfDay(windowStart);
    const todayStr = startOfDay(new Date()).toDateString();

    return Array.from({ length: dayCount }, (_, index) => {
        const date = new Date(start);
        date.setDate(start.getDate() + index);
        const isToday = date.toDateString() === todayStr;

        return {
            iso: isoDate(date),
            dayNum: date.getDate(),
            monthLabel: date.toLocaleDateString('en', { month: 'short' }).replace('.', ''),
            isToday,
        };
    });
}

export function formatCalendarRange(days, usingTodayDefault = false) {
    if (!days.length) return '';

    if (usingTodayDefault) {
        const today = startOfDay(new Date());
        const end = new Date(today);
        end.setDate(today.getDate() + 11);
        const fmt = (date) =>
            `${date.getDate()}. ${date.toLocaleDateString('en', { month: 'short' }).replace('.', '')}`;
        return `${fmt(today)} – ${fmt(end)}`;
    }

    const first = days[0];
    const last = days[days.length - 1];
    return `${first.dayNum}. ${first.monthLabel} – ${last.dayNum}. ${last.monthLabel}`;
}

export function clipBookingBar(item, windowStart, windowEnd, dayCount = CALENDAR_DAYS) {
    const checkIn = parseIsoDate(item.check_in);
    const checkOut = parseIsoDate(item.check_out);

    if (!checkIn || !checkOut || checkOut <= windowStart || checkIn >= windowEnd) {
        return null;
    }

    const clippedStart = checkIn < windowStart ? windowStart : checkIn;
    const clippedEnd = checkOut > windowEnd ? windowEnd : checkOut;
    const dayWidthPct = 100 / dayCount;
    const startIdx = Math.round((clippedStart - windowStart) / 86400000);
    const spanDays = Math.max(1, Math.round((clippedEnd - clippedStart) / 86400000));

    return {
        ...item,
        leftPct: startIdx * dayWidthPct,
        widthPct: spanDays * dayWidthPct,
        color: CALENDAR_TYPE_COLORS[item.type] ?? CALENDAR_TYPE_COLORS.vietstays,
    };
}

export function matchesCalendarTypeFilter(item, filter) {
    if (filter === 'all') return true;
    if (filter === 'vietstays') return item.type === 'vietstays';
    if (filter === 'airbnb') return item.type === 'airbnb';
    if (filter === 'blocked') return item.type === 'blocked';
    return true;
}
