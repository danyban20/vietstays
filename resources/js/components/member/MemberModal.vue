<template>
    <Teleport to="body">
        <div v-if="open" class="member-modal" role="dialog" aria-modal="true" :aria-label="title" @mousedown.self="close">
            <div class="popup_block member-modal__panel" :class="panelClass">
                <button type="button" class="member-modal__close" aria-label="Close" @click="close" />
                <div class="popup_block_inner">
                    <h3 v-if="title">{{ title }}</h3>
                    <slot />
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { onBeforeUnmount, watch } from 'vue';

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: '' },
    panelClass: { type: [String, Array, Object], default: '' },
});

const emit = defineEmits(['close']);

function close() {
    emit('close');
}

function onKey(event) {
    if (event.key === 'Escape') {
        close();
    }
}

watch(
    () => props.open,
    (open) => {
        document.body.classList.toggle('member-modal-open', open);

        if (open) {
            document.addEventListener('keydown', onKey);
        } else {
            document.removeEventListener('keydown', onKey);
        }
    },
    { immediate: true },
);

onBeforeUnmount(() => {
    document.body.classList.remove('member-modal-open');
    document.removeEventListener('keydown', onKey);
});
</script>
