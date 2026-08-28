const BASE_STYLE_SHEETS = [
    '/home/assets/bootstrap.min.css',
    '/home/assets/swiper-bundle.min.css',
    '/home/assets/ion.rangeSlider.min.css',
    '/home/assets/fancybox.min.css',
    '/home/assets/fonts.css',
    '/home/assets/neno.css',
    '/home/assets/grid.css',
    '/home/assets/common.css',
    '/home/assets/style.css',
    '/home/assets/style-main.css',
    '/home/assets/responsive.css',
    '/home/assets/daterangepicker.css',
];

const PAGE_CONFIG = {
    home: {
        bodyClasses: ['home', 'wp-theme-visitvietnam', 'page-template-home-page'],
        extraSheets: ['/home/assets/home-fixes.css'],
    },
    apartments: {
        bodyClasses: ['search', 'wp-theme-visitvietnam'],
        extraSheets: ['/home/assets/apartments-fixes.css'],
    },
    'apartment-detail': {
        bodyClasses: ['single-apartment', 'wp-theme-visitvietnam'],
        extraSheets: ['/home/assets/apartment-detail-fixes.css'],
    },
    booking: {
        bodyClasses: ['page-template-booking', 'wp-theme-visitvietnam'],
        extraSheets: [],
    },
};

export function usePublicLegacyStyles(pageKey = 'home') {
    const config = PAGE_CONFIG[pageKey] ?? PAGE_CONFIG.home;
    let mounted = false;

    function mount() {
        if (mounted || typeof document === 'undefined') {
            return;
        }

        mounted = true;
        document.body.classList.add(...config.bodyClasses);

        [...BASE_STYLE_SHEETS, ...config.extraSheets].forEach((href) => {
            if (document.querySelector(`link[data-public-legacy="${href}"]`)) {
                return;
            }

            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.dataset.publicLegacy = href;
            document.head.appendChild(link);
        });
    }

    function unmount() {
        if (!mounted || typeof document === 'undefined') {
            return;
        }

        mounted = false;
        document.body.classList.remove(...config.bodyClasses);
        document.querySelectorAll('link[data-public-legacy]').forEach((node) => node.remove());
    }

    return { mount, unmount };
}
