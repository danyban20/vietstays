import jQuery from 'jquery';
import momentImport from 'moment';

const moment = typeof momentImport === 'function' ? momentImport : momentImport.default;
const $ = jQuery;

let pluginReady = null;

async function ensurePluginLoaded() {
    if (typeof $.fn.daterangepicker === 'function') {
        return;
    }

    if (!pluginReady) {
        pluginReady = (async () => {
            window.jQuery = window.$ = jQuery;
            window.moment = moment;

            await import('daterangepicker/daterangepicker.js');

            if (typeof $.fn.daterangepicker !== 'function') {
                throw new Error('Date range picker plugin failed to load.');
            }
        })();
    }

    await pluginReady;
}

export function useHomeSearchBar() {
    let $input = null;

    async function initDateRangePicker(inputEl, { onApply, onClear, onShow } = {}) {
        if (!inputEl) {
            return;
        }

        await ensurePluginLoaded();
        $input = $(inputEl);

        if ($input.data('daterangepicker')) {
            return;
        }

        $input.daterangepicker({
            autoUpdateInput: false,
            autoApply: true,
            minDate: new Date(),
            locale: {
                cancelLabel: 'Clear',
            },
        });

        $input.on('apply.daterangepicker', (_ev, picker) => {
            $input.val(
                `${picker.startDate.format('MM/DD/YYYY')} - ${picker.endDate.format('MM/DD/YYYY')}`,
            );
            onApply?.(
                picker.startDate.format('YYYY-MM-DD'),
                picker.endDate.format('YYYY-MM-DD'),
            );
        });

        $input.on('cancel.daterangepicker', () => {
            $input.val('');
            onClear?.();
        });

        $input.on('show.daterangepicker', () => {
            document.body.classList.add('book_overlay_open');
            onShow?.();
        });

        $input.on('hide.daterangepicker', () => {
            document.body.classList.remove('book_overlay_open');
        });
    }

    function hideDatePicker() {
        if (!$input) {
            return;
        }

        const picker = $input.data('daterangepicker');
        if (picker?.isShowing) {
            picker.hide();
        }
    }

    function destroyDateRangePicker() {
        if (!$input) {
            return;
        }

        const picker = $input.data('daterangepicker');
        if (picker) {
            picker.remove();
        }

        $input.off('apply.daterangepicker cancel.daterangepicker show.daterangepicker hide.daterangepicker');
        $input = null;
    }

    return { initDateRangePicker, hideDatePicker, destroyDateRangePicker };
}
