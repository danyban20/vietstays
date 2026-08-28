/**
 * Base path when Laravel is served from a subdirectory (e.g. /public on shared hosting).
 * Set via <meta name="app-base"> in the Blade shell from APP_URL.
 */
export function getAppBasePath() {
    if (typeof document === 'undefined') {
        return '';
    }

    const raw = document.querySelector('meta[name="app-base"]')?.content ?? '';

    if (!raw || raw === '/') {
        return '';
    }

    return raw.replace(/\/$/, '');
}

export function withAppBase(path) {
    const base = getAppBasePath();
    const normalized = path.startsWith('/') ? path : `/${path}`;

    return `${base}${normalized}`;
}

/**
 * Shared-hosting fix: trailing-slash rewrites can expose /public/ in the browser URL.
 * Normalize /public/admin → /admin and /admin/public/admin → /admin before Vue boots.
 */
export function normalizeLegacyAppUrl(appSegment) {
    if (typeof window === 'undefined') {
        return;
    }

    const segment = appSegment.replace(/^\//, '');
    const { pathname, search, hash } = window.location;

    const legacyPublic = new RegExp(`^/public/${segment}(?=/|$)`);
    const legacyDoubled = new RegExp(`^/${segment}/public/${segment}(?=/|$)`);

    if (!legacyPublic.test(pathname) && !legacyDoubled.test(pathname)) {
        return;
    }

    const suffix = pathname.replace(
        new RegExp(`^/(?:public/${segment}|${segment}/public/${segment})`),
        '',
    );

    window.location.replace(`/${segment}${suffix}${search}${hash}`);
}
