import { withAppBase } from '@/utils/app-base';

// The customer dashboard uses the designer's own stylesheets (copied to
// public/member). They style bare elements (body, h1, button…), so they are
// only attached while a member page is on screen.
const STYLE_SHEETS = [
    '/member/fonts/dashboard_fonts.css',
    '/member/css/dashboard_grid.css',
    '/member/css/dashboard.css?v=1',
    '/member/css/dashboard_responsive.css?v=1',
    '/home/assets/daterangepicker.css',
    '/member/css/member-extras.css?v=7',
];

const FONT_SHEET = 'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap';

export function useMemberAreaStyles() {
    /**
     * Resolves once the stylesheets have loaded (or after a short timeout),
     * so the page is not shown unstyled.
     */
    function mount() {
        if (typeof document === 'undefined') {
            return Promise.resolve();
        }

        document.body.classList.add('member-area');

        const pending = [FONT_SHEET, ...STYLE_SHEETS.map((href) => withAppBase(href))].map((href) => {
            if (document.querySelector(`link[data-member-area="${href}"]`)) {
                return Promise.resolve();
            }

            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = href;
            link.dataset.memberArea = href;

            const loaded = new Promise((resolve) => {
                link.addEventListener('load', resolve, { once: true });
                link.addEventListener('error', resolve, { once: true });
            });

            document.head.appendChild(link);

            return loaded;
        });

        return Promise.race([
            Promise.all(pending),
            new Promise((resolve) => setTimeout(resolve, 3000)),
        ]);
    }

    function unmount() {
        if (typeof document === 'undefined') {
            return;
        }

        document.body.classList.remove('member-area', 'book_overlay_open', 'member-modal-open');
        document.querySelectorAll('link[data-member-area]').forEach((node) => node.remove());
    }

    return { mount, unmount };
}
