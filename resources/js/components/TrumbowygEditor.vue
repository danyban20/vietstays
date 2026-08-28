<template>
    <div class="trumbowyg-editor">
        <p v-if="editorError" class="host-form-error">{{ editorError }}</p>
        <textarea v-show="!editorError" ref="textareaRef" />
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { loadTrumbowyg } from '@/lib/trumbowyg-loader';

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const textareaRef = ref(null);
const editorError = ref('');
let $editor = null;
let jQuery = null;

function syncFromEditor() {
    if (! $editor) {
        return;
    }

    const html = $editor.trumbowyg('html') ?? '';

    if (html !== props.modelValue) {
        emit('update:modelValue', html);
    }
}

function setEditorHtml(html) {
    if (! $editor) {
        return;
    }

    const current = $editor.trumbowyg('html') ?? '';

    if (current !== html) {
        $editor.trumbowyg('html', html);
    }
}

onMounted(async () => {
    if (! textareaRef.value) {
        return;
    }

    try {
        const { jQuery: $, svgPath } = await loadTrumbowyg();
        jQuery = $;
        $editor = jQuery(textareaRef.value);
        $editor.trumbowyg({
            svgPath,
            semantic: false,
            autogrow: true,
            btns: [
                ['viewHTML'],
                ['undo', 'redo'],
                ['formatting'],
                ['strong', 'em', 'del'],
                ['superscript', 'subscript'],
                ['link'],
                ['insertImage'],
                ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                ['unorderedList', 'orderedList'],
                ['horizontalRule'],
                ['removeformat'],
                ['fullscreen'],
            ],
        });

        setEditorHtml(props.modelValue);
        $editor.on('tbwchange tbwblur', syncFromEditor);
    } catch (error) {
        console.error(error);
        editorError.value = 'Rich text editor failed to load.';
    }
});

watch(
    () => props.modelValue,
    (value) => {
        setEditorHtml(value ?? '');
    },
);

onBeforeUnmount(() => {
    if ($editor) {
        $editor.off('tbwchange tbwblur', syncFromEditor);
        $editor.trumbowyg('destroy');
        $editor = null;
    }
});
</script>

<style scoped>
.trumbowyg-editor :deep(.trumbowyg-box) {
    margin: 0;
    border-color: var(--vs-border);
    border-radius: var(--vs-radius-sm);
}

.trumbowyg-editor :deep(.trumbowyg-editor) {
    min-height: 280px;
}

.trumbowyg-editor :deep(.trumbowyg-button-pane) {
    background: var(--vs-panel-sand);
    border-color: var(--vs-border);
}
</style>
