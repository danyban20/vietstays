export function isValidApartmentImage(image) {
    if (!image || typeof image !== 'object') {
        return false;
    }

    const src = image.thumb || image.url || '';

    return typeof src === 'string' && src.trim() !== '';
}

export function resolveApartmentImageUrl(src) {
    if (!src || typeof src !== 'string') {
        return '';
    }

    if (src.startsWith('http') || src.startsWith('blob:') || src.startsWith('data:')) {
        return src;
    }

    if (src.startsWith('/')) {
        return src;
    }

    return `/storage/${src.replace(/^\//, '')}`;
}

export function stripHtml(value) {
    if (!value) {
        return '';
    }

    return value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
}
