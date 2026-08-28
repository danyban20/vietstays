<template>
    <Teleport to="body">
        <div v-if="open" class="host-modal-overlay" @click.self="onBackdrop">
            <div class="host-modal" role="dialog" :aria-label="title">
                <header class="host-modal__header">
                    <h2 class="host-modal__title">{{ title }}</h2>
                    <button type="button" class="host-modal__close" aria-label="Close" @click="emit('close')">
                        ✕
                    </button>
                </header>

                <div class="host-modal__body">
                    <slot />
                </div>

                <footer v-if="$slots.footer" class="host-modal__footer">
                    <slot name="footer" />
                </footer>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: '' },
    closeOnBackdrop: { type: Boolean, default: true },
});

const emit = defineEmits(['close']);

function onBackdrop() {
    if (props.closeOnBackdrop) {
        emit('close');
    }
}
</script>
