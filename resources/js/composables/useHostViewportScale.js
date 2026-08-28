import { onMounted, onUnmounted, ref } from 'vue';

const DEFAULT_STAGE_W = 1520;
const DEFAULT_STAGE_H = 720;
const NARROW_BREAKPOINT = 1100;

/**
 * Matches design_handoff Aug 20 prototype: fixed design canvas scaled to fit the viewport,
 * or fluid fill-window mode (stageFluid) with a 1100px narrow breakpoint.
 */
export function useHostViewportScale(options = {}) {
    const stageW = ref(options.stageW ?? DEFAULT_STAGE_W);
    const stageH = ref(options.stageH ?? DEFAULT_STAGE_H);
    const stageScale = ref(1);
    const stageFluid = ref(options.fluid ?? true);
    const narrowLayout = ref(false);

    function updateLayout() {
        const vw = window.innerWidth || document.documentElement.clientWidth || stageW.value;
        const vh = window.innerHeight || document.documentElement.clientHeight || stageH.value;

        if (stageFluid.value) {
            if (stageScale.value !== 1) {
                stageScale.value = 1;
            }
            const nextNarrow = vw < NARROW_BREAKPOINT;
            if (nextNarrow !== narrowLayout.value) {
                narrowLayout.value = nextNarrow;
            }
            return;
        }

        const scale = Math.min(vw / stageW.value, vh / stageH.value) || 1;
        if (scale > 0.01 && Math.abs(scale - stageScale.value) > 0.002) {
            stageScale.value = scale;
        }

        const nextNarrow = stageW.value < NARROW_BREAKPOINT;
        if (nextNarrow !== narrowLayout.value) {
            narrowLayout.value = nextNarrow;
        }
    }

    let resizeObserver;

    onMounted(() => {
        updateLayout();
        window.addEventListener('resize', updateLayout);
        resizeObserver = typeof ResizeObserver !== 'undefined'
            ? new ResizeObserver(updateLayout)
            : null;
        resizeObserver?.observe(document.documentElement);
        requestAnimationFrame(updateLayout);
        setTimeout(updateLayout, 120);
    });

    onUnmounted(() => {
        window.removeEventListener('resize', updateLayout);
        resizeObserver?.disconnect();
    });

    return {
        stageW,
        stageH,
        stageScale,
        stageFluid,
        narrowLayout,
        updateLayout,
    };
}
