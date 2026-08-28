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

export function useApartmentBookingDates() {
    const pickers = [];

    async function initSingleDatePicker(inputEl, { onApply, minDate } = {}) {
        if (!inputEl) {
            return;
        }

        await ensurePluginLoaded();

        const $input = $(inputEl);

        if ($input.data('daterangepicker')) {
            return;
        }

        $input.daterangepicker({
            autoUpdateInput: false,
            alwaysShowCalendars: true,
            singleDatePicker: true,
            autoApply: true,
            minDate: minDate ?? new Date(),
            locale: {
                cancelLabel: 'Clear',
                format: 'MM/DD/YYYY',
            },
        });

        $input.on('apply.daterangepicker', (_ev, picker) => {
            $input.val(picker.startDate.format('MM/DD/YYYY'));
            onApply?.(picker.startDate.format('YYYY-MM-DD'), picker);
        });

        $input.on('cancel.daterangepicker', () => {
            $input.val('');
            onApply?.('', null);
        });

        pickers.push($input);
    }

    function destroy() {
        pickers.forEach(($input) => {
            const instance = $input.data('daterangepicker');
            if (instance) {
                instance.remove();
            }
            $input.off('apply.daterangepicker cancel.daterangepicker');
        });
        pickers.length = 0;
    }

    return { initSingleDatePicker, destroy };
}
