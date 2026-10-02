/**
 * Returns `redirect` only when it is a path inside this site ("/apartments/3"),
 * so a crafted ?redirect=https://evil.example can't send people elsewhere
 * after they sign in. Anything else falls back to `fallback`.
 */
export function safeRedirect(redirect, fallback) {
    if (typeof redirect === 'string' && redirect.startsWith('/') && !redirect.startsWith('//')) {
        return redirect;
    }

    return fallback;
}
