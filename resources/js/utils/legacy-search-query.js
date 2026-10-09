function first(value) {
    if (Array.isArray(value)) {
        return value[0] ?? '';
    }

    return value ?? '';
}

function toIsoDate(value) {
    const raw = String(value || '').trim();
    if (!raw) {
        return '';
    }

    const slash = raw.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
    if (slash) {
        return `${slash[3]}-${slash[1].padStart(2, '0')}-${slash[2].padStart(2, '0')}`;
    }

    const timestamp = Date.parse(raw);
    if (Number.isNaN(timestamp)) {
        return '';
    }

    const date = new Date(timestamp);
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

export function hasLegacySearchQuery(query = {}) {
    return 'city_id' in query || 'vv_action' in query || 'datefilter' in query;
}

export function rewriteLegacySearchQuery(query = {}) {
    const next = { ...query };
    delete next.vv_action;

    const cityId = first(next.city_id);
    if (!first(next.city) && cityId) {
        next.city = cityId;
    }
    delete next.city_id;

    const datefilter = first(next.datefilter);
    if (datefilter.includes(' - ')) {
        const [rawFrom, rawTo] = datefilter.split(' - ').map((part) => part.trim());
        const from = toIsoDate(rawFrom);
        const to = toIsoDate(rawTo);
        if (from && !first(next.from)) {
            next.from = from;
        }
        if (to && !first(next.to)) {
            next.to = to;
        }
    }
    delete next.datefilter;

    return Object.fromEntries(
        Object.entries(next).filter(([, value]) => value !== '' && value != null),
    );
}
