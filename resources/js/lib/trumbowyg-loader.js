let loadPromise = null;

/**
 * Trumbowyg must load after jQuery is on window — static imports run too early in Vite.
 */
export function loadTrumbowyg() {
    if (loadPromise) {
        return loadPromise;
    }

    loadPromise = (async () => {
        const jQuery = (await import('jquery')).default;

        window.jQuery = jQuery;
        window.$ = jQuery;

        await import('trumbowyg/dist/trumbowyg.min.js');
        await import('trumbowyg/dist/ui/trumbowyg.css');

        const svgPath = (await import('trumbowyg/dist/ui/icons.svg?url')).default;

        if (typeof jQuery.fn.trumbowyg !== 'function') {
            throw new Error('Trumbowyg failed to register with jQuery.');
        }

        return { jQuery, svgPath };
    })();

    return loadPromise;
}
