import { withAppBase } from '@/utils/app-base';

const CSRF_COOKIE = 'XSRF-TOKEN';

function getCookie(name) {
    const match = document.cookie.match(new RegExp(`(^|;\\s*)${name}=([^;]*)`));
    return match ? decodeURIComponent(match[2]) : null;
}

let csrfInitialized = false;

function wrapNetworkError(err, context = 'request') {
    if (err instanceof TypeError && err.message === 'Failed to fetch') {
        const error = new Error(
            context === 'upload'
                ? 'Photo upload failed — try fewer or smaller images (max 8 MB each).'
                : 'Could not reach the server. Check your connection and try again.',
        );
        error.cause = err;
        throw error;
    }

    throw err;
}

export async function ensureCsrfCookie(force = false) {
    if (csrfInitialized && !force) {
        return;
    }

    try {
        const response = await fetch(withAppBase('/sanctum/csrf-cookie'), {
            credentials: 'include',
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (!response.ok) {
            throw new Error(`Could not initialize session (HTTP ${response.status}).`);
        }

        csrfInitialized = true;
    } catch (err) {
        csrfInitialized = false;
        wrapNetworkError(err, 'request');
    }
}

function buildHeaders(extra = {}, body = undefined) {
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...extra,
    };

    if (!(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
    }

    const token = getCookie(CSRF_COOKIE);
    if (token) {
        headers['X-XSRF-TOKEN'] = token;
    }

    return headers;
}

function buildRequestBody(body) {
    if (body === undefined) {
        return undefined;
    }

    if (body instanceof FormData || typeof body === 'string') {
        return body;
    }

    return JSON.stringify(body);
}

async function parseResponse(response) {
    const contentType = response.headers.get('content-type') ?? '';

    try {
        if (contentType.includes('application/json')) {
            return await response.json();
        }

        return await response.text();
    } catch {
        throw new Error(`Invalid server response (HTTP ${response.status}).`);
    }
}

function buildApiError(response, payload) {
    const message =
        typeof payload === 'object' && payload?.message
            ? payload.message
            : typeof payload === 'object' && payload?.errors
              ? Object.values(payload.errors).flat().join(' ')
              : typeof payload === 'string' && payload.trim() !== ''
                ? payload.slice(0, 300)
                : `Request failed (HTTP ${response.status})`;

    const error = new Error(message);
    error.status = response.status;
    error.payload = payload;
    return error;
}

async function performFetch(url, options = {}) {
    const method = (options.method ?? 'GET').toUpperCase();
    const isMutation = !['GET', 'HEAD', 'OPTIONS'].includes(method);

    if (isMutation) {
        await ensureCsrfCookie(true);
    } else if (!csrfInitialized) {
        await ensureCsrfCookie();
    }

    const { headers: extraHeaders, body, ...rest } = options;

    const requestInit = {
        credentials: 'include',
        ...rest,
        headers: buildHeaders(extraHeaders, body),
        body: buildRequestBody(body),
    };

    let response;
    try {
        response = await fetch(url, requestInit);
    } catch (err) {
        wrapNetworkError(err, isMutation && body instanceof FormData ? 'upload' : 'request');
    }

    if (response.status === 419 && isMutation) {
        csrfInitialized = false;
        await ensureCsrfCookie(true);
        requestInit.headers = buildHeaders(extraHeaders, body);

        try {
            response = await fetch(url, requestInit);
        } catch (err) {
            wrapNetworkError(err, body instanceof FormData ? 'upload' : 'request');
        }
    }

    return response;
}

export async function api(path, options = {}) {
    const normalizedPath = path.startsWith('/') ? path : `/${path}`;
    const url = withAppBase(normalizedPath.startsWith('/api') ? normalizedPath : `/api${normalizedPath}`);

    const response = await performFetch(url, options);

    if (response.status === 204) {
        return null;
    }

    const payload = await parseResponse(response);

    if (!response.ok) {
        throw buildApiError(response, payload);
    }

    return payload;
}

export async function uploadFiles(path, formData) {
    const normalizedPath = path.startsWith('/') ? path : `/${path}`;
    const url = withAppBase(normalizedPath.startsWith('/api') ? normalizedPath : `/api${normalizedPath}`);

    const response = await performFetch(url, {
        method: 'POST',
        body: formData,
    });

    const payload = await parseResponse(response);

    if (!response.ok) {
        throw buildApiError(response, payload);
    }

    return payload;
}

export const apiClient = {
    get: (path, options) => api(path, { ...options, method: 'GET' }),
    post: (path, body, options) => api(path, { ...options, method: 'POST', body }),
    put: (path, body, options) => api(path, { ...options, method: 'PUT', body }),
    patch: (path, body, options) => api(path, { ...options, method: 'PATCH', body }),
    delete: (path, options) => api(path, { ...options, method: 'DELETE' }),
};

export default apiClient;
